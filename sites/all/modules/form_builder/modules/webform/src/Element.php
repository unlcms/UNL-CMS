<?php

namespace Drupal\form_builder_webform;

use Drupal\form_builder\ElementBase;

class Element extends ElementBase {

  /**
   * {@inheritdoc}
   */
  protected function setProperty($property, $value) {
    $component = &$this->element['#webform_component'];
    $properties = $this->getProperties();
    $properties[$property]->setValue($component, $value);
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $element = $this->element;
    if (isset($element['#webform_component'])) {
      $component = $element['#webform_component'];
      $new_element = webform_component_invoke($component['type'], 'render', $component, NULL, FALSE);
      // Preserve the #weight. It may have been changed by the positions form.
      $new_element['#weight'] = $element['#weight'];
      $new_element['#key'] = $component['form_key'];
      $new_element['#webform_component'] = $component;
      $new_element['#form_builder'] = $element['#form_builder'];
      return $this->addPreRender($new_element);
    }
    return $this->addPreRender($element);
  }

  public function title() {
    return $this->element['#webform_component']['name'];
  }

  /**
   * Get the element’s form key.
   *
   * @return string
   *   The element’s form key.
   */
  public function key() {
    return $this->element['#webform_component']['form_key'];
  }

  /**
   * Generate the component edit form for this component.
   *
   * @return array
   *   Form-API array of the component edit form.
   */
  protected function componentEditForm($component) {
    $component = $this->element['#webform_component'];
    $form_id = 'webform_component_edit_form';
    $form_state = form_state_defaults();
    $nid = isset($component['nid']) ? $component['nid'] : NULL;
    $node = !isset($nid) ? (object) array('nid' => NULL, 'webform' => webform_node_defaults()) : node_load($nid);

    // The full node is needed here so that the "private" option can be access
    // checked.
    $form = $form_id([], $form_state, $node, $component);
    // We want to avoid a full drupal_get_form() for now but some alter hooks
    // need defaults normally set in drupal_prepare_form().
    $form += ['#submit' => []];
    $form_state['build_info']['args'][1] = $component;
    drupal_alter(['form', 'form_webform_component_edit_form'], $form, $form_state, $form_id);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function configurationForm($form, &$form_state) {
    form_load_include($form_state, 'properties.inc', 'form_builder_webform');
    $component = $this->element['#webform_component'];
    $edit = $this->componentEditForm($component);
    foreach ($this->getProperties() as $property) {
      $form = array_merge($form, $property->form($component, $edit, $form_state));
    }
    return $form;
  }

}

