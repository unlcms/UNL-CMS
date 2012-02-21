<?php
/**
 * 'installed' database number codes (also seen in unl_multisite/unl_site_creation.php)
 *  0: 'Scheduled for creation.'
 *  1: 'Curently being created.'
 *  2: 'In production.'
 *  3: 'Scheduled for removal.'
 *  4: 'Currently being removed.'
 *  5: 'Failure/Unknown.'
 *  6: 'Scheduled for site update.'
 */

if (PHP_SAPI != 'cli') {
  echo 'This script must be run from the shell!';
  exit;
}

chdir(dirname(__FILE__) . '/../../../..');
define('DRUPAL_ROOT', getcwd());

require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
drupal_override_server_variables();
drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);
require_once drupal_get_path('module', 'unl') . '/includes/common.php';

unl_edit_sites();
unl_remove_aliases();
unl_remove_sites();
unl_remove_page_aliases();
unl_add_sites();
unl_add_aliases();
unl_add_page_aliases();

function unl_add_sites() {
  $query = db_query('SELECT * FROM {unl_sites} WHERE installed=0');

  while ($row = $query->fetchAssoc()) {
    db_update('unl_sites')
      ->fields(array('installed' => 1))
      ->condition('site_id', $row['site_id'])
      ->execute();
    try {
      unl_add_site($row['site_path'], $row['uri'], $row['clean_url'], $row['db_prefix'], $row['site_id']);
      db_update('unl_sites')
        ->fields(array('installed' => 2))
        ->condition('site_id', $row['site_id'])
        ->execute();
    } catch (Exception $e) {
      watchdog('unl cron', $e->getMessage(), array(), WATCHDOG_ERROR);
      db_update('unl_sites')
        ->fields(array('installed' => 5))
        ->condition('site_id', $row['site_id'])
        ->execute();
    }
  }
}

function unl_remove_sites() {
  $query = db_query('SELECT * FROM {unl_sites} WHERE installed=3');
  while ($row = $query->fetchAssoc()) {
    db_update('unl_sites')
      ->fields(array('installed' => 4))
      ->condition('site_id', $row['site_id'])
      ->execute();
    try {
      unl_remove_site($row['site_path'], $row['uri'], $row['db_prefix'], $row['site_id']);
      db_delete('unl_sites')
        ->condition('site_id', $row['site_id'])
        ->execute();
    } catch (Exception $e) {
      watchdog('unl cron', $e->getMessage(), array(), WATCHDOG_ERROR);
      db_update('unl_sites')
        ->fields(array('installed' => 5))
        ->condition('site_id', $row['site_id'])
        ->execute();
    }
  }
}

function unl_edit_sites() {
  $query = db_query('SELECT * FROM {unl_sites} WHERE installed=6');
  while ($row = $query->fetchAssoc()) {
    try {
      $alias = db_select('unl_sites_aliases')
        ->fields('unl_sites_aliases', array('site_alias_id', 'site_id', 'base_uri', 'path'))
        ->condition('installed', 6)
        ->condition('site_id', $row['site_id'])
        ->execute()
        ->fetchAssoc();

      $new_uri = $alias['base_uri'] . $alias['path'];
      db_update('unl_sites')
        ->fields(array('site_path' => $alias['path'], 'uri' => $new_uri))
        ->condition('site_id', $row['site_id'])
        ->execute();

      db_update('unl_sites_aliases')
        ->fields(array('path' => $row['site_path']))
        ->condition('site_id', $row['site_id'])
        ->condition('installed', 6)
        ->execute();

      unl_remove_site_from_htaccess($row['site_id'], FALSE);
      unl_add_site_to_htaccess($row['site_id'], $alias['path'], FALSE);

      // Original sites subdir
      $sites_subdir = unl_get_sites_subdir($row['uri']);
      $sites_subdir = DRUPAL_ROOT . '/sites/' . $sites_subdir;
      $sites_subdir = realpath($sites_subdir);
      // New sites subdir
      $new_sites_subdir = unl_get_sites_subdir(strtolower($new_uri));
      $new_sites_subdir = DRUPAL_ROOT . '/sites/' . $new_sites_subdir;
      // mv original to new
      shell_exec('chmod -R u+w ' . escapeshellarg($sites_subdir));
      $command = 'mv ' . escapeshellarg($sites_subdir) . ' ' . escapeshellarg($new_sites_subdir);
      shell_exec($command);

      // Recreate all existing aliases so that they point to the new URI.
      $existingAliases = db_select('unl_sites_aliases', 'a')
        ->condition('site_id', $row['site_id'])
        ->condition('installed', 2)
        ->fields('a', array('site_alias_id', 'base_uri', 'path'))
        ->execute()
        ->fetchAll();
      foreach ($existingAliases as $existingAlias) {
          unl_remove_alias($existingAlias->base_uri, $existingAlias->path, $existingAlias->site_alias_id);
          unl_add_alias($new_uri, $existingAlias->base_uri, $existingAlias->path, $existingAlias->site_alias_id);
      }

      // Add the old location as a new alias.
      unl_add_alias($new_uri, $alias['base_uri'], $row['site_path'], $alias['site_alias_id']);

      db_update('unl_sites')
        ->fields(array('installed' => 2))
        ->condition('site_id', $row['site_id'])
        ->execute();
      db_update('unl_sites_aliases')
        ->fields(array('installed' => 2))
        ->condition('site_id', $row['site_id'])
        ->condition('installed', 6)
        ->execute();
    } catch (Exception $e) {
      watchdog('unl cron', $e->getMessage(), array(), WATCHDOG_ERROR);
      db_update('unl_sites')
        ->fields(array('installed' => 5))
        ->condition('site_id', $row['site_id'])
        ->execute();
      db_update('unl_sites_aliases')
        ->fields(array('installed' => 5))
        ->condition('site_id', $row['site_id'])
        ->condition('installed', 6)
        ->execute();
    }
  }
}

