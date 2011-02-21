<?php

  /* Using Drupal OO Coding Standards as described: http://drupal.org/node/608152 */

  class Maestro {

    private static $MAESTRO;
    private $_engine = null;

    function createMaestroObject ($version, $options = FALSE){
        if (!isset(self::$MAESTRO)) {
            // instance does not exist, so create it
            self::$MAESTRO = new self($version, $options);
        } else {
          return self::$MAESTRO;
        }
        return self::$MAESTRO;
    }

    function __construct($version, $options = FALSE) {
      include_once './' . drupal_get_path('module', 'maestro') . '/maestro_base_engine.class.php';
      include_once './' . drupal_get_path('module', 'maestro') . '/maestro_tasks.class.php';
      $classfile = drupal_get_path('module','maestro')."/maestro_engine_version{$version}.class.php";
      if (require_once $classfile) {
        $class = "MaestroEngineVersion{$version}";
        if (class_exists($class)) {
          $this->_engine = new $class($options);
        } else {
          die("maestro.class - Unable to instantiate class $class from $classfile");
        }
      } else {
        die("maestro.class - Unable to include file: $classfile");
      }
    }


   /**
   * Returns a reference to the instantiated engine object for use in working with the engine.
   *
   * @code
   * $x = $maestro->engine()->newprocess(1);
   * @endcode
   *
   * @return
   *   A reference to the engine object
   */
    public function engine(){
      return $this->_engine;
    }
}