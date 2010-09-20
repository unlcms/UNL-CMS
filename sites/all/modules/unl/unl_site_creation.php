<?php

require_once DRUPAL_ROOT . '/includes/install.core.inc';

function unl_site_creation_page()
{
    return drupal_get_form('unl_site_creation');
}


function unl_site_creation($form, &$form_state)
{
    $form['root'] = array(
        '#type' => 'fieldset',
        '#title' => 'Site Creation Tool'
    );
    
    $form['root']['site_path_prefix'] = array(
        '#type' => 'textfield',
        '#title' => t('Site path prefix'),
        '#description' => t('A URL path used to separate subsites from the main site.'),
        '#default_value' => t('s')
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
    $php_path = $form_state['values']['php_path'];
    $site_path = $form_state['values']['site_path'];
    $site_path_prefix = $form_state['values']['site_path_prefix'];
    $clean_url = $form_state['values']['clean_url'];
    
    if (substr($site_path, 0, 1) == '/') {
        $site_path = substr($site_path, 1);
    }
    if (substr($site_path, -1) == '/') {
        $site_path = substr($site_path, 0, -1);
    }
    if (substr($site_path_prefix, 0, 1) == '/') {
        $site_path_prefix = substr($site_path_prefix, 1);
    }
    if (substr($site_path_prefix, -1) == '/') {
        $site_path_prefix = substr($site_path_prefix, 0, -1);
    }
    
    $full_path = $site_path;
    if ($site_path_prefix) {
        $full_path = $site_path_prefix . '/' . $full_path;
    }
    
    $uri = url($full_path, array('absolute' => TRUE));
    
    $clean_url = intval($clean_url);
    
    db_insert('unl_sites')->fields(array(
    	'site_path_prefix' => $site_path_prefix,
        'site_path'        => $site_path,
        'uri'              => $uri,
        'clean_url'        => $clean_url
    ))->execute();
    
    exit;
}