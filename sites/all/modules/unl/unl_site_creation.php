<?php
/**
 * Contains the callback functions for the pages/forms located at
 * admin/sites/unl/% as specified in unl_menu().
 */

require_once DRUPAL_ROOT . '/includes/install.core.inc';

/**
 * Page callback for admin/sites/unl
 */
function unl_sites_page() {
  $page = array();
  $page[] = drupal_get_form('unl_site_create');
  $page[] = drupal_get_form('unl_site_list');
  $page[] = drupal_get_form('unl_site_updates');
  $page[] = drupal_get_form('unl_site_email_settings');

  return $page;
}

function unl_site_create($form, &$form_state) {
  $form['root'] = array(
    '#type' => 'fieldset',
    '#title' => t('Create New Site'),
  );
  $form['root']['site_path'] = array(
    '#type' => 'textfield',
    '#title' => t('New site path'),
    '#description' => t('Relative url for the new site'),
    '#default_value' => t('newsite'),
    '#required' => TRUE,
  );
  $form['root']['clean_url'] = array(
    '#type' => 'checkbox',
    '#title' => t('Use clean URLs'),
    '#description' => t('Unless you have some reason to think your site won\'t support this, leave it checked.'),
    '#default_value' => 1,
  );
  $form['root']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Create Site'),
  );

  return $form;
}

function unl_site_create_validate($form, &$form_state) {
  $site_path = trim($form_state['values']['site_path']);

  if (substr($site_path, 0, 1) == '/') {
    $site_path = substr($site_path, 1);
  }
  if (substr($site_path, -1) != '/') {
    $site_path .= '/';
  }

  $site_path_parts = explode('/', $site_path);
  $first_directory = array_shift($site_path_parts);
  if (in_array($first_directory, array('includes', 'misc', 'modules', 'profiles', 'scripts', 'sites', 'themes'))) {
    form_set_error('site_path', t('Drupal site paths must not start with the "' . $first_directory . '" directory.'));
  }

  $form_state['values']['site_path'] = $site_path;
}

function unl_site_create_submit($form, &$form_state) {
  $site_path = $form_state['values']['site_path'];
  $clean_url = $form_state['values']['clean_url'];

  $db_prefix = unl_create_db_prefix($site_path);

  $site_path = explode('/', $site_path);
  foreach (array_keys($site_path) as $i) {
    $site_path[$i] = unl_sanitize_url_part($site_path[$i]);
  }
  $site_path = implode('/', $site_path);
  $uri = url($site_path, array('absolute' => TRUE, 'https' => FALSE));

  $clean_url = intval($clean_url);

  db_insert('unl_sites')->fields(array(
    'site_path' => $site_path,
    'uri' => $uri,
    'clean_url' => $clean_url,
    'db_prefix' => $db_prefix
  ))->execute();

  drupal_set_message(t('The site ' . $uri . ' has been started, run unl/cron.php to finish setup.'));
  $form_state['redirect'] = 'admin/sites/unl/add';
  return;
}

/**
 * Site List appears on admin/sites/unl, admin/sites/unl/sites
 */
