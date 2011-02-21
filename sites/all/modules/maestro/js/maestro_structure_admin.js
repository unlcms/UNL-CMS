var maestro_structure_cntr=0;

function maestro_OpenCloseCreateVariable(cntr) {
	jQuery('#variableAdd_' + cntr).toggle();
}

function maestro_saveTemplateName(id, cntr) {
	maestro_hideErrorBar();
	var errormsg=Drupal.t('There has been an error.  Please try your save again.');
	var ajaxwaitobject='#maestro_updating_' + cntr;
	var frmID = "#maestro_template_save_" + cntr;
	dataString = jQuery(frmID).serialize();
	dataString += "&id=" + id;
	dataString += "&cntr=" + cntr;
	dataString += "&op=savetemplate";
	maestro_structure_cntr = cntr;
	jQuery('#maestro_updating_' + cntr).addClass('maestro_working');
	jQuery.ajax( {
		type : 'POST',
		cache : false,
		url : ajax_url,
		dataType : "json",
		success : function (data) {
			jQuery('#maestro_updating_' + maestro_structure_cntr).removeClass('maestro_working');
			if (data.status == "0") { // query failed
				maestro_showErrorBar();
				var error = Drupal.t('There has been an error saving your template.  Please try your save again.');
				jQuery('#maestro_error_message').html(error);
			} else {
				maestro_hideErrorBar();
				jQuery('#maestro_error_message').html('');
			}
		},
		
		error : function (request, status, error){
			maestro_structure_handleAjaxError(ajaxwaitobject, errormsg, request, status, error);
		},
		data : dataString
	});
}

function maestro_CreateVariable(id, cntr) {
	maestro_hideErrorBar();
	var errormsg=Drupal.t('There has been an error saving your variable.  Please try your save again.');
	var ajaxwaitobject='#maestro_variable_updating_' + cntr;
	var frmID = "#frmVariableAdd_" + cntr;
	dataString = jQuery(frmID).serialize();
	dataString += "&id=" + id;
	dataString += "&cntr=" + cntr;
	dataString += "&op=createvariable";
	maestro_structure_cntr = cntr;
	jQuery('#maestro_variable_updating_' + cntr).addClass('maestro_working');
	jQuery.ajax( {
		type : 'POST',
		cache : false,
		url : ajax_url,
		dataType : "json",
		success : function(data){
			jQuery("#newVariableName").attr("value", "");
			jQuery("#newVariableValue").attr("value", "");
			jQuery('#maestro_variable_updating_' + maestro_structure_cntr).removeClass('maestro_working');
			if (data.status == "1") {
				maestro_hideErrorBar();
				jQuery('#ajaxReplaceTemplateVars_' + data.cntr).html(data.data);
			} else {
				maestro_showErrorBar();
				var error = Drupal.t('There has been an error saving your template variable.  Please try your save again.');
				jQuery('#maestro_error_message').html(error);
			}
		},
		error : function (request, status, error){
			maestro_structure_handleAjaxError(ajaxwaitobject, errormsg, request, status, error);
		},
		data : dataString
	});
}

function maestro_CancelTemplateVariable(id) {
	maestro_hideErrorBar();
	var errormsg=Drupal.t('There has been an error cancelling your variable edit.  Please try your cancel again.');
	var ajaxwaitobject='#maestro_variable_updating_' + maestro_structure_cntr;
	dataString = "";
	dataString += "id=" + id;
	dataString += "&op=showvariables";
	jQuery.ajax( {
		type : 'POST',
		cache : false,
		url : ajax_url,
		dataType : "json",
		success : function(data){
			jQuery("#newVariableName").attr("value", "");
			jQuery("#newVariableValue").attr("value", "");
			jQuery('#maestro_variable_updating_' + maestro_structure_cntr).removeClass('maestro_working');
			if (data.status == "1") {
				maestro_hideErrorBar();
				jQuery('#ajaxReplaceTemplateVars_' + data.cntr).html(data.data);
			} else {
				maestro_showErrorBar();
				var error = Drupal.t('There has been an error saving your template variable.  Please try your save again.');
				jQuery('#maestro_error_message').html(error);
			}
		},
		error : function (request, status, error){
			maestro_structure_handleAjaxError(ajaxwaitobject, errormsg, request, status, error);
		},
		data : dataString
	});
}

