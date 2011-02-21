<?php
  
  /* Using Drupal OO Coding Standards as described: http://drupal.org/node/608152 */
  
  class MaestroEngineVersion2 extends MaestroEngine {
      
      var $_version = '2.x';
      var $_properties;
      
      function __construct($options) {
        echo "<br>Version 2 __constructor";
        print_r($options);
        $this->_properties = $options;        
      }
      
      
      public function getVersion() {
        return $this->_version;      
      }   
    
      function cleanQueue() {}
      
    function assignTask($queueId,$userObject) {}
    
    function completeTask($queueId) {}     
    
    function archiveTask($queueId) {}    
    
    function cancelTask($queueId) {}    

    function getProcessVariable($variable) {}
    
    function setProcessVariable($variable,$value) {}    

  }
  
  