function unl_site_list($form, &$form_state) {
  // Get all the custom made roles
  $roles = user_roles(TRUE);
  unset($roles[DRUPAL_AUTHENTICATED_RID]);
  unset($roles[variable_get('user_admin_role')]);
  $roles_in = implode(',', array_keys($roles)); //For use in the DB query

  // Setup alternate db connection so we can query other sites' tables without a prefix being attached
  global $databases;
  $database_noprefix = array(
    'database' => $databases['default']['default']['database'],
    'username' => $databases['default']['default']['username'],
    'password' => $databases['default']['default']['password'],
    'host' => $databases['default']['default']['host'],
    'port' => $databases['default']['default']['port'],
    'driver' => $databases['default']['default']['driver'],
  );
  Database::addConnectionInfo('UNLNoPrefix', 'default', $database_noprefix);
  db_set_active('UNLNoPrefix');

  $header = array(
    'uri' => array(
      'data' => t('Default Path'),
      'field' => 's.uri',
    ),
    'site_name' => t('Site Name'),
    'last_access' => t('Last Access'),
    'installed' => array(
      'data' => t('Status'),
      'field' => 's.installed',
    ),
    'operations' => t('Operations'),
  );

  // The master prefix that was specified during initial drupal install
  $master_prefix = $databases['default']['default']['prefix'];

  // Get all the db prefixes for every UNL site sorted by uri
  $prefixes = db_query('SELECT db_prefix FROM ' . $master_prefix . 'unl_sites ORDER BY uri')->fetchCol();
  if (isset($_GET['sort']) && $_GET['sort'] == 'desc' && (!isset($_GET['order']) || (isset($_GET['order']) && $_GET['order'] !== 'Status'))) {
    $prefixes = array_reverse($prefixes);
  }

  // Get a portion of the prefixes based on the page
  $prefixes = new ArrayIterator($prefixes);
  $count = 50;
  $offset = (isset($_GET['page']) ? (int)$_GET['page'] : 0) * $count;
  $prefixIterator = new LimitIterator($prefixes, $offset, $count);

  // Prepare the CASE expression for the following query
  $case_expression = "CASE";
  foreach ($prefixIterator as $key => $prefix) {
    if (db_table_exists($prefix.'_'.$master_prefix.'variable')) {
      $case_expression .= " WHEN {u}.db_prefix = '".$prefix."' THEN p".$key.".value";
    }
  }
  $case_expression .= " ELSE '* Scheduled for creation (or Error)' END";

  // Combines the site_id from the unl_sites table with the corresponding site_name from [unl_sites.db_prefix]_variable table
  $subquery = db_select($master_prefix.'unl_sites', 'u');
  $subquery->addField('u', 'site_id', 'site_id');
  $subquery->addExpression($case_expression, 'site_name');
  foreach ($prefixIterator as $key => $prefix) {
    if (db_table_exists($prefix.'_'.$master_prefix.'variable')) {
      $subquery->leftJoin($prefix.'_'.$master_prefix.'variable', 'p'.$key, 'p'.$key.'.name = :site_name', array('site_name'=>'site_name'));
    }
  }

  // The query that will be displayed - uses the subquery above in a JOIN
  $sites = db_select($master_prefix.'unl_sites', 's');
  $sites->join($subquery, 'i', 'i.site_id=s.site_id');
  $sites = $sites
    ->fields('s', array('site_id', 'db_prefix', 'installed', 'site_path', 'uri'))
    ->fields('i', array('site_name'))
    ->extend('TableSort')->extend('PagerDefault')->limit($count)
    ->orderByHeader($header)
    ->execute()
    ->fetchAll();

  // Generate an array of Last Access timestamps for each site based on time last accessed by someone in a non-admin role
  foreach ($sites as $site) {
    if (db_table_exists($site->db_prefix.'_'.$master_prefix.'users') &&
        db_table_exists($site->db_prefix.'_'.$master_prefix.'users_roles') &&
        !empty($roles_in)) {
      $query = 'SELECT u.access FROM '.$site->db_prefix.'_'.$master_prefix.'users u, '.$site->db_prefix.'_'.$master_prefix.'users_roles r WHERE u.uid = r.uid AND u.access > 0 AND r.rid IN ('.$roles_in.') ORDER BY u.access DESC';
      $last_access[$site->site_id] = db_query($query)->fetchColumn();
    }
  }

  // Restore default db connection
  db_set_active();

  // Setup the form
  foreach ($sites as $site) {
    $options[$site->site_id] = array(
      'uri' => theme('unl_site_details', array('site_path' => $site->site_path, 'uri' => $site->uri, 'db_prefix' => $site->db_prefix)),
      'site_name' => @unserialize($site->site_name),
      'last_access' => (isset($last_access[$site->site_id]) && $last_access[$site->site_id]) ? t('@time ago', array('@time' => format_interval(REQUEST_TIME - $last_access[$site->site_id]))) : t('never'),
      'installed' => _unl_get_install_status_text($site->installed),
      'operations' => array(
        'data' => array(
          '#theme' => 'links__node_operations',
          '#links' => array(
            'delete' => array(
              'title' => t('delete'),
              'href' => 'admin/sites/unl/' . $site->site_id . '/delete',
              'query' => drupal_get_destination(),
            ),
          ),
          '#attributes' => array('class' => array('links', 'inline')),
        ),
      ),
    );
  }

  $form['unl_sites'] = array(
    '#type' => 'fieldset',
    '#title' => t('Existing Sites: ') . count($prefixes),
  );
  $form['unl_sites']['site_list'] = array(
    '#theme' => 'table',
    '#header' => $header,
    '#rows' => $options,
    '#empty' => t('No sites available.'),
  );
  $form['unl_sites']['pager'] = array('#markup' => theme('pager', array('tags' => NULL)));
  return $form;
}

