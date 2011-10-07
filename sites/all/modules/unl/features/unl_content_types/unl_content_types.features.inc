<?php
/**
 * @file
 * unl_content_types.features.inc
 */

/**
 * Implementation of hook_node_info().
 */
function unl_content_types_node_info() {
  $items = array(
    'unl_carousel' => array(
      'name' => t('Carousel'),
      'base' => 'node_content',
      'description' => t('A slideshow of photos.'),
      'has_title' => '1',
      'title_label' => t('Title'),
      'help' => '',
    ),
    'unl_people_directory' => array(
      'name' => t('People Directory'),
      'base' => 'node_content',
      'description' => t('A directory listing of staff and/or faculty members of the specified unit pulled from directory.unl.edu.'),
      'has_title' => '1',
      'title_label' => t('Title'),
      'help' => '',
    ),
  );
  return $items;
}
