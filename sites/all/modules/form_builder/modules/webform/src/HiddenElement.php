<?php

namespace Drupal\form_builder_webform;

/**
 * Special behaviour for hidden elements.
 */
class HiddenElement extends Element {

  /**
   * {@inheritdoc}
   */
  public function configurationForm($form, &$form_state) {
    $form = parent::configurationForm($form, $form_state);

    // Configure default value as textarea to allow for more data.
    $form['default_value']['#type'] = 'textarea';

    // Add configuration for $component['extra']['hidden_type'].
    $display['#form_builder']['property_group'] = 'display';
    $e = webform_component_invoke('hidden', 'edit', $this->element['#webform_component']);
    $form['hidden_type'] = $e['display']['hidden_type'] + $display;

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function configurationSubmit(&$form, &$form_state) {
    parent::configurationSubmit($form, $form_state);
    $this->element['#webform_component']['extra']['hidden_type'] = $form_state['values']['extra']['hidden_type'];
  }

}
