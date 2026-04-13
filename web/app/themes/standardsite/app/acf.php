<?php

namespace App;

/**
 * Registrera ACF-fältgrupper
 */
add_action('acf/init', function () {
    if (!function_exists('acf_add_local_field_group')) return;

    // =====================
    // HERO-INSTÄLLNINGAR
    // =====================
    acf_add_local_field_group([
        'key'    => 'group_hero',
        'title'  => 'Hero-inställningar',
        'fields' => [
            [
                'key'   => 'field_hero_type',
                'label' => 'Hero-typ',
                'name'  => 'hero_type',
                'type'  => 'select',
                'choices' => [
                    'slides' => 'Bildspel',
                    'video'  => 'Video',
                ],
                'default_value' => 'slides',
            ],
            [
                'key'               => 'field_hero_slides',
                'label'             => 'Bilder',
                'name'              => 'hero_slides',
                'type'              => 'repeater',
                'conditional_logic' => [
                    [['field' => 'field_hero_type', 'operator' => '==', 'value' => 'slides']]
                ],
                'sub_fields' => [
                    [
                        'key'   => 'field_hero_slide_image',
                        'label' => 'Bild',
                        'name'  => 'image',
                        'type'  => 'image',
                        'return_format' => 'array',
                        'preview_size'  => 'medium',
                    ],
                    [
                        'key'   => 'field_hero_slide_title',
                        'label' => 'Rubrik',
                        'name'  => 'title',
                        'type'  => 'text',
                    ],
                    [
                        'key'   => 'field_hero_slide_subtitle',
                        'label' => 'Underrubrik',
                        'name'  => 'subtitle',
                        'type'  => 'text',
                    ],
                ],
            ],
            [
                'key'               => 'field_hero_video',
                'label'             => 'Video URL',
                'name'              => 'hero_video',
                'type'              => 'url',
                'conditional_logic' => [
                    [['field' => 'field_hero_type', 'operator' => '==', 'value' => 'video']]
                ],
            ],
        ],
        'location' => [
            [['param' => 'page_type', 'operator' => '==', 'value' => 'front_page']],
        ],
        'menu_order' => 0,
    ]);

    // =====================
    // WEBBPLATSINSTÄLLNINGAR
    // =====================
    acf_add_local_field_group([
        'key'    => 'group_site_settings',
        'title'  => 'Webbplatsinställningar',
        'fields' => [
            [
                'key'   => 'field_site_logo',
                'label' => 'Logotyp',
                'name'  => 'prek_logo',
                'type'  => 'image',
                'return_format' => 'array',
                'preview_size'  => 'medium',
            ],
            [
                'key'   => 'field_site_logo_footer',
                'label' => 'Logotyp (footer)',
                'name'  => 'prek_logo_footer',
                'type'  => 'image',
                'return_format' => 'array',
                'preview_size'  => 'medium',
            ],
            [
                'key'          => 'field_site_footer_text',
                'label'        => 'Footer-text',
                'name'         => 'prek_footer_text',
                'type'         => 'text',
                'placeholder'  => 'Erfarna mäklare sedan 2001',
            ],
            [
                'key'   => 'field_site_phone',
                'label' => 'Telefon',
                'name'  => 'prek_phone',
                'type'  => 'text',
            ],
            [
                'key'   => 'field_site_email',
                'label' => 'E-post',
                'name'  => 'prek_email',
                'type'  => 'email',
            ],
            [
                'key'   => 'field_site_address',
                'label' => 'Adress',
                'name'  => 'prek_address',
                'type'  => 'textarea',
                'rows'  => 3,
            ],
            [
                'key'   => 'field_site_opening_hours',
                'label' => 'Öppettider',
                'name'  => 'prek_opening_hours',
                'type'  => 'textarea',
                'rows'  => 3,
            ],
            [
                'key'   => 'field_site_instagram',
                'label' => 'Instagram URL',
                'name'  => 'prek_instagram',
                'type'  => 'url',
            ],
            [
                'key'   => 'field_site_facebook',
                'label' => 'Facebook URL',
                'name'  => 'prek_facebook',
                'type'  => 'url',
            ],
            [
                'key'     => 'field_theme_colors',
                'label'   => 'Färgtema',
                'name'    => 'theme-colors',
                'type'    => 'group',
                'sub_fields' => [
                    [
                        'key'   => 'field_color_accent',
                        'label' => 'Accentfärg',
                        'name'  => 'theme-colors_theme-custom-background',
                        'type'  => 'color_picker',
                        'default_value' => '#1a1a1a',
                    ],
                ],
            ],
        ],
        'location' => [
            [['param' => 'options_page', 'operator' => '==', 'value' => 'acf-options']],
        ],
        'menu_order' => 0,
    ]);

    // =====================
    // OM OSS-SIDAN
    // =====================
    acf_add_local_field_group([
        'key'    => 'group_om_oss',
        'title'  => 'Om oss – Innehåll',
        'fields' => [
            [
                'key'   => 'field_om_oss_rubrik',
                'label' => 'Huvudrubrik',
                'name'  => 'om_oss_rubrik',
                'type'  => 'text',
                'default_value' => 'Vi hittar rätt hem för dig',
            ],
            [
                'key'   => 'field_om_oss_intro',
                'label' => 'Introtext',
                'name'  => 'om_oss_intro',
                'type'  => 'textarea',
                'rows'  => 4,
            ],
            [
                'key'        => 'field_om_oss_blocks',
                'label'      => 'Informationsblock',
                'name'       => 'om_oss_blocks',
                'type'       => 'repeater',
                'min'        => 0,
                'max'        => 6,
                'sub_fields' => [
                    [
                        'key'   => 'field_om_oss_block_rubrik',
                        'label' => 'Rubrik',
                        'name'  => 'rubrik',
                        'type'  => 'text',
                    ],
                    [
                        'key'   => 'field_om_oss_block_text',
                        'label' => 'Text',
                        'name'  => 'text',
                        'type'  => 'textarea',
                        'rows'  => 3,
                    ],
                ],
            ],
        ],
        'location' => [
            [['param' => 'page', 'operator' => '==', 'value' => get_page_by_path('om-oss') ? get_page_by_path('om-oss')->ID : 0]],
        ],
        'menu_order' => 0,
    ]);

    // =====================
    // KONTAKTSIDAN
    // =====================
    acf_add_local_field_group([
        'key'    => 'group_kontakt',
        'title'  => 'Kontakt – Innehåll',
        'fields' => [
            [
                'key'   => 'field_kontakt_rubrik',
                'label' => 'Rubrik',
                'name'  => 'kontakt_rubrik',
                'type'  => 'text',
                'default_value' => 'Kontakta oss',
            ],
            [
                'key'   => 'field_kontakt_underrubrik',
                'label' => 'Underrubrik',
                'name'  => 'kontakt_underrubrik',
                'type'  => 'text',
            ],
            [
                'key'   => 'field_kontakt_intro',
                'label' => 'Introtext',
                'name'  => 'kontakt_intro',
                'type'  => 'textarea',
                'rows'  => 3,
            ],
        ],
        'location' => [
            [['param' => 'page', 'operator' => '==', 'value' => get_page_by_path('kontakt') ? get_page_by_path('kontakt')->ID : 0]],
        ],
        'menu_order' => 0,
    ]);

    // =====================
    // TILL SALU-SIDAN
    // =====================
    acf_add_local_field_group([
        'key'    => 'group_till_salu',
        'title'  => 'Till salu – Innehåll',
        'fields' => [
            [
                'key'   => 'field_till_salu_rubrik',
                'label' => 'Sidrubrik',
                'name'  => 'till_salu_rubrik',
                'type'  => 'text',
                'default_value' => 'Hem till salu',
            ],
            [
                'key'   => 'field_till_salu_underrubrik',
                'label' => 'Underrubrik',
                'name'  => 'till_salu_underrubrik',
                'type'  => 'text',
                'default_value' => 'Linköping och Östergötland',
            ],
        ],
        'location' => [
            [['param' => 'page', 'operator' => '==', 'value' => get_page_by_path('objekt') ? get_page_by_path('objekt')->ID : 0]],
        ],
        'menu_order' => 0,
    ]);

});

// Registrera ACF options-sida på init
add_action('init', function() {
    if (function_exists('acf_add_options_page')) {
        acf_add_options_page([
            'page_title' => 'Webbplatsinställningar',
            'menu_title' => 'Inställningar',
            'menu_slug'  => 'acf-options',
            'capability' => 'edit_posts',
            'redirect'   => false,
            'icon_url'   => 'dashicons-admin-settings',
            'position'   => 2,
        ]);
    }
}, 20);
