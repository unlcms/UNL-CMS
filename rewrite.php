#!/usr/bin/php
<?php

define('DRUPAL_ROOT', getcwd());

require_once DRUPAL_ROOT . '/includes/unl_bootstrap.inc';
require_once DRUPAL_ROOT . '/includes/bootstrap.inc';

$stdin = fopen('php://stdin', 'r');
$cache = new RewriteCache();

// Each mapping request is on its own line.
while ($line = fgets($stdin)) {
  // Remove the trailing newline
  $line = trim($line, "\n");
  
  // Check for this result in the cache
  if (!($output = $cache->get($line))) {
  
    // Parse the 3 fields.
    list($host, $uri, $path) = explode(';delim;', $line);
    
    // Get the base path of the drupal install
    $base_path = substr($uri, 0, strlen($uri) - strlen($path));
    
    // Set up some _SERVER variables as if this was a HTTP request.
    $_SERVER['SCRIPT_NAME'] = $base_path . 'index.php';
    $_SERVER['SCRIPT_FILENAME'] = DRUPAL_ROOT . '/index.php';
    $_SERVER['REQUEST_URI'] = $uri;
    $_SERVER['HTTP_HOST'] = $host;
    
    // Call the UNL bootstrap to fix conf_path and SCRIPT_NAME
    unl_bootstrap();
    $site_dir = conf_path();
    $base_path = substr($_SERVER['SCRIPT_NAME'], 0, -9);
    // Now we fix the drupal path.
    $drupal_path = substr($uri, strlen($base_path));
    
    // Finally, generate the path to the file we might be accessing
    $file_path = $site_dir . '/files/' . $drupal_path;
    
    // If that file exists, return the correct path to it, otherwise, return what we were given. 
    if (is_file($file_path)) {
      $output = $file_path;
    }
    else {
      $output = 'NULL';
    }
    
    $cache->set($line, $output);
  }
  
  echo $output . PHP_EOL;
}


// A basic in-memory cache that with a Least Recently Used expiration policy with a limitted number of entries.
class RewriteCache {
  protected $_storage;
  protected $_cache_size;
  protected $_lifetime;
  
  function __construct($cache_size = 1000, $lifetime = 30) {
    $this->_storage = array();
    $this->_cache_size = $cache_size;
    $this->_lifetime = $lifetime;
  }
  
  function get($key) {
    if (isset($this->_storage[$key])) {
      $entry = $this->_storage[$key];
      unset($this->_storage[$key]);
      
      // If the entry isn't expired, promote it to the front and return it.
      if ($entry['time'] + $this->_lifetime > time()) {
        $this->_storage[$key] = $entry;
        return $entry['data'];
      }
    }
    return FALSE;
  }
  
  function set($key, $value) {
    if (count($this->_storage) >= $this->_cache_size) {
      array_shift($this->_storage);
    }
    $this->_storage[$key] = array('time' => time(), 'data' => $value);
  }
}