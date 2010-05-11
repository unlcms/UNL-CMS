<?php

function unl_wdn_form_system_theme_settings_alter(&$form, &$form_state)
{
    $form['zen_forms'] = array(
        '#type' => 'checkbox',
        '#title' => t('Use Zen Forms'),
        '#default_value' => theme_get_setting('zen_forms'),
        '#description' => t('Transforms all forms into the list-based zen forms.')
    );
}
