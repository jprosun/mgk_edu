<?php
/**
 * Register ACF field groups for mg_teacher post type.
 * Clients add/edit teachers via wp-admin with these fields.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'acf/init', 'mgk_register_teacher_fields' );
add_action( 'acf/init', 'mgk_register_review_fields' );
add_action( 'acf/init', 'mgk_register_plan_fields' );
add_action( 'acf/init', 'mgk_register_parent_fields' );
add_action( 'acf/init', 'mgk_register_child_fields' );
add_action( 'acf/init', 'mgk_register_lead_fields' );
add_action( 'acf/init', 'mgk_register_proposal_fields' );

function mgk_register_teacher_fields() {
    if ( ! function_exists( 'acf_add_local_field_group' ) ) return;

    // ── Group 1: Listing Card Fields ─────────────────────────────────────────
    acf_add_local_field_group( [
        'key'      => 'group_mgk_teacher_listing',
        'title'    => '1 · Listing Card',
        'location' => [ [ [ 'param' => 'post_type', 'operator' => '==', 'value' => 'mg_teacher' ] ] ],
        'position' => 'normal',
        'menu_order' => 1,
        'fields'   => [
            [
                'key'           => 'field_mgk_tier',
                'name'          => 'mgk_tier',
                'label'         => 'Tutor Tier',
                'type'          => 'select',
                'choices'       => [
                    'Part-time'     => 'Part-time',
                    'Full-time'     => 'Full-time',
                    'NUS Part-time' => 'NUS Part-time',
                    'Ex-MOE'        => 'Ex-MOE',
                    'IB Specialist' => 'IB Specialist',
                    'Premium'       => 'Premium',
                ],
                'allow_null'    => 1,
                'return_format' => 'value',
            ],
            [
                'key'          => 'field_mgk_experience',
                'name'         => 'mgk_experience',
                'label'        => 'Years of Experience',
                'type'         => 'text',
                'placeholder'  => '8y',
                'instructions' => 'E.g. 8y',
            ],
            [
                'key'          => 'field_mgk_rate_num',
                'name'         => 'mgk_rate_num',
                'label'        => 'Hourly Rate ($)',
                'type'         => 'number',
                'placeholder'  => '65',
                'instructions' => 'Number only, e.g. 65',
                'min'          => 0,
            ],
            [
                'key'          => 'field_mgk_trial_price',
                'name'         => 'mgk_trial_price',
                'label'        => 'Trial Price',
                'type'         => 'text',
                'placeholder'  => '$40',
                'instructions' => 'E.g. $40',
            ],
            [
                'key'         => 'field_mgk_rating',
                'name'        => 'mgk_rating',
                'label'       => 'Rating',
                'type'        => 'number',
                'placeholder' => '4.9',
                'min'         => 0,
                'max'         => 5,
                'step'        => '0.1',
            ],
            [
                'key'         => 'field_mgk_reviews',
                'name'        => 'mgk_reviews',
                'label'       => 'Number of Reviews',
                'type'        => 'number',
                'placeholder' => '87',
                'min'         => 0,
            ],
            [
                'key'          => 'field_mgk_response',
                'name'         => 'mgk_response',
                'label'        => 'Response Time',
                'type'         => 'text',
                'placeholder'  => '4h',
                'instructions' => 'E.g. 2h, 1 day',
            ],
            [
                'key'           => 'field_mgk_locations',
                'name'          => 'mgk_locations',
                'label'         => 'Locations',
                'type'          => 'checkbox',
                'choices'       => [
                    'Online'       => 'Online',
                    'Central SG'   => 'Central SG',
                    'East'         => 'East',
                    'West'         => 'West',
                    'North'        => 'North',
                    'NE'           => 'North-East',
                    'Home tuition' => 'Home tuition',
                ],
                'return_format' => 'value',
                'layout'        => 'horizontal',
            ],
            [
                'key'          => 'field_mgk_is_verified',
                'name'         => 'mgk_is_verified',
                'label'        => 'Verified (show publicly)',
                'type'         => 'true_false',
                'default_value'=> 0,
                'ui'           => 1,
                'instructions' => 'Must be checked before tutor appears in listing and profile. Only check after ID + degree + background + demo are confirmed.',
            ],
            [
                'key'          => 'field_mgk_tags',
                'name'         => 'mgk_tags',
                'label'        => 'Tags (comma separated)',
                'type'         => 'text',
                'placeholder'  => 'P5-P6 Math, Online OK, Demo',
                'instructions' => 'Short labels shown on listing card.',
            ],
            [
                'key'          => 'field_mgk_bio',
                'name'         => 'mgk_bio',
                'label'        => 'Short Bio (listing card)',
                'type'         => 'textarea',
                'rows'         => 3,
                'instructions' => 'One sentence shown on the search results card.',
            ],
            [
                'key'          => 'field_mgk_availability',
                'name'         => 'mgk_availability',
                'label'        => 'Weekly Availability',
                'type'         => 'repeater',
                'instructions' => 'Each row = one day. Leave slots empty if unavailable that day.',
                'min'          => 0,
                'max'          => 7,
                'layout'       => 'table',
                'button_label' => 'Add day',
                'sub_fields'   => [
                    [
                        'key'          => 'field_mgk_avail_day',
                        'name'         => 'day',
                        'label'        => 'Day',
                        'type'         => 'select',
                        'choices'      => [
                            'Mon' => 'Monday',
                            'Tue' => 'Tuesday',
                            'Wed' => 'Wednesday',
                            'Thu' => 'Thursday',
                            'Fri' => 'Friday',
                            'Sat' => 'Saturday',
                            'Sun' => 'Sunday',
                        ],
                        'column_width' => 20,
                    ],
                    [
                        'key'          => 'field_mgk_avail_slots',
                        'name'         => 'slots',
                        'label'        => 'Time Slots (comma-separated)',
                        'type'         => 'text',
                        'placeholder'  => '4pm-6pm, 7pm-9pm',
                        'instructions' => 'Leave empty = unavailable this day.',
                        'column_width' => 80,
                    ],
                ],
            ],
        ],
    ] );

    // ── Group 2: Profile Header (Phase 1) ────────────────────────────────────
    acf_add_local_field_group( [
        'key'        => 'group_mgk_teacher_profile',
        'title'      => '2 · Profile Header',
        'location'   => [ [ [ 'param' => 'post_type', 'operator' => '==', 'value' => 'mg_teacher' ] ] ],
        'position'   => 'normal',
        'menu_order' => 2,
        'fields'     => [
            [
                'key'          => 'field_mgk_short_name',
                'name'         => 'mgk_short_name',
                'label'        => 'Short Name',
                'type'         => 'text',
                'placeholder'  => 'Ms Lee',
                'instructions' => 'First name or informal name shown in headings.',
            ],
            [
                'key'          => 'field_mgk_credential_badge',
                'name'         => 'mgk_credential_badge',
                'label'        => 'Credential Badge',
                'type'         => 'text',
                'placeholder'  => 'NIE-trained',
                'instructions' => 'E.g. NIE-trained, NUS-grad, PhD.',
            ],
            [
                'key'          => 'field_mgk_languages',
                'name'         => 'mgk_languages',
                'label'        => 'Languages',
                'type'         => 'text',
                'placeholder'  => 'English, Mandarin',
            ],
            [
                'key'          => 'field_mgk_duration',
                'name'         => 'mgk_duration',
                'label'        => 'Lesson Duration',
                'type'         => 'text',
                'placeholder'  => '1.5h or 2h · 1-2x/week',
            ],
            [
                'key'          => 'field_mgk_active_students',
                'name'         => 'mgk_active_students',
                'label'        => 'Active Students',
                'type'         => 'number',
                'placeholder'  => '12',
                'min'          => 0,
            ],
            [
                'key'          => 'field_mgk_last_active',
                'name'         => 'mgk_last_active',
                'label'        => 'Last Active',
                'type'         => 'text',
                'placeholder'  => '2d',
                'instructions' => 'E.g. 2d, 1h, today.',
            ],
            [
                'key'          => 'field_mgk_demo_video_url',
                'name'         => 'mgk_demo_video_url',
                'label'        => 'Demo Video URL',
                'type'         => 'url',
                'placeholder'  => 'https://...',
                'instructions' => 'YouTube or Vimeo link for the demo lesson video.',
            ],
            [
                'key'          => 'field_mgk_philosophy',
                'name'         => 'mgk_philosophy',
                'label'        => 'Teaching Philosophy',
                'type'         => 'textarea',
                'rows'         => 3,
                'instructions' => 'One or two sentences on teaching approach.',
            ],
        ],
    ] );

    // ── Group 3: Rich Profile Content (Phase 2 Repeaters) ────────────────────
    acf_add_local_field_group( [
        'key'        => 'group_mgk_teacher_content',
        'title'      => '3 · Profile Content',
        'location'   => [ [ [ 'param' => 'post_type', 'operator' => '==', 'value' => 'mg_teacher' ] ] ],
        'position'   => 'normal',
        'menu_order' => 3,
        'fields'     => [
            // About paragraphs
            [
                'key'          => 'field_mgk_about_paragraphs',
                'name'         => 'mgk_about_paragraphs',
                'label'        => 'About (paragraphs)',
                'type'         => 'repeater',
                'instructions' => 'Each row = one paragraph in the About section.',
                'min'          => 1,
                'max'          => 5,
                'layout'       => 'block',
                'button_label' => 'Add paragraph',
                'sub_fields'   => [
                    [
                        'key'   => 'field_mgk_about_content',
                        'name'  => 'content',
                        'label' => 'Paragraph',
                        'type'  => 'textarea',
                        'rows'  => 4,
                    ],
                ],
            ],
            // Specializations
            [
                'key'          => 'field_mgk_specializations',
                'name'         => 'mgk_specializations',
                'label'        => 'Specializations',
                'type'         => 'repeater',
                'instructions' => 'Shown in the sidebar next to About. E.g. "PSLE Math prep" / "87% A/A*".',
                'min'          => 0,
                'max'          => 6,
                'layout'       => 'table',
                'button_label' => 'Add specialization',
                'sub_fields'   => [
                    [
                        'key'         => 'field_mgk_spec_name',
                        'name'        => 'specialty',
                        'label'       => 'Specialization',
                        'type'        => 'text',
                        'placeholder' => 'PSLE Math prep',
                        'column_width' => 50,
                    ],
                    [
                        'key'         => 'field_mgk_spec_level',
                        'name'        => 'level',
                        'label'       => 'Result / Level',
                        'type'        => 'text',
                        'placeholder' => '87% A/A* rate',
                        'column_width' => 50,
                    ],
                ],
            ],
            // Qualifications
            [
                'key'          => 'field_mgk_qualifications',
                'name'         => 'mgk_qualifications',
                'label'        => 'Qualifications',
                'type'         => 'repeater',
                'instructions' => 'Degrees, certifications, background checks.',
                'min'          => 0,
                'max'          => 8,
                'layout'       => 'block',
                'button_label' => 'Add qualification',
                'sub_fields'   => [
                    [
                        'key'         => 'field_mgk_qual_title',
                        'name'        => 'title',
                        'label'       => 'Title',
                        'type'        => 'text',
                        'placeholder' => 'B.Sc. Mathematics',
                    ],
                    [
                        'key'         => 'field_mgk_qual_desc',
                        'name'        => 'description',
                        'label'       => 'Institution / Period',
                        'type'        => 'text',
                        'placeholder' => 'NUS · 2008',
                    ],
                    [
                        'key'         => 'field_mgk_qual_cert',
                        'name'        => 'cert_id',
                        'label'       => 'Certificate / Note',
                        'type'        => 'text',
                        'placeholder' => 'Cert ID: NUS-MATH-2008-1234',
                    ],
                ],
            ],
            // Track record stats
            [
                'key'          => 'field_mgk_track_stats',
                'name'         => 'mgk_track_stats',
                'label'        => 'Track Record Stats',
                'type'         => 'repeater',
                'instructions' => 'Numbers shown in the track record bar. E.g. "87%" / "PSLE A/A* rate".',
                'min'          => 0,
                'max'          => 8,
                'layout'       => 'table',
                'button_label' => 'Add stat',
                'sub_fields'   => [
                    [
                        'key'          => 'field_mgk_track_value',
                        'name'         => 'value',
                        'label'        => 'Value',
                        'type'         => 'text',
                        'placeholder'  => '87%',
                        'column_width' => 30,
                    ],
                    [
                        'key'          => 'field_mgk_track_label',
                        'name'         => 'label',
                        'label'        => 'Label',
                        'type'         => 'text',
                        'placeholder'  => 'PSLE A/A* rate',
                        'column_width' => 70,
                    ],
                ],
            ],
            // Lesson packages
            [
                'key'          => 'field_mgk_packages',
                'name'         => 'mgk_packages',
                'label'        => 'Lesson Packages',
                'type'         => 'repeater',
                'instructions' => 'Packages shown on the profile. Mark one as Recommended.',
                'min'          => 0,
                'max'          => 5,
                'layout'       => 'block',
                'button_label' => 'Add package',
                'sub_fields'   => [
                    [
                        'key'         => 'field_mgk_pkg_name',
                        'name'        => 'name',
                        'label'       => 'Package Name',
                        'type'        => 'text',
                        'placeholder' => '8 lessons',
                    ],
                    [
                        'key'         => 'field_mgk_pkg_price',
                        'name'        => 'price',
                        'label'       => 'Price',
                        'type'        => 'text',
                        'placeholder' => '$494',
                    ],
                    [
                        'key'         => 'field_mgk_pkg_desc',
                        'name'        => 'description',
                        'label'       => 'Description',
                        'type'        => 'text',
                        'placeholder' => '5% package saving',
                    ],
                    [
                        'key'           => 'field_mgk_pkg_featured',
                        'name'          => 'featured',
                        'label'         => 'Recommended',
                        'type'          => 'true_false',
                        'ui'            => 1,
                        'default_value' => 0,
                    ],
                ],
            ],
            // FAQs
            [
                'key'          => 'field_mgk_faqs',
                'name'         => 'mgk_faqs',
                'label'        => 'FAQs',
                'type'         => 'repeater',
                'instructions' => 'Questions shown in the FAQ accordion on the profile.',
                'min'          => 0,
                'max'          => 8,
                'layout'       => 'block',
                'button_label' => 'Add FAQ',
                'sub_fields'   => [
                    [
                        'key'         => 'field_mgk_faq_q',
                        'name'        => 'q',
                        'label'       => 'Question',
                        'type'        => 'text',
                        'placeholder' => 'How does the lesson work?',
                    ],
                    [
                        'key'         => 'field_mgk_faq_a',
                        'name'        => 'a',
                        'label'       => 'Answer',
                        'type'        => 'textarea',
                        'rows'        => 3,
                        'placeholder' => 'Each lesson starts with...',
                    ],
                ],
            ],
        ],
    ] );
}

/**
 * Plan — schema #3. tenant_id dropped (1 site = 1 tenant).
 * id/created_at = WP post ID + post_date.
 */
