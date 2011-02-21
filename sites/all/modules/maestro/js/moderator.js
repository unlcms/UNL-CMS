function show_reassign(link, uid) {
  (function ($) {
    var html;
    var show_flag = true;

    if ($('#reassign_form').html() != null) {
      if ($('#reassign_form').closest('tr').attr('id') == $(link).closest('tr').attr('id')) {
        show_flag = false;
      }
      $('#reassign_form').remove();
    }

    if (show_flag == true) {
      html  = '<div id="reassign_form"><form style="margin: 8px 0px 8px 0px; padding: 0px" method="post" action="' + $(link).attr('href') + '">';
      html += $('#user_select').html();
      html += '<input type="hidden" name="current_uid" value="' + uid + '">';
      html += '<input type="submit" value="' + Drupal.t('Go') + '">';
      html += '</form></div>';

      $(link).closest('td').append(html);
    }
  })(jQuery);
}

function switch_process_focus(pid) {
  (function ($) {
    var newclass;

    newclass = $('.focused_process').attr('class').replace('focused', 'blurred').replace('odd', 'even');
    $('.focused_process').attr('class', newclass);
    newclass = $('.process' + pid).attr('class').replace('blurred', 'focused').replace('even', 'odd');
    $('.process' + pid).attr('class', newclass);

    $('.process_variables').hide();
    $('#process_variables' + pid).show();
  })(jQuery);
}

function set_archived(el, index) {
  (function ($) {
    $('#archived' + index).attr('value', (el.checked) ? 1:0);
  })(jQuery);
}

function set_batch_op(el, index) {
  (function ($) {
    $('#batch_op' + index).attr('value', (el.checked) ? 1:0);
  })(jQuery);
}

function save_task_changes(frm) {
  (function ($) {
    enable_activity_indicator();
    $.ajax({
      type: 'POST',
      url: ajax_url,
      cache: false,
      data: $("#maestro_task_history_form").serialize(),
      dataType: 'json',
      success: save_success,
      error: moderator_ajax_error
    });
  })(jQuery);
}

function save_process_variables(frm) {
  (function ($) {
    enable_activity_indicator();
    $.ajax({
      type: 'POST',
      url: ajax_url,
      cache: false,
      data: $("#maestro_process_variables_form").serialize(),
      dataType: 'json',
      success: save_success,
      error: moderator_ajax_error
    });
  })(jQuery);
}

function save_success() {
  location.reload();
}

function enable_activity_indicator() {
  document.getElementById('maestro_ajax_indicator').style.display = '';
}

function disable_activity_indicator() {
  document.getElementById('maestro_ajax_indicator').style.display = 'none';
}

function moderator_ajax_error() {
  disable_activity_indicator();
}

jQuery(function($) {
  $('#filterAllFlows').click(function() {
	jQuery('#maestro_filter_working').addClass('maestro_working');
	maestro_allFlowsHideErrorBar();
	dataString = jQuery('#maestroFilterAllFlowsFrm').serialize();
    jQuery.ajax( {
      type : 'POST',
      cache : false,
      url : filter_url + '/filterprojects',
      dataType : "json",
      data : dataString,
      success : function(data) {
        try{
        	if (data.status == 1) {
        		//success	  
        		jQuery('#maestro_filter_working').removeClass('maestro_working');
        		jQuery('#maestro_all_flows_display').html(data.html);
        	} 
        	else {
        		maestro_allFlowsShowErrorBar(Drupal.t('There has been an error with your filter.  Please adjust the filter and try again'));
        		jQuery('#maestro_filter_working').removeClass('maestro_working');
        	}
        }
        catch(ex) {
        	maestro_allFlowsShowErrorBar(Drupal.t('There has been an error. Please try again'));
        	jQuery('#maestro_filter_working').removeClass('maestro_working');
        }
      },
      error : function() {
    	  maestro_allFlowsShowErrorBar(Drupal.t('There has been an error. Please try again'));
    	  jQuery('#maestro_filter_working').removeClass('maestro_working');
    	  }
    });
    return false;
  })
});



function maestro_allFlowsShowErrorBar(error) {
	jQuery('#maestro_error_message').html(error);
	jQuery('#maestro_error_message').removeClass('maestro_hide_item');
	jQuery('#maestro_error_message').addClass('maestro_show_item');
}
function maestro_allFlowsHideErrorBar() {
	var error = '';
	jQuery('#maestro_error_message').html(error);
}

function maestro_get_project_details(obj) {
  var projectID = jQuery(obj).attr('pid');
    var img, index, newicon;
    img = jQuery('#maestro_viewdetail_' + projectID).attr('src');
    index = img.indexOf('_closed');
    if(index>0) {
      jQuery.ajax({
        type: 'POST',
      url : ajax_url + '/getprojectdetails',
      cache: false,
      data : {
      projectID : projectID,
      },
      dataType: 'json',
      success:  function (data) {
        if (data.status == 1) {
        index = img.indexOf('_closed');
        newicon = img.substr(0, index) + '_open' + img.substr(index + 7);
        jQuery('#maestro_viewdetail_' + projectID).attr('src',newicon);
        jQuery('#maestro_project_information_row_' + projectID).toggle();
        jQuery('#maestro_project_information_div_'+ projectID).html(data.html);
        } else {
          alert('An error occurred updating assignment');
        }
      },
      error: function() { alert('there was a SERVER Error processing AJAX request'); }

      });
    }
    else {
      jQuery('#maestro_project_information_row_' + projectID).toggle();
      img = jQuery('#maestro_viewdetail_' + projectID).attr('src');
        index = img.indexOf('_open');
        newicon = img.substr(0, index) + '_closed' + img.substr(index + 5);
    jQuery('#maestro_viewdetail_' + projectID).attr('src',newicon);
    }

}