<?php

?>

  <div id="task<?php print $rec->id; ?>" class="<?php print $task_class; ?> maestro_task_container" onclick="draw_line_to(this);" style="left: <?php print $rec->offset_left; ?>px; top: <?php print $rec->offset_top; ?>px;">
    <?php print $ti->display(); ?>
  </div>

  <?php print $ti->getContextMenuHTML(); ?>

  <script type="text/javascript">
    <?php print $ti->getContextMenuJS(); ?>
  </script>