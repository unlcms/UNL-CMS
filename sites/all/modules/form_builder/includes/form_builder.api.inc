<?php

/**
 * @file form_builder.api.inc
 * Universally used API functions within the Form builder module.
 */

use Drupal\form_builder\FormBase;
use Drupal\form_builder\Loader;

/**
 * Retrieve multiple elements within a form structure.
 *
 * @param $form
 *   A complete hierarchical FAPI structure.
 * @param $element_ids
 *   An array of unique identifiers for elements that are to be retreived. These
 *   identifiers match against the special property
 *   "#form_builder['element_id']", which is not available in normal FAPI
 *   structures. It must be added by the respective module that is providing
 *   support for a certain field type.
 *
 *   For example, CCK provides a unique identifier for each field such as
 *   "field_my_name". This field name must be added to the form array as
 *   #form_builder['element_id'] = 'field_my_name' in CCK's implementation of
 *   hook_form_builder_load().
 * @return
 *   A single Form API element array.
 */
function form_builder_get_elements(&$form, $element_ids) {
  return FormBase::fromArray($form)->getFormArray();
}

/**
 * Helper function to sort elements by 'weight' and 'title'.
 */
function _form_builder_sort($a, $b) {
  $a_weight = (is_array($a) && isset($a['weight'])) ? $a['weight'] : 0;
  $b_weight = (is_array($b) && isset($b['weight'])) ? $b['weight'] : 0;
  if ($a_weight == $b_weight) {
    if (!isset($b['title'])) {
      return -1;
    }
    if (!isset($a['title'])) {
      return 1;
    }
    return strcasecmp($a['title'], $b['title']);
  }
  return ($a_weight < $b_weight) ? -1 : 1;
}
