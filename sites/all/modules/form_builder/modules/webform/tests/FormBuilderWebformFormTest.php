<?php

class FormBuilderWebformFormTest extends DrupalUnitTestCase {
  protected $components = array(
    1 => array(
      'nid' => 1,
      'cid' => '1',
      'pid' => '0',
      'form_key' => 'fieldset1',
      'name' => 'fieldset1',
      'type' => 'fieldset',
      'value' => '',
      'extra' => array(
        'title_display' => 0,
        'private' => 0,
        'collapsible' => 0,
        'collapsed' => 0,
        'conditional_operator' => '=',
        'description' => '',
        'conditional_component' => '',
        'conditional_values' => '',
      ),
      'mandatory' => '0',
      'weight' => '0',
      'page_num' => 1,
    ),
    3 => array(
      'nid' => 1,
      'cid' => '3',
      'pid' => '1',
      'form_key' => 'hour',
      'name' => 'hour',
      'type' => 'time',
      'value' => '',
      'extra' => array(
        'timezone' => 'user',
        'title_display' => 'before',
        'private' => 0,
        'hourformat' => '12-hour',
        'minuteincrements' => '1',
        'conditional_operator' => '=',
        'description' => '',
        'conditional_component' => '',
        'conditional_values' => '',
      ),
      'mandatory' => '0',
      'weight' => '2',
      'page_num' => 1,
    ),
    2 => array(
      'nid' => 1,
      'cid' => '2',
      'pid' => '0',
      'form_key' => 'textfield1',
      'name' => 'textfield1',
      'type' => 'textfield',
      'value' => 'textfield1',
      'extra' => array(
        'title_display' => 'before',
        'private' => 0,
        'disabled' => 1,
        'unique' => 0,
        'conditional_operator' => '=',
        'width' => '4',
        'maxlength' => '',
        'field_prefix' => 'testprefix',
        'field_suffix' => 'testpostfix',
        'description' => '',
        'attributes' => array(),
        'conditional_component' => '',
        'conditional_values' => '',
      ),
      'mandatory' => '0',
      'weight' => '1',
      'page_num' => 1,
    ),
  );

