<?php
// $Id: maestro-workflow-assign-notify-select-boxes.tpl.php,v 1.1 2010/08/19 19:34:51 chevy Exp $

/**
 * @file
 * maestro-workflow-assign-notify-select-boxes.tpl.php
 */

?>

  <tr class="<?php print "{$row_class} {$type} {$by_variable} {$when}"; ?>" style="display: <?php print $display; ?>;">
    <td style="width: 200px;">
      <select size="4" multiple="multiple" style="width: 100%;" id="<?php print "{$row_class}_{$type}_{$by_variable}_{$when}_unselected"; ?>">
<?php
        foreach ($options as $value => $label) {
          if (!in_array($value, $selected_options)) {
?>
            <option value="<?php print $value; ?>"><?php print $label; ?></option>
<?php
          }
        }
?>
      </select>
    </td>
    <td style="text-align: center;">
      <a href="#" onclick="move_options(<?php print "'{$row_class}_{$type}_{$by_variable}_{$when}', '{$row_class}_{$type}_{$by_variable}_{$when}_unselected'"; ?>); return false;"><img src="<?php print $maestro_url; ?>/images/admin/left-arrow.png"></a>
      &nbsp;&nbsp;&nbsp;
      <a href="#" onclick="move_options(<?php print "'{$row_class}_{$type}_{$by_variable}_{$when}_unselected', '{$row_class}_{$type}_{$by_variable}_{$when}'"; ?>); return false;"><img src="<?php print $maestro_url; ?>/images/admin/right-arrow.png"></a>
    </td>
    <td style="width: 200px;">
      <script type="text/javascript"> select_boxes.push('<?php print "{$row_class}_{$type}_{$by_variable}_{$when}"; ?>'); </script>
      <select size="4" multiple="multiple" style="width: 100%;" id="<?php print "{$row_class}_{$type}_{$by_variable}_{$when}"; ?>" name="<?php print $name; ?>">
<?php
        foreach ($options as $value => $label) {
          if (in_array($value, $selected_options)) {
?>
            <option value="<?php print $value; ?>"><?php print $label; ?></option>
<?php
          }
        }
?>
      </select>
    </td>
  </tr>
