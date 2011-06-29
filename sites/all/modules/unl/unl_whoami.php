<?php

/**
 * if a user is logged in, it returns the name, mail
 */
function unl_whoami() {
	if($GLOBALS['user']->uid) {
		echo 'user_loggedin';
	}
}