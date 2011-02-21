<?php
// $Id: maestro.admin.inc,v 1.62 2011/01/10 17:19:32 chevy Exp $

/**
 * @file
 * maestro.admin.inc
 */

include_once './' . drupal_get_path('module', 'maestro') . '/maestro_interface.class.php';
include_once './' . drupal_get_path('module', 'maestro') . '/maestro_task_interface.class.php';

function maestro_admin($tid=0, $operation='', $var=0) {
  global $base_url;
  $maestro_path = $base_url . '/' . drupal_get_path('module', 'maestro');
  drupal_add_css(drupal_get_path('module', 'maestro') . '/css/maestro.css');
  drupal_add_js($maestro_path . '/js/maestro_structure_admin.js');

  return theme('maestro_workflow_list', array('tid' => $tid, 'operation' => $operation , 'edit_var' => $var));
}

//called from the hooks_menu
function maestro_edit_workflow($tid='') {
  global $base_url;

  if ($tid === '') drupal_goto('admin/structure/maestro');

  $maestro_path = '/' . drupal_get_path('module', 'maestro');

  drupal_add_css(drupal_get_path('module', 'maestro') . '/css/maestro.css');
  drupal_add_js($base_url . $maestro_path . '/js/wz_jsgraphics.js');
  drupal_add_js($base_url . $maestro_path . '/js/admin_template_editor.js');
  drupal_add_js($base_url . $maestro_path . '/js/jquery.contextmenu.js');
  drupal_add_js($base_url . $maestro_path . '/js/jquery.simplemodal.min.js');

  $mi = new MaestroInterface($tid);

  return $mi->displayPage();
}

function maestro_handle_editor_ajax_request($task_type, $template_data_id, $template_id, $op) {
  if (class_exists($task_type)) {
    $ti = new $task_type($template_data_id, $template_id);
  }
  else {
    $ti = new MaestroTaskInterfaceUnknown($template_data_id, $template_id);
  }

  print json_encode($ti->$op());
  exit();
}

