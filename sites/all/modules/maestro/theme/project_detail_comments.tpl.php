        <fieldset>
            <legend>
                <span>
                    <img src="<?php print $module_base_url; ?>/images/taskconsole/collapse.png" border="0" onClick="toggleProjectSection('projectComments','Open',<?php print $rowid; ?>)">
                        <span onClick="toggleProjectSection('projectComments','Open',<?php print $rowid; ?>)"><b><?php print t('Comments'); ?></b></span>
                </span>
            </legend>
            <div style="clear:both;">&nbsp;</div>
            <?php
              if (count($comment_records) > 0) {
              foreach ($comment_records as $rec) {  ?>
                <div class="maestro_comment">
                    <img src="<?php print $module_base_url; ?>/images/taskconsole/comment.gif" alt="" align="middle" border="0" height="16" width="16">&nbsp; <?php print t('Comment by'); ?>:&nbsp;<?php print $rec->username;?>&nbsp;<?php print t('on'); ?>&nbsp;<?php print $rec->date; ?>
                    <span style="padding-left:20px;visibility:<?php print $rec->show_delete; ?>">
                      <a href="#" onClick="ajaxMaestroComment('del',<?php print $rowid; ?>,<?php print $tracking_id; ?>,<?php print $rec->id; ?>);"><img src="<?php print $module_base_url; ?>/images/taskconsole/delete.gif" Title="<?php print t('Delete Comment'); ?>" border="0"></a>
                    </span>
                    <span style="padding-left:10px;">
                      <a href="#" onClick="ajaxMaestroComment('new',<?php print $rowid; ?>);"><img src="<?php print $module_base_url; ?>/images/taskconsole/new_comment.gif" TITLE="<?php print t('Add Comment');?>" border="0"></a>
                    </span>
                    <br>
                    <div style="padding-bottom:5px;"><b><?php print t('Task'); ?>:&nbsp;</b><?php print $rec->taskname; ?></div>
                    <div class="maestro_boxed elementUpdated"><p><?php print $rec->comment; ?></p></div>
                </div>
                <?php
              }
              } else {
                ?><span style="padding-left:10px;"></span><a href="#" onClick="ajaxMaestroComment('new',<?php print $rowid; ?>);"><?php print t('New Comment'); ?></a></span>
                <?php
              }
              ?>
        </fieldset>
