<?php
/**
 * Contains the callback functions for the pages/forms located at
 * admin/sites/unl as specified in unl_multisite_menu().
 */

require_once DRUPAL_ROOT . '/includes/install.core.inc';

/**
 * Page callback for admin/sites/unl
 */
function unl_sites_page() {
  $page = array();
  $page[] = drupal_get_form('unl_site_list');
  $page[] = drupal_get_form('unl_site_updates');
  $page[] = drupal_get_form('unl_site_email_settings');
  return $page;
}

/**
 * Form: Create New Site.
 */
function unl_site_create($form, &$form_state) {
  $form['site_path'] = array(
    '#type' => 'textfield',
    '#title' => t('New site path'),
    '#description' => t('Relative url for the new site.'),
    '#default_value' => 'newsite',
    '#required' => TRUE,
  );
  $form['clean_url'] = array(
    '#type' => 'checkbox',
    '#title' => t('Use clean URLs'),
    '#description' => t("Unless you have some reason to think your site won't support this, leave it checked."),
    '#default_value' => 1,
  );
  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Create site'),
  );
  return $form;
}

/**
 * Form Validate: Create New Site.
 */
function unl_site_create_validate($form, &$form_state) {
  $form_state['values']['site_path'] = unl_validate_path($form, $form_state);
}

/**
 * Custom function to validate and correct a path submitted in a form.
 */
function unl_validate_path($form, $form_state) {
  $site_path = trim($form_state['values']['site_path']);

  if (substr($site_path, 0, 1) == '/') {
    $site_path = substr($site_path, 1);
  }
  if (substr($site_path, -1) != '/') {
    $site_path .= '/';
  }

  $site_path_parts = explode('/', $site_path);
  $first_directory = array_shift($site_path_parts);
  if (in_array($first_directory, array('core', 'includes', 'misc', 'modules', 'profiles', 'scripts', 'sites', 'themes', 'vendor'))) {
    form_set_error('site_path', t('Drupal site paths must not start with the @first_directory directory.', array('@first_directory' => $first_directory)));
  }

  if ($form['#form_id'] != 'unl_site_create') {
    if (substr(strtolower($form['site_path']['#default_value']), 0, strlen($site_path)) ==  strtolower($site_path)) {
      form_set_error('site_path', t('New path cannot be parent directory of current path.'));
    }

    if (substr(strtolower($site_path), 0, strlen($form['site_path']['#default_value'])) ==  strtolower($form['site_path']['#default_value'])) {
      form_set_error('site_path', t('New path cannot be sub directory of current path.'));
    }
  }

  $site = db_select('unl_sites', 's')
    ->fields('s', array('site_path'))
    ->condition('site_path', $site_path)
    ->execute()
    ->fetch();

  $alias = db_select('unl_sites_aliases', 'a')
    ->fields('a', array('path'))
    ->condition('path', $site_path)
    ->execute()
    ->fetch();

  if ($site || $alias) {
    form_set_error('site_path', t('Path already in use.'));
  }

  return $site_path;
}

/**
 * Form Submit: Create New Site.
 */
function unl_site_create_submit($form, &$form_state) {
  $site_path = $form_state['values']['site_path'];
  $clean_url = $form_state['values']['clean_url'];

  $db_prefix = _unl_multisite_create_db_prefix($site_path);

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

  drupal_set_message(t('The site @uri has been scheduled for creation. Run cron.php to finish install.', array('@uri' => $uri)));
  $form_state['redirect'] = 'admin/sites/unl/add';
  return;
}

/**
 * Form: Existing sites list table appears on admin/sites/unl
 */
