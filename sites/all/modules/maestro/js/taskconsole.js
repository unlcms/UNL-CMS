// $Id:

/**
 * @file
 * taskconsole.js
 */

/* When the task name in the task console is clicked, open the interactive task (inline function)
 * Trigger the AJAX update to update the task start_date
 */
jQuery(function($) {
  $('.maestro_taskconsole_interactivetaskName a').click(function() {
    var taskid = jQuery(this).attr('taskid');
    $('#maestro_actionrec' + taskid).toggle();
    $.post(ajax_url + '/starttask/',"taskid=" + taskid);
    if (document.getElementById('maestro_actionrec' + taskid)) {
      $('html,body').animate({scrollTop: $('#maestro_actionrec' + taskid).offset().top - 125},500);
    }
  })
});


/* When the task name in the task console is clicked, open the interactive task (inline function)
 * Trigger the AJAX update to update the task start_date
 */
jQuery(function($) {
  $('.maestro_taskconsole_viewdetail').click(function() {
    var taskid = jQuery(this).attr('taskid');
    var rowid = jQuery(this).attr('rowid');
    if (document.getElementById('maestro_taskconsole_detail_rec' + taskid).style.display == 'none') {
      $('#maestro_ajax_indicator' + taskid).show();
      $.ajax({
        type: 'POST',
        url : ajax_url + '/getdetails',
        cache: false,
        data : {
        taskid : taskid,
        rowid : rowid
        },
        dataType: 'json',
        success:  function (data) {
          if (data.status == 1) {
            // Swap the image of the closed folder for a open folder icon
            var s = $('#maestro_viewdetail_foldericon' + taskid).attr('src');
            var index = s.indexOf('_closed');
            var newicon = s.substr(0, index) + '_open' + s.substr(index + 7);
            $('#maestro_viewdetail_foldericon' + taskid).attr('src',newicon);
            $('#projectdetail_rec' + rowid).html(data.html);
            $('#maestro_taskconsole_detail_rec' + taskid).show();
            $('#maestro_ajax_indicator' + taskid).hide();
          } else {
            alert('An error occurred updating assignment');
          }
        },
        error: function() { alert('there was a SERVER Error processing AJAX request'); }

      });

    } else {
        // Swap the image of the open folder for a closed folder icon
        var s = $('#maestro_viewdetail_foldericon' + taskid).attr('src');
        var index = s.indexOf('_open');
        var newicon = s.substr(0, index) + '_closed' + s.substr(index + 5);
        $('#maestro_viewdetail_foldericon' + taskid).attr('src',newicon);
        $('#maestro_taskconsole_detail_rec' + taskid).hide();
    }

  })
});


/* In the project details area, the workflow admin can change the assigned user for a task */
function maestro_ajaxUpdateTaskAssignment(id) {
  (function ($) {
    $.ajax({
      type: 'POST',
      url : ajax_url + '/setassignment',
      cache: false,
      data: $("#frmOutstandingTasksRow" + id).serialize(),
      dataType: 'json',
      success:  function (data) {
        if (data.status != 1) {
          alert('An error occurred updating assignment');
        }
      },
      error: function() { alert('there was a SERVER Error processing AJAX request'); }

    });
  })(jQuery);
}

/* In the project details area, the workflow admin can delete a project and its associated tasks and content */
function maestro_ajaxDeleteProject(id) {
  alert('Delete Project feature not yet implemented.');
  /*
  (function ($) {
    $.ajax({
      type: 'POST',
      url : ajax_url + '/deleteproject',
      cache: false,
      data: {tracking_id: id},
      dataType: 'json',
      success:  function (data) {
        if (data.status != 1) {
          alert('An error occurred deleting project');
        }
      },
      error: function() { alert('there was a SERVER Error processing AJAX request'); }

    });
  })(jQuery);
  */
}

