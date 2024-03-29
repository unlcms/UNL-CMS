<?php

/**
 * @file
 * Implements the file browser.
 */

/**
 * The q = imce.
 */
function imce($scheme = NULL) {
  // Suppress admin_menu.
  module_invoke('admin_menu', 'suppress');
  $jsop = isset($_GET['jsop']) ? $_GET['jsop'] : NULL;
  drupal_add_http_header('Content-Type', 'text/html; charset=utf-8');
  print imce_page($GLOBALS['user'], $scheme, $jsop);
  exit();
}

/**
 * The q = user/x/imce.
 */
function imce_user_page($account) {
  return theme('imce_user_page', array('account' => $account));
}

/**
 * Returns the imce page for the specified user and the file scheme.
 */
function imce_page($user, $scheme = NULL, $jsop = NULL) {
  return theme('imce_page', array('content' => imce_content($user, $scheme, $jsop)));
}

/**
 * Returns the content of the file browser.
 */
function imce_content($user, $scheme = NULL, $jsop = NULL) {

  // Execute ajax calls.
  if ($jsop) {
    return imce_js($user, $scheme, $jsop);
  }

  // Initiate configuration profile.
  if (!$imce = imce_initiate_profile($user, $scheme)) {
    return '';
  }
  // Get active directory content.
  imce_process_profile($imce);

  // Before creating the content let's add main files
  // required for imce to function properly.
  $path = drupal_get_path('module', 'imce');
  drupal_add_js($path . '/js/jquery.form.js');
  drupal_add_js($path . '/js/imce.js');
  drupal_add_js($path . '/js/imce_extras.js');
  drupal_add_css($path . '/css/imce-content.css');

  // Process forms.
  $imce_ref = array('imce' => &$imce);
  $forms = array();
  if (!$imce['error']) {
    // Process file upload.
    if (imce_perm_exists($imce, 'upload')) {
      $forms[] = drupal_get_form('imce_upload_form', $imce_ref);
    }
    // Process file operations.
    $forms[] = drupal_get_form('imce_fileop_form', $imce_ref);
  }
  $forms = drupal_render($forms);

  // Run custom content functions. possible to insert
  // extra forms. content is invisible when js is enabled.
  foreach (variable_get('imce_custom_content', array()) as $func => $state) {
    if ($state && function_exists($func) && $output = $func($imce)) {
      $forms .= $output;
    }
  }

  $content = theme('imce_content', array(
    'tree' => imce_create_tree($imce),
    'forms' => $forms,
    'imce_ref' => $imce_ref,
  ));

  // Make necessary changes for js conversion.
  $imce['dir'] = str_replace('%2F', '/', rawurlencode($imce['dir']));
  unset($imce['files'], $imce['name'], $imce['directories'], $imce['subdirectories'], $imce['filesize'], $imce['quota'], $imce['tuquota'], $imce['thumbnails'], $imce['uid'], $imce['usertab']);

  //add the default sort variable
  $imce_ref['imce']['sort'] = variable_get('imce_settings_sort', 'default');

  drupal_add_js($imce_ref, 'setting');

  return $content;
}

/**
 * Ajax operations. q=imce&jsop={op}.
 */
function imce_js($user, $scheme, $jsop = '') {
  $response = array();

  // Data.
  if ($imce = imce_initiate_profile($user, $scheme)) {
    imce_process_profile($imce);
    if (!$imce['error']) {
      module_load_include('inc', 'imce', 'inc/imce.js');
      if (function_exists($func = 'imce_js_' . $jsop)) {
        $response['data'] = $func($imce);
      }
      // Allow alteration of response.
      foreach (variable_get('imce_custom_response', array()) as $func => $state) {
        if ($state && function_exists($func)) {
          $func($jsop, $response, $imce, $user);
        }
      }
    }
  }
  // Messages.
  $response['messages'] = drupal_get_messages();

  // Disable devel log.
  $GLOBALS['devel_shutdown'] = FALSE;
  // For upload we must return plain text header.
  drupal_add_http_header('Content-Type', (!empty($_POST['html_response']) ? 'text/html' : 'application/json') . '; charset=utf-8');
  print drupal_json_encode($response);
  exit();
}

/**
 * Upload form.
 */
function imce_upload_form($form, &$form_state, $ref) {
  $imce =& $ref['imce'];
  $form['imce'] = array(
    '#type' => 'file',
    '#name' => 'files[]',
    '#title' => t('File'),
    '#size' => 30,
    '#attributes' => array('multiple' => 'multiple'),
  );
  if (!empty($imce['thumbnails'])) {
    $form['thumbnails'] = array(
      '#type' => 'checkboxes',
      '#title' => t('Create thumbnails'),
      '#options' => imce_thumbnail_options($imce['thumbnails']),
    );
  }
  $replace = variable_get('imce_settings_replace', FILE_EXISTS_RENAME);
  // In case the user can decide, show options
  if ($replace == IMCE_RENAME_REPLACE) {
    $form['replace'] = array(
      '#type' => 'radios',
      '#title' => t('Behaviour for existing files'),
      '#default_value' => variable_get('imce_settings_replace', FILE_EXISTS_RENAME),
      '#options' => array(
        FILE_EXISTS_RENAME => t('Keep the existing file renaming the new one'),
        FILE_EXISTS_ERROR => t('Keep the existing file rejecting the new one'),
        FILE_EXISTS_REPLACE => t('Replace the existing file with the new one'),
      ),
    );
  }
  $form['upload'] = array(
    '#type' => 'submit',
    '#value' => t('Upload'),
    '#submit' => $imce['perm']['upload'] ? array('imce_upload_submit') : NULL,
  );
  $form = array('fset_upload' => array('#type' => 'fieldset', '#title' => t('Upload file')) + $form);
  $form['html_response'] = array('#type' => 'hidden', '#default_value' => '1');
  $form['#attributes']['enctype'] = 'multipart/form-data';
  $form['#action'] = $imce['url'];
  return $form;
}

