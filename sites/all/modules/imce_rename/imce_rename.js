//implementation of imce.hookOpSubmit
imce.renameOpSubmit = function(dop) {
  if (imce.fopValidate('rename')) {
    imce.fopLoading('rename', true);
    jQuery.ajax(jQuery.extend(imce.fopSettings('rename'), {success: imce.renameResponse}));  
  }
};

//add hook.load
imce.hooks.load.push(function() {
  //set click function for rename tab to toggle crop UI
  imce.ops['rename'].func = imce.renamePrepare;
});

// Populate the textbox with the selected file or directory name.
imce.renamePrepare = function(show) {
  if (show) {
    if (imce.selcount == 0) {
      // Hack to make renaming of directories possible
      imce.selcount = 1;
      imce.selected['__IS_DIR__'] = '__IS_DIR__';
    }

    var numSelectedFiles = 0;
    for (var fid in imce.selected) {
      if (fid == '__IS_DIR__') {
        continue;
      }
      numSelectedFiles++;
    }
    if (numSelectedFiles == 1) {
      jQuery('#edit-new-name').val(imce.decode(imce.selected[fid].id));
    }
    else if (numSelectedFiles == 0) {
      jQuery('#edit-new-name').val(imce.decode(imce.conf.dir));
    }
    else {
      imce.setMessage(Drupal.t('Only one file can be renamed at a time.'), 'error');
      imce.opShrink('rename', 'hide');
    }
  }
};

//custom response. keep track of overwritten files.
imce.renameResponse = function(response) {
  imce.processResponse(response);
  if (imce.selected['__IS_DIR__'] == '__IS_DIR__') {
    // When renaming a directory, update the tree appropriately.
    var currentDir = jQuery('#edit-new-name').val();
    var parentDir = currentDir.slice(0, currentDir.lastIndexOf('/'));
    jQuery(imce.tree[imce.conf.dir].li).remove();
    imce.dirAdd(currentDir, imce.tree[parentDir], true);
  }
  else {
    var currentDir = imce.conf.dir;
  }
  // Refresh the current directory.
  jQuery.ajax(imce.navSet(currentDir, false));
  imce.opShrink('rename', 'fadeOut');
};
