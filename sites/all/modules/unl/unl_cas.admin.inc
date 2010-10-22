<?php

function unl_cas_user_import($form, &$form_state) {
  $form['root']['#type'] = 'fieldset';
  $form['root']['#title'] = 'Import from peoplefinder.';
  $form['submit']['#type'] = 'submit';
  $form['submit']['#value'] = 'Search';
  return $form;
}