/**
 * File operations form.
 */
function imce_fileop_form($form, &$form_state, $ref) {
  $imce =& $ref['imce'];
  $form['filenames'] = array(
    '#type' => 'textfield',
    '#title' => t('Selected files'),
    '#maxlength' => $imce['filenum'] ? $imce['filenum'] * 255 : NULL,
  );

  // Thumbnail.
  if (!empty($imce['thumbnails']) && imce_perm_exists($imce, 'thumb')) {
    $form['fset_thumb'] = array(
      '#type' => 'fieldset',
      '#title' => t('Thumbnails'),
    ) + imce_thumb_form($imce);
  }

  // Delete.
  if (imce_perm_exists($imce, 'delete')) {
    $form['fset_delete'] = array(
      '#type' => 'fieldset',
      '#title' => t('Delete'),
    ) + imce_delete_form($imce);
  }

  // Resize.
  if (imce_perm_exists($imce, 'resize')) {
    $form['fset_resize'] = array(
      '#type' => 'fieldset',
      '#title' => t('Resize'),
    ) + imce_resize_form($imce);
  }

  // Download.
  $form['fset_download'] = array(
    '#type' => 'fieldset',
    '#title' => t('Download'),
  ) + imce_download_form($imce);;

  $form['#action'] = $imce['url'];
  return $form;
}

/**
 * Thumbnail form.
 */
function imce_thumb_form(&$imce) {
  $form['thumbnails'] = array(
    '#type' => 'checkboxes',
    '#title' => t('Thumbnails'),
    '#options' => imce_thumbnail_options($imce['thumbnails']),
  );
  $form['thumb'] = array(
    '#type' => 'submit',
    '#value' => t('Create thumbnails'),
    '#submit' => $imce['perm']['thumb'] ? array('imce_thumb_submit') : NULL,
  );
  return $form;
}

/**
 * Delete form.
 */
function imce_delete_form(&$imce) {
  $form['delete'] = array(
    '#type' => 'submit',
    '#value' => t('Delete'),
    '#submit' => $imce['perm']['delete'] ? array('imce_delete_submit') : NULL,
  );
  return $form;
}

/**
 * Resizing form.
 */
function imce_resize_form(&$imce) {
  $form['width'] = array(
    '#type' => 'textfield',
    '#title' => t('Width x Height'),
    '#size' => 5,
    '#maxlength' => 4,
    '#prefix' => '<div class="container-inline">',
  );
  $form['height'] = array(
    '#type' => 'textfield',
    '#size' => 5,
    '#maxlength' => 4,
    '#prefix' => 'x',
  );
  $form['resize'] = array(
    '#type' => 'submit',
    '#value' => t('Resize'),
    // Permission for submission.
    '#submit' => $imce['perm']['resize'] ? array('imce_resize_submit') : NULL,
    '#suffix' => '</div>',
  );
  $form['copy'] = array(
    '#type' => 'checkbox',
    '#title' => t('Create a new image'),
    '#default_value' => 1,
  );
  return $form;
}

/**
 * Download form.
 */
function imce_download_form(&$imce) {
  $form['download'] = array(
    '#type' => 'submit',
    '#value' => t('Download'),
    '#submit' => NULL,
  );
  return $form;
}

/**
 * Validate file operations form.
 */
function imce_fileop_form_validate($form, &$form_state) {
  $imce =& $form_state['build_info']['args'][0]['imce'];

  // Check if the filenames is empty.
  if ($form_state['values']['filenames'] == '') {
    return form_error($form['filenames'], t('Please select a file.'));
  }

  // Filenames come separated by colon.
  $filenames = explode(':', $form_state['values']['filenames']);
  $cnt = count($filenames);
  // Check the number of files.
  if ($imce['filenum'] && $cnt > $imce['filenum']) {
    return form_error($form['filenames'], t('You are not allowed to operate on more than %num files.', array('%num' => $imce['filenum'])));
  }

  // Check if there is any illegal choice.
  for ($i = 0; $i < $cnt; $i++) {
    $filenames[$i] = $filename = rawurldecode($filenames[$i]);
    if (!isset($imce['files'][$filename])) {
      watchdog('imce', 'Illegal choice %choice in !name element.', array('%choice' => $filename, '!name' => t('directory (%dir)', array('%dir' => imce_dir_uri($imce)))), WATCHDOG_ERROR);
      return form_error($form['filenames'], t('An illegal choice has been detected. Please contact the site administrator.'));
    }
  }

  $form_state['values']['filenames'] = $filenames;
}

