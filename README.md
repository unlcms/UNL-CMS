## Requirements:

  * [Core drupal requirements](http://drupal.org/requirements)
  * PHP LDAP Extension
  * PHP Tidy Extension (for migration tool)

## Get Started:

In this example the web root is /Library/WebServer/Documents and Apache runs as _www - modify the instructions below according to your setup

  *  Fork and clone your fork into /Library/WebServer/Documents/workspace/UNL-CMS
  *  Create a local database (example name: unlcms)
  *  From /Library/WebServer/Documents/workspace/UNL-CMS run:

        git submodule init
        git submodule update

  *  Create this file in your home directory with a name like resetunlcms.sh

        echo 'Resetting UNL-CMS... Get ready for action!'

        mysqldump -uYOURUSERNAME -pYOURPASS --add-drop-table --no-data unlcms | grep ^DROP | mysql -uYOURUSERNAME -pYOURPASS unlcms
        echo 'unlcms database emptied....'

        cd /Library/WebServer/Documents/workspace/UNL-CMS/sites/default
        rm -rf files
        rm settings.php
        mkdir files
        chown _www files
        cp default.settings.php settings.php
        chown _www settings.php

        cd /Library/WebServer/Documents/workspace/UNL-CMS/sites
        sudo rm -rf localhost.*
        sudo rm -rf MYDEVMACHINE.unl.edu.*
        sudo rm -rf unl.edu.*
        sudo rm sites.php
        cp example.sites.php sites.php

        echo 'Resetting .htaccess'
        cd /Library/WebServer/Documents/workspace/UNL-CMS
        sudo rm .htaccess
        sudo rm .htaccess-subsite-map.txt
        cp .htaccess.sample .htaccess
        cp .htaccess-subsite-map.txt.sample .htaccess-subsite-map.txt
        sudo chown YOURUSER .htaccess
        sudo chown YOURUSER .htaccess-subsite-map.txt
        sed -i "" 's/# RewriteBase \/drupal\//RewriteBase \/workspace\/UNL-CMS\//' ".htaccess"

        echo 'Done.'
        echo 'Note: If you want clean urls you need to look at the .htaccess file where it says "Allow public files to be accessed without the sites/<site_dir>/files prefix"';

  *  Run that script. (Can also be run whenever you want to reset your dev environment.)

        sudo sh ~/resetunlcms.sh

  *  Go to http://localhost/workspace/UNL-CMS and go through the install process

## Upgrading Drupal Core

Download the current version (drupal-7.a) being used in this repo and the latest version (drupal-7.b) from https://drupal.org/project/drupal

```
diff -ruNa drupal-7.a drupal-7.b > drupal_patch.diff
git checkout -b drupal-7.b-update master
git apply —-check drupal_patch.diff
git apply drupal_patch.diff
git add .
git commit -m "Upgrade Drupal Core to 7.b"
git push yourfork drupal-7.b-update
```

Once that is complete, open a Pull Request against develop in unlcms/UNL-CMS.

## Install Issues:

  * Can't create a new site with Drush/UNL Cron if pdo_pgsql is enabled

    If pdo_pgsql is enabled on the php install that is running drush/unl cron then it will fail without modification.
    Adding the following junk values for pgsql solves the problem at line 414 (D7.10) of install_run_task inside install.core.inc

              $form_state['values']['pgsql']['username'] = 'xxxx'; //add this
              $form_state['values']['pgsql']['database'] = 'xxxx'; //add this
              drupal_form_submit($function, $form_state); //existing code
              $errors = form_get_errors(); //existing code


## Hacks of Core:

  *  includes/bootstrap.inc

     - function drupal_settings_initialize(). UNL change: include a "global" settings file that applies to all sites.

     - function conf_path(). UNL change: Add $default_domains array support for sites.php to list which domains are ok to use with 'unl.edu.*' site_dirs.
       If no $default_domains array is defined in sites.php, this code will do nothing.

     - Fix so that drupal_serve_page_from_cache() won't override a cached Vary header. http://drupal.org/node/1321086

  *  includes/database/database.inc

     Add support for a "db_select_only" config option that prevents drupal from issuing non-select queries to that database.
     This can be used to allow Drupal to function during a FLUSH TABLES WITH READ LOCK;

  *  includes/install.inc

     Add primary key to drupal_install_test https://www.drupal.org/project/drupal/issues/2856362

  *  rewrite.php

     This custom file is used to allow public files to be accessed without the sites/<site_dir>/files prefix.

  *  sites/sites.php

     Added support for $default_domains array. See conf_path() in includes/bootstrap.inc

  *  sites/example.sites.php

     Added an example of the $default_domains array. Added the stub record needed for creating site aliases.

  *  modules/field/modules/text/text.module

     - Add nl2br() on Plain Text processor. See http://drupal.org/node/1152216#comment-7174876

  *  modules/forum

     - Removed completely because its table doesn't have a primary key. (https://github.com/unlcms/UNL-CMS/issues/942)

  *  modules/taxonomy

     - Add a primary key to the {taxonomy_index} table. https://www.drupal.org/files/issues/drupal-n610076-75.patch

## Hacks of Contrib modules:

  *  autoban

     - A "Forced mode" ban doesn't take effect until another IP triggers a watchdog message. https://www.drupal.org/project/autoban/issues/2969670
     - Applied patch: 2969670-2-autoban-forced-mode.diff

  *  cas

     - Hide the login message, don't need it.

  *  draggableviews

     - Anonymous view displays an empty form with no submit button which fails webaudit.unl.edu testing. Applied draggableviews-add_hidden_submit_button_to_form-867.patch

  *  drush/commands/core/drupal/site_install_7.inc

     - function drush_core_site_install_version(). UNL change: Setting this to FALSE because we don't want them and they're hard coded.

  *  drush/commands/core/site_install.drush.inc

     - function drush_core_pre_site_install(). UNL change: Inserted a return before code that would otherwise drop the entire database.

  *  feeds/plugins/FeedsParser.inc

     - Remove the file extension check so that UNL Events images (like https://events.unl.edu/images/12345) that don't have extensions will work.

  *  feeds_imagegrabber

     - https://www.drupal.org/project/feeds_imagegrabber/issues/2244833
     - Applied patch: patches/feeds_imagegrabber-update_processor_callback-2244833-45.patch

  *  google_analytics

     - Apply patch to support GA4: https://www.drupal.org/project/google_analytics/issues/3174214#comment-14496809

  *  honeypot

     - Applied patch: https://www.drupal.org/project/honeypot/issues/2943526

  *  imce_rename.module

     - Applied patch: https://www.drupal.org/files/issues/imce_rename-file_move-1376260-8.patch See https://www.drupal.org/node/1376260

  *  masquerade

     - Applied patch: patches/masquerade-remove_masquerade_table_and_rely_on_session-d7-1926074-31.patch.txt
     - Removed login/logout hooks calls that were added in https://www.drupal.org/project/masquerade/issues/1364574 because they break SSO

  *  menu_block

     - Added additional classes to menu_block_get_title() in menu_block.module

  *  og_menu.module

     - Fix permission problem for editor that is only the author of the page with no other permissions.

  *  picture

     - Remove height/width attributes for validity. See https://drupal.org/node/2115513

  *  redirect

     - Merge global redirect functions into Redirect module. See http://drupal.org/node/905914
     - Patch #195 applied to rc-3 with added "Sanity check" to not redirect to external URLs.

  *  themekey

     - Patch applied: themekey-PHPCompatibility-3128131-2.patch
     - PHP 8.0+ fix: https://www.drupal.org/project/themekey/issues/3128131

  *  upload_replace.module

     - Drupal 7 bug fixes. See http://drupal.org/node/1115484#comment-5646558

  *  views_autorefresh

     - In Drupal.ajax.prototype.commands.viewsAutoRefreshIncremental, change `var view_name_id = response.view_name_id;` to `var view_name_id = response.view_name;`

  *  views_infinite_scroll

     - views_plugin_pager_infinite_scroll.inc: render() - move views_infinite_scroll.js to scope => footer for compatibility with UNL Web Framework.

  *  viewreference.module

     - Lock down access to "Allow PHP code." under "Contextual filter arguments". See https://drupal.org/node/2014723#comment-7878825 Patch applied: https://drupal.org/files/viewreference-php_perm-2014723-1.patch

     - Fix label and settings var notices. See https://drupal.org/node/1790304#comment-7395496 Patch applied: https://drupal.org/files/viewreference-1790304-03-complex-entity-form.patch

  *  workbench_moderation.module

     - Fix broken books in workbench_moderation_node_presave(). See http://drupal.org/node/1505060

  *  wysiwyg/editors/js/tinymce-3.js

     - Comment out the part that switches wrappers from table-based to div. We need the original TinyMCE code for the PDW toggle plugin to work

## Use of Features

There are content types provided by Features located in sites/all/modules/custom/features. If a new content type is added the following should be done:
 * Add templates and css to unl_five theme and update unl_five_preprocess_node() to only attach css to the content type.
 * Add machine name to _unl_content_type_access() in unl.module to prevent editing the content type.
 * Remove the content type from the admin/structure/types list in unl_page_alter() in unl.module.
 * Update unl_node_add_list() in unl.module to organize the /node/add page.
 * (Optional) Update the unl_hero module to not add the standard hero group and fields.

## How to Contribute

Development is handled through GitHub

All code changes must be committed via git to a local fork and contributed back to the project via a pull request.

Ideally each developer should have a fork of the project on GitHub where they can push changes.

In your local clone:

 * git pull origin develop
 * git checkout -b topics/whatever-you-work-on (or bugfix/NUM — for bugs)
 * write code and commit
 * git push origin topics/whatever-you-work-on
 * on github open a pull request from your branch to develop
 * have someone else review

Another developer will review your changes and merge in to the develop branch.
