<?php

/**
 * Determine if we should force varnish for a user
 *
 * @param $username
 *
 * @return bool
 */
function unl_cas_smart_cache_force_varnish_for_user($username) {
  $account = user_load_by_name($username);
  
  if (!$account) {
    //Couldn't find the user, so don't use smart cache.
    return true;
  }
  
  $user_role = array_shift(array_values($account->roles));
  if (count($account->roles) === 1 && 'authenticated user' === $user_role) {
    //The user has more than one role, which means that they are more than just a guest
    return true;
  }
  
  //TODO: check if the user has any edit access to specific nodes (sounds like this might be possible)
  
  //else there is only one role left... make sure it is the authenticated user role
  return false;
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
    //Turn off smart cache because unl_access is enabled... We could improve this so only specific resources protected by unl_access are not cached.
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
  //do we really need to log the user in? Should we set a cookie to have VARNISH ignore the logged in state?
  if (unl_cas_smart_cache_force_varnish_for_user($username) && unl_cas_smart_cache_force_varnish_for_site()) {
    //set a cookie to tell varnish to always run
    setcookie('unlcms_force_varnish', 'true', 0, base_path());

    //Redirect back
    $destination = drupal_get_destination();
    unset($_GET['destination']);
    drupal_goto($destination['destination']);

    //don't proceed.
    return;
  } else {
    //make sure the force_varnish cookie is turned off (delete the cookie
    setcookie('unlcms_force_varnish', 'false', time() - 3600, base_path());
  }
}
