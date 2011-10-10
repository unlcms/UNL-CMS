<?php

function unl_reset_site($form, &$form_state) {
  $form = array();

  $form['root'] = array(
    '#type' => 'fieldset',
    '#title' => 'Reset Site',
    '#description' => 'WARNING: Performing the following action will permanently remove all content on your site!',
  );

  $form['root']['confirm'] = array(
    '#type' => 'checkbox',
    '#title' => t('Confirm'),
    '#description' => t("I am sure I want to permanently remove all content from this site."),
    '#required' => TRUE,
  );

  $form['root']['confirm_again'] = array(
    '#type' => 'checkbox',
    '#title' => t('Confirm Again'),
    '#description' => t("Yes, I am absolutely sure I want to permanently remove all content from this site."),
    '#required' => TRUE,
  );

  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => 'Reset'
  );
  return $form;
}

function unl_reset_site_submit($form, &$form_state) {

  $nids = db_select('node', 'n')
    ->fields('n', array('nid'))
    ->execute()
    ->fetchCol();
  node_delete_multiple($nids);

  variable_set('site_frontpage', 'node');


  $mlids = db_select('menu_links', 'm')
    ->fields('m', array('mlid'))
    ->condition('m.menu_name', 'main-menu')
    ->execute()
    ->fetchCol();
  foreach ($mlids as $mlid) {
    menu_link_delete($mlid);
  }

  $home_link = array(
    'link_path' => '<front>',
    'link_title' => 'Home',
    'menu_name' => 'main-menu',
    'module' => 'menu',
  );
  menu_link_save($home_link);


  $fids = db_select('file_managed', 'f')
    ->fields('f', array('fid'))
    ->execute()
    ->fetchCol();
  $files = file_load_multiple($fids);
  foreach ($files as $file) {
    file_delete($file);
  }

  $files_dir = DRUPAL_ROOT . '/' . conf_path() . '/files/';
  $cmd = 'rm -rf ' . $files_dir . '*';
  echo shell_exec($cmd);
  drupal_mkdir('public://styles/');

  variable_set('site_name', 'Site Name');

}