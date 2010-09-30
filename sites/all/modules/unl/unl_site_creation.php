<?php

require_once DRUPAL_ROOT . '/includes/install.core.inc';

function unl_site_creation($form, &$form_state) {
  $form['root'] = array(
    '#type'  => 'fieldset',
    '#title' => 'Site Creation Tool',
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
  
  $form['submit'] = array(
    '#type'  => 'submit',
    '#value' => 'Create Site',
  );
  
  return $form;
}

function unl_site_creation_validate($form, &$form_state) {
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

function unl_site_creation_submit($form, &$form_state) {
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
    '#title' => 'UNL Site List',
    '#theme' => 'unl_site_list_table'
  );
  
  $sites = db_select('unl_sites', 's')
    ->fields('s', array('site_id', 'db_prefix', 'installed', 'site_path', 'uri'))
    ->execute()
    ->fetchAll();
  
  foreach ($sites as $site) {
    $form['root'][$site->site_id] = array(
      'site_path' => array('#value' => $site->site_path),
      'db_prefix' => array('#value' => $site->db_prefix . '_' . $GLOBALS['databases']['default']['default']['prefix']),
      'uri'       => array('#value' => $site->uri),
      'remove'    => array(
        '#type'          => 'checkbox',
        '#parents'       => array('sites', $site->site_id, 'remove'),
      	'#default_value' => 0
      ),
    );
  }
  
  $form['submit'] = array(
    '#type'  => 'submit',
    '#value' => 'Delete Selected Sites',
  );
  
  //print_r($form); exit;
  return $form;
}

function theme_unl_site_list_table($variables) {
  $form = $variables['form'];
  
  $headers = array('Site Path', 'Datbase Prefix', 'Link', 'Remove (can not undo!)');
  $rows = array();
  foreach (element_children($form) as $key) {
    $rows[] = array(
      'data' => array(
        $form[$key]['site_path']['#value'],
        $form[$key]['db_prefix']['#value'],
        '<a href="' . $form[$key]['uri']['#value'] . '">' . $form[$key]['uri']['#value'] . '</a>',
        drupal_render($form[$key]['remove']),
      )
    );
  }
  
  return theme('table', array('header' => $headers, 'rows' => $rows, 'caption' => $form['#title']));
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

  $path_parts = parse_url($uri);
  $sites_subdir = $path_parts['host'] . $path_parts['path'];
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
  drupal_set_message('The site has been scheduled for removal.');
}
















