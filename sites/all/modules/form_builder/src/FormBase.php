<?php

namespace Drupal\form_builder;

class FormBase implements \Serializable {

  const CACHE_NAME = 'form_builder_cache';
  protected $formType;
  protected $params;
  protected $properties;
  protected $form;
  protected $formId;
  protected $sid;
  protected $loader;
  protected $elementArrays = array();

  /**
   * Shortcut for creating a form object from a form array.
   */
  public static function fromArray($form) {
    $fb = $form['#form_builder'] + array('sid' => NULL);
    return Loader::instance()
      ->getForm($fb['form_type'], $fb['form_id'], $fb['sid'], $form);
  }

  /**
   * {@inheritdoc}
   */
   public static function loadFromStorage($form_type, $form_id, $sid, $params) {
     $form = module_invoke_all('form_builder_load', $form_type, $form_id);
     drupal_alter('form_builder_load', $form, $form_type, $form_id);
     return new static($form_type, $form_id, $sid, $params, $form);
   }

  /**
   * {@inheritdoc}
   */
  public function saveToStorage() {
    module_invoke_all('form_builder_save', $this->form, $this->formType, $this->formId);
    $this->delete();
  }

  /**
   * {@inheritdoc}
   */
  public static function load($form_type, $form_id, $sid, $params) {
    ctools_include('object-cache');
    $obj = "$form_type:$form_id";
    $form = ctools_object_cache_get($obj, self::CACHE_NAME, FALSE, $sid);
    if ($form && is_array($form)) {
      $form = new static($form_type, $form_id, $sid, $params, $form);
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function __construct($form_type, $form_id, $sid, $params, $form) {
    $this->formType = $form_type;
    $this->formId = $form_id;
    $this->sid = $sid ? $sid : session_id();
    $this->params = $params;
    $this->properties = NULL;
    $this->form = &$form;

    $this->elementArrays[FORM_BUILDER_ROOT] = &$this->form;
    $this->addDefaults($this->form);
    $this->indexElements($this->form);
  }

  /**
   * Serialize the form.
   *
   * NOTE: This should only be used for short-term storage.
   */
  public function serialize() {
    return serialize(array(
      'formType' => $this->formType,
      'formId' => $this->formId,
      'sid' => $this->sid,
      'params' => $this->params,
      'form' => $this->form,
      // Don't save element-arrays and the loader.
    ));
  }

  /**
   * Unserialize a stored version of this form.
   */
  public function unserialize($data) {
    $data = unserialize($data);
    $this->formType = $data['formType'];
    $this->formId = $data['formId'];
    $this->sid = $data['sid'];
    $this->params = $data['params'];
    $this->form = $data['form'];
    $this->properties = array();
    $this->elementArrays[FORM_BUILDER_ROOT] = &$this->form;
    $this->addDefaults($this->form);
    $this->indexElements($this->form);
  }

  /**
   * {@inheritdoc}
   */
  public function save() {
    ctools_include('object-cache');
    $obj = "{$this->formType}:{$this->formId}";
    ctools_object_cache_set($obj, self::CACHE_NAME, $this, $this->sid);
  }

  /**
   * {@inheritdoc}
   */
  public function delete() {
    ctools_include('object-cache');
    $obj = "{$this->formType}:{$this->formId}";
    ctools_object_cache_clear($obj, self::CACHE_NAME, FALSE, $this->sid);
  }

  /**
   * Purge old cache entries.
   *
   * @param int $max_age
   *   All form_builder_cache entries older than $max_age seconds are purged.
   */
  public static function purge($max_age = NULL) {
    $expire = isset($max_age) ? $max_age : ini_get('session.cache_expire');
    return db_delete('ctools_object_cache')
      ->condition('name', 'form_builder_cache')
      ->condition('updated', REQUEST_TIME - $max_age, '<')
      ->execute();
    drupal_static_reset('ctools_object_cache_get');
  }

  /**
   * Recurse through the form array and add defaults to their element arrays.
   *
   * This function ensures the following properties:
   * $element['#pre_render'] includes 'form_builder_pre_render'
   * In $element['#form_builder']:
   *   - 'form_type'
   *   - 'form_id'
   *   - 'parent_id'
   */
  protected function addDefaults(&$element, $parent_id = FORM_BUILDER_ROOT, $key = NULL, &$element_info = NULL) {
    if (!$element_info) {
      $element_info = Loader::instance()->getElementTypeInfo($this->formType, $this->formId);
    }
    if (isset($element['#form_builder']['element_id'])) {
      $element_id = $element['#form_builder']['element_id'];
      $element += array('#key' => $key);
      $element['#form_builder']['form_type'] = $this->formType;
      $element['#form_builder']['form_id'] = $this->formId;
      $element['#form_builder']['parent_id'] = $parent_id;

      // Set defaults based on the form type.
      $settings = array();
      if (isset($element_info[$element_id]) && $element_info[$element_id]['unique']) {
        $element['#form_builder']['unique'] = TRUE;
        $element['#form_builder'] += array('element_type' => $element_id);
        $settings = $element_info[$element_id];
      }
      else {
        if (isset($element['#type'])) {
          $element['#form_builder'] += array('element_type' => $element['#type']);
        }
        if (isset($element_info[$element['#form_builder']['element_type']])) {
          $settings = $element_info[$element['#form_builder']['element_type']];
        }
        else {
          // If the type cannot be found, prevent editing of this field.
          unset($element['#form_builder']);
          return;
        }
      }

      // Set defaults for configurable and removable.
      $settings += array('configurable' => TRUE, 'removable' => TRUE);
      $element['#form_builder'] += array(
        'configurable' => $settings['configurable'],
        'removable' => $settings['removable'],
      );
      $parent_id = $element_id;
    }
    foreach (element_children($element) as $key) {
      $this->addDefaults($element[$key], $parent_id, $key, $element_info);
    }
  }

  /**
   * Add the element and it's subelements to the elementd index.
   *
   * The index is stored in $this->elementArrays and used by all element_id
   * based methods.
   */
  protected function indexElements(&$element) {
    if (isset($element['#form_builder']['element_id'])) {
      $element_id = $element['#form_builder']['element_id'];
      $this->elementArrays[$element_id] = &$element;
    }
    foreach (element_children($element) as $key) {
      $this->indexElements($element[$key]);
    }
  }

  /**
   * Remove an element and it's children from the index.
   */
  protected function unindexElements($element) {
    if ($element instanceof ElementInterface) {
      unset($this->elements[$element->getId()]);
    }
    foreach ($element->getChildren() as $child) {
      $this->unindexElements($child);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getElement($element_id) {
    if (!isset($this->elementArrays[$element_id])) {
      return NULL;
    }
    $element = &$this->elementArrays[$element_id];
    return Loader::instance()
      ->getElement($this->formType, $this->formId, $element['#form_builder']['element_type'], $this, $element);
  }

  /**
   * {@inheritdoc}
   */
  public function getElementArray($element_id) {
    if (isset($this->elementArrays[$element_id])) {
      return $this->elementArrays[$element_id];
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getElementArrays($element_ids) {
    $elements = array();
    foreach ($element_ids as $element_id) {
      if ($element = $this->getElementArray($element_id)) {
        $elements[$element_id] = $element;
      }
    }
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormArray() {
    return $this->form;
  }

  /**
   * (@inheritdoc}
   */
  public function setElementArray($element, $parent_id = FORM_BUILDER_ROOT, $alter = FALSE) {
    $return = FALSE;
    $element_id = $element['#form_builder']['element_id'];
    $element['#form_builder'] += array('parent_id' => $parent_id);
    $parent_id = $element['#form_builder']['parent_id'];

    if ($alter) {
      drupal_alter('form_builder_add_element', $element, $this->formType, $this->formId);
      // Save any element ID set by the hook_form_builder_add_element_alter().
      $element_id = $element['#form_builder']['element_id'];
      $parent_id = $element['#form_builder']['parent_id'];
      // Re-run addDefaults in case something has changed
    }
    $this->addDefaults($element, $parent_id);

    if (!isset($element['#form_builder'])) {
      return FALSE;
    }

    if (isset($this->elementArrays[$parent_id])) {
      $parent = &$this->elementArrays[$parent_id];
    }
    else {
      return FALSE;
    }

    $old_element = FALSE;
    if (isset($this->elementArrays[$element_id])) {
      $old_element = &$this->elementArrays[$element_id];
      // Remove element from old parent if needed.
      if ($parent_id !== $old_element['#form_builder']['parent_id']) {
        $old_parent =& $this->elementArrays[$old_element['#form_builder']['parent_id']];
        unset($old_parent[$old_element['#key']]);
        unset($old_element);
        unset($old_parent);
        $old_element = FALSE;
      }
    }

    if ($old_element && $old_element['#key'] != $element['#key']) {
      // Insert the (changed) element at the same position in the parent.
      $new_parent = array();
      foreach($parent as $key => &$child) {
        if ($key == $old_element['#key']) {
          $new_parent[$element['#key']] = &$element;
        }
        else {
          $new_parent[$key] = &$child;
        }
      }
      $parent = $new_parent;
    }
    else {
      $parent[$element['#key']] = &$element;
    }
    $this->indexElements($element);

    return $element_id;
  }

  /**
   * {@inheritdoc}
   */
  public function unsetElement($element_id) {
    $element = $this->elementArrays[$element_id];
    foreach (element_children($element) as $key) {
      if (!empty($element[$key]['#form_builder']['element_id'])) {
        $this->unsetElement($element[$key]['#form_builder']['element_id']);
      }
    }
    unset($this->elementArrays[$element_id]);
    $parent = &$this->elementArrays[$element['#form_builder']['parent_id']];
    unset($parent[$element['#key']]);
  }

  /**
   * Get list of element ids in depth-first pre-order.
   */
  public function getElementIdsInPreOrder() {
    $ids = array();
    $this->_recursiveElementIds($ids, $this->form);
    return $ids;
  }

  private function _recursiveElementIds(&$ids, $e) {
    foreach (element_children($e, TRUE) as $key) {
      if (isset($e[$key]['#form_builder'])) {
        $ids[] = $e[$key]['#form_builder']['element_id'];
        $this->_recursiveElementIds($ids, $e[$key]);
      }
    }
  }

  /**
   * Get element objects in depth-first pre-order.
   */
  public function getElementsInPreOrder() {
    $elements = array();
    foreach ($this->getElementIdsInPreOrder() as $id) {
      $elements[$id] = $this->getElement($id);
    }
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function getElementIds() {
    $ids = array();
    foreach (array_keys($this->elementArrays) as $id) {
      if ($id !== FORM_BUILDER_ROOT) {
        $ids[] = $id;
      }
    }
    return $ids;
  }

  /**
   * {@inheritdoc}
   */
  public function getElementTypes() {
    $types = array();
    foreach ($this->elementArrays as $element) {
      if (isset($element['#form_builder']['element_type'])) {
        $types[$element['#form_builder']['element_type']] = TRUE;
      }
    }
    return array_keys($types);
  }

  /**
   * {@inheritdoc}
   */
  public function getProperties($reset = FALSE) {
    if (!$this->properties || $reset) {
      $properties = Loader::instance()->getPropertyInfo($this->formType, $reset);
      foreach ($properties as $property => $params) {
        $class = $params['class'];
        $this->properties[$property] = new $class($property, $params, $this->formType);
      }
    }

    return $this->properties;
  }

  /**
   * Build form-tree from element objects.
   */
  public function preview() {
    $form = array();
    $elements = array(FORM_BUILDER_ROOT => &$form);
    foreach ($this->getElementsInPreOrder() as $id => $e) {
      $elements[$id] = $e->render();
      $elements[$e->parentId()][$e->key()] = &$elements[$id];
    }
    $form['#tree'] = TRUE;
    $form['#form_builder'] = array(
      'form_type' => $this->formType,
      'form_id' => $this->formId,
      'sid' => $this->sid,
    );
    return $form;
  }

}
