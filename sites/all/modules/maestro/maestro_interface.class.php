<?php
// $Id: maestro_interface.class.php,v 1.25 2010/08/31 15:13:27 chevy Exp $

/**
 * @file
 * maestro_task_interface.class.php
 */

class MaestroInterface {
  private $_template_id;

  function __construct($template_id) {
    $this->_template_id = $template_id;
    $context_options = cache_get('maestro_taskclass_info');

    // Test if context options are cached - if not then we will set it
    // The class function getContextMenu will read options from the cache
    if($context_options === FALSE) {
      $context_options = array();
      // Scan through each available class type and fetch its corresponding context menu.
      foreach (module_implements('maestro_get_taskobject_info') as $module) {
        $function = $module . '_maestro_get_taskobject_info';
        if ($arr = $function()) {
          $context_options = maestro_array_add($context_options, $arr);
        }
      }
      cache_set('maestro_taskclass_info', $context_options);
    }

    $handler_options = cache_get('maestro_handler_options');
    // Test if task type handler options are cached - if not then we will set it
    // The class function getHandlerOptions will read options from the cache
    if($handler_options === FALSE) {
      // Scan through each available class type and fetch its corresponding context menu.
      foreach (module_implements('maestro_handler_options') as $module) {
        $function = $module . '_maestro_handler_options';
        if ($arr = $function()) {
          $handler_options = maestro_array_merge_keys($arr,$handler_options);
        }
      }
      cache_set('maestro_handler_options', $handler_options);
    }

  }

  //displays the main task page
  function displayPage() {
    global $base_url;
    $maestro_url = $base_url . '/' . drupal_get_path('module', 'maestro');
    $res = db_select('maestro_template', 'a');
    $res->fields('a', array('template_name', 'canvas_height'));
    $res->condition('id', $this->_template_id, '=');
    $t_rec = current($res->execute()->fetchAll());

    $build['workflow_template'] = array(
      '#theme' => 'maestro_workflow_edit',
      '#tid' => $this->_template_id,
      '#mi' => $this,
      '#maestro_url' => $maestro_url,
      '#t_rec' => $t_rec
    );
    $build['workflow_template']['#attached']['library'][] = array('system', 'ui.draggable');
    $build['workflow_template']['#attached']['js'][] = array('data' => '(function($){$(function() { $(".maestro_task_container").draggable( {snap: true} ); })})(jQuery);', 'type' => 'inline');

    return drupal_render($build);
  }

  function displayTasks() {
    $html = '';
    $res = db_query('SELECT id, taskname, task_class_name FROM {maestro_template_data} WHERE template_id=:tid', array(':tid' => $this->_template_id));

    foreach ($res as $rec) {
      $task_type = substr($rec->task_class_name, 15);
      $task_class = 'MaestroTaskInterface' . $task_type;

      if (class_exists($task_class)) {
        $ti = new $task_class($rec->id);
      }
      else {
        $ti = new MaestroTaskInterfaceUnknown($rec->id, 0, $task_class);
      }

      $html .= $ti->displayTask();
    }

    return $html;
  }

  //should get the valid task types to create, excluding start and end tasks, from the drupal cache
  function getContextMenu() {
    //we need to rebuild the cache in the event it is empty.
    $options = cache_get('maestro_taskclass_info');
    return $options;
  }

  function getContextMenuHTML() {
    $options = $this->getContextMenu();
    $html = "<div id=\"maestro_main_context_menu\" class=\"maestro_context_menu\"><ul>\n";

    foreach ($options->data as $key => $option) {
      $task_type = substr($option['class_name'], 20);
      $option = t($option['display_name']);
      $html .= "<li style=\"white-space: nowrap;\" id=\"$task_type\">$option</li>\n";
    }
    $html .= "</ul></div>\n";

    return $html;
  }

  function getContextMenuJS() {
    $options = $this->getContextMenu();
    $js  = "(function ($) {\n";
    $js .= "\$('#maestro_workflow_container').contextMenu('maestro_main_context_menu', {\n";
    $js .= "menuStyle: {\n";
    $js .= "width: 175,\n";
    $js .= "fontSize: 12,\n";
    $js .= "},\n";

    $js .= "itemStyle: {\n";
    $js .= "padding: 0,\n";
    $js .= "paddingLeft: 10,\n";
    $js .= "},\n";

    $js .= "bindings: {\n";

    foreach ($options->data as $key => $option) {
      $task_type = substr($option['class_name'], 20);
      $js .= "'$task_type': function(t) {\n";
      $js .= "enable_ajax_indicator();\n";
      $js .= "\$.ajax({
        type: 'POST',
        url: ajax_url + 'MaestroTaskInterface$task_type/0/{$this->_template_id}/create/',
        cache: false,
        data: {task_type: '$task_type', offset_left: posx, offset_top: posy},
        dataType: 'json',
        success: add_task_success,
        error: editor_ajax_error
      });\n";
      $js .= "},\n";
    }

    $js .= "}\n";
    $js .= "});\n";
    $js .= "})(jQuery);\n";

    return $js;
  }

  function initializeJavascriptArrays() {
    $js = '';
    $res = db_query('SELECT id, offset_left, offset_top FROM {maestro_template_data} WHERE template_id=:tid', array(':tid' => $this->_template_id));
    $i = 0;
    $j = 0;
    foreach ($res as $rec) {
      $js .= "existing_tasks[{$i}] = ['task{$rec->id}', {$rec->offset_left}, {$rec->offset_top}];\n";
      $i++;
      $res2 = DB_query("SELECT template_data_to, template_data_to_false FROM {maestro_template_data_next_step} WHERE template_data_from=:tid", array(':tid'=>$rec->id));
      foreach ($res2 as $rec2) {
        $to = intval ($rec2->template_data_to);
        $to_false = intval ($rec2->template_data_to_false);
        if ($to != 0) {
          $js .= "line_ids[{$j}] = ['task{$rec->id}', 'task{$to}', true];\n";
          $j++;
        }
        if ($to_false != 0) {
          $js .= "line_ids[{$j}] = ['task{$rec->id}', 'task{$to_false}', false];\n";
          $j++;
        }
      }
    }

    return $js;
  }
}

?>
