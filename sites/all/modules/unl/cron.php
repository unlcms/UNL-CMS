<?php

if (PHP_SAPI != 'cli') {
    echo 'This script must be run from the shell!';
    exit;
}

chdir(dirname(__FILE__) . '/../../../..');
define('DRUPAL_ROOT', getcwd());

require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
drupal_override_server_variables();
drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);

$query = db_query('SELECT * FROM {unl_sites} WHERE installed=0');
while ($row = $query->fetchAssoc()) {
    db_update('unl_sites')
        ->fields(array('installed' => 1))
        ->condition('site_id', $row['site_id'])
        ->execute();
    unl_add_site($row['site_path_prefix'], $row['site_path'], $row['uri'], $row['clean_url']);
    db_update('unl_sites')
        ->fields(array('installed' => 2))
        ->condition('site_id', $row['site_id'])
        ->execute();
}


function unl_add_site($site_path_prefix, $site_path, $uri, $clean_url)
{
    
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
    
    $php_path = escapeshellarg($_SERVER['_']);
    $drupal_root = escapeshellarg(DRUPAL_ROOT);
    $uri = escapeshellarg($uri);
    $sites_subdir = escapeshellarg($sites_subdir);
    $db_url = escapeshellarg($db_url);
    $db_prefix = escapeshellarg($db_prefix);
    
    $subdir = explode('/', $full_path);
    $symlink_name = array_pop($subdir);
    $subdir_levels = count($subdir);
    $subdir = implode('/', $subdir);
    
    $symlink_target = array();
    for ($i = 0; $i < $subdir_levels; $i++) {
        $symlink_target[] = '..';
    }
    $symlink_target = implode('/', $symlink_target);
    
    $command = "$php_path sites/all/modules/drush/drush.php -y --uri=$uri site-install unl_profile --sites-subdir=$sites_subdir --db-url=$db_url --db-prefix=$db_prefix --clean-url=$clean_url";
    
    mkdir($subdir, 0755, TRUE);
    symlink($symlink_target, $subdir . '/' . $symlink_name);
    shell_exec($command);
}