#!/usr/bin/php
<?php

$stdin = fopen('php://stdin', 'r');
$cache = new RewriteCache();

// Each mapping request is on its own line.
while ($line = fgets($stdin)) {
  // Remove the trailing newline
  $line = trim($line, "\n");
  
  // Check for this result in the cache
  if (!($output = $cache->get($line))) {

    exec('/usr/bin/php ' . __DIR__ . '/rewrite_miss.php ' . escapeshellarg($line), $output, $return_var);

    // Set default route
    $route = 'NULL';

    if (!$return_var && isset($output[0])) {
      //Success! a route was found
      $route = $output[0];
    }
    
    $cache->set($line, $route);
  }
  
  echo $route . PHP_EOL;
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