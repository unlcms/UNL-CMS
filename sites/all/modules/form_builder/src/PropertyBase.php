<?php

namespace Drupal\form_builder;

class PropertyBase implements PropertyInterface {

  protected $property;
  protected $params;
  protected $formTypeName;

  /**
   * {@inheritdoc}
   */
  public function __construct($property, $params, $form_type_name) {
    $this->property = $property;
    $this->params = $params;
    $this->formTypeName = $form_type_name;
  }

  /**
   * {@inheritdoc}
   */
  public function submit($form, &$form_state) {
    if (isset($this->params['submit'])) {
      foreach ($this->params['submit'] as $function) {
        if (function_exists($function)) {
          $function($form, $form_state);
        }
      }
    }
  }

}
