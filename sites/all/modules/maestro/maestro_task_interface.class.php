<?php
// $Id: maestro_task_interface.class.php,v 1.61 2010/12/23 13:44:02 randy Exp $

/**
 * @file
 * maestro_task_interface.class.php
 */

include_once './' . drupal_get_path('module', 'maestro') . '/maestro_notification.class.php';

abstract class MaestroTaskInterface {
  protected $_task_id;
  protected $_template_id;
  protected $_task_type;
  protected $_is_interactive;
  protected $_task_data;  //used when fetching task information
  protected $_task_edit_tabs;
  protected $_taskname;

  function __construct($task_id=0, $template_id=0) {
    $this->_task_id = $task_id;
    $this->_task_data = NULL;
    $this->_task_process_data = NULL;

    if ($template_id == 0 && $task_id > 0) {
      $res = db_select('maestro_template_data', 'a');
      $res->fields('a', array('template_id', 'taskname'));
      $res->condition('a.id', $task_id, '=');
      $rec = current($res->execute()->fetchAll());

      $this->_taskname = $rec->taskname;
      $this->_template_id = intval(@($rec->template_id));
    }
    else {
      $this->_template_id = $template_id;
    }

    if ($this->_is_interactive == MaestroInteractiveFlag::IS_INTERACTIVE) {
      $this->_task_edit_tabs = array('assignment' => 1, 'notification' => 1);
    }
    else {
      $this->_task_edit_tabs = array();
    }
  }

  function get_task_id(){
    return $this->_task_id;
  }

  protected function _fetchTaskInformation() {
    $res = db_select('maestro_template_data', 'a');
    $res->fields('a', array('task_data'));
    $res->condition('a.id', $this->_task_id, '=');
    $td_rec = current($res->execute()->fetchAll());
    $td_rec->task_data = unserialize($td_rec->task_data);

    $this->_task_data = $td_rec;
  }

  //create task will insert the shell record of the task, and then the child class will handle the edit.
  function create() {
    $rec = new stdClass();
    $rec->template_id = $this->_template_id;
    $rec->taskname = t('New Task');
    $this->_taskname = $rec->taskname;
    $rec->task_class_name = 'MaestroTaskType' . $this->_task_type;
    $rec->is_interactive = $this->_is_interactive;
    if ($this->_is_interactive) {
      $rec->assigned_by_variable = 1;
      $rec->show_in_detail = 1;
    }
    $rec->first_task = 0;
    $rec->offset_left = $_POST['offset_left'];
    $rec->offset_top = $_POST['offset_top'];
    drupal_write_record('maestro_template_data', $rec);
    $this->_task_id = $rec->id;

    return array('html' => $this->displayTask());
  }

