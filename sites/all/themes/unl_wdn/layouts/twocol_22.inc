<?php
// $Id: twocol_stacked.inc,v 1.1.2.1 2008/12/16 21:27:59 merlinofchaos Exp $

/**
 * Implementation of hook_panels_layouts().
 */
function unl_wdn_twocol_22_panels_layouts() {
  $items['twocol_22'] = array(
    'title' => t('UNL Two Column (2/2 layout)'),
    'icon' => 'twocol_22.png',
    'theme' => 'twocol_22',
    'panels' => array(
      'left' => t('Left side'),
      'right' => t('Right side'),
    ),
  );

  return $items;
}
