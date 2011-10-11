<?php

function unl_config($form, &$form_state) {

  $form['root'] = array(
    '#tree' => TRUE,
    '#type'  => 'fieldset',
    '#title' => 'UNL Settings',
  );

  $form['root']['unl_use_base_tag'] = array(
    '#type' => 'checkbox',
    '#title' => 'Use Base Tag',
    '#description' => 'Insert the HTML Base tag in the head of all pages on this site.',
    '#default_value' => variable_get('unl_use_base_tag', TRUE),
  );
  
  $form['root']['unl_clean_file_url'] = array(
    '#type' => 'checkbox',
    '#title' => 'Clean File URLs',
    '#description' => 'Enable this once mod_rewrite has been set up to support clean file URLs',
    '#default_value' => variable_get('unl_clean_file_url'),
  );

  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => 'Update',
  );

  return $form;
}

function unl_config_submit($form, &$form_state) {
  variable_set('unl_use_base_tag', $form_state['values']['root']['unl_use_base_tag']);
  variable_set('unl_clean_file_url', $form_state['values']['root']['unl_clean_file_url']);
}