  //deletes the task
  function destroy() {
    $res = db_select('maestro_queue', 'a');
    $res->fields('a', array('id'));
    $res->condition('template_data_id', $this->_task_id, '=');
    $rec = current($res->execute()->fetchAll());

    if ((array_key_exists('confirm_delete', $_POST) && $_POST['confirm_delete'] == 1) || $rec == '') {
      db_query("DELETE FROM {maestro_template_data} WHERE id=:tdid", array(':tdid' => $this->_task_id));
      db_query("DELETE FROM {maestro_template_assignment} WHERE template_data_id=:tdid", array(':tdid' => $this->_task_id));

      //RK -- This query has been simplified to remove the db_query replacement facility as it fails in SQL Server
      $tdID = intval($this->_task_id);
      db_query("DELETE FROM {maestro_template_data_next_step} WHERE template_data_to={$tdID} OR template_data_to_false={$tdID} OR template_data_from={$tdID}");

      db_query("DELETE FROM {maestro_queue} WHERE template_data_id=:tdid", array(':tdid' => $this->_task_id));
      $retval = '';
      $success = 1;
    }
    else {
      $retval  = t("There are still outstanding tasks in the queue that depend on this task! Deleting this task will delete the queue records too.");
      $retval .= '<br>';
      $retval .= t("Continue with delete?");
      $retval .= '&nbsp;&nbsp;&nbsp;';
      $retval .= "<input type=\"button\" value=\"" . t('Yes') . "\" onclick=\"set_tool_tip(''); ";
      $retval .= "enable_ajax_indicator(); (function($) {\$.ajax({
        type: 'POST',
        url: ajax_url + 'MaestroTaskInterface{$this->_task_type}/{$this->_task_id}/0/destroy/',
        cache: false,
        data: {confirm_delete: 1},
        dataType: 'json',
        success: delete_task,
        error: editor_ajax_error
      }); })(jQuery);\">";
      $retval .= "<input type=\"button\" value=\"" . t('No') . "\" onclick=\"set_tool_tip('');\">";
      $success = 0;
    }

    return array('message' => $retval, 'success' => $success, 'task_id' => $this->_task_id);
  }

  function displayTask() {
    $res = db_select('maestro_template_data', 'a');
    $res->fields('a', array('id', 'taskname', 'task_class_name', 'is_interactive', 'offset_left', 'offset_top'));
    $res->condition('a.id', $this->_task_id, '=');
    $rec = current($res->execute()->fetchAll());

    $task_type = substr($rec->task_class_name, 15);
    $task_class = 'MaestroTaskInterface' . $task_type;

    return theme('maestro_workflow_task_frame', array('rec' => $rec, 'ti' => $this, 'task_class' => $task_class));
  }

  function edit() {
    global $base_url;
    $maestro_url = $base_url . '/' . drupal_get_path('module', 'maestro');

    $res = db_select('maestro_template_data', 'a');
    $res->fields('a', array('task_class_name', 'taskname', 'regenerate', 'regen_all_live_tasks', 'show_in_detail', 'pre_notify_subject', 'pre_notify_message', 'post_notify_subject', 'post_notify_message', 'reminder_subject', 'reminder_message', 'escalation_subject', 'escalation_message', 'reminder_interval', 'escalation_interval'));
    $res->condition('a.id', $this->_task_id, '=');
    $vars = current($res->execute()->fetchAll());

    $task_type = substr($vars->task_class_name, 15);
    $task_class = 'MaestroTaskInterface' . $task_type;

    $uid_options = array();
    $pv_options = array();
    $role_options = array();
    $og_options = array();

    if ((array_key_exists('assignment', $this->_task_edit_tabs) && $this->_task_edit_tabs['assignment'] == 1) ||
        (array_key_exists('notification', $this->_task_edit_tabs) && $this->_task_edit_tabs['notification'] == 1)) {
      $res = db_query("SELECT uid AS id, name FROM {users} WHERE uid > 0");
      foreach ($res as $rec) {
        $uid_options[$rec->id] = $rec->name;
      }

      $res = db_query("SELECT id, variable_name AS name FROM {maestro_template_variables} WHERE template_id=:tid", array(':tid' => $this->_template_id));
      foreach ($res as $rec) {
        $pv_options[$rec->id] = $rec->name;
      }

      $res = db_query("SELECT rid AS id, name FROM {role}");
      foreach ($res as $rec) {
        $role_options[$rec->id] = $rec->name;
      }

      //TODO: add $og_option builder logic here
    }

    //initialize the selected array
    $types = MaestroAssignmentTypes::getStatusLabel();
    $bys = MaestroAssignmentBy::getStatusLabel();
    $whens = MaestroNotificationTypes::getStatusLabel();

    $selected_options = array();
    for ($i = 1; $i <= 2; $i++) {        //1: assignment / 2: notification
      $selected_options[$i] = array();

      foreach($types as $j => $opt) {
        $selected_options[$i][$j] = array();

        foreach($bys as $k => $opt) {
          $selected_options[$i][$j][$k] = array();

            foreach($whens as $l => $opt) {
            $selected_options[$i][$j][$k][$l] = array();
          }
        }
      }
    }

    if (array_key_exists('assignment', $this->_task_edit_tabs) && $this->_task_edit_tabs['assignment'] == 1) {
      $res = db_query("SELECT assign_type, assign_by, assign_id FROM {maestro_template_assignment} WHERE template_data_id=:tdid", array(':tdid' => $this->_task_id));
      foreach ($res as $rec) {
        $selected_options[1][$rec->assign_type][$rec->assign_by][1][] = $rec->assign_id;
      }
    }

    if (array_key_exists('notification', $this->_task_edit_tabs) && $this->_task_edit_tabs['notification'] == 1) {
      $res = db_query("SELECT notify_type, notify_by, notify_when, notify_id FROM {maestro_template_notification} WHERE template_data_id=:tdid", array(':tdid' => $this->_task_id));
      foreach ($res as $rec) {
        $selected_options[2][$rec->notify_type][$rec->notify_by][$rec->notify_when][] = $rec->notify_id;
      }
    }

    $optional_parms = array();
    if (array_key_exists('optional', $this->_task_edit_tabs) && $this->_task_edit_tabs['optional'] == 1) {
      $res = db_select('maestro_template_data', 'a');
      $res->fields('a', array('task_data'));
      $res->condition('a.id', $this->_task_id, '=');
      $rec = current($res->execute()->fetchAll());
      $rec->task_data = unserialize($rec->task_data);

      if (is_array($rec->task_data) && array_key_exists('optional_parm', $rec->task_data)) {
        foreach ($rec->task_data['optional_parm'] as $var_name => $var_value) {
          $optional_parms[$var_name] = $var_value;
        }
      }
    }

    return array('html' => theme('maestro_workflow_edit_tasks_frame', array('tdid' => $this->_task_id, 'tid' => $this->_template_id, 'form_content' => $this->getEditFormContent(), 'maestro_url' => $maestro_url, 'pv_options' => $pv_options, 'uid_options' => $uid_options, 'role_options' => $role_options, 'og_options' => $og_options, 'selected_options' => $selected_options, 'task_class' => $task_class, 'vars' => $vars, 'task_edit_tabs' => $this->_task_edit_tabs, 'optional_parms' => $optional_parms, 'types' => MaestroAssignmentTypes::getStatusLabel(), 'bys' => MaestroAssignmentBy::getStatusLabel(), 'whens' => MaestroNotificationTypes::getStatusLabel())));
  }

  function save() {
    $res = db_select('maestro_template_data', 'a');
    $res->fields('a', array('id', 'task_data'));
    $res->condition('a.id', $this->_task_id, '=');
    $rec = current($res->execute()->fetchAll());

    $types = MaestroAssignmentTypes::getStatusLabel();
    $bys = MaestroAssignmentBy::getStatusLabel();
    $whens = MaestroNotificationTypes::getStatusLabel();

    db_query("DELETE FROM {maestro_template_assignment} WHERE template_data_id=:tdid", array(':tdid' => $this->_task_id));
    if (array_key_exists('assignment', $this->_task_edit_tabs) && $this->_task_edit_tabs['assignment'] == 1) {
      foreach ($types as $type => $opt) {
        foreach ($bys as $by_var => $opt) {
          if (array_key_exists("assign_ids_{$type}_{$by_var}_1", $_POST)) {
            foreach ($_POST["assign_ids_{$type}_{$by_var}_1"] as $id) {
              db_query("INSERT INTO {maestro_template_assignment} (template_data_id, assign_type, assign_by, assign_id) VALUES (:tdid, :type, :by_var, :id)", array(':tdid' => $this->_task_id, ':type' => $type, ':by_var' => $by_var, ':id' => $id));
            }
          }
        }
      }
    }

    db_query("DELETE FROM {maestro_template_notification} WHERE template_data_id=:tdid", array(':tdid' => $this->_task_id));
    if (array_key_exists('notification', $this->_task_edit_tabs) && $this->_task_edit_tabs['notification'] == 1) {
      foreach ($types as $type => $opt) {
        foreach ($bys as $by_var => $opt) {
          foreach ($whens as $when => $opt) {
            if (array_key_exists("notify_ids_{$type}_{$by_var}_{$when}", $_POST)) {
              foreach ($_POST["notify_ids_{$type}_{$by_var}_{$when}"] as $id) {
                db_query("INSERT INTO {maestro_template_notification} (template_data_id, notify_type, notify_by, notify_when, notify_id) VALUES (:tdid, :type, :by_var, :when, :id)", array(':tdid' => $this->_task_id, ':type' => $type, ':by_var' => $by_var, 'when' => $when, ':id' => $id));
              }
            }
          }
        }
      }

      $rec->pre_notify_subject = $_POST['pre_notify_subject'];
      $rec->pre_notify_message = $_POST['pre_notify_message'];
      $rec->post_notify_subject = $_POST['post_notify_subject'];
      $rec->post_notify_message = $_POST['post_notify_message'];
      $rec->reminder_subject = $_POST['reminder_subject'];
      $rec->reminder_message = $_POST['reminder_message'];
      $rec->escalation_subject = $_POST['escalation_subject'];
      $rec->escalation_message = $_POST['escalation_message'];
      $rec->reminder_interval = $_POST['reminder_interval'];
      $rec->escalation_interval = $_POST['escalation_interval'];
    }

    if (array_key_exists('optional', $this->_task_edit_tabs) && $this->_task_edit_tabs['optional'] == 1) {
      $optional_parms = array();

      if (array_key_exists('op_var_names', $_POST)) {
        foreach ($_POST['op_var_names'] as $key => $var_name) {
          if ($var_name != '') {
            $optional_parms[$var_name] = $_POST['op_var_values'][$key];
          }
        }
      }

      $rec->task_data = unserialize($rec->task_data);
      $rec->task_data['optional_parm'] = $optional_parms;
      $rec->task_data = serialize($rec->task_data);
    }

    $rec->taskname = $_POST['taskname'];
    if (array_key_exists('regen', $_POST)) {
      $rec->regenerate = $_POST['regen'];
    }
    if (array_key_exists('regenall', $_POST)) {
      $rec->regen_all_live_tasks = $_POST['regenall'];
    }
    if (array_key_exists('showindetail', $_POST)) {
      $rec->show_in_detail = $_POST['showindetail'];
    }

    drupal_write_record('maestro_template_data', $rec, array('id'));

    return array('task_id' => $this->_task_id);
  }

  //handles the update for the drag and drop
  function move() {
    $offset_left = intval($_POST['offset_left']);
    $offset_top = intval($_POST['offset_top']);

    if ($offset_left < 0) $offset_left = 0;
    if ($offset_top < 0) $offset_top = 0;

    db_query("UPDATE {maestro_template_data} SET offset_left=:ofl, offset_top=:ofr WHERE id=:tdid", array(':ofl' => $offset_left, ':ofr' => $offset_top, ':tdid' => $this->_task_id));
  }

  //handles the update when adding a line (insert the next step record)
  function drawLine() {
    $res = db_select('maestro_template_data_next_step', 'a');
    $res->fields('a', array('id'));

    $cond1 = db_or()->condition('a.template_data_to', $_POST['line_to'], '=')->condition('a.template_data_to_false', $_POST['line_to'], '=');
    $cond1fin = db_and()->condition('a.template_data_from', $this->_task_id, '=')->condition($cond1);

    $cond2 = db_or()->condition('a.template_data_to', $this->_task_id, '=')->condition('a.template_data_to_false', $this->_task_id, '=');
    $cond2fin = db_and()->condition('a.template_data_from', $_POST['line_to'], '=')->condition($cond2);

    $cond = db_or()->condition($cond1fin)->condition($cond2fin);

    $res->condition($cond);
    $rec = current($res->execute()->fetchAll());

    if ($rec == '') {
      $rec = new stdClass();
      $rec->template_data_from = $this->_task_id;
      $rec->template_data_to = $_POST['line_to'];
      $rec->template_data_to_false = 0;
      drupal_write_record('maestro_template_data_next_step', $rec);
    }
    else {
      //perhaps return a simple true/false with error message if the select failed
    }
  }

  //in theory only the if task will use this method
  function drawLineFalse() {
    $res = db_select('maestro_template_data_next_step', 'a');
    $res->fields('a', array('id'));

    $cond1 = db_or()->condition('a.template_data_to', $_POST['line_to'], '=')->condition('a.template_data_to_false', $_POST['line_to'], '=');
    $cond1fin = db_and()->condition('a.template_data_from', $this->_task_id, '=')->condition($cond1);

    $cond2 = db_or()->condition('a.template_data_to', $this->_task_id, '=')->condition('a.template_data_to_false', $this->_task_id, '=');
    $cond2fin = db_and()->condition('a.template_data_from', $_POST['line_to'], '=')->condition($cond2);

    $cond = db_or()->condition($cond1fin)->condition($cond2fin);

    $res->condition($cond);
    $rec = current($res->execute()->fetchAll());

    if ($rec == '') {
      $rec = new stdClass();
      $rec->template_data_from = $this->_task_id;
      $rec->template_data_to = 0;
      $rec->template_data_to_false = $_POST['line_to'];
      drupal_write_record('maestro_template_data_next_step', $rec);
    }
    else {
      //perhaps return a simple true/false with error message if the select failed
    }
  }

  //remove any next step records pertaining to this task
  function clearAdjacentLines() {
    //RK -- had to change the logic on this delete as PDO for SQL Server was failing for some reason even though the
    //resulting query was 100% correct.
    $taskid=intval($this->_task_id);
    db_query("DELETE FROM {maestro_template_data_next_step} WHERE template_data_from={$taskid} OR template_data_to={$taskid} OR template_data_to_false={$taskid}");
  }

  //returns an array of options for when the user right-clicks the task
  function getContextMenu() {
    $draw_line_msg = t('Select a task to draw the line to.');
    $options = array (
      'draw_line' => array(
        'label' => t('Draw Line'),
        'js' => "draw_status = 1; draw_type = 1; line_start = document.getElementById('task{$this->_task_id}'); set_tool_tip('$draw_line_msg');\n"
      ),
      'clear_lines' => array(
        'label' => t('Clear Adjacent Lines'),
        'js' => "clear_task_lines(document.getElementById('task{$this->_task_id}'));\n"
      ),
      'edit_task' => array(
        'label' => t('Edit Task'),
        'js' => "enable_ajax_indicator(); \$.ajax({
          type: 'POST',
          url: ajax_url + 'MaestroTaskInterface{$this->_task_type}/{$this->_task_id}/0/edit/',
          cache: false,
          dataType: 'json',
          success: display_task_panel,
          error: editor_ajax_error
        });"
      ),
      'delete_task' => array(
        'label' => t('Delete Task'),
        'js' => "enable_ajax_indicator(); \$.ajax({
          type: 'POST',
          url: ajax_url + 'MaestroTaskInterface{$this->_task_type}/{$this->_task_id}/0/destroy/',
          cache: false,
          success: delete_task,
          dataType: 'json',
          error: editor_ajax_error
        });\n"
      )
    );

    return $options;
  }

  function getContextMenuHTML() {
    $options = $this->getContextMenu();
    $html = "<div id=\"maestro_task{$this->_task_id}_context_menu\" class=\"maestro_context_menu\"><ul>\n";

    foreach ($options as $key => $option) {
      $option = t($option);
      $html .= "<li style=\"white-space: nowrap;\" id=\"$key\">{$option['label']}</li>\n";
    }
    $html .= "</ul></div>\n";

    return $html;
  }

  function getContextMenuJS() {
    $options = $this->getContextMenu();
    $js  = "(function ($) {\n";
    $js .= "\$('#task{$this->_task_id}').contextMenu('maestro_task{$this->_task_id}_context_menu', {\n";
    $js .= "menuStyle: {\n";
    $js .= "width: 175,\n";
    $js .= "fontSize: 12,\n";
    $js .= "},\n";

    $js .= "itemStyle: {\n";
    $js .= "padding: 0,\n";
    $js .= "paddingLeft: 10,\n";
    $js .= "},\n";

    $js .= "bindings: {\n";

    foreach ($options as $key => $option) {
      $js .= "'$key': function(t) {\n";
      $js .= $option['js'];
      $js .= "},\n";
    }

    $js .= "}\n";
    $js .= "});\n";
    $js .= "})(jQuery);\n";

    return $js;
  }

  function getAssignmentDisplay() {
    $display = t('Assigned to:') . ' ';

    $query = db_select('maestro_template_assignment', 'a');
    $query->leftJoin('maestro_template_variables', 'b', 'a.assign_id=b.id');
    $query->leftJoin('users', 'c', 'a.assign_id=c.uid');
    $query->leftJoin('role', 'd', 'a.assign_id=d.rid');
    $query->fields('a', array('assign_id', 'assign_type', 'assign_by'));
    $query->fields('b', array('variable_name'));
    $query->addField('c', 'name', 'username');
    $query->addField('d', 'name', 'rolename');
    $query->condition('a.template_data_id', $this->_task_id, '=');

    $res = $query->execute();

    $assigned_list = '';
    foreach ($res as $rec) {
      if ($assigned_list != '') {
        $assigned_list .= ', ';
      }

      if ($rec->assign_by == MaestroAssignmentBy::FIXED) {
        switch ($rec->assign_type) {
        case MaestroAssignmentTypes::USER:
          $assigned_list .= $rec->username;
          break;

        case MaestroAssignmentTypes::ROLE:
          $assigned_list .= $rec->rolename;
          break;

        case MaestroAssignmentTypes::GROUP:
          //@TODO: add support for organic groups
          break;
        }
      }
      else {
        $assigned_list .= $rec->variable_name;
      }
    }

    if ($assigned_list == '') {
      $assigned_list = '<i>' . t('nobody') . '</i>';
    }
    $display .= $assigned_list;

    return $display;
  }

  function getHandlerOptions() {
    $all_handler_options = cache_get('maestro_handler_options');
    $handler_options = array();
    if (array_key_exists('MaestroTaskType' . $this->_task_type, $all_handler_options->data)) {
      $handler_options = $all_handler_options->data['MaestroTaskType' . $this->_task_type];
    }

    return $handler_options;
  }

  function setCanvasHeight() {
    $rec = new stdClass();
    $rec->id = $this->_template_id;
    $rec->canvas_height = $_POST['height'];
    drupal_write_record('maestro_template', $rec, array('id'));
  }

  /*
   * Receive an incoming serialized data chunk.. return it to the callee serialized.
   */
  function modulateExportTaskData($data, $xref_array) {
    return $data;
  }

  /*
   * During the import routine, we take the exported username and try to find the UID.
   */
  function modulateExportUser($username) {
    $query = db_select('users', 'a');
    $query->fields('a',array('uid'));
    $query->condition('a.name',$username,'=');
    $uid=current($query->execute()->fetchAll());
    if(is_object($uid)) {
      return intval($uid->uid);
    }
    else {
      return 0;
    }
  }


  abstract function display();
  abstract function getEditFormContent();
}

