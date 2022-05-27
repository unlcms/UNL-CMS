<?php

namespace Drupal\form_builder;

interface ElementInterface {

  public function __construct($form_type, $params, &$element, $loader);

  /**
   * (Re-)Render an element.
   *
   * @return array
   *   New FAPI array reflecting all the changes made prior to callig this
   *   method.
   */
  public function render();

  /**
   * Get a list of properties available for this element.
   *
   * @return \Drupal\form_builder\PropertyInterface[]
   *   An associative array of properties keyed by the property name.
   */
  public function getProperties();

  /**
   * Get a list of properties that are supported in any way by this element.
   *
   * This returns a list of all supported properties within an element, even
   * if some of those properties do not have an interface for editing or are
   * only used internally by the module providing the form type this element
   * is being saved in.
   *
   * @return
   *   A non-indexed list of properties that may be saved for this element.
   **/
  public function getSaveableProperties();

  /**
   * Get the configuration form for this element.
   */
  public function configurationForm($form, &$form_state);

  /**
   * Submit handler for the configuration form.
   */
  public function configurationSubmit(&$form, &$form_state);

  /**
   * Get a human-readable title for this form element.
   */
  public function title();

}
