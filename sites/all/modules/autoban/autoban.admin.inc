<?php

/**
 * @file
 * Configuration for autoban module.
 */

/**
 * Menu callback. Displays autoban rules list.
 */
function autoban_page() {
  $rows = array();
  $header = array(
    array('data' => t('ID'), 'field' => 'rid', 'sort' => 'desc'),
    array('data' => t('Type'), 'field' => 'type'),
    array('data' => t('Message pattern'), 'field' => 'message'),
    array('data' => t('Threshold'), 'field' => 'threshold'),
    array('data' => t('User type'), 'field' => 'user_type'),
    array('data' => t('Referrer'), 'field' => 'referer'),
    array('data' => t('IP type'), 'field' => 'ip_type'),
    t('Actions'),
  );
  $build = array();

  $autoban_list = autoban_get_rules(NULL, $header);
  if (!empty($autoban_list)) {
    foreach ($autoban_list as $rule) {
      //Actions
      $actions = array();
      $actions[] = l(t('Edit'), AUTOBAN_BASE_URL . "/edit/$rule->rid");
      $actions[] = l(t('Delete'), AUTOBAN_BASE_URL . "/delete/$rule->rid");

      $options = array();
      $options['query'] = array('type' => $rule->type, 'message' => $rule->message, drupal_get_destination());
      $actions[] = l(t('Clone'), AUTOBAN_BASE_URL . "/add", $options);
      $actions[] = l(t('Export'), AUTOBAN_BASE_URL . "/export/$rule->rid");
      $actions[] = l(t('Test'), AUTOBAN_BASE_URL . "/test/$rule->rid");

      $rows[] = array(
        $rule->rid,
        filter_xss($rule->type),
        filter_xss($rule->message),
        $rule->threshold,
        _autoban_get_user_type_name($rule->user_type),
        filter_xss($rule->referer),
        _autoban_get_ip_type_name($rule->ip_type),
        implode(' ', $actions),
      );
    }
  }

  $build['autoban_form'] = drupal_get_form('autoban_form');
  $build['autoban_table'] = array(
    '#theme' => 'table',
    '#header' => $header,
    '#rows' => $rows,
    '#empty' => t('Table has no rows!'),
  );
  return $build;
}

/**
 * Menu callback. Test autoban rule page.
 *
 * @param int|string $rid
 *   Autoban rule id.
 */