class MaestroTaskInterfaceUnknown extends MaestroTaskInterface {
  public $_task_class;

  function __construct($task_id=0, $template_id=0, $task_class) {
    $this->_task_type = 'Unknown';
    $this->_is_interactive = MaestroInteractiveFlag::IS_NOT_INTERACTIVE;
    $this->_task_class = $task_class;

    parent::__construct($task_id, $template_id);
  }

  function display() {
    return theme('maestro_task_unknown', array('tdid' => $this->_task_id, 'taskname' => $this->_taskname, 'ti' => $this));
  }

  function getEditFormContent() {
    return '';
  }

  function getContextMenu() {
    $draw_line_msg = t('Select a task to draw the line to.');
    $options = array (
      'draw_line' => array(
        'label' => t('Draw Line'),
        'js' => "draw_status = 1; draw_type = 1; line_start = document.getElementById('task{$this->_task_id}'); set_tool_tip('$draw_line_msg');\n"
      ),
      'clear_lines' => array(
        'label' => t('Clear Adjacent Lines'),
        'js' => "clear_task_lines(document.getElementById('task{$this->_task_id}'));\n"
      ),
      'delete_task' => array(
        'label' => t('Delete Task'),
        'js' => "enable_ajax_indicator(); \$.ajax({
          type: 'POST',
          url: ajax_url + 'MaestroTaskInterface{$this->_task_type}/{$this->_task_id}/0/destroy/',
          cache: false,
          success: delete_task,
          dataType: 'json',
          error: editor_ajax_error
        });\n"
      )
    );

    return $options;
  }
}

