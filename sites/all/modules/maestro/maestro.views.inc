<?php

// $Id:

/**
 * @file
 * maestro.views.inc
 * Contains all Views related API calls and default view creation information
 */

/**
 * Implementation of hook_views_api().
*/
function maestro_views_api() {
  return array(
    'api' => 2.0,
  );
}



function maestro_views_data() {
  $data = array();
  $data['maestro_queue']['table']['group']  = t('Maestro Queue');
  $data['maestro_template_data']['table']['group']  = t('Maestro Template Data');
  $data['maestro_template']['table']['group']  = t('Maestro Template');
  $data['maestro_production_assignments']['table']['group']  = t('Maestro Assignments');

   //table definitions

  //template
  $data['maestro_template']['table']['base'] = array(
    'field' => 'id',
    'title' => t('Maestro Template'),
    'help' => t("Stores all master template records."),
    'weight' => -13,
  );

  $data['maestro_template']['table']['join']['maestro_queue'] = array(  //this one allows the template data fields to be available in the queue table view
      'left_table' => 'maestro_template_data',
      'left_field' => 'template_id',
      'field' => 'id',
  );



  //template data
  $data['maestro_template_data']['table']['base'] = array(
    'field' => 'id',
    'title' => t('Maestro Template Data'),
    'help' => t("Holds all of the Templated tasks."),
    'weight' => -10,
  );

  $data['maestro_template_data']['table']['join'] = array(  //this one allows the template data fields to be available in the queue table view
    'maestro_queue' => array(
      'left_field' => 'template_data_id',
      'field' => 'id',
    ),
  );


  //queue
  $data['maestro_queue']['table']['base'] = array(
    'field' => 'id',
    'title' => t('Maestro Queue'),
    'help' => t("Queue holds all of the production data for tasks."),
    'weight' => -11,
  );

  $data['users']['table']['join'] = array(  //telling the users table that we're joined to it by uid
    'maestro_queue' => array(
      'left_field' => 'uid',
      'field' => 'uid',
    ),
  );

  //production assignments
  $data['maestro_production_assignments']['table']['base'] = array(
    'field' => 'id',
    'title' => t('Maestro Production Assignments'),
    'help' => t("Maestro's store for production assignments."),
    'weight' => -12,
  );

  $data['maestro_production_assignments']['table']['join'] = array(  //telling the queue table that we're joined to it by taskID
    'maestro_queue' => array(
      'left_field' => 'id',
      'field' => 'task_id',
    ),
  );


  //*****Field definitions******
  $data['maestro_template']['template_name'] = array(
    'title' => t('Template Name'),
    'help' => t('Name of the Template'),
    'field' => array(
      'click sortable' => TRUE,
    ),
  );

  $data['maestro_template_data']['taskname'] = array(
    'title' => t('Task Name'),
    'help' => t('A Reference to the template data table.'),
    'field' => array(
      //'handler' =>
      'click sortable' => TRUE,
    ),
  );

  $data['maestro_queue']['assigned_users'] = array(
    'real field' => 'id',
    'title' => t('Assigned Users'),
    'help' => t('All of the assigned users'),
    'field' => array (
      'handler' => 'views_handler_field_maestro_assigned_users',
      'click sortable' => TRUE,
    ),
  );

  $data['maestro_queue']['template_data_id'] = array(
    'title' => t('Template Data ID'),
    'help' => t('A Reference to the template data table.'),
    'field' => array (
      'handler' => 'views_handler_field_numeric',
      'click sortable' => TRUE,
    ),
  );

  //INT fields here:
  $data['maestro_queue']['process_id'] = array (
    'title' => t('Process ID'),
    'help'  => t('Unique Process Identifier.'),
    'field' => array (
      'handler' => 'views_handler_field_numeric',
      'click sortable' => TRUE,
    ),
    'filter' => array(
      'handler' => 'views_handler_filter_numeric',
    ),
    'sort' => array(
      'handler' => 'views_handler_sort',
    ),
  );

  $data['maestro_queue']['engine_version'] = array (
    'title' => t('Engine Version'),
    'help'  => t('Version of the engine used to run this task.'),
    'field' => array (
      'handler' => 'views_handler_field_numeric',
      'click sortable' => TRUE,
    ),
    'filter' => array(
      'handler' => 'views_handler_filter_numeric',
    ),
    'sort' => array(
      'handler' => 'views_handler_sort',
    ),
  );

  $data['maestro_queue']['is_interactive'] = array (
    'title' => t('Interactive Flag'),
    'help'  => t('Set to 1 for interactive. 0 for non-interactive.'),
    'field' => array (
      'handler' => 'views_handler_field_numeric',
      'click sortable' => TRUE,
    ),
    'filter' => array(
      'handler' => 'views_handler_filter_numeric',
    ),
    'sort' => array(
      'handler' => 'views_handler_sort',
    ),
  );

    $data['maestro_queue']['status'] = array (
    'title' => t('Status flag'),
    'help'  => t('Textual Status flag.'),
    'field' => array (
      'handler' => 'views_handler_field_maestro_status',  //using a custom field handler
      'click sortable' => TRUE,
    ),
    'filter' => array(
      'handler' => 'views_handler_filter_numeric',
    ),
    'sort' => array(
      'handler' => 'views_handler_sort',
    ),
  );

  $data['maestro_queue']['uid'] = array (
    'title' => t('Completed By'),
    'help'  => t('The user who completed the task.'),
    'field' => array (
      'handler' => 'views_handler_field_maestro_uid',  //using a custom field handler
      'click sortable' => TRUE,
    ),
    'filter' => array(
      'handler' => 'views_handler_filter_numeric',
    ),
    'sort' => array(
      'handler' => 'views_handler_sort',
    ),
  );




  return $data;
}



