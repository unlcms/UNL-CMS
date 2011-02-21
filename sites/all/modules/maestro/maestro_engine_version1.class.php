<?php

/* Using Drupal OO Coding Standards as described: http://drupal.org/node/608152 */
include_once('maestro_task_interface.class.php');
class MaestroEngineVersion1 extends MaestroEngine {

  var $_version = '1.x';
  var $_properties;

  function __construct($options) {
    global $user;
    $this->_properties = $options;
    $this->_userId = $user->uid;
  }


  public function getVersion() {
    return $this->_version;
  }

  /* Generate a new process for a workflow template
   * @param $template:
   *   The workflow template id (int) - Mandatory
   *
   * @param $startoffset
   *   Optional paramater to launch the workflow process at other then the default task step 0.
   *   Also used if the process regeneration will not be at task 0 (automatically handled by engine)
   *
   * @param $pid
   *   Optional paramater Parent Process id. This is used when regenerating a process or
   *   if this new process should be a child process (or associated) with another workflow grouping (project)
   *
   * @param $useExistingGroupingId
   *   Optional BOOLEAN value (default FALSE) that if TRUE triggers the process related records to be grouped (related)
   *   as part of a project or related workflow grouping.
   *
   * @return
   *   The process id
   */

  function newProcess($template, $startoffset = null, $pid = null , $useExistingGroupingId = FALSE) {
    global $user;
    $queue_id_for_notifications = 0;

    // Retrieve the first step of the process and kick it off
    if ($startoffset == null ) {
      $query = db_select('maestro_template_data_next_step', 'a');
      $query->fields('a',array('template_data_from'));
      $query->fields('b',array('regen_all_live_tasks','show_in_detail','reminder_interval','task_class_name','is_interactive','task_data','handler'));
      $query->addField('b','id','taskid');
      $query->fields('c',array('template_name'));
      $query->join('maestro_template_data', 'b', 'a.template_data_from = b.id');     // default is an INNER JOIN
      $query->join('maestro_template', 'c', 'b.template_id = c.id');
      $query->condition('b.first_task',1,'=');
      $query->condition('c.id',$template,'=');
      $query->orderBy('template_data_from','ASC');
      $query->range(0,1);
    }
    else {
      // Retrieve the one queue record - where it is equal to the passed in start offset.
      $startoffset = intval($startoffset);
      $query = db_select('maestro_template_data','a');
      $query->addField('a','id','taskid');
      $query->fields('b',array('template_name'));
      $query->fields('a',array('regen_all_live_tasks','show_in_detail','reminder_interval','task_class_name','is_interactive','task_data','handler'));
      $query->join('maestro_template', 'b', 'b.id = a.template_id');
      $query->condition('a.id',$startoffset);
    }
    if ($this->_debug ) {
      watchdog('maestro','New process code executing');
    }

    // Only 1 record expected - query returns an array of object records
    $templaterec = current($query->execute()->fetchAll());

    if (!empty($templaterec->taskid)) {
      $pid = intval($pid);
      if ($pid > 0) {
        $flowname = db_query("SELECT flow_name FROM {maestro_process} WHERE id=$pid")->fetchField();
      }
      else {
        $flowname = db_query("SELECT template_name FROM {maestro_template} WHERE id=$template")->fetchField();
      }

      $process_record = new stdClass();
      $process_record->template_id = $template;
      $process_record->flow_name = $flowname;
      $process_record->complete = 0;
      $process_record->pid = $pid;
      $process_record->initiating_pid = $this->getParentProcessId($pid);
      $process_record->initiator_uid = $user->uid;
      $process_record->initiated_date = time();
      drupal_write_record('maestro_process',$process_record);
      $new_processid = $process_record->id;

      if ($process_record->id == 0) {
        watchdog('maestro', "New Process Code FAIL! - for template: $template");
        return FALSE;
      }
      if ($pid == 0) {
        $process_record->initiating_pid = $new_processid;
        drupal_write_record('maestro_process', $process_record, array('id'));
      }
      $this->setProcessId($new_processid);

      if ($templaterec->reminder_interval > 0) {
        $next_reminder_date = time() + $templaterec->reminder_interval;
      }
      else {
        $next_reminder_date = 0;
      }

      $queue_record = new stdClass();
      $queue_record->process_id = $new_processid;
      $queue_record->template_data_id = $templaterec->taskid;
      $queue_record->task_class_name = $templaterec->task_class_name;
      $queue_record->is_interactive = $templaterec->is_interactive;
      $queue_record->show_in_detail = $templaterec->show_in_detail;
      $queue_record->handler = $templaterec->handler;
      $queue_record->task_data = $templaterec->task_data;
      $queue_record->status = 0;
      $queue_record->archived = 0;
      $queue_record->engine_version = $this->_version;
      $queue_record->created_date = time();
      $queue_record->next_reminder_date = $next_reminder_date;
      // Instantiate the tasktype specific method to set the queue record task data
      $taskdata = $this->prepareTask(new $templaterec->task_class_name($templaterec));
      if (isset($taskdata) AND is_array($taskdata)) {
        if (isset($taskdata['handler'])) $queue_record->handler = $taskdata['handler'];
        if (isset($taskdata['serialized_data'])) $queue_record->task_data = $taskdata['serialized_data'];
      }
      drupal_write_record('maestro_queue',$queue_record);
      if ($queue_record->id == 0) {
        watchdog('maestro', "New Process Code FAIL! - Unexpected problem creating initial queue record for template: $template");
        return FALSE;
      }
      $queue_id_for_notifications = $queue_record->id;

      // Determine if the offset is set.. if so, update the original parent process record with a status of 2
      if (!empty($startoffset) AND !empty($pid)) {
        db_update('maestro_process')
        ->fields(array('complete' => MaestroProcessStatusCodes::STATUS_REGENERATED, 'completed_date' => time()))
        ->condition('id',$pid,'=')
        ->execute();

        // Within this section we need to detect whether or not the startoffset task has the "regenerate all live tasks" option set.
        // if so, the process we just layed to rest will hold some in-production tasks, and those tasks will have their pids set to the new pid.
        // @TODO: Need to test this condition -- RK to add more comments to explain what we are doing here and regen all vs not
        if($templaterec->regen_all_live_tasks == 1) {
          $q2 = db_select('maestro_queue','a');
          $q2->addField('a','id','id');
          $q2->join('maestro_template_data', 'b', 'a.template_data_id = b.id');
          $q2->condition('b.task_class_name','MaestroTaskTypeAnd');
          $q2->condition('a.process_id',$pid);
          $q2->condition(db_or()->condition('a.archived',0)->condition('a.archived',NULL));
          $active_queue_tasks_result = $q2->execute();
          foreach ($active_queue_tasks_result as $active_queue_record) {
            /* The maestro_queue_from table is used by the IF Task to test previous task's status if that's the condition to test
             * Also used to simplify later reporting of active tasks
             */
            $q3 = db_select('maesto_queue_from','a');
            $q3->addField('a','from_queue_id');
            $q3->condition("a.queue_id = {$active_queue_record->id}");
            $queue_reporting_result = $q3->execute();
            foreach ($queue_reporting_result as $queue_reporting_record) {
              $record = new stdClass();
              $record->id = $queue_reporting_record->from_queue_id;
              $record->process_id = $new_processid;
              drupal_write_record('maestro_queue',$record);
            }
            db_update('maestro_queue')
            ->fields(array('process_id' => $new_processid))
            ->condition('id', $active_queue_record->id)
            ->condition(db_or()->condition('archived',0)->condition('archived',NULL))
            ->execute();
          }
        }
        // Select the process variables for the parent and create new ones for the new process $this->_processId
        $pvquery = db_select('maestro_process_variables','a');
        $pvquery->addExpression($new_processid,'process_id');
        $pvquery->fields('a',array('variable_value','template_variable_id'));
        $pvquery->condition('a.process_id',$pid);
        db_insert('maestro_process_variables')
        ->fields(array('variable_value','template_variable_id','process_id'))
        ->from($pvquery)
        ->execute();


      } else {
        // Situation where this is the root process, inserts the default template variables into the process
        $pvquery = db_select('maestro_template_variables','a');
        $pvquery->addExpression($new_processid,'process_id');
        $pvquery->fields('a',array('variable_value','id'));
        $pvquery->condition('a.template_id',$template,'=');
        db_insert('maestro_process_variables')
        ->fields(array('variable_value','template_variable_id','process_id'))
        ->from($pvquery)
        ->execute();
      }
      if ($this->_debug ) {
        watchdog('maestro',"New queue id (1) : {$queue_record->id} - Template Taskid: {$templaterec->taskid}");
      }

      // Set the initiator variable here if not already set - via a regenerated process creation
      if ($this->getProcessVariable('INITIATOR', $new_processid) == 0) {
        $this->setProcessVariable('INITIATOR', $user->uid, $new_processid);
      }
      $newTaskAssignedUsers = $this->getAssignedUID($queue_record->id);
      if (is_array($newTaskAssignedUsers) AND count($newTaskAssignedUsers) > 0) {
        $this->assignTask($queue_record->id, $newTaskAssignedUsers);
      }

      if($useExistingGroupingId === FALSE) {
        // Detect whether this new process needs a more detailed project table association created for it.
        if(empty($pid)) {
          // Condition where there is no parent (totally new process)
          $project_record = new stdClass();
          $project_record->originator_uid = $user->uid;
          $project_record->task_id = $queue_record->id;
          $project_record->status = MaestroProcessStatusCodes::STATUS_ACTIVE;
          $project_record->description = $templaterec->template_name;
          drupal_write_record('maestro_projects',$project_record);
          $this->setTrackingId($project_record->id);
          if ($this->_debug ) {
            watchdog('maestro',"new process: created new project_id: {$project_record->id}");
          }
        }
        else {
          // Condition where there IS a parent AND we want a tracking table association
          // One different step here - to update the wf process association for the original Parent process to include the new process
          $parent_tracking_id = db_select('maestro_process','a')
          ->fields('a',array('tracking_id'))
          ->condition('id', $pid, '=')
          ->execute()->fetchField();
          $related_processes = db_select('maestro_projects','a')
          ->fields('a',array('related_processes'))
          ->condition('id', $parent_tracking_id, '=')
          ->execute()->fetchField();
          if (empty($related_processes)) {
            $related_processes .= $pid;
          }
          else {
            $related_processes .= ",{$new_processid}";
          }
          db_update('maestro_projects')
          ->fields(array('related_processes' => $related_processes))
          ->condition('id', $parent_tracking_id, '=')
          ->execute();
          if ($this->_debug ) {
            watchdog('maestro',"updated existing project record process ({$new_processid}), set related_processes set to: $related");
          }
          $this->setTrackingId($parent_tracking_id);
        }
      }
      else {
        // Condition here where we are spawning a new process from an already existing process
        // BUT we are not going to create a new tracking project, rather we are going to associate this process with the
        // parent's already established tracking id
        if(!empty($pid)) {
          // First, pull back the existing project (grouping) entry
          $existing_project_id = $this->getTrackingId($pid);
          if ($existing_project_id > 0) {
            $related_processes = db_select('maestro_projects','a')
            ->fields('a',array('related_processes'))
            ->condition('id', $existing_project_id, '=')
            ->execute()->fetchField();
            if(!empty($related_processes)) {
              $existing_project_result->related_processes .= ",$new_processid";
              db_update('maestro_projects')
              ->fields(array('related_processes' => $existing_project_result->related_processes))
              ->condition('id', $existing_project_id, '=')
              ->execute();
              $this->setTrackingId($existing_project_id);
            }
          }
        }
      }

      // Tracking Id (previously known as project id - should have been set by code above
      if($this->getTrackingId() == NULL) {
        watchdog('maestro', "New Process Code failed to set tracking ID for Process: {$new_processid}");
      }

      if ($this->_debug) {
        watchdog('maestro', "New Process Code completed Process: {$new_processid}, Tracking Id: {$this->_trackingId}");
      }

      // Check if notification has been defined for new task assignment
      $this->sendTaskAssignmentNotifications($queue_id_for_notifications);

      return $new_processid;

    }
    else {
      watchdog('maestro', "New Process Code FAIL! - Template: $template not defined");
    }
  }