function autoban_test($rid) {
  $backlink = isset($_GET['destination']) ? $_GET['destination'] : AUTOBAN_BASE_URL;
  $build = array();
  $build['backlink'] = array(
    '#type' => 'markup',
    '#markup' => l(t('Back'), $backlink),
  );

  if ($rid == 'direct') {
    // Test for direct type and message.
    $params = drupal_get_query_parameters();
    if (!isset($params['type']) || !isset($params['message'])) {
      drupal_set_message(t('Wrong parameters'), 'error');
      return $build;
    }

    // Create rule object manually.
    $rule = new stdClass();
    $rule->type = filter_xss($params['type']);
    $rule->message = filter_xss($params['message']);
    $rule->threshold = 1;
    $rule->user_type = 0;
    $rule->ip_type = 0;
    $rule->referer = '';
  }
  else {
    $rule = autoban_get_rules($rid);
  }

  if (!empty($rule)) {
    $params = t("Parameters: Type='%type' Message like '%message' Threshold>=%threshold User type=%user_type IP type=%ip_type Referer like %referer.",
      array(
        '%type' => filter_xss($rule->type),
        '%message' => filter_xss($rule->message),
        '%threshold' => $rule->threshold,
        '%user_type' => _autoban_get_user_type_name($rule->user_type),
        '%ip_type' => _autoban_get_ip_type_name($rule->ip_type),
        '%referer' =>  filter_xss($rule->referer),
      ));

    $build['parameters'] = array(
      '#type' => 'markup',
      '#markup' => '<h2>' . $params . '</h2>',
    );

    $rows = array();
    $header = array(
      '#',
      array('data' => t('IP'), 'field' => 'hostname'),
      array('data' => t('Count'), 'field' => 'hcount'),
      t('IP for ban'), t('Hostname'), t('Location'), t('Status'), t('Actions'),
    );

    // Hostnames(IP) list from watchdog table using the rule.
    $hostnames = autoban_get_hostnames($rule, $header);
    if (!empty($hostnames)) {
      $large_list = count($hostnames) > variable_get('autoban_large_list', 150);
      $ind = 0;
      unset($_GET['destination']);
      $options = array('query' => drupal_get_destination());
      foreach ($hostnames as $hostname) {
        $banned_ip = autoban_make_ip_style($hostname->hostname, $rule->ip_type);

        $ip_location = autoban_get_ip_location($hostname->hostname);
        $real_hostname = gethostbyaddr($hostname->hostname);

        $ip_status = '';
        if (_autoban_is_own_ip($banned_ip)) {
          $ip_status = t('Own IP address');
        }
        else if (autoban_whitelist_ip($banned_ip)) {
          $ip_status = t('In whitelist');
        }

        if (!$large_list) {
          // For large lists, this operation can significantly reduce performance. 
          $is_banned = autoban_is_banned($hostname->hostname, $rule->ip_type);
          if ($is_banned) {
            $ip_status = t('Banned');
          }
        }
        else {
          $ip_status = t('Undefined');
        }

        $srows = array();
        $srows[] = l(t(_autoban_get_ip_type_name(AUTOBAN_SINGLE_IP) . ' ban'),
          AUTOBAN_BASE_URL . "/ban/{$hostname->hostname}/" . AUTOBAN_SINGLE_IP, $options);
        if ($GLOBALS['AUTOBAN_IP_RANGES_ENABLE']) {
          $srows[] = l(t(_autoban_get_ip_type_name(AUTOBAN_RANGE_IP) . ' ban'),
            AUTOBAN_BASE_URL . "/ban/{$hostname->hostname}/" . AUTOBAN_RANGE_IP, $options);
        }

        $rows[] = array(
          ++$ind,
          $hostname->hostname,
          $hostname->hcount,
          $banned_ip,
          $real_hostname ? $real_hostname : '',
          $ip_location,
          $ip_status,
          implode(' ', $srows),
        );
      }

      $build['autoban_ban_form'] = drupal_get_form('autoban_ban_form', $rule);
    }

    $build['autoban_table'] = array(
      '#theme' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => t('Table has no rows!'),
    );
  }
  else {
    drupal_set_message(t('Empty rules list for id=@id.', array('@id' => $rid)), 'error');
  }
  return $build;
}

/**
 * Defines the form for autoban rules.
 *
 * @param int $rid
 *   (optional) Autoban rule id.
 *
 * @ingroup forms
 * @see autoban_form_submit()
 */