function unl_add_aliases() {
  $query = db_select('unl_sites_aliases', 'a');
  $query->join('unl_sites', 's', 's.site_id = a.site_id');
  $query->fields('s', array('uri'));
  $query->fields('a', array('site_alias_id', 'base_uri', 'path'));
  $query->condition('a.installed', 0);
  $results = $query->execute()->fetchAll();

  foreach ($results as $row) {
    db_update('unl_sites_aliases')
      ->fields(array('installed' => 1))
      ->condition('site_alias_id', $row->site_alias_id)
      ->execute();
    try {
      unl_add_alias($row->uri, $row->base_uri, $row->path, $row->site_alias_id);
      db_update('unl_sites_aliases')
        ->fields(array('installed' => 2))
        ->condition('site_alias_id', $row->site_alias_id)
        ->execute();
    } catch (Exception $e) {
      watchdog('unl cron', $e->getMessage(), array(), WATCHDOG_ERROR);
      db_update('unl_sites_aliases')
        ->fields(array('installed' => 5))
        ->condition('site_alias_id', $row->site_alias_id)
        ->execute();
    }
  }
}

function unl_remove_aliases() {
  $query = db_select('unl_sites_aliases', 'a');
  $query->fields('a', array('site_alias_id', 'base_uri', 'path'));
  $query->condition('a.installed', 3);
  $results = $query->execute()->fetchAll();

  foreach ($results as $row) {
    db_update('unl_sites_aliases')
      ->fields(array('installed' => 4))
      ->condition('site_alias_id', $row->site_alias_id)
      ->execute();
    try {
      unl_remove_alias($row->base_uri, $row->path, $row->site_alias_id);
      db_delete('unl_sites_aliases')
        ->condition('site_alias_id', $row->site_alias_id)
        ->execute();
    } catch (Exception $e) {
      watchdog('unl cron', $e->getMessage(), array(), WATCHDOG_ERROR);
      db_update('unl_sites_aliases')
        ->fields(array('installed' => 5))
        ->condition('site_alias_id', $row->site_alias_id)
        ->execute();
    }
  }
}

function unl_add_page_aliases() {
  $query = db_select('unl_page_aliases', 'a');
  $query->fields('a', array('page_alias_id', 'from_uri', 'to_uri'));
  $query->condition('a.installed', 0);
  $results = $query->execute()->fetchAll();

  foreach ($results as $row) {
    db_update('unl_page_aliases')
      ->fields(array('installed' => 1))
      ->condition('page_alias_id', $row->page_alias_id)
      ->execute();
    try {
      unl_add_page_alias($row->from_uri, $row->to_uri, $row->page_alias_id);
      db_update('unl_page_aliases')
        ->fields(array('installed' => 2))
        ->condition('page_alias_id', $row->page_alias_id)
        ->execute();
    } catch (Exception $e) {
      watchdog('unl cron', $e->getMessage(), array(), WATCHDOG_ERROR);
      db_update('unl_sites_aliases')
        ->fields(array('installed' => 5))
        ->condition('site_alias_id', $row->page_alias_id)
        ->execute();
    }
  }
}