function maestro_saveTemplateVariable(tid, var_id) {
	maestro_hideErrorBar();
	var errormsg=Drupal.t('There has been an error saving your variable.  Please try your save again.');
	var ajaxwaitobject='#maestro_updating_variable_' + maestro_structure_cntr;
	var name = jQuery('#editVarName_' + var_id).attr("value");
	var val = jQuery('#editVarValue_' + var_id).attr("value");
	dataString = "";
	dataString += "id=" + var_id;
	dataString += "&name=" + name;
	dataString += "&val=" + val;
	dataString += "&op=updatevariable";
	jQuery('#maestro_updating_variable_' + var_id).addClass('maestro_working');
	maestro_structure_cntr = var_id;
	jQuery.ajax( {
		type : 'POST',
		cache : false,
		url : ajax_url,
		dataType : "json",
		success : function(data){
			jQuery('#maestro_updating_variable_' + data.var_id).removeClass('maestro_working');
			if (data.status == "1") {
				maestro_hideErrorBar();
				jQuery('#ajaxReplaceTemplateVars_' + data.cntr).html(data.data);
			} else {
				maestro_showErrorBar();
				var error = Drupal.t('There has been an error saving your template variable.  Please try your save again.');
				jQuery('#maestro_error_message').html(error);
			}
		},
		error : function (request, status, error){
			maestro_structure_handleAjaxError(ajaxwaitobject, errormsg, request, status, error);
		},
		data : dataString
	});
}

function maestro_deleteTemplateVariable(tid, var_id, cntr) {
	maestro_hideErrorBar();
	var errormsg=Drupal.t('There has been an error deleting your variable.  Please try your delete again.');
	var ajaxwaitobject='#maestro_updating_variable_' + maestro_structure_cntr;
	var name = jQuery('#editVarName_' + var_id).attr("value");
	var val = jQuery('#editVarValue_' + var_id).attr("value");
	var x = confirm(Drupal.t('Delete this variable?'));
	if (x) {
		dataString = "";
		dataString += "id=" + var_id;
		dataString += "&tid=" + tid;
		dataString += "&cntr=" + cntr;
		dataString += "&op=deletevariable";
		jQuery.ajax( {
			type : 'POST',
			cache : false,
			url : ajax_url,
			dataType : "json",
			success : function(data){
				jQuery('#maestro_updating_variable_' + data.var_id).removeClass('maestro_working');
				if (data.status == "1") {
					maestro_hideErrorBar();
					jQuery('#ajaxReplaceTemplateVars_' + data.cntr).html(data.data);
				} else {
					maestro_showErrorBar();
					var error = Drupal.t('There has been an error deleting your template variable.\nYou can\'t delete the "initiator" variable.\nPlease try your delete again.');
					alert(error);
			
				}
			},
			error : function (request, status, error){
				maestro_structure_handleAjaxError(ajaxwaitobject, errormsg, request, status, error);
			},
			data : dataString
		});
	} else {
		return false;
	}
}