function unl_site_list($form, &$form_state) {
  $header = array(
    'uri' => array(
      'data' => t('Default path'),
      'field' => 'uri',
    ),
    'name' => array(
      'data' => t('Site name'),
      'field' => 'name',
    ),
    'access' =>  array(
      'data' => t('Last access'),
      'field' => 'access',
    ),
    'installed' => array(
      'data' => t('Status'),
      'field' => 'installed',
    ),
    'operations' => t('Operations'),
  );

  $sites = db_select('unl_sites', 's')
    ->fields('s', array('site_id', 'db_prefix', 'installed', 'site_path', 'uri'))
    ->execute()
    ->fetchAll();

  // In addition to the above db query, add site name and last access timestamp
  unl_add_extra_site_info($sites);

  $rows = array();
  foreach ($sites as $site) {
    $rows[$site->site_id] = array(
      'uri' => theme('unl_site_details', array('site_path' => $site->site_path, 'uri' => $site->uri, 'db_prefix' => $site->db_prefix)),
      'name' => (isset($site->name) ? $site->name : ''),
      'access' => (isset($site->access) ? $site->access : 0),
      'installed' => _unl_get_install_status_text($site->installed),
      'operations' => array(
        'data' => array(
          '#theme' => 'links__node_operations',
          '#links' => array(
            'aliases' => array(
              'title' => t('edit aliases'),
              'href' => 'admin/sites/unl/' . $site->site_id . '/aliases',
            ),
            'edit' => array(
              'title' => t('edit site'),
              'href' => 'admin/sites/unl/' . $site->site_id . '/edit',
            ),
            'delete' => array(
              'title' => t('delete site'),
              'href' => 'admin/sites/unl/' . $site->site_id . '/delete',
              'query' => drupal_get_destination(),
            ),
          ),
          '#attributes' => array('class' => array('links', 'inline')),
        ),
      ),
    );
  }

  // Sort the table data accordingly with a custom sort function
  $order = tablesort_get_order($header);
  $sort = tablesort_get_sort($header);
  $rows = unl_sites_sort($rows, $order, $sort);

  // Now that the access timestamp has been used to sort, convert it to something readable
  foreach ($rows as $key=>$row) {
    $rows[$key]['access'] = (isset($row['access']) && $row['access'] > 0) ? t('@time ago', array('@time' => format_interval(REQUEST_TIME - $row['access']))) : t('never');
  }

  $form['unl_sites']['site_list'] = array(
    '#caption' => t('Existing Sites: ') . count($sites),
    '#theme' => 'table',
    '#header' => $header,
    '#rows' => $rows,
    '#empty' => t('No sites have been created.'),
  );
  return $form;
}

/**
 * Adds virtual name and access fields to a result set from the unl_sites table.
 * @param $sites The result of db_select()->fetchAll() on the unl_sites table.
 */
function unl_add_extra_site_info($sites) {
  // Get all custom made roles (roles other than authenticated, anonymous, administrator)
  $roles = user_roles(TRUE);
  unset($roles[DRUPAL_AUTHENTICATED_RID]);
  unset($roles[variable_get('user_admin_role')]);

  // Setup alternate db connection so we can query other sites' tables without a prefix being attached
  $database_noprefix = array(
    'database' => $GLOBALS['databases']['default']['default']['database'],
    'username' => $GLOBALS['databases']['default']['default']['username'],
    'password' => $GLOBALS['databases']['default']['default']['password'],
    'host' => $GLOBALS['databases']['default']['default']['host'],
    'port' => $GLOBALS['databases']['default']['default']['port'],
    'driver' => $GLOBALS['databases']['default']['default']['driver'],
  );
  Database::addConnectionInfo('UNLNoPrefix', 'default', $database_noprefix);

  // The master prefix that was specified during initial drupal install
  $master_prefix = $GLOBALS['databases']['default']['default']['prefix'];

  foreach ($sites as $row) {
    // Skip over any sites that aren't properly installed.
    if (!in_array($row->installed, array(2, 6))) {
      continue;
    }

    // Switch to alt db connection
    db_set_active('UNLNoPrefix');

    // Get site name
    $table = $row->db_prefix.'_'.$master_prefix.'variable';
    $name = db_query("SELECT value FROM ".$table." WHERE name = 'site_name'")->fetchField();

    // Get last access timestamp (by a non-administrator)
    $table_users = $row->db_prefix.'_'.$master_prefix.'users u';
    $table_users_roles = $row->db_prefix.'_'.$master_prefix.'users_roles r';
    if (!empty($roles)) {
      $access = db_query('SELECT u.access FROM '.$table_users.', '.$table_users_roles.' WHERE u.uid = r.uid AND u.access > 0 AND r.rid IN (' . implode(',', array_keys($roles)) . ') ORDER BY u.access DESC')->fetchColumn();
    }
    else {
      $access = 0;
    }

    // Restore default db connection
    db_set_active();

    // Update unl_sites table of the default site
    $row->name = @unserialize($name);
    $row->access = (int)$access;
  }
}

