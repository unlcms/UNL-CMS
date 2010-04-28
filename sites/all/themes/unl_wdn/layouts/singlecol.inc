<?php
// $Id: twocol_stacked.inc,v 1.1.2.1 2008/12/16 21:27:59 merlinofchaos Exp $

/**
 * Implementation of hook_panels_layouts().
 */
function unl_wdn_singlecol_panels_layouts() {
  $items['singlecol'] = array(
    'title' => t('UNL Single Column'),
    'icon' => 'singlecol.png',
    'theme' => 'singlecol',
    'panels' => array(
      'center' => t('Center')
    ),
  );

  return $items;
}
