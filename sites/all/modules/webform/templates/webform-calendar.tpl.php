<?php
// $Id: webform-calendar.tpl.php,v 1.2 2010/04/11 06:14:20 quicksketch Exp $

/**
 * @file
 * Theme the button for the date component date popup.
 */
?>
<input type="image" src="<?php print base_path() . drupal_get_path('module', 'webform') . '/images/calendar.png'; ?>" class="<?php print implode(' ', $calendar_classes); ?>" title="<?php print t('Open popup calendar'); ?>" />
