<?php
// $Id: maestro-task-unknown.tpl.php,v 1.2 2010/09/02 18:09:08 chevy Exp $

/**
 * @file
 * maestro-task-unknown.tpl.php
 */

?>

<div class="maestro_task">
  <div class="t"><div class="b"><div class="r"><div class="l"><div class="bl"><div class="br"><div class="tl-red"><div class="tr-red">

    <div id="task_title<?php print $tdid; ?>" class="tm-red maestro_task_title">
      <?php print $taskname; ?>
    </div>
    <div class="maestro_task_body">
      <b><?php print t('Invalid Task Type:'); ?><br>
      <?php print $ti->_task_class; ?></b>
    </div>

  </div></div></div></div></div></div></div></div>
</div>