/**
 * Implements theme_CUSTOM() which works with unl_theme()
 */
function theme_unl_site_details($variables) {
  $output = '<div><a href="' . $variables['uri'] . '">' . $variables['site_path'] . '</a></div>';
  $output .= '<div style="display:none;">Database Prefix: ' . $variables['db_prefix'] . '_' . $GLOBALS['databases']['default']['default']['prefix'] . '</div>';
  return $output;
}

/**
 * Form to confirm UNL site delete operation.
 */
function unl_site_delete_confirm($form, &$form_state, $site_id) {
  $form['site_id'] = array(
    '#type' => 'value',
    '#value' => $site_id,
  );

  $site_path = db_select('unl_sites', 's')
    ->fields('s', array('site_path'))
    ->condition('site_id', $site_id)
    ->execute()
    ->fetchCol();

  return confirm_form($form, t('Are you sure you want to delete the site %site_path ?', array('%site_path' => $site_path[0])), 'admin/sites/unl', t('This action cannot be undone. DOUBLE CHECK WHICH CMS INSTANCE YOU ARE ON!'), t('Delete Site'));
}

/**
 * Form submit handler for unl_site_delete_confirm().
 */
function unl_site_delete_confirm_submit($form, &$form_state) {
  if (!isset($form_state['values']['site_id'])) {
    return;
  }
  unl_site_remove($form_state['values']['site_id']);
  drupal_set_message(t('The site has been scheduled for removal.'));
  $form_state['redirect'] = 'admin/sites/unl';
}

function unl_site_updates($form, &$form_state) {
  $form['root'] = array(
    '#type' => 'fieldset',
    '#title' => t('Maintenance'),
    '#description' => t('Using drush, do database updates and clear the caches of all sites.'),
  );
  $form['root']['submit'] = array(
    '#type'  => 'submit',
    '#value' => t('Run Drush'),
  );

  return $form;
}

function unl_site_updates_submit($form, &$form_state) {
  $sites = db_select('unl_sites', 's')
    ->fields('s', array('site_id', 'db_prefix', 'installed', 'site_path', 'uri'))
    ->execute()
    ->fetchAll();

  $operations = array();

  foreach ($sites as $site) {
    $operations[] = array('unl_site_updates_step', array($site->uri));
  }

  $batch = array(
    'operations' => $operations,
    'file' => substr(__FILE__, strlen(DRUPAL_ROOT) + 1),
  );
  batch_set($batch);
}

function unl_site_updates_step($site_uri, &$context) {
  $uri = escapeshellarg($site_uri);
  $root = escapeshellarg(DRUPAL_ROOT);
  $output = '';
  $command = "sites/all/modules/drush/drush.php -y --token=secret --root={$root} --uri={$uri} updatedb 2>&1";
  $output .= shell_exec($command);
  $command = "sites/all/modules/drush/drush.php -y --root={$root} --uri={$uri} cc all 2>&1";
  $output .= shell_exec($command);

  drupal_set_message('Messages from ' . $site_uri . ':<br />' . PHP_EOL . '<pre>' . $output . '</pre>', 'status');
}

