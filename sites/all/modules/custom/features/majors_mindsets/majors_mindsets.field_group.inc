<?php

/**
 * @file
 * majors_mindsets.field_group.inc
 */

/**
 * Implements hook_field_group_info().
 */
function majors_mindsets_field_group_info() {
  $field_groups = array();

  $field_group = new stdClass();
  $field_group->disabled = FALSE; /* Edit this to true to make a default field_group disabled initially */
  $field_group->api_version = 1;
  $field_group->identifier = 'group_admission_hero|node|admission|form';
  $field_group->group_name = 'group_admission_hero';
  $field_group->entity_type = 'node';
  $field_group->bundle = 'admission';
  $field_group->mode = 'form';
  $field_group->parent_name = '';
  $field_group->data = array(
    'label' => 'Hero',
    'weight' => '2',
    'children' => array(
      0 => 's_n_hero_image',
      1 => 's_n_hero_link',
      2 => 'n_admission_hero_title',
      3 => 'n_admission_hero_subhead',
    ),
    'format_type' => 'fieldset',
    'format_settings' => array(
      'formatter' => 'collapsible',
      'instance_settings' => array(
        'description' => '',
        'classes' => 'group-admission-hero field-group-fieldset',
        'required_fields' => 1,
        'id' => '',
      ),
    ),
  );
  $field_groups['group_admission_hero|node|admission|form'] = $field_group;

  $field_group = new stdClass();
  $field_group->disabled = FALSE; /* Edit this to true to make a default field_group disabled initially */
  $field_group->api_version = 1;
  $field_group->identifier = 'group_mindset_hero|node|mindset|form';
  $field_group->group_name = 'group_mindset_hero';
  $field_group->entity_type = 'node';
  $field_group->bundle = 'mindset';
  $field_group->mode = 'form';
  $field_group->parent_name = '';
  $field_group->data = array(
    'label' => 'Hero',
    'weight' => '2',
    'children' => array(
      0 => 's_n_hero_image',
      1 => 'n_mindset_hero_title',
      2 => 'n_mindset_hero_text',
    ),
    'format_type' => 'fieldset',
    'format_settings' => array(
      'formatter' => 'collapsible',
      'instance_settings' => array(
        'description' => '',
        'classes' => 'group-mindset-hero field-group-fieldset',
        'required_fields' => 1,
        'id' => '',
      ),
    ),
  );
  $field_groups['group_mindset_hero|node|mindset|form'] = $field_group;

  // Translatables
  // Included for use with string extractors like potx.
  t('Hero');

  return $field_groups;
}
