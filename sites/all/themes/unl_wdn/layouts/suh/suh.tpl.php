<?php
/**
 * @file
 * Template
 *
 * Variables:
 * - $css_id: An optional CSS id to use for the layout.
 * - $content: An array of content, each item in the array is keyed to one
 * panel of the layout. This layout supports the following sections:
 */
?>

<div class="panel-display clearfix <?php if (!empty($class)) { print $class; } ?>" <?php if (!empty($css_id)) { print "id=\"$css_id\""; } ?>>

  <div class="grid9 first">
    <?php print $content['contentmain']; ?>
  </div>
  <div class="grid3">
    <?php print $content['sidebar']; ?>
  </div>

</div>