function unl_site_email_settings($form, &$form_state) {
  $form['root'] = array(
    '#type' => 'fieldset',
    '#title' => t('Email Alert Settings'),
    '#description' => t('When a new site is created, who should be emailed?'),
  );

  $form['root']['unl_site_created_email_address'] = array(
    '#type' => 'textfield',
    '#title' => t('Address for Notification'),
    '#description' => t('When a site has been been created and migrated, send an email to this address.'),
    '#default_value' => variable_get('unl_site_created_email_address'),
  );

  $form['root']['unl_site_created_alert_admins'] = array(
    '#type' => 'checkbox',
    '#title' => t('Email Site Admins'),
    '#description' => t('When a site has been created and migrated, send an email to the Site Admins.'),
    '#default_value' => variable_get('unl_site_created_alert_admins'),
  );

  $form['root']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Update Settings'),
  );

  return $form;
}

function unl_site_email_settings_submit($form, &$form_state) {
  variable_set('unl_site_created_email_address', $form_state['values']['unl_site_created_email_address']);
  variable_set('unl_site_created_alert_admins',  $form_state['values']['unl_site_created_alert_admins']);
}

function unl_site_remove($site_id) {
  $uri = db_select('unl_sites', 's')
    ->fields('s', array('uri'))
    ->condition('site_id', $site_id)
    ->execute()
    ->fetchCol();

  if (!isset($uri[0])) {
    form_set_error(NULL, t('Unfortunately, the site could not be removed.'));
    return;
  }
  $uri = $uri[0];

  $sites_subdir = unl_get_sites_subdir($uri);
  $sites_subdir = DRUPAL_ROOT . '/sites/' . $sites_subdir;
  $sites_subdir = realpath($sites_subdir);

  // A couple checks to make sure we aren't deleting something we shouldn't be.
  if (substr($sites_subdir, 0, strlen(DRUPAL_ROOT . '/sites/')) != DRUPAL_ROOT . '/sites/') {
    form_set_error(NULL, t('Unfortunately, the site could not be removed.'));
    return;
  }

  if (strlen($sites_subdir) <= strlen(DRUPAL_ROOT . '/sites/')) {
    form_set_error(NULL, t('Unfortunately, the site could not be removed.'));
    return;
  }

  shell_exec('rm -rf ' . escapeshellarg($sites_subdir));

  db_update('unl_sites')
    ->fields(array('installed' => 3))
    ->condition('site_id', $site_id)
    ->execute();
  db_update('unl_sites_aliases')
    ->fields(array('installed' => 3))
    ->condition('site_id', $site_id)
    ->execute();

  return TRUE;
}

/**
 * Page callback for admin/sites/unl/aliases
 */
function unl_aliases_page() {
  $page = array();
  $page[] = drupal_get_form('unl_site_alias_create');
  $page[] = drupal_get_form('unl_site_alias_list');
  $page[] = drupal_get_form('unl_page_alias_create');
  $page[] = drupal_get_form('unl_page_alias_list');

  return $page;
}

function unl_site_alias_create($form, &$form_state) {
  $sites = db_select('unl_sites', 's')
    ->fields('s', array('site_id', 'uri'))
    ->orderBy('uri')
    ->execute()
    ->fetchAll();
  foreach ($sites as $site) {
    $site_list[$site->site_id] = $site->uri;
  }

  $form['root'] = array(
    '#type' => 'fieldset',
    '#title' => t('Create New Site Alias'),
  );

  $form['root']['site'] = array(
    '#type' => 'select',
    '#title' => t('Aliased Site'),
    '#description' => t('The site the alias will point to.'),
    '#options' => $site_list,
    '#required' => TRUE,
  );

  $form['root']['base_uri'] = array(
    '#type' => 'textfield',
    '#title' => t('Alias Base URL'),
    '#description' => t('The base URL for the new alias. (This should resolve to the directory containing drupal\'s .htaccess file'),
    '#default_value' => url('', array('https' => FALSE)),
    '#required' => TRUE,
  );

  $form['root']['path'] = array(
    '#type' => 'textfield',
    '#title' => t('Path'),
    '#description' => t('Path for the new alias.'),
  );

  $form['root']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Create Alias'),
  );

  return $form;
}

