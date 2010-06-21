<?php
require_once(dirname(__FILE__)).'/includes/form.inc';

//This is supposed to be how we change $head_title in the <title> tag in html.tpl.php but as of 7.0-alpha5 it don't work for eric
//TODO: fix this
function unl_wdn_preprocess_html(&$vars)
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
    
    $vars['head_title'] = implode(' | ', $path);
}

function unl_wdn_breadcrumb($variables)
{
    $breadcrumbs = $variables['breadcrumb'];
    
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

function unl_wdn_menu_item($link, $has_children, $menu = '', $in_active_trail = FALSE, $extra_class = NULL)
{
    if ($extra_class) {
        return '<li class="' . $extra_class . '">' . $link . $menu . '</li>' . "\n";
    } else {
        return '<li>' . $link . $menu . '</li>' . PHP_EOL;
    }
}

function unl_wdn_menu_tree($variables)
{
    $tree = $variables['tree'];
    return '<ul>' . $tree . '</ul>' . PHP_EOL;
}

function unl_wdn_menu_local_tasks()
{
    $output = array();
    
    if ($primary = menu_primary_local_tasks()) {
        $primary['#prefix'] = '<ul class="wdn_tabs disableSwitching">';
        $primary['#suffix'] = '</ul>';
        $output[] = $primary;
    }
    if ($secondary = menu_secondary_local_tasks()) {
        $secondary['#prefix'] = '<ul class="wdn_tabs disableSwitching">';
        $secondary['#suffix'] = '</ul>';
        $output[] = $secondary;
    }
    
    return $output;
}

function unl_wdn_menu_local_task($variables)
{
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

function unl_wdn_status_messages($variables)
{
    $display = $variables['display'];
    
    $output = '';
    foreach (drupal_get_messages($display) as $type => $messages) {
        $type = ucfirst($type);
        $output .= <<<EOF
<div class="wdn_notice">
	<div class="close">
		<a href="#" title="Close this notice">Close this notice</a>
	</div>
	<div class="message">
	    <h3>$type</h3>
EOF;
        if (count($messages) > 1) {
            $output .= '<ul>' . PHP_EOL;
            foreach ($messages as $message) {
                $output .= '<li>' . $message . '</li>' . PHP_EOL;
            }
            $output .= '</ul>' . PHP_EOL;
        } else {
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
