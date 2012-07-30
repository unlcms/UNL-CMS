<?php

/**
 *  @file
 *  Create a MediaHub Stream Wrapper class for the Media/Resource module.
 */

/**
 *  Create an instance like this:
 *  $mediahub = new ResourceMediaHubStreamWrapper('mediahub://media/[video-code]');
 */
class MediaMediaHubStreamWrapper extends MediaReadOnlyStreamWrapper {
  protected $base_url = 'http://mediahub.unl.edu/media';

  function getTarget($f) {
    return FALSE;
  }

  static function getMimeType($uri, $mapping = NULL) {
    return 'video/mediahub';
  }

  function getOriginalThumbnailPath() {
    $parts = $this->get_parameters();
    return 'http://mediahub.unl.edu/media/'. check_plain($parts['media']) .'/image';
  }

  function getLocalThumbnailPath() {
    $parts = $this->get_parameters();
    $local_path = 'public://media-mediahub/' . check_plain($parts['media']) . '.jpg';
    if (!file_exists($local_path)) {
      $dirname = drupal_dirname($local_path);
      file_prepare_directory($dirname, FILE_CREATE_DIRECTORY | FILE_MODIFY_PERMISSIONS);
      @copy($this->getOriginalThumbnailPath(), $local_path);
    }
    return $local_path;
  }
}
