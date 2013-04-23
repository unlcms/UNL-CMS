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
 * Custom function to get the db settings for the 'main' site.
 * @return array
 */
function unl_get_shared_db_settings() {
  if (file_exists(DRUPAL_ROOT . '/sites/all/settings.php')) {
    require DRUPAL_ROOT . '/sites/all/settings.php';
  }
  require DRUPAL_ROOT . '/sites/default/settings.php';
  
  return $databases;
}

/**
 * Custom function to get the db prefix of the 'main' site.
 */
function unl_get_shared_db_prefix() {
  $databases = unl_get_shared_db_settings();
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

function unl_site_variable_get($db_prefix, $name, $default = NULL) {
  $shared_prefix = unl_get_shared_db_prefix();
  $data = db_query(
    "SELECT * "
    . "FROM {$db_prefix}_{$shared_prefix}variable "
    . "WHERE name = :name",
    array(':name' => $name)
  )->fetchAll();

  if (count($data) == 0) {
    return $default;
  }

  return unserialize($data[0]->value);
}

/**
 * Given a URI, will return the name of the directory for that site in the sites directory.
 */
function unl_get_sites_subdir($uri, $trim_subdomain = TRUE) {
  $path_parts = parse_url($uri);
  if ($trim_subdomain && substr($path_parts['host'], -7) == 'unl.edu') {
    $path_parts['host'] = 'unl.edu';
  }
  $sites_subdir = $path_parts['host'] . $path_parts['path'];
  $sites_subdir = strtr($sites_subdir, array('/' => '.'));

  while (substr($sites_subdir, 0, 1) == '.') {
    $sites_subdir = substr($sites_subdir, 1);
  }
  while (substr($sites_subdir, -1) == '.') {
    $sites_subdir = substr($sites_subdir, 0, -1);
  }

  return $sites_subdir;
}

/**
 * Given a URI of an existing site, will return settings defined in that site's settings.php
 */
function unl_get_site_settings($uri) {
  $settings_file = DRUPAL_ROOT . '/sites/' . unl_get_sites_subdir($uri) . '/settings.php';
  if (!is_readable($settings_file)) {
    throw new Exception('No settings.php exists for site at ' . $uri);
  }

  if (is_readable(DRUPAL_ROOT . '/sites/all/settings.php')) {
    require DRUPAL_ROOT . '/sites/all/settings.php';
  }

  require $settings_file;
  unset($uri);
  unset($settings_file);

  return get_defined_vars();
}

/**
 * Custom function that returns TRUE if the given table is shared with another site.
 * @param string $table_name
 */
function unl_table_is_shared($table_name) {
  $db_config = $GLOBALS['databases']['default']['default'];
  if (is_array($db_config['prefix']) &&
      isset($db_config['prefix']['role']) &&
      $db_config['prefix']['default'] != $db_config['prefix'][$table_name]) {
    return TRUE;
  }
  return FALSE;
}

/**
 * Custom function that formats a string of HTML using Tidy
 * @param string $string
 */
function unl_tidy($string) {
  if (class_exists('Tidy') && variable_get('unl_tidy')) {
    $tidy = new Tidy();

    // Tidy options: http://tidy.sourceforge.net/docs/quickref.html
    $options = array(
      // HTML, XHTML, XML Options
      'doctype' => 'omit',
      'new-blocklevel-tags' => 'article,aside,header,footer,section,nav,hgroup,address,figure,figcaption,output',
      'new-inline-tags' => 'video,audio,canvas,ruby,rt,rp,time,code,kbd,samp,var,mark,bdi,bdo,wbr,details,datalist,source,summary',
      'output-xhtml' => true,
      'show-body-only' => true,
      // Pretty Print
      'indent' => true,
      'indent-spaces' => 2,
      'vertical-space' => false,
      'wrap' => 140,
      'wrap-attributes' => false,
      // Misc
      'force-output' => true,
      'quiet' => true,
      'tidy-mark' => false,
    );

    // Add &nbsp; to prevent Tidy from removing script or comment if it is the first thing
    if (strtolower(substr(trim($string), 0, 7)) == '<script' || substr(trim($string), 0, 4) == '<!--') {
      $statement = '';
      if (substr(trim($string), 0, 9) !== '<!-- Tidy') {
        $statement = "<!-- Tidy: Start field with something other than script or comment to remove this -->\n";
      }
      $string = "&nbsp;" . $statement . $string;
    }

    $tidy->parseString($string, $options, 'utf8');
    if ($tidy->cleanRepair()) {
      return $tidy;
    }
  }

  return $string;
}

/**
 * A shared-table safe method that returns TRUE if the user is a member of the super-admin role.
 */
function unl_user_is_administrator() {
  $user = $GLOBALS['user'];

  // If the role table is shared, use parent site's user_admin role, otherwise use the local value.
  if (unl_table_is_shared('role')) {
    $admin_role_id = unl_shared_variable_get('user_admin_role');
  }
  else {
    $admin_role_id = variable_get('user_admin_role');
  }

  if ($user && in_array($admin_role_id, array_keys($user->roles))) {
    return TRUE;
  }

  return FALSE;
}

/**
 * Fetch the contents at the given URL and cache the result using
 * drupal's cache for as long as the response headers allow.
 * @param string $url
 * @param resource $context
 */
function unl_url_get_contents($url, $context = NULL, &$headers = array())
{
  unl_load_zend_framework();
  if (!Zend_Uri::check($url)) {
    drupal_set_message('A non-url was passed to ' . __FUNCTION__ . '().', 'warning');
    return FALSE;
  }
  
  // get some per-request static storage
  $static = &drupal_static(__FUNCTION__);
  if (!isset($static)) {
    $static = array();
  }
  
  // If cached in the static array, return it.
  if (array_key_exists($url, $static)) {
    $headers = $static[$url]['headers'];
    return $static[$url]['body'];
  }
  
  // If cached in the drupla cache, return it.
  $data = cache_get(__FUNCTION__ . $url);
  if ($data && time() < $data->data['expires']) {
    $headers = $data->data['headers'];
    return $data->data['body'];
  }

  if (!$context) {
    // Set a 5 second timeout
    $context = stream_context_create(array('http' => array('timeout' => 5)));
  }

  // Make the request
  $http_response_header = array();
  $body = file_get_contents($url, NULL, $context);
  
  // If an error occured, just return it now.
  if ($body === FALSE) {
    $static[$url] = $body;
    return $body;
  }
  
  $headers = array();
  foreach ($http_response_header as $rawHeader) {
    $headerName = trim(substr($rawHeader, 0, strpos($rawHeader, ':')));
    $headerValue = trim(substr($rawHeader, strpos($rawHeader, ':') + 1));
    if ($headerName && $headerValue) {
      $headers[$headerName] = $headerValue;
    }
  }
  $lowercaseHeaders = array_change_key_case($headers);
  
  $cacheable = NULL;
  $expires = 0;
  
  // Check for a Cache-Control header and the max-age and/or private headers.
  if (array_key_exists('cache-control', $lowercaseHeaders)) {
    $cacheControl = strtolower($lowercaseHeaders['cache-control']);
    $matches = array();
    if (preg_match('/max-age=([0-9]+)/', $cacheControl, $matches)) {
      $expires = time() + $matches[1];
      $cacheable = TRUE;
    }
    if (strpos($cacheControl, 'private') !== FALSE) {
      $cacheable = FALSE;
    }
    if (strpos($cacheControl, 'no-cache') !== FALSE) {
      $cacheable = FALSE;
    }
  }
  // If there was no Cache-Control header, or if it wasn't helpful, check for an Expires header.
  if ($cacheable === NULL && array_key_exists('expires', $lowercaseHeaders)) {
    $cacheable = TRUE;
    $expires = DateTime::createFromFormat(DateTime::RFC1123, $lowercaseHeaders['expires'])->getTimestamp();
  }
  
  // Save to the drupal cache if caching is ok
  if ($cacheable && time() < $expires) {
    $data = array(
      'body' => $body,
      'headers' => $headers,
      'expires' => $expires,
    );
    cache_set(__FUNCTION__ . $url, $data, 'cache', $expires);
  }
  // Otherwise just save to the static per-request cache
  else {
    $static[$url] = array(
        'body' => $body,
        'headers' => $headers,
    );
  }
  
  return $body;
}

/**
 * Drop-in replacement for db_select that creates a query on default site's database.
 * @see db_select
 * @return SelectQuery
 */
function unl_shared_db_select($table, $alias = NULL, array $options = array()) {
  $databases = unl_get_shared_db_settings();
  Database::addConnectionInfo('default', 'unl_parent_site', $databases['default']['default']);
  $options['target'] = 'unl_parent_site';
  
  return db_select($table, $alias, $options);
}
