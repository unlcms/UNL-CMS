<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="<?php print $GLOBALS['language']->language; ?>" xml:lang="<?php print $GLOBALS['language']->language; ?>" class="imce">

<head>
  <title><?php print t('File Browser'); ?></title>
  <meta name="robots" content="noindex,nofollow" />
  <?php
  if (isset($_GET['app'])) {
    drupal_add_js(drupal_get_path('module', 'imce') . '/js/imce_set_app.js');
  }
  ?>
  <?php print drupal_get_html_head(); ?>
  <?php print drupal_get_css(); ?>
  <?php print drupal_get_js('header'); ?>
  <style media="all" type="text/css">/*Quick-override*/</style>
</head>

<body class="imce">
<div id="imce-messages"><?php print theme('status_messages'); ?></div>
<?php print $content; ?>
<?php print drupal_get_js('footer'); ?>
</body>

</html>