/**
 * Custom sort the Existing Sites table.
 */
function unl_sites_sort($rows, $order, $sort) {
  switch ($order['sql']) {
    case 'uri':
      if ($sort == 'asc') {
        usort($rows, "unl_uri_cmp_asc");
      }
      else {
        usort($rows, "unl_uri_cmp_desc");
      }
      break;
    case 'name':
      if ($sort == 'asc') {
        usort($rows, "unl_name_cmp_asc");
      }
      else {
        usort($rows, "unl_name_cmp_desc");
      }
      break;
    case 'access':
      if ($sort == 'asc') {
        usort($rows, "unl_access_cmp_asc");
      }
      else {
        usort($rows, "unl_access_cmp_desc");
      }
      break;
    case 'installed':
      if ($sort == 'asc') {
        usort($rows, "unl_installed_cmp_asc");
      }
      else {
        usort($rows, "unl_installed_cmp_desc");
      }
      break;
  }

  return $rows;
}

/**
 * Comparison functions used in unl_sites_sort().
 */
function unl_uri_cmp_asc($a, $b) {
  return strcasecmp($a['uri'], $b['uri']);
}
function unl_uri_cmp_desc($a, $b) {
  return strcasecmp($b['uri'], $a['uri']);
}
function unl_name_cmp_asc($a, $b) {
  return strcasecmp($a['name'], $b['name']);
}
function unl_name_cmp_desc($a, $b) {
  return strcasecmp($b['name'], $a['name']);
}
function unl_access_cmp_asc($a, $b) {
  return strcmp($b['access'], $a['access']);
}
function unl_access_cmp_desc($a, $b) {
  return strcmp($a['access'], $b['access']);
}
function unl_installed_cmp_asc($a, $b) {
  return strcmp($a['installed'], $b['installed']);
}
function unl_installed_cmp_desc($a, $b) {
  return strcmp($b['installed'], $a['installed']);
}

/**
 * Implements theme_CUSTOM() which works with unl_theme().
 * This themes the Default Path column on the UNL Sites tables at admin/sites/unl
 */
function theme_unl_site_details($variables) {
  $output = '<div><a href="' . $variables['uri'] . '">' . $variables['site_path'] . '</a></div>';
  $output .= '<div class="db_prefix" style="display:none;">Database Prefix: ' . $variables['db_prefix'] . '_' . $GLOBALS['databases']['default']['default']['prefix'] . '</div>';
  return $output;
}

/**
 * Form to edit site details stored in unl_sites.
 */
function unl_site_edit($form, &$form_state, $site_id) {
  $site = db_select('unl_sites', 's')
    ->fields('s', array('site_path', 'uri', 'installed', 'clean_url', 'db_prefix'))
    ->condition('site_id', $site_id)
    ->execute()
    ->fetch();

  $form['site_id'] = array(
    '#title' => 'site_id',
    '#type' => 'textfield',
    '#default_value' => $site_id,
    '#disabled' => TRUE,
  );
  $form['site_path'] = array(
    '#title' => 'site_path',
    '#type' => 'textfield',
    '#default_value' => $site->site_path,
    '#required' => $site->installed != 6,
    '#disabled' => $site->installed == 6,
  );
  $form['uri'] = array(
    '#title' => 'uri',
    '#type' => 'textfield',
    '#default_value' => $site->uri,
    '#disabled' => TRUE,
  );
  $form['db_prefix'] = array(
    '#title' => 'db_prefix',
    '#type' => 'textfield',
    '#default_value' => $site->db_prefix,
    '#disabled' => TRUE,
  );
  $form['installed'] = array(
    '#title' => 'installed',
    '#type' => 'textfield',
    '#default_value' => $site->installed,
    '#disabled' => TRUE,
  );
  $form['clean_url'] = array(
    '#title' => 'clean_url',
    '#type' => 'textfield',
    '#default_value' => $site->clean_url,
    '#disabled' => TRUE,
  );
  $form['submit'] = array(
    '#type' => 'submit',
    '#default_value' => t('Submit'),
    '#access' => $site->installed != 6,
  );
  return $form;
}

