<?php

/**
 * @file media_mediahub/includes/themes/media_mediahub.theme.inc
 *
 * Theme and preprocess functions for Media: MediaHub.
 */

/**
 * Preprocess function for theme('media_mediahub_video').
 */
function media_mediahub_preprocess_media_mediahub_video(&$variables) {
  // Build the URL for display.
  $uri = $variables['uri'];
  $wrapper = file_stream_wrapper_get_instance_by_uri($uri);
  $parts = $wrapper->get_parameters();
  $variables['video_id'] = check_plain($parts['media']);
  $variables['query'] = array();

  $variables['wrapper_id'] = 'media_mediahub_' . $variables['video_id'] . '_' . $variables['id'];

  // Pass the settings to replace the object tag with an iframe.
  $settings = array(
    'media_mediahub' => array(
      $variables['wrapper_id'] => array(
        'video_id' => $variables['video_id'],
        'id' => $variables['wrapper_id'],
      ),
    ),
  );

  drupal_add_js($settings, 'setting');
  drupal_add_css(drupal_get_path('module', 'media_mediahub') . '/css/media_mediahub.css');

  $variables['url_api'] = 'media/' . $variables['video_id'];

  $variables['url'] = url('http://mediahub.unl.edu/' . $variables['url_api'], array('query' => $variables['query'], 'external' => TRUE, 'https' => TRUE));

  $variables['output'] = <<<OUTPUT
<video class="wdn_player" src="http://mediahub.unl.edu/media/{$variables['video_id']}/file" controls poster="http://mediahub.unl.edu/media/{$variables['video_id']}/image">
        <track src="http://mediahub.unl.edu/media/{$variables['video_id']}/vtt" kind="subtitles" type="text/vtt" srclang="en" />
    </video>
    <script>WDN.initializePlugin('mediaelement_wdn');</script>

OUTPUT;
}
