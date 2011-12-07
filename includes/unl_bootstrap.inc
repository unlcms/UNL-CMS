<?php

/**
 * Do so special setup for UNL specific features.
 */
function unl_bootstrap() {
  unl_bootstrap_multisite_without_symlinks();
  unl_bootstrap_proxy_pass_support();
  unl_bootstrap_mobile_internal_redirect();
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
 * Fix some paths when used through a ProxyPass
 */
function unl_bootstrap_proxy_pass_support() {
  if (isset($_SERVER['HTTP_X_FORWARDED_HOST']) && isset($_SERVER['HTTP_X_FORWARDED_PATH'])) {
    $GLOBALS['base_url'] = 'http://' . $_SERVER['HTTP_X_FORWARDED_HOST'] . $_SERVER['HTTP_X_FORWARDED_PATH'];
    $GLOBALS['cookie_domain'] = $_SERVER['HTTP_X_FORWARDED_HOST'];
    $_SERVER['REQUEST_URI'] = $_SERVER['HTTP_X_FORWARDED_PATH'] . '/' . request_path();
  }
}

/**
 * If this site should be served with a mobile theme, do an internal redirect
 * so that the page cache can store this mobile themed page separate from
 * the normally themed page.
 */
function unl_bootstrap_mobile_internal_redirect() {
  if (unl_bootstrap_is_mobile_user() && !isset($_GET['format'])) {
    $_GET['format'] = 'mobile';
    $_SERVER['QUERY_STRING'] = http_build_query($_GET);
    $_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_URL'] . '?' . $_SERVER['QUERY_STRING'];
  }
}

/**
 * Check if the user is using a "supported" mobile user agent
 *
 * @return bool
 */
function unl_bootstrap_is_mobile_user() {
  if (isset($_SERVER['X-UNL-Mobile'])) {
    // If Varnish set the X-UNL-Mobile header, use it instead of checking again.
    return $_SERVER['X-UNL-Mobile'] == 'Yes';
  }
  
  if (!isset($_SERVER['HTTP_ACCEPT'], $_SERVER['HTTP_USER_AGENT'])) {
    // We have no vars to check
    return false;
  }

  if (isset($_COOKIE['wdn_mobile'])
      && $_COOKIE['wdn_mobile'] == 'no') {
    // The user has a cookie set, requesting no mobile views
    return false;
  }
  
  if ( // Check the http_accept and user agent and see
    preg_match('/text\/vnd\.wap\.wml|application\/vnd\.wap\.xhtml\+xml/i', $_SERVER['HTTP_ACCEPT'])
    ||
    (preg_match('/'.
      'sony|symbian|nokia|samsung|mobile|windows ce|epoc|opera mini|' .
      'nitro|j2me|midp-|cldc-|netfront|mot|up\.browser|up\.link|audiovox|' .
      'blackberry|ericsson,|panasonic|philips|sanyo|sharp|sie-|' .
      'portalmmm|blazer|avantgo|danger|palm|series60|palmsource|pocketpc|' .
      'smartphone|rover|ipaq|au-mic|alcatel|ericy|vodafone\/|wap1\.|wap2\.|iPhone|Android' .
      '/i', $_SERVER['HTTP_USER_AGENT'])
    ) && !preg_match('/ipad/i', $_SERVER['HTTP_USER_AGENT'])) {
    return true;
  }

  return false;
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