function unl_remove_page_aliases() {
  $query = db_select('unl_page_aliases', 'a');
  $query->fields('a', array('page_alias_id'));
  $query->condition('a.installed', 3);
  $results = $query->execute()->fetchAll();

  foreach ($results as $row) {
    db_update('unl_page_aliases')
      ->fields(array('installed' => 4))
      ->condition('page_alias_id', $row->page_alias_id)
      ->execute();
    try {
      unl_remove_page_alias($row->page_alias_id);
      db_delete('unl_page_aliases')
        ->condition('page_alias_id', $row->page_alias_id)
        ->execute();
    } catch (Exception $e) {
      watchdog('unl cron', $e->getMessage(), array(), WATCHDOG_ERROR);
      db_update('unl_page_aliases')
        ->fields(array('installed' => 5))
        ->condition('page_alias_id', $row->page_alias_id)
        ->execute();
    }
  }
}

function unl_add_site($site_path, $uri, $clean_url, $db_prefix, $site_id) {
  if (substr($site_path, 0, 1) == '/') {
    $site_path = substr($site_path, 1);
  }
  if (substr($site_path, -1) == '/') {
    $site_path = substr($site_path, 0, -1);
  }

  $sites_subdir = unl_get_sites_subdir($uri);

  $database = $GLOBALS['databases']['default']['default'];
  $db_url = $database['driver']
          . '://' . $database['username']
          . ':'   . $database['password']
          . '@'   . $database['host']
          . ($database['port'] ? ':' . $database['port'] : '')
          . '/'   . $database['database']
          ;
  $db_prefix .= '_' . $database['prefix'];

  $php_path = escapeshellarg($_SERVER['_']);
  $drupal_root = escapeshellarg(DRUPAL_ROOT);
  $uri = escapeshellarg($uri);
  $sites_subdir = escapeshellarg($sites_subdir);
  $db_url = escapeshellarg($db_url);
  $db_prefix = escapeshellarg($db_prefix);
  $site_mail = escapeshellarg('noreply@unl.edu');

  $command = "$php_path sites/all/modules/drush/drush.php -y --uri=$uri site-install unl_profile --sites-subdir=$sites_subdir --db-url=$db_url --db-prefix=$db_prefix --clean-url=$clean_url 2>&1 --site-mail=$site_mail";

  $result = shell_exec($command);
  echo $result;
  if (stripos($result, 'Drush command terminated abnormally due to an unrecoverable error.') !== FALSE) {
    throw new Exception('Error while running drush site-install.');
  }

  unl_add_site_to_htaccess($site_id, $site_path, FALSE);
}

function unl_remove_site($site_path, $uri, $db_prefix, $site_id) {
  // Grab the list of tables we need to drop.
  $schema = drupal_get_schema(NULL, TRUE);
  $tables = array_keys($schema);
  sort($tables);

  $database = $GLOBALS['databases']['default']['default'];
  $db_prefix .= '_' . $database['prefix'];


  $sites_subdir = unl_get_sites_subdir($uri);
  $sites_subdir = DRUPAL_ROOT . '/sites/' . $sites_subdir;
  $sites_subdir = realpath($sites_subdir);

  // A couple checks to make sure we aren't deleting something we shouldn't be.
  if (substr($sites_subdir, 0, strlen(DRUPAL_ROOT . '/sites/')) != DRUPAL_ROOT . '/sites/') {
    throw new Exception('Attempt to delete a directory outside DRUPAL_ROOT was aborted.');
  }

  if (strlen($sites_subdir) <= strlen(DRUPAL_ROOT . '/sites/')) {
    throw new Exception('Attempt to delete a directory outside DRUPAL_ROOT was aborted.');
  }

  // Drop the site's tables
  foreach ($tables as $table) {
    $table = $db_prefix . $table;
    try {
      db_query("DROP TABLE $table");
    } catch (PDOException $e) {
      // probably already gone?
    }
  }

  // Do our best to remove the sites
  shell_exec('chmod -R u+w ' . escapeshellarg($sites_subdir));
  shell_exec('rm -rf ' . escapeshellarg($sites_subdir));

  // Remove the rewrite rules from .htaccess for this site.
  unl_remove_site_from_htaccess($site_id, FALSE);

  // If we were using memcache, flush its cache so new sites don't have stale data.
  if (class_exists('MemCacheDrupal', FALSE)) {
    dmemcache_flush();
  }
}

function unl_add_alias($site_uri, $base_uri, $path, $alias_id) {
  $alias_uri = $base_uri . $path;
  $real_config_dir = unl_get_sites_subdir($site_uri);
  $alias_config_dir = unl_get_sites_subdir($alias_uri, FALSE);

  unl_add_alias_to_sites_php($alias_config_dir, $real_config_dir, $alias_id);
  if ($path) {
    unl_add_site_to_htaccess($alias_id, $path, TRUE);
  }
}

