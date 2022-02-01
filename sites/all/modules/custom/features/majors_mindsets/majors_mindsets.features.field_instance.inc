<?php

/**
 * @file
 * majors_mindsets.features.field_instance.inc
 */

/**
 * Implements hook_field_default_field_instances().
 */
function majors_mindsets_field_default_field_instances() {
  $field_instances = array();

  // Exported field_instance: 'node-admission-n_admission_college_specific'.
  $field_instances['node-admission-n_admission_college_specific'] = array(
    'bundle' => 'admission',
    'default_value' => array(
      0 => array(
        'summary' => '',
        'value' => '<div class="dcf-bleed dcf-d-grid unl-grid-cols dcf-pt-8 dcf-pb-8 dcf-ai-center">
    <div class="dcf-2nd dcf-pt-6 dcf-pl-7 dcf-pb-6 dcf-pr-7 dcf-z-1 dev-bg-scarlet-gradient-transparent dev-section-featured-region-2">
        <h3 class="dcf-txt-h4 unl-cream">Here is a default College Specific Header</h3>
        <p class="unl-cream">Here\'s some college-specific text that you can replace with your own. Here\'s some college-specific text that you can replace with your own. Here\'s some college-specific text that you can replace with your own. Here\'s some college-specific text that you can replace with your own. </p>
        <a href="https://admissions.unl.edu/" class="unl-cream">Replace this link text</a>
    </div>
    <div class="dcf-1st dcf-relative dev-section-featured-region-1">
        <div class="dcf-ratio dcf-ratio-16x9 tl-bg-block mobile-img-width">
            <img class="dcf-ratio-child dcf-obj-fit-cover" src="https://wdn.unl.edu/images/documentation/dcf-bands-basketball-halftime.jpg" alt="Cheerleaders holding flags at Nebraska Basketball game">
        </div>
        <div class="dcf-absolute dcf-pin-top dcf-pin-left dcf-ratio dcf-ratio-16x9 asem-ms-z-neg1 asem-ms-bg-block asem-ms-bg-block-lt mobile-screen">
            <div class="dcf-ratio-child unl-bg-dots-gray"></div>
        </div>
    </div>
</div>',
        'format' => 'html',
      ),
    ),
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'hidden',
        'module' => 'text',
        'settings' => array(),
        'type' => 'text_default',
        'weight' => 4,
      ),
      'teaser' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'node',
    'field_name' => 'n_admission_college_specific',
    'label' => 'College Specific',
    'required' => 0,
    'settings' => array(
      'display_summary' => 0,
      'text_processing' => 1,
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'text',
      'settings' => array(
        'rows' => 20,
        'summary_rows' => 5,
      ),
      'type' => 'text_textarea_with_summary',
      'weight' => 10,
    ),
  );

  // Exported field_instance: 'node-admission-n_admission_hero_subhead'.
  $field_instances['node-admission-n_admission_hero_subhead'] = array(
    'bundle' => 'admission',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'above',
        'module' => 'text',
        'settings' => array(),
        'type' => 'text_default',
        'weight' => 7,
      ),
      'teaser' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'node',
    'field_name' => 'n_admission_hero_subhead',
    'label' => 'Admission Hero Subhead',
    'required' => 0,
    'settings' => array(
      'text_processing' => 0,
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'text',
      'settings' => array(
        'size' => 60,
      ),
      'type' => 'text_textfield',
      'weight' => 5,
    ),
  );

  // Exported field_instance: 'node-admission-n_admission_hero_title'.
  $field_instances['node-admission-n_admission_hero_title'] = array(
    'bundle' => 'admission',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'above',
        'module' => 'text',
        'settings' => array(),
        'type' => 'text_default',
        'weight' => 6,
      ),
      'teaser' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'node',
    'field_name' => 'n_admission_hero_title',
    'label' => 'Admission Hero Title',
    'required' => 0,
    'settings' => array(
      'text_processing' => 0,
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'text',
      'settings' => array(
        'size' => 60,
      ),
      'type' => 'text_textfield',
      'weight' => 4,
    ),
  );

  // Exported field_instance: 'node-admission-n_admission_lead_copy'.
  $field_instances['node-admission-n_admission_lead_copy'] = array(
    'bundle' => 'admission',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'hidden',
        'module' => 'text',
        'settings' => array(),
        'type' => 'text_default',
        'weight' => 1,
      ),
      'teaser' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'node',
    'field_name' => 'n_admission_lead_copy',
    'label' => 'Lead Copy',
    'required' => 0,
    'settings' => array(
      'display_summary' => 0,
      'text_processing' => 0,
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'text',
      'settings' => array(
        'rows' => 5,
        'summary_rows' => 5,
      ),
      'type' => 'text_textarea_with_summary',
      'weight' => 7,
    ),
  );

  // Exported field_instance: 'node-admission-n_admission_mindsets'.
  $field_instances['node-admission-n_admission_mindsets'] = array(
    'bundle' => 'admission',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'hidden',
        'module' => 'paragraphs',
        'settings' => array(
          'view_mode' => 'full',
        ),
        'type' => 'paragraphs_view',
        'weight' => 3,
      ),
      'teaser' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'node',
    'field_name' => 'n_admission_mindsets',
    'label' => 'Mindsets',
    'required' => 0,
    'settings' => array(
      'add_mode' => 'select',
      'allowed_bundles' => array(
        'major' => -1,
        'mindset_teaser' => 'mindset_teaser',
        'test_paragraph_bundle_eric_' => -1,
      ),
      'bundle_weights' => array(
        'major' => 2,
        'mindset_teaser' => 3,
        'test_paragraph_bundle_eric_' => 4,
      ),
      'default_edit_mode' => 'open',
      'default_edit_mode_override' => 1,
      'title' => 'Paragraph',
      'title_multiple' => 'Paragraphs',
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 0,
      'module' => 'paragraphs',
      'settings' => array(),
      'type' => 'paragraphs_embed',
      'weight' => 9,
    ),
  );

  // Exported field_instance: 'node-admission-n_admission_scholarship'.
  $field_instances['node-admission-n_admission_scholarship'] = array(
    'bundle' => 'admission',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'above',
        'module' => 'paragraphs',
        'settings' => array(
          'view_mode' => 'full',
        ),
        'type' => 'paragraphs_view',
        'weight' => 10,
      ),
      'teaser' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'node',
    'field_name' => 'n_admission_scholarship',
    'label' => 'Scholarship',
    'required' => 0,
    'settings' => array(
      'add_mode' => 'select',
      'allowed_bundles' => array(
        'contact' => -1,
        'major' => -1,
        'mindset_teaser' => -1,
        'section_featured' => 'section_featured',
        'section_secondary' => -1,
        'support_section' => -1,
        'test_paragraph_bundle_eric_' => -1,
      ),
      'bundle_weights' => array(
        'contact' => 2,
        'major' => 3,
        'mindset_teaser' => 4,
        'section_featured' => 5,
        'section_secondary' => 6,
        'support_section' => 7,
        'test_paragraph_bundle_eric_' => 8,
      ),
      'default_edit_mode' => 'open',
      'default_edit_mode_override' => 1,
      'title' => 'Paragraph',
      'title_multiple' => 'Paragraphs',
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 0,
      'module' => 'paragraphs',
      'settings' => array(),
      'type' => 'paragraphs_embed',
      'weight' => 11,
    ),
  );

  // Exported field_instance: 'node-admission-s_n_contacts'.
  $field_instances['node-admission-s_n_contacts'] = array(
    'bundle' => 'admission',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'above',
        'module' => 'paragraphs',
        'settings' => array(
          'view_mode' => 'full',
        ),
        'type' => 'paragraphs_view',
        'weight' => 8,
      ),
      'teaser' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'node',
    'field_name' => 's_n_contacts',
    'label' => 'Contacts',
    'required' => 0,
    'settings' => array(
      'add_mode' => 'select',
      'allowed_bundles' => array(
        'contact' => 'contact',
        'major' => -1,
        'mindset_teaser' => -1,
        'test_paragraph_bundle_eric_' => -1,
      ),
      'bundle_weights' => array(
        'contact' => 2,
        'major' => 3,
        'mindset_teaser' => 4,
        'test_paragraph_bundle_eric_' => 5,
      ),
      'default_edit_mode' => 'open',
      'default_edit_mode_override' => 1,
      'title' => 'Paragraph',
      'title_multiple' => 'Paragraphs',
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 0,
      'module' => 'paragraphs',
      'settings' => array(),
      'type' => 'paragraphs_embed',
      'weight' => 12,
    ),
  );

  // Exported field_instance: 'node-admission-s_n_hero_image'.
  $field_instances['node-admission-s_n_hero_image'] = array(
    'bundle' => 'admission',
    'deleted' => 0,
    'description' => 'This image should be 16x9 aspect ratio and optimized for the web, no larger than 2400 pixels wide.',
    'display' => array(
      'default' => array(
        'label' => 'hidden',
        'module' => 'image',
        'settings' => array(
          'class' => 'dcf-ratio-child dcf-obj-fit-cover',
          'image_link' => '',
          'image_style' => 'mindsets_hero',
        ),
        'type' => 'image',
        'weight' => 2,
      ),
      'teaser' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'node',
    'field_name' => 's_n_hero_image',
    'label' => 'Hero Image',
    'required' => 0,
    'settings' => array(
      'alt_field' => 1,
      'default_image' => 0,
      'file_directory' => 'images/mindset/hero',
      'file_extensions' => 'jpg jpeg',
      'max_filesize' => '',
      'max_resolution' => '',
      'min_resolution' => '',
      'title_field' => 0,
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'image',
      'settings' => array(
        'preview_image_style' => 'thumbnail',
        'progress_indicator' => 'throbber',
      ),
      'type' => 'image_image',
      'weight' => 3,
    ),
  );

  // Exported field_instance: 'node-admission-s_n_hero_link'.
  $field_instances['node-admission-s_n_hero_link'] = array(
    'bundle' => 'admission',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'above',
        'module' => 'link',
        'settings' => array(),
        'type' => 'link_default',
        'weight' => 5,
      ),
      'teaser' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'node',
    'field_name' => 's_n_hero_link',
    'label' => 'Admission Links',
    'required' => 0,
    'settings' => array(
      'attributes' => array(
        'class' => 'dcf-btn dcf-btn-primary',
        'configurable_title' => 0,
        'rel' => '',
        'target' => 'default',
        'title' => '',
      ),
      'display' => array(
        'url_cutoff' => 80,
      ),
      'enable_tokens' => 1,
      'rel_remove' => 'default',
      'title' => 'optional',
      'title_maxlength' => 128,
      'title_value' => '',
      'url' => 0,
      'user_register_form' => FALSE,
      'validate_url' => 1,
    ),
    'widget' => array(
      'active' => 0,
      'module' => 'link',
      'settings' => array(),
      'type' => 'link_field',
      'weight' => 6,
    ),
  );

  // Exported field_instance: 'node-admission-s_p_supsec_featured'.
  $field_instances['node-admission-s_p_supsec_featured'] = array(
    'bundle' => 'admission',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'above',
        'module' => 'paragraphs',
        'settings' => array(
          'view_mode' => 'full',
        ),
        'type' => 'paragraphs_view',
        'weight' => 9,
      ),
      'teaser' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'node',
    'field_name' => 's_p_supsec_featured',
    'label' => 'Section Majors',
    'required' => 0,
    'settings' => array(
      'add_mode' => 'select',
      'allowed_bundles' => array(
        'contact' => -1,
        'major' => -1,
        'mindset_teaser' => -1,
        'section_featured' => 'section_featured',
        'section_secondary' => -1,
        'support_section' => -1,
        'test_paragraph_bundle_eric_' => -1,
      ),
      'bundle_weights' => array(
        'contact' => 2,
        'major' => 3,
        'mindset_teaser' => 4,
        'section_featured' => 5,
        'section_secondary' => 6,
        'support_section' => 7,
        'test_paragraph_bundle_eric_' => 8,
      ),
      'default_edit_mode' => 'open',
      'default_edit_mode_override' => 1,
      'title' => 'Paragraph',
      'title_multiple' => 'Paragraphs',
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 0,
      'module' => 'paragraphs',
      'settings' => array(),
      'type' => 'paragraphs_embed',
      'weight' => 8,
    ),
  );

  // Exported field_instance: 'node-major-n_major_4year_link'.
  $field_instances['node-major-n_major_4year_link'] = array(
    'bundle' => 'major',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => 'This link will be replicated further down page with course descriptions.',
    'display' => array(
      'default' => array(
        'label' => 'hidden',
        'module' => 'link',
        'settings' => array(),
        'type' => 'link_default',
        'weight' => 8,
      ),
      'teaser' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'node',
    'field_name' => 'n_major_4year_link',
    'label' => '4 Year Plans',
    'required' => 0,
    'settings' => array(
      'attributes' => array(
        'class' => 'dcf-btn dcf-btn-primary',
        'configurable_title' => 0,
        'rel' => '',
        'target' => 'default',
        'title' => '',
      ),
      'display' => array(
        'url_cutoff' => 80,
      ),
      'enable_tokens' => 1,
      'rel_remove' => 'default',
      'title' => 'optional',
      'title_maxlength' => 128,
      'title_value' => '',
      'url' => 0,
      'user_register_form' => FALSE,
      'validate_url' => 1,
    ),
    'widget' => array(
      'active' => 0,
      'module' => 'link',
      'settings' => array(),
      'type' => 'link_field',
      'weight' => 7,
    ),
  );

  // Exported field_instance: 'node-major-n_major_areas_emphasis'.
  $field_instances['node-major-n_major_areas_emphasis'] = array(
    'bundle' => 'major',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'hidden',
        'module' => 'text',
        'settings' => array(),
        'type' => 'text_default',
        'weight' => 11,
      ),
      'teaser' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'node',
    'field_name' => 'n_major_areas_emphasis',
    'label' => 'Areas of Emphasis',
    'required' => 0,
    'settings' => array(
      'text_processing' => 0,
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'text',
      'settings' => array(
        'size' => 60,
      ),
      'type' => 'text_textfield',
      'weight' => 5,
    ),
  );

  // Exported field_instance: 'node-major-n_major_career_connections'.
  $field_instances['node-major-n_major_career_connections'] = array(
    'bundle' => 'major',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => 'Maximum of four points emphasizing Career Connections with this major.',
    'display' => array(
      'default' => array(
        'label' => 'hidden',
        'module' => 'text',
        'settings' => array(),
        'type' => 'text_default',
        'weight' => 14,
      ),
      'teaser' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'node',
    'field_name' => 'n_major_career_connections',
    'label' => 'Career Connections',
    'required' => 0,
    'settings' => array(
      'text_processing' => 0,
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'text',
      'settings' => array(
        'size' => 100,
      ),
      'type' => 'text_textfield',
      'weight' => 9,
    ),
  );

  // Exported field_instance: 'node-major-n_major_career_paths'.
  $field_instances['node-major-n_major_career_paths'] = array(
    'bundle' => 'major',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'above',
        'module' => 'paragraphs',
        'settings' => array(
          'view_mode' => 'full',
        ),
        'type' => 'paragraphs_view',
        'weight' => 29,
      ),
      'teaser' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'node',
    'field_name' => 'n_major_career_paths',
    'label' => 'Career Paths',
    'required' => 0,
    'settings' => array(
      'add_mode' => 'select',
      'allowed_bundles' => array(
        'career_path' => 'career_path',
        'contact' => -1,
        'mindset_teaser' => -1,
        'notable_course' => -1,
        'point_of_pride' => -1,
        'section_featured' => -1,
        'section_secondary' => -1,
        'support_section' => -1,
        'test_paragraph_bundle_eric_' => -1,
      ),
      'bundle_weights' => array(
        'career_path' => 2,
        'contact' => 3,
        'mindset_teaser' => 4,
        'notable_course' => 5,
        'point_of_pride' => 6,
        'section_featured' => 7,
        'section_secondary' => 8,
        'support_section' => 9,
        'test_paragraph_bundle_eric_' => 10,
      ),
      'default_edit_mode' => 'open',
      'default_edit_mode_override' => 1,
      'title' => 'Paragraph',
      'title_multiple' => 'Paragraphs',
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 0,
      'module' => 'paragraphs',
      'settings' => array(),
      'type' => 'paragraphs_embed',
      'weight' => 13,
    ),
  );

  // Exported field_instance: 'node-major-n_major_career_skills'.
  $field_instances['node-major-n_major_career_skills'] = array(
    'bundle' => 'major',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'hidden',
        'module' => 'text',
        'settings' => array(),
        'type' => 'text_default',
        'weight' => 16,
      ),
      'teaser' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'node',
    'field_name' => 'n_major_career_skills',
    'label' => 'Career Skills',
    'required' => 0,
    'settings' => array(
      'text_processing' => 0,
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'text',
      'settings' => array(
        'size' => 100,
      ),
      'type' => 'text_textfield',
      'weight' => 12,
    ),
  );

  // Exported field_instance: 'node-major-n_major_core_academic_image'.
  $field_instances['node-major-n_major_core_academic_image'] = array(
    'bundle' => 'major',
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'hidden',
        'module' => 'image',
        'settings' => array(
          'class' => 'dcf-ratio-child dcf-obj-fit-cover',
          'image_link' => '',
          'image_style' => '',
        ),
        'type' => 'image',
        'weight' => 21,
      ),
      'teaser' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'node',
    'field_name' => 'n_major_core_academic_image',
    'label' => 'Core Academic Image',
    'required' => 0,
    'settings' => array(
      'alt_field' => 1,
      'default_image' => 0,
      'file_directory' => 'images/mindset/major',
      'file_extensions' => 'jpg jpeg',
      'max_filesize' => '',
      'max_resolution' => '',
      'min_resolution' => '',
      'title_field' => 0,
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'image',
      'settings' => array(
        'preview_image_style' => 'thumbnail',
        'progress_indicator' => 'throbber',
      ),
      'type' => 'image_image',
      'weight' => 12,
    ),
  );

  // Exported field_instance: 'node-major-n_major_core_academic_text'.
  $field_instances['node-major-n_major_core_academic_text'] = array(
    'bundle' => 'major',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'hidden',
        'module' => 'text',
        'settings' => array(),
        'type' => 'text_default',
        'weight' => 22,
      ),
      'teaser' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'node',
    'field_name' => 'n_major_core_academic_text',
    'label' => 'Core Academic Text',
    'required' => 0,
    'settings' => array(
      'display_summary' => 0,
      'text_processing' => 1,
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'text',
      'settings' => array(
        'rows' => 10,
        'summary_rows' => 5,
      ),
      'type' => 'text_textarea_with_summary',
      'weight' => 13,
    ),
  );

  // Exported field_instance: 'node-major-n_major_core_academics'.
  $field_instances['node-major-n_major_core_academics'] = array(
    'bundle' => 'major',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'hidden',
        'module' => 'text',
        'settings' => array(),
        'type' => 'text_default',
        'weight' => 19,
      ),
      'teaser' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'node',
    'field_name' => 'n_major_core_academics',
    'label' => 'Core Academics',
    'required' => 0,
    'settings' => array(
      'text_processing' => 0,
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'text',
      'settings' => array(
        'size' => 100,
      ),
      'type' => 'text_textfield',
      'weight' => 15,
    ),
  );

  // Exported field_instance: 'node-major-n_major_courses_lead'.
  $field_instances['node-major-n_major_courses_lead'] = array(
    'bundle' => 'major',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'above',
        'module' => 'text',
        'settings' => array(),
        'type' => 'text_default',
        'weight' => 26,
      ),
      'teaser' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'node',
    'field_name' => 'n_major_courses_lead',
    'label' => 'Courses Lead',
    'required' => 0,
    'settings' => array(
      'display_summary' => 0,
      'text_processing' => 1,
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'text',
      'settings' => array(
        'rows' => 4,
        'summary_rows' => 5,
      ),
      'type' => 'text_textarea_with_summary',
      'weight' => 22,
    ),
  );

  // Exported field_instance: 'node-major-n_major_custom'.
  $field_instances['node-major-n_major_custom'] = array(
    'bundle' => 'major',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => 'Optional field for major specific information that may not fit anywhere else. All content should be contained within a dcf-bleed div. ',
    'display' => array(
      'default' => array(
        'label' => 'above',
        'module' => 'text',
        'settings' => array(),
        'type' => 'text_default',
        'weight' => 28,
      ),
      'teaser' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'node',
    'field_name' => 'n_major_custom',
    'label' => 'Major Specific',
    'required' => 0,
    'settings' => array(
      'display_summary' => 0,
      'text_processing' => 1,
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'text',
      'settings' => array(
        'rows' => 20,
        'summary_rows' => 5,
      ),
      'type' => 'text_textarea_with_summary',
      'weight' => 14,
    ),
  );

  // Exported field_instance: 'node-major-n_major_hands_on'.
  $field_instances['node-major-n_major_hands_on'] = array(
    'bundle' => 'major',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'hidden',
        'module' => 'text',
        'settings' => array(),
        'type' => 'text_default',
        'weight' => 15,
      ),
      'teaser' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'node',
    'field_name' => 'n_major_hands_on',
    'label' => 'Hands On Learning',
    'required' => 0,
    'settings' => array(
      'text_processing' => 0,
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'text',
      'settings' => array(
        'size' => 100,
      ),
      'type' => 'text_textfield',
      'weight' => 11,
    ),
  );

  // Exported field_instance: 'node-major-n_major_hero_link'.
  $field_instances['node-major-n_major_hero_link'] = array(
    'bundle' => 'major',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => 'This link will be replicated in the footer area along with "Request More Information" and "Visit Campus" links',
    'display' => array(
      'default' => array(
        'label' => 'hidden',
        'module' => 'link',
        'settings' => array(),
        'type' => 'link_default',
        'weight' => 7,
      ),
      'teaser' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'node',
    'field_name' => 'n_major_hero_link',
    'label' => 'Apply Link',
    'required' => 0,
    'settings' => array(
      'attributes' => array(
        'class' => 'dcf-btn dcf-btn-primary',
        'configurable_title' => 0,
        'rel' => '',
        'target' => 'default',
        'title' => '',
      ),
      'display' => array(
        'url_cutoff' => 80,
      ),
      'enable_tokens' => 1,
      'rel_remove' => 'default',
      'title' => 'optional',
      'title_maxlength' => 128,
      'title_value' => '',
      'url' => 0,
      'user_register_form' => FALSE,
      'validate_url' => 1,
    ),
    'widget' => array(
      'active' => 0,
      'module' => 'link',
      'settings' => array(),
      'type' => 'link_field',
      'weight' => 6,
    ),
  );

  // Exported field_instance: 'node-major-n_major_infovisit_link'.
  $field_instances['node-major-n_major_infovisit_link'] = array(
    'bundle' => 'major',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => 'These links will appear in footer along with "Apply" link.',
    'display' => array(
      'default' => array(
        'label' => 'hidden',
        'module' => 'link',
        'settings' => array(),
        'type' => 'link_default',
        'weight' => 9,
      ),
      'teaser' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'node',
    'field_name' => 'n_major_infovisit_link',
    'label' => 'Info Visit Links',
    'required' => 0,
    'settings' => array(
      'attributes' => array(
        'class' => 'dcf-btn dcf-btn-primary',
        'configurable_title' => 0,
        'rel' => '',
        'target' => 'default',
        'title' => '',
      ),
      'display' => array(
        'url_cutoff' => 80,
      ),
      'enable_tokens' => 1,
      'rel_remove' => 'default',
      'title' => 'optional',
      'title_maxlength' => 128,
      'title_value' => '',
      'url' => 0,
      'user_register_form' => FALSE,
      'validate_url' => 1,
    ),
    'widget' => array(
      'active' => 0,
      'module' => 'link',
      'settings' => array(),
      'type' => 'link_field',
      'weight' => 8,
    ),
  );

  // Exported field_instance: 'node-major-n_major_lead_copy'.
  $field_instances['node-major-n_major_lead_copy'] = array(
    'bundle' => 'major',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'hidden',
        'module' => 'text',
        'settings' => array(),
        'type' => 'text_default',
        'weight' => 10,
      ),
      'teaser' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'node',
    'field_name' => 'n_major_lead_copy',
    'label' => 'Major Lead Copy',
    'required' => 0,
    'settings' => array(
      'display_summary' => 0,
      'text_processing' => 0,
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'text',
      'settings' => array(
        'rows' => 5,
        'summary_rows' => 5,
      ),
      'type' => 'text_textarea_with_summary',
      'weight' => 4,
    ),
  );

  // Exported field_instance: 'node-major-n_major_learning_outcomes'.
  $field_instances['node-major-n_major_learning_outcomes'] = array(
    'bundle' => 'major',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => 'Maximum of four points emphasizing Learning Outcomes with this major.',
    'display' => array(
      'default' => array(
        'label' => 'hidden',
        'module' => 'text',
        'settings' => array(),
        'type' => 'text_default',
        'weight' => 13,
      ),
      'teaser' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'node',
    'field_name' => 'n_major_learning_outcomes',
    'label' => 'Learning Outcomes',
    'required' => 0,
    'settings' => array(
      'text_processing' => 0,
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'text',
      'settings' => array(
        'size' => 100,
      ),
      'type' => 'text_textfield',
      'weight' => 8,
    ),
  );

  // Exported field_instance: 'node-major-n_major_make_difference'.
  $field_instances['node-major-n_major_make_difference'] = array(
    'bundle' => 'major',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'hidden',
        'module' => 'text',
        'settings' => array(),
        'type' => 'text_default',
        'weight' => 18,
      ),
      'teaser' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'node',
    'field_name' => 'n_major_make_difference',
    'label' => 'Make Difference',
    'required' => 0,
    'settings' => array(
      'text_processing' => 0,
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'text',
      'settings' => array(
        'size' => 100,
      ),
      'type' => 'text_textfield',
      'weight' => 14,
    ),
  );

  // Exported field_instance: 'node-major-n_major_mediahub_url'.
  $field_instances['node-major-n_major_mediahub_url'] = array(
    'bundle' => 'major',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => 'This video is what students will see first on your page. Make sure you select a video that is a good introduction to this specific major.

This should just be the URL to the mediahub file. Example: https://mediahub.unl.edu/media/17672',
    'display' => array(
      'default' => array(
        'label' => 'above',
        'module' => 'text',
        'settings' => array(),
        'type' => 'text_default',
        'weight' => 38,
      ),
      'teaser' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'node',
    'field_name' => 'n_major_mediahub_url',
    'label' => 'Mediahub URL',
    'required' => 0,
    'settings' => array(
      'text_processing' => 0,
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'text',
      'settings' => array(
        'size' => 60,
      ),
      'type' => 'text_textfield',
      'weight' => 11,
    ),
  );

  // Exported field_instance: 'node-major-n_major_notable_courses'.
  $field_instances['node-major-n_major_notable_courses'] = array(
    'bundle' => 'major',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => 'For highlighting most interesting courses for the major. Maximum 6.',
    'display' => array(
      'default' => array(
        'label' => 'above',
        'module' => 'paragraphs',
        'settings' => array(
          'view_mode' => 'full',
        ),
        'type' => 'paragraphs_view',
        'weight' => 27,
      ),
      'teaser' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'node',
    'field_name' => 'n_major_notable_courses',
    'label' => 'Notable Courses',
    'required' => 0,
    'settings' => array(
      'add_mode' => 'select',
      'allowed_bundles' => array(
        'contact' => -1,
        'mindset_teaser' => -1,
        'notable_course' => 'notable_course',
        'point_of_pride' => -1,
        'section_featured' => -1,
        'section_secondary' => -1,
        'support_section' => -1,
        'test_paragraph_bundle_eric_' => -1,
      ),
      'bundle_weights' => array(
        'contact' => 2,
        'mindset_teaser' => 3,
        'notable_course' => 4,
        'point_of_pride' => 5,
        'section_featured' => 6,
        'section_secondary' => 7,
        'support_section' => 8,
        'test_paragraph_bundle_eric_' => 9,
      ),
      'default_edit_mode' => 'open',
      'default_edit_mode_override' => 1,
      'title' => 'Paragraph',
      'title_multiple' => 'Paragraphs',
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 0,
      'module' => 'paragraphs',
      'settings' => array(),
      'type' => 'paragraphs_embed',
      'weight' => 23,
    ),
  );

  // Exported field_instance: 'node-major-n_major_point_pride'.
  $field_instances['node-major-n_major_point_pride'] = array(
    'bundle' => 'major',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => 'Icon with statement and supporting text. Maximum of 4 allowed.  ',
    'display' => array(
      'default' => array(
        'label' => 'hidden',
        'module' => 'paragraphs',
        'settings' => array(
          'view_mode' => 'full',
        ),
        'type' => 'paragraphs_view',
        'weight' => 23,
      ),
      'teaser' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'node',
    'field_name' => 'n_major_point_pride',
    'label' => 'Point of Pride',
    'required' => 0,
    'settings' => array(
      'add_mode' => 'select',
      'allowed_bundles' => array(
        'contact' => -1,
        'mindset_teaser' => -1,
        'point_of_pride' => 'point_of_pride',
        'section_featured' => -1,
        'section_secondary' => -1,
        'support_section' => -1,
        'test_paragraph_bundle_eric_' => -1,
      ),
      'bundle_weights' => array(
        'contact' => 2,
        'mindset_teaser' => 3,
        'point_of_pride' => 4,
        'section_featured' => 5,
        'section_secondary' => 6,
        'support_section' => 7,
        'test_paragraph_bundle_eric_' => 8,
      ),
      'default_edit_mode' => 'open',
      'default_edit_mode_override' => 1,
      'title' => 'Paragraph',
      'title_multiple' => 'Paragraphs',
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 0,
      'module' => 'paragraphs',
      'settings' => array(),
      'type' => 'paragraphs_embed',
      'weight' => 7,
    ),
  );

  // Exported field_instance: 'node-major-n_major_res_aca_alt_title'.
  $field_instances['node-major-n_major_res_aca_alt_title'] = array(
    'bundle' => 'major',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => 'The title "Research and Academic Opportunities" will be displayed for this section, unless this title is filled in. Only change it if your major is highlighting different student opportunities.',
    'display' => array(
      'default' => array(
        'label' => 'above',
        'module' => 'text',
        'settings' => array(),
        'type' => 'text_default',
        'weight' => 41,
      ),
      'teaser' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'node',
    'field_name' => 'n_major_res_aca_alt_title',
    'label' => 'Research Academic Alt Title',
    'required' => 0,
    'settings' => array(
      'text_processing' => 0,
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'text',
      'settings' => array(
        'size' => 60,
      ),
      'type' => 'text_textfield',
      'weight' => 15,
    ),
  );

  // Exported field_instance: 'node-major-n_major_research_academic_link'.
  $field_instances['node-major-n_major_research_academic_link'] = array(
    'bundle' => 'major',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'above',
        'module' => 'link',
        'settings' => array(),
        'type' => 'link_default',
        'weight' => 32,
      ),
      'teaser' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'node',
    'field_name' => 'n_major_research_academic_link',
    'label' => 'Research Academic Link',
    'required' => 0,
    'settings' => array(
      'attributes' => array(
        'class' => 'dcf-btn dcf-btn-primary',
        'configurable_title' => 0,
        'rel' => '',
        'target' => 'default',
        'title' => '',
      ),
      'display' => array(
        'url_cutoff' => 80,
      ),
      'enable_tokens' => 1,
      'rel_remove' => 'default',
      'title' => 'optional',
      'title_maxlength' => 128,
      'title_value' => '',
      'url' => 0,
      'user_register_form' => FALSE,
      'validate_url' => 1,
    ),
    'widget' => array(
      'active' => 0,
      'module' => 'link',
      'settings' => array(),
      'type' => 'link_field',
      'weight' => 17,
    ),
  );

  // Exported field_instance: 'node-major-n_major_research_grad_prep'.
  $field_instances['node-major-n_major_research_grad_prep'] = array(
    'bundle' => 'major',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'hidden',
        'module' => 'text',
        'settings' => array(),
        'type' => 'text_default',
        'weight' => 20,
      ),
      'teaser' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'node',
    'field_name' => 'n_major_research_grad_prep',
    'label' => 'Research Grad Prep',
    'required' => 0,
    'settings' => array(
      'text_processing' => 0,
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'text',
      'settings' => array(
        'size' => 100,
      ),
      'type' => 'text_textfield',
      'weight' => 16,
    ),
  );

  // Exported field_instance: 'node-major-n_major_social_connections'.
  $field_instances['node-major-n_major_social_connections'] = array(
    'bundle' => 'major',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'hidden',
        'module' => 'text',
        'settings' => array(),
        'type' => 'text_default',
        'weight' => 17,
      ),
      'teaser' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'node',
    'field_name' => 'n_major_social_connections',
    'label' => 'Social Connections',
    'required' => 0,
    'settings' => array(
      'text_processing' => 0,
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'text',
      'settings' => array(
        'size' => 100,
      ),
      'type' => 'text_textfield',
      'weight' => 13,
    ),
  );

  // Exported field_instance: 'node-major-n_major_student_org_text_1'.
  $field_instances['node-major-n_major_student_org_text_1'] = array(
    'bundle' => 'major',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'above',
        'module' => 'text',
        'settings' => array(),
        'type' => 'text_default',
        'weight' => 36,
      ),
      'teaser' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'node',
    'field_name' => 'n_major_student_org_text_1',
    'label' => 'Student Org Text 1',
    'required' => 0,
    'settings' => array(
      'text_processing' => 0,
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'text',
      'settings' => array(
        'size' => 100,
      ),
      'type' => 'text_textfield',
      'weight' => 21,
    ),
  );

  // Exported field_instance: 'node-major-n_major_student_org_text_2'.
  $field_instances['node-major-n_major_student_org_text_2'] = array(
    'bundle' => 'major',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'above',
        'module' => 'text',
        'settings' => array(),
        'type' => 'text_default',
        'weight' => 37,
      ),
      'teaser' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'node',
    'field_name' => 'n_major_student_org_text_2',
    'label' => 'Student Org Text 2',
    'required' => 0,
    'settings' => array(
      'text_processing' => 0,
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'text',
      'settings' => array(
        'size' => 100,
      ),
      'type' => 'text_textfield',
      'weight' => 23,
    ),
  );

  // Exported field_instance: 'node-major-n_major_student_org_title_1'.
  $field_instances['node-major-n_major_student_org_title_1'] = array(
    'bundle' => 'major',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'above',
        'module' => 'text',
        'settings' => array(),
        'type' => 'text_default',
        'weight' => 34,
      ),
      'teaser' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'node',
    'field_name' => 'n_major_student_org_title_1',
    'label' => 'Student Org Title 1',
    'required' => 0,
    'settings' => array(
      'text_processing' => 0,
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'text',
      'settings' => array(
        'size' => 60,
      ),
      'type' => 'text_textfield',
      'weight' => 20,
    ),
  );

  // Exported field_instance: 'node-major-n_major_student_org_title_2'.
  $field_instances['node-major-n_major_student_org_title_2'] = array(
    'bundle' => 'major',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'above',
        'module' => 'text',
        'settings' => array(),
        'type' => 'text_default',
        'weight' => 35,
      ),
      'teaser' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'node',
    'field_name' => 'n_major_student_org_title_2',
    'label' => 'Student Org Title 2',
    'required' => 0,
    'settings' => array(
      'text_processing' => 0,
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'text',
      'settings' => array(
        'size' => 60,
      ),
      'type' => 'text_textfield',
      'weight' => 22,
    ),
  );

  // Exported field_instance: 'node-major-n_major_student_orgs_link'.
  $field_instances['node-major-n_major_student_orgs_link'] = array(
    'bundle' => 'major',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'above',
        'module' => 'link',
        'settings' => array(),
        'type' => 'link_default',
        'weight' => 33,
      ),
      'teaser' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'node',
    'field_name' => 'n_major_student_orgs_link',
    'label' => 'Student Orgs Link',
    'required' => 0,
    'settings' => array(
      'attributes' => array(
        'class' => 'dcf-btn dcf-btn-primary',
        'configurable_title' => 0,
        'rel' => '',
        'target' => 'default',
        'title' => '',
      ),
      'display' => array(
        'url_cutoff' => 80,
      ),
      'enable_tokens' => 1,
      'rel_remove' => 'default',
      'title' => 'optional',
      'title_maxlength' => 128,
      'title_value' => '',
      'url' => 0,
      'user_register_form' => FALSE,
      'validate_url' => 1,
    ),
    'widget' => array(
      'active' => 0,
      'module' => 'link',
      'settings' => array(),
      'type' => 'link_field',
      'weight' => 24,
    ),
  );

  // Exported field_instance: 'node-major-n_major_study_abroad'.
  $field_instances['node-major-n_major_study_abroad'] = array(
    'bundle' => 'major',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'above',
        'module' => 'paragraphs',
        'settings' => array(
          'view_mode' => 'full',
        ),
        'type' => 'paragraphs_view',
        'weight' => 30,
      ),
      'teaser' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'node',
    'field_name' => 'n_major_study_abroad',
    'label' => 'Study Abroad',
    'required' => 0,
    'settings' => array(
      'add_mode' => 'select',
      'allowed_bundles' => array(
        'career_path' => -1,
        'contact' => -1,
        'mindset_teaser' => -1,
        'notable_course' => -1,
        'point_of_pride' => -1,
        'section_featured' => -1,
        'section_secondary' => -1,
        'study_abroad' => 'study_abroad',
        'support_section' => -1,
        'test_paragraph_bundle_eric_' => -1,
      ),
      'bundle_weights' => array(
        'career_path' => 2,
        'contact' => 3,
        'mindset_teaser' => 4,
        'notable_course' => 5,
        'point_of_pride' => 6,
        'section_featured' => 7,
        'section_secondary' => 8,
        'study_abroad' => 9,
        'support_section' => 10,
        'test_paragraph_bundle_eric_' => 11,
      ),
      'default_edit_mode' => 'open',
      'default_edit_mode_override' => 1,
      'title' => 'Paragraph',
      'title_multiple' => 'Paragraphs',
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 0,
      'module' => 'paragraphs',
      'settings' => array(),
      'type' => 'paragraphs_embed',
      'weight' => 15,
    ),
  );

  // Exported field_instance: 'node-major-n_major_study_abroad_link'.
  $field_instances['node-major-n_major_study_abroad_link'] = array(
    'bundle' => 'major',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'above',
        'module' => 'link',
        'settings' => array(),
        'type' => 'link_default',
        'weight' => 31,
      ),
      'teaser' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'node',
    'field_name' => 'n_major_study_abroad_link',
    'label' => 'Study Abroad Link',
    'required' => 0,
    'settings' => array(
      'attributes' => array(
        'class' => 'dcf-btn dcf-btn-primary',
        'configurable_title' => 0,
        'rel' => '',
        'target' => 'default',
        'title' => '',
      ),
      'display' => array(
        'url_cutoff' => 80,
      ),
      'enable_tokens' => 1,
      'rel_remove' => 'default',
      'title' => 'optional',
      'title_maxlength' => 128,
      'title_value' => '',
      'url' => 0,
      'user_register_form' => FALSE,
      'validate_url' => 1,
    ),
    'widget' => array(
      'active' => 0,
      'module' => 'link',
      'settings' => array(),
      'type' => 'link_field',
      'weight' => 16,
    ),
  );

  // Exported field_instance: 'node-major-n_major_video_text'.
  $field_instances['node-major-n_major_video_text'] = array(
    'bundle' => 'major',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'above',
        'module' => 'text',
        'settings' => array(),
        'type' => 'text_default',
        'weight' => 40,
      ),
      'teaser' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'node',
    'field_name' => 'n_major_video_text',
    'label' => 'Video Text',
    'required' => 0,
    'settings' => array(
      'text_processing' => 0,
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'text',
      'settings' => array(
        'size' => 100,
      ),
      'type' => 'text_textfield',
      'weight' => 13,
    ),
  );

  // Exported field_instance: 'node-major-n_major_video_title'.
  $field_instances['node-major-n_major_video_title'] = array(
    'bundle' => 'major',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'above',
        'module' => 'text',
        'settings' => array(),
        'type' => 'text_default',
        'weight' => 39,
      ),
      'teaser' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'node',
    'field_name' => 'n_major_video_title',
    'label' => 'Video title',
    'required' => 0,
    'settings' => array(
      'text_processing' => 0,
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'text',
      'settings' => array(
        'size' => 60,
      ),
      'type' => 'text_textfield',
      'weight' => 12,
    ),
  );

  // Exported field_instance: 'node-major-n_major_what_youll_learn'.
  $field_instances['node-major-n_major_what_youll_learn'] = array(
    'bundle' => 'major',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => 'Maximum of four points emphasizing What Students Will Learn with this major.',
    'display' => array(
      'default' => array(
        'label' => 'hidden',
        'module' => 'text',
        'settings' => array(),
        'type' => 'text_default',
        'weight' => 12,
      ),
      'teaser' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'node',
    'field_name' => 'n_major_what_youll_learn',
    'label' => 'What You\'ll Learn',
    'required' => 0,
    'settings' => array(
      'text_processing' => 0,
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'text',
      'settings' => array(
        'size' => 100,
      ),
      'type' => 'text_textfield',
      'weight' => 7,
    ),
  );

  // Exported field_instance: 'node-major-s_n_contacts'.
  $field_instances['node-major-s_n_contacts'] = array(
    'bundle' => 'major',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'above',
        'module' => 'paragraphs',
        'settings' => array(
          'view_mode' => 'full',
        ),
        'type' => 'paragraphs_view',
        'weight' => 24,
      ),
      'teaser' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'node',
    'field_name' => 's_n_contacts',
    'label' => 'Contacts',
    'required' => 0,
    'settings' => array(
      'add_mode' => 'select',
      'allowed_bundles' => array(
        'contact' => 'contact',
        'mindset_teaser' => -1,
        'point_of_pride' => -1,
        'section_featured' => -1,
        'section_secondary' => -1,
        'support_section' => -1,
        'test_paragraph_bundle_eric_' => -1,
      ),
      'bundle_weights' => array(
        'contact' => 2,
        'mindset_teaser' => 3,
        'point_of_pride' => 4,
        'section_featured' => 5,
        'section_secondary' => 6,
        'support_section' => 7,
        'test_paragraph_bundle_eric_' => 8,
      ),
      'default_edit_mode' => 'open',
      'default_edit_mode_override' => 1,
      'title' => 'Paragraph',
      'title_multiple' => 'Paragraphs',
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 0,
      'module' => 'paragraphs',
      'settings' => array(),
      'type' => 'paragraphs_embed',
      'weight' => 16,
    ),
  );

  // Exported field_instance: 'node-major-s_p_supsec_secondary'.
  $field_instances['node-major-s_p_supsec_secondary'] = array(
    'bundle' => 'major',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'above',
        'module' => 'paragraphs',
        'settings' => array(
          'view_mode' => 'full',
        ),
        'type' => 'paragraphs_view',
        'weight' => 25,
      ),
      'teaser' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'node',
    'field_name' => 's_p_supsec_secondary',
    'label' => 'Research Academic Opportunities',
    'required' => 0,
    'settings' => array(
      'add_mode' => 'select',
      'allowed_bundles' => array(
        'contact' => -1,
        'mindset_teaser' => -1,
        'point_of_pride' => -1,
        'section_featured' => -1,
        'section_secondary' => 'section_secondary',
        'support_section' => -1,
        'test_paragraph_bundle_eric_' => -1,
      ),
      'bundle_weights' => array(
        'contact' => 2,
        'mindset_teaser' => 3,
        'point_of_pride' => 4,
        'section_featured' => 5,
        'section_secondary' => 6,
        'support_section' => 7,
        'test_paragraph_bundle_eric_' => 8,
      ),
      'default_edit_mode' => 'open',
      'default_edit_mode_override' => 1,
      'title' => 'Paragraph',
      'title_multiple' => 'Paragraphs',
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 0,
      'module' => 'paragraphs',
      'settings' => array(),
      'type' => 'paragraphs_embed',
      'weight' => 16,
    ),
  );

  // Exported field_instance: 'node-mindset-n_mindset_hero_text'.
  $field_instances['node-mindset-n_mindset_hero_text'] = array(
    'bundle' => 'mindset',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'above',
        'module' => 'text',
        'settings' => array(),
        'type' => 'text_default',
        'weight' => 13,
      ),
      'teaser' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'node',
    'field_name' => 'n_mindset_hero_text',
    'label' => 'Mindsets Hero Text',
    'required' => 0,
    'settings' => array(
      'text_processing' => 0,
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'text',
      'settings' => array(
        'rows' => 2,
      ),
      'type' => 'text_textarea',
      'weight' => 5,
    ),
  );

  // Exported field_instance: 'node-mindset-n_mindset_hero_title'.
  $field_instances['node-mindset-n_mindset_hero_title'] = array(
    'bundle' => 'mindset',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'hidden',
        'module' => 'text',
        'settings' => array(),
        'type' => 'text_default',
        'weight' => 7,
      ),
      'teaser' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'node',
    'field_name' => 'n_mindset_hero_title',
    'label' => 'Mindsets Hero Title',
    'required' => 0,
    'settings' => array(
      'text_processing' => 0,
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'text',
      'settings' => array(
        'size' => 60,
      ),
      'type' => 'text_textfield',
      'weight' => 4,
    ),
  );

  // Exported field_instance: 'node-mindset-n_mindset_support_sections'.
  $field_instances['node-mindset-n_mindset_support_sections'] = array(
    'bundle' => 'mindset',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'above',
        'module' => 'paragraphs',
        'settings' => array(
          'view_mode' => 'full',
        ),
        'type' => 'paragraphs_view',
        'weight' => 11,
      ),
      'teaser' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'node',
    'field_name' => 'n_mindset_support_sections',
    'label' => 'Support Sections',
    'required' => 0,
    'settings' => array(
      'add_mode' => 'select',
      'allowed_bundles' => array(
        'contact' => -1,
        'major' => -1,
        'mindset_teaser' => -1,
        'section_featured' => -1,
        'section_secondary' => -1,
        'support_section' => 'support_section',
        'test_paragraph_bundle_eric_' => -1,
      ),
      'bundle_weights' => array(
        'contact' => 2,
        'major' => 3,
        'mindset_teaser' => 4,
        'section_featured' => 5,
        'section_secondary' => 6,
        'support_section' => 7,
        'test_paragraph_bundle_eric_' => 8,
      ),
      'default_edit_mode' => 'open',
      'default_edit_mode_override' => 1,
      'title' => 'Paragraph',
      'title_multiple' => 'Paragraphs',
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 0,
      'module' => 'paragraphs',
      'settings' => array(),
      'type' => 'paragraphs_embed',
      'weight' => 6,
    ),
  );

  // Exported field_instance: 'node-mindset-s_n_contacts'.
  $field_instances['node-mindset-s_n_contacts'] = array(
    'bundle' => 'mindset',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'above',
        'module' => 'paragraphs',
        'settings' => array(
          'view_mode' => 'full',
        ),
        'type' => 'paragraphs_view',
        'weight' => 12,
      ),
      'teaser' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'node',
    'field_name' => 's_n_contacts',
    'label' => 'Contacts',
    'required' => 0,
    'settings' => array(
      'add_mode' => 'select',
      'allowed_bundles' => array(
        'contact' => 'contact',
        'major' => -1,
        'mindset_teaser' => -1,
        'section_featured' => -1,
        'section_secondary' => -1,
        'support_section' => -1,
        'test_paragraph_bundle_eric_' => -1,
      ),
      'bundle_weights' => array(
        'contact' => 2,
        'major' => 3,
        'mindset_teaser' => 4,
        'section_featured' => 5,
        'section_secondary' => 6,
        'support_section' => 7,
        'test_paragraph_bundle_eric_' => 8,
      ),
      'default_edit_mode' => 'open',
      'default_edit_mode_override' => 1,
      'title' => 'Paragraph',
      'title_multiple' => 'Paragraphs',
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 0,
      'module' => 'paragraphs',
      'settings' => array(),
      'type' => 'paragraphs_embed',
      'weight' => 7,
    ),
  );

  // Exported field_instance: 'node-mindset-s_n_hero_image'.
  $field_instances['node-mindset-s_n_hero_image'] = array(
    'bundle' => 'mindset',
    'deleted' => 0,
    'description' => 'This image should be 16x9 aspect ratio and optimized for the web, no larger than 2400 pixels wide.',
    'display' => array(
      'default' => array(
        'label' => 'hidden',
        'module' => 'image',
        'settings' => array(
          'class' => '',
          'image_link' => '',
          'image_style' => 'mindsets_hero',
        ),
        'type' => 'image',
        'weight' => 6,
      ),
      'teaser' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'node',
    'field_name' => 's_n_hero_image',
    'label' => 'Hero Image',
    'required' => 0,
    'settings' => array(
      'alt_field' => 1,
      'default_image' => 0,
      'file_directory' => 'images/mindset/hero',
      'file_extensions' => 'jpg jpeg',
      'max_filesize' => '',
      'max_resolution' => '',
      'min_resolution' => '',
      'title_field' => 0,
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'image',
      'settings' => array(
        'preview_image_style' => 'thumbnail',
        'progress_indicator' => 'throbber',
      ),
      'type' => 'image_image',
      'weight' => 3,
    ),
  );

  // Exported field_instance: 'node-mindset-s_n_hero_link'.
  $field_instances['node-mindset-s_n_hero_link'] = array(
    'bundle' => 'mindset',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'above',
        'module' => 'link',
        'settings' => array(),
        'type' => 'link_default',
        'weight' => 14,
      ),
      'teaser' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'node',
    'field_name' => 's_n_hero_link',
    'label' => 'Admission Links',
    'required' => 0,
    'settings' => array(
      'attributes' => array(
        'class' => 'dcf-btn dcf-btn-primary',
        'configurable_title' => 0,
        'rel' => '',
        'target' => 'default',
        'title' => '',
      ),
      'display' => array(
        'url_cutoff' => 80,
      ),
      'enable_tokens' => 1,
      'rel_remove' => 'default',
      'title' => 'optional',
      'title_maxlength' => 128,
      'title_value' => '',
      'url' => 0,
      'user_register_form' => FALSE,
      'validate_url' => 1,
    ),
    'widget' => array(
      'active' => 0,
      'module' => 'link',
      'settings' => array(),
      'type' => 'link_field',
      'weight' => 5,
    ),
  );

  // Exported field_instance: 'paragraphs_item-career_path-p_n_career_examples'.
  $field_instances['paragraphs_item-career_path-p_n_career_examples'] = array(
    'bundle' => 'career_path',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => 'Maximum of 5 allowed per listing.',
    'display' => array(
      'default' => array(
        'label' => 'hidden',
        'module' => 'text',
        'settings' => array(),
        'type' => 'text_default',
        'weight' => 1,
      ),
      'paragraphs_editor_preview' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'paragraphs_item',
    'field_name' => 'p_n_career_examples',
    'label' => 'Career Examples',
    'required' => 0,
    'settings' => array(
      'text_processing' => 0,
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'text',
      'settings' => array(
        'size' => 60,
      ),
      'type' => 'text_textfield',
      'weight' => 2,
    ),
  );

  // Exported field_instance: 'paragraphs_item-career_path-p_n_career_title'.
  $field_instances['paragraphs_item-career_path-p_n_career_title'] = array(
    'bundle' => 'career_path',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'hidden',
        'module' => 'text',
        'settings' => array(),
        'type' => 'text_default',
        'weight' => 0,
      ),
      'paragraphs_editor_preview' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'paragraphs_item',
    'field_name' => 'p_n_career_title',
    'label' => 'Career Title',
    'required' => 0,
    'settings' => array(
      'text_processing' => 0,
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'text',
      'settings' => array(
        'size' => 60,
      ),
      'type' => 'text_textfield',
      'weight' => 1,
    ),
  );

  // Exported field_instance: 'paragraphs_item-contact-p_n_contact_email'.
  $field_instances['paragraphs_item-contact-p_n_contact_email'] = array(
    'bundle' => 'contact',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'above',
        'module' => 'text',
        'settings' => array(),
        'type' => 'text_default',
        'weight' => 4,
      ),
      'paragraphs_editor_preview' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'paragraphs_item',
    'field_name' => 'p_n_contact_email',
    'label' => 'Contact Email',
    'required' => 0,
    'settings' => array(
      'text_processing' => 0,
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'text',
      'settings' => array(
        'size' => 60,
      ),
      'type' => 'text_textfield',
      'weight' => 5,
    ),
  );

  // Exported field_instance: 'paragraphs_item-contact-p_n_contact_image'.
  $field_instances['paragraphs_item-contact-p_n_contact_image'] = array(
    'bundle' => 'contact',
    'deleted' => 0,
    'description' => 'This image should be 1x1 aspect ratio and 800x800 maximum, optimized for the web.',
    'display' => array(
      'default' => array(
        'label' => 'above',
        'module' => 'image',
        'settings' => array(
          'class' => 'dcf-ratio-child dcf-obj-fit-cover dcf-circle',
          'image_link' => '',
          'image_style' => 'contact_card',
        ),
        'type' => 'image',
        'weight' => 0,
      ),
      'paragraphs_editor_preview' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'paragraphs_item',
    'field_name' => 'p_n_contact_image',
    'label' => 'Contact Image',
    'required' => 0,
    'settings' => array(
      'alt_field' => 1,
      'default_image' => 0,
      'file_directory' => 'images/mindset/contact',
      'file_extensions' => 'jpg jpeg',
      'max_filesize' => '',
      'max_resolution' => '',
      'min_resolution' => '',
      'title_field' => 0,
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'image',
      'settings' => array(
        'preview_image_style' => 'thumbnail',
        'progress_indicator' => 'throbber',
      ),
      'type' => 'image_image',
      'weight' => 1,
    ),
  );

  // Exported field_instance: 'paragraphs_item-contact-p_n_contact_name'.
  $field_instances['paragraphs_item-contact-p_n_contact_name'] = array(
    'bundle' => 'contact',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'above',
        'module' => 'text',
        'settings' => array(),
        'type' => 'text_default',
        'weight' => 1,
      ),
      'paragraphs_editor_preview' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'paragraphs_item',
    'field_name' => 'p_n_contact_name',
    'label' => 'Contact Name',
    'required' => 0,
    'settings' => array(
      'text_processing' => 0,
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'text',
      'settings' => array(
        'size' => 60,
      ),
      'type' => 'text_textfield',
      'weight' => 2,
    ),
  );

  // Exported field_instance: 'paragraphs_item-contact-p_n_contact_phone'.
  $field_instances['paragraphs_item-contact-p_n_contact_phone'] = array(
    'bundle' => 'contact',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => 'Enter full number with area code and dashes, without 1 in front.
Example: 402-472-1111',
    'display' => array(
      'default' => array(
        'label' => 'above',
        'module' => 'text',
        'settings' => array(),
        'type' => 'text_default',
        'weight' => 3,
      ),
      'paragraphs_editor_preview' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'paragraphs_item',
    'field_name' => 'p_n_contact_phone',
    'label' => 'Contact Phone',
    'required' => 0,
    'settings' => array(
      'text_processing' => 0,
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'text',
      'settings' => array(
        'size' => 60,
      ),
      'type' => 'text_textfield',
      'weight' => 4,
    ),
  );

  // Exported field_instance: 'paragraphs_item-contact-p_n_contact_title'.
  $field_instances['paragraphs_item-contact-p_n_contact_title'] = array(
    'bundle' => 'contact',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'above',
        'module' => 'text',
        'settings' => array(),
        'type' => 'text_default',
        'weight' => 2,
      ),
      'paragraphs_editor_preview' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'paragraphs_item',
    'field_name' => 'p_n_contact_title',
    'label' => 'Contact Title',
    'required' => 0,
    'settings' => array(
      'text_processing' => 0,
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'text',
      'settings' => array(
        'size' => 60,
      ),
      'type' => 'text_textfield',
      'weight' => 3,
    ),
  );

  // Exported field_instance:
  // 'paragraphs_item-mindset_teaser-p_n_msteaser_image'.
  $field_instances['paragraphs_item-mindset_teaser-p_n_msteaser_image'] = array(
    'bundle' => 'mindset_teaser',
    'deleted' => 0,
    'description' => 'This image should be 4x3 aspect ratio and optimized for the web, no larger than 1600 pixels wide.',
    'display' => array(
      'default' => array(
        'label' => 'hidden',
        'module' => 'image',
        'settings' => array(
          'class' => 'dcf-ratio-child dcf-obj-fit-cover dcf-rounded-top dcf-lazy-loaded',
          'image_link' => '',
          'image_style' => 'primary_support_photo',
        ),
        'type' => 'image',
        'weight' => 0,
      ),
      'paragraphs_editor_preview' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'paragraphs_item',
    'field_name' => 'p_n_msteaser_image',
    'label' => 'MS Image Teaser',
    'required' => 0,
    'settings' => array(
      'alt_field' => 1,
      'default_image' => 0,
      'file_directory' => 'images/mindset/admission',
      'file_extensions' => 'jpg jpeg',
      'max_filesize' => '',
      'max_resolution' => '',
      'min_resolution' => '',
      'title_field' => 0,
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'image',
      'settings' => array(
        'preview_image_style' => 'thumbnail',
        'progress_indicator' => 'throbber',
      ),
      'type' => 'image_image',
      'weight' => 1,
    ),
  );

  // Exported field_instance:
  // 'paragraphs_item-mindset_teaser-p_n_msteaser_link'.
  $field_instances['paragraphs_item-mindset_teaser-p_n_msteaser_link'] = array(
    'bundle' => 'mindset_teaser',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'hidden',
        'module' => 'link',
        'settings' => array(),
        'type' => 'link_default',
        'weight' => 3,
      ),
      'paragraphs_editor_preview' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'paragraphs_item',
    'field_name' => 'p_n_msteaser_link',
    'label' => 'MS Link Teaser',
    'required' => 0,
    'settings' => array(
      'attributes' => array(
        'class' => 'dcf-btn dcf-btn-primary dcf-card-link',
        'configurable_title' => 0,
        'rel' => '',
        'target' => 'default',
        'title' => '',
      ),
      'display' => array(
        'url_cutoff' => 80,
      ),
      'enable_tokens' => 1,
      'rel_remove' => 'default',
      'title' => 'required',
      'title_maxlength' => 128,
      'title_value' => '',
      'url' => 0,
      'user_register_form' => FALSE,
      'validate_url' => 1,
    ),
    'widget' => array(
      'active' => 0,
      'module' => 'link',
      'settings' => array(),
      'type' => 'link_field',
      'weight' => 4,
    ),
  );

  // Exported field_instance:
  // 'paragraphs_item-mindset_teaser-p_n_msteaser_text'.
  $field_instances['paragraphs_item-mindset_teaser-p_n_msteaser_text'] = array(
    'bundle' => 'mindset_teaser',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'hidden',
        'module' => 'text',
        'settings' => array(),
        'type' => 'text_default',
        'weight' => 2,
      ),
      'paragraphs_editor_preview' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'paragraphs_item',
    'field_name' => 'p_n_msteaser_text',
    'label' => 'MS Text Teaser',
    'required' => 0,
    'settings' => array(
      'text_processing' => 0,
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'text',
      'settings' => array(
        'size' => 60,
      ),
      'type' => 'text_textfield',
      'weight' => 3,
    ),
  );

  // Exported field_instance:
  // 'paragraphs_item-mindset_teaser-p_n_msteaser_title'.
  $field_instances['paragraphs_item-mindset_teaser-p_n_msteaser_title'] = array(
    'bundle' => 'mindset_teaser',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'hidden',
        'module' => 'text',
        'settings' => array(),
        'type' => 'text_default',
        'weight' => 1,
      ),
      'paragraphs_editor_preview' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'paragraphs_item',
    'field_name' => 'p_n_msteaser_title',
    'label' => 'MS Title Teaser',
    'required' => 0,
    'settings' => array(
      'text_processing' => 0,
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'text',
      'settings' => array(
        'size' => 60,
      ),
      'type' => 'text_textfield',
      'weight' => 2,
    ),
  );

  // Exported field_instance: 'paragraphs_item-notable_course-p_n_course_desc'.
  $field_instances['paragraphs_item-notable_course-p_n_course_desc'] = array(
    'bundle' => 'notable_course',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'hidden',
        'module' => 'text',
        'settings' => array(),
        'type' => 'text_default',
        'weight' => 1,
      ),
      'paragraphs_editor_preview' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'paragraphs_item',
    'field_name' => 'p_n_course_desc',
    'label' => 'Course Desc',
    'required' => 0,
    'settings' => array(
      'text_processing' => 0,
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'text',
      'settings' => array(
        'size' => 100,
      ),
      'type' => 'text_textfield',
      'weight' => 2,
    ),
  );

  // Exported field_instance: 'paragraphs_item-notable_course-p_n_course_title'.
  $field_instances['paragraphs_item-notable_course-p_n_course_title'] = array(
    'bundle' => 'notable_course',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'hidden',
        'module' => 'text',
        'settings' => array(),
        'type' => 'text_default',
        'weight' => 0,
      ),
      'paragraphs_editor_preview' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'paragraphs_item',
    'field_name' => 'p_n_course_title',
    'label' => 'Course Title',
    'required' => 0,
    'settings' => array(
      'text_processing' => 0,
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'text',
      'settings' => array(
        'size' => 60,
      ),
      'type' => 'text_textfield',
      'weight' => 1,
    ),
  );

  // Exported field_instance: 'paragraphs_item-point_of_pride-p_n_pop_icon'.
  $field_instances['paragraphs_item-point_of_pride-p_n_pop_icon'] = array(
    'bundle' => 'point_of_pride',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => 'Text determines which icon shows up. All lowercase, no spaces before or after.<br>
<b>money</b> - scholarships or financial aid related, lowest debt, etc<br>
<b>award</b> - recognition given by outside entities, ratings, etc<br>
<b>grad</b> - graduate employment levels, etc<br>
<b>check</b> - generic positive to emphasize<br>
<b>support</b> - faculty/student ratio, other support programs<br>
<b>column</b> - generic program positive to emphasize<br>',
    'display' => array(
      'default' => array(
        'label' => 'hidden',
        'module' => 'text',
        'settings' => array(),
        'type' => 'text_default',
        'weight' => 0,
      ),
      'paragraphs_editor_preview' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'paragraphs_item',
    'field_name' => 'p_n_pop_icon',
    'label' => 'Icon',
    'required' => 0,
    'settings' => array(
      'text_processing' => 0,
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'text',
      'settings' => array(
        'size' => 60,
      ),
      'type' => 'text_textfield',
      'weight' => 1,
    ),
  );

  // Exported field_instance:
  // 'paragraphs_item-point_of_pride-p_n_pop_statement'.
  $field_instances['paragraphs_item-point_of_pride-p_n_pop_statement'] = array(
    'bundle' => 'point_of_pride',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'hidden',
        'module' => 'text',
        'settings' => array(),
        'type' => 'text_default',
        'weight' => 1,
      ),
      'paragraphs_editor_preview' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'paragraphs_item',
    'field_name' => 'p_n_pop_statement',
    'label' => 'Statement',
    'required' => 0,
    'settings' => array(
      'text_processing' => 0,
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'text',
      'settings' => array(
        'size' => 60,
      ),
      'type' => 'text_textfield',
      'weight' => 2,
    ),
  );

  // Exported field_instance: 'paragraphs_item-point_of_pride-p_n_pop_support'.
  $field_instances['paragraphs_item-point_of_pride-p_n_pop_support'] = array(
    'bundle' => 'point_of_pride',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'hidden',
        'module' => 'text',
        'settings' => array(),
        'type' => 'text_default',
        'weight' => 2,
      ),
      'paragraphs_editor_preview' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'paragraphs_item',
    'field_name' => 'p_n_pop_support',
    'label' => 'Support',
    'required' => 0,
    'settings' => array(
      'text_processing' => 0,
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'text',
      'settings' => array(
        'size' => 60,
      ),
      'type' => 'text_textfield',
      'weight' => 3,
    ),
  );

  // Exported field_instance:
  // 'paragraphs_item-section_featured-p_n_secfeat_copy'.
  $field_instances['paragraphs_item-section_featured-p_n_secfeat_copy'] = array(
    'bundle' => 'section_featured',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'hidden',
        'module' => 'text',
        'settings' => array(),
        'type' => 'text_default',
        'weight' => 2,
      ),
      'paragraphs_editor_preview' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'paragraphs_item',
    'field_name' => 'p_n_secfeat_copy',
    'label' => 'Section Featured Copy',
    'required' => 0,
    'settings' => array(
      'text_processing' => 0,
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'text',
      'settings' => array(
        'rows' => 2,
      ),
      'type' => 'text_textarea',
      'weight' => 3,
    ),
  );

  // Exported field_instance:
  // 'paragraphs_item-section_featured-p_n_secfeat_image'.
  $field_instances['paragraphs_item-section_featured-p_n_secfeat_image'] = array(
    'bundle' => 'section_featured',
    'deleted' => 0,
    'description' => 'This image should be 16x9 aspect ratio and optimized for the web, no larger than 1600 pixels wide.',
    'display' => array(
      'default' => array(
        'label' => 'hidden',
        'module' => 'image',
        'settings' => array(
          'class' => 'dcf-ratio-child dcf-obj-fit-cover',
          'image_link' => '',
          'image_style' => 'primary_support_photo',
        ),
        'type' => 'image',
        'weight' => 0,
      ),
      'paragraphs_editor_preview' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'paragraphs_item',
    'field_name' => 'p_n_secfeat_image',
    'label' => 'Section Featured Image',
    'required' => 0,
    'settings' => array(
      'alt_field' => 1,
      'default_image' => 0,
      'file_directory' => 'images/mindset/sections/featured',
      'file_extensions' => 'jpg jpeg',
      'max_filesize' => '',
      'max_resolution' => '',
      'min_resolution' => '',
      'title_field' => 0,
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'image',
      'settings' => array(
        'preview_image_style' => 'thumbnail',
        'progress_indicator' => 'throbber',
      ),
      'type' => 'image_image',
      'weight' => 1,
    ),
  );

  // Exported field_instance:
  // 'paragraphs_item-section_featured-p_n_secfeat_link'.
  $field_instances['paragraphs_item-section_featured-p_n_secfeat_link'] = array(
    'bundle' => 'section_featured',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'hidden',
        'module' => 'link',
        'settings' => array(),
        'type' => 'link_default',
        'weight' => 3,
      ),
      'paragraphs_editor_preview' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'paragraphs_item',
    'field_name' => 'p_n_secfeat_link',
    'label' => 'Section Featured Link',
    'required' => 0,
    'settings' => array(
      'attributes' => array(
        'class' => 'unl-cream',
        'configurable_title' => 0,
        'rel' => '',
        'target' => 'default',
        'title' => '',
      ),
      'display' => array(
        'url_cutoff' => 80,
      ),
      'enable_tokens' => 1,
      'rel_remove' => 'default',
      'title' => 'optional',
      'title_maxlength' => 128,
      'title_value' => '',
      'url' => 0,
      'user_register_form' => FALSE,
      'validate_url' => 1,
    ),
    'widget' => array(
      'active' => 0,
      'module' => 'link',
      'settings' => array(),
      'type' => 'link_field',
      'weight' => 4,
    ),
  );

  // Exported field_instance:
  // 'paragraphs_item-section_featured-p_n_secfeat_title'.
  $field_instances['paragraphs_item-section_featured-p_n_secfeat_title'] = array(
    'bundle' => 'section_featured',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'hidden',
        'module' => 'text',
        'settings' => array(),
        'type' => 'text_default',
        'weight' => 1,
      ),
      'paragraphs_editor_preview' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'paragraphs_item',
    'field_name' => 'p_n_secfeat_title',
    'label' => 'Section Featured Title',
    'required' => 0,
    'settings' => array(
      'text_processing' => 0,
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'text',
      'settings' => array(
        'size' => 60,
      ),
      'type' => 'text_textfield',
      'weight' => 2,
    ),
  );

  // Exported field_instance:
  // 'paragraphs_item-section_secondary-p_p_secsecondary_image'.
  $field_instances['paragraphs_item-section_secondary-p_p_secsecondary_image'] = array(
    'bundle' => 'section_secondary',
    'deleted' => 0,
    'description' => 'This image should be 4x3 aspect ratio and optimized for the web, no larger than 1200 pixels wide.',
    'display' => array(
      'default' => array(
        'label' => 'above',
        'module' => 'image',
        'settings' => array(
          'class' => 'dcf-ratio-child dcf-obj-fit-cover',
          'image_link' => '',
          'image_style' => 'secondary_support_card',
        ),
        'type' => 'image',
        'weight' => 0,
      ),
      'paragraphs_editor_preview' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'paragraphs_item',
    'field_name' => 'p_p_secsecondary_image',
    'label' => 'Section Secondary Image',
    'required' => 0,
    'settings' => array(
      'alt_field' => 1,
      'default_image' => 0,
      'file_directory' => 'images/mindset/sections/secondary',
      'file_extensions' => 'jpg jpeg',
      'max_filesize' => '',
      'max_resolution' => '',
      'min_resolution' => '',
      'title_field' => 0,
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'image',
      'settings' => array(
        'preview_image_style' => 'thumbnail',
        'progress_indicator' => 'throbber',
      ),
      'type' => 'image_image',
      'weight' => 1,
    ),
  );

  // Exported field_instance:
  // 'paragraphs_item-section_secondary-p_p_secsecondary_link'.
  $field_instances['paragraphs_item-section_secondary-p_p_secsecondary_link'] = array(
    'bundle' => 'section_secondary',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'above',
        'module' => 'link',
        'settings' => array(),
        'type' => 'link_default',
        'weight' => 3,
      ),
      'paragraphs_editor_preview' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'paragraphs_item',
    'field_name' => 'p_p_secsecondary_link',
    'label' => 'Section Secondary Link',
    'required' => 0,
    'settings' => array(
      'attributes' => array(
        'class' => 'dcf-txt-decor-none',
        'configurable_title' => 0,
        'rel' => '',
        'target' => 'default',
        'title' => '',
      ),
      'display' => array(
        'url_cutoff' => '',
      ),
      'enable_tokens' => 1,
      'rel_remove' => 'default',
      'title' => 'optional',
      'title_maxlength' => 180,
      'title_value' => '',
      'url' => 0,
      'user_register_form' => FALSE,
      'validate_url' => 1,
    ),
    'widget' => array(
      'active' => 0,
      'module' => 'link',
      'settings' => array(),
      'type' => 'link_field',
      'weight' => 4,
    ),
  );

  // Exported field_instance:
  // 'paragraphs_item-section_secondary-p_p_secsecondary_title'.
  $field_instances['paragraphs_item-section_secondary-p_p_secsecondary_title'] = array(
    'bundle' => 'section_secondary',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => 'Name / title of source for support section.',
    'display' => array(
      'default' => array(
        'label' => 'above',
        'module' => 'text',
        'settings' => array(),
        'type' => 'text_default',
        'weight' => 1,
      ),
      'paragraphs_editor_preview' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'paragraphs_item',
    'field_name' => 'p_p_secsecondary_title',
    'label' => 'Section Secondary Title',
    'required' => 0,
    'settings' => array(
      'text_processing' => 0,
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'text',
      'settings' => array(
        'size' => 60,
      ),
      'type' => 'text_textfield',
      'weight' => 2,
    ),
  );

  // Exported field_instance: 'paragraphs_item-study_abroad-p_n_sa_image'.
  $field_instances['paragraphs_item-study_abroad-p_n_sa_image'] = array(
    'bundle' => 'study_abroad',
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'hidden',
        'module' => 'image',
        'settings' => array(
          'class' => 'dcf-ratio-child dcf-obj-fit-cover dcf-rounded-top dcf-lazy-loaded',
          'image_link' => '',
          'image_style' => 'primary_support_photo',
        ),
        'type' => 'image',
        'weight' => 0,
      ),
      'paragraphs_editor_preview' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'paragraphs_item',
    'field_name' => 'p_n_sa_image',
    'label' => 'SA Image',
    'required' => 0,
    'settings' => array(
      'alt_field' => 1,
      'default_image' => 0,
      'file_directory' => 'images/mindset/majors',
      'file_extensions' => 'jpg jpeg',
      'max_filesize' => '',
      'max_resolution' => '',
      'min_resolution' => '',
      'title_field' => 0,
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'image',
      'settings' => array(
        'preview_image_style' => 'thumbnail',
        'progress_indicator' => 'throbber',
      ),
      'type' => 'image_image',
      'weight' => 0,
    ),
  );

  // Exported field_instance: 'paragraphs_item-study_abroad-p_n_sa_text'.
  $field_instances['paragraphs_item-study_abroad-p_n_sa_text'] = array(
    'bundle' => 'study_abroad',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'above',
        'module' => 'text',
        'settings' => array(),
        'type' => 'text_default',
        'weight' => 2,
      ),
      'paragraphs_editor_preview' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'paragraphs_item',
    'field_name' => 'p_n_sa_text',
    'label' => 'SA Text',
    'required' => 0,
    'settings' => array(
      'text_processing' => 0,
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'text',
      'settings' => array(
        'size' => 100,
      ),
      'type' => 'text_textfield',
      'weight' => 2,
    ),
  );

  // Exported field_instance: 'paragraphs_item-study_abroad-p_n_sa_title'.
  $field_instances['paragraphs_item-study_abroad-p_n_sa_title'] = array(
    'bundle' => 'study_abroad',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'above',
        'module' => 'link',
        'settings' => array(),
        'type' => 'link_default',
        'weight' => 3,
      ),
      'paragraphs_editor_preview' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'paragraphs_item',
    'field_name' => 'p_n_sa_title',
    'label' => 'SA Title',
    'required' => 0,
    'settings' => array(
      'attributes' => array(
        'class' => 'dcf-txt-decor-none',
        'configurable_title' => 0,
        'rel' => '',
        'target' => 'default',
        'title' => '',
      ),
      'display' => array(
        'url_cutoff' => 80,
      ),
      'enable_tokens' => 1,
      'rel_remove' => 'default',
      'title' => 'optional',
      'title_maxlength' => 128,
      'title_value' => '',
      'url' => 'optional',
      'user_register_form' => FALSE,
      'validate_url' => 1,
    ),
    'widget' => array(
      'active' => 0,
      'module' => 'link',
      'settings' => array(),
      'type' => 'link_field',
      'weight' => 1,
    ),
  );

  // Exported field_instance: 'paragraphs_item-support_section-p_n_supsec_lead'.
  $field_instances['paragraphs_item-support_section-p_n_supsec_lead'] = array(
    'bundle' => 'support_section',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'hidden',
        'module' => 'text',
        'settings' => array(),
        'type' => 'text_default',
        'weight' => 1,
      ),
      'paragraphs_editor_preview' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'paragraphs_item',
    'field_name' => 'p_n_supsec_lead',
    'label' => 'Section Lead Copy',
    'required' => 0,
    'settings' => array(
      'text_processing' => 0,
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'text',
      'settings' => array(
        'rows' => 2,
      ),
      'type' => 'text_textarea',
      'weight' => 2,
    ),
  );

  // Exported field_instance:
  // 'paragraphs_item-support_section-p_n_supsec_title'.
  $field_instances['paragraphs_item-support_section-p_n_supsec_title'] = array(
    'bundle' => 'support_section',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'hidden',
        'module' => 'text',
        'settings' => array(),
        'type' => 'text_default',
        'weight' => 0,
      ),
      'paragraphs_editor_preview' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'paragraphs_item',
    'field_name' => 'p_n_supsec_title',
    'label' => 'Section Title',
    'required' => 0,
    'settings' => array(
      'text_processing' => 0,
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'text',
      'settings' => array(
        'size' => 60,
      ),
      'type' => 'text_textfield',
      'weight' => 1,
    ),
  );

  // Exported field_instance:
  // 'paragraphs_item-support_section-s_p_supsec_featured'.
  $field_instances['paragraphs_item-support_section-s_p_supsec_featured'] = array(
    'bundle' => 'support_section',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'hidden',
        'module' => 'paragraphs',
        'settings' => array(
          'view_mode' => 'full',
        ),
        'type' => 'paragraphs_view',
        'weight' => 2,
      ),
      'paragraphs_editor_preview' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'paragraphs_item',
    'field_name' => 's_p_supsec_featured',
    'label' => 'Section Support Featured',
    'required' => 0,
    'settings' => array(
      'add_mode' => 'select',
      'allowed_bundles' => array(
        'contact' => -1,
        'major' => -1,
        'mindset_teaser' => -1,
        'section_featured' => 'section_featured',
        'section_secondary' => -1,
        'support_section' => -1,
        'test_paragraph_bundle_eric_' => -1,
      ),
      'bundle_weights' => array(
        'contact' => 2,
        'major' => 3,
        'mindset_teaser' => 4,
        'section_featured' => 5,
        'section_secondary' => 6,
        'support_section' => 7,
        'test_paragraph_bundle_eric_' => 8,
      ),
      'default_edit_mode' => 'open',
      'default_edit_mode_override' => 1,
      'title' => 'Paragraph',
      'title_multiple' => 'Paragraphs',
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 0,
      'module' => 'paragraphs',
      'settings' => array(),
      'type' => 'paragraphs_embed',
      'weight' => 3,
    ),
  );

  // Exported field_instance:
  // 'paragraphs_item-support_section-s_p_supsec_secondary'.
  $field_instances['paragraphs_item-support_section-s_p_supsec_secondary'] = array(
    'bundle' => 'support_section',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'hidden',
        'module' => 'paragraphs',
        'settings' => array(
          'view_mode' => 'full',
        ),
        'type' => 'paragraphs_view',
        'weight' => 3,
      ),
      'paragraphs_editor_preview' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'paragraphs_item',
    'field_name' => 's_p_supsec_secondary',
    'label' => 'Section Support Secondary',
    'required' => 0,
    'settings' => array(
      'add_mode' => 'select',
      'allowed_bundles' => array(
        'contact' => -1,
        'major' => -1,
        'mindset_teaser' => -1,
        'section_featured' => -1,
        'section_secondary' => 'section_secondary',
        'support_section' => -1,
        'test_paragraph_bundle_eric_' => -1,
      ),
      'bundle_weights' => array(
        'contact' => 2,
        'major' => 3,
        'mindset_teaser' => 4,
        'section_featured' => 5,
        'section_secondary' => 6,
        'support_section' => 7,
        'test_paragraph_bundle_eric_' => 8,
      ),
      'default_edit_mode' => 'open',
      'default_edit_mode_override' => 1,
      'title' => 'Paragraph',
      'title_multiple' => 'Paragraphs',
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 0,
      'module' => 'paragraphs',
      'settings' => array(),
      'type' => 'paragraphs_embed',
      'weight' => 4,
    ),
  );

  // Translatables
  // Included for use with string extractors like potx.
  t('4 Year Plans');
  t('Admission Hero Subhead');
  t('Admission Hero Title');
  t('Admission Links');
  t('Apply Link');
  t('Areas of Emphasis');
  t('Career Connections');
  t('Career Examples');
  t('Career Paths');
  t('Career Skills');
  t('Career Title');
  t('College Specific');
  t('Contact Email');
  t('Contact Image');
  t('Contact Name');
  t('Contact Phone');
  t('Contact Title');
  t('Contacts');
  t('Core Academic Image');
  t('Core Academic Text');
  t('Core Academics');
  t('Course Desc');
  t('Course Title');
  t('Courses Lead');
  t('Enter full number with area code and dashes, without 1 in front.
Example: 402-472-1111');
  t('For highlighting most interesting courses for the major. Maximum 6.');
  t('Hands On Learning');
  t('Hero Image');
  t('Icon');
  t('Icon with statement and supporting text. Maximum of 4 allowed.  ');
  t('Info Visit Links');
  t('Lead Copy');
  t('Learning Outcomes');
  t('MS Image Teaser');
  t('MS Link Teaser');
  t('MS Text Teaser');
  t('MS Title Teaser');
  t('Major Lead Copy');
  t('Major Specific');
  t('Make Difference');
  t('Maximum of 5 allowed per listing.');
  t('Maximum of four points emphasizing Career Connections with this major.');
  t('Maximum of four points emphasizing Learning Outcomes with this major.');
  t('Maximum of four points emphasizing What Students Will Learn with this major.');
  t('Mediahub URL');
  t('Mindsets');
  t('Mindsets Hero Text');
  t('Mindsets Hero Title');
  t('Name / title of source for support section.');
  t('Notable Courses');
  t('Optional field for major specific information that may not fit anywhere else. All content should be contained within a dcf-bleed div. ');
  t('Point of Pride');
  t('Research Academic Alt Title');
  t('Research Academic Link');
  t('Research Academic Opportunities');
  t('Research Grad Prep');
  t('SA Image');
  t('SA Text');
  t('SA Title');
  t('Scholarship');
  t('Section Featured Copy');
  t('Section Featured Image');
  t('Section Featured Link');
  t('Section Featured Title');
  t('Section Lead Copy');
  t('Section Majors');
  t('Section Secondary Image');
  t('Section Secondary Link');
  t('Section Secondary Title');
  t('Section Support Featured');
  t('Section Support Secondary');
  t('Section Title');
  t('Social Connections');
  t('Statement');
  t('Student Org Text 1');
  t('Student Org Text 2');
  t('Student Org Title 1');
  t('Student Org Title 2');
  t('Student Orgs Link');
  t('Study Abroad');
  t('Study Abroad Link');
  t('Support');
  t('Support Sections');
  t('Text determines which icon shows up. All lowercase, no spaces before or after.<br>
<b>money</b> - scholarships or financial aid related, lowest debt, etc<br>
<b>award</b> - recognition given by outside entities, ratings, etc<br>
<b>grad</b> - graduate employment levels, etc<br>
<b>check</b> - generic positive to emphasize<br>
<b>support</b> - faculty/student ratio, other support programs<br>
<b>column</b> - generic program positive to emphasize<br>');
  t('The title "Research and Academic Opportunities" will be displayed for this section, unless this title is filled in. Only change it if your major is highlighting different student opportunities.');
  t('These links will appear in footer along with "Apply" link.');
  t('This image should be 16x9 aspect ratio and optimized for the web, no larger than 1600 pixels wide.');
  t('This image should be 16x9 aspect ratio and optimized for the web, no larger than 2400 pixels wide.');
  t('This image should be 1x1 aspect ratio and 800x800 maximum, optimized for the web.');
  t('This image should be 4x3 aspect ratio and optimized for the web, no larger than 1200 pixels wide.');
  t('This image should be 4x3 aspect ratio and optimized for the web, no larger than 1600 pixels wide.');
  t('This link will be replicated further down page with course descriptions.');
  t('This link will be replicated in the footer area along with "Request More Information" and "Visit Campus" links');
  t('This video is what students will see first on your page. Make sure you select a video that is a good introduction to this specific major.

This should just be the URL to the mediahub file. Example: https://mediahub.unl.edu/media/17672');
  t('Video Text');
  t('Video title');
  t('What You\'ll Learn');

  return $field_instances;
}
