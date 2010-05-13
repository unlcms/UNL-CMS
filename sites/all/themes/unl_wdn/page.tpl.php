<?php

set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__) . '/lib');
require_once "UNL/Templates.php";

UNL_Templates::$options['version'] = UNL_Templates::VERSION3;
$t = UNL_Templates::factory('Fixed');

$t->head .= PHP_EOL
          . $head . PHP_EOL
          . $styles . PHP_EOL
          . $scripts . PHP_EOL;

$t->doctitle = '<title>'. unl_wdn_head_title() .'</title>';

if (isset($site_name)) {
    $t->titlegraphic = '<h1>' . $site_name . '</h1>';
}
if (isset($title)) {
    $t->pagetitle = '<h2>' . $title . '</h2>';
} else {
    $t->pagetitle = '';
}

if (isset($breadcrumb)) {
    $t->breadcrumbs = $breadcrumb;
}

$t->navlinks = $navlinks;

$t->maincontentarea = $maincontentprefix . PHP_EOL
                    . str_replace(array('"tabs', 'active'),
                                  array('"wdn_tabs disableSwitching', 'selected'),
                                  theme_menu_local_tasks()) . PHP_EOL
                    . str_replace(array('sticky-enabled'),
                                  array('zentable cool'),
                                  $content) . PHP_EOL
                    . $maincontentpostfix . PHP_EOL
                    . $closure . PHP_EOL;

if (!$leftcollinks) {
    $leftcollinks = <<<EOF
<ul>
    <li class="first"><a href="http://ucomm.unl.edu/">University Communications</a>
        <ul>
            <li><a href="http://ucomm.unl.edu/resources.shtml">Print Resources </a></li>
        </ul>
    </li>
    <li><a href="http://www.unl.edu/ucomm/chancllr/">Office of the Chancellor</a>  </li>
</ul>
EOF;
}

$t->leftcollinks = <<<EOF
<h3>Related Links</h3>
$leftcollinks
EOF;



if (!$contactinfo) {
    $contactinfo = <<<EOF
<p>
    The WDN is coordinated by:<br />
    <strong>University Communications</strong><br />
    Internet and Interactive Media<br />
    WICK 17<br />
    Lincoln, NE 68583-0218
</p>
EOF;
}

$t->contactinfo = <<<EOF
<h3>Contacting Us</h3>
$contactinfo
EOF;

if ($optionalfooter) {
    $t->optionalfooter = $optionalfooter;
}

$t->footercontent = '';
if ($footer_message) {
    $t->footercontent .= '<div>' . $footer_message . '</div>';
}
if ($footercontent) {
    $t->footercontent .= '<div>' . $footercontent . '</div>';
}

echo $t->toHtml();