function maestro_editTemplateVariable(tid, var_id) {
	maestro_hideErrorBar();
	var errormsg=Drupal.t('There has been an error editing your variable.  Please try your edit again.');
	var ajaxwaitobject='#maestro_updating_variable_' + maestro_structure_cntr;
	dataString = "";
	dataString += "id=" + var_id;
	dataString += "&tid=" + tid;
	dataString += "&op=editvariable";
	jQuery.ajax( {
		type : 'POST',
		cache : false,
		url : ajax_url,
		dataType : "json",
		success : function(data){
			jQuery('#maestro_updating_variable_' + data.var_id).removeClass('maestro_working');
			if (data.status == "1") {
				maestro_hideErrorBar();
				jQuery('#ajaxReplaceTemplateVars_' + data.cntr).html(data.data);
			} else {
				maestro_showErrorBar();
				var error = Drupal.t('There has been an error saving your template variable.  Please try your save again.');
				jQuery('#maestro_error_message').html(error);
			}
		},
		error : function (request, status, error){
			maestro_structure_handleAjaxError(ajaxwaitobject, errormsg, request, status, error);
		},
		data : dataString
	});
}

function maestro_CreateTemplate() {
	maestro_hideErrorBar();
	var errormsg=Drupal.t('There has been an error creating your template.  Please try your create again.');
	var ajaxwaitobject='#maestro_new_template_updating';
	jQuery('#maestro_new_template_updating').addClass('maestro_working');
	var name = jQuery('#newTemplateName').attr("value");
	dataString = "";
	dataString += "name=" + name;
	dataString += "&op=createtemplate";
	jQuery.ajax( {
		type : 'POST',
		cache : false,
		url : ajax_url,
		dataType : "json",
		success : function(data){
			jQuery('#maestro_new_template_updating').removeClass('maestro_working');
			if (data.status == "1") {
				maestro_hideErrorBar();
				jQuery('#maestro_template_admin').html(data.data);
			} else {
				maestro_showErrorBar();
				var error = Drupal.t('There has been an error saving your template.  Please try your save again.');
				jQuery('#maestro_error_message').html(error);
			}
		},
		error : function (request, status, error){
			maestro_structure_handleAjaxError(ajaxwaitobject, errormsg, request, status, error);
		},
		data : dataString
	});
}

function maestro_CreateAppgroup() {
	maestro_hideErrorBar();
	var errormsg=Drupal.t('There has been an error creating your Application Group.  Please try your create again.');
	var ajaxwaitobject='#maestro_new_appgroup_updating';
	jQuery('#maestro_new_appgroup_updating').addClass('maestro_working');
	var name = jQuery('#appGroupName').attr("value");
	dataString = "";
	dataString += "name=" + name;
	dataString += "&op=createappgroup";
	jQuery.ajax( {
		type : 'POST',
		cache : false,
		url : ajax_url,
		dataType : "json",
		success : function(data){
			jQuery('#maestro_new_appgroup_updating').removeClass('maestro_working');
			jQuery('#appGroupName').attr("value","");
			if (data.status == "0") {
				maestro_showErrorBar();
				var error = Drupal.t('There has been an error saving your App Group.  Please try your save again.');
				jQuery('#maestro_error_message').html(error);
			}
			else {
				maestro_hideErrorBar();
				maestro_refreshAppGroup('deleteAppGroup');
			}
		},
		error : function (request, status, error){
			maestro_structure_handleAjaxError(ajaxwaitobject, errormsg, request, status, error);
		},
		data : dataString
	});
}

function maestro_refreshAppGroup(which) {
	maestro_hideErrorBar();
	var errormsg=Drupal.t('There has been an AJAX error refreshing your Application Group.');
	var ajaxwaitobject='#maestro_new_appgroup_updating';
	dataString = "";
	dataString += "id=" + name;
	dataString += "&which=" + which;
	dataString += "&op=refreshappgroup";
	jQuery.ajax( {
		type : 'POST',
		cache : false,
		url : ajax_url,
		dataType : "json",
		success :  function(data){
			jQuery('#maestro_del_appgroup_updating').removeClass('maestro_working');
			if (data.status == "1") {
				maestro_hideErrorBar();
				jQuery('#replaceDeleteAppGroup').html(data.data);
			} else {
				maestro_showErrorBar();
				var error = Drupal.t('There has been an error deleting your app gropu.  Please try your delete again.');
				jQuery('#maestro_error_message').html(error);
	
			}
		},
		error : function (request, status, error){
			maestro_structure_handleAjaxError(ajaxwaitobject, errormsg, request, status, error);
		},
		data : dataString
	});
}