function maestro_handle_structure_ajax_request(){
  global $base_url;
  if(!user_access('maestro admin')) return false;
  $op=@check_plain($_POST['op']);
  $cntr=@check_plain($_POST['cntr']);
  $id=@check_plain($_POST['id']);

  switch($op){
    case 'openimport':
      //the fact that they made it in here means that we're OK as far as security is concerned.
      //we have to check to see if the config parameter is set to even show this option.
      $status = 0;
      if(variable_get('maestro_enable_import_window',0) == 1) {
        $status = 1;
      }
      $arr=array('status' => $status);
      echo json_encode($arr);
      exit(0);
      break;

    case 'doimport':
      $status = 0;
      if(variable_get('maestro_enable_import_window',0) == 1) {
        $templatecode=($_POST['templatecode']);
        //Lets see if someone has tried to do something "evil".
        if(strpos(strtolower($templatecode),'delete') !== FALSE ||
        strpos(strtolower($templatecode),'drop') !== FALSE ||
        strpos(strtolower($templatecode),'truncate') !== FALSE ) {
          $status = -1;
        }
        else {
          try {
            $ret=eval($templatecode);
            $status = 1;
          }
          catch (Exception $ex) {
            $status = 0;
          }
        }

      }
      $data=theme('maestro_workflow_list', array('tid' => 0, 'operation' => '' , 'edit_var' => 0));
      $arr=array('data' => $data, 'status' => $status);
      echo json_encode($arr);
      break;

    case 'savetemplate':
      $name=check_plain($_POST['templateName']);
      $app_group=intval(check_plain($_POST['appGroup']));

      $update=db_update('maestro_template')
      ->fields(array( 'template_name' => $name,
                      'app_group' => $app_group
      ))
      ->condition('id', $id)
      ->execute();
      $status="0";
      if($update>=0){
        $status="1";
      }
      $arr=array('id' => $id, 'cntr' => $cntr, 'status' => $status);
      echo json_encode($arr);
      break;

    case 'createvariable':
      $name=check_plain($_POST['newVariableName']);
      $value=check_plain($_POST['newVariableValue']);
      $recID=maestro_createTemplateVariable($id, $name, $value);
      $status = 0;
      if($recID > 0){
        $status = 1;
      }
      $data=theme('maestro_workflow_edit_template_variables',  array('tid' => $id ));

      $arr=array('status' => $status, 'data' => $data, 'cntr' => $id );
      echo json_encode($arr);
      break;

    case 'updatevariable':
      $name=check_plain($_POST['name']);
      $value=check_plain($_POST['val']);
      $update=db_update('maestro_template_variables')
      ->fields(array( 'variable_name' => $name,
                      'variable_value' => $value
      ))
      ->condition('id', $id)
      ->execute();

      $query = db_select('maestro_template_variables', 'a');
      $query->fields('a',array('template_id'));
      $query->condition('a.id',$id,'=');
      $res=current($query->execute()->fetchAll());

      $status="0";
      if($update>=0){
        $status="1";
      }
      $data=theme('maestro_workflow_edit_template_variables',  array('tid' => $res->template_id ));
      $arr=array('status' => $status, 'data' => $data, 'var_id' => $id);
      echo json_encode($arr);
      break;

    case 'deletevariable':
      $tid=check_plain($_POST['tid']);
      $ret=maestro_deleteTemplateVariable($id);
      $data=theme('maestro_workflow_edit_template_variables',  array('tid' => $tid ));
      $status="0";
      if($ret){
        $status="1";
      }
      $arr=array('status' => $status, 'data' => $data, 'cntr' => $tid);
      echo json_encode($arr);
      break;

    case 'editvariable':
      $tid=check_plain($_POST['tid']);
      $data=theme('maestro_workflow_edit_template_variables',  array('tid' => $tid, 'edit_var' => $id));
      $arr=array('status' => "1", 'data' => $data, 'cntr' => $tid);
      echo json_encode($arr);
      break;

    case 'showvariables':
      $data=theme('maestro_workflow_edit_template_variables',  array('tid' => $id ));
      $arr=array('status' => "1", 'data' => $data, 'cntr' => $id);
      echo json_encode($arr);
      break;

    case 'createtemplate':
      $name=check_plain($_POST['name']);
      $recID=maestro_createNewTemplate($name);
      $status = 0;
      if($recID > 0){
        $status = 1;
      }
      $data=theme('maestro_workflow_list', array('tid' => 0, 'operation' => '' , 'edit_var' => 0));

      $arr=array('status' => $status, 'data' => $data);
      echo json_encode($arr);
      break;

    case 'createappgroup':
      $name=check_plain($_POST['name']);
      if($name != '')  $recID=maestro_createAppGroup($name);
      $status = 0;
      if($recID > 0){
        $status = 1;
      }
      $arr=array('status' => $status, 'data' => '');
      echo json_encode($arr);
      break;

    case 'deleteappgroup':
      maestro_deleteAppGroup($id);
      $data=maestro_createAppGroupDropDown('deleteAppGroup');
      $arr=array('status' => "1", 'data' => $data);
      echo json_encode($arr);
      break;

    case 'refreshappgroup':
      $which=check_plain($_POST['which']);
      $data=maestro_createAppGroupDropDown($which);
      $arr=array('status' => "1", 'data' => $data);
      echo json_encode($arr);
      break;

    case 'deletetemplate':
      $res=db_query("SELECT id FROM {maestro_template_data} WHERE template_id = :tid", array('tid' => $id));
      foreach($res as $rec){
        $query = db_delete('maestro_template_assignment');
        $query->condition('template_data_id',$rec->id,'=');
        $query->execute();

        $query = db_delete('maestro_template_notification');
        $query->condition('template_data_id',$rec->id,'=');
        $query->execute();

        $query = db_delete('maestro_template_data_next_step');
        $query->condition('template_data_from',$rec->id,'=');
        $query->execute();

        $query = db_delete('maestro_template_data');
        $query->condition('id',$rec->id,'=');
        $query->execute();
      }

      $query = db_delete('maestro_template_variables');
      $query->condition('template_id',$id,'=');
      $query->execute();

      $query = db_delete('maestro_template');
      $query->condition('id',$id,'=');
      $query->execute();

      $data = theme('maestro_workflow_list', array('tid' => 0, 'operation' => '' , 'edit_var' => 0));
      $arr=array('status' => "1", 'data' => $data);
      echo json_encode($arr);
      break;

    case 'copytemplate':
      $query = db_select('maestro_template', 'a');
      $query->fields('a',array('template_name', 'app_group','canvas_height'));
      $query->condition('a.id',$id,'=');
      $name=current($query->execute()->fetchAll());
      $record = new stdClass();
      $record ->template_name = $name->template_name . " - COPY";
      $record ->app_group = $name->app_group;
      $record ->canvas_height = $name->canvas_height;
      drupal_write_record('maestro_template', $record);
      $newTID=$record->id;

      $variable_xref_array = Array();
      $variable_xref_array[0] = 0;
      $res=db_query("SELECT * FROM {maestro_template_variables} WHERE template_id = :tid", array('tid' => $id));
      foreach($res as $rec) {
        $newrecord = new stdClass();
        $newrecord->template_id = $newTID;
        $newrecord->variable_name = $rec->variable_name;
        $newrecord->variable_value = $rec->variable_value;
        drupal_write_record('maestro_template_variables', $newrecord);
        $variable_xref_array[$rec->id] = $newrecord->id;
      }

      $taskDataArray=array();
      $res=db_query("SELECT * FROM {maestro_template_data} WHERE template_id = :tid", array('tid' => $id));
      foreach($res as $rec) {
        $newrecord = new stdClass();
        $newrecord->template_id = $newTID;
        $newrecord->task_class_name = $rec->task_class_name;
        $newrecord->is_interactive = $rec->is_interactive;

        //we need to transform this data here to use the new variables etc etc if required.
        $task_type = substr($rec->task_class_name, 15);
        $task_class = 'MaestroTaskInterface' . $task_type;
        $ti = new $task_class(0, 0);
        $fixed_data = $ti->modulateExportTaskData($rec->task_data, $variable_xref_array);
        $rec->task_data = $fixed_data;
        $newrecord->task_data = $rec->task_data;

        $newrecord->handler = $rec->handler;
        $newrecord->first_task = $rec->first_task;
        $newrecord->taskname = $rec->taskname;
        $newrecord->assigned_by_variable = $rec->assigned_by_variable;
        $newrecord->argument_variable = $rec->argument_variable;
        $newrecord->if_argument_process = $rec->if_argument_process;
        $newrecord->operator = $rec->operator;
        $newrecord->if_value = $rec->if_value;
        $newrecord->regenerate = $rec->regenerate;
        $newrecord->regen_all_live_tasks = $rec->regen_all_live_tasks;
        $newrecord->is_dynamic_form = $rec->is_dynamic_form;
        $newrecord->dynamic_form_variable_id = $rec->dynamic_form_variable_id;
        $newrecord->is_dynamic_taskname = $rec->is_dynamic_taskname;
        $newrecord->dynamic_taskname_variable_id = $rec->dynamic_taskname_variable_id;
        $newrecord->function = $rec->function;
        $newrecord->form_id = $rec->form_id;
        $newrecord->field_id = $rec->field_id;
        $newrecord->var_value = $rec->var_value;
        $newrecord->inc_value = $rec->inc_value;
        $newrecord->var_to_set = $rec->var_to_set;
        $newrecord->optional_parm = $rec->optional_parm;
        $newrecord->reminder_interval = $rec->reminder_interval;
        $newrecord->reminder_interval_variable = $rec->reminder_interval_variable;
        $newrecord->subsequent_reminder_interval = $rec->subsequent_reminder_interval;
        $newrecord->last_updated = $rec->last_updated;
        $newrecord->pre_notify_subject = $rec->pre_notify_subject;
        $newrecord->post_notify_subject = $rec->post_notify_subject;
        $newrecord->reminder_subject = $rec->reminder_subject;
        $newrecord->pre_notify_message = $rec->pre_notify_message;
        $newrecord->post_notify_message = $rec->post_notify_message;
        $newrecord->reminder_message = $rec->reminder_message;
        $newrecord->num_reminders = $rec->num_reminders;
        $newrecord->escalate_variable_id = $rec->escalate_variable_id;
        $newrecord->offset_left = $rec->offset_left;
        $newrecord->offset_top = $rec->offset_top;
        $newrecord->surpress_first_notification = $rec->surpress_first_notification;
        drupal_write_record('maestro_template_data', $newrecord);
        $taskDataArray[$rec->id]= $newrecord->id;
      }

      //at this point, we have a list that points the old ID record to the new one.
      //now for each template data next step, we determine which record to go to/from
      $res=db_query("SELECT * FROM {maestro_template_data} WHERE template_id = :tid", array('tid' => $id));
      foreach($res as $rec) {
        $nextstepres = db_query("SELECT * FROM {maestro_template_data_next_step} WHERE template_data_from = :id", array('id' => $rec->id));
        foreach($nextstepres as $nextstep) {
          $sql  = "INSERT INTO {maestro_template_data_next_step} (template_data_from, template_data_to, template_data_to_false) ";
          $sql .= "VALUES (:a1, :b1, :c1)";
          db_query($sql, array('a1' => $taskDataArray[$nextstep->template_data_from], 'b1' => $taskDataArray[$nextstep->template_data_to], 'c1' => $taskDataArray[$nextstep->template_data_to_false]));
        }

        $assignmentres = db_query("SELECT * FROM {maestro_template_assignment} WHERE template_data_id = :id", array('id' => $rec->id));
        foreach($assignmentres as $ares) {
          $assign_by = $ares->assign_by;
          $modulated_id = intval($ares->assign_id);
          switch($ares->assign_type) {
            case MaestroAssignmentTypes::USER:
              if($assign_by == MaestroAssignmentBy::VARIABLE) {
                $modulated_id = $variable_xref_array[$ares->assign_id];
              }
              break;

            case MaestroAssignmentTypes::GROUP:
              //@TODO:  implement this when we implement OG suppport
              break;

            case MaestroAssignmentTypes::ROLE:
              //@TODO:  implement this when we implement Role suppport
              break;
          }

          $sql  = "INSERT INTO {maestro_template_assignment} (template_data_id, assign_type, assign_by, assign_id) ";
          $sql .= "VALUES (:a1, :b1, :c1, :d1)";
          db_query($sql, array( 'a1' => $taskDataArray[$ares->template_data_id],
                                'b1' => $ares->assign_type,
                                'c1' => $ares->assign_by,
                                'd1' => $modulated_id
          ));
        }

      }
      $data = theme('maestro_workflow_list', array('tid' => 0, 'operation' => '' , 'edit_var' => 0));
      $arr=array('status' => "1", 'data' => $data);
      echo json_encode($arr);
      break;

  }
}