class MaestroTaskInterfaceStart extends MaestroTaskInterface {
  function __construct($task_id=0, $template_id=0) {
    $this->_task_type = 'Start';
    $this->_is_interactive = MaestroInteractiveFlag::IS_NOT_INTERACTIVE;

    parent::__construct($task_id, $template_id);
  }

  function create() {
    parent::create();
    $update=db_update('maestro_template_data')
      ->fields(array( 'taskname' => t('Start'),
                      'first_task' => 1,
                      'offset_left' => 34,
                      'offset_top' => 38

      ))
      ->condition('id', $this->_task_id)
      ->execute();
  }

  function display() {
    return theme('maestro_task_start', array('tdid' => $this->_task_id, 'taskname' => $this->_taskname, 'ti' => $this));
  }

  function getEditFormContent() {
    return '';
  }

  function getContextMenu() {
    $draw_line_msg = t('Select a task to draw the line to.');
    $options = array (
      'draw_line' => array(
        'label' => t('Draw Line'),
        'js' => "draw_status = 1; draw_type = 1; line_start = document.getElementById('task{$this->_task_id}'); set_tool_tip('$draw_line_msg');\n"
      ),
      'clear_lines' => array(
        'label' => t('Clear Adjacent Lines'),
        'js' => "clear_task_lines(document.getElementById('task{$this->_task_id}'));\n"
      )
    );

    return $options;
  }
}