function maestro_DeleteAppgroup() {
	maestro_hideErrorBar();
	var errormsg=Drupal.t('There has been an error deleting your Application Group.  Please try your delete again.');
	var ajaxwaitobject='#maestro_del_appgroup_updating';
	jQuery('#maestro_del_appgroup_updating').addClass('maestro_working');
	var name = jQuery('#deleteAppGroup').attr("value");
	dataString = "";
	dataString += "id=" + name;
	dataString += "&op=deleteappgroup";
	jQuery.ajax( {
		type : 'POST',
		cache : false,
		url : ajax_url,
		dataType : "json",
		success : function(data){
		jQuery('#maestro_del_appgroup_updating').removeClass('maestro_working');
			if (data.status == "1") {
				maestro_hideErrorBar();
				jQuery('#replaceDeleteAppGroup').html(data.data);
			} else {
				maestro_showErrorBar();
				var error = Drupal.t('There has been an error deleting your app gropu.  Please try your delete again.');
				jQuery('#maestro_error_message').html(error);
	
			}
		},
		error : function (request, status, error){
			maestro_structure_handleAjaxError(ajaxwaitobject, errormsg, request, status, error);
		},
		data : dataString
	});
}

function maestro_deleteTemplate(tid) {
	maestro_hideErrorBar();
	var errormsg=Drupal.t('There has been an error deleting your Template.  Please try your delete again.');
	var ajaxwaitobject='';
	var x = confirm(Drupal.t('Delete this template?'));
	if (x) {
		dataString = "";
		dataString += "id=" + tid;
		dataString += "&op=deletetemplate";
		jQuery.ajax( {
			type : 'POST',
			cache : false,
			url : ajax_url,
			dataType : "json",
			success : function(data){
				if (data.status == "1") {
					maestro_hideErrorBar();
					jQuery('#maestro_template_admin').html(data.data);
				} else {
					maestro_showErrorBar();
					var error = Drupal.t('There has been an error deleting your template.  Please try your save again.');
					jQuery('#maestro_error_message').html(error);
				}
			},
			error : function (request, status, error){
				maestro_structure_handleAjaxError(ajaxwaitobject, errormsg, request, status, error);
			},
			data : dataString
		});
	} else {
		return false;
	}
}

function maestro_copyTemplate(tid) {
	maestro_hideErrorBar();
	var errormsg=Drupal.t('There has been an error copying your Template.  Please try your copy again.');
	var ajaxwaitobject='';
	dataString = "";
	dataString += "id=" + tid;
	dataString += "&op=copytemplate";
	jQuery.ajax( {
		type : 'POST',
		cache : false,
		url : ajax_url,
		dataType : "json",
		success : function(data){
			if (data.status == "1") {
				maestro_hideErrorBar();
				document.location.reload();
			} else {
				maestro_showErrorBar();
				var error = Drupal.t('There has been an error copying your template.  Please try your save again.');
				jQuery('#maestro_error_message').html(error);
			}
		},
		error : function (request, status, error){
			maestro_structure_handleAjaxError(ajaxwaitobject, errormsg, request, status, error);
		},
		data : dataString
	});
}

function maestro_showErrorBar() {
	jQuery('#maestro_error_row').removeClass('maestro_hide_item');
	jQuery('#maestro_error_row').addClass('maestro_show_item');
}
function maestro_hideErrorBar() {
	var error = '';
	jQuery('#maestro_error_message').html(error);
	jQuery('#maestro_error_row').removeClass('maestro_show_item');
	jQuery('#maestro_error_row').addClass('maestro_hide_item');
}


