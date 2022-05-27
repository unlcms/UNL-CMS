<?php

namespace Drupal\form_builder_examples;

use Drupal\form_builder\Loader;

class ExamplesTest extends \DrupalUnitTestCase {

  public static function getInfo() {
    return array(
      'name' => 'form_builder_examples unit tests.',
      'description' => 'Tests form element handling in example forms.',
      'group' => 'Form builder',
    );
  }

  public function testOptionsConfiguration() {
    module_load_include('inc', 'form_builder', 'includes/form_builder.admin');
    $loader = Loader::instance();
    $form = $loader->getForm('example', 'test', 'testsid');
    $form->save();
    $data = _form_builder_add_element('example', 'test', 'select', NULL, 'testsid', TRUE);
    $options_id = $data['elementId'];

    $form = $loader->fromCache('example', 'test', 'testsid');
    $e = $form->getElement($options_id);
    $form_state = array();
    $form = $e->configurationForm(array(), $form_state);
    $this->assertEqual(array(1 => 'one', 2 => 'two', 3 => 'three'), $form['options']['#options']);
  }

}
