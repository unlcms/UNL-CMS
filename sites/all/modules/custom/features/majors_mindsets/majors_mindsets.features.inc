<?php

/**
 * @file
 * majors_mindsets.features.inc
 */

/**
 * Implements hook_ctools_plugin_api().
 */
function majors_mindsets_ctools_plugin_api($module = NULL, $api = NULL) {
  if ($module == "field_group" && $api == "field_group") {
    return array("version" => "1");
  }
}

/**
 * Implements hook_image_default_styles().
 */
function majors_mindsets_image_default_styles() {
  $styles = array();

  // Exported image style: contact_card.
  $styles['contact_card'] = array(
    'label' => 'Contact Card (800)',
    'effects' => array(
      9 => array(
        'name' => 'image_scale',
        'data' => array(
          'width' => 800,
          'height' => '',
          'upscale' => 0,
        ),
        'weight' => 1,
      ),
    ),
  );

  // Exported image style: mindsets_hero.
  $styles['mindsets_hero'] = array(
    'label' => 'Mindsets Hero (2400)',
    'effects' => array(
      6 => array(
        'name' => 'image_scale',
        'data' => array(
          'width' => 2400,
          'height' => '',
          'upscale' => 0,
        ),
        'weight' => 1,
      ),
    ),
  );

  // Exported image style: primary_support_photo.
  $styles['primary_support_photo'] = array(
    'label' => 'Primary Support Photo (1200)',
    'effects' => array(
      3 => array(
        'name' => 'image_scale',
        'data' => array(
          'width' => 1200,
          'height' => '',
          'upscale' => 0,
        ),
        'weight' => 1,
      ),
    ),
  );

  // Exported image style: secondary_support_card.
  $styles['secondary_support_card'] = array(
    'label' => 'Secondary Support Card (800)',
    'effects' => array(
      1 => array(
        'name' => 'image_scale',
        'data' => array(
          'width' => 800,
          'height' => '',
          'upscale' => 0,
        ),
        'weight' => 1,
      ),
    ),
  );

  return $styles;
}

/**
 * Implements hook_node_info().
 */
function majors_mindsets_node_info() {
  $items = array(
    'admission' => array(
      'name' => t('Admission page'),
      'base' => 'node_content',
      'description' => t('Admission / Prospective student page with Mindsets feature.'),
      'has_title' => '1',
      'title_label' => t('Title'),
      'help' => '',
    ),
    'mindset' => array(
      'name' => t('Mindset page'),
      'base' => 'node_content',
      'description' => t('For building out three Mindsets support pages.'),
      'has_title' => '1',
      'title_label' => t('Title'),
      'help' => '',
    ),
  );
  drupal_alter('node_info', $items);
  return $items;
}

/**
 * Implements hook_paragraphs_info().
 */
function majors_mindsets_paragraphs_info() {
  $items = array(
    'contact' => array(
      'name' => 'Contact',
      'bundle' => 'contact',
      'label' => 'Contact',
      'description' => 'For adding Contacts to bottom of pages.',
      'locked' => '1',
    ),
    'mindset_teaser' => array(
      'name' => 'Mindset Teaser',
      'bundle' => 'mindset_teaser',
      'label' => 'Mindset Teaser',
      'description' => 'Mindsets information for Admission / Prospective Student page.',
      'locked' => '1',
    ),
    'section_featured' => array(
      'name' => 'Section Featured',
      'bundle' => 'section_featured',
      'label' => 'Section Featured',
      'description' => '',
      'locked' => '1',
    ),
    'section_secondary' => array(
      'name' => 'Section Secondary',
      'bundle' => 'section_secondary',
      'label' => 'Section Secondary',
      'description' => '',
      'locked' => '1',
    ),
    'support_section' => array(
      'name' => 'Support Section',
      'bundle' => 'support_section',
      'label' => 'Support Section',
      'description' => 'Mindset pages copy and data support sections.',
      'locked' => '1',
    ),
  );
  return $items;
}
