<?php

class FormBuilderAdminTest extends \DrupalUnitTestCase {

  /**
   * Prepare test objects and load includes.
   */
  public function setUp() {
    parent::setUp();
    $this->form = new FormBuilderFormBase('webform', 'test', NULL, array(), array(), NULL);
    $this->form->save();

    module_load_include('inc', 'form_builder', 'includes/form_builder.admin');
  }

  /**
   * Purge data stored to the form cache during the tests.
   */
  public function tearDown() {
    parent::tearDown();
    FormBuilderFormBase::purge(0);
    FormBuilderLoader::instance()->fromCache(NULL, NULL, NULL, TRUE);
  }

  /**
   * Test: Elements can change the tabs displayed on their configure form.
   */
  public function testChangingGroupsInElement() {
    $loader = FormBuilderLoader::instance();
    $fields = $loader->getElementTypeInfo('webform', 0);
    $a = $fields['textfield']['default'];
    $a['#form_builder']['element_id'] = 'A';
    $a['#key'] = 'a';
    $a['#type'] = 'textfield';
    $a['#weight'] = 0;
    $element_id = $this->form->setElementArray($a);
    $this->form->save();

    $form_state = [];
    $form = form_builder_field_configure([], $form_state, 'webform', 'test', $element_id);
    $form['#property_groups']['test'] = [
      'title' => 'Test',
      'weight' => 0,
    ];
    $form['size']['#form_builder']['property_group'] = 'test';
    $form = form_builder_field_configure_pre_render($form);

    $this->assertArrayHasKey('test_property_group', $form);
    $this->assertEqual('Test', $form['test_property_group']['#title']);
    $this->assertArrayHasKey('size', $form['test_property_group']);
  }

}
