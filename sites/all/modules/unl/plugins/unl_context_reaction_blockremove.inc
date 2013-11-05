<?php

class unl_context_reaction_blockremove extends context_reaction {
  function options_form($context) {
    $values = $this->fetch_from_context($context);
    $form = array(
      '#tree' => TRUE,
      '#title' => t('Block Remove variables'),
      'block' => array(
        '#title' => t('Block'),
        '#description' => t('Not much you can do with this. Currently used to remove main-menu from nav.'),
        '#type' => 'textfield',
        '#maxlength' => 255,
        '#default_value' => isset($values['block']) ? $values['block'] : 'main-menu',
      ),
    );
    return $form;
  }

  function execute(&$page) {
    global $theme;

    // The theme system might not yet be initialized. We need $theme.
    drupal_theme_initialize();

    // Populate all block regions
    $all_regions = system_region_list($theme);

    foreach ($this->get_contexts() as $k => $v) {
      if (!empty($v->reactions[$this->plugin]['block'])) {
        foreach (array_keys($all_regions) as $region) {
          if (array_key_exists($region, $page)) {
            foreach ($page[$region] as $key => $item) {
              if (isset($item['#block']->delta) && !empty($item['#block']->delta) && $item['#block']->delta == $v->reactions[$this->plugin]['block']) {
                unset($page[$region][$key]);
              }
            }
          }
        }
      }
    }
  }
}
