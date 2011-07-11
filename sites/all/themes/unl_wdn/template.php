<?php

/**
 * Implements hook_css_alter().
 */
function unl_wdn_css_alter(&$css) {
  // Turn off some styles from the system module.
  // If some of this is later found desireable, add "stylesheets[all][] = css/unl_wdn.menus.css" to unl_wdn.info and copy these files to that locaiton with edits.
  $path = drupal_get_path('module','system');
  unset($css[$path.'/system.menus.css']);
  unset($css[$path.'/system.theme.css']);
}

/**
 * Implements template_preprocess_field().
 */
function unl_wdn_preprocess_field(&$vars, $hook) {
  $element = $vars['element'];
  // Set the field label tag to a header or default to div
  if (strlen($element['#label_display']) == 2 && substr($element['#label_display'], 0, 1) == 'h') {
    $vars['label_html_tag'] = $element['#label_display'];
  }
  else {
    $vars['label_html_tag'] = 'div';
  }
}

/**
 * Implements template_preprocess_html().
 */
function unl_wdn_preprocess_html(&$vars, $hook) {
  /**
   * Change the <title> tag to UNL format: UNL | Department | Section | Page
   */
  $head_title[] = 'Home';

  $trail = menu_get_active_trail();
  foreach ($trail as $item) {
    if ($item['type'] & MENU_VISIBLE_IN_BREADCRUMB) {
      if (isset($item['title']) && !empty($item['title'])) {
          $head_title[] = $item['title'];
      }
      if (isset($item['page_arguments'],
                $item['page_arguments'][0],
                $item['page_arguments'][0]->title)) {
          $head_title[] = $item['page_arguments'][0]->title;
      }
    }
  }

  // Change 'Home' to be $site_name
  array_unshift($head_title, str_replace( 'Home', variable_get('site_name', 'Department'), array_shift($head_title)));

  //Prepend UNL
  if (variable_get('site_name') != 'UNL') {
    array_unshift($head_title, 'UNL');
  }

  $vars['head_title'] = implode(' | ', $head_title);
}

function unl_wdn_preprocess_node(&$vars) {
  // Drupal doesn't correctly set the $page flag for the preview on node/add/page which results in the <h2> being displayed in modules/node/node.tpl.php
  if (isset($vars['elements']['#node']->op) && $vars['elements']['#node']->op == 'Preview') {
    $vars['page'] = true;
  }
}

function unl_wdn_preprocess_page(&$vars, $hook) {
  //Unset the sidebars if on a user page (i.e. user profile or imce file browser)
  if (arg(0) == 'user') {
    $vars['page']['sidebar_first'] = array();
    $vars['page']['sidebar_second'] = array();
  }
}

function unl_wdn_get_instance() {
  static $instance;
  if (!$instance) {
    set_include_path(dirname(__FILE__) . '/lib/php');
    require_once "UNL/Templates.php";

    UNL_Templates::$options['version'] = UNL_Templates::VERSION3;

    if (theme_get_setting('toggle_main_menu')) {
      $instance = UNL_Templates::factory('Fixed');
    }
    else {
      $instance = UNL_Templates::factory('Document');
    }

    if (theme_get_setting('wdn_beta')) {
      UNL_Templates::$options['templatedependentspath'] = $_SERVER['DOCUMENT_ROOT'].'/wdntemplates-dev';
    }
  }

  return $instance;
}

require_once dirname(__FILE__) . '/includes/form.inc';

function unl_wdn_breadcrumb($variables) {
  $breadcrumbs = $variables['breadcrumb'];

  if (count($breadcrumbs) == 0) {
    $breadcrumbs[] = '<a href="">' . unl_get_site_name_abbreviated() . '</a>';
  }
  else {
    //Change 'Home' to be $site_name
    array_unshift($breadcrumbs,
                  str_replace('Home', unl_get_site_name_abbreviated(),
                  array_shift($breadcrumbs)));
  }

  //Add the intermediate breadcrumbs if they exist
  $intermediateBreadcrumbs = theme_get_setting('intermediate_breadcrumbs');
  if (is_array($intermediateBreadcrumbs)) {
    krsort($intermediateBreadcrumbs);
    foreach ($intermediateBreadcrumbs as $intermediateBreadcrumb) {
      if ($intermediateBreadcrumb['text'] && $intermediateBreadcrumb['href']) {
        array_unshift($breadcrumbs, '<a href="' . $intermediateBreadcrumb['href'] . '">' . $intermediateBreadcrumb['text'] . '</a>');
      }
    }
  }

  //Prepend UNL
  if (variable_get('site_name') != 'UNL') {
    array_unshift($breadcrumbs, '<a href="http://www.unl.edu/">UNL</a>');
  }

  //Append title of current page -- http://drupal.org/node/133242
  if (!drupal_is_front_page()) {
    $breadcrumbs[] = drupal_get_title();
  }

  $html = '<ul>' . PHP_EOL;
  foreach ($breadcrumbs as $breadcrumb) {
    $html .= '<li>' .  $breadcrumb . '</li>';
  }
  $html .= '</ul>';

  return $html;
}