/**
 * Field handler to display the Maestro Status
 */
class views_handler_field_maestro_status extends views_handler_field_numeric {

  function render($values) {
    global $base_path;
    @include_once($base_path . drupal_get_path('module','maestro') . '/maestro_contatnts.class.php');
    $value = $values->{$this->field_alias};
    return MaestroTaskStatusCodes::getStatusLabel($value);
  }
}

/**
 * Field handler to display the Completed User
 */
class views_handler_field_maestro_uid extends views_handler_field_numeric {
  function render($values) {
    $value = $values->{$this->field_alias};
    if ($value==0 && isset($value)) {
      return 'Maestro';
    }
    elseif (!isset($value)) {
      return t('Not yet completed');
    }
    else {
      return $value;
    }
  }
}


/**
 * Field handler to display the assigned users
 */
class views_handler_field_maestro_assigned_users extends views_handler_field_numeric {
  function render($values) {
    $value = $values->{$this->field_alias};
    //return $value;
    //@TODO:  need to fix this method once the OG/role ability is rolled out
    $q = db_select('maestro_production_assignments', 'a');
    $q->join('users', 'b', 'a.uid = b.uid');
    $q->addField('a','uid');
    $q->addField('b','mail');
    $q->addField('b','name');
    $q->condition('a.task_id',intval($value));
    $res = $q->execute();
    $output = "";
    foreach($res as $record) {
      if($output != '') $output .= "<br>";
      $output .="{$record->name}";
    }
    if($output == '') {
      $output = t('Maestro');
    }
    return $output;
  }
}
/**
 * Implements hook_views_default_views().
 */