function autoban_form($form, $form_state, $rid = NULL) {
  if ($rid == NULL && current_path() == AUTOBAN_BASE_URL) {
    $form['autoban'] = array(
      '#type' => 'fieldset',
      '#title' => t('Add autoban rule'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    );
  };

  $params = drupal_get_query_parameters();

  $form['autoban']['type'] = array(
    '#title' => t('Type'),
    '#type' => 'textfield',
    '#size' => 32,
    '#required' => FALSE,
    '#maxlength' => 64,
    '#default_value' => isset($params['type']) ? filter_xss($params['type']) : '',
    '#description' => t('Type of <a href="/admin/reports/dblog">log message</a>, for example "access denied" or "page not found".'),
  );
  $form['autoban']['message'] = array(
    '#title' => t('Message'),
    '#type' => 'textfield',
    '#size' => 64,
    '#required' => FALSE,
    '#maxlength' => 255,
    '#default_value' => isset($params['message']) ? filter_xss($params['message']) : '',
    '#description' => t('<a href="/admin/reports/dblog">Log message</a> pattern. Use delimiter "!delimiter" for multiple values.',
    array('!delimiter' => AUTOBAN_MESSAGE_SEPARATOR)),
  );

  $form['autoban']['threshold'] = array(
    '#title' => t('Threshold'),
    '#type' => 'select',
    '#multiple' => FALSE,
    '#options' => drupal_map_assoc(_autoban_get_thresholds()),
    '#required' => TRUE,
    '#description' => t('The threshold number of the <a href="/admin/reports/dblog">log entries</a>.'),
  );
  $form['autoban']['user_type'] = array(
    '#title' => t('User type'),
    '#type' => 'select',
    '#multiple' => FALSE,
    '#options' => _autoban_get_user_type_name(),
    '#default_value' => 0,
    '#required' => TRUE,
    '#description' => t('Choose user type.'),
  );
  $form['autoban']['referer'] = array(
    '#title' => t('Referrer'),
    '#type' => 'textfield',
    '#size' => 64,
    '#required' => FALSE,
    '#maxlength' => 255,
    '#default_value' => isset($params['referer']) ? filter_xss($params['referer']) : '',
    '#description' => t('URL of referring page pattern. Use delimiter "!delimiter" for multiple values.',
     array('!delimiter' => AUTOBAN_MESSAGE_SEPARATOR)),
  );
  $form['autoban']['ip_type'] = array(
    '#title' => t('IP type'),
    '#type' => 'select',
    '#multiple' => FALSE,
    '#options' => _autoban_get_ip_type_names_list(),
    '#default_value' => 0,
    '#required' => TRUE,
    '#description' => t('Choose IP type.'),
  );
  $form['rule_id'] = array(
    '#type' => 'value',
    '#value' => $rid,
  );

  $form['autoban']['actions'] = array('#type' => 'actions');

  if ($rid != NULL) {
    // Edit rule.
    $rule = autoban_get_rules($rid);
    $form['autoban']['type']['#default_value']     = filter_xss($rule->type);
    $form['autoban']['message']['#default_value']  = filter_xss($rule->message);
    $form['autoban']['threshold']['#default_value'] = (int)$rule->threshold;
    $form['autoban']['user_type']['#default_value'] = $rule->user_type;
    $form['autoban']['referer']['#default_value'] = filter_xss($rule->referer); 
    $form['autoban']['ip_type']['#default_value']  = $rule->ip_type;

    $form['autoban']['actions']['save'] = array(
      '#type' => 'submit',
      '#value' => t('Edit'),
    );
    $form['autoban']['actions']['cancel'] = array(
      '#type' => 'link',
      '#title' => t('Cancel'),
      '#href' => isset($_GET['destination']) ? $_GET['destination'] : AUTOBAN_BASE_URL,
    );
  }
  else {
    // Add rule.
    $form['autoban']['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Add'),
    );

    if (current_path() != AUTOBAN_BASE_URL) {
      $form['autoban']['actions']['cancel'] = array(
        '#type' => 'link',
        '#title' => t('Cancel'),
        '#href' => isset($_GET['destination']) ? $_GET['destination'] : AUTOBAN_BASE_URL,
      );
    }
  }

  return $form;
}

/**
 * Form validation for autoban_form().
 */
function autoban_form_validate($form, &$form_state) {
  $type = filter_xss(trim($form_state['values']['type']));
  $message = filter_xss(trim($form_state['values']['message']));
  if (empty($type) && empty($message)) {
    form_set_error('type', t('Type and Message can not both be empty.'));
    form_set_error('message');
  }

  $threshold = intval($form_state['values']['threshold']);
  if ($threshold < 1) {
    form_set_error('threshold', t('Threshold value must be positive integer value.'));
  }
}

/**
 * Form submission for autoban_form().
 */
