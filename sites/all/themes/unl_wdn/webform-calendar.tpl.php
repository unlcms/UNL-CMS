<?php

/**
 * @file
 * Theme the button for the date component date popup.
 */
?>
<?php if (theme_get_setting('zen_forms')) {
  print '<li>';
} ?>
<input type="image" src="<?php print base_path() . drupal_get_path('module', 'webform') . '/images/calendar.png'; ?>" class="<?php print implode(' ', $calendar_classes); ?>" alt="<?php print t('Open popup calendar'); ?>" title="<?php print t('Open popup calendar'); ?>" />
<?php if (theme_get_setting('zen_forms')) {
  print '</li>';
} ?>