function ajaxMaestroComment(op, rowid, id, cid) {
  if (op == 'new') {
    jQuery('#newcomment_container_' + rowid).show();
    jQuery('html,body').animate({scrollTop: jQuery('#newcomment_container_' + rowid).offset().top -50},500);
  } else if (op == 'add') {
    (function ($) {
      $.ajax({
        type : 'POST',
        url : ajax_url + '/add_comment',
        cache : false,
        data : {
          rowid : rowid,
          tracking_id : id,
          comment: document.getElementById("newcomment_" + id).value
        },
        dataType : 'json',
        success : function(data) {
          if (data.status == 1) {
            $('#projectCommentsOpen_rec' + rowid).html(data.html);
            $('html,body').animate({scrollTop: $('#projectCommentsOpen_rec' + rowid).offset().top-1},500);
          } else {
            alert('An error occurred adding comment');
          }
        },
        error : function() {
          alert('there was a SERVER Error processing AJAX request');
        }

      });
      $('#newcomment_container_' + rowid).hide();
    })(jQuery);

  } else if (op == 'del') {
    (function ($) {
      $.ajax({
      type : 'POST',
      url : ajax_url + '/del_comment',
      cache : false,
      data : {
        rowid : rowid,
        tracking_id : id,
        cid : cid
      },
      dataType : 'json',
      success : function(data) {
        if (data.status == 1) {
          $('#projectCommentsOpen_rec' + rowid).html(data.html);
          $('html,body').animate({scrollTop: $('#projectCommentsOpen_rec' + rowid).offset().top-1},500);
        } else {
          alert('An error occurred deleting comment');
        }
      },
      error : function() {
        alert('there was a SERVER Error processing AJAX request');
      }

    });
    })(jQuery);

  }

}

/*
 * Function handles the form submit buttons for the inline interactive tasks All
 * the form buttons should be of input type 'button' even the 'task complete'
 * Function will fire automatically when a form button is pressed and execute
 * the ajax operation for the interactive_post action and automatically post the
 * form contents plus the taskid and task operation that was picked up from the
 * button's custom 'maestro' attribute. <input type="button" maestro="save"
 * value="Save Data">
 */
jQuery(function($) {
  $('.maestro_taskconsole_interactivetaskcontent input[type=button]').click(function() {
    var id = jQuery(this).parents('tr').filter(".maestro_taskconsole_interactivetaskcontent").attr('id');
    var idparts = id.split('maestro_actionrec');
    var taskid = idparts[1];
    var op = jQuery(this).attr('maestro');
    dataString = jQuery(this).closest('form').serialize();
    dataString += "&queueid=" + taskid;
    dataString += "&op=" + op;
    jQuery.ajax( {
      type : 'POST',
      cache : false,
      url : ajax_url + '/interactivetask_post',
      dataType : "json",
      success : function(data) {
        $("#maestro_actionrec" + taskid).hide();
        if (data.status == 1) {
          if (data.hidetask == 1) {
            $("#maestro_taskcontainer" + taskid).hide();
            $("#maestro_taskconsole_detail_rec" + taskid).hide();
          }
        } else {
          alert('An error occurred processing this interactive task');
        }
      },
      error : function() { alert('there was a SERVER Error processing AJAX request'); },
      data : dataString
    });
    return false;

  })
});


jQuery(function($) {
  $('#taskConsoleLaunchNewProcess').click(function() {
    $("#newProcessStatusRowSuccess").hide();
    $("#newProcessStatusRowFailure").hide();
    dataString = jQuery('#frmLaunchNewProcess').serialize();
    dataString += "&op=newprocess";
    jQuery.ajax( {
      type : 'POST',
      cache : false,
      url : ajax_url + '/newprocess',
      dataType : "json",
      success : function(data) {
        if (data.status == 1 && data.processid > 0) {
          $("#newProcessStatusRowSuccess").show();
        } else {
          $("#newProcessStatusRowFailure").show();
        }
      },
      error : function() { $("#newProcessStatusRowFailure").show(); },
      data : dataString
    });
    return false;
  })
});


function toggleProjectSection(section,state,rowid) {
    var obj1 = document.getElementById(section + state + '_rec' + rowid);
    if (obj1) {
      if (state == 'Open') {
        obj1.style.display = 'none';
        var obj2 = document.getElementById(section + 'Closed_rec' + rowid);
        obj2.style.display = '';
      } else if (state = 'Closed') {
        obj1.style.display = 'none';
        var obj2 = document.getElementById(section + 'Open_rec' + rowid);
        obj2.style.display = '';
      }
    }
}

function projectDetailToggleAll(state,rowid) {
  jQuery(function($) {
    $(".taskdetailOpenRec" + rowid).each(function() {
      if (state == 'expand') {
        $(this).show();
      } else {
        $(this).hide();
      }
    })
    $(".taskdetailClosedRec" + rowid).each(function() {
      if (state == 'expand') {
        $(this).hide();
      } else {
        $(this).show();
      }
    })
    if (state == 'expand') {
      $("#expandProject" + rowid).hide();
      $("#collapseProject" + rowid).show();
    } else {
      $("#expandProject" + rowid).show();
      $("#collapseProject" + rowid).hide();
    }
  });
}

