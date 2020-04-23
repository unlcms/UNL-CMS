<?php

/**
 * @file
 * Views Auto-Refresh ping script global functions.
 */

/*
 * Helper function to initialize ping.
 */
function _views_autorefresh_ping_init($cache = 'skip', $debug = FALSE) {
  // Prevent sql injection.
  $timestamp_request = isset($_GET['timestamp']) ? _views_autorefresh_ping_check_plain($_GET['timestamp']) : NULL;
  $view_name = isset($_GET['view_name']) ? _views_autorefresh_ping_check_plain($_GET['view_name']) : '';
  $view_display_id = isset($_GET['view_display']) ? _views_autorefresh_ping_check_plain($_GET['view_display']) : '';

  // Validate.
  if (!$timestamp_request || !is_numeric($timestamp_request)) {
    // Return empty.
    _views_autorefresh_ping_pong(0, 0, 0, 'Timestamp invalid', $cache, $debug);
  }
  else {
    // Normalize integer.
    $timestamp_request = (int) $timestamp_request;
    $timestamp_updated = _views_autorefresh_ping_get_updated($timestamp_request, $view_name, $view_display_id);
    $pong = ($timestamp_updated > $timestamp_request) ? 1 : 0;

    _views_autorefresh_ping_pong($pong, $timestamp_updated, $timestamp_request, '', $cache, $debug);
  }
}

/*
 * Helper function to output the response.
 */
function _views_autorefresh_ping_pong($pong, $timestamp_updated, $timestamp_request, $message = '', $cache = 'skip', $debug = FALSE) {
  $response = array(
    'pong' => $pong,
  );

  header('Content-Type: application/json');

  switch ($cache) {
    case 'skip':
      // Leave empty.

      break;
    case 'none':
      // HTTP headers to prevent caching the result of this call.
      header('Cache-Control: no-cache, must-revalidate'); // HTTP/1.1.
      header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past.

      break;
    default:
      // Validate seconds.
      if (is_numeric($cache)) {
        header('Cache-Control: max-age=' . $cache);
      }
  }

  // Optionally debug.
  if ($debug) {
    $response['timestamp_updated'] = $timestamp_updated;
    $response['timestamp_request'] = $timestamp_request;
    $response['timestamp_diff'] = $timestamp_updated - $timestamp_request;
    $response['cache'] = $cache;
    $response['message'] = $message;
  }

  // JSON response.
  print json_encode($response);

  // End all other processes.
  exit;
}

/*
 * Helper function to properly encode characters and prevent attacks.
 *
 * @see check_plain();
 */
function _views_autorefresh_ping_check_plain($text) {
  return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}
