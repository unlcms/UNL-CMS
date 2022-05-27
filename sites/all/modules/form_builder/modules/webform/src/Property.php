<?php

namespace Drupal\form_builder_webform;

use Drupal\form_builder\PropertyBase;

class Property extends PropertyBase {

  protected $storageParents;

  /**
   * {@inheritdoc}
   */
  public function __construct($property, $params, $form_type_name) {
    $params += array('storage_parents' => array($property));
    parent::__construct($property, $params, $form_type_name);
    $this->storageParents = $params['storage_parents'];
  }

  /**
   * {@inheritdoc}
   */
  public function setValue(&$component, $value) {
    drupal_array_set_nested_value($component, $this->storageParents, $value);
  }

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
  public function form($component, $edit, &$form_state) {
    // Set weight to just anything. Element positions aren't configured in
    // this way in form_builder.
    $component['weight'] = 0;
    if (isset($this->params['form']) && function_exists($this->params['form'])) {
      $function = $this->params['form'];
      return $function($component, $edit, $form_state, $this->property);
    }
    return array();
  }

  /**
   * Read the value from a component array.
   */
  public function getValue($component) {
    return drupal_array_get_nested_value($component, $this->storageParents);
  }

}
