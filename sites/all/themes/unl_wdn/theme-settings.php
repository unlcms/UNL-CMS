<?php

function unl_wdn_form_system_theme_settings_alter(&$form, &$form_state)
{
    $form[] = array(
        '#type' => 'fieldset',
        '#title' => t('Site Name Abbreviation'),
        
        'site_name_abbreviation' => array(
	        '#type' => 'textfield',
	        '#default_value' => theme_get_setting('site_name_abbreviation'),
	        '#description' => t('An abbreviated version of your site\'s name to use in breadcrumbs.')
        )
    );
    
    $form['intermediate_breadcrumb'] = array(
        '#type' => 'fieldset',
        '#title' => t('Intermediate Breadcrumb'),
        '#description' => t('A breadcrumb that is displayed between the UNL breadcrumb and this site\'s breadcrumb'),
    
        'intermediate_breadcrumb_text' => array( 
            '#type' => 'textfield',
            '#title' => t('Text'),
            '#default_value' => theme_get_setting('intermediate_breadcrumb_text'),
            '#description' => t('An abbreviated version of your site\'s name to use in breadcrumbs.')
        ),
        
        'intermediate_breadcrumb_href' => array(
            '#type' => 'textfield',
            '#title' => t('URL'),
            '#default_value' => theme_get_setting('intermediate_breadcrumb_href'),
            '#description' => t('An abbreviated version of your site\'s name to use in breadcrumbs.')
        )
    );
    
    $form['advanced_settings'] = array(
        '#type' => 'fieldset',
        '#title' => t('Advanced Settings'),
    
        'zen_forms' => array(
	        '#type' => 'checkbox',
	        '#title' => t('Use Zen Forms'),
	        '#default_value' => theme_get_setting('zen_forms'),
	        '#description' => t('Transforms all forms into the list-based zen forms.')
        ),
    
        'use_base' => array(
	        '#type' => 'checkbox',
	        '#title' => t('Use HTML Base Tag in Head'),
	        '#default_value' => theme_get_setting('use_base'),
	        '#description' => t('Adds an HTML Base tag to the &lt;head&gt; section with href="' . url('<front>', array('absolute' => TRUE)) . '"')
        )
    );
}
