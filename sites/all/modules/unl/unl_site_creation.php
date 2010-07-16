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
    
    $form['root']['php_path'] = array(
        '#type' => 'textfield',
        '#title' => t('PHP Path'),
        '#description' => t('Full Path to the server\'s PHP binary'),
        '#default_value' => t('/usr/bin/php'),
        '#required' => TRUE
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
    $path_parts = parse_url($uri);
    $sites_subdir = $path_parts['host'] . $path_parts['path'];
    $sites_subdir = strtr($sites_subdir, array('/' => '.')); 
    
    
    $database = $GLOBALS['databases']['default']['default'];
    $db_url = $database['driver']
            . '://' . $database['username']
            . ':'   . $database['password']
            . '@'   . $database['host']
            . ($database['port'] ? ':' . $database['port'] : '') 
            . '/'   . $database['database']
            ;
    $db_prefix = explode('/', $site_path);
    $db_prefix = array_reverse($db_prefix);
    $db_prefix = implode('_', $db_prefix) . '_' . $database['prefix'];
    
    
    $php_path = escapeshellarg($php_path);
    $drupal_root = escapeshellarg(DRUPAL_ROOT);
    $uri = escapeshellarg($uri);
    $sites_subdir = escapeshellarg($sites_subdir);
    $db_url = escapeshellarg($db_url);
    $db_prefix = escapeshellarg($db_prefix);
   
    $command = "$php_path sites/all/modules/drush/drush.php --uri=$uri site-install unl_profile --sites-subdir=$sites_subdir --db-url=$db_url --db-prefix=$db_prefix";
    
    header('Content-Type: text/plain');
    echo 'Run the following commands on the drupal server:' . PHP_EOL;
    echo 'cd ' . $drupal_root . PHP_EOL;
    echo $command . PHP_EOL;
    
    
    exit;
}