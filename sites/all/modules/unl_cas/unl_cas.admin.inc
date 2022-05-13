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
    '#validate' => array('unl_cas_user_import_validate_search'),
  );

  if (isset($form_state['values']['name'])) {
    $matches = array();
    $search = $form_state['values']['name'];
    // TODO Use directory for now, use LDAP query once this app is granted access
    try {
      $ldap = new Unl_Ldap(unl_cas_get_setting('ldap_uri'));
      $ldap->bind(unl_cas_get_setting('ldap_dn'), unl_cas_get_setting('ldap_password'));

      $searchFields = array('sAMAccountName', 'mail', 'unlMail', 'cn', 'givenName', 'sn', 'eduPersonNickname');
      $filter = '(&';
      foreach (preg_split('/\s+/', $search) as $searchTerm) {
        $searchTerm = str_replace(array('"', ',', '*'), '', $searchTerm);
        $filter .= '(|';
        foreach ($searchFields as $searchField) {
          $filter .= '(' . $searchField . '=*' . $searchTerm . '*)';
        }
        $filter .= '))';
      }

      // Convert the uid to a string to match with peoplefinder results.
      $results = $ldap->search('ou=people,dc=unl,dc=edu', $filter);
      foreach ($results as $key => $result) {
        $results[$key]['uid'] = $result['samaccountname'][0];
      }
    }
    catch (Exception $e) {
      $results = json_decode(file_get_contents('https://directory.unl.edu/service.php?q='.urlencode($search).'&format=json&method=getLikeMatches'), TRUE);

      // Convert each key to lowercase to mimic the LDAP results.
      foreach ($results as $index => $result) {
        foreach ($result as $key => $value) {
          $results[$index][strtolower($key)] = $value;
        }
      }
    }

    $ldap = new Unl_Ldap(unl_cas_get_setting('ldap_uri'));
    foreach ($results as $result) {
      if (!$result['uid']) {
        continue;
      }
      $displayName = $result['displayname'][0];
      $uid = $result['uid'];
      $affiliations = array();
      if (isset($result['edupersonaffiliation'])) {
        foreach ($result['edupersonaffiliation'] as $affiliation) {
          $affiliations[] = $affiliation;
        }
      }
      $affiliations = implode(', ', $affiliations);
      // TODO Need to handle incomplete results better
      if ($uid && !empty($affiliations)) {
        $matches[$uid] = "$displayName ($affiliations) ($uid)";
      }
      else if ($uid) {
        $matches[$uid] = "$displayName ($uid)";
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
      '#validate' => array('unl_cas_user_import_validate_add'),
    );
  }
  return $form;
}

function unl_cas_user_import_search($form, &$form_state) {
  if (!$form_state['values']['name']) {
    form_set_error('username', 'Please enter a search string.');
  }
  // if only one result is returned should we instead create the user?
  $form_state['rebuild'] = TRUE;
}

function unl_cas_user_import_validate_search($form, &$form_state) {
  if (!$form_state['values']['name']) {
    form_set_error('username', 'Please enter a search term.');
  }
}

function unl_cas_user_import_validate_add($form, &$form_state) {
  if (!$form_state['values']['username']) {
    form_set_error('username', 'Please select a user.');
  }
}

function unl_cas_user_import_submit($form, &$form_state) {

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

  $form['ldap']['seconds'] = array(
    '#type' => 'textfield',
    '#title' => 'Seconds between lookups',
    '#description' => 'For existing users, the minimum length of time to wait before doing another lookup upon login.',
    '#default_value' => unl_cas_get_setting('ldap_seconds'),
    '#parents' => array('ldap', 'seconds'),
    '#required' => TRUE,
    '#element_validate' => array('element_validate_number'),
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
  unl_cas_set_setting('ldap_seconds', $form_state['values']['ldap']['seconds']);
}
