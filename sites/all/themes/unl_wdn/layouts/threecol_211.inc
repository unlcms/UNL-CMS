<?php
// $Id: twocol_stacked.inc,v 1.1.2.1 2008/12/16 21:27:59 merlinofchaos Exp $

/**
 * Implementation of hook_panels_layouts().
 */
function unl_wdn_threecol_211_panels_layouts() {
  $items['threecol_211'] = array(
    'title' => t('UNL Three Column (2/1/1 layout)'),
    'icon' => 'threecol_211.png',
    'theme' => 'threecol_211',
    'panels' => array(
      'left' => t('Left side'),
      'center' => t('Center'),
      'right' => t('Right side'),
    ),
  );

  return $items;
}