class MaestroTaskInterfaceEnd extends MaestroTaskInterface {
  function __construct($task_id=0, $template_id=0) {
    $this->_task_type = 'End';
    $this->_is_interactive = MaestroInteractiveFlag::IS_NOT_INTERACTIVE;

    parent::__construct($task_id, $template_id);
  }

  function create() {
    parent::create();
    $update=db_update('maestro_template_data')
      ->fields(array( 'taskname' => t('End'),
                      'offset_left' => 280,
                      'offset_top' => 200
      ))
      ->condition('id', $this->_task_id)
      ->execute();
  }

  function display() {
    return theme('maestro_task_end', array('tdid' => $this->_task_id, 'taskname' => $this->_taskname, 'ti' => $this));
  }

  function getEditFormContent() {
    return '';
  }

  function getContextMenu() {
    $options = array (
      'clear_lines' => array(
        'label' => t('Clear Adjacent Lines'),
        'js' => "clear_task_lines(document.getElementById('task{$this->_task_id}'));\n"
      )
    );

    return $options;
  }
}

class MaestroTaskInterfaceIf extends MaestroTaskInterface {
  function __construct($task_id=0, $template_id=0) {
    $this->_task_type = 'If';
    $this->_is_interactive = MaestroInteractiveFlag::IS_NOT_INTERACTIVE;

    parent::__construct($task_id, $template_id);
  }

