<?php

include dirname(__FILE__) . '/includes/form.inc';

function unl_wdn_breadcrumb($breadcrumbs)
{
	if (count($breadcrumbs) == 0) {
		$breadcrumbs[] = variable_get('site_name', 'Department');
	} else {
	    //Change 'Home' to be $site_name
	    array_unshift($breadcrumbs,
	                  str_replace('Home', variable_get('site_name', 'Department'),
	                  array_shift($breadcrumbs)));
	}
    //Prepend UNL
    array_unshift($breadcrumbs, '<a href="http://www.unl.edu/">UNL</a>');
    
    //Append title of current page -- http://drupal.org/node/133242
    $breadcrumbs[] = drupal_get_title();
    
    $html = '<ul>' . PHP_EOL;
    foreach ($breadcrumbs as $breadcrumb) {
        $html .= '<li>' .  $breadcrumb . '</li>';
    }
    $html .= '</ul>';
    
    return $html;
}

function unl_wdn_head_title()
{
    // Based on
    // http://api.drupal.org/api/function/menu_get_active_breadcrumb/5
    // We don't have to add the current page, as drupal normally drops it
    $path[] = 'Home';
    
    //  $trail = _menu_get_active_trail();
    $trail = array();
    foreach ($trail as $mid) {
        $item = menu_get_item($mid);
        
        if ($item['type'] & MENU_VISIBLE_IN_BREADCRUMB) {
            $path[] = $item['title'];
        }
    }
    
    // Change 'Home' to be $site_name
    array_unshift($path, str_replace( 'Home', variable_get('site_name', 'Department'), array_shift($path)));
    
    //Prepend UNL
    array_unshift($path, 'UNL');
    
    return implode(' | ', $path);
}

function unl_wdn_menu_item($link, $has_children, $menu = '', $in_active_trail = FALSE, $extra_class = NULL)
{
    if ($extra_class) {
        return '<li class="' . $extra_class . '">' . $link . $menu . '</li>' . "\n";
    } else {
        return '<li>' . $link . $menu . '</li>' . PHP_EOL;
    }
}

function unl_wdn_menu_tree($tree)
{
    return '<ul>' . $tree . '</ul>' . PHP_EOL;
}

function unl_wdn_theme()
{
	return array('page_node_form' => array('arguments' => array('form' => NULL),));
}

