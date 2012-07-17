<?php

/**
 * @file media_mediahub/includes/themes/media-mediahub-video.tpl.php
 *
 * Template file for theme('media_mediahub_video').
 *
 * Variables available:
 *  $uri - The uri to the MediaHub video, such as mediahub://media/2134.
 *  $video_id - The unique identifier of the MediaHub video.
 *  $width - The width to render.
 *  $height - The height to render.
 *  $autoplay - If TRUE, then start the player automatically when displaying.
 *  $fullscreen - Whether to allow fullscreen playback.
 *
 * Note that we set the width & height of the outer wrapper manually so that
 * the JS will respect that when resizing later.
 */
?>
<div class="media-mediahub-outer-wrapper" id="media-mediahub-<?php print $id; ?>" style="position:relative;">
  <div class="media-mediahub-preview-wrapper" id="<?php print $wrapper_id; ?>">
    <?php print $output; ?>
  </div>
</div>

