<?php

function unl_wdn_form($variables)
{
    if (!theme_get_setting('zen_forms')) {
        return theme_form($variables);
    }

    $output = <<<EOF
<script type="text/javascript">
//<![CDATA[
	WDN.jQuery(document).ready(function(){
	     WDN.initializePlugin('zenform');
	});
//]]>
</script>

EOF;
    
    $element = $variables['element'];
    $element['#attributes']['class'][] = 'zenform';
    $action = $element['#action'] ? 'action="' . check_url($element['#action']) . '" ' : '';
    $output .= PHP_EOL . '<!-- FORM STARTS HERE -->' . PHP_EOL . PHP_EOL;
    $output .= '<form ' . $action . ' accept-charset="UTF-8" method="' . $element['#method'] . '" id="' . $element['#id'] . '"' . drupal_attributes($element['#attributes']) . ">\n" . $element['#children'] . "\n</form>\n";
    $output .= PHP_EOL . PHP_EOL . '<!-- FORM ENDS HERE -->' . PHP_EOL;
    return $output;
}

function unl_wdn_fieldset($variables)
{
    if (!theme_get_setting('zen_forms')) {
        return theme_fieldset($variables);
    }
    
    $element = $variables['element'];

    $output = '<fieldset' . drupal_attributes($element['#attributes']) . '>';
    if (!empty($element['#title'])) {
        // Always wrap fieldset legends in a SPAN for CSS positioning.
        $output .= '<legend>' . $element['#title'] . '</legend>';
    }
    if (!empty($element['#description'])) {
        $output .= '<div>' . $element['#description'] . '</div>';
    }
    $output .= '<ol>' . $element['#children'] . '</ol>';
    if (isset($element['#value'])) {
        $output .= $element['#value'];
    }
    $output .= "</fieldset>\n";
    return $output;
}

function unl_wdn_form_element($variables)
{
    if (!theme_get_setting('zen_forms')) {
        return theme_form_element($variables);
    }
    
    $element = $variables['element'];
    // This is also used in the installer, pre-database setup.
    $t = get_t();
    
    // Add element #id for #type 'item'.
    if (isset($element['#markup']) && !empty($element['#id'])) {
        $attributes['id'] = $element['#id'];
    }
    // Add element's #type and #name as class to aid with JS/CSS selectors.
    $attributes['class'] = array();
    $output = '<li' . drupal_attributes($attributes) . '>' . "\n";
    
    // If #title is not set, we don't display any label or required marker.
    if (!isset($element['#title'])) {
        $element['#title_display'] = 'none';
    }
    $prefix = isset($element['#field_prefix']) ? '<span class="field-prefix">' . $element['#field_prefix'] . '</span> ' : '';
    $suffix = isset($element['#field_suffix']) ? ' <span class="field-suffix">' . $element['#field_suffix'] . '</span>' : '';
    
    switch ($element['#title_display']) {
        case 'before':
            $output .= ' ' . theme('form_element_label', $variables);
            $output .= ' ' . $prefix . $element['#children'] . $suffix . "\n";
            break;
        
        case 'invisible':
        case 'after':
            $output .= ' ' . $prefix . $element['#children'] . $suffix;
            $output .= ' ' . theme('form_element_label', $variables) . "\n";
            break;
        
        case 'none':
        case 'attribute':
            // Output no label and no required marker, only the children.
            $output .= ' ' . $prefix . $element['#children'] . $suffix . "\n";
            break;
    }
    
    $output .= "</li>\n";
    
    return $output;
}


function unl_wdn_form_element_label($variables)
{
    if (!theme_get_setting('zen_forms')) {
        return theme_form_element_label($variables);
    }
    
    $element = $variables['element'];
    
    if (!$element['#title']) {
        return '';
    }
    
    $attributes = array();
    if (!empty($element['#id'])) {
        $attributes['for'] = $element['#id'];
    }
    
    $output = '<label ' . drupal_attributes($attributes) . '>' . PHP_EOL
            . ($element['#required'] ? '<span class="required">*</span>' . PHP_EOL : '')
            . filter_xss_admin($element['#title']) . PHP_EOL
            . (isset($element['#description']) ? '<span class="helper">' . $element['#description'] . '</span>' . PHP_EOL : '')
            .'</label>' . PHP_EOL;
    
    return $output;
}


