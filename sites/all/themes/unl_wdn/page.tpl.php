<?php
/**
 * @file
 * UNL_WDN theme implementation to display a single Drupal page.
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
 * - $page['leftcollinks']: Related Links
 * - $page['contactinfo']: Contact Us
 * - $page['optionalfooter']: Optional Footer
 * - $page['footercontent']: Footer Content
 *
 * @see template_preprocess()
 * @see template_preprocess_page()
 * @see template_process()
 */
?>
<p class="skipnav"> <a class="skipnav" href="#maincontent">Skip Navigation</a> </p>
<div id="wdn_wrapper">
    <div id="header"> <a href="http://www.unl.edu/" title="UNL website"><img src="/wdn/templates_3.0/images/logo.png" alt="UNL graphic identifier" id="logo" /></a>
        <h1>University of Nebraska&ndash;Lincoln</h1>
        <?php echo file_get_contents($_SERVER['DOCUMENT_ROOT'].'/wdn/templates_3.0/includes/wdnTools.html'); ?>
    </div>
    <div id="wdn_navigation_bar">
        <div id="breadcrumbs">
            <!-- WDN: see glossary item 'breadcrumbs' -->
            <!-- TemplateBeginEditable name="breadcrumbs" -->
            <?php print $breadcrumb; ?>
            <!-- TemplateEndEditable --></div>
        <div id="wdn_navigation_wrapper">
            <div id="navigation"><!-- TemplateBeginEditable name="navlinks" -->
                <?php print render($page['navlinks']); ?>
                <!-- TemplateEndEditable --></div>
        </div>
    </div>
    <div id="wdn_content_wrapper">
        <div id="titlegraphic"><!-- TemplateBeginEditable name="titlegraphic" -->
            <h1><?php print $site_name; ?></h1>
            <!-- TemplateEndEditable --></div>
        <div id="pagetitle"><!-- TemplateBeginEditable name="pagetitle" -->
            <h2><?php print $title; ?></h2>
            <!-- TemplateEndEditable --></div>
        <div id="maincontent">
            <!--THIS IS THE MAIN CONTENT AREA; WDN: see glossary item 'main content area' -->
            <!-- TemplateBeginEditable name="maincontentarea" -->
            <?php // echo '<pre>';var_dump($page);exit();//$messages . PHP_EOL
                   // . render($tabs) . PHP_EOL
                   // . render($action_links) . PHP_EOL
                  //  . '<h3>' . render($title_prefix) . $title . render($title_suffix) . '</h3>'?>
            <?php print strtr(render($page['content']), array('sticky-enabled' => 'zentable cool')); ?>
            <!-- TemplateEndEditable -->
            <div class="clear"></div>
            <?php echo file_get_contents($_SERVER['DOCUMENT_ROOT'].'/wdn/templates_3.0/includes/noscript.html'); ?>
            <!--THIS IS THE END OF THE MAIN CONTENT AREA.-->
        </div>
        <div id="footer">
            <div id="footer_floater"></div>
            <div class="footer_col">
                <?php echo file_get_contents($_SERVER['DOCUMENT_ROOT'].'/wdn/templates_3.0/includes/feedback.html'); ?>
            </div>
            <div class="footer_col"><!-- TemplateBeginEditable name="leftcollinks" -->
                <h3>Related Links</h3>
                <?php if ($page['leftcollinks']) : ?>
                <?php render($page['leftcollinks']); ?>
                <?php else : ?>
                <ul>
                    <li><a href="http://ucomm.unl.edu/">University Communications</a></li>
                    <li><a href="http://www.unl.edu/ucomm/chancllr/">Office of the Chancellor</a></li>
                </ul>
                <?php endif; ?>
                <!-- TemplateEndEditable --></div>
            <div class="footer_col"><!-- TemplateBeginEditable name="contactinfo" -->
                <h3>Contacting Us</h3>
                <?php if ($page['contactinfo']) : ?>
                <?php render($page['contactinfo']); ?>
                <?php else : ?>
                <p>
                    <strong>University of Nebraska-Lincoln</strong><br />
                    1400 R Street<br />
                    Lincoln, NE 68588<br />
                    402-472-7211
                </p>
                <?php endif; ?>
                <!-- TemplateEndEditable --></div>
            <div class="footer_col">
                <?php echo file_get_contents($_SERVER['DOCUMENT_ROOT'].'/wdn/templates_3.0/includes/socialmediashare.html'); ?>
            </div>
            <!-- TemplateBeginEditable name="optionalfooter" -->
            <?php if ($page['optionalfooter']) : ?>
            <?php render($page['optionalfooter']); ?>
            <?php endif; ?>
            <!-- TemplateEndEditable -->
            <div id="wdn_copyright"><!-- TemplateBeginEditable name="footercontent" -->
                <?php if ($page['footercontent']) : ?>
                <?php render($page['footercontent']); ?>
                <?php else : ?>
                &copy; 2010 University of Nebraska&ndash;Lincoln | Lincoln, NE 68588 | 402-472-7211 | <a href="http://www.unl.edu/ucomm/aboutunl/" title="Click here to know more about UNL">About UNL</a> | <a href="http://www1.unl.edu/comments/" title="Click here to direct your comments and questions">comments?</a><br />
                UNL is an equal opportunity employer with a comprehensive plan for diversity. Find out more: <a href="https://employment.unl.edu/" target="_blank" title="Employment at UNL">employment.unl.edu</a><br />
                <?php endif; ?>
                <p style="margin:0.5em 0 -1.4em 0">This site is an instance of <a href="http://unlcms.unl.edu/" title="Go to the UNL CMS website">UNL CMS</a> powered by <a href="http://drupal.org/" title="Go to the official website of Drupal">Drupal</a></p>
                <!-- TemplateEndEditable -->
                <?php echo file_get_contents($_SERVER['DOCUMENT_ROOT'].'/wdn/templates_3.0/includes/wdn.html'); ?>
                | <a href="http://validator.unl.edu/check/referer">W3C</a> | <a href="http://jigsaw.w3.org/css-validator/check/referer?profile=css3">CSS</a> <a href="http://www.unl.edu/" title="UNL Home" id="wdn_unl_wordmark"><img src="/wdn/templates_3.0/css/footer/images/wordmark.png" alt="UNL's wordmark" /></a> </div>
        </div>
    </div>
    <div id="wdn_wrapper_footer"> </div>
</div>