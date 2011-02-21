<?php
// $Id: maestro-workflow-edit.tpl.php,v 1.6 2010/07/22 21:16:52 chevy Exp $

/**
 * @file
 * maestro-workflow-edit.tpl.php
 */

?>

  <div id="maestro_ajax_indicator" class="maestro_ajax_indicator" style="display: none;"><img src="<?php print $maestro_url; ?>/images/admin/status-active.gif"></div>

  <div class="maestro_heading"><?php print $t_rec->template_name; ?></div>

  <form name="frm_animate" action="#" method="post">
      <?php print t('Enable Animation'); ?>: <input type="checkbox" name="animateFlag" value="1" checked="checked">&nbsp;&nbsp;&nbsp;
      <?php print t('Snap to Grid'); ?>: <input type="checkbox" name="snapToGrid" value="1" onclick="update_snap_to_grid();">&nbsp;&nbsp;&nbsp;
      <?php print t('Snap to Objects'); ?>: <input type="checkbox" name="snapToObjects" value="1" onclick="update_snap_to_objects();" checked="checked">
  </form>

  <div id="maestro_tool_tip_container" class="maestro_tool_tip" style="display: none;"><div class="t"><div class="b"><div class="r"><div class="l"><div class="bl-bge"><div class="br-bge"><div class="tl-bge"><div class="tr-bge">
  <div id="maestro_tool_tip" class="maestro_tool_tip_inner"></div>
  </div></div></div></div></div></div></div></div></div>

  <div id="maestro_workflow_container" class="maestro_workflow_container" style="position: abosolute; height: <?php print $t_rec->canvas_height; ?>px;">
    <?php print $mi->displayTasks(); ?>
  </div>

  <?php print $mi->getContextMenuHTML(); ?>

  <div>
    <a href="#" onClick="grow_canvas(); return false;"><?php print t('Grow Canvas'); ?></a>
    <a href="#" onClick="shrink_canvas(); return false;"><?php print t('Shrink Canvas'); ?></a>
  </div>

  <script type="text/javascript">
    var ajax_url = '<?php print $ajax_url; ?>';
    var template_id = <?php print $tid; ?>;
    <?php print $additional_js; ?>
    <?php print $mi->getContextMenuJS(); ?>
  </script>
