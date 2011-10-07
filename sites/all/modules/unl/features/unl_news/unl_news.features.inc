<?php
/**
 * @file
 * unl_news.features.inc
 */

/**
 * Implements hook_views_api().
 */
function unl_news_views_api() {
  list($module, $api) = func_get_args();
  if ($module == "views" && $api == "views_default") {
    return array("version" => "3.0");
  }
}

/**
 * Implements hook_image_default_styles().
 */
function unl_news_image_default_styles() {
  $styles = array();

  // Exported image style: square_thumbnail
  $styles['square_thumbnail'] = array(
    'name' => 'square_thumbnail',
    'effects' => array(
      2 => array(
        'label' => 'Scale and crop',
        'help' => 'Scale and crop will maintain the aspect-ratio of the original image, then crop the larger dimension. This is most useful for creating perfectly square thumbnails without stretching the image.',
        'effect callback' => 'image_scale_and_crop_effect',
        'form callback' => 'image_resize_form',
        'summary theme' => 'image_resize_summary',
        'module' => 'image',
        'name' => 'image_scale_and_crop',
        'data' => array(
          'width' => '60',
          'height' => '60',
        ),
        'weight' => '-10',
      ),
    ),
  );

  return $styles;
}

/**
 * Implements hook_node_info().
 */
function unl_news_node_info() {
  $items = array(
    'unl_newsitem' => array(
      'name' => t('News item'),
      'base' => 'node_content',
      'description' => t('A news article with byline and associated imagery. <em>(Provided by the UNLcms project.)</em>'),
      'has_title' => '1',
      'title_label' => t('Title'),
      'help' => '',
    ),
  );
  return $items;
}