/**
 * Submit upload form.
 */
function imce_upload_submit($form, &$form_state) {
  // Multiple files support.  Credit: http://mohitaghera.drupalgardens.com/content/multiple-file-upload-usind-drupal-7-form-api
  $num_files = count($_FILES['files']['name']);
  for ($i = 0; $i < $num_files; $i++) {

    $form_state['redirect'] = FALSE;
    $imce =& $form_state['build_info']['args'][0]['imce'];
    // Need to provide extension validatior,
    // otherwise file_save_upload uses the default.
    $validators['file_validate_extensions'] = array($imce['extensions'] === '*' ? NULL : $imce['extensions']);
    $validators['imce_validate_all'] = array(&$imce);
    $diruri = imce_dir_uri($imce);

    // Save uploaded file.
    $replace = variable_get('imce_settings_replace', FILE_EXISTS_RENAME);
    // Retrieve the user option when the option is set for the user to decide.
    if ($replace == IMCE_RENAME_REPLACE) {
      $replace = $form_state['values']['replace'];
    }
    if ($file = file_save_upload($i, $validators, $diruri, $replace)) {

      // Core bug #54223.
      if ($replace == FILE_EXISTS_RENAME) {
        $name = basename($file->uri);
        if ($name != $file->filename) {
          $file->filename = $name;
          drupal_set_message(t('The file has been renamed to %filename.', array('%filename' => $file->filename)));
        }
      }

      // Global user may not be the owner.
      $file->uid = $imce['uid'];
      $file->status = FILE_STATUS_PERMANENT;
      file_save($file);
      imce_file_register($file);
      drupal_set_message(t('%filename has been uploaded.', array('%filename' => $file->filename)));

      // Update file list.
      $img = imce_image_info($file->uri);
      $file->width = $img ? $img['width'] : 0;
      $file->height = $img ? $img['height'] : 0;
      imce_add_file($file, $imce);

      // Create thumbnails.
      if (isset($form_state['values']['thumbnails']) && $img) {
        imce_create_thumbnails($file->filename, $imce, $form_state['values']['thumbnails']);
      }
    }
    else {
      drupal_set_message(t('Upload failed.'), 'error');
    }
  }
}

/**
 * Submit thumbnail form.
 */
function imce_thumb_submit($form, &$form_state) {
  $form_state['redirect'] = FALSE;
  $imce =& $form_state['build_info']['args'][0]['imce'];
  // Create thumbnails.
  imce_process_files($form_state['values']['filenames'], $imce, 'imce_create_thumbnails', array($form_state['values']['thumbnails']));
}

/**
 * Submit delete form.
 */
function imce_delete_submit($form, &$form_state) {
  $form_state['redirect'] = FALSE;
  $imce =& $form_state['build_info']['args'][0]['imce'];

  $deleted = imce_process_files($form_state['values']['filenames'], $imce, 'imce_delete_file');

  if (!empty($deleted)) {
    drupal_set_message(t('File deletion successful: %files.', array('%files' => utf8_encode(implode(', ', $deleted)))));
  }

}

/**
 * Submit resize form.
 */
function imce_resize_submit($form, &$form_state) {
  $form_state['redirect'] = FALSE;
  $imce =& $form_state['build_info']['args'][0]['imce'];

  // Check dimensions.
  $width = (int) $form_state['values']['width'];
  $height = (int) $form_state['values']['height'];
  list($maxw, $maxh) = $imce['dimensions'] ? explode('x', $imce['dimensions']) : array(0, 0);
  if ($width < 1 || $height < 1 || ($maxw && ($width > $maxw || $height > $maxh))) {
    drupal_set_message(t('Please specify dimensions within the allowed range that is from 1x1 to @dimensions.', array('@dimensions' => $imce['dimensions'] ? $imce['dimensions'] : t('unlimited'))), 'error');
    return;
  }

  $resized = imce_process_files($form_state['values']['filenames'], $imce, 'imce_resize_image', array($width, $height, $form_state['values']['copy']));

  if (!empty($resized)) {
    drupal_set_message(t('File resizing successful: %files.', array('%files' => utf8_encode(implode(', ', $resized)))));
  }

}

/**
 * Do batch operations on files.
 *
 * Used by delete, resize, create thumbnail submissions.
 */
function imce_process_files($filenames, &$imce, $function, $args = array()) {
  $args = array_merge(array('', &$imce), $args);
  $processed = array();

  foreach ($filenames as $filename) {
    $args[0] = $filename;
    if (call_user_func_array($function, $args)) {
      $processed[] = $filename;
    }
  }

  return $processed;
}

/**
 * Deletes a file in the file list.
 */
function imce_delete_file($filename, &$imce) {
  $uri = imce_dir_uri($imce) . $filename;
  if (!imce_delete_filepath($uri)) {
    return FALSE;
  }
  imce_remove_file($filename, $imce);
  return TRUE;
}

/**
 * Deletes a file by uri.
 */
