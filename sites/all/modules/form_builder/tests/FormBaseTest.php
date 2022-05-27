<?php

namespace Drupal\form_builder;

class FormBaseTest extends \DrupalUnitTestCase {

  public static function getInfo() {
    return array(
      'name' => '\Drupal\form_builder\FormBase unit tests.',
      'description' => 'Tests form element handling.',
      'group' => 'Form builder',
    );
  }

  protected function emptyForm() {
    return new FormBase('webform', 'test', NULL, array(), array(), NULL);
  }

  public function tearDown() {
    parent::tearDown();
    FormBase::purge(0);
    Loader::instance()->fromCache(NULL, NULL, NULL, TRUE);
  }

  /**
   * @cover \Drupal\form_builder\Loader::fromCache
   * @cover \Drupal\form_builder\FormBase::load
   * @cover \Drupal\form_builder\FormBase::save
   */
  public function testSaveAndLoad() {
    $loader = Loader::instance();
    $form = $loader->getForm('webform', 'test', 'test', array());
    $form->save();
    $this->assertEqual(
      $form->getFormArray(),
      $loader->fromCache('webform', 'test', 'test')->getFormArray()
    );

  }

  /**
   * @covers \Drupal\form_builder\FormBase::setElementArray
   * @covers \Drupal\form_builder\FormBase::getElement
   * @covers \Drupal\form_builder\FormBase::getElementArray
   * @covers \Drupal\form_builder\FormBase::getFormArray
   * @covers \Drupal\form_builder\FormBase::addDefaults
   */
  public function testSetElementArray() {
    $form = $this->emptyForm();
    $a['#form_builder']['element_id'] = 'A';
    $a['#key'] = 'a';
    $a['#type'] = 'textfield';
    $this->assertEqual('A', $form->setElementArray($a));
    $rform = $form->getFormArray();
    $this->assertArrayHasKey('a', $rform);

    $a['#key'] = 'x';
    $this->assertEqual('A', $form->setElementArray($a));
    $rform = $form->getFormArray();
    $this->assertArrayNotHasKey('a', $rform);
    $this->assertArrayHasKey('x', $rform);

    $b['#key'] = 'b';
    $b['#type'] = 'textfield';
    $b['#form_builder'] = array('element_id' => 'B', 'parent_id' => 'A');
    $this->assertEqual('B', $form->setElementArray($b));
    $this->assertArrayNotHasKey('b', $form->getFormArray());
    $this->assertArrayHasKey('b', $form->getElementArray('A'));

    $b['#form_builder']['parent_id'] = 'NON EXISTING';
    $this->assertFalse($form->setElementArray($b));
    $this->assertArrayHasKey('b', $form->getElementArray('A'));

    $b['#form_builder']['parent_id'] = FORM_BUILDER_ROOT;
    $this->assertEqual('B', $form->setElementArray($b));
    $this->assertArrayHasKey('b', $form->getFormArray());
    $this->assertArrayNotHasKey('b', $form->getElementArray('A'));
  }

  /**
   * @covers \Drupal\form_builder\FormBase::getElementIds
   * @covers \Drupal\form_builder\FormBase::unsetElement
   * @covers \Drupal\form_builder\FormBase::unindexElements
   */
  public function test_unsetElementArray() {
    $form['a']['#type'] = 'textfield';
    $form['a']['#form_builder'] = array('element_id' => 'A');
    $form['a']['b'] = array('#type' => 'textfield');
    $form['a']['b']['#form_builder'] = array('element_id' => 'B');
    $form_obj =  new FormBase('webform', 'test', NULL, array(), $form);
    $this->assertEqual(array('A', 'B'), $form_obj->getElementIds());
    $form_obj->unsetElement('A');
    $this->assertEqual(array(), $form_obj->getElementIds());
  }

  /**
   * @covers \Drupal\form_builder\FormBase::__construct
   * @covers \Drupal\form_builder\FormBase::indexElements
   */
  public function testElementIdIndexing() {
    $form['a']['#type'] = 'textfield';
    $form['a']['#form_builder'] = array('element_id' => 'A');
    $form['a']['b'] = array('#type' => 'textfield');
    $form['a']['b']['#form_builder'] = array('element_id' => 'B');
    $form_obj = new FormBase('webform', 'test', NULL, array(), $form);
    $this->assertNotEmpty($form_obj->getElementArray('A'));
    $this->assertNotEmpty($form_obj->getElementArray('B'));
  }

