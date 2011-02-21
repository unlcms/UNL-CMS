<?php
// $Id: maestro.moderator.inc,v 1.16 2011/01/07 18:17:36 randy Exp $

/**
 * @file
 * maestro.moderator.inc
 */

function maestro_outstanding_tasks($op='', $qid=0, $other=NULL) {
  global $base_url;

  $maestro_path = '/' . drupal_get_path('module', 'maestro');
  $maestro_url = $base_url . $maestro_path;

  drupal_add_css($base_url . $maestro_path . '/css/maestro.css');
  drupal_add_js($base_url . $maestro_path . '/js/moderator.js');

  $maestro = Maestro::createMaestroObject(1);    // Initiate the processing of all tasks of type 1.x
  $m = $maestro->engine();

  $show_system_tasks = (intval(@($_POST['show_system_tasks'])) == 1) ? TRUE:FALSE;

  switch ($op) {
  case 'reassign':
    $m->reassignTask($qid, $_POST['reassign_uid'], $_POST['current_uid']);
    drupal_set_message("Task re-assignment completed and notification sent.");
    break;

  case 'email':
    $m->sendTaskReminderNotifications($qid, $other);
    drupal_set_message("Task assignment reminder notification sent.");
    break;

  case 'trace':
    break;

  case 'delete':
    $m->deleteTask($qid);
    break;

  default:
    $maestro->engine()->setMode('admin');
    $q = $m->getQueue($show_system_tasks);

    $query = db_select('users', 'a');
    $query->fields('a', array('uid', 'name'));
    $query->condition('a.uid', 0, '>');
    $user_res = $query->execute();

    return theme('maestro_outstanding_tasks', array('maestro_url' => $maestro_url, 'show_system_tasks' => $show_system_tasks, 'maestro' => $m, 'queue' => $q, 'users' => $user_res));
    break;
  }

  drupal_goto('maestro/outstanding');
}

function maestro_trace($tracking_id=0, $process_id=0, $queue_id=0) {
  global $base_url;

  $op = '';
  if (is_array($_POST) && array_key_exists('op', $_POST)) {
    $op = $_POST['op'];
  }

  switch ($op) {
  case 'save_task_changes':
    maestro_save_task_changes();
    break;

  case 'save_process_variables':
    maestro_save_process_variables();
    break;

  default:
    $maestro_path = '/' . drupal_get_path('module', 'maestro');
    $maestro_url = $base_url . $maestro_path;
    $ajax_url = url('maestro');

    drupal_add_css($base_url . $maestro_path . '/css/maestro.css');
    drupal_add_js($base_url . $maestro_path . '/js/moderator.js');

    $maestro = Maestro::createMaestroObject(1);    // Initiate the processing of all tasks of type 1.x
    $m = $maestro->engine();

    if ($process_id == 0 && $queue_id > 0) {
      $res = db_select('maestro_queue', 'a')
        ->fields('a', array('process_id'))
        ->condition('a.id', $queue_id, '=')
        ->execute();
      $process_id = current($res->fetchAll())->process_id;
    }

    if ($process_id > 0) {
      $res = db_select('maestro_process', 'a')
        ->fields('a', array('tracking_id'))
        ->condition('a.id', $process_id, '=')
        ->execute();
      $tracking_id = current($res->fetchAll())->tracking_id;
    }

    $initiating_pid = $m->getParentProcessId($process_id);

    $properties = new stdClass();
    $properties->queue_id = $queue_id;
    $properties->process_id = $process_id;
    $properties->tracking_id = $tracking_id;
    $properties->initiating_pid = $initiating_pid;


    $query = db_select('maestro_process', 'a');
    $query->fields('b', array('variable_value', 'process_id', 'id'));
    $query->fields('c', array('variable_name'));
    $query->leftJoin('maestro_process_variables', 'b', 'a.id=b.process_id');
    $query->leftJoin('maestro_template_variables', 'c', 'b.template_variable_id=c.id');
    $query->condition('a.initiating_pid', $initiating_pid, '=');
    $query->orderBy('a.id', 'ASC');
    $query->orderBy('c.id', 'ASC');
    $pv_res = $query->execute();

    $query = db_select('maestro_process', 'a');
    $query->fields('a', array('id'));
    $query->fields('b', array('template_name'));
    $query->leftJoin('maestro_template', 'b', 'a.template_id=b.id');
    $query->condition('a.initiating_pid', $initiating_pid, '=');
    $proc_res = $query->execute();

    $statuses = MaestroTaskStatusCodes::getStatusLabel();

    $wf_res = $m->getRelatedWorkflows($tracking_id);
    $trace = $m->getQueueHistory($initiating_pid);

    return theme('maestro_trace', array('maestro_url' => $maestro_url, 'ajax_url' => $ajax_url, 'trace' => $trace, 'properties' => $properties, 'pv_res' => $pv_res, 'proc_res' => $proc_res, 'wf_res' => $wf_res, 'statuses' => $statuses));
    break;
  }
}

