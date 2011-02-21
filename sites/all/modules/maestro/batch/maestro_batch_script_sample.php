<?php
// $Id: maestro_batch_script_sample.php,v 1.2 2010/08/24 15:51:22 randy Exp $


/**
 * @file
 * maestro_batch_script_sample.php
 *
 * Description:  This is a sample file for showing how Maestro can link in at task execution time, an entire script.
 * In order for the engine to detect that the execution has been successful, you must set a key variable named $success to
 * the boolean value of TRUE.
 *
 * Since this is a simple example, we are not doing anything terribly important in this script, so we are just setting the success variable
 * to TRUE.
 * If you do not set $success to TRUE, $success is set as FALSE in the engine and the task will not flag its execution as complete.
 *
 * Use case scenarios for this task type include running 3rd party scripts, running fully written scripts outside of web root, and running
 * non-included-in-Drupal scripts.
 * Batch Tasks differ from Batch Function Tasks in that a batch task is 100% free form code that is included at task execution time.
 *
 *
 */


//Perform any batch automation functions in here.


$success = TRUE;  //If we don't set this to TRUE here, the engine will re-execute this code on the next scheduled task execution phase.