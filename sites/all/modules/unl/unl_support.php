<?php

function unl_support($form, &$form_state) {
  $form['root'] = array(
    '#title' => 'UNLcms support form',
  );
   $form['root']['cas_username'] = array(
    '#type' => 'textfield',
    '#title' => t('Username'),
    '#value' => ($GLOBALS['user']->name),
    '#disabled' => 'disabled',
  );
  $form['root']['email'] = array(
    '#type' => 'textfield',
    '#title' => t('Email'),
    '#value' => ($GLOBALS['user']->mail),
    '#disabled' => 'disabled',
  );
  $form['root']['browser_useragent'] = array(
    '#type' => 'textfield',
    '#title' => t('Browser UserAgent'),
    '#value' => $_SERVER['HTTP_USER_AGENT'],
    '#disabled' => 'disabled',
    '#size' => 120,
  );
  $form['root']['site'] = array(
    '#type' => 'textfield',
    '#title' => t('Site'),
    '#value' => $GLOBALS['base_root'] . (isset($GLOBALS['base_path']) ? $GLOBALS['base_path'] : ''),
    '#disabled' => 'disabled',
    '#size' => 120,
  );
  $form['root']['current_url'] = array(
    '#type' => 'textfield',
    '#title' => t('Page address in question'),
    '#value' => $_SERVER['HTTP_REFERER'],
    '#size' => 120,
  );
  $form['root']['technical_feedback'] = array(
    '#type' => 'textarea',
    '#title' => t('Your feedback or the issue you are having'),
    '#required' => TRUE,
  );
  $form['root']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Submit'),
  );

  return $form;
}

function unl_support_submit($form, &$form_state) {
  $to = 'mysupport@unl.edu';
  $subject = 'UNLcms: ' . substr($form_state['values']['technical_feedback'], 0, 44) . '...';

  $message = <<< EOF
contact={$form_state['values']['email']}
assignees="UNLCMS and Web Support"

{$form_state['values']['technical_feedback']}

Requestor: {$form_state['values']['cas_username']}
Email: {$form_state['values']['email']}
UserAgent: {$form_state['values']['browser_useragent']}
Site: {$form_state['values']['site']}
Page: {$form_state['input']['current_url']}

(This request was sent from a UNLcms support form at {$form_state['values']['site']}user/unl/support)
EOF;

  $headers = "From: mysupportform@unl.edu\n"
           . "MIME-Version: 1.0\n"
           . "Content-type:text/plain; charset=UTF-8\n";

  mail($to, $subject, $message, $headers);

  drupal_set_message(t('Your message was submitted as a support ticket to MySupport (http://mysupport.unl.edu/) and the UNLcms team was notified.'));

  return;
}
