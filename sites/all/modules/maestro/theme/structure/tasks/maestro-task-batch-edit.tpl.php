<?php
// $Id: maestro-task-batch-edit.tpl.php,v 1.4 2010/07/21 21:52:54 chevy Exp $

/**
 * @file
 * maestro-task-batch-edit.tpl.php
 */

?>

<table>
  <tr>
   <td colspan="2">
      <?php print t('Handler base path:'); ?>
      <?php print $td_rec->task_data['handler_location'];?>
    </td>
  </tr>
  <tr>
    <td><?php print t('Handler:'); ?></td>
    <td><input type="text" name="handler" value="<?php print $td_rec->task_data['handler']; ?>"></td>
  </tr>
</table>