  /* Main method for the Maestro Workflow Engine. Query the queue table and determine if
   * any items in the queue associated with a process are complete.
   * If they are complete, its the job of this function to determine if there are any next steps and fill the queue.
   */
  function cleanQueue() {
    $processTaskList = array("id" => array(), "processid" => array() );
    $processTaskListcount = 0;

    $query = db_select('maestro_queue', 'a');
    $query->join('maestro_process', 'b', 'a.process_id = b.id');
    $query->join('maestro_template_data', 'c', 'a.template_data_id = c.id');
    $query->join('maestro_template', 'd', 'b.template_id = d.id');
    $query->fields('a',array('id','status','archived','template_data_id','task_class_name','engine_version','is_interactive'));
    $query->addField('b','id','process_id');
    $query->addField('c','task_class_name','step_type');
    $query->addField('a','handler');
    $query->addField('d','template_name');
    $query->condition(db_and()->condition('a.archived',0)->condition('b.complete',0)->condition('a.run_once',0));
    $res = $query->execute();
    if ($this->_debug) {
      watchdog('maestro',"CleanQueue: Number of entries in the queue:" . count($res));
    }
    $numrows = 0;
    foreach ($res as $queueRecord) {
      $this->_lastTestStatus = 0;
      if ($this->_debug) {
        watchdog('maestro',"CleanQueue: processing task of type: {$queueRecord->step_type}");
      }
      $numrows++;
      $this->_processId = $queueRecord->process_id;
      $this->_queueId = $queueRecord->id;

      /* Clean queue will execute non-interactive tasks in one step - execute and then crank the process to setup the next task.
       * The interactive tasks like manual web, interactiveTasks and contentType tasks will not complete immediately.
       * The new interactive tasks (first appearance in the queue) will be executed now but do not complete immediately.
       * They will complete at some time in the future. They will be flagged complete then but since there should only
       * be one instance of the workflow engine that processes the tasks and sets up the next task.
       * That's what this method is for and so we will look for any completed interactive tasks and archive them.
       * Once archived, we crank that process forward (nextStep) and setup the next task for that workflow instance (process)
       */

      // Test for any interactive Tasks that have completed - we need to archive them now and crank the engine
      if ($queueRecord->status > 0 AND $queueRecord->is_interactive == MaestroInteractiveFlag::IS_INTERACTIVE AND $queueRecord->archived == 0) {
        $this->nextStep();
      } else {

        /* Using the strategy Design Pattern - Pass a new taskclass as the object to the maestro engine execute method
         * All non-interactive tasks will normally execute and complete in one step and return a true/false.
         * There can be batchTasks like that test for a condition and if not yet met - they would return FALSE
         * Example: have a batch task that holds up the workflow until it's Friday at 12:00 noon - then send out an email.
         * The interactive tasks like manual web, interactiveTask and contentType tasks will not execute and
         * not complete now, they will at some time in the future and be archived by this method.
         * InteractiveTasks will be picked up for the user via a call by the taskconsole to getQueue and presented
         * to the user where they interact (example: inline interactive task, question, redirect or create/edit content)
         * It's up to the code associated with the interactive task to trigger the completeTask operation - most likely by a
         * a defined MENU CALLBACK to our defined menu handlers - review code for example interactiveTasks.
         */
        $task = $this->executeTask(new $queueRecord->task_class_name($queueRecord));
        if ($task->executionStatus === FALSE) {
          watchdog('maestro',"Failed Task: {$this->_queueId}, Process: {$this->_processId} , Step Type: $this->_taskType");
          watchdog('maestro',"Task Information: ". $task->getMessage());
          // TODO:  what do we do for a failed task?
          // A task should have some sort of error recovery method
        }
        else if ($task->completionStatus > 0) {
          // Execution successful with a valid return status which will now be used to complete the task.
          $this->completeTask($task->_properties->id, $task->completionStatus);
          // @TODO:  any post complete task hooks?
          $this->_lastTestStatus = $task->getLastTestStatus();
          $this->nextStep();
        }
      }
    }

    if ($numrows == 0 AND $this->_debug) {
      watchdog('maestro','cleanQueue - 0 rows returned.  Nothing in queue.');
    }
    return $this;
  }