function mgk_register_plan_fields() {
    if ( ! function_exists( 'acf_add_local_field_group' ) ) return;

    acf_add_local_field_group( [
        'key'        => 'group_mgk_plan',
        'title'      => 'Plan Details',
        'location'   => [ [ [ 'param' => 'post_type', 'operator' => '==', 'value' => 'mg_plan' ] ] ],
        'position'   => 'normal',
        'menu_order' => 1,
        'fields'     => [
            [
                'key'           => 'field_mgk_plan_type',
                'name'          => 'mgk_plan_type',
                'label'         => 'Type',
                'type'          => 'select',
                'choices'       => [
                    'TRIAL'      => 'Trial Lesson',
                    'PACKAGE_8'  => '8-Lesson Package',
                    'PACKAGE_16' => '16-Lesson Package',
                    'SINGLE'     => 'Single Lesson',
                ],
                'required'      => 1,
                'return_format' => 'value',
            ],
            [
                'key'          => 'field_mgk_plan_lessons_count',
                'name'         => 'mgk_plan_lessons_count',
                'label'        => 'Lessons Count',
                'type'         => 'number',
                'instructions' => 'Number of lessons in this plan, e.g. 1, 8, 16.',
                'min'          => 1,
                'default_value'=> 1,
            ],
            [
                'key'          => 'field_mgk_plan_duration_min',
                'name'         => 'mgk_plan_default_duration_min',
                'label'        => 'Default Duration (min)',
                'type'         => 'select',
                'choices'      => [ 60 => '60', 90 => '90', 120 => '120' ],
                'default_value'=> 60,
                'return_format'=> 'value',
            ],
            [
                'key'          => 'field_mgk_plan_discount',
                'name'         => 'mgk_plan_discount_percent',
                'label'        => 'Discount %',
                'type'         => 'number',
                'instructions' => 'Trial 40, package 5–10. Whole number (40 = 40%).',
                'min'          => 0,
                'max'          => 100,
                'default_value'=> 0,
            ],
            [
                'key'          => 'field_mgk_plan_validity',
                'name'         => 'mgk_plan_validity_days',
                'label'        => 'Validity (days)',
                'type'         => 'number',
                'instructions' => 'Trial 7, 8-pkg 90, 16-pkg 180.',
                'min'          => 1,
                'default_value'=> 90,
            ],
            [
                'key'           => 'field_mgk_plan_active',
                'name'          => 'mgk_plan_is_active',
                'label'         => 'Active',
                'type'          => 'true_false',
                'ui'            => 1,
                'default_value' => 1,
            ],
            [
                'key'          => 'field_mgk_plan_sort',
                'name'         => 'mgk_plan_sort_order',
                'label'        => 'Sort Order',
                'type'         => 'number',
                'default_value'=> 0,
                'min'          => 0,
            ],
        ],
    ] );
}

