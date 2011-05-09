<?php

function unl_cas_user_import($form, &$form_state) {
  $form['root']['#type'] = 'fieldset';
  $form['root']['#title'] = 'Import from peoplefinder.';
	$form['account']['name'] = array();
  $form['account']['name']['#type'] = 'textfield';
  $form['account']['name']['#title'] = 'Search String';
  $form['account']['name']['#maxlength'] = 60;
  $form['account']['name']['#description'] = 'The name or username of the person to search for.';
#  $form['account']['name']['#required'] = 1;
  $form['account']['name']['#attributes'] = array();
  $form['account']['name']['#attributes']['class'] = array();
  $form['account']['name']['#attributes']['class'][0] = 'username';
  $form['account']['name']['#default_value'] = '';
	$form['account']['name']['#access'] = 1;
  $form['account']['name']['#weight'] = -10;
  $form['submit']['#type'] = 'submit';
  $form['submit']['#value'] = 'Search';
  $form['submit']['#submit'] = array('unl_cas_user_import_search');
#  $form['submit']['#validate'] = array('unl_cas_user_validate');

	if(isset($form_state['searchstring'])){
		$matches=array();
		$search=$form_state['searchstring'];
		// TODO Use peoplefinder for now, use LDAP query once this app is granted access
		$results = explode('</person>', file_get_contents('http://peoplefinder.unl.edu/service.php?q='.urlencode($search).'&format=xml&method=getLikeMatches'));
		foreach($results as $result) {
		  $displayName=$uid=$affiliation=$mail=array();
		  preg_match('/displayName>([^<]*)/', $result, $displayName);
		  preg_match('/<uid>([^<]*)/', $result, $uid);
		  preg_match('/<mail>([^<]*)/', $result, $mail);
		  preg_match('/<eduPersonPrimaryAffiliation>([^<]*)/', $result, $affiliation);
			// TODO Need to handle incomplete results better
			if(sizeof($uid)>1) {
				$matches[$uid[1].'_'.(sizeof($mail)>1?$mail[1]:'')]=$displayName[1].' ('.$affiliation[1].') ('.$uid[1].')';
			}
		}
		$form['account']['radio']=array();
		$form['account']['radio']['#type'] = 'radios';
		$form['account']['radio']['#title'] = sizeof($matches).' Records Found';
#		$form['account']['radio']['#required'] = 1;
		$form['account']['radio']['#options'] = $matches;

  	$form['submit']['#value'] = 'Search Again';
	  $form['submit2']['#type'] = 'submit';
	  $form['submit2']['#value'] = 'Add Selected User';
	  $form['submit2']['#submit'] = array('unl_cas_user_import_submit');
	}
  return $form;
}

function unl_cas_user_import_search($form, &$form_state) {
	// if only one result is returned should we instead create the user?
	$form_state['searchstring']=$form['account']['name']['#value'];
	$form_state['rebuild']=TRUE;
}

function unl_cas_user_import_submit($form, &$form_state) {
	list($name, $mail) = explode('_', $form_state['complete form']['account']['radio']['#value']);
	$userData = array(
		'name' => $name,  'mail' => $mail, 'status' => 1
	);
	$user = user_save(NULL, $userData);
	if($user) drupal_set_message('<li>User '.$name.' successfully created.</li>');
}

function unl_cas_config($form, &$form_state) {
  
  $form['ldap'] = array(
    '#tree' => TRUE,
    '#type'  => 'fieldset',
    '#title' => 'LDAP Settings',
  );
  
  $form['ldap']['uri'] = array(
    '#type' => 'textfield',
    '#title' => 'URI',
    '#description' => 'ie: ldap://example.com/',
    '#default_value' => unl_cas_get_setting('ldap_uri'),
    '#parents' => array('ldap', 'uri'),
    '#required' => TRUE,
  );
  
  $form['ldap']['dn'] = array(
    '#type' => 'textfield',
    '#title' => 'Distinguished Name (DN)',
    '#description' => 'ie: uid=admin,dc=example,dc=com',
    '#default_value' => unl_cas_get_setting('ldap_dn'),
    '#parents' => array('ldap', 'dn'),
    '#required' => TRUE,
  );
  
  $form['ldap']['password'] = array(
    '#type' => 'password',
    '#title' => 'Password',
    '#parents' => array('ldap', 'password'),
    '#required' => TRUE,
  );
  
  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => 'Update',
  );
  
  return $form;
}

function unl_cas_config_submit($form, &$form_state) {
  unl_cas_set_setting('ldap_uri', $form_state['values']['ldap']['uri']);
  unl_cas_set_setting('ldap_dn', $form_state['values']['ldap']['dn']);
  unl_cas_set_setting('ldap_password', $form_state['values']['ldap']['password']);
}
