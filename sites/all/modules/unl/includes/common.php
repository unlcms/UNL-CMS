<?php

function unl_load_zend_framework() {
  static $isLoaded = FALSE;

  if ($isLoaded) {
    return;
  }

  set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__) . '/../../../libraries');
  require_once 'Zend/Loader/Autoloader.php';
  $autoloader = Zend_Loader_Autoloader::getInstance();
  $autoloader->registerNamespace('Unl_');
  $isLoaded = TRUE;
}

/**
 * Custom function to get the db prefix of the 'main' site.
 */
function unl_get_shared_db_prefix() {
  require 'sites/default/settings.php';
  $shared_prefix = $databases['default']['default']['prefix'];

  return $shared_prefix;
}

/**
 * Custom function.
 */
function unl_shared_variable_get($name, $default = NULL) {
  $shared_prefix = unl_get_shared_db_prefix();
  $data = db_query(
    "SELECT * "
    . "FROM {$shared_prefix}variable "
    . "WHERE name = :name",
    array(':name' => $name)
  )->fetchAll();

  if (count($data) == 0) {
    return $default;
  }

  return unserialize($data[0]->value);
}

/**
 * Given a URI, will return the name of the directory for that site in the sites directory.
 */
function unl_get_sites_subdir($uri, $trim_subdomain = TRUE) {
  $path_parts = parse_url($uri);
  if ($trim_subdomain && substr($path_parts['host'], -7) == 'unl.edu') {
    $path_parts['host'] = 'unl.edu';
  }
  $sites_subdir = $path_parts['host'] . $path_parts['path'];
  $sites_subdir = strtr($sites_subdir, array('/' => '.'));

  while (substr($sites_subdir, 0, 1) == '.') {
    $sites_subdir = substr($sites_subdir, 1);
  }
  while (substr($sites_subdir, -1) == '.') {
    $sites_subdir = substr($sites_subdir, 0, -1);
  }

  return $sites_subdir;
}

/**
 * Given a URI of an existing site, will return settings defined in that site's settings.php
 */
function unl_get_site_settings($uri) {
  $settings_file = DRUPAL_ROOT . '/sites/' . unl_get_sites_subdir($uri) . '/settings.php';
  if (!is_readable($settings_file)) {
    throw new Exception('No settings.php exists for site at ' . $uri);
  }
  
  if (is_readable(DRUPAL_ROOT . '/sites/all/settings.php')) {
    require DRUPAL_ROOT . '/sites/all/settings.php';
  }
  
  require $settings_file;
  unset($uri);
  unset($settings_file);
  
  return get_defined_vars();
} 