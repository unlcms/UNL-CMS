<?php

namespace Drupal\form_builder;

/**
 * This class is a wrapper around all the hooks used for getting pluigns.
 *
 * Currently supported plugin-types are:
 * - form types: hook_form_builder_form_types().
 * - element types: hook_form_builder_types().
 * - properties: hook_form_builder_properties().
 */
class Loader {

  protected static $instance = NULL;
  protected $formTypeInfo;
  protected $paletteGroupInfo = array();
  protected $elementTypeInfo = array();
  protected $propertyInfo = array();
  protected $formCache = array();

  /**
   * Get a singleton-like class instance.
   */
  public static function instance() {
    if (!static::$instance) {
      static::$instance = new static();
    }
    return static::$instance;
  }

  public function __construct() {
    module_load_include('api.inc', 'form_builder', 'includes/form_builder');
    $this->loadFormTypeInfo();
  }

  protected function loadFormTypeInfo() {
    $defaults = array(
      'class' => FormBase::class,
      'property class' => PropertyBase::class,
      'element class' => ElementBase::class,
    );

    $form_types = module_invoke_all('form_builder_form_types');
    foreach ($form_types as $form_type => &$info) {
      $info += $defaults;
    }
    drupal_alter('form_builder_form_types', $form_types);

    $this->formTypeInfo = $form_types;
  }

  public function getElementTypeInfo($form_type, $form_id) {
    if (!isset($this->elementTypeInfo[$form_type][$form_id])) {
      $element_types = module_invoke_all('form_builder_element_types', $form_type, $form_id);
      $groups = $this->getPaletteGroupInfo($form_type, $form_id);
      // Add default values for undefined properties.
      foreach ($element_types as $key => &$type) {
        $type += array(
          'class' => $this->formTypeInfo[$form_type]['element class'],
          'configurable' => TRUE,
          'removable' => TRUE,
          'palette_group' => 'default',
          'properties' => array(),
        );
        $type += array(
          'addable' => $type['removable'] && isset($type['default']),
        );
        $type['unique'] = !empty($type['unique']);
        $type['palette_group'] = isset($groups[$type['palette_group']]) ? $type['palette_group'] : 'default';

        // All fields must support weight.
        if (!in_array('weight', $type['properties'])) {
          $type['properties'][] = 'weight';
        }

        // Update the default elements with some defaults.
        // Note that if a field is not removable, it doesn't have a default.
        $type['default'] += array('#form_builder' => array());
        if ($type['addable']) {
          $type['default']['#form_builder'] += array('element_type' => $key);
          if ($type['unique']) {
            $type['default']['#form_builder']['element_id'] = $key;
          }
        }
      }
      // Sort fields by weight and title.
      uasort($element_types, '_form_builder_sort');
      drupal_alter('form_builder_element_types', $element_types, $form_type, $form_id);
      $this->elementTypeInfo[$form_type][$form_id] = $element_types;
    }
    return $this->elementTypeInfo[$form_type][$form_id];
  }

  public function getPaletteGroupInfo($form_type, $form_id, $reset = FALSE) {
    if (!isset($this->paletteGroupInfo[$form_type]) || $reset) {
      $this->paletteGroupInfo[$form_type] = module_invoke_all('form_builder_palette_groups', $form_type, $form_id);
    }
    return $this->paletteGroupInfo[$form_type];
  }

  public function getPropertyInfo($form_type, $reset = FALSE) {
    if (!isset($this->propertyInfo[$form_type]) || $reset) {
      // Don't use module_invoke_all here as it uses array_merge_recursive()
      // which creates sub-arrays for duplicate array keys.
      $properties = array();
      foreach (module_implements('form_builder_properties') as $module) {
        $new_properties = module_invoke($module, 'form_builder_properties', $form_type);
        $properties += $new_properties;
        foreach ($new_properties as $k => $v) {
          $properties[$k] = array_merge($properties[$k], $new_properties[$k]);
        }
      }
      drupal_alter('form_builder_properties', $properties, $form_type);
      $defaults['class'] = $this->formTypeInfo[$form_type]['property class'];
      foreach ($properties as $property => &$params) {
        $params += $defaults;
      }
      $this->propertyInfo[$form_type] = $properties;
    }

    return $this->propertyInfo[$form_type];
  }

  /**
   * Get a form object.
   */
  public function getForm($form_type, $form_id, $sid, $form = array()) {
    if (!isset($this->formTypeInfo[$form_type])) {
      return FALSE;
    }
    $info = $this->formTypeInfo[$form_type];
    $class = $info['class'];
    return new $class($form_type, $form_id, $sid, $info, $form);
  }

  /**
   * Load a form from storage.
   */
  public function fromStorage($form_type, $form_id, $sid = NULL) {
    if (!isset($this->formTypeInfo[$form_type])) {
      return FALSE;
    }
    $info = $this->formTypeInfo[$form_type];
    $class = $info['class'];
    return $class::loadFromStorage($form_type, $form_id, $sid, $info);
  }

  /**
   * Load a form from the form_builder_cache.
   */
  public function fromCache($form_type, $form_id, $sid = NULL, $reset = FALSE) {
    if ($reset) {
      $this->formCache = array();
    }

    if ($form_type && $form_id) {
      if (empty($this->formCache[$form_type][$form_id])) {
        $this->formCache[$form_type][$form_id] = FALSE;

        if (isset($this->formTypeInfo[$form_type])) {
          $info = $this->formTypeInfo[$form_type];
          $class = $info['class'];
          $sid = $sid ? $sid : session_id();
          if ($form = $class::load($form_type, $form_id, $sid, $info)) {
            $this->formCache[$form_type][$form_id] = $form;
          }
        }
      }
      return $this->formCache[$form_type][$form_id];
    }
    return NULL;
  }

  /**
   * Get element instance.
   */
  public function getElement($form_type, $form_id, $element_type, $form, &$element) {
    $infos = $this->getElementTypeInfo($form_type, $form_id);
    $info = $infos[$element_type];
    $class = $info['class'];
    return new $class($form, $info, $element, $this);
  }
}
