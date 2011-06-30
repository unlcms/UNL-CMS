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
