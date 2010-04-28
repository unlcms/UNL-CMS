<?php
// $Id: twocol_stacked.inc,v 1.1.2.1 2008/12/16 21:27:59 merlinofchaos Exp $

/**
 * Implementation of hook_panels_layouts().
 */
function unl_wdn_fourcol_panels_layouts() {
  $items['fourcol'] = array(
    'title' => t('UNL Four Column'),
    'icon' => 'fourcol.png',
    'theme' => 'fourcol',
    'panels' => array(
      'left' => t('Far Left'),
      'center-left' => t('Center Left'),
      'center-right' => t('Center Right'),
      'right' => t('Far Right'),
    ),
  );

  return $items;
}
