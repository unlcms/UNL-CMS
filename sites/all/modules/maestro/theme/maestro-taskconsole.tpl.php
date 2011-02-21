<?php
// $Id:

/**
 * @file
 * maestro-taskconsole.tpl.php
 */

?>
<script type="text/javascript">
  var ajax_url = '<?php print $ajax_server_url; ?>';
</script>

<table width="100%">
  <tr>
    <td style="text-align: right">
      <form method="get" action="<?php print url("maestro/launch"); ?>" id="frmLaunchNewProcess">
      <?php print t('Start New Process:'); ?>
      <select name="templateid">
      <?php foreach($process_dropdown as $optid => $optval){ ?>
        <option value="<?php print $optid; ?>"><?php print $optval; ?></option>
      <?php } ?>
      </select>
      <input type="button" value="Launch" id="taskConsoleLaunchNewProcess"></input>
      <div id="newProcessStatusRowSuccess" style="display:none;color: green;">
        <?php print t('Started Process successfully.'); ?>
      </div>
      <div id="newProcessStatusRowFailure" style="display:none;color: green;">
        <?php print t('Error Starting Process.'); ?>
      </div>
      </form>
    </td>
  </tr>
</table>

<table width="100%">
<tr>
  <th width="3%"><?php print t(''); ?></th>
  <th width="30%"><?php print t('Flow Name'); ?></th>
  <th width="30%"><?php print t('Task Name'); ?></th>
  <th width="12%"><?php print t('Assigned'); ?></th>
  <th colspan="2" width="5%">&nbsp;</th>
</tr>

<?php
 $rowid = 1;
 foreach ($variables['formatted_tasks'] as $task) {
 ?>
<tr id="maestro_taskcontainer<?php print $task->queue_id; ?>" class="maestro_taskconsole_interactivetask <?php print $zebra ?>">
    <td width="3%" class="<?php print $task->queue_id; ?>" style="border-left:1px solid white">
        <img src="<?php print $task->task_icon; ?>" TITLE="<?php print t('Process ID: '); print $task->process_id; print t(', Task ID: '); print $task->queue_id; print $task->task_started; ?>" id="taskIconImg<?php print $rowid; ?>">
    </td>
    <td width="30%"><?php print $task->flow_name; ?></td>
    <td width="30%" class="maestro_taskconsole_interactivetaskName">
        <a class="info" style="text-decoration:none;" taskid="<?php print $task->queue_id; ?>" href="<?php print $task->task_action_url; ?>"><?php print $task->taskname; ?>
            <span style="width:300px;display: <?php print $task->hidetaskinfo; ?>;">
                <?php print $task->onholdnotice; ?>
                <b><?php print t('Date Assigned:'); ?></b>&nbsp;<?php print $task->assigned_longdate; ?>
                <div style="display:<?php print $task->showmoretaskdetail; ?>">
                  <b><?php print t('Description:'); ?></b>&nbsp;<?php print $task->description; ?><br>
                  <b><?php print t('Comments:'); ?></b>&nbsp;<?php print $task->comment_note; ?>
                </div>
            </span>
        </a>
    </td>
    <td width="12%" nowrap><?php print $task->assigned_shortdate; ?></td>
    <td width="2%" nowrap>
      <span id="maestro_ajax_indicator<?php print $task->queue_id;?>" class="maestro_ajax_indicator" style="display:none;"><img src="<?php print $module_base_url; ?>/images/admin/status-active.gif"></span>
    </td>
    <td width="5%" style="border-right:1px solid white;" nowrap>
    	<span class="maestro_taskconsole_viewdetail" taskid="<?php print $task->queue_id;?>" rowid="<?php print $rowid;?>">
         <img id="maestro_viewdetail_foldericon<?php print $task->queue_id; ?>" src="<?php print $module_base_url; ?>/images/taskconsole/folder_closed.gif" TITLE="<?php print t('Click to toggle workflow details'); ?>">
      </span>
    </td>
</tr>

<!-- {inline action record} -->
  <?php
    print $task->action_record;
  ?>
  <tr  id="maestro_taskconsole_detail_rec<?php print $task->queue_id; ?>" style="display:none;">
      <td colspan="6" style="padding:10px;">
          <div id="projectdetail_rec<?php print $rowid; ?>"></div>
      </td>
  </tr>
    <?php
      $rowid++;
      $zebra = ($zebra == 'even') ? 'odd': 'even';
}
?>

</table>

