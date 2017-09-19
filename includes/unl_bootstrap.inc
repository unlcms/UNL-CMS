<?php

/**
 * Special setup for UNL specific features.
 */
function unl_bootstrap() {
  unl_bootstrap_short_hostname_redirect();
  unl_bootstrap_multisite_without_symlinks();
  unl_bootstrap_proxy_pass_support();
}

/**
 * Check that the hostname resolves to an IP Address.
 * If it doesn't redirect to <hostname>.unl.edu.
 */
function unl_bootstrap_short_hostname_redirect() {
  // Don't do a redirect when using the command line.
  if (PHP_SAPI == 'cli') {
    return;
  }

  $hostname = $_SERVER['HTTP_HOST'];
  if (gethostbynamel($hostname)) {
    // The provided host name is just fine.
    return;
  }

  // Otherwise, try adding .unl.edu.
  $hostname .= '.unl.edu';
  if (gethostbynamel($hostname)) {
    // If its a valid domain, redirect to it.
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
      $uri = 'https://';
    }
    else {
      $uri = 'http://';
    }
    $uri .= $hostname . $_SERVER['REQUEST_URI'];

    header('Location: ' . $uri);
    exit;
  }
}

/**
 * Enable the set up of multiple sites without making symbolics links.
 * Instead, a few entries in .htaccess and sites.php will be all that is needed.
 */
function unl_bootstrap_multisite_without_symlinks() {
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

  conf_path(TRUE, TRUE);
}

/**
 * Fix some paths when used through a ProxyPass.
 */
function unl_bootstrap_proxy_pass_support() {
  if (isset($_SERVER['HTTP_X_FORWARDED_HOST']) && isset($_SERVER['HTTP_X_FORWARDED_PATH'])) {
    $GLOBALS['unl_proxy_pass'] = TRUE;
    $GLOBALS['base_url'] = 'https://' . $_SERVER['HTTP_X_FORWARDED_HOST'] . $_SERVER['HTTP_X_FORWARDED_PATH'];
    $GLOBALS['cookie_domain'] = $_SERVER['HTTP_X_FORWARDED_HOST'];
    $_SERVER['REQUEST_URI'] = $_SERVER['HTTP_X_FORWARDED_PATH'] . '/' . request_path();
    if (isset($_SERVER['QUERY_STRING'])) {
      $_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
    }
  }
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
