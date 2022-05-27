<?php

namespace Drupal\form_builder_webform;

/**
 * Special handling for $component['options'].
 */
class PropertyOptions extends Property {

  public function submit($form, &$form_state) {
    // Webform needs the options to be saved as text.
    $options = $form_state['values']['options']['options_field'];
    parent::submit($form, $form_state);
    $form_state['values']['options'] = $options;
  }

}