  function nextStep() {
    if ($this->_debug ) {
      watchdog('maestro', "nextStep: QueueId: $this->_queueId, ProcessId: $this->_processId");
    }
    // using the queueid and the processid, we are able to create or generate the
    // next step or the regenerated next step in a new process
    $query = db_select('maestro_queue', 'a');
    //if the archive status explicitly says that we're looking at a false condition from an IF, use the false path instead
    if($this->_lastTestStatus == MaestroTaskStatusCodes::STATUS_IF_CONDITION_FALSE) {
      $query->addField('b','template_data_to_false','taskid');
    }
    else {
      $query->addField('b','template_data_to','taskid');
    }
    $query->fields('c',array('task_class_name','is_interactive','show_in_detail','reminder_interval'));
    $query->join('maestro_template_data_next_step', 'b', 'a.template_data_id = b.template_data_from');
    if($this->_lastTestStatus == MaestroTaskStatusCodes::STATUS_IF_CONDITION_FALSE) {
      $query->join('maestro_template_data', 'c', 'c.id = b.template_data_to_false');
    }
    else {
      $query->join('maestro_template_data', 'c', 'c.id = b.template_data_to');
    }
    $query->condition('a.process_id',$this->_processId,'=');
    $query->condition('a.id',$this->_queueId,'=');
    $nextTaskResult = $query->execute();

    $nextTaskRows = $query->countQuery()->execute()->fetchField();
    //watchdog('maestro',"nextStep: Number of next task records: $nextTaskRows");
    if ($nextTaskRows == 0 ) {
      // There are no rows for this specific queueId and nothing for this processId, there's no next task
      $this->archiveTask($this->_queueId);
      db_update('maestro_process')
      ->fields(array('complete' => MaestroProcessStatusCodes::STATUS_COMPLETED, 'completed_date' => time()))
      ->condition('id', $this->_processId, '=')
      ->execute();

    } else { // we've got tasks
      foreach ($nextTaskResult as $nextTaskRec) {
        if ($this->_debug ) {
          watchdog('maestro',"Got tasks  qid: {$this->_queueId}, pid: {$this->_processId} and Next taskid: {$nextTaskRec->taskid}");
        }
        // Check if the next template id is null, ensures that if we're on the last task and it points to null, that we end it properly
        if ($nextTaskRec->taskid == null or $nextTaskRec->taskid == '' ) {
          // Process is done - archive queue item
          $this->archiveTask($this->_queueId);
          db_update('maestro_process')
          ->fields(array('complete' => MaestroProcessStatusCodes::STATUS_COMPLETED, 'completed_date' => time()))
          ->condition('id', $this->_processId, '=')
          ->execute();
        }
        else {
          /* We have a next step, thus we can archive the queue item and also insert a
           * new queue item with the next step populated as the next template_stepid
           */
          $query = db_select('maestro_queue', 'a');
          $query->addField('a','id');
          $query->addExpression('COUNT(a.id)','rec_count');
          $query->groupBy('a.id');
          $query->condition('a.process_id', $this->_processId,'=');
          $query->condition('a.template_data_id', $nextTaskRec->taskid,'=');
          $nextTaskQueueRec = $query->execute()->fetchObject();
          if ($nextTaskQueueRec == FALSE OR $nextTaskQueueRec->rec_count == 0 ) {
            $this->archiveTask($this->_queueId);
            if ($nextTaskRec->reminder_interval > 0) {
              $next_reminder_date = time() + $nextTaskRec->reminder_interval;
            }
            else {
              $next_reminder_date = 0;
            }
            // No next item in the queue.. just create it
            $queue_record = new stdClass();
            $queue_record->process_id = $this->_processId;
            $queue_record->template_data_id = $nextTaskRec->taskid;
            $queue_record->task_class_name = $nextTaskRec->task_class_name;
            $queue_record->is_interactive = $nextTaskRec->is_interactive;
            $queue_record->show_in_detail = $nextTaskRec->show_in_detail;
            $queue_record->status = 0;
            $queue_record->archived = 0;
            $queue_record->engine_version = $this->_version;
            $queue_record->created_date = time();
            $queue_record->next_reminder_date = $next_reminder_date;
            // Instantiate the tasktype specific method to set the queue record task data
            if (class_exists($nextTaskRec->task_class_name)) {
              $taskdata = $this->prepareTask(new $nextTaskRec->task_class_name($nextTaskRec));
              if (isset($taskdata) AND is_array($taskdata)) {
                if (isset($taskdata['handler'])) $queue_record->handler = $taskdata['handler'];
                if (isset($taskdata['serialized_data'])) $queue_record->task_data = $taskdata['serialized_data'];
              }

              drupal_write_record('maestro_queue',$queue_record);

              $next_record = new stdClass();
              $next_record->queue_id = $queue_record->id;
              $next_record->from_queue_id = $this->_queueId;
              drupal_write_record('maestro_queue_from',$next_record);

              if ($queue_record->id > 0) {
                if ($this->_debug ) {
                  $logmsg  = "New queue id (3) : {$this->_queueId} - Template Taskid: {$nextTaskRec->taskid} - ";
                  $logmsg .= "Assigned to " . $this->getTaskOwner($nextTaskRec->taskid,$this->_processId);
                  watchdog('maestro', $logmsg);
                }
              }
              else {
                watchdog('maestro', "nextStep Method FAIL! - Unexpected problem creating queue record");
              }

              $newTaskAssignedUsers = $this->getAssignedUID($queue_record->id);
              if (is_array($newTaskAssignedUsers) AND count($newTaskAssignedUsers) > 0) {
                $this->assignTask($queue_record->id,$newTaskAssignedUsers);
              }

              // Check if notification has been defined for new task assignment
              $this->sendTaskAssignmentNotifications($queue_record->id);
            }
            else {
              watchdog('maestro', "Invalid Task Type: {$nextTaskRec->task_class_name}");
              drupal_set_message("Invalid Task Type: {$nextTaskRec->task_class_name}", 'error');
            }

          }
          else {
            /* We have a situation here where the next item already exists.
             * need to determine if the next item has a regeneration flag.
             * If there is a regeneration flag, then create a new process starting with that regeneration flagged item
             */
            $query = db_select('maestro_template_data', 'a');
            $query->fields('a',array('id','regenerate','template_id'));
            $query->addExpression('COUNT(id)','rec_count');
            $query->groupBy('a.regenerate');
            $query->groupBy('a.template_id');
            $query->groupBy('a.id');
            $query->condition('a.id', $nextTaskRec->taskid,'=');
            $regenRec = current($query->execute()->fetchAll());

            if ($regenRec->regenerate == 1) {
              $this->archiveTask($this->_queueId);
              // Regenerate the same process starting at the next step
              // Set the current process' complete status to 2.. 0 is active, 1 is done, 2 is has children
              $this->newProcess($regenRec->template_id, $nextTaskRec->taskid, $this->_processId);
            }
            else {
              // No regeneration so we are done
              $this->archiveTask($this->_queueId);
              $toQueueID = $nextTaskQueueRec->id;
              $next_record = new stdClass();
              $next_record->queue_id = $nextTaskQueueRec->id;
              $next_record->from_queue_id = $this->_queueId;
              drupal_write_record('maestro_queue_from',$next_record);

              $query = db_select('maestro_queue', 'a');
              $query->addExpression('COUNT(id)','rec_count');
              $query->condition('a.process_id', $this->_processId,'=');
              $query->condition('a.template_data_id', $nextTaskRec->taskid,'=');
              if ($query->execute()->fetchField() == 0 ) {
                db_update('maestro_process')
                ->fields(array('complete' => MaestroProcessStatusCodes::STATUS_COMPLETED, 'completed_date' => time()))
                ->condition('id', $this->_processId, '=')
                ->execute();
              }
            }
          }
        }
      }
    }
    return $this;
  }


