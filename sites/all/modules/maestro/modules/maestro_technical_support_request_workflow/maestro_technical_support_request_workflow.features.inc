<?php

/**
 * Implementation of hook_node_info().
 */
function maestro_technical_support_request_workflow_node_info() {
  $items = array(
    'technical_support_request' => array(
      'name' => t('Technical Support Request'),
      'base' => 'node_content',
      'description' => '',
      'has_title' => '1',
      'title_label' => t('Title'),
      'help' => ''
    ),
  );
  return $items;
}
