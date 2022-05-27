<?php

namespace Drupal\form_builder_examples;

use Drupal\form_builder\PropertyBase;

/**
 * Form builder property for the example form type.
 */
class Property extends PropertyBase {

  protected $property;
  protected $params;
  protected $formTypeName;

  /**
   * Generate form-API elements for editing this property.
   *
   * @param array $form_state
   *   Form API form_state of the field configure form.
   * @param \Drupal\form_builder\ElementInterface $element
   *   The currently stored element. Use this to get the "current" values.
   *
   * @return array
   *   Form-API array that will be merged into the field configure form.
   */
  public function form(&$form_state, $element) {
    $e = $element->render();
    if (isset($this->params['form']) && function_exists($this->params['form'])) {
      $function = $this->params['form'];
      $p = $this->property;
      // Set a default value on the property to avoid notices.
      $e['#' . $p] = isset($e['#' . $p]) ? $e['#' . $p] : NULL;
      return $function($form_state, $this->formTypeName, $e, $p);
    }
    return array();
  }

}
