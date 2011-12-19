<?php

/**
 * Implements hook_form_system_theme_settings_alter().
 * Done as THEMENAME_form_system_theme_settings_alter(), reference http://drupal.org/node/177868
 */
function unl_wdn_form_system_theme_settings_alter(&$form, &$form_state) {
  global $user;

  // Add checkboxes to the Toggle Display form to hide UNL template items on an affiliate site
  $form['theme_settings'] += array(
    'toggle_unl_banner' => array(
      '#type' => 'checkbox',
      '#title' => t('UNL Affiliate Banner'),
      '#default_value' => theme_get_setting('toggle_unl_banner'),
      '#access' => theme_get_setting('unl_affiliate'),
    ),
    'toggle_unl_branding' => array(
      '#type' => 'checkbox',
      '#title' => t('UNL Branding Elements'),
      '#default_value' => theme_get_setting('toggle_unl_branding'),
      '#access' => theme_get_setting('unl_affiliate'),
    ),
    'toggle_unl_breadcrumb' => array(
      '#type' => 'checkbox',
      '#title' => t('UNL Breadcrumb'),
      '#default_value' => theme_get_setting('toggle_unl_breadcrumb'),
      '#access' => theme_get_setting('unl_affiliate'),
    ),
    'toggle_unl_search' => array(
      '#type' => 'checkbox',
      '#title' => t('UNL Search box'),
      '#default_value' => theme_get_setting('toggle_unl_search'),
      '#access' => theme_get_setting('unl_affiliate'),
    ),
    'toggle_unl_tools' => array(
      '#type' => 'checkbox',
      '#title' => t('UNL Tools'),
      '#default_value' => theme_get_setting('toggle_unl_tools'),
      '#access' => theme_get_setting('unl_affiliate'),
    ),
  );

  $form['intermediate_breadcrumbs'] = array(
    '#type' => 'fieldset',
    '#title' => t('Intermediate Breadcrumbs'),
    '#description' => t('Breadcrumbs that are displayed between the UNL breadcrumb and this site\'s breadcrumb'),
    'site_name_abbreviation' => array(
      '#type' => 'textfield',
      '#title' => t('Site Name Abbreviation'),
      '#default_value' => theme_get_setting('site_name_abbreviation'),
      '#description' => t('An abbreviated version of your site\'s name to use in breadcrumbs when not on the front page.'),
      '#weight' => 10,
    ),
  );
  $intermediate_breadcrumbs = theme_get_setting('intermediate_breadcrumbs');
  for ($i = 0; $i < 3; $i++) {
    $form['intermediate_breadcrumbs'][] = array(
      'text' => array(
        '#type' => 'textfield',
        '#field_prefix' => t('Text ' . ($i + 1)),
        '#default_value' => isset($intermediate_breadcrumbs[$i]) ? $intermediate_breadcrumbs[$i]['text'] : '',
        '#parents' => array('intermediate_breadcrumbs' , $i, 'text'),
      ),
      'href' => array(
        '#type' => 'textfield',
        '#field_prefix' => t('&nbsp;URL ' . ($i + 1)),
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
    'sidebar_first_width' => array(
      '#type' => 'textfield',
      '#title' => t('Sidebar first Grid Size'),
      '#default_value' => theme_get_setting('sidebar_first_width'),
      '#description' => t('Enter only the numeral, for grid4 just enter 4.'),
    ),
    'sidebar_second_width' => array(
      '#type' => 'textfield',
      '#title' => t('Sidebar second Grid Size'),
      '#default_value' => theme_get_setting('sidebar_second_width'),
      '#description' => t('Enter only the numeral, for grid4 just enter 4.'),
    ),
    'zen_forms' => array(
      '#type' => 'checkbox',
      '#title' => t('Use Zen Forms'),
      '#default_value' => theme_get_setting('zen_forms'),
      '#description' => t('Transforms all forms into the list-based zen forms.'),
    ),
    'wdn_beta' => array(
      '#type' => 'checkbox',
      '#title' => t('Use WDN Beta/Development CSS and JavaScript'),
      '#default_value' => theme_get_setting('wdn_beta'),
      '#description' => t('Replaces the links in &lt;head&gt; to the stable /wdn directory with the latest development versions.'),
      '#access' => _unl_wdn_use_wdn_beta(),
    ),
    'unl_affiliate' => array(
      '#type' => 'checkbox',
      '#title' => t('Affiliate Site'),
      '#default_value' => theme_get_setting('unl_affiliate'),
      '#description' => t('Grants access to the Color scheme picker, Logo image settings, Shortcut icon settings on this page for customizing the UNL template.'),
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
