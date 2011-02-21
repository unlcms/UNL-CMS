<?php
// $Id: maestro_notification.class.php,v 1.18 2010/08/25 19:40:39 chevy Exp $

/**
 * @file
 * maestro_notification.class.php
 */

/*
 * We are implementing an observer pattern to accomplish our notifications.
 * Any additional module that would like to create a notification simply
 * has to subscribe/attach to the main notification object as an observer
 * and the main notification mechanism will push out the notification to them.
 * I've included our email observer in this file as well as a Skeletal Twitter observer pattern.
*/

include_once('maestro_constants.class.php');

abstract class MaestroNotificationObserver {
  public $displayName;

  function __construct() {
    $this->displayName = "";  //You must give the observer a friendly display name so that the admin console can display it
  }

  abstract function notify(MaestroNotification &$obj);
}

class MaestroNotification {
  protected $_userIDArray = array();
  protected $_userEmailArray = array();
  protected $_observers = array();
  protected $_message = "";
  protected $_subject = "";
  protected $_queueID = 0;
  protected $_notificationType = "";

  /**
   * Constructor
   *
   * @param $users
   *   Mandatory - An array of integers or single integer specifying the Drupal users to notify.
   *
   * @param $defaultMessage
   *   String: The default message to send in the email, overridden with the message stored in the template_data record
   *
   * @param $defaultSubject
   *   String: The default email subject, overridden with the message stored in the template_data record
   *
   * @param $queueID
   *   Integer: The QueueID associated with the message you're sending out
   *
   * @param $type
   *   String: The actual notification type using the MaestroNotificationTypes Constants
   */
  function __construct($defaultMessage = '', $defaultSubject = '', $queueID = 0, $type = MaestroNotificationTypes::ASSIGNMENT) {
    $observers = array();
    $this->_notificationType = $type;
    $this->_queueID = $queueID;
    $this->getNotificationUserIDs();

    $this->setNotificationSubjectAndMessage($defaultMessage, $defaultSubject);

    //Now, lets determine if we've got our observers cached.  If not, lets rebuild that observer list
    //This is how we subscribe to becoming a notification provider for Maestro.
    $observers = cache_get('maestro_notification_observers');
    if($observers === FALSE) {  //build the observer cache
      //need to scan through each available class type and fetch its corresponding context menu.
      foreach (module_implements('maestro_notification_observer') as $module) {
        $function = $module . '_maestro_notification_observer';
        if ($declaredObserver = $function()) {
          foreach($declaredObserver as $observerToCache) {
            $observers[] = $observerToCache;
            $this->_observers[] = $observerToCache;
          }
        }
      }
      cache_set('maestro_notification_observers', $observers);
    }
    else {
      $this->_observers = ($observers->data);
    }
  }

  function setNotificationSubjectAndMessage($defaultMessage, $defaultSubject) {
    global $base_url;

    $fields = array();
    $fields[MaestroNotificationTypes::ASSIGNMENT] = 'pre_notify';
    $fields[MaestroNotificationTypes::COMPLETION] = 'post_notify';
    $fields[MaestroNotificationTypes::REMINDER] = 'reminder';
    $fields[MaestroNotificationTypes::ESCALATION] = 'escalation';

    $query = db_select('maestro_queue', 'a');
    $query->leftJoin('maestro_template_data', 'b', 'a.template_data_id=b.id');
    $query->leftJoin('maestro_template', 'c', 'b.template_id=c.id');
    $query->fields('b', array('taskname'));
    $query->fields('c', array('template_name'));
    $query->addField('b', $fields[$this->_notificationType] . '_message', 'message');
    $query->addField('b', $fields[$this->_notificationType] . '_subject', 'subject');
    $query->condition('a.id', $this->_queueID, '=');
    $rec = $query->execute()->fetchObject();

    $message = ($rec->message == '') ? $defaultMessage : $rec->message;
    $subject = ($rec->subject == '') ? $defaultSubject : $rec->subject;

    //now apply the string replace for the tokens
    $tokens = array('task_console_url' => '[task_console_url]', 'workflow_name' => '[workflow_name]', 'task_name' => '[task_name]', 'task_owner' => '[task_owner]');
    $replace = array();
    $replace['task_console_url'] = $base_url . url('maestro/taskconsole');
    $replace['task_name'] = $rec->taskname;
    $replace['workflow_name'] = $rec->template_name;

    $userQuery = db_select('maestro_production_assignments', 'a');
    $userQuery->leftJoin('users', 'b', 'a.uid=b.uid');
    $userQuery->fields('b', array('name'));
    $userQuery->condition('a.task_id', $this->_queueID, '=');
    $userRes = $userQuery->execute()->fetchAll();

    $replace['task_owner'] = '';
    foreach ($userRes as $userRec) {
      if ($replace['task_owner'] != '') {
        $replace['task_owner'] .= ', ';
      }
      $replace['task_owner'] .= $userRec->name;
    }

    $message = str_replace($tokens, $replace, $message);
    $subject = str_replace($tokens, $replace, $subject);

    $this->_message = $message;
    $this->_subject = $subject;
  }

