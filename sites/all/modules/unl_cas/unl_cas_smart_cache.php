<?php

/**
 * Determine if we should force varnish for a user.
 *
 * @param $username
 *
 * @return bool
 */
function unl_cas_smart_cache_force_varnish_for_user($username) {
  $account = user_load_by_name($username);
  
  if (!$account) {
    // Couldn't find the user, so don't use smart cache.
    return false;
  }

  if (count($account->roles) > 1) {
    // The user has more than one role, which means that they are more than just a guest.
    return false;
  }
  
  // Check if the user is the author of any nodes.
  $result = db_query('SELECT COUNT(nid) FROM {node} where uid = :uid', array(':uid' => $account->uid))->fetchfield();
  if ($result > 0) {
    return false;
  }

  return true;
}

/**
 * Determine if we should use 'smart caching' for this site. In this case, smart caching is when we force varnish cache if the user has no role on the site.
 *
 * @return bool
 */
function unl_cas_smart_cache_force_varnish_for_site() {
  if ('disable' === unl_cas_get_setting('disable_smart_cache')) {
    return false;
  }
  
  if (module_exists('unl_access')) {
    // Turn off smart cache because unl_access is enabled... We could improve this so only specific resources protected by unl_access are not cached.
    return false;
  }
  
  return true;
}

/**
 * This will set the appropriate cookies and redirect if we need to. It should be ran right after CAS authentication and before a user account is created.
 * 
 * @param $username
 */
function unl_cas_smart_cache_run_for_user($username) {

  if (unl_cas_smart_cache_force_varnish_for_user($username) && unl_cas_smart_cache_force_varnish_for_site()) {
    // Set a cookie to tell varnish to always run
    setcookie('unlcms_force_varnish', 'true', 0, base_path());

    // Redirect back. If they are trying to visit 'user' just send them to the front page.
    $destination = drupal_get_destination();
    if ($destination['destination'] !== 'user' && $destination['destination'] !== 'user/cas') {
      unset($_GET['destination']);
      drupal_goto($destination['destination']);
    }
    else {
      unset($_GET['destination']);
      drupal_goto('<front>');
    }

    // Don't proceed.
    return;
  }
  else {
    // Make sure the force varnish cookie is turned off. (Delete the cookie.)
    setcookie('unlcms_force_varnish', 'false', time() - 3600, base_path());
  }
}