function autoban_form_submit($form, &$form_state) {
  $type = filter_xss(trim($form_state['values']['type']));
  $message = filter_xss(trim($form_state['values']['message']));
  $threshold = intval($form_state['values']['threshold']);
  $user_type = $form_state['values']['user_type'];
  $referer = $form_state['values']['referer'];
  $ip_type = $form_state['values']['ip_type'];
  $rid = $form_state['values']['rule_id'];

  $fields = array(
    'type' => $type,
    'message' => $message,
    'threshold' => $threshold,
    'user_type' => $user_type,
    'referer' => $referer,
    'ip_type' => $ip_type,
  );

  if ($rid) {
    // Update record.
    db_update('autoban')
      ->fields($fields)
      ->condition('rid', $rid)
      ->execute();
    $message = t('Autoban rule @rid was updated.', array('@rid' => $rid));
  }
  else {
    // Insert record.
    db_insert('autoban')
      ->fields($fields)
      ->execute();
    $message = t('New autoban rule was inserted.');
  }
  drupal_set_message($message);

  // Redirect to autoban base url.
  if (current_path() != AUTOBAN_BASE_URL) {
    $form_state['redirect'] = AUTOBAN_BASE_URL;
  }

  // Reset stored rules.
  drupal_static_reset('autoban_get_rules');
}

/**
 * Autoban rule deletion confirm page.
 *
 * @see autoban_delete_submit()
 */
function autoban_delete($form, &$form_state, $rid) {
  $form['rule_id'] = array(
    '#type' => 'value',
    '#value' => $rid,
  );
  return confirm_form($form,
           t('Are you sure you want to delete Autoban rules %id?', array('%id' => $rid)),
           AUTOBAN_BASE_URL,
           t('This action cannot be undone.'),
           t('Delete'),
           t('Cancel')
         );
}

/**
 * Processes autoban_delete form submissions.
 */
function autoban_delete_submit($form, &$form_state) {
  $rid = $form_state['values']['rule_id'];
  if (!empty($rid)) {
    db_delete('autoban')
      ->condition('rid', $rid)
      ->execute();

    watchdog('autoban', 'Deleted %id from autoban rules.', array('%id' => $rid));
    drupal_set_message(t('The autoban rules %id was deleted.', array('%id' => $rid)));

    // Reset stored rules.
    drupal_static_reset('autoban_get_rules');

    $form_state['redirect'] = AUTOBAN_BASE_URL;
  }
}

/**
 * Ban IP's for all autoban rule.
 *
 * @see autoban_ban_all_form_submit()
 */
function autoban_ban_all_form($form, &$form_state) {
  $form['ban_all'] = array(
    '#title' => t('Ban IP for all rules'),
  );
  $form['ban_all']['actions'] = array('#type' => 'actions');
  $form['ban_all']['actions']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Ban all IPs'),
    '#suffix' => t('This action will automatically by cron. Your own IP @ip cannot be blocked.', array('@ip' => ip_address())),
  );
  return $form;
}

/**
 * Form submission for autoban_ban_all_form().
 */
function autoban_ban_all_form_submit($form, &$form_state) {
  // Stored number banned IP to session.
  $_SESSION['autoban_ban_all_count'] = 0;

  $rules = autoban_get_rules();
  if (!empty($rules)) {
    $operations = array();
    foreach ($rules as $rule) {
      $operations[] = array('autoban_ban', array($rule));
    }

    $batch = array(
      'operations' => $operations,
      'finished' => 'autoban_ban_all_batch_finished',
      'title' => t('IP ban'),
      'file' => drupal_get_path('module', 'autoban') . '/autoban.admin.inc',
    );
    batch_set($batch);
  }

  $form_state['redirect'] = AUTOBAN_BASE_URL;
}

/**
 * Clear tables.
 *
 * @see autoban_clear_tables_form_submit()
 */