  /**
   * Method assign task - create productionAssignment Record and test if to-be-assigned user has their out-of-office setting active
   * @param        int         $queueID     Task ID from the workflow queue table
   * @param        array       $assignemnt  Array of records where the key is the variable id  if applicable and the user id
   If the assignment is by user, the key will be 0 or a negative value - in the case of multiple assignments
   * @return       n/a         No return
   */
  function assignTask($queueId,$userObject) {
    foreach ($userObject as $processVariableId => $userId) {
      if (strpos($userId, ':') !== false) {
        $userIds = explode(':', $userId);
      }
      else {
        $userIds = array($userId);
      }

      foreach ($userIds as $userId) {
        $userId = intval($userId);
        /* The array of users to be assigned may be an array of multiple assignments by user not variable
         * In this case, we can not have multiple array records with a key of 0 - so a negative value is used
         */
        if($processVariableId < 0) $processVariableId = 0;
        if ($userId > 0) {
          $query = db_select('maestro_user_away', 'a');
          $query->fields('a',array('away_start','away_return','is_active'));
          $query->condition('a.uid',$userId,'=');
          //$res1 = $query->execute()->fetchObject();
          $res1 = NULL; //temporary until user away settings are added
          if ($res1) {
            // Check if user is away - away feature active and current time within the away window
            if ($res1->is_active == 1 AND time() > $res1->away_start AND time() < $res1->away_return) {
              /* User is away - determine who to re-assign task to */
              $assignToUserId = $this->getAwayReassignmentUid($userId);
              // If we have a new value for the assignment - then we need to set the assignBack field
              if ($assignToUserId != $userId) {
                $assignBack = $userId;
              }
              else {
                $assignBack = 0;
              }
            }
            else {
              $assignToUserId = $userId;
              $assignBack = 0;
            }
          }
          else {
            $assignToUserId = $userId;
            $assignBack = 0;
          }
        }
        else {
          $assignToUserId = 0;
          $assignBack = 0;
        }

        // Check and see if we have an production assignment record for this task and processVariable
        $query = db_select('maestro_production_assignments', 'a');
        $query->addField('a','uid');
        $query->condition('a.task_id',$queueId,'=');
        if ($processVariableId > 0) {
          $query->condition('a.process_variable',$processVariableId,'=');
        }
        else {
          $query->condition('a.process_variable',0,'=');
          $query->condition('a.uid',$userId,'=');
        }
        $res2 = $query->execute();
        $numrows = $query->countQuery()->execute()->fetchField();
        if ($numrows < count($userIds)) {
          db_insert('maestro_production_assignments')
          ->fields(array('task_id','uid','process_variable','assign_back_uid','last_updated'))
          ->values(array(
                    'task_id' => $queueId,
                    'uid' => $assignToUserId,
                    'process_variable' => $processVariableId,
                    'assign_back_uid' => $assignBack,
                    'last_updated'  => time()
          ))
          ->execute();
        }
        else {
          db_update('maestro_production_assignments')
          ->fields(array('uid' => $assignToUserId, 'last_updated' => time(), 'assign_back_uid' => $assignBack))
          ->condition('task_id', $queueId, '=')
          ->condition('process_variable',$processVariableId,'=')
          ->execute();
        }
      }
    }
  }

