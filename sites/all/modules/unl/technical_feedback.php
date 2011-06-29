<?php

function technical_feedback(){
	echo drupal_render(drupal_get_form('technical_feedback_form'));
}

function technical_feedback_form($form, &$form_state) {

	 $form['root'] = array(
      	'#type' => 'fieldset',
      	'#title' => 'Technical Feeedback Form',
  	);
  	
  	$form['root']['browser_useragent'] = array(
  		'#type' => 'textfield',
  		'#title' => t('Browser UserAgent (textfield disabled)'),
  		'#value' => $_SERVER['HTTP_USER_AGENT'],
  		'#disabled' => 'disabled',
  	);
  	
  	$form['root']['current_url'] = array(
  		'#type' => 'textfield',
  		'#title' => t('Current url (text disabled)'),
  		'#value' => $_SERVER['HTTP_REFERER'],
  		'#disabled' => 'disabled'
  	);
  	
  	 $form['root']['cas_username'] = array(
        '#type' => 'textfield',
        '#title' => t('CAS Name (textfield disabled'),
        '#value' => ($GLOBALS['user']->name),
        '#disabled' => 'disabled',
       
  	);
  	
  	$form['root']['email'] = array(
  		'#type' => 'textfield',
  		'#title' => t('Email (textfield disabled)'),
  		'#value' => ($GLOBALS['user']->mail),
  		'#disabled' => 'disabled'
  
  	);
  	
  	$form['root']['full_name'] = array(
  		'#type' => 'textfield',
  		'#title' => t('Your first, last name'),
  		'#required' => TRUE,
  	);
  	
  	$form['root']['technical_feedback'] = array(
  		'#type' => 'textarea',
  		'#title' => t('Please give your feedback or describe the issue you are having'),
  		'#required' => TRUE,
  	);
  	
  	$form['root']['submit'] = array(
  		'#type' => 'submit',
  		'#value' => t('Submit'),
  	);
  	    	
  	
  	return $form;    	
}

function technical_feedback_form_submit($form, &$form_state){
	$browser_useragent 		= $form_state['values']['browser_useragent'];
	$current_url					= $form_state['values']['current_url'];
	$cas_username					= $form_state['values']['cas_username'];
	$email								= $form_state['values']['email'];
	$full_name						= $form_state['values']['full_name'];
	$technical_feedback		= $form_state['values']['technical_feedback'];
	
	$to = "unlcms-dev@listserv.unl.edu";
	$from = $email;
	$subject = "UNLcms Technical Feedback";
	
	$message = '
	<html>
		<body>
			<table style="border:1px solid #bbb;cellpadding="10";">
				<tr style="background-color: #eee;">
					<td>Browser UserAgent</td><td>'.$browser_useragent.'</td>
				</tr>
				<tr>
					<td>CAS Username</td><td>'.$cas_username.'</td>
				</tr>
				<tr style="background-color: #eee;">
					<td>Full Name</td><td>'.$full_name.'</td>
				</tr>
				<tr>
					<td>Email</td><td>'.$email.'</td>
				</tr>
				<tr style="background-color: #eee;">
					<td>Page URL</td><td>'.$current_url.'</td>
				</tr>
				<tr>				
					<td>Technical Feedback/Issue</td><td>'.$technical_feedback.'</td>
				</tr>
			</table>
		</body>
	</html>
	';
	
	$technical_feedback_email_headers 	= 'From: ' . $email . "\r\n";

	$technical_feedback_email_headers  .= "MIME-Version: 1.0\r\n";
	$technical_feedback_email_headers  .= "Content-type:text/html; charset=ISO-8859-1\r\n";
	
		
	mail($to, $subject, $message, $technical_feedback_email_headers);
	
	
	drupal_set_message(t('Your feedback has been emailed to the UNLcms dev team. Thank you!'));
	$form_state['redirect'] = $current_url;
  return;
}
