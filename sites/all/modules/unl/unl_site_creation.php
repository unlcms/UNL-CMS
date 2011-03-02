<?php

require_once DRUPAL_ROOT . '/includes/install.core.inc';



function unl_sites_page() {
  $page = array();
  $page[] = drupal_get_form('unl_site_create');
  $page[] = drupal_get_form('unl_site_list');
  $page[] = drupal_get_form('unl_site_updates');
  
  return $page;
}


function unl_site_create($form, &$form_state) {
  $form['root'] = array(
    '#type'  => 'fieldset',
    '#title' => 'Create New Site',
  );
  
  $form['root']['site_path'] = array(
    '#type'          => 'textfield',
    '#title'         => t('New site path'),
    '#description'   => t('Relative url for the new site'),
    '#default_value' => t('newsite'),
    '#required'      => TRUE,
  );
  
  $form['root']['clean_url'] = array(
    '#type'          => 'checkbox',
    '#title'         => t('Use clean URLs'),
    '#description'   => t('Unless you have some reason to think your site won\'t support this, leave it checked.'),
    '#default_value' => 1,
  );
  
  $form['root']['submit'] = array(
    '#type'  => 'submit',
    '#value' => 'Create Site',
  );
  
  return $form;
}

function unl_site_create_validate($form, &$form_state) {
  $site_path = trim($form_state['values']['site_path']);
  
  if (substr($site_path, 0, 1) == '/') {
    $site_path = substr($site_path, 1);
  }
  if (substr($site_path, -1) == '/') {
    $site_path = substr($site_path, 0, -1);
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
  
  $uri = url($site_path, array('absolute' => TRUE, 'https' => FALSE));
  
  $clean_url = intval($clean_url);
  
  $db_prefix = explode('/', $site_path);
  $db_prefix = implode('_', $db_prefix);
  
  db_insert('unl_sites')->fields(array(
    'site_path' => $site_path,
    'uri'       => $uri,
    'clean_url' => $clean_url,
    'db_prefix' => $db_prefix
  ))->execute();
  
  drupal_set_message(t('The site ' . $uri . ' has been started, run unl/cron.php to finish setup.'));
  $form_state['redirect'] = 'admin/sites/unl/add';
  return;
}


function unl_site_list($form, &$form_state) {
  $form['root'] = array(
    '#type'  => 'fieldset',
    '#title' => 'Existing Sites',
  );
  
  $headers = array(
    'site_path' => array(
      'data' => 'Site Path',
      'field' => 's.site_path',
    ),
    'db_prefix' => array(
      'data' => 'Datbase Prefix',
      'field' => 's.db_prefix',
    ),
    'installed' => array(
      'data' => 'Status',
      'field' => 's.installed',
    ),
    'uri'       => array(
      'data' => 'Link',
      'field' => 's.uri',
    ),
    'remove'    => 'Remove (can not undo!)'
  );
  
  $form['root']['site_list'] = array(
    '#theme' => 'unl_table',
    '#header' => $headers,
  );
  
  $sites = db_select('unl_sites', 's')
    ->fields('s', array('site_id', 'db_prefix', 'installed', 'site_path', 'uri'))
    ->extend('TableSort')
    ->orderByHeader($headers)
    ->execute()
    ->fetchAll();
  
  foreach ($sites as $site) {
    unset($checkbox);
    $form['root']['site_list']['rows'][$site->site_id] = array(
      'site_path' => array('#prefix' => $site->site_path),
      'db_prefix' => array('#prefix' => $site->db_prefix . '_' . $GLOBALS['databases']['default']['default']['prefix']),
      'installed' => array('#prefix' => _unl_get_install_status_text($site->installed)),
      'uri'       => array(
        '#type'  => 'link',
        '#title' => $site->uri,
        '#href'  => $site->uri,
      ),
      'remove'    => array(
        '#type'          => 'checkbox',
        '#parents'       => array('sites', $site->site_id, 'remove'),
        '#default_value' => 0,
      ),
    );
  }
  
  $form['root']['submit'] = array(
    '#type'  => 'submit',
    '#value' => 'Delete Selected Sites',
  );
  
  return $form;
}

function unl_site_list_submit($form, &$form_state) {
  if (!isset($form_state['values']['sites'])) {
    return;
  }
  
  foreach($form_state['values']['sites'] as $site_id => $site) {
    if ($site['remove']) {
      unl_site_remove($site_id);
    }
  }
}


function unl_site_updates($form, &$form_state) {
  $form['root'] = array(
    '#type' => 'fieldset',
    '#title' => 'Maintenance',
    '#description' => 'Using drush, do database updates and clear the caches of all sites.',
  );
  
  $form['root']['submit'] = array(
    '#type'  => 'submit',
    '#value' => 'Run Drush',
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
  $command = "sites/all/modules/drush/drush.php -y --token=secret --root={$root} --uri={$uri} updatedb";
  drupal_set_message('Messages from ' . $site_uri . ':<br />' . PHP_EOL . '<pre>' . shell_exec($command) . '</pre>', 'status');
}



function unl_site_remove($site_id) {
  $uri = db_select('unl_sites', 's')
    ->fields('s', array('uri'))
    ->condition('site_id', $site_id)
    ->execute()
    ->fetchCol();
  
  if (!isset($uri[0])) {
    form_set_error(NULL, 'Unfortunately, the site could not be removed.');
    return;
  }
  $uri = $uri[0];

  $sites_subdir = _unl_get_sites_subdir($uri);
  $sites_subdir = strtr($sites_subdir, array('/' => '.'));
  $sites_subdir = DRUPAL_ROOT . '/sites/' . $sites_subdir;
  $sites_subdir = realpath($sites_subdir);
  
  // A couple checks to make sure we aren't deleting something we shouldn't be.
  if (substr($sites_subdir, 0, strlen(DRUPAL_ROOT . '/sites/')) != DRUPAL_ROOT . '/sites/') {
    form_set_error(NULL, 'Unfortunately, the site could not be removed.');
    return;
  }
  
  if (strlen($sites_subdir) <= strlen(DRUPAL_ROOT . '/sites/')) {
    form_set_error(NULL, 'Unfortunately, the site could not be removed.');
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
  drupal_set_message('The site has been scheduled for removal.');
}

function _unl_get_sites_subdir($uri) {
  $path_parts = parse_url($uri);
  if (substr($path_parts['host'], -7) == 'unl.edu') {
    $path_parts['host'] = 'unl.edu';
  }
  $sites_subdir = $path_parts['host'] . $path_parts['path'];
  $sites_subdir = strtr($sites_subdir, array('/' => '.')); 
  
  return $sites_subdir;
}



function unl_aliases_page() {
  $page = array();
  $page[] = drupal_get_form('unl_alias_create');
  $page[] = drupal_get_form('unl_alias_list');
  
  return $page;
}


function unl_alias_create($form, &$form_state) {
  
  $sites = db_select('unl_sites', 's')
    ->fields('s', array('site_id', 'uri'))
    ->execute()
    ->fetchAll();
  foreach ($sites as $site) {
    $site_list[$site->site_id] = $site->uri . '/';
  }
  
  $form['root'] = array(
    '#type'  => 'fieldset',
    '#title' => 'Create New Alias',
  );
  
  $form['root']['site'] = array(
    '#type' => 'select',
    '#title' => 'Aliased Site',
    '#description' => 'The site the alias will point to.',
    '#options' => $site_list,
    '#required' => TRUE,
  );
  
  $form['root']['base_uri'] = array(
    '#type'          => 'textfield',
    '#title'         => t('Alias Base URL'),
    '#description'   => t('The base URL for the new alias.'),
    '#default_value' => url('', array('https' => FALSE)),
    '#required'      => TRUE,
  );
  
  $form['root']['path'] = array(
    '#type'          => 'textfield',
    '#title'         => t('Path'),
    '#description'   => t('Path for the new alias.'),
  );
  
  $form['root']['submit'] = array(
    '#type'  => 'submit',
    '#value' => 'Create Alias',
  );
  
  return $form;
}

function unl_alias_create_submit($form, &$form_state) {
  db_insert('unl_sites_aliases')->fields(array(
    'site_id'  => $form_state['values']['site'],
    'base_uri' => $form_state['values']['base_uri'],
    'path'     => $form_state['values']['path'],
  ))->execute();
}


function unl_alias_list($form, &$form_state) {

  $form['root'] = array(
    '#type'  => 'fieldset',
    '#title' => 'Existing Aliases',
  );
  
  $headers = array(
    'site_uri' => array(
      'data' => 'Site URI',
      'field' => 's.uri',
    ),
    'alias_uri' => array(
      'data' => 'Alias URI',
      'field' => 'a.uri',
    ),
    'installed' => array(
      'data' => 'Status',
      'field' => 'a.installed',
    ),
    'remove'    => 'Remove (can not undo!)'
  );
  
  $form['root']['alias_list'] = array(
    '#theme'  => 'unl_table',
    '#header' => $headers,
  );
  
  $query = db_select('unl_sites_aliases', 'a')
    ->extend('TableSort')
    ->orderByHeader($headers);
  $query->join('unl_sites', 's', 's.site_id = a.site_id');
  $query->fields('s', array('uri'));
  $query->fields('a', array('site_alias_id', 'base_uri', 'path', 'installed'));
  $sites = $query->execute()->fetchAll();
  
  foreach ($sites as $site) {
    $form['root']['alias_list']['rows'][$site->site_alias_id] = array(
      'site_uri' => array('#prefix'  => $site->uri),
      'alias_uri' => array('#prefix' => $site->base_uri . $site->path),
      'installed' => array('#prefix' => _unl_get_install_status_text($site->installed)),
      'remove'    => array(
        '#type'          => 'checkbox',
        '#parents'       => array('aliases', $site->site_alias_id, 'remove'),
        '#default_value' => 0
      ),
    );
  }
  
  $form['root']['submit'] = array(
    '#type'  => 'submit',
    '#value' => 'Delete Selected Aliases',
  );
  
  return $form;
}

function unl_alias_list_submit($form, &$form_state) {
  $site_alias_ids = array();
  foreach ($form_state['values']['aliases'] as $site_alias_id => $alias) {
    if ($alias['remove']) {
      $site_alias_ids[] = $site_alias_id;
    }
  }
  
  db_update('unl_sites_aliases')
    ->fields(array('installed' => 3))
    ->condition('site_alias_id', $site_alias_ids, 'IN')
    ->execute();
}


function unl_wdn_registry($form, &$form_state) {
  
  $form['root'] = array(
    '#type'  => 'fieldset',
    '#title' => 'WDN Registry Database',
  );
  
  $form['root']['production'] = array(
    '#type' => 'checkbox',
    '#title' => 'This is production.',
    '#description' => 'If this box checked, sites imported will be marked as imported.',
    '#default_value' => variable_get('unl_wdn_registry_production'),
  );
  
  $form['root']['host'] = array(
    '#type' => 'textfield',
    '#title' => 'Host',
    '#description' => 'Hostname of the WDN Registry database.',
    '#default_value' => variable_get('unl_wdn_registry_host'),
    '#required' => TRUE,
  );
  
  $form['root']['username'] = array(
    '#type' => 'textfield',
    '#title' => 'Username',
    '#description' => 'Username for the WDN Registry database.',
    '#default_value' => variable_get('unl_wdn_registry_username'),
    '#required' => TRUE,
  );
  
  $form['root']['password'] = array(
    '#type' => 'password',
    '#title' => 'Password',
    '#description' => 'Password for the WDN Registry database.',
    '#required' => TRUE,
  );
  
  $form['root']['database'] = array(
    '#type' => 'textfield',
    '#title' => 'Database',
    '#description' => 'Database for the WDN Registry database.',
    '#default_value' => variable_get('unl_wdn_registry_database'),
    '#required' => TRUE,
  );
  
  $form['root']['frontier_username'] = array(
    '#type' => 'textfield',
    '#title' => 'Frontier Username',
    '#description' => 'Username to connect to frontier FTP.',
    '#default_value' => variable_get('unl_frontier_username'),
    '#required' => TRUE,
  );
  
  $form['root']['frontier_password'] = array(
    '#type' => 'password',
    '#title' => 'Frontier Password',
    '#description' => 'Password to connect to frontier FTP.',
    '#required' => TRUE,
  );
  
  $form['root']['submit'] = array(
    '#type'  => 'submit',
    '#value' => 'Update',
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
    $installed = 'Scheduled for creation.';
    break;
    
    case 1:
    $installed = 'Curently being created.';
    break;

    case 2:
      $installed = 'In production.';
      break;

    case 3:
      $installed = 'Scheduled for removal.';
      break;
      
    case 4:
      $installed = 'Currently being removed.';
      break;

    default:
      $installed = 'Unknown';
      break;
  }

  return $installed;
}


function theme_unl_table($variables) {
  $form = $variables['form'];
  foreach (element_children($form['rows']) as $row_index) {
    foreach (element_children($form['rows'][$row_index]) as $column_index) {
      $form['#rows'][$row_index][$column_index] = drupal_render($form['rows'][$row_index][$column_index]);
    }
  }
  
  return theme('table', $form);
}





