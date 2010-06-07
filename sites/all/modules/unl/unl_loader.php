<?php

function unl_load_zend_framework()
{
	static $isLoaded = FALSE;
	
	if ($isLoaded) {
		return;
	}
	
	set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__) . '/../../libraries' );
    require_once 'Zend/Loader/Autoloader.php';
    $autoloader = Zend_Loader_Autoloader::getInstance();
    $autoloader->registerNamespace('Unl_');
    $isLoaded = TRUE;
}
