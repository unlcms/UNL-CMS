<?php

/**
 * @file
 * Callbacks for the token module.
 */

/**
 * Implements hook_token_info().
 */
function unl_token_info() {
  $info['tokens']['node']['og_path'] = array(
    'name' => t('Group path'),
    'description' => t('The alias of the group the node is in.'),
  );

  return $info;
}

/**
 * Implements hook_tokens().
 */
function unl_tokens($type, $tokens, array $data = array(), array $options = array()) {
  $replacements = array();

  if ($type == 'node') {
    foreach ($tokens as $name => $original) {
      switch ($name) {
        case 'og_path':
          if (isset($data['node'])) {
            $group = field_get_items('node', $data['node'], 'og_group_ref');
            if ($group != null) {
              $keys = array_keys($group);
              foreach($keys as $key) {
                $og_path = drupal_get_path_alias('node/' . $group[$key]['target_id']);
              }
              $replacements[$original] = $og_path;
            }
          }
        break;
      }
    }
  }

  return $replacements;
}