  function reassignTask($queueId, $assignUid, $currentUid=0, $variableId=0) {
    /* Assignment Record has to exist - but there can be multiple for this workflow queue record (process task)
     * If the assign_uid is 0 then it's not presently assigned
     * If the process_variable field is 0 then the task is assigned by UID and not by variable
     */

    // Check that user exists, is valid and status is an active user - else skip the re-assignment
    $user_status = db_query("SELECT status FROM {users} WHERE uid = :uid", array(':uid' => $assignUid))->fetchField();

    if ($assignUid >= 1 AND $user_status > 0) {
      $query = db_select('maestro_production_assignments', 'a');
      $query->fields('a', array('id', 'uid', 'assign_back_uid'));
      $query->condition('task_id', $queueId, '=');
      if ($variableId > 0) {
        $query->condition('process_variable', $variableId, '=');
      }
      else if ($currentUid > 0) {
        $query->condition('uid', $currentUid, '=');
      }

      $res = $query->execute();
      $rec = $query->execute()->fetchObject();
      // Check and see if we have a production assignment record - if unassigned, then lets create one
      if ($rec === FALSE OR !isset($rec->id)) {
        db_insert('maestro_production_assignments')
        ->fields(array('task_id','uid','process_variable','assign_back_uid','last_updated'))
        ->values(array(
            'task_id' => $queueId,
            'uid' => $assignUid,
            'process_variable' => 0,
            'assign_back_uid' => 0,
            'last_updated'  => time()
        ))
        ->execute();
        $assignToUserId = $assignUid;
      } else {
        /* If the task has been re-assigned previously for this task, then we will now loose the originally assigned user */
        /* Need to now check if the to-be-assigned user is away and if so .. then assigned to their backup */
        $assignToUserId = $this->getAwayReassignmentUid($assignUid);
        db_update('maestro_production_assignments')
        ->fields(array('uid' => $assignToUserId, 'last_updated' => time(), 'assign_back_uid' => $currentUid))
        ->condition('id', $rec->id, '=')
        ->execute();
      }

      // Create a comment in the project comments
      $query = db_select('maestro_queue', 'a');
      $query->join('maestro_process','b','b.id = a.process_id');
      $query->fields('a', array('id','template_data_id', 'process_id'));
      $query->fields('b', array('tracking_id'));
      $query->condition('a.id', $queueId, '=');
      $rec = $query->execute()->fetchObject();
      $taskname = db_query("SELECT taskname FROM {maestro_template_data} WHERE id = :tid",
      array(':tid' => $rec->template_data_id))->fetchField();
      $assigned_name = db_query("SELECT name FROM {users} WHERE uid = :uid",
      array(':uid' => $currentUid))->fetchField();
      $reassigned_name = db_query("SELECT name FROM {users} WHERE uid = :uid",
      array(':uid' => $assignToUserId))->fetchField();
      $comment = "Task Owner change, was {$assigned_name}, now {$reassigned_name} for task: {$taskname}";
      db_insert('maestro_project_comments')
      ->fields(array('tracking_id','uid','task_id','timestamp','comment'))
      ->values(array(
              'tracking_id' => $rec->tracking_id,
              'uid' => $assignToUserId,
              'task_id' => $rec->id,
              'timestamp' => time(),
              'comment'  => $comment
      ))
      ->execute();
    }
  }


