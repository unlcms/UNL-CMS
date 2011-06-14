<?php

/**
 * Enable the set up of multiple sites without making symbolics links.
 * Instead, a few entries in .htaccess will be all that is needed.
 */
function unl_bootstrap() {
  $original_script_name = $_SERVER['SCRIPT_NAME'];
  $php_file = basename($original_script_name);
  
  $request_uri = parse_url($_SERVER['REQUEST_URI']);
  $path_parts = explode('/', $request_uri['path']);
  foreach ($path_parts as $path_index => $path_part) {
    if (!$path_part) {
      unset($path_parts[$path_index]);
    }
  }
  
  $previous_conf_path = '';
  $previous_script_name = '';
  for ($i = count($path_parts); $i >= 0; $i--) {
    if ($i == 0) {
      $_SERVER['SCRIPT_NAME'] = '/' . $php_file;
    }
    else {
      $_SERVER['SCRIPT_NAME'] = '/' . implode('/', array_slice($path_parts, 0, $i)) . '/' . $php_file;
    }
    $conf_path = conf_path(TRUE, TRUE);
    
    if ($previous_conf_path && ($conf_path != $previous_conf_path)) {
      $_SERVER['SCRIPT_NAME'] = $previous_script_name;
      break;
    }
    
    if ($_SERVER['SCRIPT_NAME'] == $original_script_name) {
      break;
    }
    $previous_conf_path = $conf_path;
    $previous_script_name = $_SERVER['SCRIPT_NAME'];
  }
  
  // Fix some paths when used through a ProxyPass
  if (isset($_SERVER['HTTP_X_FORWARDED_HOST']) && isset($_SERVER['HTTP_X_FORWARDED_PATH'])) {
    $GLOBALS['base_url'] = 'http://' . $_SERVER['HTTP_X_FORWARDED_HOST'] . $_SERVER['HTTP_X_FORWARDED_PATH'];
    $GLOBALS['cookie_domain'] = $_SERVER['HTTP_X_FORWARDED_HOST'];
    $_SERVER['REQUEST_URI'] = $_SERVER['HTTP_X_FORWARDED_PATH'] . '/' . request_path();
  }
  
  conf_path(TRUE, TRUE);
}

/**
 * This will be called during update.php's bootstrap to remove any
 * shared table prefixes from the database config.
 * This allows the same updates to be run on all sites, even if
 * they would normally be applied to the same table.
 */
function unl_bootstrap_update() {
  foreach ($GLOBALS['databases'] as $key1 => $databases) {
    foreach ($databases as $key2 => $database) {
      if ($key2 == 'slave') {
        foreach ($database as $key3 => $slave) {
          if (is_array($slave['prefix'])) {
            $GLOBALS['databases'][$key1][$key2][$key3]['prefix'] = $slave['prefix']['default'];
          }
        }
      }
      else {
        if (is_array($database['prefix'])) {
          $GLOBALS['databases'][$key1][$key2]['prefix'] = $database['prefix']['default'];
        }
      }
    }
  }
}
