<?php

include_once('maestro_constants.class.php');

abstract class MaestroTask {
  public $_properties = NULL;
  protected $_message = NULL;
  protected $_lastTestStatus = 0;
  public $executionStatus = NULL;   // Did task's execute method execute of was there an error
  public $completionStatus = NULL;  // Did the task's execution method complete and if so set to one of the defined status code CONST values

  function __construct($properties = NULL) {
    $this->_properties = $properties;
  }


  /* execute: Nothing much for an interactiveTask to do in the execute method.
   * For Interactive tasks, we will want to return an executionStatus of FALSE as this task
   * is really executed from the task console by the user.
   * The defined function for this task will execute and present the task to the user in the task console.
   * The taskconsole will call the processInteractiveTask method for this task type.
   * It's up to the defined interactiveTask function to complete the task.
   */
  abstract function execute ();


  /* prepareTask: Opportunity to set task specific data that will be used to create the queue record
   Specifically, the task handler and task_data fields - which is a serialized array of task specific options/data
   @retval:  associative array (handler => varchar, task_data => serialized array)
   */
  abstract function prepareTask ();

  function showInteractiveTask() {
    return FALSE;
  }

  function getTaskConsoleURL(){
    return "#";
  }

  /* Method called by maestro_node_insert to handle tracking of the node record
   * The contentType task implements this method for example to handle tracking
   * of the node that is created when task is executed
   */
  function processContent() {
  }

  /* Method called by taskconsole when task is expanded to show project details
   * Allows tasks to return any tracked content such as links to nodes for view/edit
   */
  function showContentDetail() {
    return '';
  }

  function setMessage($msg) {
    $this->_message = $msg;
  }

  function getMessage() {
    return $this->_message;
  }

  function getLastTestStatus() {
    return $this->_lastTestStatus;
  }

  function setLastTestStatus($setval) {
    $this->_lastTestStatus = $setval;
  }

  function saveTempData($data) {
    if ($this->_properties->queue_id > 0) {
      db_update('maestro_queue')
      ->fields(array('temp_data' => serialize($data)))
      ->condition('id', $this->_properties->queue_id, '=')
      ->execute();
    }
  }

  function getTempData() {
    if ($this->_properties->queue_id > 0) {
      $data = db_query("SELECT temp_data FROM {maestro_queue} WHERE id = :tid",
      array(':tid' => $this->_properties->queue_id))->fetchField();
      $retval = unserialize($data);
      return $retval;
    }
  }

  /*
   * function saveProjectData saves project data to the project content table.
   * it uses the content type and tracking id so the data can be brought back for other tasks
   * in the workflow with the same identifiers.
   *
   * array $data - an array containing the data to be saved. structure should be array ('var_name' => 'var_value')
   * int $tracking_id - the tracking id of this project
   * string $content_type - a string containing the content type of the data you wish to store
   */
  function saveProjectData($data, $tracking_id, $content_type) {
    global $user;

    $query = db_select('maestro_project_content', 'a');
    $query->fields('a', array('id', 'nid', 'tracking_id', 'task_id', 'instance', 'content_type', 'task_data', 'created_by_uid', 'status'));
    $query->condition('a.tracking_id', $tracking_id, '=');
    $query->condition('a.content_type', $content_type, '=');
    $rec = $query->execute()->fetchObject();

    if ($rec === FALSE) {
      $rec = new stdClass();
      $rec->nid = 0;
      $rec->tracking_id = $tracking_id;
      $rec->task_id = $this->_properties->queue_id;
      $rec->instance = 1;
      $rec->content_type = $content_type;
      $rec->task_data = serialize($data);
      $rec->created_by_uid = $user->uid;
      $rec->status = 1; //@TODO: add the project content status code to the constants class, and properly update the status throughout the logic
      drupal_write_record('maestro_project_content', $rec);
    }
    else {
      $rec->task_data = serialize($data);
      drupal_write_record('maestro_project_content', $rec, array('id'));
    }
  }

  function getProjectData($tracking_id, $content_type) {
    $query = db_select('maestro_project_content', 'a');
    $query->fields('a', array('task_data'));
    $query->condition('a.tracking_id', $tracking_id, '=');
    $query->condition('a.content_type', $content_type, '=');
    $rec = $query->execute()->fetchObject();

    if ($rec === FALSE) {
      return $rec;
    }
    else {
      return unserialize($rec->task_data);
    }
  }

