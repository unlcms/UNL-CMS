<?php
/**
 * Implements hook_form_system_theme_settings_alter().
 * Done as THEMENAME_form_system_theme_settings_alter(), reference http://drupal.org/node/177868
 */
function unl_wdn_form_system_theme_settings_alter(&$form, &$form_state) {
  $form[] = array(
    '#type' => 'fieldset',
    '#title' => t('Site Name Abbreviation'),
    'site_name_abbreviation' => array(
      '#type' => 'textfield',
      '#default_value' => theme_get_setting('site_name_abbreviation'),
      '#description' => t('An abbreviated version of your site\'s name to use in breadcrumbs.'),
    ),
  );

  $form['intermediate_breadcrumbs'] = array(
    '#type' => 'fieldset',
    '#title' => t('Intermediate Breadcrumbs'),
    '#description' => t('Breadcrumbs that are displayed between the UNL breadcrumb and this site\'s breadcrumb'),
  );

  $intermediate_breadcrumbs = theme_get_setting('intermediate_breadcrumbs');
  for ($i = 0; $i < 3; $i++) {
    $form['intermediate_breadcrumbs'][] = array(
      'text' => array(
        '#type' => 'textfield',
        '#title' => t('Text ' . ($i + 1)),
        '#default_value' => isset($intermediate_breadcrumbs[$i]) ? $intermediate_breadcrumbs[$i]['text'] : '',
        '#parents' => array('intermediate_breadcrumbs' , $i, 'text'),
      ),
      'href' => array(
        '#type' => 'textfield',
        '#title' => t('URL ' . ($i + 1)),
        '#default_value' => isset($intermediate_breadcrumbs[$i]) ? $intermediate_breadcrumbs[$i]['href'] : '',
        '#parents' => array('intermediate_breadcrumbs' , $i, 'href'),
      ),
    );
  }

  $form[] = array(
    '#type' => 'fieldset',
    '#title' => t('Head HTML'),
    '#description' => t('Additional HTML (your own css, js) to be added inside the &lt;head&gt;&lt;/head&gt; tags.'),
    'head_html' => array(
      '#type' => 'textarea',
      '#default_value' => theme_get_setting('head_html'),
    ),
  );

  $form['advanced_settings'] = array(
    '#type' => 'fieldset',
    '#title' => t('Advanced Settings'),
    'zen_forms' => array(
      '#type' => 'checkbox',
      '#title' => t('Use Zen Forms'),
      '#default_value' => theme_get_setting('zen_forms'),
      '#description' => t('Transforms all forms into the list-based zen forms.'),
    ),
    'use_base' => array(
      '#type' => 'checkbox',
      '#title' => t('Use HTML Base Tag in Head'),
      '#default_value' => theme_get_setting('use_base'),
      '#description' => t('Adds an HTML Base tag to the &lt;head&gt; section with href="' . url('<front>', array('absolute' => TRUE)) . '"'),
    ),
    'wdn_beta' => array(
      '#type' => 'checkbox',
      '#title' => t('Use WDN Beta/Development CSS and JavaScript'),
      '#default_value' => theme_get_setting('wdn_beta'),
      '#description' => t('Replaces the links in &lt;head&gt; to the stable /wdn directory with the latest development versions.'),
      '#access' => _unl_wdn_use_wdn_beta(),
    ),
  );
}

/**
 * Custom access function to determine if it is staging or live since the live site should not allow WDN dev code to be used.
 * @TODO: Make this better using something other than site_name.
 */
function _unl_wdn_use_wdn_beta() {
  $site_name = variable_get('site_name');
  if (strpos($site_name, 'STAGING') === 0) {
    return TRUE;
  }
  return FALSE;
}
