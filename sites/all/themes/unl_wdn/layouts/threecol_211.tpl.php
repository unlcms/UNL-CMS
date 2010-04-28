<?php
// $Id: panels-twocol-stacked.tpl.php,v 1.1.2.1 2008/12/16 21:27:59 merlinofchaos Exp $
/**
 * @file
 * Template for a 2 column panel layout.
 *
 * This template provides a two column panel display layout, with
 * additional areas for the top and the bottom.
 *
 * Variables:
 * - $id: An optional CSS id to use for the layout.
 * - $content: An array of content, each item in the array is keyed to one
 *   panel of the layout. This layout supports the following sections:
 *   - $content['left']: Content in the left column.
 *   - $content['right']: Content in the right column.
 */
?>
<div <?php if (!empty($css_id)) { print "id=\"$css_id\""; } ?>>
    <div class="two_col left">
        <?php echo $content['left']; ?>
    </div>
    <div class="col">
        <?php echo $content['center']; ?>
    </div>
    <div class="col right">
        <?php echo $content['right']; ?>
    </div>
    <div style="clear: both;"></div>
</div>

