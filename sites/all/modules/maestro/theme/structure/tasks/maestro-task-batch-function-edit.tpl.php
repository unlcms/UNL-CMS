<?php
// $Id: maestro-task-batch-function-edit.tpl.php,v 1.4 2010/08/23 19:21:46 chevy Exp $

/**
 * @file
 * maestro-task-batch-function-edit.tpl.php
 */

?>

<table>
  <tr>
    <td nowrap style="vertical-align: top;"><?php print t('Handler:'); ?></td>
    <td width="90%">
      <select id="handler_options" name="handler" onchange="change_handler_option();">
        <option id="handler_" value="" message="<?php print t('Please specify the handler in the text box provided'); ?>"><?php print t('other'); ?></option>
<?php
        foreach ($handler_options as $value => $label) {
?>
          <option id="handler_<?php print $value; ?>" message="<?php print str_replace('"', '\'', $label); ?>" value="<?php print $value; ?>" <?php print ($td_rec->task_data['handler'] == $value) ? 'selected="selected"':''; ?>><?php print $value; ?></option>
<?php
        }
?>
      </select>
      <div id="handler_options_other" style="padding: 5px 0px 0px 0px; display: <?php print (array_key_exists($td_rec->task_data['handler'], $handler_options)) ? 'none':''; ?>"><input id="handler_options_other_text" type="text" name="handler_other" value="<?php print $td_rec->task_data['handler']; ?>"></div>
    </td>
  </tr>
  <tr>
    <td></td>
    <td id="handler_options_message"><?php print (array_key_exists($td_rec->task_data['handler'], $handler_options)) ? $handler_options[$td_rec->task_data['handler']] : t('Please specify the handler in the text box provided'); ?></td>
  </tr>
</table>
