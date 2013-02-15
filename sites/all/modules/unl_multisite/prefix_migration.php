<?php

// Run as sudo to be able to write to settings.php

if (PHP_SAPI != 'cli') {
  echo 'This script must be run from the shell!';
  exit;
}

chdir(dirname(__FILE__) . '/../../../..');
define('DRUPAL_ROOT', getcwd());

require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
drupal_override_server_variables();
drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);

$database = array(
  'database' => $GLOBALS['databases']['default']['default']['database'],
  'username' => $GLOBALS['databases']['default']['default']['username'],
  'password' => $GLOBALS['databases']['default']['default']['password'],
  'host' => $GLOBALS['databases']['default']['default']['host'],
  'port' => $GLOBALS['databases']['default']['default']['port'],
  'driver' => $GLOBALS['databases']['default']['default']['driver'],
  'prefix' => $GLOBALS['databases']['default']['default']['prefix'],
);

$query = db_query('SELECT * FROM {unl_sites}');
$site_prefixes = $query->fetchAllAssoc('db_prefix');

$query = db_query('SHOW TABLES');
$tables = $query->fetchCol();

// Switch to alternate db connection to run RENAME TABLE commands without the prefix
$database_noprefix = $database;
$database_noprefix['prefix'] = '';
Database::addConnectionInfo('NoPrefix', 'default', $database_noprefix);
db_set_active('NoPrefix');


// Rename the tables
foreach ($tables as $key => $table) {
  /**
   * Important: Assuming no site db_prefix contains the master database prefix: drupal_
   */
  $pieces = explode($database['prefix'], $table);
  $prefix = substr($pieces[0], 0, -1);
  if (!empty($prefix)) {
    $pieces[0] = 's' . $site_prefixes[$prefix]->site_id . '_' . $database['prefix'];
    $new_name = implode('', $pieces);
    if ($table !== $new_name && $site_prefixes[$prefix]->site_id !== NULL) {
      echo "RENAME TABLE " . $table . " TO ". $new_name . "\n";
      $query = db_query("RENAME TABLE ".$table." TO ".$new_name);
    }
    else {
      echo "Dropping abandoned table " . $table . "\n";
      $query = db_query("DROP TABLE " . $table);
    }
  }
}


foreach ($site_prefixes as $site_prefix => $site) {
  // Change db_prefix col in default site unl_sites to what we created above
  echo "Changing $site_prefix db_prefix to s$site->site_id in the {$database['prefix']}unl_sites table\n";
  $query = db_query("UPDATE " . $database['prefix'] . "unl_sites SET db_prefix='s" . $site->site_id . "' WHERE site_id='" . $site->site_id . "'");

  // Alter the settings.php file
  echo "Rewriting the db_prefix in settings.php\n";
  $command = "find sites -name 'settings.php' -print | xargs sed -i -e 's/" . $site_prefix . "/s" . $site->site_id . "/g'";
  exec($command);
}


exit;