  /* @TODO: Need to complete this function and add the user profile feature for
   * user to setup auto re-assignment options if they are away
   */
  function getAwayReassignmentUid($uid) {
    return $uid;
  }

  function getAssignedUID($queue_id=0) {
    if ($queue_id == 0) {
      $queue_id = $this->_queueId;
    }

    $assigned = array();
    $query = db_select('maestro_template_assignment', 'a');
    $query->leftJoin('maestro_queue', 'b', 'a.template_data_id=b.template_data_id');
    $query->fields('a', array('assign_type', 'assign_by', 'assign_id'));
    $query->fields('b', array('process_id'));
    $query->condition('b.id', $queue_id, '=');
    $res = $query->execute()->fetchAll();

    $assigned[MaestroAssignmentTypes::USER][MaestroAssignmentBy::FIXED] = array();
    $assigned[MaestroAssignmentTypes::USER][MaestroAssignmentBy::VARIABLE] = array();

    foreach ($res as $rec) {
      if ($rec->assign_by == MaestroAssignmentBy::FIXED) {
        $assigned[$rec->assign_type][$rec->assign_by][] = $rec->assign_id;
      }
      else {
        $pvQuery = db_select('maestro_process_variables', 'a');
        $pvQuery->fields('a', array('variable_value'));
        $pvQuery->condition('a.template_variable_id', $rec->assign_id, '=');
        $pvQuery->condition('a.process_id', $rec->process_id, '=');
        $pvRec = current($pvQuery->execute()->fetchAll());
        $assign_id = $pvRec->variable_value;
        $assigned[$rec->assign_type][$rec->assign_by][$rec->assign_id] = $assign_id;
      }
    }

    if (count($assigned) == 0) {
      //check to see if this is a valid queue_id, if so add a blank assignment record
      $query = db_select('maestro_queue', 'a');
      $query->fields('a', array('id'));
      $query->condition('a.id', $queue_id, '=');
      $rec = current($query->execute()->fetchAll());
      if ($rec != FALSE) {
        $assigned[MaestroAssignmentTypes::USER][MaestroAssignmentBy::FIXED][0] = 0;
      }
    }

    //TODO: hack for now to support current assignment. in beta we will need to return the $assigned array as it is right now, without the following logic
    if (count($assigned[MaestroAssignmentTypes::USER][MaestroAssignmentBy::FIXED]) < count($assigned[MaestroAssignmentTypes::USER][MaestroAssignmentBy::VARIABLE])) {
      return $assigned[MaestroAssignmentTypes::USER][MaestroAssignmentBy::VARIABLE];
    }
    else {
      return $assigned[MaestroAssignmentTypes::USER][MaestroAssignmentBy::FIXED];
    }

    return $assigned;
  }


