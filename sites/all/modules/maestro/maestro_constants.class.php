<?php
// $Id:

/**
 * @file
 * maestro_constants.class.php
 *
 * Description:  This file holds all of the classes used to define constants
 *
 */

class MaestroNotificationTypes {
  CONST ASSIGNMENT = 1;
  CONST COMPLETION = 2;
  CONST REMINDER = 3;
  CONST ESCALATION = 4;

  static public function getStatusLabel($var=NULL) {
    $labels = array();
    $labels[self::ASSIGNMENT] = array('name' => 'ASSIGNMENT', 'label' => t('On Assignment'));
    $labels[self::COMPLETION] = array('name' => 'COMPLETION', 'label' => t('On Completion'));
    $labels[self::REMINDER] = array('name' => 'REMINDER', 'label' => t('Reminder'));
    $labels[self::ESCALATION] = array('name' => 'ESCALATION', 'label' => t('Escalation'));

    return ($var === NULL) ? $labels:$labels[$var];
  }
}

// The maestro Engine's clean queue will not pick up any status' less than 0
class MaestroTaskStatusCodes {
  CONST STATUS_DELETED = -2;
  CONST STATUS_ON_HOLD = -1;
  CONST STATUS_READY = 0;
  CONST STATUS_COMPLETE = 1;
  CONST STATUS_ABORTED = 2;
  CONST STATUS_IF_CONDITION_FALSE = 3;

  static public function getStatusLabel($var=NULL) {
    $labels = array();
    $labels[self::STATUS_DELETED] = t('Deleted');
    $labels[self::STATUS_ON_HOLD] = t('On Hold');
    $labels[self::STATUS_READY] = t('Ready');
    $labels[self::STATUS_COMPLETE] = t('Complete');
    $labels[self::STATUS_ABORTED] = t('Aborted');
    $labels[self::STATUS_IF_CONDITION_FALSE] = t('If Condition False');

    return ($var === NULL) ? $labels:$labels[$var];
  }

}

// Maestro Project or workflow instance entity status codes
class MaestroProjectStatusCodes {
  CONST STATUS_ON_HOLD = -1;
  CONST STATUS_ACTIVE = 0;
  CONST STATUS_COMPLETED = 1;
  CONST STATUS_CANCELLED = 2;
  CONST STATUS_REGENERATED = 3;

  static public function getStatusLabel($var=NULL) {
    $labels = array();
    $labels[self::STATUS_ON_HOLD] = t('On Hold');
    $labels[self::STATUS_ACTIVE] = t('Active');
    $labels[self::STATUS_COMPLETED] = t('Completed');
    $labels[self::STATUS_CANCELLED] = t('Cancelled');
    $labels[self::STATUS_REGENERATED] = t('Regenerated');

    return ($var === NULL) ? $labels:$labels[$var];
  }
}


// Maestro Project or workflow instance entity status codes
class MaestroContentStatusCodes {
  CONST STATUS_UNDEFINED = 0;
  CONST STATUS_SUBMITTED = 1;
  CONST STATUS_DRAFT = 2;
  CONST STATUS_UNAPPROVED = 3;
  CONST STATUS_UNDER_REVIEW = 4;
  CONST STATUS_ACCEPTED = 10;
  CONST STATUS_PUBLISHED = 11;
  CONST STATUS_REJECTED = 20;


  static public function getStatusLabel($var=NULL) {
    $labels = array();
    $labels[self::STATUS_UNDEFINED] = t('Un-Defined');
    $labels[self::STATUS_SUBMITTED] = t('Submitted');
    $labels[self::STATUS_DRAFT] = t('Draft');
    $labels[self::STATUS_UNAPPROVED] = t('Unapproved');
    $labels[self::STATUS_UNDER_REVIEW] = t('Under Review');
    $labels[self::STATUS_ACCEPTED] = t('Accepted');
    $labels[self::STATUS_PUBLISHED] = t('Published');
    $labels[self::STATUS_REJECTED] = t('Rejected');

    return ($var === NULL) ? $labels:$labels[$var];
  }
}


// Maestro Process entity status codes
class MaestroProcessStatusCodes {
  CONST STATUS_ON_HOLD = -1;
  CONST STATUS_ACTIVE = 0;
  CONST STATUS_COMPLETED = 1;
  CONST STATUS_REGENERATED = 2;

  static public function getStatusLabel($var=NULL) {
    $labels = array();
    $labels[self::STATUS_ON_HOLD] = t('On Hold');
    $labels[self::STATUS_ACTIVE] = t('Active');
    $labels[self::STATUS_COMPLETED] = t('Completed');
    $labels[self::STATUS_REGENERATED] = t('Regenerated');

    return ($var === NULL) ? $labels:$labels[$var];
  }
}

class MaestroAssignmentTypes {
  CONST USER = 1;
  CONST ROLE = 2;
  CONST GROUP = 3;

  static public function getStatusLabel($var=NULL) {
    $labels = array();
    $labels[self::USER] = array('name' => 'USER', 'label' => t('User'));
    $labels[self::ROLE] = array('name' => 'ROLE', 'label' => t('Role'));
    $labels[self::GROUP] = array('name' => 'GROUP', 'label' => t('Organic Group'));

    return ($var === NULL) ? $labels:$labels[$var];
  }
}

//options for the assignment and notification, it can be either variable or static
class MaestroAssignmentBy {
  CONST FIXED = 1;
  CONST VARIABLE = 2;

  static public function getStatusLabel($var=NULL) {
    $labels = array();
    $labels[self::FIXED] = array('name' => 'FIXED', 'label' => t('Fixed'));
    $labels[self::VARIABLE] = array('name' => 'VARIABLE', 'label' => t('Variable'));

    return ($var === NULL) ? $labels:$labels[$var];
  }
}

class MaestroInteractiveFlag {
  CONST IS_INTERACTIVE = 1;
  CONST IS_NOT_INTERACTIVE = 0;
}