/**
 * Parent account — schema #8. tenant_id dropped.
 * status enum kept; created_from_lead_id = post_object to mg_lead.
 */
function mgk_register_parent_fields() {
    if ( ! function_exists( 'acf_add_local_field_group' ) ) return;

    acf_add_local_field_group( [
        'key'        => 'group_mgk_parent',
        'title'      => 'Parent Account',
        'location'   => [ [ [ 'param' => 'post_type', 'operator' => '==', 'value' => 'mg_parent' ] ] ],
        'position'   => 'normal',
        'menu_order' => 1,
        'fields'     => [
            [
                'key'      => 'field_mgk_parent_email',
                'name'     => 'mgk_parent_email',
                'label'    => 'Email',
                'type'     => 'email',
                'required' => 1,
            ],
            [
                'key'          => 'field_mgk_parent_phone',
                'name'         => 'mgk_parent_phone_e164',
                'label'        => 'Phone (E.164)',
                'type'         => 'text',
                'placeholder'  => '+6591234567',
            ],
            [
                'key'   => 'field_mgk_parent_name',
                'name'  => 'mgk_parent_full_name',
                'label' => 'Full Name',
                'type'  => 'text',
            ],
            [
                'key'           => 'field_mgk_parent_marketing',
                'name'          => 'mgk_parent_marketing_consent',
                'label'         => 'Marketing Consent',
                'type'          => 'true_false',
                'ui'            => 1,
                'default_value' => 0,
            ],
            [
                'key'           => 'field_mgk_parent_pdpa_at',
                'name'          => 'mgk_parent_pdpa_accepted_at',
                'label'         => 'PDPA Accepted At',
                'type'          => 'date_time_picker',
                'return_format' => 'Y-m-d H:i:s',
            ],
            [
                'key'           => 'field_mgk_parent_from_lead',
                'name'          => 'mgk_parent_created_from_lead_id',
                'label'         => 'Created From Lead',
                'type'          => 'post_object',
                'post_type'     => [ 'mg_lead' ],
                'return_format' => 'id',
                'instructions'  => 'The original enquiry this parent came from.',
            ],
            [
                'key'           => 'field_mgk_parent_status',
                'name'          => 'mgk_parent_status',
                'label'         => 'Status',
                'type'          => 'select',
                'choices'       => [
                    'ACTIVE'    => 'Active',
                    'SUSPENDED' => 'Suspended',
                    'DELETED'   => 'Deleted',
                ],
                'default_value' => 'ACTIVE',
                'return_format' => 'value',
            ],
        ],
    ] );
}