function autoban_clear_tables_form($form, &$form_state) {
  $form['clear_tables'] = array(
    '#title' => t('Clear tables'),
  );

  $tables_list = array(
    'autoban' => t('Autoban rules'),
    'watchdog' => t('Log messages'),
    'blocked_ips' => t('IP address blocking'),
  );
  if ($GLOBALS['AUTOBAN_BLOCKED_IPS_EXPIRE_ENABLE']) {
    $tables_list['blocked_ips'] .= ' (' . t('with blocked_ips_expire') . ')'; 
  }
  if ($GLOBALS['AUTOBAN_IP_RANGES_ENABLE']) {
    $tables_list['ip_ranges'] = t('IP range bans (blacklist)');
  }
  $form['clear_tables']['tables'] = array(
    '#title' => t('Tables'),
    '#type' => 'checkboxes',
    '#options' => $tables_list,
    '#required' => TRUE,
  );
  $form['clear_tables']['actions'] = array('#type' => 'actions');
  $form['clear_tables']['actions']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Clear'),
  );
  return $form;
}

/**
 * Form submission for autoban_clear_tables_form().
 */
function autoban_clear_tables_form_submit($form, &$form_state) {
  foreach ($form_state['values']['tables'] as $table) {
    if ($table) {
      $query = db_delete($table);
      if ($table == 'ip_ranges') {
        $query->condition('type', 'blacklist', '=');
      }
      $query->execute();
      if ($table == 'autoban') {
        drupal_static_reset('autoban_get_rules');
      }

      if ($table == 'blocked_ips' && $GLOBALS['AUTOBAN_BLOCKED_IPS_EXPIRE_ENABLE']) {
        $query = db_delete('blocked_ips_expire');
        $query->execute();
      }

      drupal_set_message(t('Table %table was deleted.', array('%table' => $table)));
    }
  }

  $form_state['redirect'] = AUTOBAN_BASE_URL;
}

/**
 * Callback batch finished for ban all batch.
 *
 * @param bool $success
 *   A boolean indicating whether the batch has completed successfully.
 * @param array $results
 *   The value set in $context['results'] by callback_batch_operation().
 * @param array $operations
 *   If $success is FALSE, contains the operations that remained unprocessed.
 */
function autoban_ban_all_batch_finished($success, $results, $operations) {
  if ($success) {
    drupal_set_message(t('Successful IP ban.'));
    if (isset($_SESSION['autoban_ban_all_count'])) {
      drupal_set_message(t('IP ban for all rules has finished. Total IP insertion: @count.',
        array('@count' => $_SESSION['autoban_ban_all_count'])));
    }
  }
  else {
    drupal_set_message(t('Fail IP ban'), 'error');
  }
}

/**
 * Ban IP's for one autoban rule.
 *
 * @see autoban_ban_form_submit()
 */
function autoban_ban_form($form, &$form_state, $rule) {
  $form = array();
  $form['rule'] = array(
    '#type' => 'value',
    '#value' => $rule,
  );
  $form['actions'] = array('#type' => 'actions');
  $form['actions']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Ban all IPs'),
    '#suffix' => t('Ban all IPs from the list by inserting to banned IP tables.'),
  );
  return $form;
}

/**
 * Form submission for autoban_ban_form().
 */
function autoban_ban_form_submit($form, &$form_state) {
  $rule = $form_state['values']['rule'];
  if (!empty($rule)) {
    $count = autoban_ban($rule);
    drupal_set_message(t('IP ban for the rule has finished. Total IP insertion: @count.',
      array('@count' => $count)));
  }
}

/**
 * Menu callback. Analize watchdog.
 */
