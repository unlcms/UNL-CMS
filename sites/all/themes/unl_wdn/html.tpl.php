<?php
/**
 * @file
 * unl_wdn theme implementation to display the basic html structure of a single Drupal page.
 *
 * Variables:
 * - $css: An array of CSS files for the current page.
 * - $language: (object) The language the site is being displayed in.
 *   $language->language contains its textual representation.
 *   $language->dir contains the language direction. It will either be 'ltr' or 'rtl'.
 * - $rdf_namespaces: All the RDF namespace prefixes used in the HTML document.
 * - $grddl_profile: A GRDDL profile allowing agents to extract the RDF data.
 * - $head_title: A modified version of the page title, for use in the TITLE tag.
 * - $head: Markup for the HEAD section (including meta tags, keyword tags, and
 *   so on).
 * - $styles: Style tags necessary to import all CSS files for the page.
 * - $scripts: Script tags necessary to load the JavaScript files and settings
 *   for the page.
 * - $page_top: Initial markup from any modules that have altered the
 *   page. This variable should always be output first, before all other dynamic
 *   content.
 * - $page: The rendered page content.
 * - $page_bottom: Final closing markup from any modules that have altered the
 *   page. This variable should always be output last, after all other dynamic
 *   content.
 * - $classes String of classes that can be used to style contextually through
 *   CSS.
 *
 * @see template_preprocess()
 * @see template_preprocess_html()
 * @see template_process()
 */

$t = unl_wdn_get_instance();

if (theme_get_setting('use_base')) {
  $t->head = PHP_EOL
           . '<base href="' . url('<front>', array('absolute' => TRUE)) . '" />' . PHP_EOL
           . $t->head;
}

$t->head .= PHP_EOL
          . $head . PHP_EOL
          . $styles . PHP_EOL
          . $scripts . PHP_EOL
          . '<link rel="logout" href="user/logout" />' . PHP_EOL
          . '<link rel="login" href="' . url('user', array('query' => drupal_get_destination())) . '" />' . PHP_EOL
          . theme_get_setting('head_html') . PHP_EOL
          ;

$t->doctitle = '<title>'.$head_title.'</title>';

$html = $t->toHtml();

if (theme_get_setting('wdn_beta')) {
  $html = str_replace(
            array('/wdn/templates',
                  'css/all.css',
                  'scripts/all.js'),
            array('/wdntemplates-dev/wdn/templates',
                  'css/debug.css',
                  'scripts/debug.js'),
            $html);
}

if (module_exists('rdf')) {
  $html = str_replace(
            array('<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">',
                  '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">',
                  '<head>'),
            array('<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="'.$language->language.'" version="XHTML+RDFa 1.0" dir="'.$language->dir.'" '.$rdf_namespaces.'>'.PHP_EOL,
                  '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML+RDFa 1.0//EN" "http://www.w3.org/MarkUp/DTD/xhtml-rdfa-1.dtd">',
                  '<head profile="'.$grddl_profile.'">'),
            $html);
}

$html = str_replace(
          array('<body class="fixed">',
                '<p class="skipnav">',
                '</body>'),
          array('<body class="fixed '.$classes.'" '.$attributes.'>',
                $page_top . PHP_EOL . '<p class="skipnav">',
                $page_bottom . PHP_EOL . '</body>'),
          $html);

$format = filter_input(INPUT_GET, 'format', FILTER_SANITIZE_STRING);
if ($format == 'partial') {
  echo $t->maincontentarea;
}
else {
  echo $html;
}