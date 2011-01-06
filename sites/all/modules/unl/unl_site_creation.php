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
  
  $uri = url($site_path, array('absolute' => TRUE));
  
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
  
  $form['root']['site_list'] = array(
    '#theme' => 'unl_site_list_table',
  );
  
  $sites = db_select('unl_sites', 's')
    ->fields('s', array('site_id', 'db_prefix', 'installed', 'site_path', 'uri'))
    ->execute()
    ->fetchAll();
  
  foreach ($sites as $site) {
    $form['root']['site_list'][$site->site_id] = array(
      'site_path' => array('#value' => $site->site_path),
      'db_prefix' => array('#value' => $site->db_prefix . '_' . $GLOBALS['databases']['default']['default']['prefix']),
      'installed' => array('#value' => $site->installed),
      'uri'       => array('#value' => $site->uri),
      'remove'    => array(
        '#type'          => 'checkbox',
        '#parents'       => array('sites', $site->site_id, 'remove'),
      	'#default_value' => 0
      ),
    );
  }
  
  $form['root']['submit'] = array(
    '#type'  => 'submit',
    '#value' => 'Delete Selected Sites',
  );
  
  return $form;
}

function theme_unl_site_list_table($variables) {
  $form = $variables['form'];
  
  $headers = array('Site Path', 'Datbase Prefix', 'Status', 'Link', 'Remove (can not undo!)');
  $rows = array();
  foreach (element_children($form) as $key) {
    $installed = _unl_get_install_status_text($form[$key]['installed']['#value']);
    $rows[] = array(
      'data' => array(
        $form[$key]['site_path']['#value'],
        $form[$key]['db_prefix']['#value'],
        $installed,
        '<a href="' . $form[$key]['uri']['#value'] . '/">' . $form[$key]['uri']['#value'] . '/</a>',
        drupal_render($form[$key]['remove']),
      )
    );
  }
  
  return theme('table', array('header' => $headers, 'rows' => $rows));
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
  
  if ($form_state['rebuild']) {
    $button_text = 'Continue Drush';
  }
  else {
    $button_text = 'Run Drush';
  } 
  $form['root']['submit'] = array(
    '#type'  => 'submit',
    '#value' => $button_text,
  );
  
  return $form;
}

function unl_site_updates_submit($form, &$form_state) {
  $sites = db_select('unl_sites', 's')
    ->fields('s', array('site_id', 'db_prefix', 'installed', 'site_path', 'uri'))
    ->execute()
    ->fetchAll();
  
  $start_time = time();
  if (isset($form_state['storage'])) {
    $completed_sites = $form_state['storage'];
  }
  else {
    $completed_sites = array();
  }
  
  foreach ($sites as $site) {
    if (in_array($site->uri, $completed_sites)) {
      continue;
    }
  
    if (time() - $start_time > 30) {
      $form_state['rebuild'] = TRUE;
      $form_state['storage'] = $completed_sites;
      drupal_set_message('Drush ran out of time to process every site. Click "Continue Drush" below.', 'warning');
      return;
    }
    
    $uri = escapeshellarg($site->uri);
    $root = escapeshellarg(DRUPAL_ROOT);
    $command = "sites/all/modules/drush/drush.php -y --token=secret --root={$root} --uri={$uri} updatedb";
    drupal_set_message('Messages from ' . $site->uri . ':<br />' . PHP_EOL . '<pre>' . shell_exec($command) . '</pre>', 'status');
    
    $completed_sites[] = $site->uri;
  }
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
    
  $form['root']['alias_uri'] = array(
    '#type'          => 'textfield',
    '#title'         => t('Alias URL'),
    '#description'   => t('Full URL for the new alias.'),
    '#default_value' => t('http://newsite.example.com/'),
    '#required'      => TRUE,
  );
  
  $form['root']['submit'] = array(
    '#type'  => 'submit',
    '#value' => 'Create Alias',
  );
  
  return $form;
}

function unl_alias_create_submit($form, &$form_state) {
  db_insert('unl_sites_aliases')->fields(array(
    'site_id' => $form_state['values']['site'],
    'uri'     => $form_state['values']['alias_uri'],
  ))->execute();
}


function unl_alias_list($form, &$form_state) {
  $query = db_select('unl_sites_aliases', 'a');
  $query->join('unl_sites', 's', 's.site_id = a.site_id');
  $query->fields('s', array('uri'));
  $query->fields('a', array('site_alias_id', 'uri', 'installed'));
  $sites = $query->execute()->fetchAll();

  $form['root'] = array(
    '#type'  => 'fieldset',
    '#title' => 'Existing Aliases',
  );
  
  $form['root']['alias_list'] = array(
    '#theme' => 'unl_alias_list_table',
  );
  
  foreach ($sites as $site) {
    $form['root']['alias_list'][$site->site_alias_id] = array(
      'site_uri' => array('#value' => $site->uri),
      'alias_uri' => array('#value' => $site->a_uri),
      'installed' => array('#value' => $site->installed),
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

function theme_unl_alias_list_table($variables) {
  $form = $variables['form'];
  
  $headers = array('Site URI', 'Alias URI', 'Status', 'Remove (can not undo!)');
  $rows = array();
  foreach (element_children($form) as $key) {
    $installed = _unl_get_install_status_text($form[$key]['installed']['#value']);
    $rows[] = array(
      'data' => array(
        $form[$key]['site_uri']['#value'],
        $form[$key]['alias_uri']['#value'],
        $installed,
        drupal_render($form[$key]['remove']),
      )
    );
  }
  
  return theme('table', array('header' => $headers, 'rows' => $rows));
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