function unl_wdn_menu_item($link, $has_children, $menu = '', $in_active_trail = FALSE, $extra_class = NULL) {
  if ($extra_class) {
    return '<li class="' . $extra_class . '">' . $link . $menu . '</li>' . "\n";
  }
  else {
    return '<li>' . $link . $menu . '</li>' . PHP_EOL;
  }
}

function unl_wdn_menu_tree($variables) {
  $tree = $variables['tree'];
  return '<ul>' . $tree . '</ul>' . PHP_EOL;
}

function unl_wdn_menu_local_tasks($variables) {
  $output = '';

  if (!empty($variables['primary'])) {
    $variables['primary']['#prefix'] = '<ul class="wdn_tabs cms_tabs disableSwitching">';
    $variables['primary']['#suffix'] = '</ul>';
    $output .= drupal_render($variables['primary']);
  }
  if (!empty($variables['secondary'])) {
    $variables['secondary']['#prefix'] = '<ul class="wdn_tabs cms_tabs disableSwitching">';
    $variables['secondary']['#suffix'] = '</ul>';
    $output .= drupal_render($variables['secondary']);
  }

  return $output;
}

function unl_wdn_menu_local_task($variables) {
  $link = $variables['element']['#link'];
  $link_text = $link['title'];

  if (!empty($variables['element']['#active'])) {
    // If the link does not contain HTML already, check_plain() it now.
    // After we set 'html'=TRUE the link will not be sanitized by l().
    if (empty($link['localized_options']['html'])) {
      $link['title'] = check_plain($link['title']);
    }
    $link['localized_options']['html'] = TRUE;
    $link_text = t('!local-task-title !active', array('!local-task-title' => $link['title'], '!active' => ''));
  }
  return '<li' . (!empty($variables['element']['#active']) ? ' class="selected"' : '') . '>' . l($link_text, $link['href'], $link['localized_options']) . "</li>\n";
}

function unl_wdn_status_messages($variables) {
  $display = $variables['display'];

  $output = '';
  foreach (drupal_get_messages($display) as $type => $messages) {
    switch ($type) {
      case 'status':
        $extra_class = ' affirm';
        break;
      
      case 'warning':
        $extra_class = ' alert';
        break;
      
      case 'error':
        $extra_class = ' negate';
        break;
        
      default:
        $extra_class = '';
        break;
    }
    $type = ucfirst($type);
    $output .= <<<EOF
<div class="wdn_notice$extra_class">
    <div class="close">
        <a href="#" title="Close this notice">Close this notice</a>
    </div>
    <div class="message">
        <h4>$type</h4>
EOF;
    if (count($messages) > 1) {
      $output .= '<ul>' . PHP_EOL;
      foreach ($messages as $message) {
        $output .= '<li>' . $message . '</li>' . PHP_EOL;
      }
      $output .= '</ul>' . PHP_EOL;
    }
    else {
      $output .= $messages[0];
    }
    $output .= <<<EOF
    </div>
</div>
EOF;
  }

  if (!$output) {
    return '';
  }

  $output = <<<EOF
<script type="text/javascript">
WDN.initializePlugin('notice');
</script>
$output
EOF;

  return $output;
}

/**
 * Return the abbreviated site name, assuming it has been set and we're not on the front page.
 * Otherwise, it returns the full site name.
 */
function unl_get_site_name_abbreviated() {
  if (!drupal_is_front_page() && theme_get_setting('site_name_abbreviation')) {
    return theme_get_setting('site_name_abbreviation');
  }
  else {
    return variable_get('site_name', 'Department');
  }
}