function maestro_views_default_views() {
  $view = new view;
  $view->name = 'All Queue Tasks';
  $view->description = 'Sample View to show off all in queue tasks';
  $view->tag = 'maestro';
  $view->view_php = '';
  $view->base_table = 'maestro_queue';
  $view->is_cacheable = FALSE;
  $view->api_version = '3.0-alpha1';
  $view->disabled = FALSE; /* Edit this to true to make a default view disabled initially */

  /* Display: Defaults */
  $handler = $view->new_display('default', 'Defaults', 'default');
  $handler->display->display_options['title'] = 'All Queue Tasks';
  $handler->display->display_options['access']['type'] = 'none';
  $handler->display->display_options['cache']['type'] = 'none';
  $handler->display->display_options['query']['type'] = 'views_query';
  $handler->display->display_options['exposed_form']['type'] = 'basic';
  $handler->display->display_options['pager']['type'] = 'full';
  $handler->display->display_options['pager']['options']['items_per_page'] = '50';
  $handler->display->display_options['pager']['options']['offset'] = '0';
  $handler->display->display_options['pager']['options']['id'] = '0';
  $handler->display->display_options['style_plugin'] = 'table';
  $handler->display->display_options['style_options']['columns'] = array(
    'process_id' => 'process_id',
    'status' => 'status',
    'taskname' => 'taskname',
    'uid' => 'uid',
    'template_name' => 'template_name',
  );
  $handler->display->display_options['style_options']['default'] = '-1';
  $handler->display->display_options['style_options']['info'] = array(
    'process_id' => array(
      'sortable' => 1,
      'align' => '',
      'separator' => '',
    ),
    'status' => array(
      'sortable' => 1,
      'align' => '',
      'separator' => '',
    ),
    'taskname' => array(
      'sortable' => 1,
      'align' => '',
      'separator' => '',
    ),
    'uid' => array(
      'sortable' => 1,
      'align' => '',
      'separator' => '',
    ),
    'template_name' => array(
      'sortable' => 1,
      'align' => '',
      'separator' => '',
    ),
  );
  $handler->display->display_options['style_options']['override'] = 1;
  $handler->display->display_options['style_options']['sticky'] = 0;
  /* Field: Maestro Queue: Process ID */
  $handler->display->display_options['fields']['process_id']['id'] = 'process_id';
  $handler->display->display_options['fields']['process_id']['table'] = 'maestro_queue';
  $handler->display->display_options['fields']['process_id']['field'] = 'process_id';
  /* Field: Maestro Queue: Status flag */
  $handler->display->display_options['fields']['status']['id'] = 'status';
  $handler->display->display_options['fields']['status']['table'] = 'maestro_queue';
  $handler->display->display_options['fields']['status']['field'] = 'status';
  /* Field: Maestro Template Data: Task Name */
  $handler->display->display_options['fields']['taskname']['id'] = 'taskname';
  $handler->display->display_options['fields']['taskname']['table'] = 'maestro_template_data';
  $handler->display->display_options['fields']['taskname']['field'] = 'taskname';
  /* Field: Maestro Queue: Completed By */
  $handler->display->display_options['fields']['uid']['id'] = 'uid';
  $handler->display->display_options['fields']['uid']['table'] = 'maestro_queue';
  $handler->display->display_options['fields']['uid']['field'] = 'uid';
  /* Field: Maestro Template: Template Name */
  $handler->display->display_options['fields']['template_name']['id'] = 'template_name';
  $handler->display->display_options['fields']['template_name']['table'] = 'maestro_template';
  $handler->display->display_options['fields']['template_name']['field'] = 'template_name';
  /* Field: Maestro Queue: Assigned Users */
  $handler->display->display_options['fields']['assigned_users']['id'] = 'assigned_users';
  $handler->display->display_options['fields']['assigned_users']['table'] = 'maestro_queue';
  $handler->display->display_options['fields']['assigned_users']['field'] = 'assigned_users';

  /* Display: All Queue Tasks */
  $handler = $view->new_display('page', 'All Queue Tasks', 'all_queue_tasks');
  $handler->display->display_options['defaults']['fields'] = FALSE;
  /* Field: Maestro Queue: Process ID */
  $handler->display->display_options['fields']['process_id']['id'] = 'process_id';
  $handler->display->display_options['fields']['process_id']['table'] = 'maestro_queue';
  $handler->display->display_options['fields']['process_id']['field'] = 'process_id';
  /* Field: Maestro Queue: Status flag */
  $handler->display->display_options['fields']['status']['id'] = 'status';
  $handler->display->display_options['fields']['status']['table'] = 'maestro_queue';
  $handler->display->display_options['fields']['status']['field'] = 'status';
  /* Field: Maestro Template Data: Task Name */
  $handler->display->display_options['fields']['taskname']['id'] = 'taskname';
  $handler->display->display_options['fields']['taskname']['table'] = 'maestro_template_data';
  $handler->display->display_options['fields']['taskname']['field'] = 'taskname';
  /* Field: Maestro Queue: Completed By */
  $handler->display->display_options['fields']['uid']['id'] = 'uid';
  $handler->display->display_options['fields']['uid']['table'] = 'maestro_queue';
  $handler->display->display_options['fields']['uid']['field'] = 'uid';
  /* Field: Maestro Template: Template Name */
  $handler->display->display_options['fields']['template_name']['id'] = 'template_name';
  $handler->display->display_options['fields']['template_name']['table'] = 'maestro_template';
  $handler->display->display_options['fields']['template_name']['field'] = 'template_name';
  /* Field: Maestro Queue: Assigned Users */
  $handler->display->display_options['fields']['assigned_users']['id'] = 'assigned_users';
  $handler->display->display_options['fields']['assigned_users']['table'] = 'maestro_queue';
  $handler->display->display_options['fields']['assigned_users']['field'] = 'assigned_users';
  $handler->display->display_options['path'] = 'all_queue_tasks';
  $views[$view->name] = $view;
  return $views;
}