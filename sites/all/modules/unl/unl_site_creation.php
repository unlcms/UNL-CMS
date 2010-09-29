<?php

require_once DRUPAL_ROOT . '/includes/install.core.inc';

function unl_site_creation($form, &$form_state)
{
    $form['root'] = array(
        '#type' => 'fieldset',
        '#title' => 'Site Creation Tool'
    );
    
    $form['root']['site_path'] = array(
        '#type' => 'textfield',
        '#title' => t('New site path'),
        '#description' => t('Relative url for the new site'),
        '#default_value' => t('newsite'),
        '#required' => TRUE
    );
    
    $form['root']['clean_url'] = array(
        '#type' => 'checkbox',
        '#title' => t('Use clean URLs'),
        '#description' => t('Unless you have some reason to think your site won\'t support this, leave it checked.'),
        '#default_value' => 1
    );
    
    $form['submit'] = array(
        '#type' => 'submit',
        '#value' => 'Create Site'
    );
    
    return $form;
}

function unl_site_creation_submit($form, &$form_state)
{   
    //$php_path = $form_state['values']['php_path'];
    $site_path = $form_state['values']['site_path'];
    $clean_url = $form_state['values']['clean_url'];
    
    if (substr($site_path, 0, 1) == '/') {
        $site_path = substr($site_path, 1);
    }
    if (substr($site_path, -1) == '/') {
        $site_path = substr($site_path, 0, -1);
    }
    
    $uri = url($site_path, array('absolute' => TRUE));
    
    $clean_url = intval($clean_url);
    
    $db_prefix = explode('/', $site_path);
    $db_prefix = implode('_', $db_prefix);
    
    db_insert('unl_sites')->fields(array(
        'site_path' => $site_path,
        'uri'       => $uri,
        'clean_url' => $clean_url,
        'db_prefix' => $db_prefix,
    ))->execute();
    
    drupal_set_message(t('The site '.$uri.' has been started, run unl/cron.php to finish setup.'));
    $form_state['redirect'] = 'admin/sites/unl/add';
    return;
}