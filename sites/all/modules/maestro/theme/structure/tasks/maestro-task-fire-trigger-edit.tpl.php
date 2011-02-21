<?php
// $Id: maestro-task-fire-trigger-edit.tpl.php,v 1.1 2010/08/03 14:05:48 chevy Exp $

/**
 * @file
 * maestro-task-set-process-variable-edit.tpl.php
 */

?>

  <fieldset class="form-wrapper">
    <legend>
      <span class="fieldset-legend"><?php print t('TRIGGER: WHEN THIS TASK IS EXECUTED'); ?></span>
    </legend>
    <div class="fieldset-wrapper">
      <table id="actions_table" class="sticky-enabled sticky-table">
        <thead class="tableheader-processed">
          <tr>
            <th><?php print t('NAME'); ?></th>
            <th><?php print t('OPERATION'); ?></th>
          </tr>
        </thead>
        <tbody id="actions">
<?php
          $css_row = 1;
          foreach ($aa_res as $aa_rec) {
?>
            <tr class="<?php print (($css_row % 2) == 0) ? 'even' : 'odd'; ?>">
              <td><?php print $aa_rec->label; ?><input type="hidden" id="actions<?php print $css_row; ?>" name="actions[]" value="<?php print $aa_rec->aid; ?>"></td>
              <td><a href="#" onclick="delete_action(this); return false;">unassign</a></td>
            </tr>
<?php
            $css_row++;
          }
?>
        </tbody>
      </table>

      <div class="container-inline">
        <select id="action_options">
          <option value=""><?php print t('Choose an action'); ?></option>
<?php
          foreach ($options as $group => $optionset) {
?>
            <optgroup label="<?php print $group; ?>">
<?php
              foreach ($optionset as $key => $option) {
?>
                <option value="<?php print $key; ?>"><?php print $option; ?></option>
<?php
              }
?>
            </optgroup>
<?php
          }
?>
        </select>
        <input class="form-submit" type="button" value="<?php print t('Assign'); ?>" onclick="add_action();">
      </div>
    </div>
  </fieldset>

  <script type="text/javascript">
    function delete_action(handle) {
      (function($) {
        $(handle).closest('tr').remove();
        $('#actions_table').find('tr').each(function(i, el) {
          if (i > 0) {
            el.className = ((i % 2) == 1) ? 'odd':'even';
          }
        });
      })(jQuery);
    }

    function add_action() {
      (function($) {
        var i;
        var key = $("#action_options option:selected").attr('value');
        var duplicate_flag = 0;
        if (key != '') {
          $('#actions_table').find('input').each(function(i, el) {
            if (el.value == key) {
              duplicate_flag = 1;
              return;
            }
          });

          if (duplicate_flag == 1) {
            return;
          }

          var label = $("#action_options option:selected").text();
          var rows = $('#actions_table tr').length;
          var css_row = ((rows % 2) == 1) ? 'odd':'even';
          var html  = '<tr class="' + css_row + '">';
          html += '<td>' + label + '<input id="actions' + rows + '" type="hidden" name="actions[]" value="' + key + '"></td>';
          html += '<td><a href="#" onclick="delete_action(this); return false;"><?php print t('unassign'); ?></a></td>';
          html += '</tr>';
          $('#actions').append(html);
        }
      })(jQuery);
    }
  </script>