  function setRunOnceFlag($task_id) {
    $task_id = intval($task_id);
    db_update('maestro_queue')
    ->fields(array('run_once' => 1))
    ->condition('id', $task_id, '=')
    ->execute();
  }

  function setTaskStartedDate($task_id) {
    $task_id = intval($task_id);
    db_update('maestro_queue')
    ->fields(array('started_date' => time()))
    ->condition('id', $task_id, '=')
    ->execute();
  }
}


// Classes can be in their own file or library and included via several options

class MaestroTaskTypeStart extends MaestroTask {

  function execute() {
    $this->setTaskStartedDate($this->_properties->id);
    $this->executionStatus = TRUE;
    $this->completionStatus = MaestroTaskStatusCodes::STATUS_COMPLETE;
    return $this;
  }

  function prepareTask() {}


}

class MaestroTaskTypeEnd extends MaestroTask {

  function execute() {
    $this->setTaskStartedDate($this->_properties->id);
    $this->executionStatus = TRUE;
    $this->completionStatus = MaestroTaskStatusCodes::STATUS_COMPLETE;
    return $this;
  }

  function prepareTask() {}

}


class MaestroTaskTypeBatch extends MaestroTask {

  function execute() {
    $success = FALSE;
    $current_path=variable_get('maestro_batch_script_location',drupal_get_path('module','maestro') . "/batch/");

    if (file_exists($current_path . $this->_properties->handler)) {
      require($current_path . $this->_properties->handler );
    } elseif (file_exists($this->_properties->handler)) {  // Check in current directory
      require($this->_properties->handler);
    }
    //Assumption made here that the $success variable is set by the batch task.
    if ($success) {
      $this->completionStatus = MaestroTaskStatusCodes::STATUS_COMPLETE;
    }
    else {
      $this->completionStatus = FALSE;
    }
    $this->setTaskStartedDate($this->_properties->id);
    $this->executionStatus = TRUE;
    return $this;
  }

  function prepareTask() {
    $serializedData = db_query("SELECT task_data FROM {maestro_template_data} WHERE id = :tid",
    array(':tid' => $this->_properties->taskid))->fetchField();
    $taskdata = @unserialize($serializedData);
    return array('handler' => $taskdata['handler'],'serialized_data' => $serializedData);
  }
}

class MaestroTaskTypeBatchFunction extends MaestroTask {

  function execute() {
    $success = FALSE;

    $function = $this->_properties->handler;
    if (function_exists($function)) {
      $this->setTaskStartedDate($this->_properties->id);
      $success = $function($this->_properties->id,$this->_properties->process_id);
    } else {
      watchdog('maestro',"MaestroTaskTypeBatchFunction - unable to find the function: {$this->_properties->handler}");
    }
    // Assumption made here that the $success variable is set by the batch task.
    if ($success) {
      $this->completionStatus = MaestroTaskStatusCodes::STATUS_COMPLETE;
    }
    else {
      $this->completionStatus = FALSE;
    }
    $this->executionStatus = TRUE;
    return $this;
  }

  function prepareTask() {
    $serializedData = db_query("SELECT task_data FROM {maestro_template_data} WHERE id = :tid",
    array(':tid' => $this->_properties->taskid))->fetchField();
    $taskdata = @unserialize($serializedData);
    return array('handler' => $taskdata['handler'],'serialized_data' => $serializedData);
  }

}


class MaestroTaskTypeAnd extends MaestroTask {

  function execute() {
    $this->setTaskStartedDate($this->_properties->id);
    $numComplete = 0;
    $numIncomplete = 0;

    $query = db_select('maestro_queue', 'a');
    $query->join('maestro_template_data_next_step', 'b', 'a.template_data_id = b.template_data_to OR a.template_data_id=b.template_data_to_false');
    $query->addExpression('COUNT(a.id)','templatecount');
    $query->condition("a.id",$this->_properties->id,"=");
    $numComplete = $query->execute()->fetchObject();

    $query = db_select('maestro_queue_from', 'a');
    $query->join('maestro_queue', 'b', 'a.from_queue_id = b.id');
    $query->addExpression('COUNT(a.id)','processcount');
    $query->condition(db_and()->condition("a.queue_id",$this->_properties->id,"=")->condition("b.process_id",$this->_properties->process_id,"="));
    $numIncomplete = $query->execute()->fetchObject();

    // sounds confusing, but if the processCount is greater than the completed ones, we're ok too
    $this->executionStatus = TRUE;
    if ($numIncomplete->processcount == $numComplete->templatecount || $numIncomplete->processcount > $numComplete->templatecount ) {
      // All of the incoming items done for this AND we can now carry out updating this queue item's information
      $this->completionStatus = MaestroTaskStatusCodes::STATUS_COMPLETE;
    } else {
      // Not all the incomings for the AND are done - can not complete task yet
      $this->completionStatus = FALSE;
      $this->executionStatus = FALSE;
    }
    return $this;
  }

