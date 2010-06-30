<?php
/**
 * @file
 * Default theme implementation to display the basic html structure of a single
 * Drupal page.
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

$t->head .= PHP_EOL
          . $head . PHP_EOL
          . $styles . PHP_EOL
          . $scripts . PHP_EOL
          . '<link href="' . url('<front>', array('absolute' => TRUE)) . '" rel="home" />' . PHP_EOL
          . '<link rel="logout" href="user/logout" title="Log out of myapp" />' . PHP_EOL
          ;

if (theme_get_setting('use_base')) {
	$t->head .= '<base href="' . url('<front>', array('absolute' => TRUE)) . '" />' . PHP_EOL;
}

$t->doctitle = '<title>'. unl_wdn_head_title() .'</title>';

$html = $t->toHtml();

$html = strtr($html, array('<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">' => '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="'.$language->language.'" version="XHTML+RDFa 1.0" dir="'.$language->dir.'" '.$rdf_namespaces.'>',
                           '<head>'                 => '<head profile="'.$grddl_profile.'">',
                           '<body class="fixed">'   => '<body class="fixed '.$classes.'" '.$attributes.'>',
                           '<p class="skipnav">'    => $page_top . PHP_EOL . '<p class="skipnav">',
                           '</body>'                => $page_bottom . PHP_EOL . '</body>',
                          ));

echo $html;
