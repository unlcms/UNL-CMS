<?php

namespace Drupal\form_builder;

interface PropertyInterface {

  /**
   * Construct a new instance of this property class.
   *
   * @param string $property
   *   Name of the property to be manipulated by this object.
   * @param array $params
   *   Additional parameters passed to hook_form_builder_properties().
   */
  public function __construct($property, $params, $form_type_name);

  /**
   * Submit handler for the editing form().
   *
   * This function is responsible to store the new value into the $form_state.
   * The value must be located at $form_state['values'][$property].
   *
   * @param array $form_state
   *   Form API form_state of the field configure form.
   */
  public function submit($form, &$form_state);

}
