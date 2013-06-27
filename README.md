## Requirements:

  * [Core drupal requirements](http://drupal.org/requirements)
  * PHP LDAP Extension
  * PHP Tidy Extension (for migration tool)

## Get Started:

In this example the web root is /Library/WebServer/Documents and Apache runs as _www - modify the instructions below according to your setup

  *  Fork UNL-Information-Services/UNL-CMS and clone your fork into /Library/WebServer/Documents/workspace/UNL-CMS
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

  *  rewrite.php

     This custom file is used to allow public files to be accessed without the sites/<site_dir>/files prefix.

  *  sites/sites.php

     Added support for $default_domains array. See conf_path() in includes/bootstrap.inc

  *  sites/example.sites.php

     Added an example of the $default_domains array. Added the stub record needed for creating site aliases.

  *  modules/image/image.field.inc

     - theme_image_formatter ignores attributes so classes can't be added to an image in a theme (needed for photo frame). See http://drupal.org/node/1025796#comment-4298698 and http://drupal.org/files/issues/1025796.patch


## Hacks of Contrib modules:

  *  drush/commands/core/drupal/site_install.inc

     - function drush_core_site_install_version(). UNL change: Setting this to FALSE because we don't want them and they're hard coded.

  *  drush/commands/core/site_install.drush.inc

     - function drush_core_pre_site_install(). UNL change: Inserted a return before code that would otherwise drop the entire database.

  *  drush/includes/environment.inc

     - Fix so that drush pulls in the correct uri parameter. See http://drupal.org/node/1331106

  *  entity/entity.module, entity/modules/callbacks.inc

     - Add 'uri callback' for file entities. See http://drupal.org/node/1481372#comment-6529650

  *  media/includes/media.variables.inc

     - Convert FILE_ENTITY_DEFAULT_ALLOWED_EXTENSIONS to the new variable. See http://drupal.org/node/1846674#comment-6760286

  *  webform.module

     - Make Safe Key values accessible via tokens. See http://drupal.org/node/1340010#comment-6709520 Patch applied: http://drupal.org/files/webform-1340010-19.patch

  *  workbench_moderation.module

     - Fix broken books in workbench_moderation_node_presave(). See http://drupal.org/node/1505060

  *  wysiwyg/editors/js/tinymce-3.js

     - Comment out the part that switches wrappers from table-based to div. We need the original TinyMCE code for the PDW toggle plugin to work

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