/**
 * Child — schema #9. tenant_id dropped.
 * parent_account_id = post_object to mg_parent (the FK).
 */
function mgk_register_child_fields() {
    if ( ! function_exists( 'acf_add_local_field_group' ) ) return;

    acf_add_local_field_group( [
        'key'        => 'group_mgk_child',
        'title'      => 'Child Details',
        'location'   => [ [ [ 'param' => 'post_type', 'operator' => '==', 'value' => 'mg_child' ] ] ],
        'position'   => 'normal',
        'menu_order' => 1,
        'fields'     => [
            [
                'key'           => 'field_mgk_child_parent',
                'name'          => 'mgk_child_parent_id',
                'label'         => 'Parent',
                'type'          => 'post_object',
                'post_type'     => [ 'mg_parent' ],
                'return_format' => 'id',
                'required'      => 1,
                'instructions'  => 'Which parent account this child belongs to.',
            ],
            [
                'key'      => 'field_mgk_child_name',
                'name'     => 'mgk_child_full_name',
                'label'    => 'Full Name',
                'type'     => 'text',
                'required' => 1,
            ],
            [
                'key'           => 'field_mgk_child_level',
                'name'          => 'mgk_child_current_level',
                'label'         => 'Current Level',
                'type'          => 'taxonomy',
                'taxonomy'      => 'mgk_level',
                'field_type'    => 'select',
                'add_term'      => 0,
                'save_terms'    => 0,
                'load_terms'    => 0,
                'return_format' => 'object',
                'instructions'  => 'Options come from the MGK Levels taxonomy (wp-admin), not hardcoded.',
            ],
            [
                'key'         => 'field_mgk_child_school',
                'name'        => 'mgk_child_school_name',
                'label'       => 'School Name',
                'type'        => 'text',
                'placeholder' => 'Optional',
            ],
            [
                'key'   => 'field_mgk_child_goals',
                'name'  => 'mgk_child_learning_goals',
                'label' => 'Learning Goals',
                'type'  => 'textarea',
                'rows'  => 3,
            ],
        ],
    ] );
}

