<?php 
/**
 * Return a themed form element.
 *
 * @param element
 *   An associative array containing the properties of the element.
 *   Properties used: title, description, id, required
 * @param $value
 *   The form element's data.
 * @return
 *   A string representing the form element.
 *
 * @ingroup themeable
 */
function unl_wdn_form_element($element, $value) {
	//Based on
	//http://api.drupal.org/api/function/theme_form_element/5
	//
//print_r($element);
  $output = '';
  $output .= '<!-- start ' . $element['#type'] . '::' . $element['#id'] . ' -->' . PHP_EOL;
    
  $output  .= '<li class="form-item">'."\n";
  $required = !empty($element['#required']) ? '<span class="required" title="'. t('This field is required.') .'">*</span>' : '';

  if (!empty($element['#title'])) {
    $title = $element['#title'];
    if (!empty($element['#id'])) {
      $output .= ' <label for="'. $element['#id'] .'">'. t('!title: !required', array('!title' => filter_xss_admin($title), '!required' => $required)) ."</label>\n";
    }
    else {
      $output .= ' <label>'. t('!title: !required', array('!title' => filter_xss_admin($title), '!required' => $required)) ."</label>\n";
    }
  }
  
  if ($element['#children']) {
    $output .= '<ol>' . PHP_EOL;
  }

  $output .= " $value\n";

  if ($element['#children']) {
    $output .= '</ol>' . PHP_EOL;
  }

  if (!empty($element['#description'])) {
    $output .= ' <div class="description">'. $element['#description'] ."</div>\n";
  }  
  $output .= "</li>\n";
  
  $output .= '<!-- end ' . $element['#id'] . ' -->' . PHP_EOL;

  return $output;
}

/**
 * Format a set of radio buttons.
 *
 * @param $element
 *   An associative array containing the properties of the element.
 *   Properties used: title, value, options, description, required and attributes.
 * @return
 *   A themed HTML string representing the radio button set.
 *
 * @ingroup themeable
 */
function unl_wdn_radios($element) {
  $class = 'form-radios';
  if (isset($element['#attributes']['class'])) {
    $class .= ' '. $element['#attributes']['class'];
  }
//  $element['#children'] = '<div class="'. $class .'">'. (!empty($element['#children']) ? $element['#children'] : '') .'</div>';
  if ($element['#title'] || $element['#description']) {
    unset($element['#id']);
    return theme('form_element', $element, $element['#children']);
  }
  else {
    return $element['#children'];
  }
}

/**
 * Format a group of form items.
 *
 * @param $element
 *   An associative array containing the properties of the element.
 *   Properties used: attributes, title, value, description, children, collapsible, collapsed
 * @return
 *   A themed HTML string representing the form item group.
 *
 * @ingroup themeable
 */
function unl_wdn_fieldset($element) {
  if (!empty($element['#collapsible'])) {
    drupal_add_js('misc/collapse.js');

    if (!isset($element['#attributes']['class'])) {
      $element['#attributes']['class'] = '';
    }

    $element['#attributes']['class'] .= ' collapsible';
    if (!empty($element['#collapsed'])) {
      $element['#attributes']['class'] .= ' collapsed';
    }
  }

  return '<fieldset'. drupal_attributes($element['#attributes']) .'>'
       . ($element['#title'] ? '<legend>'. $element['#title'] .'</legend>' : '')
       . (isset($element['#description']) && $element['#description'] ? '<div class="description">' . $element['#description'] .'</div>' : '')
       . '<ol>' . PHP_EOL
       . (!empty($element['#children']) ? $element['#children'] : '')
       . (isset($element['#value']) ? $element['#value'] : '')
       . "</ol></fieldset>\n";
}
/**
 * Format a form.
 *
 * @param $element
 *   An associative array containing the properties of the element.
 *   Properties used: action, method, attributes, children
 * @return
 *   A themed HTML string representing the form.
 *
 * @ingroup themeable
 */
function unl_wdn_form($element) {
  // Anonymous div to satisfy XHTML compliance.
  $action = $element['#action'] ? 'action="'. check_url($element['#action']) .'" ' : '';
  return '<script type="text/javascript">
			WDN.loadCSS("http://localhost/wdn/templates_3.0/css/content/forms.css");
			WDN.loadCSS("http://localhost/workspace/UNL_ENews/www/css/all.css");
		</script>
		<form class="energetic" '. $action .' accept-charset="UTF-8" method="'. $element['#method'] .'" id="'. $element['#id'] .'"'. drupal_attributes($element['#attributes']) .">\n". $element['#children'] ."\n</form>\n";
}




