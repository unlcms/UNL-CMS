<?php

error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);



function maestro_createtestworkflow() {

}

function test_observers() {
  include('maestro_notification.class.php');

  $notification = new MaestroNotification(1,'this is a test message','Sample Subject',0);
  $notification->notify();
}