  function prepareTask() {}

}


class MaestroTaskTypeIf extends MaestroTask {

  function execute() {
    $this->setTaskStartedDate($this->_properties->id);

    $serializedData = db_query("SELECT task_data FROM {maestro_queue} WHERE id = :tid",
    array(':tid' => $this->_properties->id))->fetchField();
    $taskdata = @unserialize($serializedData);

    $templateVariableID = $taskdata['if_argument_variable'];
    $operator = $taskdata['if_operator'];
    $ifValue = $taskdata['if_value'];
    $ifArgumentProcess = $taskdata['if_process_arguments'];

    if ($templateVariableID == null or $templateVariableID == '' ) { // logical entry it is
      //this is a logical entry.  that is, not using a variable.. need to see what the last task's status is.
      $query = db_select('maestro_queue_from', 'a');
      $query->join('maestro_queue','b','a.from_queue_id=b.id');
      $query->fields('b', array('status'));
      $query->condition("a.queue_id", $this->_properties->id,"=");
      $res = $query->execute();
      $row=$res->fetchObject();
      $lastStatus = intval($row->status);
      $useTrueBranch = null;
      switch (strtolower($ifArgumentProcess) ) {
        case 'lasttasksuccess':
          if ($lastStatus == 0 or $lastStatus == 1) {
            $useTrueBranch = TRUE;
          }
          else {
            $useTrueBranch = FALSE;
          }
          break;
        case 'lasttaskcancel':
          if ($lastStatus == 3) {
            $useTrueBranch = TRUE;
          }
          else {
            $useTrueBranch = FALSE;
          }
          break;
        case 'lasttaskhold':
          if ($lastStatus == 2) {
            $useTrueBranch = TRUE;
          }
          else {
            $useTrueBranch = FALSE;
          }
          break;
        case 'lasttaskaborted':
          if ($lastStatus == 3) {
            $useTrueBranch = TRUE;
          }
          else {
            $useTrueBranch = FALSE;
          }
          break;
      }
    }
    else {    // variableID it is - we're using a variable for testing the IF condition

      /* need to perform a variable to value operation based on the selected operation!
       * $templateVariableID ,$operator ,$ifValue, $processID
       * need to select the process variable using the ID from the current process
       */
      $query = db_select('maestro_process_variables', 'a');
      $query->fields('a',array('variable_value'));
      $query->condition(db_and()->condition("a.process_id",$this->_properties->process_id)->condition('a.template_variable_id',$templateVariableID));
      $ifRes = $query->execute();
      $ifQueryNumRows = $query->countQuery()->execute()->fetchField();
      if ($ifQueryNumRows > 0 ) {
        $ifArray = $ifRes->fetchObject();
        $variableValue = $ifArray->variable_value;
        switch ($operator ) {
          case '=':
            if ($variableValue == $ifValue ) {
              $useTrueBranch = TRUE;
            } else {
              $useTrueBranch = FALSE;
            }
            break;
          case '<':
            if ($variableValue < $ifValue ) {
              $useTrueBranch = TRUE;
            } else {
              $useTrueBranch = FALSE;
            }
            break;
          case '>':
            if ($variableValue > $ifValue ) {
              $useTrueBranch = TRUE;
            } else {
              $useTrueBranch = FALSE;
            }
            break;
          case '!=':
            if ($variableValue != $ifValue ) {
              $useTrueBranch = TRUE;
            } else {
              $useTrueBranch = FALSE;
            }

            break;
        }
      }
      else { // force the branch to the false side since the variable does not exist...
        $useTrueBranch = FALSE;
      }

    }

    if ($useTrueBranch === TRUE ) {  // point to the true branch
      // This task completed successfully but we want to signal to the engine the condition it was testing
      // for should use the default workflow path in the engines->nextStep method
      $this->_lastTestStatus = MaestroTaskStatusCodes::STATUS_COMPLETE;
      $this->executionStatus = TRUE;
      $this->completionStatus = MaestroTaskStatusCodes::STATUS_COMPLETE;
    }
    else if ($useTrueBranch === FALSE) { // point to the false branch
      // This task completed successfully but we want to signal to the engine the condition it was testing
      // for should branching to the alternate workflow path in the engines->nextStep method
      $this->_lastTestStatus = MaestroTaskStatusCodes::STATUS_IF_CONDITION_FALSE;
      $this->executionStatus = TRUE;
      $this->completionStatus = MaestroTaskStatusCodes::STATUS_COMPLETE;
    } else {   // We have an unexpected situation - so flag a task error
      $this->executionStatus = FALSE;
    }

    return $this;
  }