function maestro_structure_handleAjaxError(ajaxwaitobject, errormsg, request, status, error) {
	if(errormsg != '') {
		maestro_showErrorBar();
		jQuery('#maestro_error_message').html(errormsg);
		var el=document.getElementById('maestro_template_admin');
		el.scrollIntoView(true);
	}
	if(ajaxwaitobject != '') jQuery(ajaxwaitobject).removeClass('maestro_working');
}


jQuery(function($) {
  $('#importMaestroTemplate').click(function() {
	maestro_hideErrorBar();
	maestro_hideImportMessages();
    dataString = "op=openimport";
    jQuery.ajax( {
      type : 'POST',
      cache : false,
      url : ajax_url + '/openimport',
      dataType : "json",
      data : dataString,
      success : function(data) {
        try{
        	if (data.status == 1) {
        		$("#importTemplate").toggle();	          
        	} 
        	else {
        		var error = Drupal.t('There has been an error.  Either you do not have enough permissions to perform this action or you have not enabled the import in the configuration panel.');
            	maestro_structure_handleAjaxError(null, error, null, 0, null);	
        	}
        }
        catch(ex) {
        	var error = Drupal.t('There has been an error.  Either you do not have enough permissions to perform this action or you have not enabled the import in the configuration panel.');
        	maestro_structure_handleAjaxError(null, error, null, 0, null);	
        }
      },
      error : function() {
    	  var error = Drupal.t('There has been an error.  Either you do not have enough permissions to perform this action or you have not enabled the import in the configuration panel.');
    	  jQuery('#maestro_error_message').html(error);
    	  maestro_showErrorBar(); 
    	  }
    });
    return false;
  })
});

jQuery(function($) {
	  $('#doMaestroTemplateImport').click(function() {
		maestro_hideErrorBar();
		maestro_hideImportMessages();
		var frmID = "#maestroImportTemplateFrm";
		dataString = jQuery(frmID).serialize();
		dataString += "&op=doimport";
	    jQuery.ajax( {
	      type : 'POST',
	      cache : false,
	      url : ajax_url + '/doimport',
	      dataType : "json",
	      data : dataString,
	      success : function(data) {
	        try{
	        	if (data.status == 1) {
	        		maestro_hideImportMessages();
	        		jQuery('#importSuccessMessage').removeClass('maestro_hide_item');
	        		jQuery('#importSuccessMessage').addClass('maestro_show_item');  
	        		document.location.reload();
	        	}
	        	else if(data.status == -1) {
	        		maestro_hideImportMessages();
	        		jQuery('#importProblemMessage').removeClass('maestro_hide_item');
	        		jQuery('#importProblemMessage').addClass('maestro_show_item');     
	        	}
	        	else {
	        		maestro_hideImportMessages();
	        		jQuery('#importFailureMessage').removeClass('maestro_hide_item');
	        		jQuery('#importFailureMessage').addClass('maestro_show_item');     
	        	}
	        }
	        catch(ex) {
	        	maestro_hideImportMessages();
        		jQuery('#importFailureMessage').removeClass('maestro_hide_item');
        		jQuery('#importFailureMessage').addClass('maestro_show_item');   
	        }
	      },
	      error : function() {
	    	maestro_hideImportMessages();
      		jQuery('#importFailureMessage').removeClass('maestro_hide_item');
      		jQuery('#importFailureMessage').addClass('maestro_show_item');   
	    	  }
	    });
	    return false;
	  })
	});


function maestro_hideImportMessages() {
	jQuery('#importFailureMessage').removeClass('maestro_show_item');
	jQuery('#importFailureMessage').addClass('maestro_hide_item');     
	jQuery('#importSuccessMessage').removeClass('maestro_show_item');
	jQuery('#importSuccessMessage').addClass('maestro_hide_item');    
	jQuery('#importProblemMessage').removeClass('maestro_show_item');
	jQuery('#importProblemMessage').addClass('maestro_hide_item');    
	
	
}