  /**
   * Integration test _form_builder_add_element().
   *
   * @covers ::_form_builder_add_element
   * @covers ::form_builder_field_render
   * @covers \Drupal\form_builder\FormBase::load
   * @covers \Drupal\form_builder\FormBase::save
   * @covers \Drupal\form_builder\FormBase::serialize
   * @covers \Drupal\form_builder\FormBase::unserialize
   */
  public function test_form_builder_add_element() {
    module_load_include('inc', 'form_builder', 'includes/form_builder.admin');
    $loader = Loader::instance();
    $form = $loader->getForm('webform', 'test', 'test', array());
    $form->save();
    $data = _form_builder_add_element('webform', 'test', 'email', NULL, 'test', TRUE);
    $this->assertNotEmpty($data);
    $this->assertNotEmpty($data['html']);
  }

  /**
   * Integration test: Render textfield inside fieldset.
   *
   * @covers ::_form_builder_add_element
   * @covers ::form_builder_field_render
   * @covers \Drupal\form_builder\FormBase::load
   * @covers \Drupal\form_builder\FormBase::fromArray
   * @covers \Drupal\form_builder\FormBase::setElementArray
   */
  public function test_render_fieldset() {
    module_load_include('inc', 'form_builder', 'includes/form_builder.admin');
    $loader = Loader::instance();
    $form = $loader->getForm('webform', 'test', 'test', array());
    $form->save();
    drupal_static_reset('drupal_html_id');
    $data = _form_builder_add_element('webform', 'test', 'fieldset', NULL, 'test', TRUE);
    $wrapper = simplexml_load_string($data['html']);
    // Test if element is properly wrapped.
    $this->assertEqual('form-builder-wrapper', (string) $wrapper['class']);
    $this->assertEqual('form-builder-title-bar', (string) $wrapper->div[0]['class']);
    $element = $wrapper->div[1];
    $this->assertEqual('form-builder-element form-builder-element-fieldset', (string) $element['class']);
    $this->assertNotEmpty($element->fieldset);
    $fieldset_id = $data['elementId'];

    // Add a textfield to the form.
    $data = _form_builder_add_element('webform', 'test', 'textfield', NULL, 'test', TRUE);
    $this->assertNotEquals($fieldset_id, $data['elementId']);
    $textfield_id = $data['elementId'];

    $form = $loader->fromCache('webform', 'test', 'test');
    // Move the textfield inside the fieldset.
    $element = $form->getElementArray($textfield_id);
    $element['#weight'] = 1;
    $element['#form_builder']['parent_id'] = $fieldset_id;
    $this->assertEqual($textfield_id, $form->setElementArray($element));

    $form_array = $form->getFormArray();
    $this->assertEqual(array($fieldset_id), element_children($form_array));
    $this->assertEqual(array($textfield_id), element_children($form_array[$fieldset_id]));
  }

  public function testChangeElementKey() {
    $a['#type'] = 'textfield';
    $a['#form_builder'] = array('element_id' => 'A');
    $form_obj = new FormBase('webform', 'test', NULL, array(), array('a' => $a));
    $a['#key'] = 'b';
    $form_obj->setElementArray($a);
    $form = $form_obj->getFormArray();
    $this->assertArrayHasKey('b', $form);
    $this->assertArrayNotHasKey('a', $form);
  }

  protected function eArray($type, $id, $key, $weight = 0, $parent_id = FORM_BUILDER_ROOT) {
    return array(
      '#type' => $type,
      '#key' => $key,
      '#form_builder' => array('element_id' => $id, 'parent_id' => $parent_id),
      '#weight' => $weight,
    );
  }

  public function test_getElementsInPreOrder() {
    $form['a'] = $this->eArray('textfield', 'a', 'a', 1);
    $form['fieldset'] = $this->eArray('fieldset', 'fs', 'fieldset');
    $form['fieldset']['b'] = $this->eArray('textfield', 'b', 'b', 0, 'fs');
    $form['fieldset']['c'] = array('#markup' => 'Not a form_builder element');
    $form_obj = new FormBase('webform', 'test', NULL, array(), $form);
    $expected = array('fs', 'b', 'a');
    $this->assertEqual($expected, array_keys($form_obj->getElementsInPreOrder()));
  }
}
