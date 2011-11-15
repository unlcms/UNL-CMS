<?php

/**
 * Settings for admin/people/import setup in unl_cas.module
 */
function unl_cas_user_import($form, &$form_state) {
  $form['root'] = array(
    '#type' => 'fieldset',
    '#title' => 'Import from UNL Directory.',
  );

  $form['root']['account'] = array();

  $form['root']['account']['name'] = array(
    '#type' => 'textfield',
    '#title' => 'Search String',
    '#description' => 'The name or username of the person to search for.',
#    '#required' => TRUE,
  );

  $form['root']['submit'] = array(
    '#type' => 'submit',
    '#value' => 'Search',
    '#submit' => array('unl_cas_user_import_search'),
#    '#validate' => array('unl_cas_user_validate'),
  );

  if (isset($form_state['values']['name'])) {
    $matches = array();
    $search = $form_state['values']['name'];
    // TODO Use directory for now, use LDAP query once this app is granted access
    $results = json_decode(file_get_contents('http://directory.unl.edu/service.php?q='.urlencode($search).'&format=json&method=getLikeMatches'));
    foreach ($results as $result) {
      $displayName = $result->displayName->{0};
      $uid = $result->uid;
      $mail = $result->mail;
      $affiliations = array();
      foreach ($result->eduPersonPrimaryAffiliation as $affiliation) {
        $affiliations[] = $affiliation;
      }
      $affiliations = implode(', ', $affiliations);
      // TODO Need to handle incomplete results better
      if ($uid) {
        $matches[$uid] = "$displayName ($affiliations) ($uid)";
      }
    }

    $form['root']['account']['username'] = array(
      '#type' => 'radios',
      '#title' => sizeof($matches).' Records Found',
#      '#required' => TRUE,
      '#options' => $matches,
    );

    $form['root']['submit']['#value'] = 'Search Again';

    $form['root']['submit2'] = array(
      '#type' => 'submit',
      '#value' => 'Add Selected User',
      '#submit' => array('unl_cas_user_import_submit'),
    );
  }
  return $form;
}

function unl_cas_user_import_search($form, &$form_state) {
  // if only one result is returned should we instead create the user?
  $form_state['rebuild'] = TRUE;
}

function unl_cas_user_import_submit($form, &$form_state) {
  if (!$form_state['values']['username']) {
    drupal_set_message('Please select a user.', 'error');
    $form_state['rebuild'] = TRUE;
    return;
  }

  $user = unl_cas_import_user($form_state['values']['username']);

  if ($user) {
    drupal_set_message('User ' . $form_state['values']['username'] . ' successfully created.');
    drupal_goto('user/'.$user->uid.'/edit', array('query'=>array('destination'=>'admin/people')));
  } else {
    drupal_set_message('An error occured importing the user.', 'error');
  }
}

/**
 * Settings for admin/config/people/unl_cas setup in unl_cas.module
 */
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