/**
 * Lead — schema #6. UI for the mg_lead records created at form submit.
 * Existing runtime code writes mgk_lead_* meta; these surface them as an
 * editable admin form. tenant_id dropped; created_at/sla via post_date.
 * NOTE: existing runtime meta keys are mgk_lead_<formfield>; this group
 * exposes the SRS field-level set under stable names for admin editing.
 */
function mgk_register_lead_fields() {
    if ( ! function_exists( 'acf_add_local_field_group' ) ) return;

    acf_add_local_field_group( [
        'key'        => 'group_mgk_lead',
        'title'      => 'Lead Details',
        'location'   => [ [ [ 'param' => 'post_type', 'operator' => '==', 'value' => 'mg_lead' ] ] ],
        'position'   => 'normal',
        'menu_order' => 1,
        'fields'     => [
            [
                'key'   => 'field_mgk_lead_parent_email',
                'name'  => 'mgk_lead_parent_email',
                'label' => 'Parent Email',
                'type'  => 'email',
            ],
            [
                'key'         => 'field_mgk_lead_parent_phone',
                'name'        => 'mgk_lead_parent_phone',
                'label'       => 'Parent Phone (E.164)',
                'type'        => 'text',
                'placeholder' => '+6591234567',
            ],
            [
                'key'   => 'field_mgk_lead_child_name',
                'name'  => 'mgk_lead_child_name',
                'label' => 'Child Name',
                'type'  => 'text',
            ],
            [
                'key'           => 'field_mgk_lead_child_level',
                'name'          => 'mgk_lead_child_level',
                'label'         => 'Child Level',
                'type'          => 'taxonomy',
                'taxonomy'      => 'mgk_level',
                'field_type'    => 'select',
                'add_term'      => 0,
                'save_terms'    => 0,
                'load_terms'    => 0,
                'return_format' => 'object',
            ],
            [
                'key'           => 'field_mgk_lead_subject',
                'name'          => 'mgk_lead_subject',
                'label'         => 'Subject',
                'type'          => 'taxonomy',
                'taxonomy'      => 'mgk_subject',
                'field_type'    => 'select',
                'add_term'      => 0,
                'save_terms'    => 0,
                'load_terms'    => 0,
                'return_format' => 'object',
            ],
            [
                'key'          => 'field_mgk_lead_schedule_pref',
                'name'         => 'mgk_lead_schedule_preference',
                'label'        => 'Schedule Preference',
                'type'         => 'textarea',
                'rows'         => 2,
                'instructions' => 'Days / time / frequency (free text or JSON).',
            ],
            [
                'key'         => 'field_mgk_lead_budget_min',
                'name'        => 'mgk_lead_budget_min_sgd',
                'label'       => 'Budget Min ($)',
                'type'        => 'number',
                'min'         => 0,
            ],
            [
                'key'         => 'field_mgk_lead_budget_max',
                'name'        => 'mgk_lead_budget_max_sgd',
                'label'       => 'Budget Max ($)',
                'type'        => 'number',
                'min'         => 0,
            ],
            [
                'key'           => 'field_mgk_lead_location_type',
                'name'          => 'mgk_lead_location_type',
                'label'         => 'Location Type',
                'type'          => 'select',
                'choices'       => [
                    'HOME_TUTORING' => 'Home tutoring',
                    'ONLINE'        => 'Online',
                    'EITHER'        => 'Either',
                ],
                'return_format' => 'value',
            ],
            [
                'key'         => 'field_mgk_lead_location_area',
                'name'        => 'mgk_lead_location_area',
                'label'       => 'Location Area',
                'type'        => 'text',
            ],
            [
                'key'          => 'field_mgk_lead_note',
                'name'         => 'mgk_lead_note',
                'label'        => 'Note',
                'type'         => 'textarea',
                'rows'         => 3,
                'maxlength'    => 500,
            ],
            [
                'key'           => 'field_mgk_lead_marketing',
                'name'          => 'mgk_lead_marketing_consent',
                'label'         => 'Marketing Consent (PDPA)',
                'type'          => 'true_false',
                'ui'            => 1,
                'default_value' => 0,
            ],
            [
                'key'           => 'field_mgk_lead_state',
                'name'          => 'mgk_lead_state',
                'label'         => 'State',
                'type'          => 'select',
                'choices'       => [
                    'REQUESTED'      => 'Requested',
                    'MATCHING'       => 'Matching',
                    'PROPOSED'       => 'Proposed',
                    'ACCEPTED'       => 'Accepted',
                    'TRIAL_BOOKED'   => 'Trial Booked',
                    'TRIAL_ATTENDED' => 'Trial Attended',
                    'ENROLLED'       => 'Enrolled',
                    'ACTIVE'         => 'Active',
                    'LAPSED'         => 'Lapsed',
                    'CANCELLED'      => 'Cancelled',
                ],
                'default_value' => 'REQUESTED',
                'return_format' => 'value',
                'instructions'  => 'Lead lifecycle state. The booking flow also writes a separate runtime state (mgk_lead_state) for held/booked slots.',
            ],
            [
                'key'           => 'field_mgk_lead_sla_due',
                'name'          => 'mgk_lead_sla_due_at',
                'label'         => 'SLA Due At',
                'type'          => 'date_time_picker',
                'return_format' => 'Y-m-d H:i:s',
                'instructions'  => 'created + 6h.',
            ],
        ],
    ] );
}

