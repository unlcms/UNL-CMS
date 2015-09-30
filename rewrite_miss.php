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

// Generate the path to the file we might be accessing
$file_path = $site_dir . '/files/' . $drupal_path;

// If that file exists, return the correct path to it
if (is_file($file_path)) {
  $output = $file_path;
}
// else if on lancaster.unl.edu look for the file in a case-insensitive manner
else if ($host === 'lancaster.unl.edu' && !empty($drupal_path) && $file_path = _insensitive_is_file($file_path)) {
  $output = $file_path;
}
else {
  $output = 'NULL';
}

echo $output;


/**
 * @param $path
 * @return bool|string
 *
 * Checks to see if the file exists with a case-insensitive search.
 * $path is relative to the DRUPAL_ROOT.
 * Returns the correctly capitalized path on success, FALSE on failure.
 */
function _insensitive_is_file($path) {
  $subdirs = array();
  $filename = basename($path);
  for ($subdir = $path; ($subdir = dirname($subdir)) != '.';) {
    $subdirs[] = basename($subdir);
  }
  $subdirs = array_reverse($subdirs);

  $path = DRUPAL_ROOT;
  foreach ($subdirs as $subdir) {
    $cmd = 'find '
         . escapeshellarg($path) . ' '
         . '-maxdepth 1 '
         . '-type d '
         . '-iname ' . escapeshellarg($subdir) . ' '
         ;
    $path = trim(shell_exec($cmd));
    if (!$path) {
      return false;
    }
  }

  $cmd = 'find '
    . escapeshellarg($path) . ' '
    . '-type f '
    . '-maxdepth 1 '
    . '-iname ' . escapeshellarg($filename) . ' '
    ;
  $path = trim(shell_exec($cmd));
  if (!$path) {
    return false;
  }

  return substr($path, strlen(DRUPAL_ROOT) + 1);
}
