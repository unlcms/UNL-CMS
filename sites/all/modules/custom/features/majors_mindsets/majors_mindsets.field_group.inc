<?php

/**
 * @file
 * majors_mindsets.field_group.inc
 */

/**
 * Implements hook_field_group_info().
 */
function majors_mindsets_field_group_info() {
  $field_groups = array();

  $field_group = new stdClass();
  $field_group->disabled = FALSE; /* Edit this to true to make a default field_group disabled initially */
  $field_group->api_version = 1;
  $field_group->identifier = 'group_admission_hero|node|admission|form';
  $field_group->group_name = 'group_admission_hero';
  $field_group->entity_type = 'node';
  $field_group->bundle = 'admission';
  $field_group->mode = 'form';
  $field_group->parent_name = '';
  $field_group->data = array(
    'label' => 'Hero',
    'weight' => '2',
    'children' => array(
      0 => 's_n_hero_image',
      1 => 's_n_hero_link',
      2 => 'n_admission_hero_title',
      3 => 'n_admission_hero_subhead',
    ),
    'format_type' => 'fieldset',
    'format_settings' => array(
      'formatter' => 'collapsible',
      'instance_settings' => array(
        'description' => '',
        'classes' => 'group-admission-hero field-group-fieldset',
        'required_fields' => 1,
        'id' => '',
      ),
    ),
  );
  $field_groups['group_admission_hero|node|admission|form'] = $field_group;

  $field_group = new stdClass();
  $field_group->disabled = FALSE; /* Edit this to true to make a default field_group disabled initially */
  $field_group->api_version = 1;
  $field_group->identifier = 'group_core_academic|node|major|form';
  $field_group->group_name = 'group_core_academic';
  $field_group->entity_type = 'node';
  $field_group->bundle = 'major';
  $field_group->mode = 'form';
  $field_group->parent_name = '';
  $field_group->data = array(
    'label' => 'Core Academic Experience',
    'weight' => '9',
    'children' => array(
      0 => 'n_major_core_academic_image',
      1 => 'n_major_core_academic_text',
    ),
    'format_type' => 'html-element',
    'format_settings' => array(
      'formatter' => 'collapsible',
      'instance_settings' => array(
        'description' => '',
        'classes' => 'group-core-academic field-group-fieldset',
        'required_fields' => 1,
        'id' => '',
        'element' => 'div',
        'show_label' => 0,
        'label_element' => 'div',
        'attributes' => '',
      ),
    ),
  );
  $field_groups['group_core_academic|node|major|form'] = $field_group;

  $field_group = new stdClass();
  $field_group->disabled = FALSE; /* Edit this to true to make a default field_group disabled initially */
  $field_group->api_version = 1;
  $field_group->identifier = 'group_courses|node|major|form';
  $field_group->group_name = 'group_courses';
  $field_group->entity_type = 'node';
  $field_group->bundle = 'major';
  $field_group->mode = 'form';
  $field_group->parent_name = '';
  $field_group->data = array(
    'label' => 'Courses',
    'weight' => '15',
    'children' => array(
      0 => 'n_major_courses_lead',
      1 => 'n_major_notable_courses',
    ),
    'format_type' => 'fieldset',
    'format_settings' => array(
      'formatter' => 'collapsible',
      'instance_settings' => array(
        'description' => '',
        'classes' => 'group-courses field-group-fieldset',
        'required_fields' => 1,
        'id' => '',
      ),
    ),
  );
  $field_groups['group_courses|node|major|form'] = $field_group;

  $field_group = new stdClass();
  $field_group->disabled = FALSE; /* Edit this to true to make a default field_group disabled initially */
  $field_group->api_version = 1;
  $field_group->identifier = 'group_how_you_learn|node|major|form';
  $field_group->group_name = 'group_how_you_learn';
  $field_group->entity_type = 'node';
  $field_group->bundle = 'major';
  $field_group->mode = 'form';
  $field_group->parent_name = '';
  $field_group->data = array(
    'label' => 'How You Learn',
    'weight' => '8',
    'children' => array(
      0 => 'n_major_hands_on',
      1 => 'n_major_career_skills',
      2 => 'n_major_social_connections',
      3 => 'n_major_make_difference',
      4 => 'n_major_core_academics',
      5 => 'n_major_research_grad_prep',
    ),
    'format_type' => 'fieldset',
    'format_settings' => array(
      'formatter' => 'collapsible',
      'instance_settings' => array(
        'description' => '',
        'classes' => 'group-how-you-learn field-group-fieldset',
        'required_fields' => 1,
        'id' => '',
      ),
    ),
  );
  $field_groups['group_how_you_learn|node|major|form'] = $field_group;

  $field_group = new stdClass();
  $field_group->disabled = FALSE; /* Edit this to true to make a default field_group disabled initially */
  $field_group->api_version = 1;
  $field_group->identifier = 'group_major_hero|node|major|form';
  $field_group->group_name = 'group_major_hero';
  $field_group->entity_type = 'node';
  $field_group->bundle = 'major';
  $field_group->mode = 'form';
  $field_group->parent_name = '';
  $field_group->data = array(
    'label' => 'Hero',
    'weight' => '2',
    'children' => array(
      0 => 'n_major_hero_link',
      1 => 'n_major_4year_link',
      2 => 'n_major_infovisit_link',
    ),
    'format_type' => 'fieldset',
    'format_settings' => array(
      'formatter' => 'collapsible',
      'instance_settings' => array(
        'description' => '',
        'classes' => 'group-major-hero field-group-fieldset',
        'required_fields' => 1,
        'id' => '',
      ),
    ),
  );
  $field_groups['group_major_hero|node|major|form'] = $field_group;

  $field_group = new stdClass();
  $field_group->disabled = FALSE; /* Edit this to true to make a default field_group disabled initially */
  $field_group->api_version = 1;
  $field_group->identifier = 'group_major_learn_career|node|major|form';
  $field_group->group_name = 'group_major_learn_career';
  $field_group->entity_type = 'node';
  $field_group->bundle = 'major';
  $field_group->mode = 'form';
  $field_group->parent_name = '';
  $field_group->data = array(
    'label' => 'Learn Outcomes Career',
    'weight' => '6',
    'children' => array(
      0 => 'n_major_what_youll_learn',
      1 => 'n_major_learning_outcomes',
      2 => 'n_major_career_connections',
    ),
    'format_type' => 'fieldset',
    'format_settings' => array(
      'formatter' => 'collapsible',
      'instance_settings' => array(
        'description' => '',
        'classes' => 'group-major-learn-career field-group-fieldset',
        'required_fields' => 1,
        'id' => '',
      ),
    ),
  );
  $field_groups['group_major_learn_career|node|major|form'] = $field_group;

  $field_group = new stdClass();
  $field_group->disabled = FALSE; /* Edit this to true to make a default field_group disabled initially */
  $field_group->api_version = 1;
  $field_group->identifier = 'group_mindset_hero|node|mindset|form';
  $field_group->group_name = 'group_mindset_hero';
  $field_group->entity_type = 'node';
  $field_group->bundle = 'mindset';
  $field_group->mode = 'form';
  $field_group->parent_name = '';
  $field_group->data = array(
    'label' => 'Hero',
    'weight' => '2',
    'children' => array(
      0 => 's_n_hero_image',
      1 => 'n_mindset_hero_title',
      2 => 'n_mindset_hero_text',
    ),
    'format_type' => 'fieldset',
    'format_settings' => array(
      'formatter' => 'collapsible',
      'instance_settings' => array(
        'description' => '',
        'classes' => 'group-mindset-hero field-group-fieldset',
        'required_fields' => 1,
        'id' => '',
      ),
    ),
  );
  $field_groups['group_mindset_hero|node|mindset|form'] = $field_group;

  $field_group = new stdClass();
  $field_group->disabled = FALSE; /* Edit this to true to make a default field_group disabled initially */
  $field_group->api_version = 1;
  $field_group->identifier = 'group_research_academics|node|major|form';
  $field_group->group_name = 'group_research_academics';
  $field_group->entity_type = 'node';
  $field_group->bundle = 'major';
  $field_group->mode = 'form';
  $field_group->parent_name = '';
  $field_group->data = array(
    'label' => 'Research Academics',
    'weight' => '10',
    'children' => array(
      0 => 's_p_supsec_secondary',
      1 => 'n_major_research_academic_link',
    ),
    'format_type' => 'html-element',
    'format_settings' => array(
      'formatter' => 'collapsible',
      'instance_settings' => array(
        'description' => '',
        'classes' => 'group-research-academics field-group-fieldset',
        'required_fields' => 1,
        'id' => '',
        'element' => 'div',
        'show_label' => 0,
        'label_element' => 'div',
        'attributes' => '',
      ),
    ),
  );
  $field_groups['group_research_academics|node|major|form'] = $field_group;

  $field_group = new stdClass();
  $field_group->disabled = FALSE; /* Edit this to true to make a default field_group disabled initially */
  $field_group->api_version = 1;
  $field_group->identifier = 'group_student_orgs|node|major|form';
  $field_group->group_name = 'group_student_orgs';
  $field_group->entity_type = 'node';
  $field_group->bundle = 'major';
  $field_group->mode = 'form';
  $field_group->parent_name = '';
  $field_group->data = array(
    'label' => 'Student Orgs',
    'weight' => '12',
    'children' => array(
      0 => 'n_major_student_orgs_link',
      1 => 'n_major_student_org_title_1',
      2 => 'n_major_student_org_title_2',
      3 => 'n_major_student_org_text_1',
      4 => 'n_major_student_org_text_2',
      5 => '_add_new_field',
    ),
    'format_type' => 'fieldset',
    'format_settings' => array(
      'formatter' => 'collapsible',
      'instance_settings' => array(
        'description' => '',
        'classes' => 'group-student-orgs field-group-fieldset',
        'required_fields' => 1,
        'id' => '',
      ),
    ),
  );
  $field_groups['group_student_orgs|node|major|form'] = $field_group;

  $field_group = new stdClass();
  $field_group->disabled = FALSE; /* Edit this to true to make a default field_group disabled initially */
  $field_group->api_version = 1;
  $field_group->identifier = 'group_study_abroad|node|major|form';
  $field_group->group_name = 'group_study_abroad';
  $field_group->entity_type = 'node';
  $field_group->bundle = 'major';
  $field_group->mode = 'form';
  $field_group->parent_name = '';
  $field_group->data = array(
    'label' => 'Study Abroad',
    'weight' => '11',
    'children' => array(
      0 => 'n_major_study_abroad',
      1 => 'n_major_study_abroad_link',
    ),
    'format_type' => 'fieldset',
    'format_settings' => array(
      'formatter' => 'collapsible',
      'instance_settings' => array(
        'description' => '',
        'classes' => 'group-study-abroad field-group-fieldset',
        'required_fields' => 1,
        'id' => '',
      ),
    ),
  );
  $field_groups['group_study_abroad|node|major|form'] = $field_group;

  $field_group = new stdClass();
  $field_group->disabled = FALSE; /* Edit this to true to make a default field_group disabled initially */
  $field_group->api_version = 1;
  $field_group->identifier = 'group_video|node|major|form';
  $field_group->group_name = 'group_video';
  $field_group->entity_type = 'node';
  $field_group->bundle = 'major';
  $field_group->mode = 'form';
  $field_group->parent_name = '';
  $field_group->data = array(
    'label' => 'Video',
    'weight' => '3',
    'children' => array(
      0 => 'n_major_mediahub_url',
      1 => 'n_major_video_title',
      2 => 'n_major_video_text',
    ),
    'format_type' => 'fieldset',
    'format_settings' => array(
      'formatter' => 'collapsible',
      'instance_settings' => array(
        'description' => '',
        'classes' => 'group-video field-group-fieldset',
        'required_fields' => 1,
        'id' => '',
      ),
    ),
  );
  $field_groups['group_video|node|major|form'] = $field_group;

  // Translatables
  // Included for use with string extractors like potx.
  t('Core Academic Experience');
  t('Courses');
  t('Hero');
  t('How You Learn');
  t('Learn Outcomes Career');
  t('Research Academics');
  t('Student Orgs');
  t('Study Abroad');
  t('Video');

  return $field_groups;
}