function maestro_save_task_changes() {
  $maestro = Maestro::createMaestroObject(1);    // Initiate the processing of all tasks of type 1.x
  $m = $maestro->engine();

  foreach ($_POST['queue_id'] as $key => $qid) {
    $query = db_select('maestro_queue', 'a');
    $query->fields('a', array('id', 'status', 'archived'));
    $query->condition('a.id', $qid, '=');
    $rec = current($query->execute()->fetchAll());

    $prev_status = $rec->status;
    $status = ($_POST['batch_op'][$key] == 1) ? $_POST['batch_op_status'] : $_POST['status'][$key];

    $rec->status = $status;
    $rec->archived = $_POST['archived'][$key];
    drupal_write_record('maestro_queue', $rec, array('id'));

    if ($status == MaestroTaskStatusCodes::STATUS_DELETED) {
      $m->deleteTask($qid);
    }
    else { //need to readd the procution assignment records
      if ($prev_status == MaestroTaskStatusCodes::STATUS_DELETED) {
        $m->reviveTask($qid);
      }
    }
  }

  print json_encode(array('success' => 1));
  exit();
}

function maestro_save_process_variables() {
  foreach ($_POST['process_variable_id'] as $key => $vid) {
    $query = db_update('maestro_process_variables');
    $query->fields(array('variable_value' => $_POST['process_variable_value'][$key]));
    $query->condition('id', $vid, '=');
    $query->execute();
  }

  print json_encode(array('success' => 1));
  exit();
}


function maestro_all_flows() {
  global $base_url;
  $maestro_path = '/' . drupal_get_path('module', 'maestro');
  $maestro_url = $base_url . $maestro_path;
  drupal_add_css($base_url . $maestro_path . '/css/maestro.css');
  drupal_add_js($base_url . $maestro_path . '/js/moderator.js');
  drupal_add_js($base_url . $maestro_path . '/js/taskconsole.js');
  $ajax_url = url('maestro/taskconsole/ajax') ;
  $rows = maestro_get_all_flows_data();  //no filters.  all flows here
  return theme('maestro_all_flows', array('ajax_url' => $ajax_url, 'filter_url' => $ajax_url, 'database_result_set' => $rows));
}

function maestro_my_flows() {
  global $base_url, $user;
  $maestro_path = '/' . drupal_get_path('module', 'maestro');
  $maestro_url = $base_url . $maestro_path;
  drupal_add_css($base_url . $maestro_path . '/css/maestro.css');
  drupal_add_js($base_url . $maestro_path . '/js/moderator.js');
  drupal_add_js($base_url . $maestro_path . '/js/taskconsole.js');
  $ajax_url = url('maestro/taskconsole/ajax')  ;
  $filter = array(
        'myflows' => true,
        );
  $rows = maestro_get_all_flows_data($filter);  //user as a filter
  return theme('maestro_all_flows', array('ajax_url' => $ajax_url , 'filter_url' => $ajax_url . '/myflows', 'database_result_set' => $rows));
}

//filters is a keyed array where the key is what to search for, value is what to filter on
function maestro_get_all_flows_data($filters = NULL) {
  global $user;

  $query = db_select('maestro_projects', 'a')->extend('PagerDefault');
  $query->fields('a', array('originator_uid', 'description', 'id'));
  $query->fields('c', array('name'));
  $query->join('maestro_process', 'b', 'a.id = b.tracking_id');
  $query->join('users', 'c', 'a.originator_uid = c.uid');
  $query->join('maestro_queue', 'd', 'b.id = d.process_id');
  $query->join('users', 'e', 'd.uid = e.uid');
  $query->groupBy('a.originator_uid');
  $query->groupBy('a.description');
  $query->groupBy('a.id');
  $query->groupBy('c.name');
  //RK -- commented out the next 2 lines as they were destroying the SQL Server output for all flows
  //$query->groupBy('d.id');
  //$query->orderBy('d.id','DESC');
  //$query->limit(1);

  if(is_array($filters)) {
    foreach($filters as $key => $filter) {
      if(isset($filter) && $filter !=''){

        switch($key) {
          case 'userid':
            $query->condition(db_or()->condition('a.originator_uid', intval($filter))->condition('e.uid', intval($filter)));
            break;

          case 'projectname':
            $query->condition('a.description', '%' . $filter . '%', 'LIKE');
            break;

          case 'username':
            $query->condition(db_or()->condition('c.name', $filter)->condition('e.name', $filter));
            break;

          case 'myflows':  //this is a pseudo filter to force on the right filtering for the query to isolate the flows ONLY for that user
            $query->condition(db_or()->condition('a.originator_uid', intval($user->uid))->condition('e.uid', intval($user->uid)));
            break;
        }
      }
    }
  }
  $res = $query->execute();
  return $res;
}

