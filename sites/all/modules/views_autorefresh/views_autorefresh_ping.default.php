<?php

/**
 * @file
 * Views Auto-Refresh default ping script.
 *
 * This script is intended to be used as a starting point to copy and
 * adapt to your needs.
 */

// Optionally change included file directory.
require 'includes/views_autorefresh.inc';

// Defines the root directory.
define('DRUPAL_ROOT', '/PATH/TO/DRUPAL');
// Optionally configure cache headers to either 'none', 'skip', or a numeric value (seconds).
define('CACHE', 'none');
// Optionally debug. Disable on production.
define('DEBUG', FALSE);

_views_autorefresh_ping_init(CACHE, DEBUG);

/*
 * Helper function to get updated value.
 */
function _views_autorefresh_ping_get_updated($timestamp_request, $view_name = '', $view_display_id = '') {
  $timestamp_updated = 0;

  // Optionally add logic for different views.
  //if ($view_name == 'VIEW_NAME' && $view_display_id == 'VIEW_DISPLAY_ID') {}

  $db = _views_autorefresh_ping_connect_db();
  // Optionally alter your database query.
  $query = "SELECT count(nid) FROM node WHERE created > $timestamp_request;";

  if ($result = $db->query($query)) {
    $timestamp_updated = $result->fetchColumn();
  }

  // Normalize integer.
  $timestamp_updated = (int) $timestamp_updated;

  return $timestamp_updated;
}

/*
 * Helper function to connect to the default database.
 */
function _views_autorefresh_ping_connect_db() {
  $db = NULL;
  // Optionally change your settings file directory.
  $settings = DRUPAL_ROOT . '/sites/default/settings.php';

  if (file_exists($settings)) {
    require $settings;

    // $databases variable scoped by settings file above.
    if (isset($databases) && $databases) {
      // Optionally change which database to connect to.
      $creds = $databases['default']['default'];
      $constr = sprintf("%s:dbname=%s", $creds['driver'], $creds['database']);
      $db = new PDO($constr, $creds['username'], $creds['password']);
    }
  }

  // Fail.
  if (!$db) {
    _views_autorefresh_ping_pong(0, 0, 0, 'Database connection error', CACHE, DEBUG);
  }

  return $db;
}