  function completeTask($qid, $status = 1) {
    $pid = db_query("SELECT process_id FROM {maestro_queue} WHERE id = :qid",
    array(':qid' => $qid))->fetchField();

    if (empty($pid)) {
      watchdog('maestro',"Task ID #$qid no longer exists in queue table.  It was potenially removed by an admin from outstanding tasks.");
      return FALSE;
    }

    $trackingId = db_query("SELECT tracking_id FROM {maestro_process} WHERE id = :pid",
    array(':pid' => $pid))->fetchField();

    if ($this->_debug ) {
      watchdog('maestro',"Complete_task - updating queue item: $qid, project (tracking id): $trackingId");
    }

    //check if this task is interactive.  If interactive, assigned_uid is the user assigned.  else, its a 0 as its engine run.
    $is_interactive = db_query("SELECT is_interactive FROM {maestro_queue} WHERE id = :qid",
    array(':qid' => $qid))->fetchField();
    if($is_interactive == MaestroInteractiveFlag::IS_INTERACTIVE) {
      if ($this->_userId == '' or $this->_userId == null ) {
        $assigned_uid = db_query("SELECT uid FROM {maestro_production_assignments} WHERE task_id = :qid",
        array(':qid' => $qid))->fetchField();
      } else {
        $assigned_uid = $this->_userId;
      }
    }
    else {
      $assigned_uid = 0;
    }

    db_update('maestro_queue')
    ->fields(array('uid' => $assigned_uid , 'status' => $status, 'run_once' => 0))
    ->condition('id',$qid,'=')
    ->execute();

    //notify before deleting the production assignment record
    $this->sendTaskCompletionNotifications($qid);

    // Self Prune Production Assignment table - delete the now completed task assignment record
    db_delete('maestro_production_assignments')
    ->condition('task_id',$qid,'=')
    ->execute();

  }

  function archiveTask($qid) {
    db_update('maestro_queue')
    ->fields(array('completed_date' => time(), 'archived' => 1))
    ->condition('id',$qid,'=')
    ->execute();

    // Self Prune Production Assignment table - delete the now completed task assignment record
    db_delete('maestro_production_assignments')
    ->condition('task_id',$qid,'=')
    ->execute();
  }


  function cancelTask($queueId) {
    db_update('maestro_queue')
    ->fields(array('status' => $status, 'completed_date' => time(), 'archived' => 1))
    ->condition('id',$qid,'=')
    ->execute();
  }