function autoban_analyze() {
  // Default value.
  $threshold = 5;
  if (!empty($_SESSION['autoban']['analyze']['threshold'])) {
    $threshold = $_SESSION['autoban']['analyze']['threshold'];
  }

  $header = array(
    '#',
    array('data' => t('Count'), 'field' => 'cnt', 'sort' => 'desc'),
    array('data' => t('Type'), 'field' => 'type'),
    array('data' => t('Message'), 'field' => 'message'),
    array('data' => t('Referrer'), 'field' => 'referer'),
    t('Actions'),
  );

  $query = db_select('watchdog', 'log')
    ->fields('log', array('type', 'message', 'referer'))
    ->groupBy('log.message');

  $query->condition('log.type', autoban_get_log_whitelist(), 'NOT IN');
  $query->addExpression('COUNT(*)', 'cnt');
  $query->havingCondition('cnt', $threshold, '>=');
  $query->extend('TableSort')->orderByHeader($header);
  $log = $query->execute()->fetchAll();

  $rows = array();
  if (count($log)) {
    $ind = 0;
    foreach ($log as $item) {
      $options = array();
      $options['query'] = array(
        'type' => $item->type,
        'message' => filter_xss($item->message),
        drupal_get_destination(),
      );

      $newitem = array(
        ++$ind,
        l($item->cnt, AUTOBAN_BASE_URL . '/test/direct', $options),
        $item->type,
        filter_xss($item->message),
        _autoban_short_referer($item->referer),
      );

      $actions = array();
      $actions[] = l(t('Add rule'), AUTOBAN_BASE_URL . '/add', $options);
      $actions[] = l(t('Test'), AUTOBAN_BASE_URL . '/test/direct', $options);
      $newitem['action'] = implode(' ', $actions);

      $rows[] = $newitem;
    }
  }

  $build = array();
  $params = t("Parameters: Threshold>=%threshold.", array('%threshold' => $threshold));
  $build['parameters'] = array(
    '#type' => 'markup',
    '#markup' => '<h2>' . $params . '</h2>',
  );

  $build['autoban_ban_all_form'] = drupal_get_form('autoban_analyze_form', $threshold);
  $build['autoban_table'] = array(
    '#theme' => 'table',
    '#header' => $header,
    '#rows' => $rows,
    '#empty' => t('Table has no rows!'),
  );

  return $build;
}

/**
 * Set threshold for log analize table.
 *
 * @see autoban_ban_all_form_submit()
 */
function autoban_analyze_form($form, &$form_state, $threshold) {
  $form['analyze'] = array(
    '#type' => 'fieldset',
    '#title' => t('Threshold'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
  );
  $form['analyze']['threshold'] = array(
    '#title' => t('Threshold'),
    '#type' => 'select',
    '#multiple' => FALSE,
    '#options' => drupal_map_assoc(_autoban_get_thresholds()),
    '#required' => TRUE,
    '#default_value' => $threshold,
    '#description' => t('The threshold number of the equal <a href="/admin/reports/dblog">log entries</a>.'),
  );

  $form['analyze']['actions'] = array('#type' => 'actions');
  $form['analyze']['actions']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Set'),
  );
  return $form;
}

/**
 * Form submission for autoban_analize_form().
 */
function autoban_analyze_form_submit($form, &$form_state) {
  $threshold = $form_state['values']['threshold'];
  $_SESSION['autoban']['analyze']['threshold'] = $threshold;
  drupal_set_message(t('The analyze threshold is set to %threshold',
    array('%threshold' => $threshold)));
}

/**
 * Export autoban rules.
 */
function autoban_export_form($form, &$form_state) {
  $form = array();
  $export = array();
  // Rules ID
  $rid = arg(5) ? arg(5) : NULL;
  $rules = autoban_get_rules($rid);
  if (!empty($rules)) {
    if ($rid) {
      $rules = array($rules);
    }
    foreach ($rules as $rule) {
      $rule_arr = (array) $rule;
      unset($rule_arr['rid']);
      $export[] .= autoban_var_json_export($rule_arr);
    }
  }

  $form['export'] = array(
    '#title' => t('Export'),
    '#type' => 'textarea',
    '#rows' => 10,
    '#default_value' => implode(AUTOBAN_EXPORT_SEPARATOR, $export),
    '#description' => t('For importing copy the content of the text area and paste it into the import page.'),
  );

  return $form;
}

/**
 * Import autoban rules.
 *
 * @see import_form_submit()
 */
