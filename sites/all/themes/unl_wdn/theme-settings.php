<?php

function unl_wdn_form_system_theme_settings_alter(&$form, &$form_state)
{
    $form['site_name_abbreviation'] = array(
        '#type' => 'textfield',
        '#title' => t('Site Name Abbreviation'),
        '#default_value' => theme_get_setting('site_name_abbreviation'),
        '#description' => t('An abbreviated version of your site\'s name to use in breadcrumbs.')
    );
    
    $form['zen_forms'] = array(
        '#type' => 'checkbox',
        '#title' => t('Use Zen Forms'),
        '#default_value' => theme_get_setting('zen_forms'),
        '#description' => t('Transforms all forms into the list-based zen forms.')
    );
    
    $form['use_base'] = array(
        '#type' => 'checkbox',
        '#title' => t('Use HTML Base Tag in Head'),
        '#default_value' => theme_get_setting('use_base'),
        '#description' => t('Adds an HTML Base tag to the &lt;head&gt; section with href="' . url('<front>', array('absolute' => TRUE)) . '"')
    );
}
