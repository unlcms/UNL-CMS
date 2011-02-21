<?php

  /* Using Drupal OO Coding Standards as described: http://drupal.org/node/608152 */
include_once('maestro_constants.class.php');

  abstract class MaestroEngine {
//@TODO: Need to convert these to the proper public/protected variables.

    var $_version                 = '';       // Current engine version
    var $_processId               = NULL;     // Current Process the workflow engine is working on
    var $_templateId              = NULL;     // Current workflow template id being processed
    var $_queueId                 = NULL;     // Current Queue record id being processed. This is either null, a single item or a semi colon delimited list
    var $_userId                  = NULL;     // Current User Id
    var $_trackingId              = NULL;     // Workflow grouping Tracking Id to enable project or detail workflow tracking and link related workflows
    var $_taskType                = '';
    var $_debug                   = FALSE;    // Set the current debug level to false.
    var $_userTaskCount           = 0;        // Number of tasks the user has in the queue
    var $_userTaskObject          = NULL;     // Users Active Tasks in the queue
    var $_templateCount           = 0;        // Number of templates the user is able to kick off
    var $_processTaskCount        = 0;        // Number of tasks the current process has in the queue
    var $_status                  = 0;        // Set in cleanQueue to indicate status of last executing task before calling nextStep method
    var $_lastTestStatus          = 0;        // Used in nextStep when the task that last executed will branch to different tasks - like an IF task
    var $_mode;
    var $task                     = NULL;

    function setMode($mode) { $this->_mode = $mode; }
    function getMode() { return $this->_mode; }

    // Simply sets the debug parameter.
    function setDebug($debug) {
        if ($debug) {
            watchdog('maestro',"Set debug mode on");
        }
        $this->_debug = $debug;
    }

    public function setProcessId($id) {
      if (intval($id) > 0) {
        $this->_processId = $id;
      }
    }

    /* If a valid tracking_id is passed in, set the class variable
     * and update the process table's value for this process record.
     * The tracking_id is so we can group related workflow instance information together,
     * such as content_type node records, task history, comments and other data.
     */
    function setTrackingId($id) {
      if (intval($id) > 0) {
        $this->_trackingId = $id;
        if ($this->_processId > 0) {
          db_update('maestro_process')
            ->fields(array('tracking_id' => $id))
            ->condition('id', $this->_processId, '=')
            ->execute();
        }
      }
    }

    /* Default will be to return the tracking_id that is set but if not set,
     * then we may be asking for the tracking_id for another process ($pid)
     * like a parent process - called this way in the newProcess code if we are
     * regenerating the existing process and want to inherit the same tracking_id
     * If no process_id is passed in, then test if $this->_processId is set and
     * then look up in the process table for the tracking_id.
     */
    function getTrackingId($pid = 0) {
      $retval = FALSE;
      if ($this->_trackingId > 0) {
        $retval = $this->_trackingId;
      }
      elseif ($pid == 0 AND $this->_processId > 0) {
        $id = db_select('maestro_process')
          ->fields('maestro_process', array('tracking_id'))
          ->condition('id', $this->_processId, '=')
          ->execute()->fetchField();
        $retval = $id;
      } elseif ($pid > 0) {
        $id = db_select('maestro_process')
          ->fields('maestro_process', array('tracking_id'))
          ->condition('id', $pid, '=')
          ->execute()->fetchField();
        $retval = $id;
      }
      return $retval;
    }

    public function getUserTaskCount() {
      return $this->_userTaskCount;
    }

    public function executeTask(MaestroTask $task) {
       return $task->execute();
    }

    public function prepareTask(MaestroTask $task) {
       return $task->prepareTask();
    }

    public function showInteractiveTask(MaestroTask $task,$taskid) {
      /* Common HTML container with an ID set that we will hook onto to show/hide.
       * This lets developer not have to worry about returning a table row with 5 columns
       */
      $prehtmlfragment = '<tr class="maestro_taskconsole_interactivetaskcontent" id="maestro_actionrec' . $taskid . '" style="display:none;"><td colspan="6">';
      $posthtmlfragment = '</td></tr>';
      $retval = $task->showInteractiveTask();
      if ($retval === FALSE) {
      	return '';
      }
      else if (empty($retval)) {
        return $prehtmlfragment . t('empty interactive task - nothing to display for interactive function.') . $posthtmlfragment;
      }
      else {
        return $prehtmlfragment . $retval . $posthtmlfragment;
      }
    }


    // Get a process variable as defined for this template
    // Requires the processID to be set and then pass in a variable's name.
    // if both the process and the name exist, you get a value..
    // otherwise, you get NULL
    function getProcessVariable($variable, $process_id=0) {
      $retval = NULL;
      $thisvar = strtolower($variable);

      if ($process_id == 0 && !empty($this->_processId)) {
        $process_id = $this->_processId;
      }
      else if ($process_id == 0) {
        if ($this->_debug ) {
          watchdog('maestro',"get_ProcessVariable: The Process ID has not been set.");
          return $retval;
        }
      }

      $query = db_select('maestro_process_variables', 'a');
      $query->addField('a','variable_value');
      $query->join('maestro_template_variables', 'b', 'a.template_variable_id = b.id');
      $query->condition('a.process_id',$process_id,'=');
      $query->condition('b.variable_name',$thisvar,'=');
      $result = $query->execute();
      $numrows = $query->countQuery()->execute()->fetchField();
      if ($numrows > 0 ) {
        $record = $result->fetchObject();
        $retval = $record->variable_value;
        if ($this->_debug ) {
          watchdog('maestro',"get_ProcessVariable: $variable -> $retval");
        }
      }
      else {
        if ($this->_debug ) {
          watchdog('maestro',"get_processVariable -> Process:{$this->_processId}, variable:$variable - DOES NOT EXIST");
        }
      }

      return $retval;
    }


    // Set a process variable as defined for this template
    // Requires the processID to be set and then pass in a variable's name and value
    // if both the process and the name exist, you get a value..
    // otherwise, you get NULL
    function setProcessVariable($variableName, $variableValue=0, $process_id=0) {
      $retval = NULL;
      $thisvar = strtolower($variableName);

      if ($process_id == 0 && !empty($this->_processId)) {
        $process_id = $this->_processId;
      }
      else if ($process_id == 0) {
        if ($this->_debug ) {
          watchdog('maestro',"get_ProcessVariable: The Process ID has not been set.");
          return $retval;
        }
      }

      // setting the value
      $query = db_select('maestro_process_variables', 'a');
      $query->addField('a','id','process_variable_id');
      $query->addField('a','template_variable_id','variable_id');
      $query->join('maestro_template_variables', 'b', 'a.template_variable_id = b.id');
      $query->condition('a.process_id', $process_id, '=');
      $query->condition('b.variable_name',$thisvar,'=');
      $result = $query->execute();
      $numrows = $query->countQuery()->execute()->fetchField();
      if ($numrows > 0) {
        $processVariableRecord = $result->fetchObject();
        $count = db_update('maestro_process_variables')
          ->fields(array('variable_value' => $variableValue))
          ->condition('id', $processVariableRecord->process_variable_id, '=')
          ->condition('process_id', $process_id, '=')
          ->execute();
        if ($this->_debug ) {
            watchdog('maestro',"set_processVariable -> Process:{$process_id}, variable:$thisvar, value:$variableValue");
        }
        if ($count == 1) {
            $retval = $variableValue;
        }

         // Now see if that process variable controlled assignment
        if (isset($processVariableRecord->template_variable_id) AND $processVariableRecord->template_variable_id > 0) {
          $query = db_select('maestro_queue', 'a');
          $query->leftJoin('maestro_template_assignment', 'b', 'a.template_data_id=b.template_data_id');
          $query->fields('a', array('id'));
          $query->condition('b.assign_by', MaestroAssignmentBy::VARIABLE, '=');
          $query->condition('b.assign_id', $processVariableRecord->template_variable_id, '=');
          $res = $query->execute()->fetchAll();
          $queueRecords = $query->execute();
          foreach ($queueRecords as $queueRecord) {
            $this->assignTask($queueRecord->id, array($processVariableRecord->variable_id => $variableValue));
          }
        }
      }
      else {
        if ($this->_debug ) {
          watchdog('maestro',"set_processVariable -> Process:{$process_id}, variable:$thisvar - DOES NOT EXIST");
        }
      }

      return $retval;
    }

    function sendTaskAssignmentNotifications ($qid=0) {
      include_once('maestro_notification.class.php');
      if ($qid == 0) {
        $qid = $this->_queueId;
      }

      $message = variable_get('maestro_assignment_message');
      $subject = variable_get('maestro_assignment_subject');
      $notification = new MaestroNotification($message, $subject, $qid, MaestroNotificationTypes::ASSIGNMENT);
      $notification->notify();
    }

    function sendTaskCompletionNotifications ($qid=0) {
      include_once('maestro_notification.class.php');
      if ($qid == 0) {
          $qid = $this->_queueId;
      }

      $message = variable_get('maestro_completion_message');
      $subject = variable_get('maestro_completion_subject');
      $notification = new MaestroNotification($message, $subject, $qid, MaestroNotificationTypes::COMPLETION);
      $notification->notify();
    }

    function sendTaskReminderNotifications ($qid=0, $user_id=0) {
      include_once('maestro_notification.class.php');
      if ($qid == 0) {
        $qid = $this->_queueId;
      }

      $message = variable_get('maestro_reminder_message');
      $subject = variable_get('maestro_reminder_subject');
      $notification = new MaestroNotification($message, $subject, $qid, MaestroNotificationTypes::REMINDER);

      if($user_id != 0) {
        $notification->setUserIDs($user_id);
      }
      $notification->notify();
    }

    function reassignTask($qid, $current_uid, $reassign_uid) {
      if ($qid > 0 && $reassign_uid > 0) {
        db_update('maestro_production_assignments')
          ->fields(array('uid' => $reassign_uid, 'assign_back_uid' => $current_uid))
          ->condition('task_id', $qid, '=')
          ->condition('uid', $current_uid, '=')
          ->execute();
      }
    }

    function deleteTask($qid) {
      if ($qid > 0) {
        db_delete('maestro_production_assignments')
          ->condition('task_id', $qid, '=')
          ->execute();
        db_update('maestro_queue')
          ->fields(array('status' => MaestroTaskStatusCodes::STATUS_DELETED, 'archived' => 1))
          ->condition('id', $qid, '=')
          ->execute();
      }
    }

    function getQueueHistory($initiating_pid) {
      $query = db_select('maestro_queue', 'a');
      $query->fields('a', array('id', 'process_id', 'status', 'archived', 'created_date', 'started_date', 'completed_date'));
      $query->fields('c', array('taskname'));
      $query->fields('d', array('name'));
      $query->leftJoin('maestro_process', 'b', 'a.process_id=b.id');
      $query->leftJoin('maestro_template_data', 'c', 'a.template_data_id=c.id');
      $query->leftJoin('users', 'd', 'a.uid=d.uid');
      $query->condition('b.initiating_pid', $initiating_pid, '=');
      $query->orderBy('a.id', 'ASC');
      $res = $query->execute();

      $queue_history = array();
      foreach ($res as $rec) {
        if ($rec->archived != 1) {
          $q2 = db_select('maestro_production_assignments', 'a');
          $q2->fields('b', array('name'));
          $q2->leftJoin('users', 'b', 'a.uid=b.uid');
          $q2->condition('a.task_id', $rec->id, '=');
          $res2 = $q2->execute();
          $rec->username = '';
          foreach ($res2 as $userRec) {
            if ($rec->username != '') {
              $rec->username .= ', ';
            }
            $rec->username .= $userRec->name;
          }
        }
        else {
          $rec->username = $rec->name;
        }

        $queue_history[] = $rec;
      }

      return $queue_history;
    }

    function getRelatedWorkflows($tracking_id) {
      $query = db_select('maestro_process', 'a');
      $query->fields('a', array('tracking_id', 'initiating_pid'));
      $query->fields('b', array('template_name'));
      $query->leftJoin('maestro_template', 'b', 'a.template_id=b.id');
      $query->groupBy('a.initiating_pid');
      $query->condition('a.tracking_id', $tracking_id, '=');

      return $query->execute();
    }

    abstract function getVersion();

    abstract function assignTask($queueId,$userObject);

    abstract function getAssignedUID($queue_id=0);

    abstract function completeTask($queueId,$status = 1);

    abstract function archiveTask($queueId);

    abstract function cancelTask($queueId);

    /* Main method for the Maestro Workflow Engine. Query the queue table and determine if
     * any items in the queue associated with a process are complete.
     * If they are complete, its the job of this function to determine if there are any next steps and fill the queue.
     */
    abstract function cleanQueue();


}
