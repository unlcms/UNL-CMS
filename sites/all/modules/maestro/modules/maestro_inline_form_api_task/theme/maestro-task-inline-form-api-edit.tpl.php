<?php
// $Id: maestro-task-inline-form-api-edit.tpl.php,v 1.1 2010/08/31 13:09:19 chevy Exp $

/**
 * @file
 * maestro-task-interactive-function-edit.tpl.php
 */

?>

<table>
  <tr>
    <td><?php print t('Unique Form Name:'); ?></td>
    <td><input type="text" name="content_type" value="<?php print $td_rec->task_data['content_type']; ?>"></td>
  </tr>
  <tr>
    <td colspan="2"><?php print t('Form API PHP Array:'); ?></td>
  </tr>
  <tr>
    <td colspan="2"><textarea name="form_api_code" rows="8" style="width: 100%;"><?php print $td_rec->task_data['form_api_code']; ?></textarea></td>
  </tr>
  <tr>
    <td colspan="2" style="font-style: italic; font-size: 0.8em;"><?php print t('Create your form fields in an array variable named $form. Leave out any default values, the system will add them automatically.'); ?></td>
  </tr>
</table>