/**
 * Proposal — schema #7. tenant_id dropped.
 * lead_id / tutor_id / suggested_plan_id as post_object FKs.
 */
function mgk_register_proposal_fields() {
    if ( ! function_exists( 'acf_add_local_field_group' ) ) return;

    acf_add_local_field_group( [
        'key'        => 'group_mgk_proposal',
        'title'      => 'Proposal Details',
        'location'   => [ [ [ 'param' => 'post_type', 'operator' => '==', 'value' => 'mg_proposal' ] ] ],
        'position'   => 'normal',
        'menu_order' => 1,
        'fields'     => [
            [
                'key'           => 'field_mgk_prop_lead',
                'name'          => 'mgk_prop_lead_id',
                'label'         => 'Lead',
                'type'          => 'post_object',
                'post_type'     => [ 'mg_lead' ],
                'return_format' => 'id',
                'required'      => 1,
            ],
            [
                'key'           => 'field_mgk_prop_tutor',
                'name'          => 'mgk_prop_tutor_id',
                'label'         => 'Tutor',
                'type'          => 'post_object',
                'post_type'     => [ 'mg_teacher' ],
                'return_format' => 'id',
                'required'      => 1,
            ],
            [
                'key'          => 'field_mgk_prop_rank',
                'name'         => 'mgk_prop_rank_order',
                'label'        => 'Rank Order',
                'type'         => 'number',
                'instructions' => '1–5, lower = shown first.',
                'min'          => 1,
                'max'          => 5,
                'default_value'=> 1,
            ],
            [
                'key'          => 'field_mgk_prop_score',
                'name'         => 'mgk_prop_match_score',
                'label'        => 'Match Score',
                'type'         => 'number',
                'min'          => 0,
                'max'          => 100,
                'step'         => '0.1',
            ],
            [
                'key'          => 'field_mgk_prop_reason',
                'name'         => 'mgk_prop_match_reason',
                'label'        => 'Match Reason',
                'type'         => 'textarea',
                'rows'         => 2,
                'instructions' => 'Shown to parent on S08.',
            ],
            [
                'key'           => 'field_mgk_prop_plan',
                'name'          => 'mgk_prop_suggested_plan_id',
                'label'         => 'Suggested Plan',
                'type'          => 'post_object',
                'post_type'     => [ 'mg_plan' ],
                'return_format' => 'id',
            ],
            [
                'key'         => 'field_mgk_prop_rate',
                'name'        => 'mgk_prop_suggested_hourly_rate_sgd',
                'label'       => 'Suggested Hourly Rate ($)',
                'type'        => 'number',
                'min'         => 0,
            ],
            [
                'key'           => 'field_mgk_prop_status',
                'name'          => 'mgk_prop_status',
                'label'         => 'Status',
                'type'          => 'select',
                'choices'       => [
                    'PROPOSED' => 'Proposed',
                    'VIEWED'   => 'Viewed',
                    'SELECTED' => 'Selected',
                    'REJECTED' => 'Rejected',
                    'EXPIRED'  => 'Expired',
                ],
                'default_value' => 'PROPOSED',
                'return_format' => 'value',
            ],
            [
                'key'           => 'field_mgk_prop_expires',
                'name'          => 'mgk_prop_expires_at',
                'label'         => 'Expires At',
                'type'          => 'date_time_picker',
                'return_format' => 'Y-m-d H:i:s',
                'instructions'  => '48h after sent.',
            ],
            [
                'key'           => 'field_mgk_prop_selected_at',
                'name'          => 'mgk_prop_selected_at',
                'label'         => 'Selected At',
                'type'          => 'date_time_picker',
                'return_format' => 'Y-m-d H:i:s',
            ],
        ],
    ] );
}