  function modulateExportTaskData($data, $xref_array) {
    $fixed_data = @unserialize($data);
    if($fixed_data !== FALSE) {
      if($fixed_data['if_argument_variable'] != '') $fixed_data['if_argument_variable'] = $xref_array[$fixed_data['if_argument_variable']];
      return serialize($fixed_data);
    }
    else {
      return $data;
    }
  }

  function display() {
    return theme('maestro_task_if', array('tdid' => $this->_task_id, 'taskname' => $this->_taskname, 'ti' => $this));
  }

  function getEditFormContent() {
    $this->_fetchTaskInformation();

    $res = db_query("SELECT id, variable_name, variable_value FROM {maestro_template_variables} WHERE template_id=:tid", array('tid' => $this->_template_id));
    $argument_variables = "<option></option>";
    foreach ($res as $rec) {
      $selected = '';
      if($this->_task_data->task_data['if_argument_variable'] == $rec->id) $selected = " selected ";
      $argument_variables .= "<option value='{$rec->id}' {$selected}>{$rec->variable_name}</option>";
    }

    return theme('maestro_task_if_edit', array('tdid' => $this->_task_id, 'td_rec' => $this->_task_data, 'argument_variables' => $argument_variables));
  }

  function save() {
    $rec = new stdClass();
    $rec->id = $_POST['template_data_id'];

    if(check_plain($_POST['ifTaskArguments']) == 'status'){
      $rec->task_data = serialize(array(
                                    'if_operator' => '',
                                    'if_value' => '',
                                    'if_process_arguments' => $_POST['ifProcessArguments'],
                                    'if_argument_variable' => '',
                                    'if_task_arguments' => $_POST['ifTaskArguments']
      ));
    }
    else {
      $rec->task_data = serialize(array(
                                    'if_operator' => $_POST['ifOperator'],
                                    'if_value' => check_plain($_POST['ifValue']),
                                    'if_process_arguments' => '',
                                    'if_argument_variable' => $_POST['argumentVariable'],
                                    'if_task_arguments' => $_POST['ifTaskArguments']
      ));
    }
    drupal_write_record('maestro_template_data', $rec, array('id'));

    return parent::save();
  }

  function getContextMenu() {
    $draw_line_msg = t('Select a task to draw the line to.');
    $options = array (
      'draw_line' => array(
        'label' => t('Draw Success Line'),
        'js' => "draw_status = 1; draw_type = 1; line_start = document.getElementById('task{$this->_task_id}'); set_tool_tip('$draw_line_msg');\n"
      ),
      'draw_line_false' => array(
        'label' => t('Draw Fail Line'),
        'js' => "draw_status = 1; draw_type = 2; line_start = document.getElementById('task{$this->_task_id}'); set_tool_tip('$draw_line_msg');\n"
      ),
      'clear_lines' => array(
        'label' => t('Clear Adjacent Lines'),
        'js' => "clear_task_lines(document.getElementById('task{$this->_task_id}'));\n"
      ),
      'edit_task' => array(
        'label' => t('Edit Task'),
        'js' => "enable_ajax_indicator(); \$.ajax({
          type: 'POST',
          url: ajax_url + 'MaestroTaskInterface{$this->_task_type}/{$this->_task_id}/0/edit/',
          cache: false,
          dataType: 'json',
          success: display_task_panel,
          error: editor_ajax_error
        });"
      ),
      'delete_task' => array(
        'label' => t('Delete Task'),
        'js' => "enable_ajax_indicator(); \$.ajax({
          type: 'POST',
          url: ajax_url + 'MaestroTaskInterface{$this->_task_type}/{$this->_task_id}/0/destroy/',
          cache: false,
          dataType: 'json',
          success: delete_task,
          error: editor_ajax_error
        });\n"
      )
    );

    return $options;
  }
}

class MaestroTaskInterfaceBatch extends MaestroTaskInterface {
  function __construct($task_id=0, $template_id=0) {
    $this->_task_type = 'Batch';
    $this->_is_interactive = MaestroInteractiveFlag::IS_NOT_INTERACTIVE;

    parent::__construct($task_id, $template_id);

    $this->_task_edit_tabs = array('notification' => 1);
  }

  function display() {
    return theme('maestro_task_batch', array('tdid' => $this->_task_id, 'taskname' => $this->_taskname, 'ti' => $this));
  }

  function getEditFormContent() {
    $this->_fetchTaskInformation();
    $this->_task_data->task_data['handler_location'] = variable_get('maestro_batch_script_location', drupal_get_path('module','maestro') . "/batch/");
    return theme('maestro_task_batch_edit', array('tdid' => $this->_task_id, 'td_rec' => $this->_task_data));
  }

  function save() {
    $rec = new stdClass();
    $rec->id = $_POST['template_data_id'];
    $rec->task_data = serialize(array('handler' => $_POST['handler']));
    drupal_write_record('maestro_template_data', $rec, array('id'));

    return parent::save();
  }
}