function unl_remove_alias($base_uri, $path, $alias_id) {
  $alias_uri = $base_uri . $path;
  $alias_config_dir = unl_get_sites_subdir($alias_uri, FALSE);
  /* TODO: Remove the next line once all sites have been converted
   *       to the new method of creating aliases.
   */
  unlink(DRUPAL_ROOT . '/sites/' . $alias_config_dir);

  unl_remove_alias_from_sites_php($alias_id);
  unl_remove_site_from_htaccess($alias_id, TRUE);
}

function unl_add_page_alias($from_uri, $to_uri, $alias_id) {
  $host = parse_url($from_uri, PHP_URL_HOST);
  $path = parse_url($from_uri, PHP_URL_PATH);

  unl_add_page_alias_to_htaccess($alias_id, $host, $path, $to_uri);
}

function unl_remove_page_alias($alias_id) {
  unl_remove_page_alias_from_htaccess($alias_id);
}

function unl_add_site_to_htaccess($site_id, $site_path, $is_alias) {
  if ($is_alias) {
    $site_or_alias = 'ALIAS';
  }
  else {
    $site_or_alias = 'SITE';
  }

  if (substr($site_path, -1) != '/') {
    $site_path .= '/';
  }

  unl_require_writable(DRUPAL_ROOT . '/.htaccess');

  $stub_token = '  # %UNL_CREATION_TOOL_STUB%';
  $htaccess = file_get_contents(DRUPAL_ROOT . '/.htaccess');
  $stub_pos = strpos($htaccess, $stub_token);
  if ($stub_pos === FALSE) {
    throw new Exception('Unable to find stub site entry in .htaccess.');
  }
  $new_htaccess = substr($htaccess, 0, $stub_pos)
                . "  # %UNL_START_{$site_or_alias}_ID_{$site_id}%\n"
                . "  RewriteRule {$site_path}misc/(.*) misc/$1\n"
                . "  RewriteRule {$site_path}modules/(.*) modules/$1\n"
                . "  RewriteRule {$site_path}sites/(.*) sites/$1 [DPI]\n"
                . "  RewriteRule {$site_path}themes/(.*) themes/$1\n"
                . "  # %UNL_END_{$site_or_alias}_ID_{$site_id}%\n\n"
                . $stub_token
                . substr($htaccess, $stub_pos + strlen($stub_token));

  _unl_file_put_contents_atomic(DRUPAL_ROOT . '/.htaccess', $new_htaccess);
}

function unl_remove_site_from_htaccess($site_id, $is_alias) {
  if ($is_alias) {
    $site_or_alias = 'ALIAS';
  }
  else {
    $site_or_alias = 'SITE';
  }

  unl_require_writable(DRUPAL_ROOT . '/.htaccess');

  $htaccess = file_get_contents(DRUPAL_ROOT . '/.htaccess');
  $site_start_token = "\n  # %UNL_START_{$site_or_alias}_ID_{$site_id}%";
  $site_end_token = "  # %UNL_END_{$site_or_alias}_ID_{$site_id}%\n";

  $start_pos = strpos($htaccess, $site_start_token);
  $end_pos = strpos($htaccess, $site_end_token);

  // If its already gone, we don't need to do anything.
  if ($start_pos === FALSE || $end_pos === FALSE) {
    return;
  }
  $new_htaccess = substr($htaccess, 0, $start_pos)
                . substr($htaccess, $end_pos + strlen($site_end_token))
                ;
  _unl_file_put_contents_atomic(DRUPAL_ROOT . '/.htaccess', $new_htaccess);
}

function unl_add_page_alias_to_htaccess($site_id, $host, $path, $to_uri) {
  unl_require_writable(DRUPAL_ROOT . '/.htaccess');

  $stub_token = '  # %UNL_CREATION_TOOL_STUB%';
  $htaccess = file_get_contents(DRUPAL_ROOT . '/.htaccess');
  $stub_pos = strpos($htaccess, $stub_token);
  if ($stub_pos === FALSE) {
    throw new Exception('Unable to find stub page alias entry in .htaccess.');
  }
  $new_htaccess = substr($htaccess, 0, $stub_pos)
                . "  # %UNL_START_PAGE_ALIAS_ID_{$site_id}%\n"
                . "  RewriteCond %{HTTP_HOST} ^{$host}$\n"
                . "  RewriteCond %{REQUEST_URI} ^{$path}$\n"
                . "  RewriteRule (.*) {$to_uri} [R,L]\n"
                . "  # %UNL_END_PAGE_ALIAS_ID_{$site_id}%\n\n"
                . $stub_token
                . substr($htaccess, $stub_pos + strlen($stub_token));

  _unl_file_put_contents_atomic(DRUPAL_ROOT . '/.htaccess', $new_htaccess);
}

