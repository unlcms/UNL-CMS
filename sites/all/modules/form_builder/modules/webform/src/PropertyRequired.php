<?php

namespace Drupal\form_builder_webform;

/**
 * Special handling for the mandatory -> required rename in webform4.
 */
class PropertyRequired extends Property {

  public function setValue(&$component, $value) {
    $component['required'] = $value; // webform 4
    $component['mandatory'] = $value; // webform 3
  }

}