function autoban_import_form($form, &$form_state) {
  $form = array();
  $form['import'] = array(
    '#title' => t('Import'),
    '#type' => 'textarea',
    '#rows' => 10,
    '#description' => t('Copy the content of the text area from export page.'),
  );
  $form['clear_before'] = array(
    '#title' => t('Clear before insertion'),
    '#type' => 'checkbox',
    '#default_value' => FALSE,
  );

  $form['actions'] = array('#type' => 'actions');
  $form['actions']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Import'),
  );

  return $form;
}

/**
 * Form submission for autoban_import_form().
 */
function autoban_import_form_submit($form, &$form_state) {
  $import =trim($form_state['values']['import']);
  if (!empty($import)) {
    $vars = explode(AUTOBAN_EXPORT_SEPARATOR, $import);
    if (count($vars)) {
      $errors = $imported = 0;
      $import_rows = array();
      foreach ($vars as $item) {
        $var = drupal_json_decode($item);
        if ($var) {
          $imported++;
          $import_rows[] = $var;
        }
        else {
          $errors++;
        }
      }
      if ($imported > 0) {
        drupal_set_message(t('Import checked rows: @imported.', array('@imported' => $imported)));
      }
      if ($errors) {
        drupal_set_message(t('Import errors: @errors.', array('@errors' => $errors)), 'error');
        drupal_set_message(t('Import was failed.'), 'error');
      }
      else {
        $clear_before = $form_state['values']['clear_before'];
        if ($clear_before) {
          drupal_set_message('Autoban rules table was cleared.');
          db_delete('autoban')->execute();
        }
        if (count($import_rows)) {
          foreach ($import_rows as $fields) {
            db_insert('autoban')
              ->fields($fields)
              ->execute();
          }
          drupal_set_message(t('Import was finished.'));
        }
      }
    }
  }
}

/**
 * Autoban Settings form.
 */
function autoban_settings_form($form, &$form_state) {
  $form['autoban_cron_enable'] = array(
    '#type' => 'checkbox',
    '#title' => t('Cron mode'),
    '#default_value' => variable_get('autoban_cron_enable', TRUE),
    '#description' => t('If checked, Autoban will enabled IP ban by cron.'),
  );
  $form['autoban_force_enable'] = array(
    '#type' => 'checkbox',
    '#title' => t('Forced mode'),
    '#default_value' => variable_get('autoban_force_enable', FALSE),
    '#description' => t('If checked, Autoban will do IP ban by every watchdog events.'),
  );
  $form['autoban_thresholds'] = array(
    '#type' => 'textfield',
    '#title' => t('Thresholds'),
    '#required' => TRUE,
    '#default_value' => variable_get('autoban_thresholds', '1,2,3,5,10,20,50,100,200,500,1000'),
    '#description' => t('Thresholds list, comma delimited.'),
  );
  $form['autoban_dblog_type_exclude'] = array(
    '#type' => 'textfield',
    '#title' => t('Exclude dblog types'),
    '#default_value' => variable_get('autoban_dblog_type_exclude', 'autoban,cron,php,system,user'),
    '#description' => t('Exclude dblog types events from ban, analyze and force mode.'),
  );
  $form['autoban_whitelist'] = array(
    '#type' => 'textarea',
    '#title' => t('IP addresses whitelist'),
    '#default_value' => variable_get('autoban_whitelist', ''),
    '#description' => t('Enter a list of IP addresses. Format: CIDR "aa.bb.cc.dd/ee" or "aa.bb.cc.dd". # symbol use as comment.'
       . 'For example: <a href="https://www.ip2location.com/free/robot-whitelist" rel="nofollow" target="_new">robot-whitelist site</a>.'),
    '#rows' => 10,
    '#cols' => 30,
  );
  $form['autoban_biglist'] = array(
    '#type' => 'select',
    '#title' => t('Number rows for large lists.'),
    '#multiple' => FALSE,
    '#options' => drupal_map_assoc(array(50,100,150,200,500,1000,1000000)),
    '#default_value' => variable_get('autoban_large_list', 150),
    '#description' => t('The number of rows for which the list is large. To speed up, large lists have limited functionality.'),
  );

  return system_settings_form($form);
}
