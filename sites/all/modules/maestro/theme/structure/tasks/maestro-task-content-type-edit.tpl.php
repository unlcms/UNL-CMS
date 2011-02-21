<?php
// $Id:

/**
 * @file
 * maestro-task-content-type-edit.tpl.php
 */

?>
<br></br>
<table>
  <tr>
    <td>
      <?php print t('Select the Content Type for this task:'); ?>
      <select name="content_type">
        <?php foreach($content_types as $type => $obj) {?>
        <option value="<?php print $obj->type; ?>" <?php if($obj->type == $td_rec->task_data['content_type']) print "selected"; ?>>
          <?php print $obj->name; ?>
        </option>
        <?php } ?>
      </select>
    </td>
  </tr>
</table>
