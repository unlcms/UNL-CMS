Hacks of Core:

includes/bootstrap.inc
function drupal_settings_initialize()
 * UNL change: include a "global" settings file that applies to all sites.
 
function conf_path()
 * UNL change: Add $default_domains array support for sites.php to list which domains are ok to use with 'unl.edu.*' site_dirs.
               If no $default_domains array is defined in sites.php, this code will do nothing.

------------------------------------

sites/all/modules/drush/commands/core/drupal/site_install.inc
function drush_core_site_install_version()
 * UNL change! Setting this to FALSE because we don't want them and they're hard coded.

------------------------------------

drush/commands/core/site_install.drush.inc
function drush_core_pre_site_install()
 * UNL change: Inserted a return before code that would otherwise drop the entire database.

-------------------------------------

rewrite.php
used to allow public files to be accessed without the sites/<site_dir>/files prefix

------------------------------------

modules/image/image.field.inc
 * theme_image_formatter ignores attributes so classes can't be added to an image in a theme (needed for photo frame)
 * http://drupal.org/node/1025796#comment-4298698
 * http://drupal.org/files/issues/1025796.patch

-------------------------------------

sites/all/modules/form_builder/modules/webform/form_builder_webform.module
 * In form_builder_webform_components_page() load jquery.ui.datepicker.min.js so the Date element will work on a new form that does not have ui.datepicker loaded
 * http://drupal.org/node/1307838
 
-------------------------------------

includes/bootstrap.inc
 * Fix so that drupal_serve_page_from_cache() won't override a cached Vary header.
 * http://drupal.org/node/1321086

-------------------------------------

drush/includes/environment.inc
 * Fix so that drush pulls in the correct uri parameter.
 * http://drupal.org/node/1331106

-------------------------------------

sites/sites.php
 * Added support for $default_domains array. See includes/bootstrap.inc conf_path().

------------------------------------

sites/example.sites.php
 * Added an example of the $default_domains array.
 * Added the stub record needed for creating site aliases.