class MaestroTaskInterfaceBatchFunction extends MaestroTaskInterface {
  function __construct($task_id=0, $template_id=0) {
    $this->_task_type = 'BatchFunction';
    $this->_is_interactive = MaestroInteractiveFlag::IS_NOT_INTERACTIVE;

    parent::__construct($task_id, $template_id);

    $this->_task_edit_tabs = array('optional' => 1, 'notification' => 1);
  }

  function display() {
    return theme('maestro_task_batch_function', array('tdid' => $this->_task_id, 'taskname' => $this->_taskname, 'ti' => $this));
  }

  function getEditFormContent() {
    $this->_fetchTaskInformation();
    $batch_function = drupal_get_path('module','maestro') . "/batch/batch_functions.php";
    $this->_task_data->task_data['handler_location'] = $batch_function;

    $handler_options = $this->getHandlerOptions();

    return theme('maestro_task_batch_function_edit', array('tdid' => $this->_task_id, 'td_rec' => $this->_task_data, 'handler_options' => $handler_options));
  }

  function save() {
    $rec = new stdClass();
    $rec->id = $_POST['template_data_id'];
    $rec->task_data = serialize(array('handler' => ($_POST['handler'] == '') ? $_POST['handler_other'] : $_POST['handler']));
    drupal_write_record('maestro_template_data', $rec, array('id'));

    return parent::save();
  }
}

class MaestroTaskInterfaceInteractiveFunction extends MaestroTaskInterface {
  function __construct($task_id=0, $template_id=0) {
    $this->_is_interactive = MaestroInteractiveFlag::IS_INTERACTIVE;
    $this->_task_type = 'InteractiveFunction';

    parent::__construct($task_id, $template_id);

    $this->_task_edit_tabs = array('assignment' => 1, 'notification' => 1, 'optional' => 1);
  }

  function display() {
    return theme('maestro_task_interactive_function', array('tdid' => $this->_task_id, 'taskname' => $this->_taskname, 'ti' => $this));
  }

  function getEditFormContent() {
    $this->_fetchTaskInformation();
    if (@($this->_task_data->optional_parm) == NULL) {
      $this->_task_data->optional_parm = '';
    }

    $handler_options = $this->getHandlerOptions();

    return theme('maestro_task_interactive_function_edit', array('tdid' => $this->_task_id, 'td_rec' => $this->_task_data, 'handler_options' => $handler_options));
  }

  function save() {
    $rec = new stdClass();
    $rec->id = $_POST['template_data_id'];
    $rec->task_data = serialize(array('handler' => ($_POST['handler'] == '') ? $_POST['handler_other'] : $_POST['handler']));

    drupal_write_record('maestro_template_data', $rec, array('id'));

    return parent::save();
  }
}

class MaestroTaskInterfaceSetProcessVariable extends MaestroTaskInterface {
  function __construct($task_id=0, $template_id=0) {
    $this->_task_type = 'SetProcessVariable';
    $this->_is_interactive = MaestroInteractiveFlag::IS_NOT_INTERACTIVE;

    parent::__construct($task_id, $template_id);
  }

  function display() {
    return theme('maestro_task_set_process_variable', array('tdid' => $this->_task_id, 'taskname' => $this->_taskname, 'ti' => $this));
  }

  function modulateExportTaskData($data, $xref_array) {
    $fixed_data = @unserialize($data);
    if($fixed_data !== FALSE) {
      $fixed_data['var_to_set'] = $xref_array[$fixed_data['var_to_set']];
      return serialize($fixed_data);
    }
    else {
      return $data;
    }
  }

  function getEditFormContent() {
    $methods = $this->getSetMethods();

    $this->_fetchTaskInformation();
    if (!is_array(@($this->_task_data->task_data)) || !array_key_exists('set_type', $this->_task_data->task_data)) {
      $this->_task_data->task_data['var_to_set'] = '';
      $this->_task_data->task_data['set_type'] = 0;

      $i = 0;
      foreach ($methods as $key => $method) {
        if ($i++ == 0) {
          $this->_task_data->task_data['set_type'] = $key;
        }
        $this->_task_data->task_data[$key . '_value'] = '';
      }
    }

    $res = db_query("SELECT id, variable_name FROM {maestro_template_variables} WHERE template_id=:tid", array('tid' => $this->_template_id));
    foreach ($res as $rec) {
      $pvars[$rec->id] = $rec->variable_name;
    }

    return theme('maestro_task_set_process_variable_edit', array('tdid' => $this->_task_id, 'td_rec' => $this->_task_data, 'pvars' => $pvars, 'set_methods' => $methods));
  }

  function save() {
    $rec = new stdClass();
    $rec->id = $_POST['template_data_id'];
    $methods = $this->getSetMethods();
    $task_data = array();
    foreach ($methods as $key => $method) {
      $task_data[$key . '_value'] = $_POST[$key . '_value'];
    }
    $task_data['var_to_set'] = $_POST['var_to_set'];
    $task_data['set_type'] = $_POST['set_type'];
    $rec->task_data = serialize($task_data);

    drupal_write_record('maestro_template_data', $rec, array('id'));

    return parent::save();
  }

