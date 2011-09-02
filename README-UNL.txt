Hacks of Core:

includes/bootstrap.inc
function drupal_settings_initialize()
 * UNL change: include a "global" settings file that applies to all sites.

------------------------------------

sites/all/modules/drush/commands/core/drupal/site_install_7.inc
function drush_core_site_install_version()
 * UNL change! Setting this to FALSE because we don't want them and they're hard coded.

------------------------------------

rewrite.php
used to allow public files to be accessed without the sites/<site_dir>/files prefix

------------------------------------

modules/image/image.field.inc
 * theme_image_formatter ignores attributes so classes can't be added to an image in a theme (needed for photo frame)
 * http://drupal.org/node/1025796#comment-4298698
 * http://drupal.org/files/issues/1025796.patch