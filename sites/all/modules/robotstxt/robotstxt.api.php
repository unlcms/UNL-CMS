<?php
// $Id: robotstxt.api.php,v 1.2.2.2 2011/01/05 23:24:10 hass Exp $

/**
 * @file
 * Hooks provided by the robotstxt module.
 */

/**
 * Add additional lines to the site's robots.txt file.
 *
 * @return
 *   An array of strings to add to the robots.txt.
 */
function hook_robotstxt() {
  return array(
    'Disallow: /foo',
    'Disallow: /bar',
  );
}