function maestro_configure() {
  return drupal_get_form('maestro_admin_form');
}

function maestro_admin_form() {

  $form['maestro_batch_script_location'] = array(
    '#type' => 'textfield',
    '#title' => t('Maestro batch script physical file location'),
    '#default_value' => variable_get('maestro_batch_script_location', drupal_get_path('module','maestro') . "/batch/"),
    '#size' => 100,
    '#description' => t('This is the physical directory where the batch scripts are stored.
          It is highly recommended that batch scripts be placed in a directory outside of web root.'),
    '#required' => TRUE,
  );
  $form['maestro_run_orchestrator_in_task_console'] = array(
    '#type' => 'checkbox',
    '#title' => t('Run the Orchestrator when the Task Console Renders'),
    '#default_value' => variable_get('maestro_run_orchestrator_in_task_console', 0),
    '#description' => t('Enabling this option will run the Orchestrator each time you visit or refresh the Task Console page.'),
    '#required' => FALSE,
  );
  $form['maestro_enable_import_window'] = array(
    '#type' => 'checkbox',
    '#title' => t('Enable the import window in the Template Editor.'),
    '#default_value' => variable_get('maestro_enable_import_window', 0),
    '#description' => t('Enabling this option will turn on the Import Window in the template editor.  It is NOT recommended to have this on in production environments.'),
    '#required' => FALSE,
  );

  $form['maestro_enable_notifications'] = array(
    '#type' => 'checkbox',
    '#title' => t('Enable Maestro Notifications.'),
    '#default_value' => variable_get('maestro_enable_notifications', 1),
    '#description' => t('Checking this option will turn on the Maestro Notifications mechanism.'),
    '#required' => FALSE,
  );

  include_once('maestro_notification.class.php');
  $observers = array();
  $observers[0] = t("No Notifier");
  foreach (module_implements('maestro_notification_observer') as $module) {
    $function = $module . '_maestro_notification_observer';
    if ($declaredObserver = $function()) {
      foreach($declaredObserver as $observer) {
        $obj = new $observer();
        $observers[$observer] = $obj->displayName;
      }
    }
  }

  if(variable_get('maestro_enabled_notifiers') == NULL || (is_array(variable_get('maestro_enabled_notifiers')) && count(variable_get('maestro_enabled_notifiers')) == 0 )  ) {
    //this means that all of them should be selected by default
    $x = array();
    foreach($observers as $key => $val) {
      if($key != '0') {
        $x[$key] = $key;
      }
    }

    variable_set('maestro_enabled_notifiers',$x);
  }


  $form['maestro_enabled_notifiers'] = array(
    '#type' => 'select',
    '#title' => t('Select Which Notifiers to Enable'),
    '#default_value' => variable_get('maestro_enabled_notifiers', NULL),
    '#options' =>  $observers,
    '#description' => t('The notifiers highlighted will be used to send Maestro notifications.'),
    '#rows'        => 10,
    '#multiple'    => TRUE,
  );

  $form['notifications'] = array(
  '#type' => 'vertical_tabs',
  );

  $form['assignment_notifications'] = array(
    '#type' => 'fieldset',
    '#title' => t('Task Assignment Notifcation settings'),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
    '#tree' => TRUE,
    '#group' => 'notifications'
    );

    $form['assignment_notifications']['maestro_assignment_subject'] = array(
    '#type' => 'textfield',
    '#title' => t('Assignment Notification Default Subject'),
    '#default_value' => variable_get('maestro_assignment_subject'),
    '#required' => FALSE,
    );

    $form['assignment_notifications']['maestro_assignment_message'] = array(
    '#type' => 'textarea',
    '#title' => t('Assignment Notification Default Message'),
    '#default_value' => variable_get('maestro_assignment_message'),
    '#description' => t('Valid tokens: [workflow_name], [task_name], [task_owner], [task_console_url]'),
    '#required' => FALSE,
    );

    $form['commpletion_notifications'] = array(
    '#type' => 'fieldset',
    '#title' => t('Task Completion Notifcation settings'),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
    '#tree' => TRUE,
    '#group' => 'notifications'
    );

    $form['commpletion_notifications']['maestro_completion_subject'] = array(
    '#type' => 'textfield',
    '#title' => t('Completion Notification Default Subject'),
    '#default_value' => variable_get('maestro_completion_subject'),
    '#required' => FALSE,
    );

    $form['commpletion_notifications']['maestro_completion_message'] = array(
    '#type' => 'textarea',
    '#title' => t('Completion Notification Default Message'),
    '#default_value' => variable_get('maestro_completion_message'),
    '#description' => t('Valid tokens: [workflow_name], [task_name], [task_owner], [task_console_url]'),
    '#required' => FALSE,
    );

    $form['reminder_notifications'] = array(
    '#type' => 'fieldset',
    '#title' => t('Task Reminder Notifcation settings'),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
    '#tree' => TRUE,
    '#group' => 'notifications'
    );

    $form['reminder_notifications']['maestro_reminder_subject'] = array(
    '#type' => 'textfield',
    '#title' => t('Reminder Notification Default Subject'),
    '#default_value' => variable_get('maestro_reminder_subject'),
    '#required' => FALSE,
    );

    $form['reminder_notifications']['maestro_reminder_message'] = array(
    '#type' => 'textarea',
    '#title' => t('Reminder Notification Default Message'),
    '#default_value' => variable_get('maestro_reminder_message'),
    '#description' => t('Valid tokens: [workflow_name], [task_name], [task_owner], [task_console_url]'),
    '#required' => FALSE,
    );

    $form['escalation_notifications'] = array(
    '#type' => 'fieldset',
    '#title' => t('Task Escalation Notification settings'),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
    '#tree' => TRUE,
    '#group' => 'notifications'
    );

    $form['escalation_notifications']['maestro_escalation_subject'] = array(
    '#type' => 'textfield',
    '#title' => t('Escalation Notification Default Subject'),
    '#default_value' => variable_get('maestro_escalation_subject'),
    '#required' => FALSE,
    );

    $form['escalation_notifications']['maestro_escalation_message'] = array(
    '#type' => 'textarea',
    '#title' => t('Escalation Notification Default Message'),
    '#default_value' => variable_get('maestro_escalation_message'),
    '#description' => t('Valid tokens: [workflow_name], [task_name], [task_owner], [task_console_url]'),
    '#required' => FALSE,
    );

    return system_settings_form($form);
}


function maestro_edit_properties($tid, $var=0){
  //$tid is the edited template
  return maestro_admin($tid, 'edit', $var);
}

/*
 * The following functions are here in support of the Maestro Structure page functionality.
 * These functions need to exist in order to run unit tests against their functionality.
 */

function maestro_createNewTemplate($name, $skip_task_creation = FALSE, $skip_variable_creation = FALSE, $canvas_height=500){
  if(!user_access('maestro admin')) return false;
  if(trim($name) != '') {
    $record = new stdClass();
    $record->template_name = $name;
    $record->canvas_height = $canvas_height;
    drupal_write_record('maestro_template', $record);
    $newTemplateRecId = $record->id;
    if(!$skip_task_creation) {
      $task=new MaestroTaskInterfaceStart(0, $newTemplateRecId);
      $task->create();
      $task=new MaestroTaskInterfaceEnd(0, $newTemplateRecId);
      $task->create();
    }
    if(!$skip_variable_creation) {
      maestro_createTemplateVariable($newTemplateRecId, 'initiator', '');
    }
    return $newTemplateRecId;
  }
  else {
    return FALSE;
  }
}

function maestro_createTemplateVariable($tid, $name, $value){
  if(!user_access('maestro admin')) return false;
  $record = new stdClass();
  $record ->variable_name = $name;
  $record ->variable_value = $value;
  $record ->template_id = $tid;
  drupal_write_record('maestro_template_variables', $record);
  return $record->id;
}

function maestro_deleteTemplateVariable($id){
  if(!user_access('maestro admin')) return false;
  $query = db_select('maestro_template_variables', 'a');
  $query->fields('a',array('variable_name'));
  $query->condition('a.id', $id, '=');
  $name=current($query->execute()->fetchAll());
  if($name->variable_name != 'initiator') {
    $query = db_delete('maestro_template_variables');
    $query->condition('id', $id, '=');
    $query->execute();
    return true;
  }
  else {
    return false;
  }

}

function maestro_createAppGroup($name){
  if(!user_access('maestro admin')) return false;
  $record = new stdClass();
  $record ->app_group = $name;
  drupal_write_record('maestro_app_groups', $record);
  return $record->id;
}

function maestro_deleteAppGroup($id){
  if(!user_access('maestro admin')) return false;
  $query = db_delete('maestro_app_groups');
  $query->condition('id', $id, '=');
  $query->execute();
}

function maestro_joinAppGroup($template_id, $appgroup_id) {
  if(!user_access('maestro admin')) return false;
  $update = db_update('maestro_template')
  ->fields(array( 'app_group' => $appgroup_id
  ))
  ->condition('id', $template_id)
  ->execute();
}

function maestro_export($id) {
  $output_php = "";
  $id=intval($id);
  if($id > 0) {
    //first step -- fetch the name of the original template
    $output_php .= '$_POST[\'offset_top\'] = 0;' . "\n";
    $output_php .= '$_POST[\'offset_left\'] = 0;' . "\n";
    $output_php .= '$variable_xref_array = Array();' . "\n";
    $output_php .= '$variable_xref_array[0] = 0;' . "\n";
    $output_php .= '$task_xref_array = Array();' . "\n";
    $output_php .= '$task_xref_array[0] = 0;' . "\n";
    $query = db_select('maestro_template', 'a');
    $query->fields('a',array('template_name', 'canvas_height'));
    $query->condition('a.id',$id,'=');
    $name=current($query->execute()->fetchAll());
    $original_template_name = str_replace('"', '\"', $name->template_name);
    $canvas_height = $name->canvas_height;
    $output_php .= '$templateID = maestro_createNewTemplate("IMPORT - ' . $original_template_name . '", TRUE, TRUE, ' . $canvas_height . ');' . "\n"; //create new template
    //lets get all of the template variables:
    $template_variables_record_set=db_query("SELECT * FROM {maestro_template_variables} WHERE template_id = :tid", array('tid' => $id));
    foreach($template_variables_record_set as $rec) {
      $var_name = str_replace('"', '\"', $rec->variable_name);
      $var_value = str_replace('"', '\"', $rec->variable_value);
      $output_php .= '$variable_xref_array[' . $rec->id . '] = maestro_createTemplateVariable($templateID, "' . $var_name . '", "' . $var_value . '");' . "\n";
    }
    //lets get all of the template_data entries
    $template_data_record_set=db_query("SELECT * FROM {maestro_template_data} WHERE template_id = :tid", array('tid' => $id));
    foreach($template_data_record_set as $rec) {
      $task_type = substr($rec->task_class_name, 15);
      $task_class = 'MaestroTaskInterface' . $task_type;
      $output_php .= '$ti = new ' . $task_class . '(0, $templateID);' . "\n";
      $output_php .= '$ti->create();' . "\n";
      $output_php .= '$task_xref_array[' . $rec->id . '] = $ti->get_task_id();' . "\n";
      $output_php .= '$res = db_select(\'maestro_template_data\', \'a\');' . "\n";
      $output_php .= '$res->fields(\'a\', array(\'id\', \'task_data\'));' . "\n";
      $output_php .= '$res->condition(\'a.id\', $ti->get_task_id(), \'=\');' . "\n";
      $output_php .= '$rec = current($res->execute()->fetchAll());' . "\n";
      foreach($rec as $key => $data){
        if($key!='id' && $key!='task_class_name' && $key!='template_id') {
          if($key == 'task_data') {
            $output_php .= '$fixed_data = $ti->modulateExportTaskData(\'' . str_replace("'", "\'", $data) . '\', $variable_xref_array);' . "\n";
            $output_php .='$rec->task_data = $fixed_data;' . "\n";
          }
          elseif(is_numeric($data)) {
            $output_php .='$rec->' . $key . ' = ' . $data . ';' . "\n";
          }
          else {
            $output_php .='$rec->' . $key . ' = \'' . $data . '\';' . "\n";
          }
        }
      }
      $output_php .= 'drupal_write_record(\'maestro_template_data\', $rec, array(\'id\'));' . "\n";
    }
    //we now have to create the next step and assignmnents
    $template_data_record_set=db_query("SELECT * FROM {maestro_template_data} WHERE template_id = :tid", array('tid' => $id));
    foreach($template_data_record_set as $rec) {
      $nextstepres = db_query("SELECT * FROM {maestro_template_data_next_step} WHERE template_data_from = :id", array('id' => $rec->id));
      foreach($nextstepres as $nextstep) {
        $output_php .='$sql  = "INSERT INTO {maestro_template_data_next_step} (template_data_from, template_data_to, template_data_to_false) VALUES (:a1, :b1, :c1)";' . "\n";
        $output_php .='db_query($sql, array(\'a1\' => $task_xref_array[' . $nextstep->template_data_from . '], \'b1\' => $task_xref_array[' . $nextstep->template_data_to . '], \'c1\' => $task_xref_array[' . $nextstep->template_data_to_false . ']));' . "\n";
      }
      $assignmentres = db_query("SELECT * FROM {maestro_template_assignment} WHERE template_data_id = :id", array('id' => $rec->id));
      foreach($assignmentres as $ares) {
        $output_php .='$query = db_select(\'maestro_template_data\', \'a\');' . "\n";
        $output_php .='$query->fields(\'a\',array(\'task_class_name\'));' . "\n";
        $output_php .='$query->condition(\'a.id\',$task_xref_array[' . $ares->template_data_id . '],\'=\');' . "\n";
        $output_php .='$name=current($query->execute()->fetchAll());'. "\n";
        $output_php .='$task_type = substr($name->task_class_name, 15);' . "\n";
        $output_php .='$task_class = \'MaestroTaskInterface\' . $task_type;' . "\n";
        $output_php .='$ti = new $task_class(0,0);' . "\n";

        //we'll just make an initial assumption that the assign_by is by variable
        //we overwrite this in the switch if it is actually by a fixed value
        $assign_by = $ares->assign_by;
        $modulated_string = '$modulated_id = $variable_xref_array[' . intval($ares->assign_id) . '];' . "\n";
        switch($ares->assign_type) {
          case MaestroAssignmentTypes::USER:
            if($assign_by == MaestroAssignmentBy::FIXED) {
              $temp_user_info = user_load($ares->assign_id);
              $modulated_string ='$modulated_id = $ti->modulateExportUser("' . $temp_user_info->name . '");' . "\n";
            }
            break;

          case MaestroAssignmentTypes::GROUP:
            //@TODO:  implement this when we implement OG suppport
            break;

          case MaestroAssignmentTypes::ROLE:
            //@TODO:  implement this when we implement Role suppport
            break;
        }

        $output_php .= $modulated_string;

        $output_php .='$sql  = "INSERT INTO {maestro_template_assignment} (template_data_id, assign_type, assign_by, assign_id) VALUES (:a1, :b1, :c1, :d1)";' . "\n";
        $output_php .='db_query($sql, array( \'a1\' => $task_xref_array[' . $ares->template_data_id . '],';
        $output_php .='\'b1\' => ' . $ares->assign_type . ',';
        $output_php .='\'c1\' => ' . $ares->assign_by . ',';
        $output_php .='\'d1\' => $modulated_id,';
        $output_php .='));' . "\n";

      }
    }
  }
  return $output_php;
}

function maestro_export_template($tid, $var=0){
  $content=maestro_export($tid);
  return maestro_admin($tid, 'export_template', $var);
}