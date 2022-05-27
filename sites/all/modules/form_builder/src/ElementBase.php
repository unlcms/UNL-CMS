<?php

namespace Drupal\form_builder;

abstract class ElementBase implements ElementInterface {

  protected $form;
  protected $params;
  protected $element;
  protected $loader;
  public function __construct($form, $params, &$element, $loader) {
    $this->form = $form;
    $this->params = $params;
    $this->element = &$element;
    $this->loader = $loader;
  }

  /**
   * Add our pre-render function to the element-array.
   */
  protected function addPreRender($element) {
    if (isset($element['#type']) && (!isset($element['#pre_render']) || !in_array('form_builder_pre_render', $element['#pre_render']))) {
      $element['#pre_render'] = array_merge(
        element_info_property($element['#type'], '#pre_render', array()),
        array('form_builder_pre_render')
      );
    }
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    return $this->addPreRender($this->element);
  }

  /**
   * {@inheritdoc}
   */
  public function getProperties() {
    $return = array();
    $properties = $this->form->getProperties();
    // Order of the properties is important because of a form-API bug.
    // See: https://www.drupal.org/node/990218.
    foreach ($this->params['properties'] as $name) {
      if (isset($properties[$name])) {
        $return[$name] = $properties[$name];
      }
    }
    return $return;
  }

  /**
   * Set the value of a property.
   *
   * This method must update the $element for rendering as well as for
   * later storage.
   *
   * @param string $property
   *   Key of the property.
   * @param mixed $value
   *   New value for the property.
   */
  protected function setProperty($property, $value) {
    // Remove empty properties entirely.
    if ($value === '' || is_null($value)) {
      unset($this->element['#'. $property]);
    }
    else {
      $this->element['#'. $property] = $value;
    }
  }

  public function getSaveableProperties() {
    return $this->params['properties'];
  }

  /**
   * {@inheritdoc}
   */
  public function configurationSubmit(&$form, &$form_state) {
    // Allow each property to do any necessary submission handling.
    foreach ($this->getProperties() as $property) {
      $property->submit($form, $form_state);
    }

    // Update the field according to the settings in $form_state['values'].
    $saveable = $this->getSaveableProperties();
    foreach ($form_state['values'] as $property => $value) {
      if (in_array($property, $saveable, TRUE)) {
        $this->setProperty($property, $value);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function title() {
    return $this->element['#title'];
  }

  public function parentId() {
    return $this->element['#form_builder']['parent_id'];
  }

  public function key() {
    return $this->element['#key'];
  }

}
