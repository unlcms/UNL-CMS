<?php

if (PHP_SAPI != 'cli') {
  echo 'This script must be run from the shell!';
  exit;
}

chdir(dirname(__FILE__) . '/../../../..');
define('DRUPAL_ROOT', getcwd());

require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
drupal_override_server_variables();
drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);

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
    if (unl_add_site($row['site_path'], $row['uri'], $row['clean_url'], $row['db_prefix'], $row['site_id'])) {
      db_update('unl_sites')
        ->fields(array('installed' => 2))
        ->condition('site_id', $row['site_id'])
        ->execute();
    }
    else {
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
    if (unl_remove_site($row['site_path'], $row['uri'], $row['db_prefix'], $row['site_id'])) {
      db_delete('unl_sites')
        ->condition('site_id', $row['site_id'])
        ->execute();
    }
    else {
      db_update('unl_sites')
        ->fields(array('installed' => 5))
        ->condition('site_id', $row['site_id'])
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
    if (unl_add_alias($row->uri, $row->base_uri, $row->path, $row->site_alias_id)) {
      db_update('unl_sites_aliases')
        ->fields(array('installed' => 2))
        ->condition('site_alias_id', $row->site_alias_id)
        ->execute();
    }
    else {
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
    if (unl_remove_alias($row->base_uri, $row->path, $row->site_alias_id)) {
      db_delete('unl_sites_aliases')
        ->condition('site_alias_id', $row->site_alias_id)
        ->execute();
    }
    else {
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
    if (unl_add_page_alias($row->from_uri, $row->to_uri, $row->page_alias_id)) {
      db_update('unl_page_aliases')
        ->fields(array('installed' => 2))
        ->condition('page_alias_id', $row->page_alias_id)
        ->execute();
    }
    else {
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
    if (unl_remove_page_alias($row->page_alias_id)) {
      db_delete('unl_page_aliases')
        ->condition('page_alias_id', $row->page_alias_id)
        ->execute();
    }
    else {
      db_update('unl_page_aliases')
        ->fields(array('installed' => 5))
        ->condition('page_alias_id', $row->page_alias_id)
        ->execute();
    }
  }
}


function _unl_get_sites_subdir($uri, $trim_subdomain = TRUE) {
  $path_parts = parse_url($uri);
  if ($trim_subdomain && substr($path_parts['host'], -7) == 'unl.edu') {
    $path_parts['host'] = 'unl.edu';
  }
  $sites_subdir = $path_parts['host'] . $path_parts['path'];
  $sites_subdir = strtr($sites_subdir, array('/' => '.'));

  while (substr($sites_subdir, 0, 1) == '.') {
    $sites_subdir = substr($sites_subdir, 1);
  }
  while (substr($sites_subdir, -1) == '.') {
    $sites_subdir = substr($sites_subdir, 0, -1);
  }
  
  return $sites_subdir;
}


function unl_add_site($site_path, $uri, $clean_url, $db_prefix, $site_id) {
  if (substr($site_path, 0, 1) == '/') {
    $site_path = substr($site_path, 1);
  }
  if (substr($site_path, -1) == '/') {
    $site_path = substr($site_path, 0, -1);
  }
  
  $sites_subdir = _unl_get_sites_subdir($uri);
  
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
  $site_mail    = escapeshellarg(variable_get('site_mail'));
  
  $command = "$php_path sites/all/modules/drush/drush.php -y --uri=$uri site-install unl_profile --sites-subdir=$sites_subdir --db-url=$db_url --db-prefix=$db_prefix --clean-url=$clean_url";
  if ($site_mail) {
    $command .= " --site-mail=$site_mail"; 
  }
  shell_exec($command);
  
  unl_add_site_to_htaccess($site_id, $site_path, FALSE);
  
  return TRUE;
}

function unl_remove_site($site_path, $uri, $db_prefix, $site_id) {
  $schema = drupal_get_schema();
  $tables = array_keys($schema);
  sort($tables);
    
  $database = $GLOBALS['databases']['default']['default'];
  $db_prefix .= '_' . $database['prefix'];
  
  
  $sites_subdir = _unl_get_sites_subdir($uri);
  $sites_subdir = DRUPAL_ROOT . '/sites/' . $sites_subdir;
  $sites_subdir = realpath($sites_subdir);
  
  // A couple checks to make sure we aren't deleting something we shouldn't be.
  if (substr($sites_subdir, 0, strlen(DRUPAL_ROOT . '/sites/')) != DRUPAL_ROOT . '/sites/') {
    return FALSE;
  }
  
  if (strlen($sites_subdir) <= strlen(DRUPAL_ROOT . '/sites/')) {
    return FALSE;
  }

  foreach ($tables as $table) {
    $table = $db_prefix . $table;
    try {
      db_query("DROP TABLE $table");
    } catch (PDOException $e) {
      // probably already gone?
    }
  }
  
  shell_exec('chmod -R u+w ' . escapeshellarg($sites_subdir));
  shell_exec('rm -rf ' . escapeshellarg($sites_subdir));
  
  // Remove the rewrite rules from .htaccess for this site.
  $htaccess = file_get_contents(DRUPAL_ROOT . '/.htaccess');
  $site_start_token = "\n  # %UNL_START_SITE_ID_$site_id%";
  $site_end_token = "  # %UNL_END_SITE_ID_$site_id%\n";
  
  $start_pos = strpos($htaccess, $site_start_token);
  $end_pos = strpos($htaccess, $site_end_token);
  
  if ($start_pos === FALSE || $end_pos === FALSE) {
    return FALSE;
  }
  $new_htaccess = substr($htaccess, 0, $start_pos)
                . substr($htaccess, $end_pos + strlen($site_end_token))
                ;
  file_put_contents(DRUPAL_ROOT . '/.htaccess', $new_htaccess);
  
  unl_remove_site_from_htaccess($site_id, FALSE);
  
  return TRUE;
}

function unl_add_alias($site_uri, $base_uri, $path, $alias_id) {
  $alias_uri = $base_uri . $path;
  $real_config_dir = _unl_get_sites_subdir($site_uri);
  $alias_config_dir = _unl_get_sites_subdir($alias_uri, FALSE);
  $result = symlink($real_config_dir, DRUPAL_ROOT . '/sites/' . $alias_config_dir);
  
  if ($path) {
    unl_add_site_to_htaccess($alias_id, $path, TRUE);
  }
  
  return TRUE;
}

function unl_remove_alias($base_uri, $path, $alias_id) {
  $alias_uri = $base_uri . $path;
  $alias_config_dir = _unl_get_sites_subdir($alias_uri, FALSE);
  unlink(DRUPAL_ROOT . '/sites/' . $alias_config_dir);
  
  unl_remove_site_from_htaccess($alias_id, TRUE);
  
  return TRUE;
}

function unl_add_page_alias($from_uri, $to_uri, $alias_id) {
  $host = parse_url($from_uri, PHP_URL_HOST);
  $path = parse_url($from_uri, PHP_URL_PATH);
  
  unl_add_page_alias_to_htaccess($alias_id, $host, $path, $to_uri);
  
  return TRUE;
}

function unl_remove_page_alias($alias_id) {
  unl_remove_page_alias_from_htaccess($alias_id);
  
  return TRUE;
}

function unl_add_site_to_htaccess($site_id, $site_path, $is_alias) {
  if ($is_alias) {
    $site_or_alias = 'ALIAS';
  }
  else {
    $site_or_alias = 'SITE';
  }
  
  $stub_token = '  # %UNL_CREATION_TOOL_STUB%';
  $htaccess = file_get_contents(DRUPAL_ROOT . '/.htaccess');
  $stub_pos = strpos($htaccess, $stub_token);
  if ($stub_pos === FALSE) {
    return FALSE;
  }
  $new_htaccess = substr($htaccess, 0, $stub_pos)
                . "  # %UNL_START_{$site_or_alias}_ID_{$site_id}%\n";
  foreach (array('misc', 'modules', 'sites', 'themes') as $drupal_dir) {
    $new_htaccess .=  "  RewriteRule $site_path/$drupal_dir/(.*) $drupal_dir/$1\n";
  }
  $new_htaccess .= "  # %UNL_END_{$site_or_alias}_ID_{$site_id}%\n\n" 
                 . $stub_token
                 . substr($htaccess, $stub_pos + strlen($stub_token));
  
  file_put_contents(DRUPAL_ROOT . '/.htaccess', $new_htaccess);
}

function unl_remove_site_from_htaccess($site_id, $is_alias) {
  if ($is_alias) {
    $site_or_alias = 'ALIAS';
  }
  else {
    $site_or_alias = 'SITE';
  }
  
  $htaccess = file_get_contents(DRUPAL_ROOT . '/.htaccess');
  $site_start_token = "\n  # %UNL_START_{$site_or_alias}_ID_{$site_id}%";
  $site_end_token = "  # %UNL_END_{$site_or_alias}_ID_{$site_id}%\n";
  
  $start_pos = strpos($htaccess, $site_start_token);
  $end_pos = strpos($htaccess, $site_end_token);
  
  if ($start_pos === FALSE || $end_pos === FALSE) {
    return FALSE;
  }
  $new_htaccess = substr($htaccess, 0, $start_pos)
                . substr($htaccess, $end_pos + strlen($site_end_token))
                ;
  file_put_contents(DRUPAL_ROOT . '/.htaccess', $new_htaccess);
}

function unl_add_page_alias_to_htaccess($site_id, $host, $path, $to_uri) {
  $stub_token = '  # %UNL_CREATION_TOOL_STUB%';
  $htaccess = file_get_contents(DRUPAL_ROOT . '/.htaccess');
  $stub_pos = strpos($htaccess, $stub_token);
  if ($stub_pos === FALSE) {
    return FALSE;
  }
  $new_htaccess = substr($htaccess, 0, $stub_pos)
                . "  # %UNL_START_PAGE_ALIAS_ID_{$site_id}%\n"
                . "  RewriteCond %{HTTP_HOST} ^{$host}$\n"
                . "  RewriteCond %{REQUEST_URI} ^{$path}$\n"
                . "  RewriteRule (.*) {$to_uri} [R,L]\n"
  				. "  # %UNL_END_PAGE_ALIAS_ID_{$site_id}%\n\n" 
                . $stub_token
                . substr($htaccess, $stub_pos + strlen($stub_token));
  
  file_put_contents(DRUPAL_ROOT . '/.htaccess', $new_htaccess);
}

function unl_remove_page_alias_from_htaccess($site_id) {
  $htaccess = file_get_contents(DRUPAL_ROOT . '/.htaccess');
  $site_start_token = "\n  # %UNL_START_PAGE_ALIAS_ID_{$site_id}%";
  $site_end_token = "  # %UNL_END_PAGE_ALIAS_ID_{$site_id}%\n";
  
  $start_pos = strpos($htaccess, $site_start_token);
  $end_pos = strpos($htaccess, $site_end_token);
  
  if ($start_pos === FALSE || $end_pos === FALSE) {
    return FALSE;
  }
  $new_htaccess = substr($htaccess, 0, $start_pos)
                . substr($htaccess, $end_pos + strlen($site_end_token))
                ;
  file_put_contents(DRUPAL_ROOT . '/.htaccess', $new_htaccess);
}












