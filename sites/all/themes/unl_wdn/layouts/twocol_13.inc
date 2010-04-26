<?php
// $Id: twocol_stacked.inc,v 1.1.2.1 2008/12/16 21:27:59 merlinofchaos Exp $

/**
 * Implementation of hook_panels_layouts().
 */
function unl_wdn_twocol_13_panels_layouts() {
  $items['twocol_13'] = array(
    'title' => t('UNL Two Column (1/3 layout)'),
    'icon' => 'twocol_13.png',
    'theme' => 'twocol_13',
    'panels' => array(
      'left' => t('Left side'),
      'right' => t('Right side'),
    ),
  );

  return $items;
}