  function prepareTask() {
    $serializedData = db_query("SELECT task_data FROM {maestro_template_data} WHERE id = :tid",
    array(':tid' => $this->_properties->taskid))->fetchField();
    $taskdata = @unserialize($serializedData);
    return array('handler' => '' ,'serialized_data' => $serializedData);
  }


}

class MaestroTaskTypeInteractiveFunction extends MaestroTask {

  function execute() {
    /* Nothing much for an interactiveTask to do in the execute method.
     * We want to return an executionStatus of FALSE as this task is really executed from the task console by the user.
     * The defined function for this task will execute and present the task to the user in the task console.
     * The taskconsole will call the processInteractiveTask method for this task type.
     * It's up to the defined interactiveTask function to complete the task.
     */
    $this->setRunOnceFlag($this->_properties->id);
    $this->completionStatus = FALSE;
    $this->executionStatus = TRUE;
    return $this;
  }

  function prepareTask() {
    $serializedData = db_query("SELECT task_data FROM {maestro_template_data} WHERE id = :tid",
    array(':tid' => $this->_properties->taskid))->fetchField();
    $taskdata = @unserialize($serializedData);
    return array('handler' => $taskdata['handler'],'serialized_data' => $serializedData);
  }

  function showInteractiveTask() {
    $retval = '';
    $serializedData = db_query("SELECT task_data FROM {maestro_queue} WHERE id = :id",
    array(':id' => $this->_properties->queue_id))->fetchField();
    $taskdata = @unserialize($serializedData);
    if (function_exists($taskdata['handler'])) {
      $ret = $taskdata['handler']('display',$this,$taskdata['optional_parm']);
      if ($ret->retcode === TRUE) {
        $retval = $ret->html;
      }
    } else {
      $retval = '<div style="text-align:center;margin:5px;padding:10px;border:1px solid #CCC;font-size:14pt;">';
      $retval .= t('Interactive Function "@taskname" was  not found.',array('@taskname' => $taskdata['handler']));
      $retval .= '</div>';
    }
    return $retval;
  }

  function processInteractiveTask($taskid,$taskop) {
    $ret = new stdClass();
    $ret->retcode = FALSE;
    $ret->engineop = '';
    $serializedData = db_query("SELECT task_data FROM {maestro_queue} WHERE id = :id",
    array(':id' => $taskid))->fetchField();
    $taskdata = @unserialize($serializedData);
    if (function_exists($taskdata['handler'])) {
      $ret = $taskdata['handler']($taskop,$this,$taskdata['optional_parm']);
    }
    return $ret;
  }

}



class MaestroTaskTypeSetProcessVariable extends MaestroTask {