function imce_delete_filepath($uri) {
  $file = file_load_multiple(array(), array('uri' => $uri));
  $file = reset($file);

  // File exists in database.
  if ($file) {
    $usage = file_usage_list($file);
    $is_imce = isset($usage['imce']);
    unset($usage['imce']);
    // File is in use by an other module.
    if (!empty($usage)) {
      drupal_set_message(t('%filename is in use by another application.', array('%filename' => $file->filename)), 'error');
      return FALSE;
    }
    // Force delete file. Prevent running file_usage_list() second time.
    if (!file_delete($file, TRUE)) {
      return FALSE;
    }
  }
  // Not in db. Probably loaded via ftp.
  elseif (!file_unmanaged_delete($uri)) {
    return FALSE;
  }

  return TRUE;
}

/**
 * Create all selected thumbnails.
 */
function imce_create_thumbnails($filename, &$imce, $values) {
  $created = array();
  foreach ($imce['thumbnails'] as $thumbnail) {
    if ($values[$thumbnail['name']] && imce_create_thumbnail($filename, $imce, $thumbnail)) {
      $created[] = $thumbnail['name'];
    }
  }
  if (!empty($created)) {
    drupal_set_message(t('Thumbnail creation (%thumbnames) successful for %filename.', array(
      '%thumbnames' => implode(', ', $created),
      '%filename' => utf8_encode($filename),
    )));
  }
  return $created;
}

/**
 * Create a thumbnail.
 */
function imce_create_thumbnail($filename, &$imce, $thumbnail) {
  // Generate thumbnail name.
  $name = $thumbnail['prefix'];
  if ($thumbnail['suffix'] != '' && $dot = strrpos($filename, '.')) {
    $name .= substr($filename, 0, $dot);
    $name .= $thumbnail['suffix'];
    $name .= substr($filename, $dot);
  }
  else {
    $name .= $filename;
  }
  // Scale the image.
  list($width, $height) = explode('x', $thumbnail['dimensions']);
  return imce_resize_image($filename, $imce, $width, $height, TRUE, $name, variable_get('imce_settings_thumb_method', 'scale_and_crop'));
}

/**
 * Resize an image in the file list. Also used for thumbnail creation.
 */
function imce_resize_image($filename, &$imce, $width, $height, $copy = TRUE, $destname = FALSE, $op = 'resize') {
  $destdir = imce_dir_uri($imce);
  $imguri = $destdir . $filename;

  // Check if the file is an image.
  if (!$imce['files'][$filename]['width'] || !$img = image_get_info($imguri)) {
    drupal_set_message(t('%filename is not an image.', array('%filename' => utf8_encode($filename))), 'error', FALSE);
    return FALSE;
  }

  if (substr($op, 0, 5) == 'scale' && !($width < $img['width'] || $height < $img['height'])) {
    drupal_set_message(t('Scaling up is not allowed.'), 'error', FALSE);
    return FALSE;
  }

  // Create file object.
  $file = new stdClass();
  $file->uri = $destdir . $destname;
  if (!$destname || $destname == $filename) {
    $file->uri = $copy ? file_create_filename($filename, $destdir) : $imguri;
  }
  $file->filename = drupal_basename($file->uri);

  // Check if a file having the same properties exists already.
  if (isset($imce['files'][$file->filename])) {
    if (($f = $imce['files'][$file->filename]) && $f['width'] == $width && $f['height'] == $height) {
      drupal_set_message(t('%filename(%dimensions) already exists.', array('%filename' => utf8_encode($file->filename), '%dimensions' => $width . 'x' . $height)), 'error');
      return FALSE;
    }
  }

  // Validate file name.
  $errors = file_validate_name_length($file);
  if (!empty($errors)) {
    drupal_set_message($errors[0], 'error');
    return FALSE;
  }

  // Resize image.
  $image = image_load($imguri);
  $function = 'image_' . $op;
  if (!$image || !function_exists($function) || !$function($image, $width, $height)) {
    drupal_set_message(t('%filename cannot be resized to %dimensions', array('%filename' => utf8_encode($filename), '%dimensions' => $width . 'x' . $height)), 'error', FALSE);
    return FALSE;
  }

  // Copy to a temp file.
  if (!$tempuri = drupal_tempnam('temporary://', 'imce')) {
    return FALSE;
  }
  register_shutdown_function('file_unmanaged_delete', $tempuri);
  if (!image_save($image, $tempuri) || !$image->info) {
    return FALSE;
  }

  // Validate quota.
  $file->filesize = $image->info['file_size'];
  $quotadiff = $file->filename == $filename ? -$imce['files'][$filename]['size'] : 0;
  if (!imce_validate_quotas($file, $imce, $quotadiff)) {
    return FALSE;
  }

  // Build the rest of the file object.
  $file->uid = $imce['uid'];
  $file->filemime = $img['mime'];
  $file->status = FILE_STATUS_PERMANENT;
  $file->timestamp = REQUEST_TIME;

  // Copy from temp to file uri.
  $destination = $file->uri;
  $file->uri = $tempuri;
  if (!$file = file_copy($file, $destination, FILE_EXISTS_REPLACE)) {
    return FALSE;
  }
  imce_file_register($file);

  // Update file list.
  // If the file was scaled get the new dimensions.
  if ($op == 'scale') {
    $img = imce_image_info($file->uri);
    $width = $img['width'];
    $height = $img['height'];
  }
  $file->width = $width;
  $file->height = $height;
  imce_add_file($file, $imce);

  return $file;
}

/**
 * Add a new file to the file list.
 */
