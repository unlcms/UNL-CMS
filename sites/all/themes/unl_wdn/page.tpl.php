We need to output something to make drupal think this file actually does something.
Any real output is being deferred to html.tpl.php
<?php
/**
 * @file
 * unl_wdn theme implementation to display a single Drupal page.
 *
 * Available variables:
 *
 * General utility variables:
 * - $base_path: The base URL path of the Drupal installation. At the very
 *   least, this will always default to /.
 * - $directory: The directory the template is located in, e.g. modules/system
 *   or themes/garland.
 * - $is_front: TRUE if the current page is the front page.
 * - $logged_in: TRUE if the user is registered and signed in.
 * - $is_admin: TRUE if the user has permission to access administration pages.
 *
 * Site identity:
 * - $front_page: The URL of the front page. Use this instead of $base_path,
 *   when linking to the front page. This includes the language domain or
 *   prefix.
 * - $logo: The path to the logo image, as defined in theme configuration.
 * - $site_name: The name of the site, empty when display has been disabled
 *   in theme settings.
 * - $site_slogan: The slogan of the site, empty when display has been disabled
 *   in theme settings.
 *
 * Navigation:
 * - $main_menu (array): An array containing the Main menu links for the
 *   site, if they have been configured.
 * - $secondary_menu (array): An array containing the Secondary menu links for
 *   the site, if they have been configured.
 * - $breadcrumb: The breadcrumb trail for the current page.
 *
 * Page content (in order of occurrence in the default page.tpl.php):
 * - $title_prefix (array): An array containing additional output populated by
 *   modules, intended to be displayed in front of the main title tag that
 *   appears in the template.
 * - $title: The page title, for use in the actual HTML content.
 * - $title_suffix (array): An array containing additional output populated by
 *   modules, intended to be displayed after the main title tag that appears in
 *   the template.
 * - $messages: HTML for status and error messages. Should be displayed
 *   prominently.
 * - $tabs (array): Tabs linking to any sub-pages beneath the current page
 *   (e.g., the view and edit tabs when displaying a node).
 * - $action_links (array): Actions local to the page, such as 'Add menu' on the
 *   menu administration interface.
 * - $feed_icons: A string of all feed icons for the current page.
 * - $node: The node object, if there is an automatically-loaded node
 *   associated with the page, and the node ID is the second argument
 *   in the page's path (e.g. node/12345 and node/12345/revisions, but not
 *   comment/reply/12345).
 *
 * Regions:
 * - $page['navlinks']: Navigation Links
 * - $page['content']: Main Content Area
 * - $page['sidebar_first']: Sidebar first
 * - $page['sidebar_second']: Sidebar second
 * - $page['leftcollinks']: Related Links
 * - $page['contactinfo']: Contact Us
 * - $page['optionalfooter']: Optional Footer
 * - $page['footercontent']: Footer Content
 *
 * @see template_preprocess()
 * @see template_preprocess_page()
 * @see template_process()
 */

$t = unl_wdn_get_instance();

// Breadcrumbs
if (isset($breadcrumb)) {
  $t->breadcrumbs = PHP_EOL . $breadcrumb . PHP_EOL;
}

// Navigation
$t->navlinks = PHP_EOL . render($page['navlinks']);

// Site Title and Page Title
if (isset($site_name) && $site_name) {
  $t->titlegraphic = $site_name;
  if (!empty($site_slogan)) {
    $t->titlegraphic .= '<span>' . $site_slogan . '</span>';
  }
}
if (isset($title) && $title) {
  $t->pagetitle = '<h1>' . render($title_prefix) . $title . render($title_suffix) . '</h1>';
}

// Main Content Area
$format = filter_input(INPUT_GET, 'format', FILTER_SANITIZE_STRING);
if ($format == 'partial') {
  $t->maincontentarea = '';
}
else {
  $t->maincontentarea = $messages . PHP_EOL
                      . render($tabs) . PHP_EOL
                      . render($action_links) . PHP_EOL;
}

if ($page['sidebar_first']) {
  $t->maincontentarea .= render($page['sidebar_first']) . PHP_EOL;
}
$t->maincontentarea .= render($page['content']) . PHP_EOL;
if ($page['sidebar_second']) {
  $t->maincontentarea .= render($page['sidebar_second']) . PHP_EOL;
}

// Related Links
if ($page['leftcollinks']) {
  $t->leftcollinks = render($page['leftcollinks']);
}

// Contact Us
if ($page['contactinfo']) {
  $t->contactinfo = render($page['contactinfo']);
}

// Optional Footer
if ($page['optionalfooter']) {
  $t->optionalfooter = render($page['optionalfooter']);
}

// Copyright Area
if ($page['footercontent']) {
  $t->footercontent = PHP_EOL.render($page['footercontent']);
}
$t->footercontent = preg_replace(array('/©\s*[0-9]{4}/', '/&copy;\s*[0-9]{4}/'), '&copy; ' . date('Y'), $t->footercontent);
