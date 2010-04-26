<?php
// $Id: twocol_stacked.inc,v 1.1.2.1 2008/12/16 21:27:59 merlinofchaos Exp $

/**
 * Implementation of hook_panels_layouts().
 */
function unl_wdn_threecol_112_panels_layouts() {
  $items['threecol_112'] = array(
    'title' => t('UNL Three Column (1/1/2 layout)'),
    'icon' => 'threecol_112.png',
    'theme' => 'threecol_112',
    'panels' => array(
      'left' => t('Left side'),
      'center' => t('Center'),
      'right' => t('Right side'),
    ),
  );

  return $items;
}
