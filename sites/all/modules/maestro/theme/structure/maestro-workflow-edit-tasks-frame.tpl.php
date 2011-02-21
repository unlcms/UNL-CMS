<?php
// $Id: maestro-workflow-edit-tasks-frame.tpl.php,v 1.21 2010/08/30 16:13:53 blainelang Exp $

/**
 * @file
 * maestro-workflow-edit-tasks-frame.tpl.php
 */

?>

<div>
  <div style="margin: 0px 0px 0px 10px; float: left;">&nbsp;</div>

  <div id="task_edit_tab_main" class="active"><div class="maestro_task_edit_tab"><div class="t"><div class=""><div class="r"><div class="l"><div class="bl-tab"><div class="br-tab"><div class="tl-tab"><div class="tr-tab">
  <a href="#" onclick="switch_task_edit_section('main'); return false;"><?php print t('Main'); ?></a>
  </div></div></div></div></div></div></div></div></div></div>

<?php
  if (array_key_exists('optional', $task_edit_tabs) && $task_edit_tabs['optional'] == 1) {
?>
    <div id="task_edit_tab_optional" class="unactive"><div class="maestro_task_edit_tab"><div class="t"><div class=""><div class="r"><div class="l"><div class="bl-tab"><div class="br-tab"><div class="tl-tab"><div class="tr-tab">
    <a href="#" onclick="switch_task_edit_section('optional'); return false;"><?php print t('Optional'); ?></a>
    </div></div></div></div></div></div></div></div></div></div>
<?php
  }
?>

<?php
  if (array_key_exists('assignment', $task_edit_tabs) && $task_edit_tabs['assignment'] == 1) {
?>
    <div id="task_edit_tab_assignment" class="unactive"><div class="maestro_task_edit_tab"><div class="t"><div class=""><div class="r"><div class="l"><div class="bl-tab"><div class="br-tab"><div class="tl-tab"><div class="tr-tab">
    <a href="#" onclick="switch_task_edit_section('assignment'); set_summary('assign'); return false;"><?php print t('Assignment'); ?></a>
    </div></div></div></div></div></div></div></div></div></div>
<?php
  }
?>

<?php
  if (array_key_exists('notification', $task_edit_tabs) && $task_edit_tabs['notification'] == 1) {
?>
    <div id="task_edit_tab_notification" class="unactive"><div class="maestro_task_edit_tab"><div class="t"><div class=""><div class="r"><div class="l"><div class="bl-tab"><div class="br-tab"><div class="tl-tab"><div class="tr-tab">
    <a href="#" onclick="switch_task_edit_section('notification'); set_summary('notify'); return false;"><?php print t('Notification'); ?></a>
    </div></div></div></div></div></div></div></div></div></div>
<?php
  }
?>

  <div style="margin: 0px 10px 0px 0px; float: right;">&nbsp;</div>

  <div class="active"><div class="maestro_task_edit_tab_close" style="float: right;"><div class="t"><div class=""><div class="r"><div class="l"><div class="bl-cl"><div class="br-cl"><div class="tl-cl"><div class="tr-cl">
  <a href="#" onclick="(function($) { $.modal.close(); disable_ajax_indicator(); select_boxes = []; })(jQuery); return false;"><img src="<?php print $maestro_url; ?>/images/admin/close.png"></a>
  </div></div></div></div></div></div></div></div></div></div>

  <div style="clear: both;"></div>

  <div class="maestro_task_edit_panel">
    <div class="t"><div class="b"><div class="r"><div class="l"><div class="bl-wht"><div class="br-wht"><div class="tl-wht"><div class="tr-wht">
      <form id="maestro_task_edit_form" method="post" action="" onsubmit="return save_task(this);">
        <input type="hidden" name="task_class" value="<?php print $task_class; ?>">
        <input type="hidden" name="template_data_id" value="<?php print $tdid; ?>">

        <div id="task_edit_main">
          <div style="float: none;" class="maestro_tool_tip maestro_taskname"><div class="t"><div class="b"><div class="r"><div class="l"><div class="bl-bge"><div class="br-bge"><div class="tl-bge"><div class="tr-bge">
            <?php print t('Task Name'); ?>: <input id="maestro_task_name" type="text" name="taskname" value="<?php print $vars->taskname; ?>"><br>
            <label for="regen"><input type="checkbox" id="regen" name="regen" value="1" <?php print ($vars->regenerate == 1) ? 'checked="checked"':''; ?>><?php print t('Regenerate This Task'); ?></label>&nbsp;&nbsp;&nbsp;
            <label for="regenall"><input type="checkbox" id="regenall" name="regenall" value="1" <?php print ($vars->regen_all_live_tasks == 1) ? 'checked="checked"':''; ?>><?php print t('Regenerate All In-Production Tasks'); ?></label>
            <label for="showindetail"><input type="checkbox" id="showindetail" name="showindetail" value="1" <?php print ($vars->show_in_detail == 1) ? 'checked="checked"':''; ?>><?php print t('Show in Detail'); ?></label>
          </div></div></div></div></div></div></div></div></div><br />

          <?php print $form_content; ?>
        </div>

