<?php

namespace Drupal\form_builder_webform;

/**
 * Special handling for $component['value'].
 */
class PropertyValue extends Property {

  /**
   * {@inheritdoc}
   */
  public function setValue(&$component, $value) {
    if (is_array($value)) {
      $value = implode(',', $value);
    }
    parent::setValue($component, $value);
  }

}
