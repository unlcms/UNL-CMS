<?php
/**
 * Report all users that should be removed (no longer found in the directory)
 * 
 * Example Cron that will email daily:
 * MAILTO="eric@unl.edu, mfairchild@unl.edu"
 * @daily php user_report.php
 * 
 */

if (PHP_SAPI != 'cli') {
  echo 'This script must be run from the shell!';
  exit;
}

chdir(dirname(__FILE__) . '/../../../..');
define('DRUPAL_ROOT', getcwd());

require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
drupal_override_server_variables();
drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);
require_once drupal_get_path('module', 'unl') . '/includes/common.php';
require_once drupal_get_path('module', 'unl_multisite') . '/unl_site_creation.php';

$map = unl_get_site_user_map('role', 'Site Admin', TRUE);

unl_cas_get_adapter();

$all_users = array();

foreach ($map as $site) {
  $users_not_found = array();
  
  foreach ($site['users'] as $uid=>$details) {
    
    if (!isset($all_users[$uid])) {
      $record = unl_cas_get_user_record($uid);
      
      $all_users[$uid]['found'] = !(bool)empty($record);
      $all_users[$uid]['sites'] = array();
    }

    if (!$all_users[$uid]['found']) {
      $all_users[$uid]['sites'][] = $site['uri'];
    }
  }
}

$total_to_remove = 0;
foreach ($all_users as $uid=>$details) {
  if ($details['found']) {
    continue;
  }
  
  $total_to_remove++;
  
  echo 'uid "'. $uid . '" not found for:' . PHP_EOL;
  foreach ($details['sites'] as $uri) {
    echo "\t php sites/all/modules/drush/drush.php -l '$uri' user-remove-role 'Site Admin' --uid='$uid'" . PHP_EOL;
  }
  
  echo PHP_EOL;
}

echo 'Total not found: ' . $total_to_remove . PHP_EOL;
echo '(jellycup)' . PHP_EOL;