function imce_add_file($file, &$imce) {
  $imce['dirsize'] += $file->filesize;
  if (isset($imce['files'][$file->filename])) {
    $imce['dirsize'] -= $imce['files'][$file->filename]['size'];
  }
  $imce['files'][$file->filename] = array(
    'name' => $file->filename,
    'size' => $file->filesize,
    'width' => $file->width,
    'height' => $file->height,
    'date' => $file->timestamp,
  );
  if (isset($_GET['jsop'])) {
    $add = $imce['files'][$file->filename];
    $add['name'] = rawurlencode($file->filename);
    $add['fsize'] = format_size($file->filesize);
    $add['fdate'] = format_date($file->timestamp, 'short');
    $add['id'] = $file->fid;
    $imce['added'][] = $add;
  }
}

/**
 * Remove a file from the file list.
 */
function imce_remove_file($filename, &$imce) {
  if (isset($imce['files'][$filename])) {
    $imce['dirsize'] -= $imce['files'][$filename]['size'];
    unset($imce['files'][$filename]);
    if (isset($_GET['jsop'])) {
      $imce['removed'][] = rawurlencode($filename);
    }
  }
}

/**
 * Validate uploaded file.
 */
function imce_validate_all($file, $imce) {

  // Validate image resolution only if filesize validation passes.
  // because user might have uploaded a very big image
  // and scaling it may exploit system memory.
  $errors = imce_validate_filesize($file, $imce['filesize']);
  // Image resolution validation.
  if (empty($errors) && preg_match('/\.(png|gif|jpe?g)$/i', $file->filename)) {
    $errors = array_merge($errors, file_validate_image_resolution($file, $imce['dimensions']));
  }
  // Directory quota validation.
  if ($imce['quota']) {
    $errors = array_merge($errors, imce_validate_quota($file, $imce['quota'], $imce['dirsize']));
  }
  // User quota validation. check it if no errors were thrown.
  if (empty($errors) && $imce['tuquota']) {
    $errors = imce_validate_tuquota($file, $imce['tuquota'], file_space_used($imce['uid']));
  }
  return $errors;
}

/**
 * Validate filesize for maximum allowed file size.
 */
function imce_validate_filesize($file, $maxsize = 0) {
  $errors = array();
  if ($maxsize && $file->filesize > $maxsize) {
    $errors[] = t('The file is %filesize exceeding the maximum file size of %maxsize.', array('%filesize' => format_size($file->filesize), '%maxsize' => format_size($maxsize)));
  }
  return $errors;
}

/**
 * Validate filesize for directory quota.
 */
function imce_validate_quota($file, $quota = 0, $currentsize = 0) {
  $errors = array();
  if ($quota && ($currentsize + $file->filesize) > $quota) {
    $errors[] = t('%filename is %filesize which would exceed your directory quota. You are currently using %size of %total_quota.', array(
      '%size' => format_size($currentsize),
      '%total_quota' => format_size($quota),
      '%filesize' => format_size($file->filesize),
      '%filename' => utf8_encode($file->filename),
    ));
  }
  return $errors;
}

/**
 * Validate filesize for total user quota.
 */
function imce_validate_tuquota($file, $quota = 0, $currentsize = 0) {
  $errors = array();
  if ($quota && ($currentsize + $file->filesize) > $quota) {
    $errors[] = t('%filename is %filesize which would exceed your total user quota. You are currently using %size of %total_quota.', array(
      '%size' => format_size($currentsize),
      '%total_quota' => format_size($quota),
      '%filesize' => format_size($file->filesize),
      '%filename' => utf8_encode($file->filename),
    ));
  }
  return $errors;
}

/**
 * Validate both directory and total user quota. Returns true/false not errors.
 */
function imce_validate_quotas($file, &$imce, $add = 0) {
  $errors = imce_validate_quota($file, $imce['quota'], $imce['dirsize'] + $add);
  if (empty($errors) && $imce['tuquota']) {
    $errors = imce_validate_tuquota($file, $imce['tuquota'], file_space_used($imce['uid']) + $add);
  }
  if (!empty($errors)) {
    drupal_set_message($errors[0], 'error');
    return FALSE;
  }
  return TRUE;
}

/**
 * Checks if the file is an image and returns info.
 *
 * There are two switchable versions:
 *   * One that disables the metadata fetch entirely.
 *   * One that uses Drupal's image_get_info().
 *   * One that uses PHP's getimagesize().
 */