<?php
  if (array_key_exists('optional', $task_edit_tabs) && $task_edit_tabs['optional'] == 1) {
?>
        <div id="task_edit_optional" style="display: none;">
          <table style="display: none;">
            <tbody id="optional_parm_form">
              <tr>
                <td width="33%" style="vertical-align: top; white-space: nowrap;">
                  <input type="text" name="op_var_names[]" value="" style="width: 150px;">
                </td>
                <td width="67%" style="vertical-align: top; white-space: nowrap;">
                  <textarea name="op_var_values[]" rows="1" cols="32"></textarea>
                  <a href="#" onclick="remove_variable(this); return false;"><img src="<?php print $maestro_url; ?>/images/admin/remove.png" class="valigntop"></a>
                </td>
              </tr>
            </tbody>
          </table>

          <fieldset class="form-wrapper">
            <legend><span class="fieldset-legend"><a href="#" onclick="add_variable(); return false;"><?php print t('Add Variable'); ?></a></span></legend>

            <div class="fieldset-wrapper">
            <table class="sticky-enabled sticky-table">
              <thead class="tableheader-processed">
                <tr>
                  <th><?php print t('Variable Name'); ?></th>
                  <th><?php print t('Variable Value'); ?></th>
                </tr>
              </thead>
              <tbody id="optional_parm_vars">
<?php
                $i = 0;
                foreach ($optional_parms as $var_name => $var_value) {
                  $classname = ((++$i % 2) == 0) ? 'even':'odd';
?>
                  <tr class="<?php print $classname; ?>">
                    <td width="33%" style="vertical-align: top; white-space: nowrap;">
                      <input type="text" name="op_var_names[]" value="<?php print $var_name; ?>" style="width: 150px;">
                    </td>
                    <td width="67%" style="vertical-align: top; white-space: nowrap;">
                      <textarea name="op_var_values[]" rows="1" cols="32"><?php print $var_value; ?></textarea>
                      <a href="#" onclick="remove_variable(this); return false;"><img src="<?php print $maestro_url; ?>/images/admin/remove.png" class="valigntop"></a>
                    </td>
                  </tr>
<?php
                }
?>
                </tbody>
              </table>
            </div>
          </fieldset>
        </div>
<?php
  }

  if (array_key_exists('assignment', $task_edit_tabs) && $task_edit_tabs['assignment'] == 1) {
?>
        <div id="task_edit_assignment" style="display: none;">
          <table>
            <tr>
              <td colspan="3">
                <?php print t('Assignment Type:'); ?>&nbsp;
                <select name="assign_type" id="assign_type" onchange="toggle_list_type('assign');">
<?php
                  foreach ($types as $opt) {
?>
                    <option value="<?php print strtolower($opt['name']); ?>"><?php print $opt['label']; ?></option>
<?php
                    break;  //remove when once we add the role / group options
                  }
?>
                  <optgroup style="color: #AAAAAA;" value="role" label="<?php print t('Role'); ?>"></optgroup>
                  <optgroup style="color: #AAAAAA;" value="organic_group" label="<?php print t('Organic Group'); ?>"></optgroup>
                </select>&nbsp;
                <select name="assign_by_variable" id="assign_by_variable" onchange="toggle_list_type('assign');">
<?php
                  foreach ($bys as $opt) {
?>
                    <option value="<?php print strtolower($opt['name']); ?>"><?php print $opt['label']; ?></option>
<?php
                  }
?>
                </select>&nbsp;
              </td>
            </tr>

            <tr>
              <td style="text-align: center;"><?php print t('Available'); ?></td>
              <td></td>
              <td style="text-align: center;"><?php print t('Assigned'); ?></td>
            </tr>


            <?php print theme('maestro_workflow_assign_notify_select_boxes', array('maestro_url' => $maestro_url, 'display' => '', 'row_class' => 'assign_row', 'type' => 'user', 'by_variable' => 'fixed', 'when' => '', 'options' => $uid_options, 'selected_options' => $selected_options[1][MaestroAssignmentTypes::USER][MaestroAssignmentBy::FIXED][1], 'name' => 'assign_ids_' . MaestroAssignmentTypes::USER . '_' . MaestroAssignmentBy::FIXED . '_1[]')); ?>
            <?php print theme('maestro_workflow_assign_notify_select_boxes', array('maestro_url' => $maestro_url, 'display' => 'none', 'row_class' => 'assign_row', 'type' => 'user', 'by_variable' => 'variable', 'when' => '', 'options' => $pv_options, 'selected_options' => $selected_options[1][MaestroAssignmentTypes::USER][MaestroAssignmentBy::VARIABLE][1], 'name' => 'assign_ids_' . MaestroAssignmentTypes::USER . '_' . MaestroAssignmentBy::VARIABLE . '_1[]')); ?>

            <?php print theme('maestro_workflow_assign_notify_select_boxes', array('maestro_url' => $maestro_url, 'display' => 'none', 'row_class' => 'assign_row', 'type' => 'role', 'by_variable' => 'fixed', 'when' => '', 'options' => $role_options, 'selected_options' => $selected_options[1][MaestroAssignmentTypes::ROLE][MaestroAssignmentBy::FIXED][1], 'name' => 'assign_ids_' . MaestroAssignmentTypes::ROLE . '_' . MaestroAssignmentBy::FIXED . '_1[]')); ?>
            <?php print theme('maestro_workflow_assign_notify_select_boxes', array('maestro_url' => $maestro_url, 'display' => 'none', 'row_class' => 'assign_row', 'type' => 'role', 'by_variable' => 'variable', 'when' => '', 'options' => $pv_options, 'selected_options' => $selected_options[1][MaestroAssignmentTypes::ROLE][MaestroAssignmentBy::VARIABLE][1], 'name' => 'assign_ids_' . MaestroAssignmentTypes::ROLE . '_' . MaestroAssignmentBy::VARIABLE . '_1[]')); ?>

            <?php print theme('maestro_workflow_assign_notify_select_boxes', array('maestro_url' => $maestro_url, 'display' => 'none', 'row_class' => 'assign_row', 'type' => 'group', 'by_variable' => 'fixed', 'when' => '', 'options' => $og_options, 'selected_options' => $selected_options[1][MaestroAssignmentTypes::GROUP][MaestroAssignmentBy::FIXED][1], 'name' => 'assign_ids_' . MaestroAssignmentTypes::GROUP . '_' . MaestroAssignmentBy::FIXED . '_1[]')); ?>
            <?php print theme('maestro_workflow_assign_notify_select_boxes', array('maestro_url' => $maestro_url, 'display' => 'none', 'row_class' => 'assign_row', 'type' => 'group', 'by_variable' => 'variable', 'when' => '', 'options' => $pv_options, 'selected_options' => $selected_options[1][MaestroAssignmentTypes::GROUP][MaestroAssignmentBy::VARIABLE][1], 'name' => 'assign_ids_' . MaestroAssignmentTypes::GROUP . '_' . MaestroAssignmentBy::VARIABLE . '_1[]')); ?>

            <tr>
              <td colspan="3"><?php print t('Assignment Summary:'); ?>&nbsp;<span id="assign_summary"></span></td>
            </tr>
          </table>
        </div>
<?php
  }

  if (array_key_exists('notification', $task_edit_tabs) && $task_edit_tabs['notification'] == 1) {
?>
        <div id="task_edit_notification" style="display: none;">
          <table>
            <tr>
              <td colspan="3">
                <?php print t('Notification Type:'); ?>&nbsp;
                <select name="notify_type" id="notify_type" onchange="toggle_list_type('notify');">
<?php
                  foreach ($types as $opt) {
?>
                    <option value="<?php print strtolower($opt['name']); ?>"><?php print $opt['label']; ?></option>
<?php
                    break;  //remove when once we add the role / group options
                  }
?>
                  <optgroup style="color: #AAAAAA;" value="role" label="<?php print t('Role'); ?>"></optgroup>
                  <optgroup style="color: #AAAAAA;" value="organic_group" label="<?php print t('Organic Group'); ?>"></optgroup>
                </select>&nbsp;
                <select name="notify_by_variable" id="notify_by_variable" onchange="toggle_list_type('notify');">
<?php
                  foreach ($bys as $opt) {
?>
                    <option value="<?php print strtolower($opt['name']); ?>"><?php print $opt['label']; ?></option>
<?php
                  }
?>
                </select>&nbsp;
                <select name="notify_when" id="notify_when" onchange="toggle_list_type('notify');">
<?php
                  foreach ($whens as $opt) {
?>
                    <option value="<?php print strtolower($opt['name']); ?>"><?php print $opt['label']; ?></option>
<?php
                  }
?>
                </select>&nbsp;
              </td>
            </tr>


            <!-- By User -->
            <?php print theme('maestro_workflow_assign_notify_select_boxes', array('maestro_url' => $maestro_url, 'display' => '', 'row_class' => 'notify_row', 'type' => 'user', 'by_variable' => 'fixed', 'when' => 'assignment', 'options' => $uid_options, 'selected_options' => $selected_options[2][MaestroAssignmentTypes::USER][MaestroAssignmentBy::FIXED][MaestroNotificationTypes::ASSIGNMENT], 'name' => 'notify_ids_' . MaestroAssignmentTypes::USER . '_' . MaestroAssignmentBy::FIXED . '_' . MaestroNotificationTypes::ASSIGNMENT . '[]')); ?>
            <?php print theme('maestro_workflow_assign_notify_select_boxes', array('maestro_url' => $maestro_url, 'display' => 'none', 'row_class' => 'notify_row', 'type' => 'user', 'by_variable' => 'variable', 'when' => 'assignment', 'options' => $pv_options, 'selected_options' => $selected_options[2][MaestroAssignmentTypes::USER][MaestroAssignmentBy::VARIABLE][MaestroNotificationTypes::ASSIGNMENT], 'name' => 'notify_ids_' . MaestroAssignmentTypes::USER . '_' . MaestroAssignmentBy::VARIABLE . '_' . MaestroNotificationTypes::ASSIGNMENT . '[]')); ?>

            <?php print theme('maestro_workflow_assign_notify_select_boxes', array('maestro_url' => $maestro_url, 'display' => 'none', 'row_class' => 'notify_row', 'type' => 'user', 'by_variable' => 'fixed', 'when' => 'completion', 'options' => $uid_options, 'selected_options' => $selected_options[2][MaestroAssignmentTypes::USER][MaestroAssignmentBy::FIXED][MaestroNotificationTypes::COMPLETION], 'name' => 'notify_ids_' . MaestroAssignmentTypes::USER . '_' . MaestroAssignmentBy::FIXED . '_' . MaestroNotificationTypes::COMPLETION . '[]')); ?>
            <?php print theme('maestro_workflow_assign_notify_select_boxes', array('maestro_url' => $maestro_url, 'display' => 'none', 'row_class' => 'notify_row', 'type' => 'user', 'by_variable' => 'variable', 'when' => 'completion', 'options' => $pv_options, 'selected_options' => $selected_options[2][MaestroAssignmentTypes::USER][MaestroAssignmentBy::VARIABLE][MaestroNotificationTypes::COMPLETION], 'name' => 'notify_ids_' . MaestroAssignmentTypes::USER . '_' . MaestroAssignmentBy::VARIABLE . '_' . MaestroNotificationTypes::COMPLETION . '[]')); ?>

            <?php print theme('maestro_workflow_assign_notify_select_boxes', array('maestro_url' => $maestro_url, 'display' => 'none', 'row_class' => 'notify_row', 'type' => 'user', 'by_variable' => 'fixed', 'when' => 'reminder', 'options' => $uid_options, 'selected_options' => $selected_options[2][MaestroAssignmentTypes::USER][MaestroAssignmentBy::FIXED][MaestroNotificationTypes::REMINDER], 'name' => 'notify_ids_' . MaestroAssignmentTypes::USER . '_' . MaestroAssignmentBy::FIXED . '_' . MaestroNotificationTypes::REMINDER . '[]')); ?>
            <?php print theme('maestro_workflow_assign_notify_select_boxes', array('maestro_url' => $maestro_url, 'display' => 'none', 'row_class' => 'notify_row', 'type' => 'user', 'by_variable' => 'variable', 'when' => 'reminder', 'options' => $pv_options, 'selected_options' => $selected_options[2][MaestroAssignmentTypes::USER][MaestroAssignmentBy::VARIABLE][MaestroNotificationTypes::REMINDER], 'name' => 'notify_ids_' . MaestroAssignmentTypes::USER . '_' . MaestroAssignmentBy::VARIABLE . '_' . MaestroNotificationTypes::REMINDER . '[]')); ?>

            <?php print theme('maestro_workflow_assign_notify_select_boxes', array('maestro_url' => $maestro_url, 'display' => 'none', 'row_class' => 'notify_row', 'type' => 'user', 'by_variable' => 'fixed', 'when' => 'escalation', 'options' => $uid_options, 'selected_options' => $selected_options[2][MaestroAssignmentTypes::USER][MaestroAssignmentBy::FIXED][MaestroNotificationTypes::ESCALATION], 'name' => 'notify_ids_' . MaestroAssignmentTypes::USER . '_' . MaestroAssignmentBy::FIXED . '_' . MaestroNotificationTypes::ESCALATION . '[]')); ?>
            <?php print theme('maestro_workflow_assign_notify_select_boxes', array('maestro_url' => $maestro_url, 'display' => 'none', 'row_class' => 'notify_row', 'type' => 'user', 'by_variable' => 'variable', 'when' => 'escalation', 'options' => $pv_options, 'selected_options' => $selected_options[2][MaestroAssignmentTypes::USER][MaestroAssignmentBy::VARIABLE][MaestroNotificationTypes::ESCALATION], 'name' => 'notify_ids_' . MaestroAssignmentTypes::USER . '_' . MaestroAssignmentBy::VARIABLE . '_' . MaestroNotificationTypes::ESCALATION . '[]')); ?>

            <!-- By Role -->
            <?php print theme('maestro_workflow_assign_notify_select_boxes', array('maestro_url' => $maestro_url, 'display' => 'none', 'row_class' => 'notify_row', 'type' => 'role', 'by_variable' => 'fixed', 'when' => 'assignment', 'options' => $role_options, 'selected_options' => $selected_options[2][MaestroAssignmentTypes::ROLE][MaestroAssignmentBy::FIXED][MaestroNotificationTypes::ASSIGNMENT], 'name' => 'notify_ids_' . MaestroAssignmentTypes::ROLE . '_' . MaestroAssignmentBy::FIXED . '_' . MaestroNotificationTypes::ASSIGNMENT . '[]')); ?>
            <?php print theme('maestro_workflow_assign_notify_select_boxes', array('maestro_url' => $maestro_url, 'display' => 'none', 'row_class' => 'notify_row', 'type' => 'role', 'by_variable' => 'variable', 'when' => 'assignment', 'options' => $pv_options, 'selected_options' => $selected_options[2][MaestroAssignmentTypes::ROLE][MaestroAssignmentBy::VARIABLE][MaestroNotificationTypes::ASSIGNMENT], 'name' => 'notify_ids_' . MaestroAssignmentTypes::ROLE . '_' . MaestroAssignmentBy::VARIABLE . '_' . MaestroNotificationTypes::ASSIGNMENT . '[]')); ?>

            <?php print theme('maestro_workflow_assign_notify_select_boxes', array('maestro_url' => $maestro_url, 'display' => 'none', 'row_class' => 'notify_row', 'type' => 'role', 'by_variable' => 'fixed', 'when' => 'completion', 'options' => $role_options, 'selected_options' => $selected_options[2][MaestroAssignmentTypes::ROLE][MaestroAssignmentBy::FIXED][MaestroNotificationTypes::COMPLETION], 'name' => 'notify_ids_' . MaestroAssignmentTypes::ROLE . '_' . MaestroAssignmentBy::FIXED . '_' . MaestroNotificationTypes::COMPLETION . '[]')); ?>
            <?php print theme('maestro_workflow_assign_notify_select_boxes', array('maestro_url' => $maestro_url, 'display' => 'none', 'row_class' => 'notify_row', 'type' => 'role', 'by_variable' => 'variable', 'when' => 'completion', 'options' => $pv_options, 'selected_options' => $selected_options[2][MaestroAssignmentTypes::ROLE][MaestroAssignmentBy::VARIABLE][MaestroNotificationTypes::COMPLETION], 'name' => 'notify_ids_' . MaestroAssignmentTypes::ROLE . '_' . MaestroAssignmentBy::VARIABLE . '_' . MaestroNotificationTypes::COMPLETION . '[]')); ?>

            <?php print theme('maestro_workflow_assign_notify_select_boxes', array('maestro_url' => $maestro_url, 'display' => 'none', 'row_class' => 'notify_row', 'type' => 'role', 'by_variable' => 'fixed', 'when' => 'reminder', 'options' => $role_options, 'selected_options' => $selected_options[2][MaestroAssignmentTypes::ROLE][MaestroAssignmentBy::FIXED][MaestroNotificationTypes::REMINDER], 'name' => 'notify_ids_' . MaestroAssignmentTypes::ROLE . '_' . MaestroAssignmentBy::FIXED . '_' . MaestroNotificationTypes::REMINDER . '[]')); ?>
            <?php print theme('maestro_workflow_assign_notify_select_boxes', array('maestro_url' => $maestro_url, 'display' => 'none', 'row_class' => 'notify_row', 'type' => 'role', 'by_variable' => 'variable', 'when' => 'reminder', 'options' => $pv_options, 'selected_options' => $selected_options[2][MaestroAssignmentTypes::ROLE][MaestroAssignmentBy::VARIABLE][MaestroNotificationTypes::REMINDER], 'name' => 'notify_ids_' . MaestroAssignmentTypes::ROLE . '_' . MaestroAssignmentBy::VARIABLE . '_' . MaestroNotificationTypes::REMINDER . '[]')); ?>

            <?php print theme('maestro_workflow_assign_notify_select_boxes', array('maestro_url' => $maestro_url, 'display' => 'none', 'row_class' => 'notify_row', 'type' => 'role', 'by_variable' => 'fixed', 'when' => 'escalation', 'options' => $role_options, 'selected_options' => $selected_options[2][MaestroAssignmentTypes::ROLE][MaestroAssignmentBy::FIXED][MaestroNotificationTypes::ESCALATION], 'name' => 'notify_ids_' . MaestroAssignmentTypes::ROLE . '_' . MaestroAssignmentBy::FIXED . '_' . MaestroNotificationTypes::ESCALATION . '[]')); ?>
            <?php print theme('maestro_workflow_assign_notify_select_boxes', array('maestro_url' => $maestro_url, 'display' => 'none', 'row_class' => 'notify_row', 'type' => 'role', 'by_variable' => 'variable', 'when' => 'escalation', 'options' => $pv_options, 'selected_options' => $selected_options[2][MaestroAssignmentTypes::ROLE][MaestroAssignmentBy::VARIABLE][MaestroNotificationTypes::ESCALATION], 'name' => 'notify_ids_' . MaestroAssignmentTypes::ROLE . '_' . MaestroAssignmentBy::VARIABLE . '_' . MaestroNotificationTypes::ESCALATION . '[]')); ?>

            <!-- By OG -->
            <?php print theme('maestro_workflow_assign_notify_select_boxes', array('maestro_url' => $maestro_url, 'display' => 'none', 'row_class' => 'notify_row', 'type' => 'group', 'by_variable' => 'fixed', 'when' => 'assignment', 'options' => $og_options, 'selected_options' => $selected_options[2][MaestroAssignmentTypes::GROUP][MaestroAssignmentBy::FIXED][MaestroNotificationTypes::ASSIGNMENT], 'name' => 'notify_ids_' . MaestroAssignmentTypes::GROUP . '_' . MaestroAssignmentBy::FIXED . '_' . MaestroNotificationTypes::ASSIGNMENT . '[]')); ?>
            <?php print theme('maestro_workflow_assign_notify_select_boxes', array('maestro_url' => $maestro_url, 'display' => 'none', 'row_class' => 'notify_row', 'type' => 'group', 'by_variable' => 'variable', 'when' => 'assignment', 'options' => $pv_options, 'selected_options' => $selected_options[2][MaestroAssignmentTypes::GROUP][MaestroAssignmentBy::VARIABLE][MaestroNotificationTypes::ASSIGNMENT], 'name' => 'notify_ids_' . MaestroAssignmentTypes::GROUP . '_' . MaestroAssignmentBy::VARIABLE . '_' . MaestroNotificationTypes::ASSIGNMENT . '[]')); ?>

            <?php print theme('maestro_workflow_assign_notify_select_boxes', array('maestro_url' => $maestro_url, 'display' => 'none', 'row_class' => 'notify_row', 'type' => 'group', 'by_variable' => 'fixed', 'when' => 'completion', 'options' => $og_options, 'selected_options' => $selected_options[2][MaestroAssignmentTypes::GROUP][MaestroAssignmentBy::FIXED][MaestroNotificationTypes::COMPLETION], 'name' => 'notify_ids_' . MaestroAssignmentTypes::GROUP . '_' . MaestroAssignmentBy::FIXED . '_' . MaestroNotificationTypes::COMPLETION . '[]')); ?>
            <?php print theme('maestro_workflow_assign_notify_select_boxes', array('maestro_url' => $maestro_url, 'display' => 'none', 'row_class' => 'notify_row', 'type' => 'group', 'by_variable' => 'variable', 'when' => 'completion', 'options' => $pv_options, 'selected_options' => $selected_options[2][MaestroAssignmentTypes::GROUP][MaestroAssignmentBy::VARIABLE][MaestroNotificationTypes::COMPLETION], 'name' => 'notify_ids_' . MaestroAssignmentTypes::GROUP . '_' . MaestroAssignmentBy::VARIABLE . '_' . MaestroNotificationTypes::COMPLETION . '[]')); ?>

            <?php print theme('maestro_workflow_assign_notify_select_boxes', array('maestro_url' => $maestro_url, 'display' => 'none', 'row_class' => 'notify_row', 'type' => 'group', 'by_variable' => 'fixed', 'when' => 'reminder', 'options' => $og_options, 'selected_options' => $selected_options[2][MaestroAssignmentTypes::GROUP][MaestroAssignmentBy::FIXED][MaestroNotificationTypes::REMINDER], 'name' => 'notify_ids_' . MaestroAssignmentTypes::GROUP . '_' . MaestroAssignmentBy::FIXED . '_' . MaestroNotificationTypes::REMINDER . '[]')); ?>
            <?php print theme('maestro_workflow_assign_notify_select_boxes', array('maestro_url' => $maestro_url, 'display' => 'none', 'row_class' => 'notify_row', 'type' => 'group', 'by_variable' => 'variable', 'when' => 'reminder', 'options' => $pv_options, 'selected_options' => $selected_options[2][MaestroAssignmentTypes::GROUP][MaestroAssignmentBy::VARIABLE][MaestroNotificationTypes::REMINDER], 'name' => 'notify_ids_' . MaestroAssignmentTypes::GROUP . '_' . MaestroAssignmentBy::VARIABLE . '_' . MaestroNotificationTypes::REMINDER . '[]')); ?>

            <?php print theme('maestro_workflow_assign_notify_select_boxes', array('maestro_url' => $maestro_url, 'display' => 'none', 'row_class' => 'notify_row', 'type' => 'group', 'by_variable' => 'fixed', 'when' => 'escalation', 'options' => $og_options, 'selected_options' => $selected_options[2][MaestroAssignmentTypes::GROUP][MaestroAssignmentBy::FIXED][MaestroNotificationTypes::ESCALATION], 'name' => 'notify_ids_' . MaestroAssignmentTypes::GROUP . '_' . MaestroAssignmentBy::FIXED . '_' . MaestroNotificationTypes::ESCALATION . '[]')); ?>
            <?php print theme('maestro_workflow_assign_notify_select_boxes', array('maestro_url' => $maestro_url, 'display' => 'none', 'row_class' => 'notify_row', 'type' => 'group', 'by_variable' => 'variable', 'when' => 'escalation', 'options' => $pv_options, 'selected_options' => $selected_options[2][MaestroAssignmentTypes::GROUP][MaestroAssignmentBy::VARIABLE][MaestroNotificationTypes::ESCALATION], 'name' => 'notify_ids_' . MaestroAssignmentTypes::GROUP . '_' . MaestroAssignmentBy::VARIABLE . '_' . MaestroNotificationTypes::ESCALATION . '[]')); ?>

            <tr>
              <td colspan="3" style="text-align: center;">
                <i><?php print t('(leave subject and/or message blank for default)'); ?></i>
              </td>
            </tr>
            <tr class="notify_row user role group fixed variable assignment">
              <td colspan="3">
                <table width="100%">
                  <tr>
                    <td><?php print t('Subject:'); ?></td>
                    <td style="width: 90%;"><input type="text" name="pre_notify_subject" value="<?php print $vars->pre_notify_subject; ?>"></td>
                  </tr>
                  <tr>
                    <td style="vertical-align: top;"><?php print t('Message:'); ?></td>
                    <td style="width: 90%;"><textarea style="width: 100%;" rows="4" name="pre_notify_message"><?php print $vars->pre_notify_message; ?></textarea></td>
                  </tr>
                </table>
              </td>
            </tr>

            <tr class="notify_row user role group fixed variable completion" style="display: none;">
              <td colspan="3">
                <table width="100%">
                  <tr>
                    <td><?php print t('Subject:'); ?></td>
                    <td style="width: 90%;"><input type="text" name="post_notify_subject" value="<?php print $vars->post_notify_subject; ?>"></td>
                  </tr>
                  <tr>
                    <td style="vertical-align: top;"><?php print t('Message:'); ?></td>
                    <td style="width: 90%;"><textarea style="width: 100%;" rows="4" name="post_notify_message"><?php print $vars->post_notify_message; ?></textarea></td>
                  </tr>
                </table>
              </td>
            </tr>

            <tr class="notify_row user role group fixed variable reminder" style="display: none;">
              <td colspan="3">
                <table width="100%">
                  <tr>
                    <td nowrap><?php print t('Subject:'); ?></td>
                    <td style="width: 90%;"><input type="text" name="reminder_subject" value="<?php print $vars->reminder_subject; ?>"></td>
                  </tr>
                  <tr>
                    <td nowrap style="vertical-align: top;"><?php print t('Message:'); ?></td>
                    <td style="width: 90%;"><textarea style="width: 100%;" rows="4" name="reminder_message"><?php print $vars->reminder_message; ?></textarea></td>
                  </tr>
                  <tr>
                    <td nowrap><?php print t('Reminder Interval (days):'); ?></td>
                    <td><input type="text" style="width: 30px;" name="reminder_interval" value="<?php print $vars->reminder_interval; ?>"></td>
                  </tr>
                </table>
              </td>
            </tr>

            <tr class="notify_row user role group fixed variable escalation" style="display: none;">
              <td colspan="3">
                <table width="100%">
                  <tr>
                    <td nowrap><?php print t('Subject:'); ?></td>
                    <td style="width: 90%;"><input type="text" name="escalation_subject" value="<?php print $vars->escalation_subject; ?>"></td>
                  </tr>
                  <tr>
                    <td nowrap style="vertical-align: top;"><?php print t('Message:'); ?></td>
                    <td style="width: 90%;"><textarea style="width: 100%;" rows="4" name="escalation_message"><?php print $vars->escalation_message; ?></textarea></td>
                  </tr>
                  <tr>
                    <td nowrap><?php print t('Escalate After (days):'); ?></td>
                    <td><input type="text" style="width: 30px;" name="escalation_interval" value="<?php print $vars->escalation_interval; ?>"></td>
                  </tr>
                </table>
              </td>
            </tr>

            <tr>
              <td colspan="3">
                <b><?php print t('On Assign:'); ?></b>&nbsp;<span id="notify_assign_summary"></span>&nbsp;&nbsp;&nbsp;
                <b><?php print t('On Complete:'); ?></b>&nbsp;<span id="notify_complete_summary"></span>&nbsp;&nbsp;&nbsp;
                <b><?php print t('On Remind:'); ?></b>&nbsp;<span id="notify_remind_summary"></span>&nbsp;&nbsp;&nbsp;
                <b><?php print t('On Escalate:'); ?></b>&nbsp;<span id="notify_escalate_summary"></span>
              </td>
            </tr>

          </table>
        </div>
<?php
  }
?>
        <div class="maestro_task_edit_save_div"><input class="form-submit" type="submit" value="<?php print t('Save'); ?>"></div>

      </form>
    </div></div></div></div></div></div></div></div>
  </div>
</div>