  function testPreview() {
    $form = new FormBuilderWebformForm('webform', 0, 'the-sid', array(), array());
    $form->addComponents($this->components);
    $this->assertEqual($form->preview(), array(
      '#tree' => TRUE,
      'fieldset1' => array(
        '#type' => 'fieldset',
        '#title' => 'fieldset1',
        '#title_display' => NULL,
        '#weight' => '0',
        '#description' => '',
        '#collapsible' => 0,
        '#collapsed' => 0,
        '#attributes' => array(
          'class' => array(
            0 => 'webform-component-fieldset',
          ),
        ),
        '#pre_render' => array(
          0 => 'form_pre_render_fieldset',
          1 => 'ctools_dependent_pre_render',
          2 => 'form_builder_pre_render',
        ),
        '#translatable' => array(
          0 => 'title',
          1 => 'description',
        ),
        '#webform_component' => array(
          'nid' => 1,
          'cid' => '1',
          'pid' => '0',
          'form_key' => 'fieldset1',
          'name' => 'fieldset1',
          'type' => 'fieldset',
          'value' => '',
          'extra' => array(
            'title_display' => 0,
            'private' => 0,
            'collapsible' => 0,
            'collapsed' => 0,
            'conditional_operator' => '=',
            'description' => '',
            'conditional_component' => '',
            'conditional_values' => '',
          ),
          'mandatory' => '0',
          'weight' => '0',
          'page_num' => 1,
        ),
        'hour' => array(
          '#type' => 'webform_time',
          '#title' => 'hour',
          '#title_display' => 'before',
          '#required' => '0',
          '#weight' => '2',
          '#description' => '',
          '#element_validate' => array(
            0 => 'webform_validate_time',
          ),
          '#hourformat' => '12-hour',
          '#minuteincrements' => '1',
          '#default_value' => '',
          '#timezone' => 'user',
          '#process' => array(
            0 => 'webform_expand_time',
          ),
          '#theme' => 'webform_time',
          '#theme_wrappers' => array(
            0 => 'webform_element',
          ),
          '#translatable' => array(
            0 => 'title',
            1 => 'description',
          ),
          '#webform_component' => array(
            'nid' => 1,
            'cid' => '3',
            'pid' => '1',
            'form_key' => 'hour',
            'name' => 'hour',
            'type' => 'time',
            'value' => '',
            'extra' => array(
              'timezone' => 'user',
              'title_display' => 'before',
              'private' => 0,
              'hourformat' => '12-hour',
              'minuteincrements' => '1',
              'conditional_operator' => '=',
              'description' => '',
              'conditional_component' => '',
              'conditional_values' => '',
            ),
            'mandatory' => '0',
            'weight' => '2',
            'page_num' => 1,
          ),
          '#form_builder' => array(
            'element_id' => 'cid_3',
            'element_type' => 'time',
            'form_type' => 'webform',
            'form_id' => 0,
            'parent_id' => 'cid_1',
            'configurable' => TRUE,
            'removable' => TRUE,
          ),
          '#pre_render' => array(
            0 => 'form_builder_pre_render',
          ),
          '#key' => 'hour',
        ),
        '#form_builder' => array(
          'element_id' => 'cid_1',
          'element_type' => 'fieldset',
          'form_type' => 'webform',
          'form_id' => 0,
          'parent_id' => 0,
          'configurable' => TRUE,
          'removable' => TRUE,
        ),
        '#key' => 'fieldset1',
      ),
      'textfield1' => array(
        '#type' => 'textfield',
        '#title' => 'textfield1',
        '#title_display' => 'before',
        '#default_value' => 'textfield1',
        '#required' => '0',
        '#weight' => '1',
        '#field_prefix' => 'testprefix',
        '#field_suffix' => 'testpostfix',
        '#description' => '',
        '#attributes' => array(),
        '#theme_wrappers' => array(
          0 => 'webform_element',
        ),
        '#translatable' => array(
          0 => 'title',
          1 => 'description',
          2 => 'field_prefix',
          3 => 'field_suffix',
        ),
        '#disabled' => TRUE,
        '#size' => '4',
        '#webform_component' => array(
          'nid' => 1,
          'cid' => '2',
          'pid' => '0',
          'form_key' => 'textfield1',
          'name' => 'textfield1',
          'type' => 'textfield',
          'value' => 'textfield1',
          'extra' => array(
            'title_display' => 'before',
            'private' => 0,
            'disabled' => 1,
            'unique' => 0,
            'conditional_operator' => '=',
            'width' => '4',
            'maxlength' => '',
            'field_prefix' => 'testprefix',
            'field_suffix' => 'testpostfix',
            'description' => '',
            'attributes' => array(),
            'conditional_component' => '',
            'conditional_values' => '',
          ),
          'mandatory' => '0',
          'weight' => '1',
          'page_num' => 1,
        ),
        '#form_builder' => array(
          'element_id' => 'cid_2',
          'element_type' => 'textfield',
          'form_type' => 'webform',
          'form_id' => 0,
          'parent_id' => 0,
          'configurable' => TRUE,
          'removable' => TRUE,
        ),
        '#pre_render' => array(
          0 => 'ctools_dependent_pre_render',
          1 => 'form_builder_pre_render',
        ),
        '#key' => 'textfield1',
      ),
      '#form_builder' => array(
        'form_type' => 'webform',
        'form_id' => 0,
        'sid' => 'the-sid',
      ),
    ));
  }