function unl_remove_page_alias_from_htaccess($site_id) {
  unl_require_writable(DRUPAL_ROOT . '/.htaccess');

  $htaccess = file_get_contents(DRUPAL_ROOT . '/.htaccess');
  $site_start_token = "\n  # %UNL_START_PAGE_ALIAS_ID_{$site_id}%";
  $site_end_token = "  # %UNL_END_PAGE_ALIAS_ID_{$site_id}%\n";

  $start_pos = strpos($htaccess, $site_start_token);
  $end_pos = strpos($htaccess, $site_end_token);

  // If its already gone, we don't need to do anything.
  if ($start_pos === FALSE || $end_pos === FALSE) {
    return;
  }
  $new_htaccess = substr($htaccess, 0, $start_pos)
                . substr($htaccess, $end_pos + strlen($site_end_token))
                ;
  _unl_file_put_contents_atomic(DRUPAL_ROOT . '/.htaccess', $new_htaccess);
}

function unl_add_alias_to_sites_php($alias_site_dir, $real_site_dir, $alias_id) {
  unl_require_writable(DRUPAL_ROOT . '/sites/sites.php');

  $stub_token = '# %UNL_CREATION_TOOL_STUB%';
  $sites_php = file_get_contents(DRUPAL_ROOT . '/sites/sites.php');
  $stub_pos = strpos($sites_php, $stub_token);
  if ($stub_pos === FALSE) {
    throw new Exception('Unable to find stub alias entry in sites.php.');
  }
  $new_sites_php = substr($sites_php, 0, $stub_pos)
                 . "# %UNL_START_ALIAS_ID_{$alias_id}%\n"
                 . "\$sites['$alias_site_dir'] = '$real_site_dir';\n"
                 . "# %UNL_END_ALIAS_ID_{$alias_id}%\n\n"
                 . $stub_token
                 . substr($sites_php, $stub_pos + strlen($stub_token))
                 ;
  _unl_file_put_contents_atomic(DRUPAL_ROOT . '/sites/sites.php', $new_sites_php);
}

function unl_remove_alias_from_sites_php($alias_id) {
  unl_require_writable(DRUPAL_ROOT . '/sites/sites.php');

  $sites_php = file_get_contents(DRUPAL_ROOT . '/sites/sites.php');
  $site_start_token = "\n# %UNL_START_ALIAS_ID_{$alias_id}%";
  $site_end_token = "# %UNL_END_ALIAS_ID_{$alias_id}%\n";

  $start_pos = strpos($sites_php, $site_start_token);
  $end_pos = strpos($sites_php, $site_end_token);

  // If its already gone, we don't need to do anything.
  if ($start_pos === FALSE || $end_pos === FALSE) {
    return;
  }
  $new_sites_php = substr($sites_php, 0, $start_pos)
                 . substr($sites_php, $end_pos + strlen($site_end_token))
                 ;
  _unl_file_put_contents_atomic(DRUPAL_ROOT . '/sites/sites.php', $new_sites_php);
}

function unl_require_writable($path) {
  if (!is_writable($path)) {
    throw new Exception('The file "' . $path . '" needs to be writable and is not.');
  }
}

/**
 * A drop-in replacement for file_put_contents that will atomically put the new file into place.
 * This additionally requires you to have write access to the directory that will contain the file.
 * @see file_put_contents
 */
function _unl_file_put_contents_atomic($filename, $data, $flags = 0, $context = NULL) {
  // Create a temporary file with a simalar name in the destination directory.
  $tempfile = tempnam(dirname($filename), basename($filename) . '_');
  if ($tempfile === FALSE) {
    return FALSE;
  }
  // Fix the permissions on the file since they will be 0600.
  if (file_exists($filename)) {
    $stat = stat($filename);
    chmod($tempfile, $stat['mode']);
  } else {
    chmod($tempfile, 0666 & ~umask());
  }

  // Do the actual file_put contents
  $bytes = file_put_contents($tempfile, $data, $flags, $context);
  if ($bytes === FALSE) {
    unlink($tempfile);
    return FALSE;
  }

  // Move the new file into place atomically.
  if (!rename($tempfile, $filename)) {
    unlink($tempfile);
    return FALSE;
  }

  return $bytes;
}
