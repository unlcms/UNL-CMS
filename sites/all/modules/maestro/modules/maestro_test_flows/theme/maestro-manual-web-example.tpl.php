<?php
// $Id:

/**
 * @file
 * maestro-manual-web-example.tpl.php
 */

?>

<table>
  <tr>
    <td>Manual Web Task Example page.</td>
  </tr>
  <tr>
    <td>
        <span id="maestro_completing"></span>
        <input id="completeButton" type="button" onclick="maestro_completeManualWebExample(<?php print $queue_id; ?>);"
        value="<?php print(t('Click here to complete your manual web task')); ?>"></input>
        <div id="completedLink"><a href="<?php print(url('maestro/taskconsole')); ?>">Click here to return to the task console.</a></div>
    </td>
  </tr>
  <tr>
    <td>
      <span id="manualWebStatus" style="color: red"></span>
    </td>
  </tr>
</table>

<script type="text/javascript">
jQuery('#completedLink').addClass('maestro_hide_item');
function maestro_completeManualWebExample(queueID) {
	  var errormsg=Drupal.t('There has been an error.  Please try your Complete again.');
	  jQuery('#maestro_completing').addClass('maestro_working');
	  jQuery.ajax( {
	    type : 'POST',
	    cache : false,
	    url : '<?php print(url('maestro/taskconsole/ajax') . '/complete_task/'); ?>' + queueID,
	    dataType : "json",
	    success : function (data) {
	      jQuery('#maestro_completing').removeClass('maestro_working');
	      if (data.status == "0") { // query failed
	        jQuery('#manualWebStatus').html(errormsg);
	      } else {
	        jQuery('#manualWebStatus').html('');
	        jQuery('#completeButton').addClass('maestro_hide_item');
	        jQuery('#completedLink').removeClass('maestro_hide_item');
	        jQuery('#completedLink').addClass('maestro_show_item');
	      }
	    },
	    error : function (request, status, error){
	    	jQuery('#manualWebStatus').html(Drupal.t('There has been an error in the request.'));
	    },
	    data : ''
	  });
	}


</script>