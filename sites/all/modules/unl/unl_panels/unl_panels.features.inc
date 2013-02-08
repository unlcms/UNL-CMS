<?php
/**
 * @file
 * unl_panels.features.inc
 */

/**
 * Implements hook_ctools_plugin_api().
 */
function unl_panels_ctools_plugin_api() {
  list($module, $api) = func_get_args();
  if ($module == "file_entity" && $api == "file_default_displays") {
    return array("version" => "1");
  }
}

/**
 * Implements hook_image_default_styles().
 */
function unl_panels_image_default_styles() {
  $styles = array();

  // Exported image style: unl_image_full
  $styles['unl_image_full'] = array(
    'name' => 'unl_image_full',
    'effects' => array(
      1 => array(
        'label' => 'Scale',
        'help' => 'Scaling will maintain the aspect-ratio of the original image. If only a single dimension is specified, the other dimension will be calculated.',
        'effect callback' => 'image_scale_effect',
        'dimensions callback' => 'image_scale_dimensions',
        'form callback' => 'image_scale_form',
        'summary theme' => 'image_scale_summary',
        'module' => 'image',
        'name' => 'image_scale',
        'data' => array(
          'width' => '960',
          'height' => '',
          'upscale' => 0,
        ),
        'weight' => '1',
      ),
    ),
  );

  return $styles;
}
