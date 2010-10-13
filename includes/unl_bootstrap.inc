<?php

function unl_bootstrap() {
  $original_script_name = $_SERVER['SCRIPT_NAME'];
  
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
      $_SERVER['SCRIPT_NAME'] = '/index.php';
    }
    else {
      $_SERVER['SCRIPT_NAME'] = '/' . implode('/', array_slice($path_parts, 0, $i)) . '/index.php';
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
  
  conf_path(TRUE, TRUE);
}