if (variable_get('imce_disable_image_info', 0)) {
  function imce_image_info($file) {
    $ext = pathinfo($file, PATHINFO_EXTENSION);
    if (in_array($ext, array('jpg', 'jpeg', 'gif', 'png',))) {

      $image_metadata = array(
        'width' => '-',
        'height' => '-',
        'type' => 2,
        'mime' => '',
      );

      return $image_metadata;
    }

    return FALSE;
  }
}
elseif (variable_get('imce_image_get_info', 0)) {

  function imce_image_info($file) {
    // Use APC to cache the file's data if available.
    if (function_exists('apcu_fetch')) {
      $cache = apcu_fetch($file);
      if ($cache) {
        return $cache;
      }
    }

    $mimes = array(
      'image/jpeg' => IMAGETYPE_JPEG,
      'image/gif'  => IMAGETYPE_GIF,
      'image/png'  => IMAGETYPE_PNG,
    );
    if (is_file($file) && ($dot = strrpos($file, '.')) && in_array(strtolower(substr($file, $dot+1)), array('jpg', 'jpeg', 'gif', 'png')) && ($info = @image_get_info($file)) && isset($mimes[$info['mime_type']]) ) {
      $result = array(
        'width' => $info['width'],
        'height' => $info['height'],
        'type' => $mimes[$info['mime_type']],
        'mime' => $info['mime_type'],
      );

      // Use APC to cache the file's data if available, so future requests of
      // this file will be quicker.
      if (function_exists('apcu_add')) {
        apcu_add($file, $result);
      }

      return $result;
    }
    return FALSE;
  }
}
else {
  function imce_image_info($file) {
    if (is_file($file) && ($dot = strrpos($file, '.')) && in_array(strtolower(substr($file, $dot+1)), array('jpg', 'jpeg', 'gif', 'png')) && ($info = @getimagesize($file)) && in_array($info[2], array(IMAGETYPE_JPEG, IMAGETYPE_GIF, IMAGETYPE_PNG)) ) {
      // Use APC to cache the file's data if available.
      if (function_exists('apcu_fetch')) {
        $cache = apcu_fetch($file);
        if ($cache) {
          return $cache;
        }
      }
      $result = array(
        'width' => $info[0],
        'height' => $info[1],
        'type' => $info[2],
        'mime' => $info['mime']
      );

      // Use APC to cache the file's data if available, so future requests of
      // this file will be quicker.
      if (function_exists('apcu_add')) {
        apcu_add($file,$result);
      }

      return $result;
    }
    return FALSE;
  }
}

/**
 * Return thumbnails as options to be used in upload form.
 */
function imce_thumbnail_options($thumbs = array()) {
  $options = array();
  foreach ($thumbs as $thumb) {
    $options[$thumb['name']] = $thumb['name'] . ' (' . $thumb['dimensions'] . ')';
  }
  return $options;
}

/**
 * Initiate and return configuration profile for the $user.
 */
function imce_initiate_profile($user, $scheme = NULL) {

  // Check user profile and translate tokens in directory paths
  // and evaluate php paths.
  if ($imce = imce_user_profile($user, $scheme)) {
    // Allow alteration of raw profile.
    foreach (variable_get('imce_custom_init', array()) as $func => $state) {
      if ($state && function_exists($func)) {
        $func($imce, $user);
      }
    }
    imce_process_directories($imce, $user);
    if (!empty($imce['directories'])) {
      $imce['uid'] = (int) $user->uid;
      $imce['url'] = url($_GET['q']);
      $imce['clean'] = variable_get('clean_url', 0) == 1;
      $imce['absurls'] = variable_get('imce_settings_absurls', 0) == 1;
      $imce['furl'] = file_create_url($imce['scheme'] . '://');
      // Convert from Mb to byte.
      $imce['filesize'] *= 1048576;
      $imce['quota'] *= 1048576;
      $imce['tuquota'] *= 1048576;
      $imce['filenum'] = (int) $imce['filenum'];
      // Check and set the active directory.
      if ($info = imce_working_directory($imce)) {
        $imce['direct'] = isset($imce['directories'][$info['name']]);
        $imce['directories'][$info['name']] = $info;
        $imce['dir'] = $info['name'];
        // Copy permissions of the active directory.
        $imce['perm'] = $info;
        unset($imce['perm']['name']);
      }
      else {
        drupal_set_message(t('Unable to get a working directory for the file browser!'), 'error');
        $imce['dir'] = FALSE;
        $imce['error'] = TRUE;
      }
      return $imce;
    }
    drupal_set_message(t('There is no valid directory specified for the file browser!'), 'error');
  }
  else {
    drupal_set_message(t('You do not have access to any configuration profile to use the file browser!'), 'error');
  }

  return FALSE;
}

/**
 * Get files and folders of the actve directory. Do custom processing.
 */
function imce_process_profile(&$imce) {
  // Get directory content. do a custom scan if it is set.
  $scan = ($scan = variable_get('imce_custom_scan', '')) && function_exists($scan) ? $scan : 'imce_scan_directory';
  $imce += $scan($imce['dir'], $imce);

  // Run custom process functions.
  foreach (variable_get('imce_custom_process', array()) as $func => $state) {
    if ($state && function_exists($func)) {
      $func($imce);
    }
  }

  // Set subdirectories.
  if (!$imce['error'] && !imce_subdirectories_accessible($imce)) {
    $imce['subdirectories'] = array();
  }
}

/**
 * Translate tokens and evaluate php in directory names.
 *
 * Convert directories into an associative array (dirname => info)
 */
function imce_process_directories(&$imce, $user) {
  $directories = $imce['directories'];
  $paths = array();
  $translate = array('%uid' => $user->uid);

  foreach ($directories as $directory) {
    if (substr($directory['name'], 0, 4) == 'php:') {
      $directory['name'] = eval(substr($directory['name'], 4));
      // The php may return an array of directories.
      if (is_array($directory['name'])) {
        foreach ($directory['name'] as $name) {
          $paths[$name] = array('name' => $name) + $directory;
        }
        continue;
      }
    }
    else {
      $directory['name'] = strtr($directory['name'], $translate);
    }
    if ($directory['name']) {
      $paths[$directory['name']] = $directory;
    }
  }

  $imce['directories'] = $paths;
}

