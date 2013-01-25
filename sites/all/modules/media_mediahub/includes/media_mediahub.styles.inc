<?php

/**
 * @file media_mediahub/includes/media_mediahub.styles.inc
 * Styles definitions for Media: MediaHub.
 */

/**
 * Implementation of Styles module hook_styles_register().
 */
function media_mediahub_styles_register() {
  return array(
    'MediaMediaHubStyles' => array(
      'field_types' => 'file',
      'name' => t('MediaMediaHub'),
      'description' => t('Media MediaHub styles.'),
      'path' => drupal_get_path('module', 'media_mediahub') .'/includes',
      'file' => 'media_mediahub.styles.inc',
    ),
  );
}

/**
 *  Implements hook_styles_containers(). (Deprecated in version 2)
 */
function media_mediahub_styles_containers() {
  return array(
    'file' => array(
      'containers' => array(
        'media_mediahub' => array(
          'label' => t('MediaHub Styles'),
          'data' => array(
            'streams' => array(
              'mediahub',
            ),
            'mimetypes' => array(
              'video/mediahub',
            ),
          ),
          'weight' => 0,
          'filter callback' => 'media_mediahub_formatter_filter',
          'themes' => array(
            'field_formatter_styles' => 'media_mediahub_field_formatter_styles',
            'styles' => 'media_mediahub_styles',
            'preview' => 'media_mediahub_preview_style',
          ),
          'description' => t('MediaHub Styles will display embedded MediaHub videos and thumbnails to your choosing, such as by resizing, setting colors, and autoplay. You can !manage.', array('!manage' => l(t('manage your MediaHub styles here'), 'admin/config/media/media-mediahub-styles'))),
        ),
      ),
    ),
  );
}

function media_mediahub_formatter_filter($variables) {
  if (isset($variables['object'])) {
    $object = isset($variables['object']->file) ? $variables['object']->file : $variables['object'];
    return (file_uri_scheme($object->uri) == 'mediahub') && ($object->filemime == 'video/mediahub');
  }
}

/**
 * Implementation of the File Styles module's hook_file_styles_filter().
 */
function media_mediahub_file_styles_filter($object) {
  $file = isset($object->file) ? $object->file : $object;
  if ((file_uri_scheme($file->uri) == 'mediahub') && ($file->filemime == 'video/mediahub')) {
    return 'media_mediahub';
  }
}

/**
 *  Implements hook_styles_styles().
 */
function media_mediahub_styles_styles() {
  $styles = array(
    'file' => array(
      'containers' => array(
        'media_mediahub' => array(
          'styles' => array(
            'mediahub_thumbnail' => array(
              'name' => 'mediahub_thumbnail',
              'effects' => array(
                array('label' => t('Thumbnail'), 'name' => 'thumbnail', 'data' => array('thumbnail' => 1)),
                array('label' => t('Resize'), 'name' => 'resize', 'data' => array('width' => 100, 'height' => 75)),
              ),
            ),
            'mediahub_preview' => array(
              'name' => 'mediahub_preview',
              'effects' => array(
                array('label' => t('Autoplay'), 'name' => 'autoplay', 'data' => array('autoplay' => 0)),
                array('label' => t('Resize'), 'name' => 'resize', 'data' => array('width' => 220, 'height' => 165)),
              ),
            ),
            'mediahub_full' => array(
              'name' => 'mediahub_full',
              'effects' => array(
                array('label' => t('Autoplay'), 'name' => 'autoplay', 'data' => array('autoplay' => 0)),
                array('label' => t('Resize'), 'name' => 'resize', 'data' => array('width' => 640, 'height' => 480)),
                array('label' => t('Full screen'), 'name' => 'fullscreen', 'data' => array('fullscreen' => 1)),
              ),
            ),
          ),
        ),
      ),
    ),
  );

  // Allow any image style to be applied to the thumbnail.
  foreach (image_styles() as $style_name => $image_style) {
    $styles['file']['containers']['media_mediahub']['styles']['mediahub_thumbnail_' . $style_name] = array(
      'name' => 'mediahub_thumbnail_' . $style_name,
      'image_style' => $style_name,
      'effects' => array(
        array('label' => t('Thumbnail'), 'name' => 'thumbnail', 'data' => array('thumbnail' => 1)),
      ),
    );
  }

  return $styles;
}

