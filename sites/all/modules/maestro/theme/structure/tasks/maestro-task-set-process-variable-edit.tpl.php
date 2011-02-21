<?php
// $Id: maestro-task-set-process-variable-edit.tpl.php,v 1.5 2010/08/30 21:25:26 chevy Exp $

/**
 * @file
 * maestro-task-set-process-variable-edit.tpl.php
 */

?>

<table>
  <tr>
    <td><?php print t('Variable to Set:'); ?></td>
    <td>
      <select name="var_to_set">
<?php
        foreach ($pvars as $value=>$label) {
          if ($value == $td_rec->task_data['var_to_set']) {
?>
            <option value="<?php print $value;?>" selected="selected"><?php print $label;?></option>
<?php
          }
          else {
?>
            <option value="<?php print $value;?>"><?php print $label;?></option>
<?php
          }
        }
?>
      </select>
    </td>
  </tr>
<?php
  foreach ($set_methods as $key => $method) {
?>
  <tr>
    <td>
      <label for="set_type_opt_<?php print $key; ?>"><input type="radio" id="set_type_opt_<?php print $key; ?>" name="set_type" value="<?php print $key; ?>" onchange="toggle_set_type('<?php print $key; ?>');" <?php print ($td_rec->task_data['set_type'] == $key) ? 'checked="checked"':''; ?>>
      <?php print $method['title']; ?></label>
    </td>
    <td><input class="set_method" id="set_type_<?php print $key; ?>" type="text" name="<?php print $key; ?>_value" value="<?php print $td_rec->task_data[$key . '_value']; ?>"></td>
  </tr>
<?php
  }
?>
</table>

<script type="text/javascript">
  setTimeout(tick, 500);

  function tick() {
    toggle_set_type('<?php print $td_rec->task_data['set_type']; ?>');
  }


  function toggle_set_type(type) {
    (function($) {
      $('.set_method').hide();
      $('#set_type_' + type).show();
    })(jQuery);
  }
</script>