/**
 * Return an available directory for the profile.
 */
function imce_working_directory(&$imce) {
  // Do not use session if there is only one directory assigned.
  $sess = TRUE;
  if (count($imce['directories']) < 2) {
    $perms = reset($imce['directories']);
    if (!isset($perms['subnav']) || !$perms['subnav']) {
      $sess = FALSE;
    }
  }
  // Check GET.
  if (isset($_GET['dir'])) {
    if ($info = imce_directory_info($_GET['dir'], $imce)) {
      if (imce_check_directory($_GET['dir'], $imce)) {
        if ($sess) {
          $_SESSION['imce_directory'] = rawurlencode($info['name']);
        }
      }
      else {
        $info = FALSE;
      }
    }
    else {
      imce_inaccessible_directory($_GET['dir'], $imce);
    }
    return $info;
  }

  // Check session.
  if ($sess && isset($_SESSION['imce_directory'])) {
    $dirname = rawurldecode($_SESSION['imce_directory']);
    if ($info = imce_directory_info($dirname, $imce)) {
      if (imce_check_directory($dirname, $imce)) {
        return $info;
      }
    }
  }

  // Or the whole list.
  foreach ($imce['directories'] as $dirname => $info) {
    $dirname = (string) $dirname;
    if (imce_check_directory($dirname, $imce)) {
      if ($sess) {
        $_SESSION['imce_directory'] = rawurlencode($dirname);
      }
      return $info;
    }
  }

  return FALSE;
}

/**
 * Create a writable directory(any level) under file system directory.
 */
function imce_check_directory($dirname, $imce) {
  $diruri = imce_dir_uri($imce, $dirname);

  if (!imce_reg_dir($dirname) || !file_prepare_directory($diruri, FILE_CREATE_DIRECTORY)) {
    return imce_inaccessible_directory($dirname, $imce);
  }

  return TRUE;
}

/**
 * Generate and log a directory access error.
 */
function imce_inaccessible_directory($dirname, $imce) {
  if (is_string($dirname)) {
    $dirname = utf8_encode($dirname);
    $diruri = imce_dir_uri($imce, $dirname);
    drupal_set_message(t('Directory %dirname is not accessible.', array('%dirname' => $dirname)), 'error');
    watchdog('imce', 'Access to %directory was denied.', array('%directory' => $diruri), WATCHDOG_ERROR);
  }
  return FALSE;
}

/**
 * Return the permissions.
 *
 * Return the permissions for a directory that is accessed directly or indirectly.
 *
 * A child of a predefined directory in the directory
 * list takes its parent's properties.
 * If it has multiple parents, it gets the properties of the latter in the list.
 */
function imce_directory_info($dirname, $imce) {

  if (isset($imce['directories'][$dirname])) {
    return $imce['directories'][$dirname];
  }

  $info = FALSE;
  if (imce_reg_dir($dirname)) {
    $diruri = imce_dir_uri($imce, $dirname);
    if (file_prepare_directory($diruri)) {
      foreach ($imce['directories'] as $name => $prop) {
        if ($prop['subnav'] && ($name === '.' || strpos($dirname . '/', $name . '/') === 0)) {
          $info = $prop;
          $info['name'] = $dirname;
        }
      }
    }
  }

  return $info;
}

/**
 * Detect if the subdirectories are accessible.
 *
 * Detect if the subdirectories are accessible through any
 * directory(not just the current one) in the list.
 */
function imce_subdirectories_accessible(&$imce) {

  if (!empty($imce['subdirectories'])) {
    if (!empty($imce['perm']['subnav'])) {
      return TRUE;
    }
    // Checking only the first one is sufficient.
    $dirname = reset($imce['subdirectories']);
    if ($imce['dir'] !== '.') {
      $dirname = $imce['dir'] . '/' . $dirname;
    }

    // Check if any setting is applicable for this subdirectory through
    // any directory in the list.
    foreach ($imce['directories'] as $name => $info) {
      $name = (string) $name;
      if ($info['subnav'] && $dirname != $name && ($name == '.' || strpos($dirname . '/', $name . '/') === 0)) {
        return TRUE;
      }
    }
  }

  return FALSE;
}

/**
 * Check if a permission is given to at least one directory in the list.
 */
function imce_perm_exists(&$imce, $perm) {
  static $perms = array();

  if (isset($perms[$perm])) {
    return $perms[$perm];
  }

  if (isset($imce['perm'][$perm]) && $imce['perm'][$perm]) {
    return $perms[$perm] = TRUE;
  }

  foreach ($imce['directories'] as $name => $info) {
    if (isset($info[$perm]) && $info[$perm]) {
      return $perms[$perm] = TRUE;
    }
  }

  return $perms[$perm] = FALSE;
}

/**
 * Scan directory and return file list, subdirectories, and total size.
 */