/**
 * Form validate handler for unl_site_edit().
 */
function unl_site_edit_validate($form, &$form_state) {
  $form_state['values']['site_path'] = unl_validate_path($form, $form_state);
}

/**
 * Form submit handler for unl_site_edit().
 */
function unl_site_edit_submit($form, &$form_state) {
  $site_id = $form_state['values']['site_id'];
  $old_site_path = $form['site_path']['#default_value'];
  $site_path = $form_state['values']['site_path'];

  // Flag row in unl_sites table for update
  db_update('unl_sites')
    ->fields(array('installed' => 6))
    ->condition('site_id', $site_id)
    ->execute();

  // Add an alias to the new site_path. This new path and the current default path in unl_sites will be exchanged with one another in cron.
  db_insert('unl_sites_aliases')->fields(array(
    'site_id' => $site_id,
    'base_uri' => url('', array('absolute' => TRUE, 'https' => FALSE)),
    'path' => $site_path,
    'installed' => 6,
  ))->execute();

  drupal_set_message("{$old_site_path} has been scheduled to change to {$site_path}");
  $form_state['redirect'] = 'admin/sites/unl';
  return;
}

/**
 * Form to confirm UNL site delete operation.
 */
function unl_site_delete_confirm($form, &$form_state, $site_id) {
  $site_path = db_select('unl_sites', 's')
    ->fields('s', array('site_path'))
    ->condition('site_id', $site_id)
    ->execute()
    ->fetchCol();

  $form['site_id'] = array(
    '#type' => 'value',
    '#value' => $site_id,
  );
  $form['confirm_delete'] = array(
    '#type' => 'checkbox',
    '#title' => t('Confirm'),
    '#description' => t('I am sure I want to permanently delete %site_path ?', array('%site_path' => $site_path[0])),
    '#required' => TRUE,
  );
  $form['confirm_again'] = array(
    '#type' => 'checkbox',
    '#title' => t('Confirm again'),
    '#description' => t('Yes, I am absolutely sure I want to permanently delete this site.'),
    '#required' => TRUE,
  );

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

/**
 * Perform the actual site delete.
 */
function unl_site_remove($site_id) {
  $uri = db_select('unl_sites', 's')
    ->fields('s', array('uri'))
    ->condition('site_id', $site_id)
    ->execute()
    ->fetchCol();

  if (!isset($uri[0])) {
    form_set_error(NULL, t('The site could not be removed.'));
    return;
  }
  $uri = $uri[0];

  $sites_subdir = unl_get_sites_subdir($uri);
  $sites_subdir = DRUPAL_ROOT . '/sites/' . $sites_subdir;
  $sites_subdir = realpath($sites_subdir);

  // A couple checks to make sure we aren't deleting something we shouldn't be.
  if (substr($sites_subdir, 0, strlen(DRUPAL_ROOT . '/sites/')) != DRUPAL_ROOT . '/sites/') {
    form_set_error(NULL, t('The site could not be removed.'));
    return;
  }

  if (strlen($sites_subdir) <= strlen(DRUPAL_ROOT . '/sites/')) {
    form_set_error(NULL, t('The site could not be removed.'));
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
 * Form: Maintenance
 */
function unl_site_updates($form, &$form_state) {
  $form['root'] = array(
    '#type' => 'fieldset',
    '#title' => t('Maintenance'),
    '#description' => t('Use drush to perform database updates and clear the caches of all sites.'),
  );
  $form['root']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Run drush'),
  );
  return $form;
}

/**
 * Form Submit: Maintenance
 */
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

/**
 * Form: Email Alert Settings
 */
function unl_site_email_settings($form, &$form_state) {
  $form['root'] = array(
    '#type' => 'fieldset',
    '#title' => t('Email alert settings'),
    '#description' => t('When a new site is created, who should be emailed?'),
  );
  $form['root']['unl_site_created_email_address'] = array(
    '#type' => 'textfield',
    '#title' => t('Address for notification'),
    '#description' => t('When a site has been been created and migrated, send an email to this address.'),
    '#default_value' => variable_get('unl_site_created_email_address'),
  );
  $form['root']['unl_site_created_alert_admins'] = array(
    '#type' => 'checkbox',
    '#title' => t('Email site admins'),
    '#description' => t('When a site has been created and migrated, send an email to the users with Site Admin role.'),
    '#default_value' => variable_get('unl_site_created_alert_admins'),
  );
  $form['root']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Update settings'),
  );
  return $form;
}

/**
 * Form Submit: Email Alert Settings
 */
function unl_site_email_settings_submit($form, &$form_state) {
  variable_set('unl_site_created_email_address', $form_state['values']['unl_site_created_email_address']);
  variable_set('unl_site_created_alert_admins',  $form_state['values']['unl_site_created_alert_admins']);
}

/**
 * Page callback for admin/sites/unl/aliases
 */
function unl_aliases_page($site_id = null) {
  $page = array();

  if (isset($site_id)) {
    $page[] = drupal_get_form('unl_site_alias_create', $site_id);
    $page[] = drupal_get_form('unl_site_alias_list', $site_id);
  }
  else {
    $page[] = drupal_get_form('unl_site_alias_create');
    $page[] = drupal_get_form('unl_site_alias_list');
    $page[] = drupal_get_form('unl_page_alias_create');
    $page[] = drupal_get_form('unl_page_alias_list');
  }

  return $page;
}

/**
 * Form: Create New Site Alias
 */
function unl_site_alias_create($form, &$form_state, $site_id = null) {
  $query = db_select('unl_sites', 's')
    ->fields('s', array('site_id', 'site_path'))
    ->orderBy('uri');
  if (isset($site_id)) {
    $query->condition('site_id', $site_id);
  }
  $sites = $query->execute()->fetchAll();
  foreach ($sites as $site) {
    $site_list[$site->site_id] = $site->site_path;
  }

  $form['root'] = array(
    '#type' => 'fieldset',
    '#title' => t('Create new site alias'),
  );
  $form['root']['site'] = array(
    '#type' => 'select',
    '#title' => t('Aliased site path'),
    '#description' => t('The site the alias will point to.'),
    '#options' => $site_list,
    '#required' => TRUE,
    '#default_value' => (isset($site_id) ? $site_id : FALSE),
    '#disabled' => (isset($site_id) ? TRUE : FALSE),
  );
  $form['root']['base_uri'] = array(
    '#type' => 'textfield',
    '#title' => t('Alias base URL'),
    '#description' => t('The base URL for the new alias. This should resolve to the directory containing the .htaccess file.'),
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
    '#value' => t('Create alias'),
  );
  return $form;
}

/**
 * Form Validate: Create New Site Alias
 */
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

/**
 * Form Submit: Create New Site Alias
 */
function unl_site_alias_create_submit($form, &$form_state) {
  db_insert('unl_sites_aliases')->fields(array(
    'site_id' => $form_state['values']['site'],
    'base_uri' => $form_state['values']['base_uri'],
    'path' => $form_state['values']['path'],
  ))->execute();
}

/**
 * Form: Existing Site Alias list table appears on admin/sites/unl/aliases
 */
function unl_site_alias_list($form, &$form_state, $site_id = null) {
  $header = array(
    'site_uri' => array(
      'data' => t('Aliased site path'),
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
    'remove' => t('Remove'),
  );

  $query = db_select('unl_sites_aliases', 'a')
    ->extend('TableSort')
    ->orderByHeader($header);
  $query->join('unl_sites', 's', 's.site_id = a.site_id');
  if (isset($site_id)) {
    $query->condition('s.site_id', $site_id);
  }
  $query->fields('s', array('site_path'));
  $query->fields('a', array('site_alias_id', 'base_uri', 'path', 'installed'));
  $sites = $query->execute()->fetchAll();

  foreach ($sites as $site) {
    $options[$site->site_alias_id] = array(
      'site_uri' => array('#prefix' => $site->site_path),
      'alias_uri' => array('#prefix' => $site->base_uri . '<span style="color:#777">' . $site->path . '</span>'),
      'installed' => array('#prefix' => _unl_get_install_status_text($site->installed)),
      'remove' => array(
        '#type' => 'checkbox',
        '#parents' => array('aliases', $site->site_alias_id, 'remove'),
        '#default_value' => 0,
        '#disabled' => $site->installed == 6,
      ),
    );
  }

  $form['unl_site_aliases'] = array(
    '#type' => 'fieldset',
    '#title' => t('Existing site aliases'),
  );
  $form['unl_site_aliases']['alias_list'] = array(
    '#theme' => 'unl_table',
    '#header' => $header,
    'rows' => (!empty($options) ? $options : FALSE),
    '#empty' => t('No aliases available.'),
  );
  $form['unl_site_aliases']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Delete selected aliases'),
  );

  return $form;
}

/**
 * Form Submit: Existing Site Alias list delete
 */
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

/**
 * Form: Create New Page Alias
 */
function unl_page_alias_create($form, &$form_state) {
  $form['root'] = array(
    '#type' => 'fieldset',
    '#title' => 'Create new page alias',
  );
  $form['root']['from_uri'] = array(
    '#type' => 'textfield',
    '#title' => t('From URL'),
    '#description' => t('The URL that users will visit.'),
    '#default_value' => url('from/url', array('https' => FALSE)),
    '#required' => TRUE,
  );
  $form['root']['to_uri'] = array(
    '#type' => 'textfield',
    '#title' => t('To URL'),
    '#description' => t('The URL users will be redirected to.'),
    '#default_value' => url('to/url', array('https' => FALSE)),
    '#required' => TRUE,
  );
  $form['root']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Create alias'),
  );
  return $form;
}

