<?php
// $Id: maestro-task-if-edit.tpl.php,v 1.6 2010/08/10 19:04:36 chevy Exp $

/**
 * @file
 * maestro-task-if-edit.tpl.php
 */

?>
<table>
  <tr>
    <td colspan="2" style="text-align:center;">
      <?php print t('Set your IF parameters to check by variable OR by Last Task Status below:'); ?><br></br>
      <label for="ifTaskArguments0"><input type="radio" id="ifTaskArguments0" name="ifTaskArguments" value="variable" <?php if($td_rec->task_data['if_task_arguments'] == 'variable') print 'checked'; ?> onclick="if_task_enable_disable_agruments('variable');"><?php print t('By Variable'); ?></label>&nbsp;&nbsp;&nbsp;
      <label for="ifTaskArguments1"><input type="radio" id="ifTaskArguments1" name="ifTaskArguments" value="status" <?php if($td_rec->task_data['if_task_arguments'] == 'status') print 'checked'; ?> onclick="if_task_enable_disable_agruments('status');"><?php print t('By Last Task Status'); ?></label>
    </td>
  </tr>
  <tr>
    <td>
      <?php print t('Argument Variable:'); ?>
    </td>
    <td id="ifArgumentVariableRow">
      <select name="argumentVariable" id="argumentVariable"<?php if($td_rec->task_data['if_task_arguments'] != 'variable') print 'disabled="true"'; ?>>
      <?php print $argument_variables; ?>
      </select>
      <select name="ifOperator" id="ifOperator" <?php if($td_rec->task_data['if_task_arguments'] != 'variable') print 'disabled="true"'; ?>>
        <option value="0"></option>
        <option value="=" <?php if($td_rec->task_data['if_operator'] == '=') print 'selected'; ?>>=</option>
        <option value=">" <?php if($td_rec->task_data['if_operator'] == '>') print 'selected'; ?>>&gt;</option>
        <option value="<" <?php if($td_rec->task_data['if_operator'] == '<') print 'selected'; ?>>&lt;</option>
        <option value="!=" <?php if($td_rec->task_data['if_operator'] == '!=') print 'selected'; ?>>!=</option>
      </select>
      <input type="text" name="ifValue" id="ifValue" value="<?php print filter_xss($td_rec->task_data['if_value']); ?>" size="3" <?php if($td_rec->task_data['if_task_arguments'] != 'variable') print 'disabled="true"'; ?>></input>
    </td>
  </tr>
    <td>
      <?php print t('Last Task Status:'); ?>
    </td>
    <td id="ifArgumentStatusRow">
      <select name="ifProcessArguments" id="ifProcessArguments" <?php if($td_rec->task_data['if_task_arguments'] != 'status') print 'disabled="true"'; ?>>
        <option value="0"></option>
        <option value="lasttasksuccess" <?php if($td_rec->task_data['if_process_arguments'] == 'lasttasksuccess') print 'selected'; ?>>Last Task Status is Success</option>
        <option value="lasttaskcancel" <?php if($td_rec->task_data['if_process_arguments'] == 'lasttaskcancel') print 'selected'; ?>>Last Task Status is Cancel</option>
        <option value="lasttaskhold" <?php if($td_rec->task_data['if_process_arguments'] == 'lasttaskhold') print 'selected'; ?>>Last Task Status is Hold</option>
        <option value="lasttaskaborted" <?php if($td_rec->task_data['if_process_arguments'] == 'lasttaskaborted') print 'selected'; ?>>Last Task Status is Aborted</option>
      </select>
    </td>
  <tr>
</table>

<script type="text/javascript">
  setTimeout(tick, 500);

  function tick() {
    if_task_enable_disable_agruments('<?php print $td_rec->task_data['if_task_arguments']; ?>');
  }

  function if_task_enable_disable_agruments(val){
    (function ($) {
      switch(val) {
      case 'status':
        $('#ifArgumentVariableRow').hide();
        $('#ifArgumentStatusRow').show();
        $('#ifOperator').attr('disabled','true');
        $('#ifValue').attr('disabled','true');
        $('#argumentVariable').attr('disabled','true');
        $('#ifProcessArguments').removeAttr('disabled');
        break;
      case 'variable':
        $('#ifArgumentStatusRow').hide();
        $('#ifArgumentVariableRow').show();
    	  $('#ifOperator').removeAttr('disabled');
        $('#ifValue').removeAttr('disabled');
        $('#argumentVariable').removeAttr('disabled');
        $('#ifProcessArguments').attr('disabled','true');
    	  break;
      }
    })(jQuery);
  }
</script>