  function execute() {
    $this->executionStatus = FALSE;
    $this->setTaskStartedDate($this->_properties->id);
    $query = db_select('maestro_template_data', 'a');
    $query->fields('a',array('task_data'));
    $query->condition('a.id', $this->_properties->template_data_id,'=');
    $taskDefinitionRec = $query->execute()->fetchObject();

    if ($taskDefinitionRec) {   // Needs to be valid variable to set
      $taskDefinitionRec->task_data = unserialize($taskDefinitionRec->task_data);
      if ($taskDefinitionRec->task_data['var_to_set'] > 0) {
        $query = db_select('maestro_process_variables', 'a');
        $query->addField('a','variable_value');
        $query->condition('a.process_id', $this->_properties->process_id,'=');
        $query->condition('a.template_variable_id', $taskDefinitionRec->task_data['var_to_set'],'=');
        $curvalue = $query->execute()->fetchField();
        $methods = $this->getSetMethods();
        $function = $methods[$taskDefinitionRec->task_data['set_type']]['engine_handler'];
        if (function_exists($function)) {
          $setvalue = $function($this, $curvalue, $taskDefinitionRec->task_data[$taskDefinitionRec->task_data['set_type'] . '_value']);
          if ($setvalue === FALSE OR $setvalue == NULL) {
            $this->executionStatus = FALSE;
          } else {
            db_update('maestro_process_variables')
            ->fields(array('variable_value' => $setvalue))
            ->condition('process_id', $this->_properties->process_id, '=')
            ->condition('template_variable_id', $taskDefinitionRec->task_data['var_to_set'], '=')
            ->execute();
            $this->executionStatus = TRUE;
          }
        }
        $query = db_select('maestro_process_variables', 'a');
        $query->addField('a','variable_value');
        $query->condition('a.process_id', $this->_properties->process_id,'=');
        $query->condition(db_and()->condition('a.template_variable_id', $taskDefinitionRec->task_data['var_to_set'],'='));
        $varvalue = $query->execute()->fetchField();
        if ($varvalue == $setvalue) $this->completionStatus = MaestroTaskStatusCodes::STATUS_COMPLETE;
      }
    }

    return $this;
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

  function prepareTask() {}

}


class MaestroTaskTypeManualWeb extends MaestroTask {

  function execute() {
    /* Nothing much for us to do for this interactiveTask in the execute method.
     * We want to return an executionStatus of FALSE as this task is really executed from the task console by the user.
     * The user will be redirected to create the defined piece of content.
     * We have a hook_node_insert method that will trigger a completeTask to tell the masesto engine
     * this task is now complete and it can be archived and crank the engine forward for this w/f instance (process).
     */
    $this->completionStatus = FALSE;
    $this->executionStatus = TRUE;
    $this->setRunOnceFlag($this->_properties->id);
    $this->setTaskStartedDate($this->_properties->id);
    return $this;
  }

  function getTaskConsoleURL(){
    global $base_url;

    $prop=unserialize($this->_properties->task_data);
    $url = $prop['handler'];
    $url=str_replace('[site_url]',$base_url,$url);

    if(strpos($url, "?")) {
      $url .= "&queueid=" . $this->_properties->queue_id;
    }
    else {
      $url .= "?queueid=" . $this->_properties->queue_id;
    }
    return $url;
  }

  function prepareTask() {
    $serializedData = db_query("SELECT task_data FROM {maestro_template_data} WHERE id = :tid",
    array(':tid' => $this->_properties->taskid))->fetchField();
    $taskdata = @unserialize($serializedData);
    return array('handler' => $taskdata['handler'],'serialized_data' => $serializedData);
  }
}

class MaestroTaskTypeContentType extends MaestroTask {

  function execute() {
    // Check to see if the current status has been set to 1.
    // If so, completion status is set to true to complete the task.

    if($this->_properties->status == 1) {
      $this->completionStatus = MaestroTaskStatusCodes::STATUS_COMPLETE;  //just complete it!
    }
    else {
      $this->completionStatus = FALSE;
      $this->setMessage( 'Conent Type task -- status is 0.  Will not complete this task yet.');
    }
    $this->executionStatus = TRUE;
    $this->setRunOnceFlag($this->_properties->id);
    $this->setTaskStartedDate($this->_properties->id);
    return $this;
  }

