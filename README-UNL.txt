Hacks of Core:

*****************************************
** UNL Mods
*****************************************

includes/bootstrap.inc
function drupal_settings_initialize()
 * UNL change: include a "global" settings file that applies to all sites.
 
function conf_path()
 * UNL change: Add $default_domains array support for sites.php to list which domains are ok to use with 'unl.edu.*' site_dirs.
               If no $default_domains array is defined in sites.php, this code will do nothing.

------------------------------------

sites/all/modules/drush/commands/core/drupal/site_install_7.inc
function drush_core_site_install_version()
 * UNL change! Setting this to FALSE because we don't want them and they're hard coded.

*****************************************
** Patches
*****************************************

modules/image/image.field.inc
 * theme_image_formatter ignores attributes so classes can't be added to an image in a theme (needed for photo frame)
 * http://drupal.org/node/1025796#comment-4298698
 * http://drupal.org/files/issues/1025796.patch

 ------------------------------------
 
includes/common.inc
 * EntityMalformedException: Missing bundle property on entity of type node. in entity_extract_ids() (line 7392 of /var/www/unl.edu/htdocs/includes/common.inc).
 * http://gforge.unl.edu/gf/project/wdn_thm_drupal/tracker/?action=TrackerItemEdit&tracker_item_id=993&start=0
 * http://drupal.org/node/1067750#comment-4941822
 * Applied patch: http://drupal.org/files/issues/empty_string_bundle.patch
 
 
*****************************************
** Other
*****************************************
 
sites/sites.php
 * Added support for $default_domains array. See includes/bootstrap.inc conf_path().

------------------------------------

sites/example.sites.php
 * Added an example of the $default_domains array.
 * Added the stub record needed for creating site aliases.
 
 ------------------------------------

rewrite.php
used to allow public files to be accessed without the sites/<site_dir>/files prefix