/**
 * Form Validate: Create New Page Alias
 */
function unl_page_alias_create_validate($form, &$form_state) {
  if ($form_state['values']['from_uri'] == $form_state['values']['to_uri']) {
    form_set_error('', t('From URL cannot equal To URL.'));
  }
  if ($form_state['values']['from_uri'] == url('', array('https' => FALSE)) ||
      $form_state['values']['from_uri'] == str_replace('http://', 'https://', url('', array('https' => FALSE)))) {
    form_set_error('', t('From URL cannot be the root of the default site.'));
  }
}

/**
 * Form Submit: Create New Page Alias
 */
function unl_page_alias_create_submit($form, &$form_state) {
  db_insert('unl_page_aliases')->fields(array(
    'from_uri' => $form_state['values']['from_uri'],
    'to_uri' => $form_state['values']['to_uri'],
  ))->execute();
}

/**
 * Form: Existing Page Aliases list appears on admin/sites/unl/aliases
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
      'site_uri' => array('#prefix' => $site->from_uri),
      'alias_uri' => array('#prefix' => $site->to_uri),
      'installed' => array('#prefix' => _unl_get_install_status_text($site->installed)),
      'remove' => array(
        '#type' => 'checkbox',
        '#parents' => array('aliases', $site->page_alias_id, 'remove'),
        '#default_value' => 0,
      ),
    );
  }

  $form['unl_page_aliases'] = array(
    '#type' => 'fieldset',
    '#title' => t('Existing page aliases'),
  );
  $form['unl_page_aliases']['alias_list'] = array(
    '#theme' => 'unl_table',
    '#header' => $header,
    'rows' => (!empty($options) ? $options : FALSE),
    '#empty' => t('No aliases available.'),
  );
  $form['unl_page_aliases']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Delete selected aliases'),
  );
  return $form;
}

/**
 * Form Submit: Existing Page Alias list delete
 */
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