function unl_site_alias_create_validate($form, &$form_state) {
  $form_state['values']['base_uri'] = trim($form_state['values']['base_uri']);
  $form_state['values']['path'] = trim($form_state['values']['path']);

  if (substr($form_state['values']['base_uri'], -1) != '/') {
    $form_state['values']['base_uri'] .= '/';
  }

  if (substr($form_state['values']['path'], -1) != '/') {
    $form_state['values']['path'] .= '/';
  }

  if (substr($form_state['values']['path'], 0, 1) == '/') {
    $form_state['values']['path'] = substr($form_state['values']['path'], 1);
  }
}

function unl_site_alias_create_submit($form, &$form_state) {
  db_insert('unl_sites_aliases')->fields(array(
    'site_id' => $form_state['values']['site'],
    'base_uri' => $form_state['values']['base_uri'],
    'path' => $form_state['values']['path'],
  ))->execute();
}

/**
 * Site Alias List appears on admin/sites/unl/aliases
 */
function unl_site_alias_list($form, &$form_state) {
  $header = array(
    'site_uri' => array(
      'data' => t('Site URI'),
      'field' => 's.uri',
    ),
    'alias_uri' => array(
      'data' => t('Alias URI'),
      'field' => 'a.path',
    ),
    'installed' => array(
      'data' => t('Status'),
      'field' => 'a.installed',
    ),
    'remove' => t('Remove (can not undo!)'),
  );

  $query = db_select('unl_sites_aliases', 'a')
    ->extend('TableSort')
    ->orderByHeader($header);
  $query->join('unl_sites', 's', 's.site_id = a.site_id');
  $query->fields('s', array('uri'));
  $query->fields('a', array('site_alias_id', 'base_uri', 'path', 'installed'));
  $sites = $query->execute()->fetchAll();

  foreach ($sites as $site) {
    $options[$site->site_alias_id] = array(
      'site_uri' => array('#prefix' => $site->uri),
      'alias_uri' => array('#prefix' => $site->base_uri . $site->path),
      'installed' => array('#prefix' => _unl_get_install_status_text($site->installed)),
      'remove' => array(
          '#type' => 'checkbox',
          '#parents' => array('aliases', $site->site_alias_id, 'remove'),
          '#default_value' => 0,
      ),
    );
  }

  $form['unl_site_aliases'] = array(
    '#type' => 'fieldset',
    '#title' => t('Existing Site Aliases'),
  );
  $form['unl_site_aliases']['alias_list'] = array(
    '#theme' => 'unl_table',
    '#header' => $header,
    'rows' => $options,
    '#empty' => t('No aliases available.'),
  );
  $form['unl_site_aliases']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Delete Selected Aliases'),
  );

  return $form;
}

function unl_site_alias_list_submit($form, &$form_state) {
  $site_alias_ids = array(-1);
  foreach ($form_state['values']['aliases'] as $site_alias_id => $alias) {
    if ($alias['remove']) {
      $site_alias_ids[] = $site_alias_id;
    }
  }

  $query = db_select('unl_sites_aliases', 'a');
  $query->join('unl_sites', 's', 'a.site_id = s.site_id');
  $data = $query
    ->fields('a', array('site_alias_id', 'base_uri', 'path'))
    ->fields('s', array('db_prefix'))
    ->condition('site_alias_id', $site_alias_ids, 'IN')
    ->execute()
    ->fetchAll();

  $site_alias_ids = array(-1);
  foreach ($data as $row) {
    $alias_url = $row->base_uri . $row->path;
    $primary_base_url = unl_site_variable_get($row->db_prefix, 'unl_primary_base_url', '');
    if ($primary_base_url == $alias_url) {
      drupal_set_message("Cannot delete the alias $alias_url.  It is currently the Primary Base URL for a site.", 'error');
      continue;
    }
    $site_alias_ids[] = $row->site_alias_id;
    drupal_set_message("The alias $alias_url was scheduled for removal.");
  }

  db_update('unl_sites_aliases')
    ->fields(array('installed' => 3))
    ->condition('site_alias_id', $site_alias_ids, 'IN')
    ->execute();
}

