<?php
// $Id:

/**
 * @file
 * maestro-workflow-edit-template-variables-list.tpl.php
 */

?>

<tr class="<?php print $zebra ?>">
  <td>
    <div style="display:<?php print $show_variable_actions; ?>;">
      <?php print filter_xss($variable_name); ?>
    </div>
    <div style="display:<?php print $show_variable_edit_actions; ?>;">
      <input type="text" name="editVarName_<?php print $var_id; ?>" id="editVarName_<?php print $var_id; ?>" value="<?php print filter_xss($variable_name); ?>" style="border: solid black 1px"></input>
    </div>
  </td>
  <td>
    <div style="display:<?php print $show_variable_actions; ?>;">
      <?php print filter_xss($variable_value); ?>
    </div>
    <div style="display:<?php print $show_variable_edit_actions; ?>;">
      <input type="text" name="editVarValue_<?php print $var_id; ?>" id="editVarValue_<?php print $var_id; ?>" value="<?php print filter_xss($variable_value); ?>" style="border: solid black 1px"></input>
    </div>
  </td>
  <td>
    <div id="showVariableActions" style="display:<?php print $show_variable_actions; ?>;">
      <input title="<?php print t('Edit the Variable'); ?>" type="image" src="<?php print $module_path; ?>/images/admin/edit_tasks.gif" onclick="maestro_editTemplateVariable(<?php print $tid; ?>,<?php print $var_id; ?>);" >&nbsp;
      <input title="<?php print t('Delete the Variable'); ?>" type="image" src="<?php print $module_path; ?>/images/admin/delete.gif" onclick="maestro_deleteTemplateVariable(<?php print $tid; ?>,<?php print $var_id; ?>);" >&nbsp;
    </div>
    <div id="showVariableEditActions" style="display:<?php print $show_variable_edit_actions; ?>;">
      <span id="maestro_updating_variable_<?php print $var_id; ?>"  class=""></span>
      <input type="button" value="<?php print t('Save'); ?>" onClick='maestro_saveTemplateVariable(<?php print $tid; ?>,<?php print $var_id; ?>);'>&nbsp;
      <input type="button" value="<?php print t('Cancel'); ?>" onClick='maestro_CancelTemplateVariable(<?php print $tid; ?>);'>&nbsp;
    </div>
  </td>
</tr>