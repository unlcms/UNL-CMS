<?php
// $Id:

/**
 * @file
 * maestro-workflow-list.tpl.php
 */

?>
<div id="maestro_template_admin">
<script type="text/javascript">
var num_records = <?php print $num_records; ?>;
var ajax_url = '<?php print filter_xss($ajax_url); ?>';
</script>
  <div id="addtemplate" style="padding:10px 0px 10px 10px;">
    <input class="form-submit" type="button" value="<?php print t('New Template'); ?>" onClick="jQuery('#newtemplate').toggle();">&nbsp;
    <input class="form-submit" type="button" value="<?php print t('Application Groups'); ?>" onClick="jQuery('#newappgroup').toggle();">&nbsp;
    <input class="form-submit" type="button" value="<?php print t('Import Template'); ?>" id="importMaestroTemplate">&nbsp;
  </div>

  <table cellpadding="2" cellspacing="1" border="1" width="100%" style="border:1px solid #CCC;">
    <tr id="importTemplate" style="display:none;">
      <td colspan="3">
        <div style="color: red; font-weight: bold;">
          <?php print t('Using the import routine will evaluate and execute live PHP code on your site.'); ?><br>
          <?php print t('Only import workflows from a trusted and reliable source.  You\'ve been warned!'); ?><br>
        </div>
        <?php print t('Please paste your import code below:'); ?><br>
        <div id="importProblemMessage" class="maestro_hide_item" style="color: red; font-size: 1.5em;">
          <?php print t('Import code has illegal instructions.  Import has been aborted.'); ?>
          <br></br>
        </div>
        <div id="importSuccessMessage" class="maestro_hide_item" style="color: green;">
          <?php print t('Import completed successfully.'); ?>
          <?php print t('Click '); ?>
          <?php print l('here','admin/structure/maestro'); ?>
          <?php print t(' to continue.'); ?>
        </div>
        <div id="importFailureMessage" class="maestro_hide_item" style="color: red;">
          <?php print t('There has been an error during your import.  Please view the error logs for details.'); ?>
        </div>
        <form id="maestroImportTemplateFrm">
        <textarea id="templatecode" name="templatecode" rows="10" cols="100" style="border: solid gray 1px;"></textarea><br><br>
        <input class="form-submit" type="button" value="<?php print t('Begin Import'); ?>" id="doMaestroTemplateImport">&nbsp;
        <input class="form-submit" type="button" value="<?php print t('Close'); ?>" onClick="jQuery('#templatecode').attr('value', '');jQuery('#importTemplate').toggle();">&nbsp;
        </form>
        <div id="importSuccessMessage" class="maestro_hide_item" style="color: green;">
          <?php print t('Import completed successfully.'); ?>
          <?php print t('Click '); ?>
          <?php print l('here','admin/structure/maestro'); ?>
          <?php print t(' to continue.'); ?>
        </div>
        <div id="importFailureMessage" class="maestro_hide_item" style="color: red;">
          <?php print t('There has been an error during your import.  Please view the error logs for details.'); ?>
        </div>

      </td>

    </tr>
    <tr>
      <td colspan="3" class="pluginInfo"><?php print t('Click on desired action to edit template'); ?></td>
    </tr>
    <tr>
      <td class="pluginTitle"><?php print t('ID'); ?></td>
      <td class="pluginTitle"><?php print t('Template Name'); ?></td>
      <td class="pluginTitle" ><?php print t('Actions'); ?></td>
    </tr>

    <tr id="newtemplate" style="display:none;">
      <td colspan="3" class="pluginRow1">
          <table cellspacing="1" cellpadding="1" border="0" width="100%" style="border:none;">
            <tr>
              <td><?php print t('Name'); ?>:</td>
              <td><input class="form-text" type="text" id="newTemplateName" value="" size="50"></td>
              <td style="text-align:right;padding-right:10px;">
                <span id="maestro_new_template_updating"></span>
                <input class="form-submit" type="button" value="<?php print t('Create'); ?>" onClick='maestro_CreateTemplate();'>&nbsp;
                <input class="form-submit" type="button" value="<?php print t('Close'); ?>" onClick="jQuery('#newtemplate').toggle();">&nbsp;
              </td>
            </tr>
          </table>
      </td>
    </tr>
    <tr id="newappgroup" style="display:none;">
      <td colspan="3" class="pluginRow1">
          <table cellspacing="1" cellpadding="1" border="0"  width="100%" style="border:none;">
            <tr>
              <td width="180"><?php print t('New Application Group Name'); ?>:</td>
              <td>
                <input class="form-text" type="text" id="appGroupName" value="" size="50">
                <input class="form-submit" type="button" value="<?php print t('Create'); ?>" onClick='maestro_CreateAppgroup();'>&nbsp;
                <span id="maestro_new_appgroup_updating"></span>
              </td>
              <td style="text-align:right;padding-right:10px;">
                <input class="form-submit" type="button" value="<?php print t('Close'); ?>" onClick="jQuery('#newappgroup').toggle();">&nbsp;
              </td>
            </tr>
          </table>
          <table cellspacing="1" cellpadding="1" border="0" width="100%" style="border:none;">
            <tr>
              <td class="aligntop" nowrap width="180"><?php print t('Delete Application Group'); ?>:</td>
              <td>
                <div id="replaceDeleteAppGroup">
                <?php print $app_groups; ?>
                </div>
                <input class="form-submit" type="button" value="<?php print t('Delete'); ?>" onClick='maestro_DeleteAppgroup();'>&nbsp;
                <span id="maestro_del_appgroup_updating"></span>
              </td>
              </tr>
          </table>

      </td>
    </tr>

    <tr id="maestro_error_row">

      <td  colspan="3" style="color:red;"  >
          <span id="maestro_error_message"><?php print filter_xss($error_message); ?></span>
      </td>
    </tr>
    <?php print $workflow_list; ?>

  </table>
</div>
<script type="text/javascript">
maestro_hideErrorBar();
</script>