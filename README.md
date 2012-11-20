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
        cp .htaccess.sample .htaccess
        sudo chown YOURUSER .htaccess
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

  *  webform.module

     - Make Safe Key values accessible via tokens. See http://drupal.org/node/1340010#comment-6709520 Patch applied: http://drupal.org/files/webform-1340010-19.patch

  *  workbench_moderation.module

     - Fix broken books in workbench_moderation_node_presave(). See http://drupal.org/node/1505060

  *  wysiwyg/editors/js/tinymce-3.js

     - Comment out the part that switches wrappers from table-based to div. We need the original TinyMCE code for the PDW toggle plugin to work


#DRUPAL 7 README

CONTENTS OF THIS FILE
---------------------

 * About Drupal
 * Configuration and features
 * Appearance
 * Developing for Drupal

ABOUT DRUPAL
------------

Drupal is an open source content management platform supporting a variety of
websites ranging from personal weblogs to large community-driven websites. For
more information, see the Drupal website at http://drupal.org/, and join the
Drupal community at http://drupal.org/community.

Legal information about Drupal:
 * Know your rights when using Drupal:
   See LICENSE.txt in the same directory as this document.
 * Learn about the Drupal trademark and logo policy:
   http://drupal.com/trademark

CONFIGURATION AND FEATURES
--------------------------

Drupal core (what you get when you download and extract a drupal-x.y.tar.gz or
drupal-x.y.zip file from http://drupal.org/project/drupal) has what you need to
get started with your website. It includes several modules (extensions that add
functionality) for common website features, such as managing content, user
accounts, image uploading, and search. Core comes with many options that allow
site-specific configuration. In addition to the core modules, there are
thousands of contributed modules (for functionality not included with Drupal
core) available for download.

More about configuration:
 * Install, upgrade, and maintain Drupal:
   See INSTALL.txt and UPGRADE.txt in the same directory as this document.
 * Learn about how to use Drupal to create your site:
   http://drupal.org/documentation
 * Download contributed modules to sites/all/modules to extend Drupal's
   functionality:
   http://drupal.org/project/modules
 * See also: "Developing for Drupal" for writing your own modules, below.

APPEARANCE
----------

In Drupal, the appearance of your site is set by the theme (themes are
extensions that set fonts, colors, and layout). Drupal core comes with several
themes. More themes are available for download, and you can also create your own
custom theme.

More about themes:
 * Download contributed themes to sites/all/themes to modify Drupal's
   appearance:
   http://drupal.org/project/themes
 * Develop your own theme:
   http://drupal.org/documentation/theme

DEVELOPING FOR DRUPAL
---------------------

Drupal contains an extensive API that allows you to add to and modify the
functionality of your site. The API consists of "hooks", which allow modules to
react to system events and customize Drupal's behavior, and functions that
standardize common operations such as database queries and form generation. The
flexible hook architecture means that you should never need to directly modify
the files that come with Drupal core to achieve the functionality you want;
instead, functionality modifications take the form of modules.

When you need new functionality for your Drupal site, search for existing
contributed modules. If you find a module that matches except for a bug or an
additional needed feature, change the module and contribute your improvements
back to the project in the form of a "patch". Create new custom modules only
when nothing existing comes close to what you need.

More about developing:
 * Search for existing contributed modules:
   http://drupal.org/project/modules
 * Contribute a patch:
   http://drupal.org/patch/submit
 * Develop your own module:
   http://drupal.org/developing/modules
 * Follow best practices:
   http://drupal.org/best-practices
 * Refer to the API documentation:
   http://api.drupal.org/api/drupal/7
