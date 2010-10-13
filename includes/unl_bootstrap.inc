<?php

function unl_bootstrap() {
  $original_script_name = $_SERVER['SCRIPT_NAME'];
  
  $path_parts = explode('/', $_SERVER['SCRIPT_URL']);
  foreach ($path_parts as $path_index => $path_part) {
    if (!$path_part) {
      unset($path_parts[$path_index]);
    }
  }
  
  $previous_conf_path = '';
  $previous_script_name = '';
  for ($i = count($path_parts); $i > 0; $i--) {
    $_SERVER['SCRIPT_NAME'] = '/' . implode('/', array_slice($path_parts, 0, $i)) . '/index.php';
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