function unl_page_alias_create($form, &$form_state) {
  $form['root'] = array(
    '#type' => 'fieldset',
    '#title' => 'Create New Page Alias',
  );

  $form['root']['from_uri'] = array(
    '#type' => 'textfield',
    '#title' => t('From URL'),
    '#description' => t('The URL that users will visit.'),
    '#default_value' => url('', array('https' => FALSE)),
    '#required' => TRUE,
  );

  $form['root']['to_uri'] = array(
    '#type' => 'textfield',
    '#title' => t('To URL'),
    '#description' => t('The URL users will be redirected to.'),
    '#default_value' => url('', array('https' => FALSE)),
    '#required' => TRUE,
  );

  $form['root']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Create Alias'),
  );

  return $form;
}

function unl_page_alias_create_submit($form, &$form_state) {
  db_insert('unl_page_aliases')->fields(array(
    'from_uri' => $form_state['values']['from_uri'],
    'to_uri' => $form_state['values']['to_uri'],
  ))->execute();
}

/**
 * Page Alias List appears on admin/sites/unl/aliases
 */
function unl_page_alias_list($form, &$form_state) {
  $header = array(
    'site_uri' => array(
      'data' => t('From URI'),
      'field' => 'a.from_uri',
    ),
    'alias_uri' => array(
      'data' => t('To URI'),
      'field' => 'a.to_uri',
    ),
    'installed' => array(
      'data' => t('Status'),
      'field' => 'a.installed',
    ),
    'remove' => t('Remove (can not undo!)'),
  );

  $query = db_select('unl_page_aliases', 'a')
    ->extend('TableSort')
    ->orderByHeader($header);
  $query->fields('a', array('page_alias_id', 'from_uri', 'to_uri', 'installed'));
  $sites = $query->execute()->fetchAll();

  foreach ($sites as $site) {
    $options[$site->page_alias_id] = array(
      'site_uri' => $site->from_uri,
      'alias_uri' => $site->to_uri,
      'installed' => _unl_get_install_status_text($site->installed),
      'remove' => array(
        'data' => array(
          '#type' => 'checkbox',
          '#parents' => array('aliases', $site->page_alias_id, 'remove'),
          '#default_value' => 0,
        ),
      ),
    );
  }

  $form['unl_page_aliases'] = array(
    '#type' => 'fieldset',
    '#title' => t('Existing Page Aliases'),
  );
  $form['unl_page_aliases']['alias_list'] = array(
    '#theme' => 'table',
    '#header' => $header,
    '#rows' => $options,
    '#empty' => t('No aliases available.'),
  );
  $form['unl_page_aliases']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Delete Selected Aliases'),
  );

  return $form;
}

function unl_page_alias_list_submit($form, &$form_state) {
  $page_alias_ids = array();
  foreach ($form_state['values']['aliases'] as $page_alias_id => $alias) {
    if ($alias['remove']) {
      $page_alias_ids[] = $page_alias_id;
    }
  }

  db_update('unl_page_aliases')
    ->fields(array('installed' => 3))
    ->condition('page_alias_id', $page_alias_ids, 'IN')
    ->execute();
}

function unl_wdn_registry($form, &$form_state) {
  $form['root'] = array(
    '#type' => 'fieldset',
    '#title' => t('WDN Registry Database'),
  );

  $form['root']['production'] = array(
    '#type' => 'checkbox',
    '#title' => t('This is production.'),
    '#description' => t('If this box checked, sites imported will be marked as imported.'),
    '#default_value' => variable_get('unl_wdn_registry_production'),
  );

  $form['root']['host'] = array(
    '#type' => 'textfield',
    '#title' => t('Host'),
    '#description' => t('Hostname of the WDN Registry database.'),
    '#default_value' => variable_get('unl_wdn_registry_host'),
    '#required' => TRUE,
  );

  $form['root']['username'] = array(
    '#type' => 'textfield',
    '#title' => t('Username'),
    '#description' => t('Username for the WDN Registry database.'),
    '#default_value' => variable_get('unl_wdn_registry_username'),
    '#required' => TRUE,
  );

  $form['root']['password'] = array(
    '#type' => 'password',
    '#title' => t('Password'),
    '#description' => t('Password for the WDN Registry database.'),
    '#required' => TRUE,
  );

  $form['root']['database'] = array(
    '#type' => 'textfield',
    '#title' => t('Database'),
    '#description' => t('Database for the WDN Registry database.'),
    '#default_value' => variable_get('unl_wdn_registry_database'),
    '#required' => TRUE,
  );

  $form['root']['frontier_username'] = array(
    '#type' => 'textfield',
    '#title' => t('Frontier Username'),
    '#description' => t('Username to connect to frontier FTP.'),
    '#default_value' => variable_get('unl_frontier_username'),
    '#required' => TRUE,
  );

  $form['root']['frontier_password'] = array(
    '#type' => 'password',
    '#title' => t('Frontier Password'),
    '#description' => t('Password to connect to frontier FTP.'),
    '#required' => TRUE,
  );

  $form['root']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Update'),
  );

  return $form;
}