function imce_scan_directory($dirname, $imce) {

  $directory = array('dirsize' => 0, 'files' => array(), 'subdirectories' => array(), 'error' => FALSE);
  $diruri = imce_dir_uri($imce, $dirname);

  if (!is_string($dirname) || $dirname == '' || !$handle = opendir($diruri)) {
    imce_inaccessible_directory($dirname, $imce);
    $directory['error'] = TRUE;
    return $directory;
  }

  while (($file = readdir($handle)) !== FALSE) {

    // Do not include dot files or folders, or the UNIX lost+found directory.
    if ((substr($file, 0, 1) === '.') || ($file == 'lost+found')) {
      continue;
    }

    $path = $diruri . $file;

    if (is_dir($path)) {
      $directory['subdirectories'][] = $file;
      continue;
    }

    $width = $height = 0;
    if ($img = imce_image_info($path)) {
      $width = $img['width'];
      $height = $img['height'];
    }
    $size = @filesize($path) ?: 0;
    $date = @filemtime($path) ?: 0;
    $directory['files'][$file] = array(
      'name' => $file,
      'size' => $size,
      'width' => $width,
      'height' => $height,
      'date' => $date
    );
    $directory['dirsize'] += $size;
  }

  closedir($handle);
  sort($directory['subdirectories']);
  return $directory;
}

/**
 * Create directory tree.
 */
function imce_create_tree(&$imce) {
  $paths = array();
  // Rearrange paths as arg0=>arg1=>...
  foreach ($imce['directories'] as $path => $arr) {
    $tmp =& $paths;
    if ($path != '.') {
      $args = explode('/', $path);
      foreach ($args as $arg) {
        if (!isset($tmp[$arg])) {
          $tmp[$arg] = array();
        }
        $tmp =& $tmp[$arg];
      }
      $tmp[':access:'] = TRUE;
    }
    if ("$path" == $imce['dir']) {
      $tmp[':active:'] = TRUE;
      foreach ($imce['subdirectories'] as $arg) {
        $tmp[$arg][':access:'] = TRUE;
      }
    }
  }
  // Set root branch.
  $root = theme('imce_root_text', array('imce_ref' => array('imce' => &$imce)));
  $q = $imce['clean'] ? '?' : '&';
  if (isset($imce['directories']['.'])) {
    $root = '<a href="' . $imce['url'] . $q . 'dir=." title="." class="folder' . ($imce['dir'] == '.' ? ' active' : '') . '">' . $root . '</a>';
  }
  else {
    $root = '<a title="." class="folder disabled">' . $root . '</a>';
  }

  return $root . imce_tree_html($imce, $paths, $q);
}

/**
 * Return tree html.
 * This is not themable because it is complex and needs to be in a proper format for js processing.
 */
function imce_tree_html(&$imce, $paths, $q = '?', $prefix = '', $eprefix = '') {
  unset($paths[':access:'], $paths[':active:']);
  $html = '';
  foreach ($paths as $arg => $children) {
    $path = $prefix . $arg;
    $earg = rawurlencode($arg);
    $epath = $eprefix . $earg;
    if (isset($children[':access:']) || imce_directory_info($path, $imce)) {
      $a = '<a href="' . $imce['url'] . $q . 'dir=' . $epath . '" title="' . $epath . '" class="folder' . (isset($children[':active:']) ? ' active' : '') . '">' . $earg . '</a>';
    }
    else {
      $a = '<a title="' . $epath . '" class="folder disabled">' . $earg . '</a>';
    }
    $ul = imce_tree_html($imce, $children, $q, $path . '/', $epath . '/');
    $class = $ul ? ' class="expanded"' : (isset($children[':active:']) ? ' class="leaf"' : '');
    $html .= '<li' . $class . '>' . $a . $ul . '</li>';
  }
  if ($html) {
    $html = '<ul>' . $html . '</ul>';
  }
  return $html;
}

/**
 * Return the uri of the active directory
 */
function imce_dir_uri($imce, $dir = NULL) {
  if (!isset($dir)) {
    $dir = $imce['dir'];
  }
  $target = $dir === '.' ? '' : $dir;
  $uri = $imce['scheme'] . '://' . $target;
  // Uri is already normalized. Call alterers.
  drupal_alter('file_stream_wrapper_uri_normalize', $uri, $imce['scheme'], $target);
  // Add the slash.
  if (substr($uri, -1) !== '/') {
    $uri .= '/';
  }
  return $uri;
}

/**
 * Returns the text for the root directory in a directory tree.
 */
function theme_imce_root_text($variables) {
  // $imce = &$variables['imce_ref']['imce'];
  return '&lt;' . t('root') . '&gt;';
}

/**
 * Returns the html for user's file browser tab.
 */
function theme_imce_user_page($variables) {
  global $user;
  $account = $variables['account'];
  $options = array();
  // Switch to account's active folder.
  if ($user->uid == 1 && $account->uid != 1) {
    $imce = imce_initiate_profile($account);
    $options['query'] = array('dir' => $imce['dir']);
  }
  return '<iframe src="' . url('imce', $options) . '" frameborder="0" style="border: 1px solid #eee; width: 99%; height: 520px" class="imce-frame"></iframe>';
}

/**
 * Registers the file as an IMCE file.
 */
function imce_file_register($file) {
  if (!db_query("SELECT 1 FROM {file_usage} WHERE module = 'imce' AND fid = :fid", array(':fid' => $file->fid))->fetchField()) {
    file_usage_add($file, 'imce', 'file', $file->fid);
  }
}
