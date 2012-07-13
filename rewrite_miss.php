<?php

if (!isset($argv, $argv[1])) {
  echo 'NULL';
  exit(1);
}

$line = $argv[1];

set_time_limit(5);

define('DRUPAL_ROOT', __DIR__);

require_once DRUPAL_ROOT . '/includes/unl_bootstrap.inc';
require_once DRUPAL_ROOT . '/includes/bootstrap.inc';

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

echo $output;

