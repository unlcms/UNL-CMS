<?php

// Put the logo path into JavaScript for the live preview.
drupal_add_js(array('color' => array('logo' => theme_get_setting('logo', 'unl_wdn'))), 'setting');

$info = array(
  // Available colors and color labels used in theme.
  'fields' => array(
    'top' => t('Navigation top'),
    'bottom' => t('Navigation bottom'),
    'navhovertop' => t('Nav hover top'),
    'navhoverbottom' => t('Nav hover bottom'),
    'footertop' => t('Footer top'),
    'footerbottom' => t('Footer bottom'),
    'link' => t('Link color'),
  ),
  // Pre-defined color schemes.
  'schemes' => array(
    'default' => array(
      'title' => t('UNL (default)'),
      'colors' => array(
        'top' => '#b70302',
        'bottom' => '#9d0201',
        'navhovertop' => '#c12726',
        'navhoverbottom' => '#ac2625',
        'footertop' => '#b70303',
        'footerbottom' => '#9d0202',
        'link' => '#ba0000',
      ),
    ),
    'brown' => array(
      'title' => t('Brown'),
      'colors' => array(
        'top' => '#906337',
        'bottom' => '#654424',
        'navhovertop' => '#a3774b',
        'navhoverbottom' => '#765330',
        'footertop' => '#654425',
        'footerbottom' => '#513316',
        'link' => '#0081AF',
      ),
    ),
    'blue' => array(
      'title' => t('Blue Steel'),
      'colors' => array(
        'top' => '#1972a7',
        'bottom' => '#0e4e74',
        'navhovertop' => '#208dcd',
        'navhoverbottom' => '#1c94db',
        'footertop' => '#0e4e74',
        'footerbottom' => '#02314e',
        'link' => '#0064f8',
      ),
    ),
  ),

  // CSS files (excluding @import) to rewrite with new color scheme.
  'css' => array(
    'css/colors.css',
  ),

  // Files to copy.
     'copy' => array(
       //'logo.png',
     ),

  // Gradient definitions.
  'gradients' => array(
    array(
      // (x, y, width, height).
      'dimension' => array(0, 0, 0, 0),
      // Direction of gradient ('vertical' or 'horizontal').
      'direction' => 'vertical',
      // Keys of colors to use for the gradient.
      'colors' => array('top', 'bottom'),
    ),
  ),

  // Color areas to fill (x, y, width, height).
  'fill' => array(),

  // Coordinates of all the theme slices (x, y, width, height)
  // with their filename as used in the stylesheet.
  'slices' => array(),

  // Reference color used for blending. Matches the base.png's colors.
  'blend_target' => '#ffffff',

  // Preview files.
  'preview_css' => 'color/preview.css',
  'preview_js' => 'color/preview.js',
  'preview_html' => 'color/preview.html',

  // Base file for image generation. (Not used, but causes error without it.)
  'base_image' => 'color/base.png',
);