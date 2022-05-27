<?php

namespace Drupal\form_builder;

interface FormInterface {

  /**
   * Load form data from the storage backend (ie. webform components).
   *
   * @param string $form_type
   *   Name of the form_type.
   * @param mixed $form_id
   *   Primary identifier for the form. (ie. node id)
   * @param string $sid
   *   User session ID. If NULL session_id() is assumed.
   * @param array $params
   *   Additional parameters passed to hook_form_builder_form_types().
   */
  public static function loadFromStorage($form_type, $form_id, $sid, $params);

  /**
   * Save form data to the storage backend.
   */
  public function saveToStorage();

  /**
   * Load a form configuration cache.
   *
   * @param string $form_type
   *   The type of form being edited.
   * @param mixed $form_id
   *   The unique identifier for the form (within the form_type).
   * @param string $sid
   *   User session ID. If NULL session_id() is assumed.
   * @param array $params
   *   Additional parameters passed to hook_form_builder_properties().
   *
   * @return
   *   A FAPI array if a cache entry was found. Boolean FALSE if an entry does not
   *   yet exist. Note that an empty FAPI array may exist, so be sure to use
   *   strict comparison (===) when checking return values.
   */
  public static function load($form_type, $form_id, $sid, $params);


  /**
   * Construct a new instance of this form type class..
   *
   * @param string $form_type
   *   Name of the form_type.
   * @param array $params
   *   Additional parameters passed to hook_form_builder_properties().
   */
  public function __construct($form_type, $params, $form);

  /**
   * Save a form builder cache based on the form structure.
   */
  public function save();

  /**
   * Delete this cache entry from the form_builder_cache table.
   */
  public function delete();

 /**
   * Get a specific element from the form.
   *
   * @param string $elment_id
   *   Unique ID of the element.
   *
   * @return \Drupal\form_builder\ElementInterface
   *   Object representing the form element.
   */
  public function getElement($element_id);

  /**
   * Get the internal element array for an element.
   *
   * @deprecated This is only here for backwards compatibility. It will be
   *   removed in 2.0.
   *
   * @param string $element_id
   *   Unique ID of the element.
   *
   * @return array
   *   The array representing the internal state of the element.
   */
  public function getElementArray($element_id);

  /**
   * Get an array of element arrays.
   *
   * @deprecated This is only here for backwards compatibility. It will be
   *   removed in 2.0.
   *
   * @param array $element_ids
   *   Array of unique element IDs.
   *
   * @return array
   *   The array representing the internal state of the element.
   */
  public function getElementArrays($element_ids);

  /**
   * Get the complete form array (FORM_BUILDER_ROOT).
   */
  public function getFormArray();

  /**
   * Set an element array.
   *
   * @deprecated This is only here for backwards compatibility. It will be
   *   removed in 2.0.
   */
  public function setElementArray($element_a, $parent_id = FORM_BUILDER_ROOT, $alter = FALSE);

  /**
   * Remove an element from the form.
   *
   * @param string $element_id
   *   Unique ID of the element.
   */
  public function unsetElement($element_id);

  /**
   * Get the list of currently used element ids.
   *
   * @return array
   *   List of element ids.
   */
  public function getElementIds();

  /**
   * Get the list of currently used element types.
   *
   * @return array
   *   List of element types.
   */
  public function getElementTypes();

}