function mgk_register_review_fields() {
    if ( ! function_exists( 'acf_add_local_field_group' ) ) return;

    acf_add_local_field_group( [
        'key'      => 'group_mgk_review_details',
        'title'    => 'MGK Review Details',
        'location' => [ [ [ 'param' => 'post_type', 'operator' => '==', 'value' => 'mg_review' ] ] ],
        'position' => 'normal',
        'fields'   => [
            [
                'key'           => 'field_mgk_review_teacher_id',
                'name'          => 'mgk_review_teacher_id',
                'label'         => 'Tutor',
                'type'          => 'post_object',
                'post_type'     => [ 'mg_teacher' ],
                'return_format' => 'id',
                'required'      => 1,
                'instructions'  => 'This review appears on the selected tutor profile.',
            ],
            [
                'key'          => 'field_mgk_review_parent_name',
                'name'         => 'mgk_review_parent_name',
                'label'        => 'Parent display name',
                'type'         => 'text',
                'placeholder'  => 'Mrs Chen',
                'required'     => 1,
            ],
            [
                'key'          => 'field_mgk_review_rating',
                'name'         => 'mgk_review_rating',
                'label'        => 'Overall rating',
                'type'         => 'number',
                'min'          => 1,
                'max'          => 5,
                'step'         => '0.1',
                'default_value'=> 5,
            ],
            [
                'key'          => 'field_mgk_review_meta',
                'name'         => 'mgk_review_meta',
                'label'        => 'Review meta line',
                'type'         => 'text',
                'placeholder'  => 'Verified · P5 Math · 2 weeks ago',
            ],
            [
                'key'           => 'field_mgk_review_verified',
                'name'          => 'mgk_review_verified',
                'label'         => 'Verified completed lesson',
                'type'          => 'true_false',
                'ui'            => 1,
                'default_value' => 1,
            ],
            [
                'key'          => 'field_mgk_review_teaching',
                'name'         => 'mgk_review_teaching',
                'label'        => 'Teaching score',
                'type'         => 'number',
                'min'          => 1,
                'max'          => 5,
                'step'         => '0.1',
                'default_value'=> 5,
            ],
            [
                'key'          => 'field_mgk_review_patience',
                'name'         => 'mgk_review_patience',
                'label'        => 'Patience score',
                'type'         => 'number',
                'min'          => 1,
                'max'          => 5,
                'step'         => '0.1',
                'default_value'=> 5,
            ],
            [
                'key'          => 'field_mgk_review_punctuality',
                'name'         => 'mgk_review_punctuality',
                'label'        => 'Punctuality score',
                'type'         => 'number',
                'min'          => 1,
                'max'          => 5,
                'step'         => '0.1',
                'default_value'=> 5,
            ],
            [
                'key'          => 'field_mgk_review_communication',
                'name'         => 'mgk_review_communication',
                'label'        => 'Communication score',
                'type'         => 'number',
                'min'          => 1,
                'max'          => 5,
                'step'         => '0.1',
                'default_value'=> 5,
            ],
            [
                'key'          => 'field_mgk_review_tags',
                'name'         => 'mgk_review_tags',
                'label'        => 'Filter tags',
                'type'         => 'text',
                'placeholder'  => 'PSLE, P5, With photos',
                'instructions' => 'Comma-separated labels used for review filters.',
            ],
            [
                'key'          => 'field_mgk_review_photo_count',
                'name'         => 'mgk_review_photo_count',
                'label'        => 'Photo count',
                'type'         => 'number',
                'min'          => 0,
                'max'          => 4,
                'default_value'=> 0,
            ],
        ],
    ] );
}