  function getTaskConsoleURL(){
    global $base_url;
    $taskdata = unserialize($this->_properties->task_data);
    /* Drupal wants to see all underscores in content type names as hyphens for URL's
     * so we need to test for that and update that for any URL link
     */
    $content_type = str_replace('_','-',$taskdata['content_type']);
    $tracking_id = db_select('maestro_process')
    ->fields('maestro_process', array('tracking_id'))
    ->condition('id', $this->_properties->process_id, '=')
    ->execute()->fetchField();

    // Check and see if there is already a record for this content type in our workflow instance - Determine the content nid
    $query = db_select('maestro_project_content', 'a');
    $query->addField('a','nid');
    $query->condition('a.instance', 1,'=');
    $query->condition('a.tracking_id', $tracking_id,'=');
    $query->condition(db_and()->condition('a.content_type', $taskdata['content_type'],'='));
    $nid = $query->execute()->fetchField();

    if ($nid > 0) {
      $query = db_select('maestro_project_content', 'a');
      $query->addField('a','nid');
      $query->condition('a.instance', 1,'=');
      $query->condition('a.tracking_id', $tracking_id,'=');
      $query->condition(db_and()->condition('a.content_type', $taskdata['content_type'],'='));
      $nid = $query->execute()->fetchField();
      if (variable_get('clean_url')) {
        $url = $base_url . "/node/$nid/edit/maestro/edit/{$this->_properties->queue_id}/completeonsubmit/";
      }
      else {
        $url = $base_url . "/index.php?q=node/$nid/edit/maestro/edit/{$this->_properties->queue_id}/completeonsubmit/";
      }
    } else {
      if (variable_get('clean_url')) {
        $url = $base_url . "/node/add/{$content_type}/maestro/{$this->_properties->queue_id}/";
      }
      else {
        $url = url($base_url . "/index.php?q=node/add/{$content_type}/maestro/{$this->_properties->queue_id}");
      }
    }
    return $url;
  }

  function prepareTask() {
    $serializedData = db_query("SELECT task_data FROM {maestro_template_data} WHERE id = :tid",
    array(':tid' => $this->_properties->taskid))->fetchField();
    $taskdata = @unserialize($serializedData);
    return array('serialized_data' => $serializedData);
  }

  // Method called by maestro_node_insert to handle tracking of the node record */
  function processContent($taskid,$op,$object) {
    watchdog('maestro',"processContent function");
    $node = $object;  // For this task type, the object passed in, is the node object.
    $rec = db_select('maestro_queue')
    ->fields('maestro_queue', array('process_id','template_data_id'))
    ->condition('id', $node->maestro_taskid, '=')
    ->execute()->fetchObject();

    if ($node->status == 1) {
      $status = MaestroContentStatusCodes::STATUS_PUBLISHED;
    } else {
      $status = 0;
    }

    $tracking_id = db_select('maestro_process')
    ->fields('maestro_process', array('tracking_id'))
    ->condition('id', $rec->process_id, '=')
    ->execute()->fetchField();

    if ($op == 'insert') {
      db_insert('maestro_project_content')
      ->fields(array(
      'nid' => $node->nid,
      'tracking_id' => $tracking_id,
      'task_id' => $taskid,
      'content_type' => $node->type,
      'status'  => $status
      ))
      ->execute();

      // Initiate the mestro workflow engine and complete the task
      // Complete task is an engine method
      $maestro = Maestro::createMaestroObject(1);
      $maestro->engine()->completeTask($taskid);
    }
  }

  // Method to return HTML formatted content to include in the project detail area
  function showContentDetail($tracking_id,$task_id) {

    $retval = '';
    /* Format any content records */
    $query = db_select('maestro_project_content','content');
    $query->addField('content','nid');
    $query->addField('content','status');
    $query->condition('content.tracking_id',$tracking_id,'=');
    $query->condition('content.task_id',$task_id,'=');
    $res = $query->execute();
    foreach ($res as $record) {
      $node = node_load($record->nid);
      $variables['content_records'][$record->nid] = $node->title;
      $retval .= '<div>' . l($node->title, "node/{$record->nid}/maestro");
      $retval .= '<span style="padding-left:10px;">' . t('Status') . ': ';
      $retval .= t(MaestroContentStatusCodes::getStatusLabel($record->status));
      $retval .= '</span></div>';
    }
    return $retval;
  }

}

class MaestroTaskTypeFireTrigger extends MaestroTask {

  function execute() {
    $this->setTaskStartedDate($this->_properties->id);
    $aids = trigger_get_assigned_actions('fire_trigger_task' . $this->_properties->template_data_id);

    $context = array(
    'group' => 'maestro',
    'hook' => 'fire_trigger_task' . $this->_properties->template_data_id
    );

    actions_do(array_keys($aids), (object) $this->_properties, $context);

    $this->completionStatus = MaestroTaskStatusCodes::STATUS_COMPLETE;
    $this->executionStatus = TRUE;

    return $this;
  }

  function prepareTask() {}

}