/**
 * Page callback for admin/sites/unl/wdn_registry
 */
function unl_wdn_registry($form, &$form_state) {
  $form['production'] = array(
    '#type' => 'checkbox',
    '#title' => t('This is production.'),
    '#description' => t('If this box checked, sites imported will be marked as imported.'),
    '#default_value' => variable_get('unl_wdn_registry_production'),
  );
  $form['host'] = array(
    '#type' => 'textfield',
    '#title' => t('Host'),
    '#description' => t('Hostname of the WDN Registry database.'),
    '#default_value' => variable_get('unl_wdn_registry_host'),
    '#required' => TRUE,
  );
  $form['username'] = array(
    '#type' => 'textfield',
    '#title' => t('Username'),
    '#description' => t('Username for the WDN Registry database.'),
    '#default_value' => variable_get('unl_wdn_registry_username'),
    '#required' => TRUE,
  );
  $form['password'] = array(
    '#type' => 'password',
    '#title' => t('Password'),
    '#description' => t('Password for the WDN Registry database.'),
    '#required' => TRUE,
  );
  $form['database'] = array(
    '#type' => 'textfield',
    '#title' => t('Database'),
    '#description' => t('Database for the WDN Registry database.'),
    '#default_value' => variable_get('unl_wdn_registry_database'),
    '#required' => TRUE,
  );
  $form['frontier_username'] = array(
    '#type' => 'textfield',
    '#title' => t('Frontier username'),
    '#description' => t('Username to connect to frontier FTP.'),
    '#default_value' => variable_get('unl_frontier_username'),
    '#required' => TRUE,
  );
  $form['frontier_password'] = array(
    '#type' => 'password',
    '#title' => t('Frontier password'),
    '#description' => t('Password to connect to frontier FTP.'),
    '#required' => TRUE,
  );
  $form['submit'] = array(
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
    case 5:
      $installed = t('Failure/Unknown.');
      break;
    case 6:
      $installed = t('Scheduled for site update.');
      break;
    default:
      $installed = t('Unknown');
      break;
  }
  return $installed;
}