  function getNotificationUserIDs() {
    if(intval($this->_queueID) > 0 && $this->_notificationType != '') {
      $query = db_select('maestro_queue', 'a');
      $query->fields('a', array('process_id', 'template_data_id'));
      $query->condition('a.id', $this->_queueID, '=');
      $qRec = current($query->execute()->fetchAll());

      $query = db_select('maestro_template_notification', 'a');
      $query->leftJoin('users', 'b', 'a.notify_id=b.uid');
      $query->fields('a', array('notify_id', 'notify_type', 'notify_when', 'notify_by'));
      $query->fields('b', array('uid', 'mail'));
      $query->condition('a.notify_when', $this->_notificationType, '=');
      $query->condition('a.notify_type', MaestroAssignmentTypes::USER, '=');   //@TODO: add support for ROLE and GROUP types
      $query->condition('a.template_data_id', $qRec->template_data_id);
      $res = $query->execute()->fetchAll();

      $this->_userIDArray = array();
      $this->_userEmailArray = array();
      foreach ($res as $rec) {
        if ($rec->notify_by == MaestroAssignmentBy::VARIABLE) {
          $query2 = db_select('maestro_process_variables', 'a');
          $query2->leftJoin('users', 'b', 'a.variable_value=b.uid');
          $query2->fields('b', array('uid', 'mail'));
          $query2->condition('a.process_id', $qRec->process_id, '=');
          $query2->condition('a.template_variable_id', $rec->notify_id, '=');
          $userRec = current($query2->execute()->fetchAll());

          $this->_userIDArray[$rec->uid] = $userRec->uid;
          $this->_userEmailArray[$rec->uid] = $userRec->mail;
        }
        else {
          $this->_userIDArray[$rec->uid] = $rec->uid;
          $this->_userEmailArray[$rec->uid] = $rec->mail;
        }
      }
    }
    else {
      return FALSE;
    }
  }

  function getQueueId() {
    return $this->_queueID;
  }

  function setQueueId($id) {
    $this->_queueID = $id;
  }

  function getNotificationType() {
    return $this->_notificationType;
  }

  function setNotificationType($type) {
    $this->_notificationType = $type;
  }

  function getSubject() {
    return $this->_subject;
  }

  function setSubject($subject) {
    $this->_subject = $subject;
  }

  function getMessage() {
    return $this->_message;
  }

  function setMessage($message) {
    $this->_message = $message;
  }

  function setUserIDs($userIDs) {
    if(is_array($userIDs) && count($userIDs) > 0) {
      $this->_userIDArray = $userIDs;
    }
    else {
      $this->_userIDArray = array();
      $this->_userIDArray[] = $userIDs;
    }
  }

  function getUserIDs(){
    return $this->_userIDArray;
  }

