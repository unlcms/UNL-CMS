<?php

namespace Drupal\form_builder_webform;

use Drupal\form_builder\FormBase;

class Form extends FormBase {

  const LABEL_PREFIX = 'progressbar_label_first';

  /**
   * {@inheritdoc}
   */
  public static function loadFromStorage($form_type, $form_id, $sid, $params) {
    // Webform identifies its forms by Node Id.
    $node = node_load($form_id);
    $form = new static($form_type, $form_id, $sid, $params, array());
    $components = static::addFirstPagebreak($node, $node->webform['components']);
    $form->addComponents($components);
    drupal_alter('form_builder_load', $form, $form_type, $form_id);
    return $form;
  }

  protected static function addFirstPagebreak($node, $components) {
    // Only do this if using webform4 or webform_steps_w3.
    if (array_key_exists('progressbar_label_first', $node->webform)) {
      $first = reset($components);
      if ($first['type'] != 'pagebreak') {
        $form_keys = array();
        foreach ($components as $c) {
          if ($c['pid'] === 0) {
            $form_keys[$c['form_key']] = TRUE;
          }
        }

        $form_key = self::LABEL_PREFIX;
        $i = 0;
        while (!empty($form_keys[$form_key])) {
          $form_key = self::LABEL_PREFIX . ++$i;
        }

        $element = _form_builder_webform_default('pagebreak', array(), array(
          'name' => $node->webform['progressbar_label_first'],
          'form_key' => $form_key,
          'weight' => -100,
        ));
        $components = array($element['#webform_component']) + $components;
      }
    }
    return $components;
  }

  /**
   * Add components to the form.
   *
   * @param array $components
   *   A components array as you would find it in $node->webform['components'].
   */
  public function addComponents($components) {
    foreach ($components as $cid => $component) {
      $element['#webform_component'] = $component;
      $element['#weight'] = $component['weight'];
      $element['#key'] = $component['form_key'];
      $parent_id = $component['pid'] ? 'cid_' . $component['pid'] : FORM_BUILDER_ROOT;
      $element['#form_builder'] = array(
        'element_id' => 'cid_' . $cid,
        'parent_id' => $parent_id,
      );
      if ($map = _form_builder_webform_property_map($component['type'])) {
        // Keep the internal type of this element as the component type. For example
        // this may match an $element['#type'] of 'webform_date' and set the
        // $element['#form_builder']['element_type'] to simply 'date'.
        if (isset($map['form_builder_type'])) {
          $element['#form_builder']['element_type'] = $map['form_builder_type'];
        }
      }
      if ($e = form_builder_webform_component_invoke($component['type'], 'form_builder_load', $element)) {
        $element = $e;
      }
      $this->setElementArray($element, $parent_id);
    }
  }

  /**
   * Create a webform component array based the form_builder cache.
   *
   * @param string $element_id
   *   Unique ID of the element.
   * @return array
   *   A webform component array.
   */
  public function getComponent($element_id) {
    module_load_include('inc', 'form_builder_webform', 'form_builder_webform.components');

    $element = $this->getElementArray($element_id);
    $component = $element['#webform_component'];
    $type = $component['type'];

    $component['email'] = 0;
    $component['nid'] = $this->formId;
    $component['weight'] = $element['#weight'];
    // The component can't decide this on it's own.
    $component['pid'] = 0;
    $component['form_builder_element_id'] = $element_id;

    // Allow each component to set any specific settings that can't be mapped.
    if ($saved_component = form_builder_webform_component_invoke($type, 'form_builder_save', $component, $element)) {
      $component = $saved_component;
    }

    return $component;
  }

  /**
   * Get a list of component-arrays just like in $node->webform['components'].
   */
  public function getComponents($node) {
    // Max CID is used in the creation of new components, preventing conflicts.
    $cids = array();
    // Filter out all cids from removed components.
    foreach (array_keys($node->webform['components']) as $cid) {
      if ($this->getElement("cid_$cid")) {
        $cids[] = $cid;
      }
    }
    $max_cid = !empty($cids) ? max($cids) : 0;

    $components = array();
    // Keep track of the element_id => cid mapping for assigning the pid.
    $element_cid_map = array();
    $page = 1;
    foreach ($this->getElementsInPreOrder() as $element_id => $element) {
      if ($component = $this->getComponent($element_id)) {
        if (empty($component['cid'])) {
          $component['cid'] = ++$max_cid;
        }
        $element_cid_map[$element_id] = $component['cid'];

        // Set the possibly changed pid.
        $parent_id = $element->parentId();
        $component['pid'] = ($parent_id === FORM_BUILDER_ROOT) ? 0 : $element_cid_map[$parent_id];
        $component['page_num'] = $page;

        $components[$component['cid']] = $component;
        if ($component['type'] == 'pagebreak') {
          $page++;
        }
      }
    }
    return $components;
  }

  public function updateNode($node) {
    $components = $this->getComponents($node);
    $first = reset($components);
    if ($first['type'] == 'pagebreak') {
      $node->webform['progressbar_label_first'] = $first['name'];

      // Remove pagebreak if it has the right key.
      if (substr($first['form_key'], 0, strlen(self::LABEL_PREFIX)) === self::LABEL_PREFIX) {
        unset($components[$first['cid']]);
        foreach ($components as &$c) {
          $c['page_num']--;
        }
      }
    }
    $node->webform['components'] = $components;
  }

}

