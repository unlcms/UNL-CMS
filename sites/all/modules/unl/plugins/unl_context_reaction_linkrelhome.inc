<?php

class unl_context_reaction_linkrelhome extends context_reaction {
  function options_form($context) {
    $values = $this->fetch_from_context($context);
    $form = array(
      '#tree' => TRUE,
      '#title' => t('link rel="home" tag variables'),
      'url' => array(
        '#title' => t('URL'),
        '#description' => t('Address to set as the href attribute in the \'link rel="home" tag\' for the purpose of setting the default selected breadcrumb in the UNL template.'),
        '#type' => 'textfield',
        '#maxlength' => 255,
        '#default_value' => isset($values['url']) ? $values['url'] : '',
      ),
    );
    return $form;
  }

  function execute() {
    foreach ($this->get_contexts() as $k => $v) {
      if (!empty($v->reactions[$this->plugin]['url'])) {
        $attributes = array(
          'rel' => 'home',
          'href' => check_plain($v->reactions[$this->plugin]['url']),
        );
        drupal_add_html_head_link($attributes);
      }
    }
  }
}
