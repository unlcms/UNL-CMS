<div style="margin:5px;padding:10px;border:1px solid #CCC;">
  <form style="margin:0px;">
    <div style="float:left;width:75%;">
      <div><?php print t('You have a task to Review and Edit the following content'); ?> <span style="padding-left:5px;"> <?php print $edit_content_link; ?></span></div>
    </div>
    <div style="float:right;width:25%;white-space:nowrap">
      <span style="float:right;padding-left:5px;"><input maestro="complete" type="button" value="<?php print t('Complete Task'); ?>"></span>
      <span style="float:right;"><input maestro="update" type="button" value="<?php print t('Update'); ?>"></span>
    </div>
    <div style="padding-top:20px;"><?php print t('Do you accept this document') ?>?&nbsp;
      <input type="radio" name="reviewstatus" value="no" <?php print $radio1opt; ?>><?php print t('No'); ?>
      <span style="padding-left:10px;"><input type="radio" name="reviewstatus" value="yes" <?php print $radio2opt; ?>><?php print t('Yes'); ?></span>
    </div>
  </form>
  <div style="clear:both;"></div>
</div>