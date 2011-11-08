<?php

/**
 * @file
 * Hooks to support UNL_WDN zenform styling and markup
 */

/**
 * Implements theme_webform_element().
 */
function unl_wdn_webform_element($variables) {
  // Ensure defaults.
  $variables['element'] += array(
    '#title_display' => 'before',
  );

  $element = $variables['element'];

  // All elements using this for display only are given the "display" type.
  if (isset($element['#format']) && $element['#format'] == 'html') {
    $type = 'display';
  }
  else {
    $type = (isset($element['#type']) && !in_array($element['#type'], array('markup', 'textfield'))) ? $element['#type'] : $element['#webform_component']['type'];
  }
  $parents = str_replace('_', '-', implode('--', array_slice($element['#parents'], 1)));

  $wrapper_classes = array(
   'form-item',
   'webform-component',
   'webform-component-' . $type,
  );
  if (isset($element['#title_display']) && $element['#title_display'] == 'inline') {
    $wrapper_classes[] = 'webform-container-inline';
  }
  $output = '<li class="' . implode(' ', $wrapper_classes) . '" id="webform-component-' . $parents . '">' . "\n";
  $required = !empty($element['#required']) ? '<span class="form-required" title="' . t('This field is required.') . '">*</span>' : '';

  // If #title is not set, we don't display any label or required marker.
  if (!isset($element['#title'])) {
    $element['#title_display'] = 'none';
  }
  $prefix = isset($element['#field_prefix']) ? '<span class="field-prefix">' . _webform_filter_xss($element['#field_prefix']) . '</span> ' : '';
  $suffix = isset($element['#field_suffix']) ? ' <span class="field-suffix">' . _webform_filter_xss($element['#field_suffix']) . '</span>' : '';

  switch ($element['#title_display']) {
    case 'inline':
    case 'before':
    case 'invisible':
      $output .= ' ' . theme('form_element_label', $variables);
      $output .= ' ' . $prefix . $element['#children'] . $suffix . "\n";
      break;

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

  if (!empty($element['#description'])) {
    $output .= ' <div class="description">' . $element['#description'] . "</div>\n";
  }

  $output .= "</li>\n";

  return $output;
}

/**
 * Implements theme_webform_date().
 */
function unl_wdn_webform_date($variables) {
    $element = $variables['element'];

    $element['year']['#attributes']['class'] = array('year');
    $element['month']['#attributes']['class'] = array('month');
    $element['day']['#attributes']['class'] = array('day');

    // Add error classes to all items within the element.
    if (form_get_error($element)) {
        $element['year']['#attributes']['class'][] = 'error';
        $element['month']['#attributes']['class'][] = 'error';
        $element['day']['#attributes']['class'][] = 'error';
    }

    $class = array('webform-container-inline');

    // Add the JavaScript calendar if available (provided by Date module package).
    if (!empty($element['#datepicker'])) {
        $class[] = 'webform-datepicker';
        $calendar_class = array('webform-calendar');
        if ($element['#start_date']) {
            $calendar_class[] = 'webform-calendar-start-' . $element['#start_date'];
        }
        if ($element['#end_date']) {
            $calendar_class[] = 'webform-calendar-end-' . $element['#end_date'];
        }
        $calendar_class[] ='webform-calendar-day-' . variable_get('date_first_day', 0);

        $calendar = theme('webform_calendar', array('component' => $element['#webform_component'], 'calendar_classes' => $calendar_class));
    }

    $output = '';
    $output .= '<ol class="' . implode(' ', $class) . '">';
    $output .= drupal_render_children($element);
    $output .= isset($calendar) ? $calendar : '';
    $output .= '</ol>';

    return $output;
}