  function testConfigurationForm() {
    // We need a real node because webform_component_edit_form() uses it.
    $node = (object) array('type' => 'webform');
    node_object_prepare($node);
    $node->webform['components'] = $this->components;
    node_save($node);

    $form = FormBuilderWebformForm::loadFromStorage('webform', $node->nid, 'the-sid', array());
    $form_state = array();
    $element = $form->getElement('cid_2');
    $a = $element->configurationForm(array(), $form_state);
    $this->assertEqual(array(
      '#_edit_element' => array(
        '#webform_component' => array(
          'nid' => $node->nid,
          'cid' => '2',
          'pid' => '0',
          'form_key' => 'textfield1',
          'name' => 'textfield1',
          'type' => 'textfield',
          'value' => 'textfield1',
          'extra' => array(
            'title_display' => 'before',
            'private' => 0,
            'disabled' => 1,
            'unique' => 0,
            'conditional_operator' => '=',
            'width' => '4',
            'maxlength' => '',
            'field_prefix' => 'testprefix',
            'field_suffix' => 'testpostfix',
            'description' => '',
            'attributes' => array(),
            'conditional_component' => '',
            'conditional_values' => '',
          ),
          'mandatory' => '0',
          'weight' => '1',
          'page_num' => 1,
        ),
        '#weight' => '1',
        '#key' => 'textfield1',
        '#form_builder' => array(
          'element_id' => 'cid_2',
          'parent_id' => 0,
          'element_type' => 'textfield',
          'form_type' => 'webform',
          'form_id' => $node->nid,
          'configurable' => TRUE,
          'removable' => TRUE,
        ),
      ),
      'size' => array(
        '#form_builder' => array(
          'property_group' => 'display',
        ),
        '#type' => 'textfield',
        '#size' => 6,
        '#title' => 'Size',
        '#default_value' => '4',
        '#weight' => 2,
        '#maxlength' => 5,
        '#element_validate' => array(
          0 => 'form_validate_integer',
        ),
      ),
      'maxlength' => array(
        '#form_builder' => array(
          'property_group' => 'validation',
        ),
        '#type' => 'textfield',
        '#size' => 6,
        '#title' => 'Max length',
        '#default_value' => '',
        '#field_suffix' => ' characters',
        '#weight' => 3,
        '#maxlength' => 7,
        '#element_validate' => array(
          0 => 'form_validate_integer',
        ),
      ),
      'field_prefix' => array(
        '#form_builder' => array(
          'property_group' => 'display',
        ),
        '#type' => 'textfield',
        '#title' => 'Prefix',
        '#default_value' => 'testprefix',
        '#weight' => -2,
      ),
      'field_suffix' => array(
        '#form_builder' => array(
          'property_group' => 'display',
        ),
        '#type' => 'textfield',
        '#title' => 'Suffix',
        '#default_value' => 'testpostfix',
        '#weight' => -1,
      ),
      'disabled' => array(
        '#form_builder' => array(
          'property_group' => 'display',
        ),
        '#title' => 'Disabled (read-only)',
        '#type' => 'checkbox',
        '#default_value' => TRUE,
        '#weight' => 12,
      ),
      'unique' => array(
        '#form_builder' => array(
          'property_group' => 'validation',
        ),
        '#title' => 'Unique',
        '#description' => 'Check that all entered values for this field are unique. The same value is not allowed to be used twice.',
        '#type' => 'checkbox',
        '#default_value' => 0,
      ),
      'title' => array(
        '#title' => 'Title',
        '#type' => 'textfield',
        '#default_value' => 'textfield1',
        '#maxlength' => 255,
        '#required' => TRUE,
        '#weight' => -10,
      ),
      'title_display' => array(
        '#type' => 'select',
        '#title' => 'Label display',
        '#default_value' => 'before',
        '#options' => array(
          'before' => 'Above',
          'inline' => 'Inline',
          'none' => 'None',
        ),
        '#description' => 'Determines the placement of the component\'s label.',
        '#weight' => 8,
        '#tree' => TRUE,
        '#form_builder' => array(
          'property_group' => 'display',
        ),
      ),
      'default_value' => array(
        '#type' => 'textfield',
        '#title' => 'Default value',
        '#default_value' => 'textfield1',
        '#weight' => 1,
      ),
      'description' => array(
        '#title' => 'Description',
        '#type' => 'textarea',
        '#default_value' => '',
        '#weight' => 5,
      ),
      'webform_private' => array(
        '#type' => 'checkbox',
        '#title' => 'Private',
        '#default_value' => FALSE,
        '#description' => 'Private fields are shown only to users with results access.',
        '#weight' => 45,
        '#disabled' => TRUE,
        '#tree' => TRUE,
        '#form_builder' => array(
          'property_group' => 'display',
        ),
      ),
      'required' => array(
        '#form_builder' => array(
          'property_group' => 'validation',
        ),
        '#title' => 'Required',
        '#type' => 'checkbox',
        '#default_value' => '0',
        '#weight' => -1,
      ),
      'key' => array(
        '#title' => 'Form key',
        '#type' => 'machine_name',
        '#default_value' => 'textfield1',
        '#maxlength' => 128,
        '#description' => 'The form key is used in the field "name" attribute. Must be alphanumeric and underscore characters.',
        '#machine_name' => array(
          'source' => array(
            0 => 'title',
          ),
          'label' => 'Form key',
        ),
        '#weight' => -9,
        '#element_validate' => array(
          0 => 'form_builder_property_key_form_validate',
        ),
      ),
      'weight' => array(
        '#form_builder' => array(
          'property_group' => 'hidden',
        ),
        '#type' => 'textfield',
        '#size' => 6,
        '#title' => 'Weight',
        '#default_value' => '1',
      ),
    ), $a);
  }

  /**
   * Test whether we have a mapping for every (core) webform component.
   */
  function testElementMappings() {
    $components = webform_webform_component_info();
    $element_info = FormBuilderLoader::instance()->getElementTypeInfo('webform', NULL);
    foreach (array_keys($components) as $type) {
      $map = _form_builder_webform_property_map($type);
      $this->assertTrue(!empty($map['form_builder_type']), "Unmapped component type '$type'.");
      $t = $map['form_builder_type'];
      $this->assertTrue(!empty($element_info[$t]), "Component type '$type' maps to undefined element_type $t");
    }
  }
}