/**
 *  Implements hook_styles_presets().
 */
function media_mediahub_styles_presets() {
  $presets = array(
    'file' => array(
      'square_thumbnail' => array(
        'media_mediahub' => array(
          'mediahub_thumbnail_square_thumbnail',
        ),
      ),
      'thumbnail' => array(
        'media_mediahub' => array(
          'mediahub_thumbnail',
        ),
      ),
      'small' => array(
        'media_mediahub' => array(
          'mediahub_preview',
        ),
      ),
      'large' => array(
        'media_mediahub' => array(
          'mediahub_full',
        ),
      ),
      'original' => array(
        'media_mediahub' => array(
          'mediahub_full',
        ),
      ),
    ),
  );
  return $presets;
}

/**
 * Implementation of Styles module hook_styles_default_containers().
 */
function media_mediahub_styles_default_containers() {
  // We append MediaHub to the file containers.
  return array(
    'file' => array(
      'containers' => array(
        'media_mediahub' => array(
          'class' => 'MediaMediaHubStyles',
          'name' => 'media_mediahub',
          'label' => t('MediaHub'),
          'preview' => 'media_mediahub_preview_style',
        ),
      ),
    ),
  );
}


/**
 * Implementation of Styles module hook_styles_default_presets().
 */
function media_mediahub_styles_default_presets() {
  $presets = array(
    'file' => array(
      'containers' => array(
        'media_mediahub' => array(
          'default preset' => 'unlinked_thumbnail',
          'styles' => array(
            'original' => array(
              'default preset' => 'video',
            ),
            'thumbnail' => array(
              'default preset' => 'linked_thumbnail',
            ),
            'square_thumbnail' => array(
              'default preset' => 'linked_square_thumbnail',
            ),
            'medium' => array(
              'default preset' => 'linked_medium',
            ),
            'large' => array(
              'default preset' => 'large_video',
            ),
          ),
          'presets' => array(
            'video' => array(
              array(
                'name' => 'video',
                'settings' => array(),
              ),
            ),
            'large_video' => array(
              array(
                'name' => 'resize',
                'settings' => array(
                  'width' => 640,
                  'height' => 390,
                ),
              ),
              array(
                'name' => 'video',
                'settings' => array(),
              ),
            ),
          ),
        ),
      ),
    ),
  );
  // Allow any image style to be applied to the thumbnail.
  foreach (image_styles() as $style_name => $image_style) {
    $presets['file']['containers']['media_mediahub']['presets']['linked_' . $style_name] = array(
      array(
        'name' => 'linkToMedia',
        'settings' => array(),
      ),
      array(
        'name' => 'imageStyle',
        'settings' => array(
          'image_style' => $style_name,
        ),
      ),
      array(
        'name' => 'thumbnail',
        'settings' => array(),
      ),
    );
    $presets['file']['containers']['media_mediahub']['presets']['unlinked_' . $style_name] = $presets['file']['containers']['media_mediahub']['presets']['linked_' . $style_name];
    array_shift($presets['file']['containers']['media_mediahub']['presets']['unlinked_' . $style_name]);
    foreach ($image_style['effects'] as $effect) {
      if (in_array($effect['name'], array('image_scale', 'image_scale_and_crop', 'image_resize', 'image_crop'))) {
        $presets['file']['containers']['media_mediahub']['presets']['video_' . $style_name] = array(
          array(
            'name' => 'resize',
            'settings' => $effect['data'],
          ),
          array(
            'name' => 'video',
            'settings' => array(),
          ),
        );
      }
    }
  }
  return $presets;
}
