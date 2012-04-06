<?php

function unl_technical_feedback($form, &$form_state) {
  $form['root'] = array(
    '#title' => 'UNLcms Technical Feeedback Form',
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
    '#title' => t('Please give your feedback or describe the issue you are having'),
    '#required' => TRUE,
  );

  $form['root']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Submit'),
  );

  return $form;
}

function unl_technical_feedback_submit($form, &$form_state) {
  $to = "unlcms-dev@listserv.unl.edu";
  $from = $form_state['values']['email'];
  $subject = "UNLcms technical feedback from " . $form_state['values']['cas_username'];

  $message = '
Username: '.$form_state['values']['cas_username'].'
Email: '.$form_state['values']['email'].'
UserAgent: '.$form_state['values']['browser_useragent'].'
Site: '.$form_state['values']['site'].'
Page: '.$form_state['input']['current_url'].'
Comment:
'.$form_state['values']['technical_feedback'];

  $headers   = 'From: ' . $form_state['values']['email'] . "\n";
  $headers  .= "MIME-Version: 1.0\n";
  $headers  .= "Content-type:text/plain; charset=UTF-8\n";

  mail($to, $subject, $message, $headers);

  drupal_set_message(t('Your feedback has been emailed to the UNLcms dev team. Thank you!'));

  return;
}