  function getQueue($show_system_tasks = FALSE) {
    if (!empty($this->_userId) AND $this->_userId > 0) {
      /* Instance where the user id is known.  need to see if there is a processID given.
       * This means that the mode in which we're working is user based.. we only care about a user in this case
       */
      if ($this->_mode != 'admin') {
        $this->_mode = 'user';
      }
      if ($this->_debug ) {
        watchdog('maestro',"Entering getQueue - {$this->mode} mode");
      }
      $this->_userTaskCount = 0;
      $query = db_select('maestro_queue', 'a');
      $query->join('maestro_template_data', 'b', 'a.template_data_id = b.id');
      $query->leftJoin('maestro_production_assignments', 'c', 'a.id = c.task_id');
      $query->join('maestro_process', 'd', 'a.process_id = d.id');
      $query->fields('a',array('id','template_data_id','process_id','is_interactive','handler','task_data','created_date','started_date'));
      $query->fields('b',array('task_class_name','template_id','taskname','is_dynamic_taskname','dynamic_taskname_variable_id'));
      if ($this->_mode == 'admin') {
        $query->fields('c',array('uid'));
        $query->fields('e',array('name'));
        $query->leftJoin('users', 'e', 'c.uid = e.uid');
      }
      $query->addField('d','pid','parent_process_id');
      $query->fields('d',array('tracking_id','flow_name'));
      if ($this->_mode != 'admin') {
        $query->condition('c.uid',$this->_userId,'=');
      }
      if ($show_system_tasks == FALSE) {
        $query->condition('a.is_interactive', MaestroInteractiveFlag::IS_INTERACTIVE);
      }
      $query->condition(db_or()->condition('a.archived',0)->condition('a.archived',NULL));
      $query->condition(db_and()->condition('a.status', 0, '>='));
      $query->orderBy('a.id','DESC');
      $userTaskResult = $query->execute();
      $numTaskRows = $query->countQuery()->execute()->fetchField();
      if ($numTaskRows == 0) {
        if ($this->_debug ) {
          watchdog('maestro',"getQueue - 0 rows returned.  Nothing in queue for this user: {$this->_userId}.");
        }
      }
      else {
        // Return a semi-colon delimited list of queue id's for that user.
        foreach ($userTaskResult as $userTaskRecord) {
          if ($this->_queueId == '' ) {
            $this->_queueId = $userTaskRecord->id;
          } else {
            $this->_queueId .= ";" . $userTaskRecord->id;
          }

          // Simple test to determine if the task ID already exists for this user
          $flag = 0;
          for($flagcntr = 0;$flagcntr <= $this->_userTaskCount;$flagcntr++ ) {
            if (isset($this->_userTaskObject[$flagcntr]->queue_id) AND $this->_userTaskObject[$flagcntr]->queue_id == $userTaskRecord->id ) {
              $flag = 1;
            }
          }
          if ($flag == 0 ) {
            $taskObject = new stdClass();
            $templatename = db_query("SELECT template_name FROM {maestro_template} WHERE id = :tid",
            array(':tid' => $userTaskRecord->template_id))->fetchField();

            // Determine if this task is for a regenerated workflow and we need to update the main project/request record
            $taskObject->regen = FALSE;
            if ($userTaskRecord->parent_process_id > 0) {
              // Now check if this same template task id was executed in the previous process - if so then it is a recycled task
              // Don't show the re-generated attribute if in this instance of the process we proceed further and are executing new tasks
              $regenquery = db_select('maestro_queue', 'a');
              $regenquery->addExpression('COUNT(id)','rec_count');
              $regenquery->condition('a.process_id', $userTaskRecord->parent_process_id,'=');
              $regenquery->condition(db_and()->condition('a.template_data_id', $userTaskRecord->template_data_id,'='));
              if ($regenquery->execute()->fetchField() > 0 ) {
                $taskObject->regen = TRUE;
              }
            }

            $queueRecDates = array('created' => $userTaskRecord->created_date, 'started' => $userTaskRecord->started_date);
            $queueRecFlags = array('is_interactive' => $userTaskRecord->is_interactive);
            $taskObject->task_data = $userTaskRecord->task_data;
            $taskObject->queue_id = $userTaskRecord->id;
            $taskObject->task_id = $userTaskRecord->template_data_id;
            $taskObject->process_id = $userTaskRecord->process_id;
            $taskObject->parent_process_id = $userTaskRecord->parent_process_id;
            $taskObject->template_id = $userTaskRecord->template_id;
            $taskObject->template_name = $templatename;
            $taskObject->flow_name = $userTaskRecord->flow_name;
            $taskObject->tracking_id = $userTaskRecord->tracking_id;
            $taskObject->url = $userTaskRecord->handler;
            $taskObject->dates = $queueRecDates;
            $taskObject->flags = $queueRecFlags;
            if ($this->_mode == 'admin') {
              $taskObject->uid = $userTaskRecord->uid;
              $taskObject->username = ($userTaskRecord->name != '') ? $userTaskRecord->name : '[' . t('nobody assigned') . ']';
            }

            // Handle dynamic task name based on a variable's value
            $taskname = '';
            if($userTaskRecord->is_dynamic_taskname == 1) {
              $q2 = db_select('maestro_process_variables', 'a');
              $q2->addField('a','variable_value');
              $q2->condition('a.process_id',$userTaskRecord->process_id,'=');
              $q2->condition('a.template_variable_id',$userTaskRecord->dynamic_taskname_variable_id,'=');
              $res1 = $query->execute()->fetchObject();
              if ($res1) {
                $userTaskRecord->taskname = $res1->variable_value;
              }
            }
            /* @TODO: Need to look at using a module HOOK that can be used in a similar way to define an custom taskname */
            /*
             if (function_exists('PLG_Nexflow_taskname')) {
             $parms = array('pid' => $A['nf_processID'], 'tid' => $A['nf_templateDataID'], 'qid' => $A['id'], 'user' => $this->_nfUserId);
             if (!empty($taskame)) {
             $apiRetval = PLG_Nexflow_taskname($parms,$taskname);
             } else {
             $apiRetval = PLG_Nexflow_taskname($parms,$A['taskname']);
             }
             $taskname = $apiRetval['taskname'];
             }
             */

            $taskObject->taskname = $userTaskRecord->taskname;
            $taskObject->tasktype = $userTaskRecord->task_class_name;
            $this->_userTaskObject[$this->_userTaskCount] = $taskObject;
            $this->_userTaskCount += 1; // Increment the total user task counter
          }
        }
      }
    }

    if ($this->_debug ) {
      watchdog('maestro',"Exiting getQueue - user mode");
    }
    return $this->_userTaskObject;
  }

  //gets the highest level parent process id for a process, aka the 'initiating_pid'
  function getParentProcessId($pid) {
    $retpid = $pid;

    while ($pid != 0) {
      $query = db_select('maestro_process', 'a');
      $query->fields('a', array('pid'));
      $query->condition('a.id', $pid, '=');
      $res = $query->execute();
      $rec = current($res->fetchAll());
      if ($rec != '') {
        $pid = $rec->pid;
        if ($pid != 0) {
          $retpid = $pid;
        }
      }
      else {
        $pid = 0;
      }
    }

    return $retpid;
  }

  //readd production assignment records for a deleted task
  function reviveTask($qid) {
    if ($qid > 0) {
      $query = db_select('maestro_queue', 'a');
      $query->fields('b', array('assigned_by_variable'));
      $query->leftJoin('maestro_template_data', 'b', 'a.template_data_id=b.id');
      $query->condition('a.id', $qid, '=');
      $assigned_by_variable = current($query->execute()->fetchAll())->assigned_by_variable;

      $assigned = $this->getAssignedUID($qid);
      foreach ($assigned as $pv_id => $assigned_uid) {
        $rec = new stdClass();
        $rec->task_id = $qid;
        $rec->uid = $assigned_uid;
        $rec->process_variable = ($assigned_by_variable == 1) ? $pv_id:0;
        $rec->assign_back_uid = 0;
        $rec->last_updated = time();
        drupal_write_record('maestro_production_assignments', $rec);
      }
    }
  }
}