function unl_wdn_registry_submit($form, &$form_state) {
  variable_set('unl_wdn_registry_production', $form_state['values']['production']);
  variable_set('unl_wdn_registry_host', $form_state['values']['host']);
  variable_set('unl_wdn_registry_username', $form_state['values']['username']);
  variable_set('unl_wdn_registry_password', $form_state['values']['password']);
  variable_set('unl_wdn_registry_database', $form_state['values']['database']);
  variable_set('unl_frontier_username', $form_state['values']['frontier_username']);
  variable_set('unl_frontier_password', $form_state['values']['frontier_password']);
}

function _unl_get_install_status_text($id) {
  switch ($id) {
    case 0:
      $installed = t('Scheduled for creation.');
      break;
    case 1:
      $installed = t('Curently being created.');
      break;
    case 2:
      $installed = t('In production.');
      break;
    case 3:
      $installed = t('Scheduled for removal.');
      break;
    case 4:
      $installed = t('Currently being removed.');
      break;
    default:
      $installed = t('Unknown');
      break;
  }

  return $installed;
}

/**
 * Callback for the path admin/sites/unl/user-audit
 * Presents a form to query what roles (if any) a user has on each site.
 */
function unl_user_audit($form, &$form_state) {
  $form['root'] = array(
    '#type' => 'fieldset',
    '#title' => t('User Audit'),
  );

  $form['root']['username'] = array(
    '#type' => 'textfield',
    '#title' => t('Username'),
    '#required' => TRUE,
  );

  /*
  $form['root']['ignore_shared_roles'] = array(
    '#type' => 'checkbox',
    '#title' => 'Ignore Shared Roles',
  );
  */

  $form['root']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Search'),
  );

  // If no user input has been received yet, return the base form.
  if (!isset($form_state['values']) || !$form_state['values']['username']) {
    return $form;
  }

  // Otherwise, since we have a username, we can query the sub-sites and return a list of roles for each.
  $username = $form_state['values']['username'];

  $form['results'] = array(
    '#type' => 'fieldset',
    '#title' => t('Results'),
  );

  $form['results']['roles'] = _unl_get_user_audit_content($username);

  return $form;
}

/**
 * Returns an array that can be passed to drupal_render() of the given user's sites/roles.
 * @param string $username
 */
function _unl_get_user_audit_content($username) {
  if (user_is_anonymous()) {
    return array();
  }

  $audit_map = array();

  foreach (unl_get_site_user_map('username', $username) as $site_id => $site) {
    $audit_map[] = array(
      'data' => l($site['uri'], $site['uri']),
      'children' => $site['roles'],
    );
  }

  if (count($audit_map) > 0) {
    $content = array(
      '#theme' => 'item_list',
      '#type'  => 'ul',
      '#items' => $audit_map,
    );
    if ($username == $GLOBALS['user']->name) {
      $content['#title'] = t('You belong to the following sites as a member of the listed roles.');
    }
    else {
      $content['#title'] = t('The user "' . $username . '" belongs to the following sites as a member of the listed roles.');
    }
  }
  else {
    $content = array(
      '#type' => 'item',
    );
    if ($username == $GLOBALS['user']->name) {
      $content['#title'] = t('You do not belong to any roles on any sites.');
    }
    else {
      $content['#title'] = t('The user "' . $username . '" does not belong to any roles on any sites.');
    }
  }

  return $content;
}

