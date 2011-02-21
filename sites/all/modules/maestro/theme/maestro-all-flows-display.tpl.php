<table>
<tr>
  <th><?php print t('Description'); ?></th>
  <th><?php print t('Initiator'); ?></th>
  <th></th>
</tr>
<?php
$cntr = 0;
foreach ($database_result_set as $record) {
($cntr%2 == 0 ) ? $rowclass = 'maestroEvenRow' : $rowclass = 'maestroOddRow'; ?>
  <tr class="<?php print $rowclass; ?>">
    <td><?php print filter_xss($record->description); ?></td>
    <td><?php print filter_xss($record->name); ?></td>
    <td><img title="test" id="maestro_viewdetail_<?php print intval($record->id); ?>" onclick="maestro_get_project_details(this);" src="<?php print $maestro_path?>/images/taskconsole/folder_closed.gif"  pid="<?php print intval($record->id); ?>"></td>
  </tr>
  <tr class="maestro_hide_secondary_row <?php print $rowclass; ?>" id="maestro_project_information_row_<?php print intval($record->id); ?>">
    <td colspan="3">
      <div id="maestro_project_information_div_<?php print intval($record->id); ?>"></div>
    </td>
  </tr>
<?php $cntr+=1;
}?>
</table>