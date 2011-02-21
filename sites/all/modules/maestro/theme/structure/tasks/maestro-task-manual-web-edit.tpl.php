<?php
// $Id: maestro-task-manual-web-edit.tpl.php,v 1.5 2010/08/24 16:51:55 randy Exp $

/**
 * @file
 * maestro-task-manual-web-edit.tpl.php
 */

?>

<table>
  <tr>
    <td style="vertical-align: top;"><?php print t('Handler URL:'); ?></td>
    <td>
    <input type="text" name="handler" value="<?php print filter_xss($td_rec->task_data['handler']); ?>"><br>
    <?php print t('Use [site_url] to denote your current site.<br>Example:  [site_url]/index.php?q=maestro_manual_web_example'); ?>
    </td>
  </tr>
  <tr>
    <td><?php print t('Open Link in new window?:'); ?></td>
    <td><input type="checkbox" name="newWindow" value="1" <?php if($td_rec->task_data['new_window'] == 1) print 'checked'; ?>></td>
  </tr>
  <tr>
    <td><?php print t('Use URL Token?:'); ?></td>
    <td><input type="checkbox" name="useToken" value="1" <?php if($td_rec->task_data['use_token'] == 1) print 'checked'; ?>></td>
  </tr>
</table>