  function getUserEmailAddresses($userid = 0) {
    $userid = intval($userid);
    if ($userid == 0) return $this->_userEmailArray;

    //add the array entry if it doesnt exist (like in some cases with the outstandind task reminder action
    if (!array_key_exists($userid, $this->_userEmailArray) && $userid > 0) {
      $query = db_select('users', 'a');
      $query->fields('a', array('uid', 'mail'));
      $query->condition('a.uid', $userid, '=');
      $userRec = current($query->execute()->fetchAll());

      $this->_userIDArray[$userRec->uid] = $userRec->uid;
      $this->_userEmailArray[$userRec->uid] = $userRec->mail;
    }

    return $this->_userEmailArray[$userid];
  }

  public function attach(MaestroNotificationObserver $observer) {
    $this->_observers[] = $observer;
  }

  /*
   * notify method
   * Responsible for pushing out the notifications to the subscribed notification mechanisms
   * Notify will be disabled when the configuration option is disabled.
   */
  public function notify() {
    if(variable_get('maestro_enable_notifications',1) == 1) {
      //we are now going to check if the maestro_enabled_notifiers is set.  If its not set, we will just set all observers to be enabled
      $enabled_notifiers = variable_get('maestro_enabled_notifiers');
      if($enabled_notifiers == NULL) {
        if(is_array($this->_observers) && count($this->_observers) > 0 ) {
          foreach($this->_observers as $obj) {
            if(class_exists($obj)) {
             $notifyObject = new $obj();
              $notifyObject->notify($this);
            }
          }
        }
      }
      else {
        foreach($enabled_notifiers as $obj) {
          if(class_exists($obj)) {
            $notifyObject = new $obj();
            $notifyObject->notify($this);
          }
        }
      }
    }
  }
}


/*
 * Here is the implementation of the observer pattern where we implement the MaestroNotificationObserver interface.
 * The only method we MUST implement is the notify where we accept the passed in object by reference to save memory.
 */

class MaestroEmailNotification extends MaestroNotificationObserver {

  public function __construct() {
    $this->displayName = "Maestro Email Notifier";
  }

  public function notify(MaestroNotification &$obj) {
    //now, we're offloading the notification to this class to do whatever it needs to do.
    $from = variable_get('site_mail', 'admin@example.com');
    $send = TRUE;
    if(is_array($obj->getUserIDs())) {
      foreach($obj->getUserIDs() as $userID) {
        $to =  $obj->getUserEmailAddresses($userID);
        $params = array('message' => $obj->getMessage(), 'subject' => $obj->getSubject(), 'queueID' => $obj->getQueueId());
        $result = drupal_mail('maestro', $obj->getNotificationType(), $to, language_default(), $params, $from, $send);
      }
    }
  }

}


class MaestroWatchDogNotification extends MaestroNotificationObserver {

  public function __construct() {
    $this->displayName = "Watchdog Notifier";
  }

  public function notify(MaestroNotification &$obj) {
    if(is_array($obj->getUserIDs())) {
      foreach($obj->getUserIDs() as $userID) {
        watchdog('Maestro', "Notification issued for UserID: ". $userID . " email address: " . $obj->getUserEmailAddresses($userID));
      }
    }
  }
}


/*
 * This is just a sample stub observer pattern for anyone to use and how simple it is to implement
 * You need to enable this observer in the maestro.module file in the maestro_maestro_notification_observer function
 * by adding 'SAMPLEMaestroTwitterNotification' in the return array.  Clear your cache and this observer pattern will
 * automatically be added and subscribed.
 * If you are writing your own Maestro task/notification module, please implement your own MODULENAME_maestro_notification_observer hook
 * and do not edit the main maestro.module file.
 */

class SAMPLEMaestroTwitterNotification extends MaestroNotificationObserver {

  public function __construct() {
    $this->displayName = "Sample Twitter Notifier";
  }

  public function notify(MaestroNotification &$obj) {
    if(is_array($obj->getUserIDs())) {
      foreach($obj->getUserIDs() as $userID) {
        //send a twitter update however that is done :-)
        //echo "twitter update to userID:" . $userID;
      }
    }
  }
}