/**
 * Submit handler for unl_user_audit form.
 * Simply tells the form to rebuild itself with the user supplied data.
 */
function unl_user_audit_submit($form, &$form_state) {
  $form_state['rebuild'] = TRUE;
}

/**
 * Similar to the table theme, but the #rows attribute is populated by the contents
 * of the 'rows' instead.  This allows form processing to be applied to table cells.
 * @param array $variables
 */
function theme_unl_table($variables) {
  $form = $variables['form'];
  foreach (element_children($form['rows']) as $row_index) {
    foreach (element_children($form['rows'][$row_index]) as $column_index) {
      $form['#rows'][$row_index][$column_index] = drupal_render($form['rows'][$row_index][$column_index]);
    }
  }
   
  return theme('table', $form);
}

/**
 * Callback for URI admin/sites/unl/feed
 */
function unl_sites_feed() {
  $data = unl_get_site_user_map('role', 'Site Admin', TRUE);

  header('Content-type: application/json');
  echo json_encode($data);
}

/**
 * Returns an array of lists of either roles a user belongs to or users belonging to a role.
 * Each key is the URI of a site and the value is the list.
 * If $list_empty_sites is set to TRUE, all sites will be listed, even if they have empty lists.
 *
 * @param string $search_by (Either 'username' or 'role')
 * @param mixed $username_or_role
 * @param bool $list_empty_sites
 * @throws Exception
 */
function unl_get_site_user_map($search_by, $username_or_role, $list_empty_sites = FALSE) {
  if (!in_array($search_by, array('username', 'role'))) {
    throw new Exception('Invalid argument for $search_by');
  }

  $sites = db_select('unl_sites', 's')
    ->fields('s', array('site_id', 'db_prefix', 'installed', 'site_path', 'uri'))
    ->execute()
    ->fetchAll();

  $audit_map = array();
  foreach ($sites as $site) {
    $shared_prefix = unl_get_shared_db_prefix();
    $prefix = $site->db_prefix;

    try {
      $site_settings = unl_get_site_settings($site->uri);
      $site_db_config = $site_settings['databases']['default']['default'];
      $roles_are_shared = is_array($site_db_config['prefix']) && array_key_exists('role', $site_db_config['prefix']);

      /*
      // If the site uses shared roles, ignore it if the user wants us to.
      if ($roles_are_shared && $form_state['values']['ignore_shared_roles']) {
        continue;
      }
      */

      $bound_params = array();
      $where = array();

      if ($search_by == 'username') {
        $return_label = 'roles';
        $select = 'r.name';
        $where[] = 'u.name = :name';
        $bound_params[':name'] = $username_or_role;
      }
      else {
        $return_label = 'users';
        $select = 'u.name';
        $where[] = 'r.name = :role';
        $bound_params[':role'] = $username_or_role;
      }

      $query = "SELECT {$select} "
             . "FROM {$prefix}_{$shared_prefix}users AS u "
             . "JOIN {$prefix}_{$shared_prefix}users_roles AS m "
             . "  ON u.uid = m.uid "
             . 'JOIN ' . ($roles_are_shared ? '' : $prefix . '_') . $shared_prefix . 'role AS r '
             . "  ON m.rid = r.rid ";

      if (count($where) > 0) {
        $query .= 'WHERE ' . implode(' AND ', $where) . ' ';
      }

      $role_names = db_query($query, $bound_params)->fetchCol();

      if (count($role_names) == 0 && !$list_empty_sites) {
        continue;
      }

      $primary_base_url = unl_site_variable_get($prefix, 'unl_primary_base_url');
      if ($primary_base_url) {
        $uri = $primary_base_url;
      }
      else {
        $uri = $site->uri;
      }
      $audit_map[$site->site_id] = array(
        'uri' => $uri,
        $return_label => $role_names,
      );
    } catch (Exception $e) {
      // Either the site has no settings.php or the db_prefix is wrong.
      drupal_set_message('Error querying database for site ' . $site->uri, 'warning');
    }
  }

  return $audit_map;
}
