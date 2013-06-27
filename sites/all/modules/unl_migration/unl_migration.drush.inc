<?php

function unl_migration_drush_command() {
  $items['unl-migration'] = array(
    'description' => 'Perform a migration.',
    'arguments' => array(
      'site-url' => dt('Full URL to the existing site you wish to migrate'),
    ),
    'options' => array(
      'frontier-path'        => dt('Full path to the root of your site on frontier (if applicable).'),
      'frontier-user'        => dt('Frontier FTP Username'),
      'frontier-pass'        => dt('Frontier FTP Password'),
      'ignore-duplicates'    => dt("This may be needed if your site has an unlimited number of dynamicly generated paths."),
      'disable-liferay-code' => dt("Normally, the liferay code won't interfere with non-liferay sites. If you have a /web directory, you should use this."),
      'use-liferay-titles'   => dt("Liferay doesn't use WDN compliant page titles. This enables their alternate method of finding the page title."),
    ),
    'examples' => array(
      'drush unl-migration http://example.unl.edu/path/ --disable_liferay_code'
    ),
    'bootstrap' => DRUSH_BOOTSTRAP_DRUPAL_FULL,
  );
  
  return $items;
}

function unl_migration_drush_help($section) {
  switch ($section) {
    case 'drush:unl_migration':
      return dt('Perform a migration');
      break;
    case 'meta:unl_migration:title':
      return dt('UNL Migration');
      break;
    case 'meta:unl_migration:summary':
      return dt('Perform a migration 2');
      break;
  }
}

function drush_unl_migration() {
  require_once dirname(__FILE__) . '/unl_migration.php';
  $arguments = drush_get_arguments();
  
  $migration = new Unl_Migration_Tool(
    $arguments[1],
    drush_get_option('frontier-path'),
    drush_get_option('frontier-user'),
    drush_get_option('frontier-pass'),
    drush_get_option('ignore-duplicates'),
    !drush_get_option('disable-liferay-code'),
    drush_get_option('use-liferay-titles')
  );
  
  $migration->setLogger(function($message, $severity) {
    switch ($severity) {
      default:
      case WATCHDOG_ERROR:
        $type = 'error';
        break;
      case WATCHDOG_WARNING:
        $type = 'warning';
        break;
      case WATCHDOG_INFO:
        $type = 'success';
        break;
      case WATCHDOG_DEBUG:
        if (!drush_get_option('verbose') && !drush_get_option('v')) {
          continue;
        }
        $type = 'notice';
        break;
    }
    drush_log($message, $type);
  });
  
  $migration->migrate(60*60*24*7);

  $filesPath = drush_get_context('DRUSH_DRUPAL_ROOT') . '/' . drush_get_context('DRUSH_DRUPAL_SITE_ROOT') . '/files';
  drush_shell_exec('chmod -R a+w ' . escapeshellarg($filesPath));
}
