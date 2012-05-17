<?php

function unl_config($form, &$form_state) {

  $form['root'] = array(
    '#tree' => TRUE,
    '#type' => 'fieldset',
    '#title' => 'UNL Settings',
  );

  $form['root']['unl_use_base_tag'] = array(
    '#type' => 'checkbox',
    '#title' => 'Use base tag',
    '#description' => 'Insert the HTML Base tag in the head of all pages on this site.',
    '#default_value' => variable_get('unl_use_base_tag', TRUE),
    '#disabled' => !unl_user_is_administrator(),
  );

  $form['root']['unl_clean_file_url'] = array(
    '#type' => 'checkbox',
    '#title' => 'Clean file URLs',
    '#description' => 'Enable this once mod_rewrite has been set up to support clean file URLs.',
    '#default_value' => variable_get('unl_clean_file_url'),
    '#disabled' => !unl_user_is_administrator(),
  );

  if (class_exists('Tidy')) {
    $form['root']['unl_tidy'] = array(
      '#type' => 'checkbox',
      '#title' => 'Clean up text area HTML',
      '#description' => 'Tidy will be used to process HTML in text areas on the node edit form. (This will remove any &lt;style&gt; tags.)',
      '#default_value' => variable_get('unl_tidy'),
    );
  }

  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => 'Update',
  );
  
  // Grab a list of available modules (active or not).
  // Based on system_modules() from modules/system.admin.inc.
  $files = system_rebuild_module_data();
  require_once DRUPAL_ROOT . '/modules/system/system.admin.inc';
  uasort($files, 'system_sort_modules_by_info_name');
  
  $modules = array();
  foreach ($files as $filename => $file) {
    if (!empty($file->info['hidden'])) {
      continue;
    }
    $modules[$filename] = $file->info['name'];
  }
  
  $form['root']['module_whitelist'] = array(
    '#type' => 'select',
    '#title' => 'Module White List (Global)',
    '#multiple' => TRUE,
    '#options' => $modules,
    '#size' => 15,
    '#default_value' => unl_shared_variable_get('unl_module_whitelist', array()),
  );

  return $form;
}

function unl_config_submit($form, &$form_state) {
  variable_set('unl_use_base_tag', $form_state['values']['root']['unl_use_base_tag']);
  variable_set('unl_clean_file_url', $form_state['values']['root']['unl_clean_file_url']);
  if (class_exists('Tidy')) {
    variable_set('unl_tidy', $form_state['values']['root']['unl_tidy']);
  }
  variable_set('unl_module_whitelist', $form_state['values']['root']['module_whitelist']);
}