/**
 * Page callback for admin/sites/unl/user-audit
 * Presents a form to query what roles (if any) a user has on each site.
 */
function unl_user_audit($form, &$form_state) {
  $form['username'] = array(
    '#type' => 'textfield',
    '#title' => t('Username'),
    '#description' => t('Will match a partial username. For example "smith" will match both jsmith2 and rsmithson1.'),
    '#required' => TRUE,
  );
/*$form['ignore_shared_roles'] = array(
    '#type' => 'checkbox',
    '#title' => 'Ignore Shared Roles',
  );*/
  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Search'),
  );

  // If no user input has been received yet, return the base form.
  if (!isset($form_state['values']) || !$form_state['values']['username']) {
    return $form;
  }

  // Otherwise, since we have a username, we can query the sub-sites and return a list of roles for each.
  $username = $form_state['values']['username'];

  $form['roles'] = _unl_get_user_audit_content($username);

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
    $audit_map[$site_id] = array(
       l($site['uri'], $site['uri']),
       '',
    );
    foreach ($site['roles'] as $role => $user) {
      $audit_map[$site_id][1] .= "$role ";
      $audit_map[$site_id][1] .= ($GLOBALS['user']->name != $username ? "($user)" : '');
      $audit_map[$site_id][1] .= "<br />";
    }
  }

  if (count($audit_map) > 0) {
    $header = array(
      t('Site'),
      t('Role') . ($GLOBALS['user']->name != $username ? ' (' . t('User') . ')' : ''),
    );
    $content = array(
      '#theme' => 'table',
      '#header' => $header,
      '#rows' => $audit_map,
    );
    if ($username == $GLOBALS['user']->name) {
      $content['#caption'] = t('You belong to the following sites as a member of the listed roles.');
    }
    else {
      $content['#caption'] = t('Users matching "@user" belong to the following sites as a member of the listed roles.', array('@user' => $username));
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
      $content['#title'] = t('User matching "@user" does not belong to any roles on any sites.', array('@user' => $username));
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
 * Page callback for admin/sites/unl/feed
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

      // If the site uses shared roles, ignore it if the user wants us to.
    /*if ($roles_are_shared && $form_state['values']['ignore_shared_roles']) {
        continue;
      }*/

      $bound_params = array();
      $where = array();

      if ($search_by == 'username') {
        $return_label = 'roles';
        $select = 'r.name, u.name';
        $where[] = 'u.name LIKE :name';
        $bound_params[':name'] = "%".$username_or_role."%";
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

      $role_user = db_query($query, $bound_params)->fetchAllKeyed();

      if (count($role_user) == 0 && !$list_empty_sites) {
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
        $return_label => $role_user,
      );
    } catch (Exception $e) {
      // Either the site has no settings.php or the db_prefix is wrong.
      drupal_set_message('Error querying database for site ' . $site->uri, 'warning');
    }
  }

  return $audit_map;
}
