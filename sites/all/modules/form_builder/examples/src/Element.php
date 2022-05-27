<?php

namespace Drupal\form_builder_examples;

use Drupal\form_builder\ElementBase;

/**
 * Form builder element for the example form type.
 */
class Element extends ElementBase {

  /**
   * {@inheritdoc}
   */
  public function configurationForm($form, &$form_state) {
    $form['#_edit_element'] = $this->element;
    foreach ($this->getProperties() as $property) {
      $form = array_merge($form, $property->form($form_state, $this));
    }
    return $form;
  }

}
