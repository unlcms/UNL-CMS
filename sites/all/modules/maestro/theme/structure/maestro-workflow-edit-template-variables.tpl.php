<?php
// $Id:

/**
 * @file
 * maestro-workflow-edit-template-variables.tpl.php
 */

?>

<table border="0">
<tr>
  <th width-"25%"><?php print t('Variable'); ?></th>
  <th width="60%"><?php print t('Default Value'); ?></th>
  <th width="15%" nowrap><?php print t('Actions'); ?></th>
</tr>
<?php print $template_variables; ?>
</table>