  function getSetMethods() {
    $set_process_variable_methods = cache_get('maestro_set_process_variable_methods');
    if($set_process_variable_methods === FALSE) {
      $set_process_variable_methods = array();
      foreach (module_implements('maestro_set_process_variable_methods') as $module) {
        $function = $module . '_maestro_set_process_variable_methods';
        if ($arr = $function()) {
          $set_process_variable_methods = maestro_array_merge_keys($set_process_variable_methods, $arr);
        }
      }
      cache_set('maestro_set_process_variable_methods', $set_process_variable_methods);
    }

    $methods = cache_get('maestro_set_process_variable_methods');

    return $methods->data;
  }
}

class MaestroTaskInterfaceAnd extends MaestroTaskInterface {
  function __construct($task_id=0, $template_id=0) {
    $this->_task_type = 'And';
    $this->_is_interactive = MaestroInteractiveFlag::IS_NOT_INTERACTIVE;

    parent::__construct($task_id, $template_id);
  }

  function display() {
    return theme('maestro_task_and', array('tdid' => $this->_task_id, 'taskname' => $this->_taskname, 'ti' => $this));
  }

  function getEditFormContent() {
    return '';
  }
}

class MaestroTaskInterfaceManualWeb extends MaestroTaskInterface {
  function __construct($task_id=0, $template_id=0) {
    $this->_task_type = 'ManualWeb';
    $this->_is_interactive = MaestroInteractiveFlag::IS_INTERACTIVE;

    parent::__construct($task_id, $template_id);
  }

  function display() {
    return theme('maestro_task_manual_web', array('tdid' => $this->_task_id, 'taskname' => $this->_taskname, 'ti' => $this));
  }

  function getEditFormContent() {
    $this->_fetchTaskInformation();
    return theme('maestro_task_manual_web_edit', array('tdid' => $this->_task_id, 'td_rec' => $this->_task_data));
  }

  function save() {
    $rec = new stdClass();
    $rec->id = $_POST['template_data_id'];
    $rec->task_data = serialize(array(
                                    'handler' => $_POST['handler'],
                                    'new_window' => $_POST['newWindow'],
                                    'use_token' => $_POST['useToken'],

      ));
    drupal_write_record('maestro_template_data', $rec, array('id'));

    return parent::save();
  }
}


class MaestroTaskInterfaceContentType extends MaestroTaskInterface {
  function __construct($task_id=0, $template_id=0) {
    $this->_task_type = 'ContentType';
    $this->_is_interactive = MaestroInteractiveFlag::IS_INTERACTIVE;
    parent::__construct($task_id, $template_id);
  }

  function display() {
    return theme('maestro_task_content_type', array('tdid' => $this->_task_id, 'taskname' => $this->_taskname, 'ti' => $this));
  }

  function getEditFormContent() {
    $this->_fetchTaskInformation();
    $content_types = node_type_get_types();

    return theme('maestro_task_content_type_edit', array('tdid' => $this->_task_id, 'td_rec' => $this->_task_data, 'content_types' => $content_types));
  }

  function save() {
    $rec = new stdClass();
    $rec->id = $_POST['template_data_id'];
    $rec->task_data = serialize(array('content_type' => $_POST['content_type']));
    drupal_write_record('maestro_template_data', $rec, array('id'));
    $retval = parent::save();

    //clear the cache for maestro_content_types so on page load the new content type task will be added for sure
    cache_clear_all();

    return $retval;
  }
}


class MaestroTaskInterfaceFireTrigger extends MaestroTaskInterface {
  function __construct($task_id=0, $template_id=0) {
    $this->_task_type = 'FireTrigger';
    $this->_is_interactive = MaestroInteractiveFlag::IS_NOT_INTERACTIVE;

    parent::__construct($task_id, $template_id);

    $this->_task_edit_tabs = array('optional' => 1);
  }

  function display() {
    return theme('maestro_task_fire_trigger', array('tdid' => $this->_task_id, 'taskname' => $this->_taskname, 'ti' => $this));
  }

  function getEditFormContent() {
    $this->_fetchTaskInformation();
    $query = db_select('trigger_assignments', 'a');
    $query->fields('a', array('hook'));
    $query->fields('b', array('aid', 'label'));
    $query->leftJoin('actions', 'b', 'a.aid=b.aid');
    $query->condition('a.hook', "fire_trigger_task{$this->_task_id}", '=');
    $aa_res = $query->execute();

    $options = array();
    $functions = array();
    $hook = 'fire_trigger_task' . $this->_task_id;
    // Restrict the options list to actions that declare support for this hook.
    foreach (actions_list() as $func => $metadata) {
      if (isset($metadata['triggers']) && array_intersect(array($hook, 'any'), $metadata['triggers'])) {
        $functions[] = $func;
      }
    }
    foreach (actions_get_all_actions() as $aid => $action) {
      if (in_array($action['callback'], $functions)) {
        $options[$action['type']][$aid] = $action['label'];
      }
    }

    return theme('maestro_task_fire_trigger_edit', array('tdid' => $this->_task_id, 'td_rec' => $this->_task_data, 'aa_res' => $aa_res, 'options' => $options));
  }

  function save() {
    $actions = $_POST['actions'];
    $hook = 'fire_trigger_task' . $this->_task_id;

    $res = db_delete('trigger_assignments')
      ->condition('hook', $hook)
      ->execute();

    $weight = 1;
    foreach ($actions as $aid) {
      $rec = new stdClass();
      $rec->hook = $hook;
      $rec->aid = $aid;
      $rec->weight = $weight++;
      drupal_write_record('trigger_assignments', $rec);
    }

    return parent::save();
  }
}

