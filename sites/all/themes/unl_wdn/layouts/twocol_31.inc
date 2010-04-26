<?php
// $Id: twocol_stacked.inc,v 1.1.2.1 2008/12/16 21:27:59 merlinofchaos Exp $

/**
 * Implementation of hook_panels_layouts().
 */
function unl_wdn_twocol_31_panels_layouts() {
  $items['twocol_31'] = array(
    'title' => t('UNL Two Column (3/1 layout)'),
    'icon' => 'twocol_31.png',
    'theme' => 'twocol_31',
    'panels' => array(
      'left' => t('Left side'),
      'right' => t('Right side'),
    ),
  );

  return $items;
}
