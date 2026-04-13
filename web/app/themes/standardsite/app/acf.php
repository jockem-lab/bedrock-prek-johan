<?php

namespace App;

add_action('acf/init', function () {
    if (!function_exists('acf_add_local_field_group')) return;

    // =====================
    // WEBBPLATSINSTÄLLNINGAR (Options)
    // =====================
    acf_add_local_field_group([
        'key'    => 'group_site_settings',
        'title'  => 'Webbplatsinställningar',
        'fields' => [
            // Logotyper
            [
                'key'   => 'field_site_tab_logo',
                'label' => 'Logotyper',
                'type'  => 'tab',
            ],
            [
                'key'           => 'field_site_logo',
                'label'         => 'Logotyp (header)',
                'name'          => 'prek_logo',
                'type'          => 'image',
                'return_format' => 'array',
                'preview_size'  => 'medium',
                'instructions'  => 'Rekommenderad storlek: 200x60px',
            ],
            [
                'key'           => 'field_site_logo_footer',
                'label'         => 'Logotyp (footer)',
                'name'          => 'prek_logo_footer',
                'type'          => 'image',
                'return_format' => 'array',
                'preview_size'  => 'medium',
                'instructions'  => 'Visas i vit/inverterad version i footern',
            ],
            // Kontaktinfo
            [
                'key'   => 'field_site_tab_contact',
                'label' => 'Kontaktinfo',
                'type'  => 'tab',
            ],
            [
                'key'         => 'field_site_phone',
                'label'       => 'Telefon',
                'name'        => 'prek_phone',
                'type'        => 'text',
                'placeholder' => '013-12 34 56',
            ],
            [
                'key'         => 'field_site_email',
                'label'       => 'E-post',
                'name'        => 'prek_email',
                'type'        => 'email',
                'placeholder' => 'info@prek.se',
            ],
            [
                'key'         => 'field_site_address',
                'label'       => 'Gatuadress',
                'name'        => 'prek_address',
                'type'        => 'text',
                'placeholder' => 'Storgatan 1',
            ],
            [
                'key'         => 'field_site_city',
                'label'       => 'Ort',
                'name'        => 'prek_city',
                'type'        => 'text',
                'placeholder' => 'Linköping',
            ],
            [
                'key'         => 'field_site_opening_hours',
                'label'       => 'Öppettider',
                'name'        => 'prek_opening_hours',
                'type'        => 'textarea',
                'rows'        => 3,
                'placeholder' => "Mån–Fre: 09–17\nLör: 10–14",
            ],
            // Sociala medier
            [
                'key'   => 'field_site_tab_social',
                'label' => 'Sociala medier',
                'type'  => 'tab',
            ],
            [
                'key'         => 'field_site_instagram',
                'label'       => 'Instagram URL',
                'name'        => 'prek_instagram',
                'type'        => 'url',
                'placeholder' => 'https://instagram.com/prek',
            ],
            [
                'key'         => 'field_site_facebook',
                'label'       => 'Facebook URL',
                'name'        => 'prek_facebook',
                'type'        => 'url',
                'placeholder' => 'https://facebook.com/prek',
            ],
            [
                'key'         => 'field_site_linkedin',
                'label'       => 'LinkedIn URL',
                'name'        => 'prek_linkedin',
                'type'        => 'url',
            ],
            // Footer
            [
                'key'   => 'field_site_tab_footer',
                'label' => 'Footer',
                'type'  => 'tab',
            ],
            [
                'key'         => 'field_site_footer_text',
                'label'       => 'Footer-tagline',
                'name'        => 'prek_footer_text',
                'type'        => 'text',
                'placeholder' => 'Erfarna mäklare sedan 2001',
            ],
            [
                'key'         => 'field_site_footer_extra',
                'label'       => 'Extra footer-text',
                'name'        => 'prek_footer_extra',
                'type'        => 'textarea',
                'rows'        => 3,
            ],
            [
                'key'         => 'field_site_org_nr',
                'label'       => 'Organisationsnummer',
                'name'        => 'prek_org_nr',
                'type'        => 'text',
                'placeholder' => '556XXX-XXXX',
            ],
            // SEO
            [
                'key'   => 'field_site_tab_seo',
                'label' => 'SEO',
                'type'  => 'tab',
            ],
            [
                'key'         => 'field_site_meta_desc',
                'label'       => 'Meta description',
                'name'        => 'prek_meta_description',
                'type'        => 'textarea',
                'rows'        => 2,
                'maxlength'   => 160,
                'instructions' => 'Max 160 tecken. Visas i sökresultat.',
            ],
            [
                'key'           => 'field_site_og_image',
                'label'         => 'Delningsbild (OG image)',
                'name'          => 'prek_og_image',
                'type'          => 'image',
                'return_format' => 'array',
                'preview_size'  => 'medium',
                'instructions'  => 'Visas när sidan delas på sociala medier. 1200x630px.',
            ],
        ],
        'location' => [
            [['param' => 'options_page', 'operator' => '==', 'value' => 'acf-options']],
        ],
        'menu_order' => 0,
    ]);

    // =====================
    // STARTSIDA
    // =====================
    acf_add_local_field_group([
        'key'    => 'group_front_page',
        'title'  => 'Startsida',
        'fields' => [
            // Hero
            [
                'key'   => 'field_fp_tab_hero',
                'label' => 'Hero',
                'type'  => 'tab',
            ],
            [
                'key'           => 'field_hero_type',
                'label'         => 'Hero-typ',
                'name'          => 'hero_type',
                'type'          => 'select',
                'choices'       => ['slides' => 'Bildspel', 'video' => 'Video'],
                'default_value' => 'slides',
            ],
            [
                'key'               => 'field_hero_slides',
                'label'             => 'Bilder',
                'name'              => 'hero_slides',
                'type'              => 'repeater',
                'button_label'      => 'Lägg till bild',
                'conditional_logic' => [[['field' => 'field_hero_type', 'operator' => '==', 'value' => 'slides']]],
                'sub_fields'        => [
                    [
                        'key'           => 'field_hero_slide_image',
                        'label'         => 'Bild',
                        'name'          => 'image',
                        'type'          => 'image',
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
                'conditional_logic' => [[['field' => 'field_hero_type', 'operator' => '==', 'value' => 'video']]],
            ],
            // Intro
            [
                'key'   => 'field_fp_tab_intro',
                'label' => 'Intro-sektion',
                'type'  => 'tab',
            ],
            [
                'key'         => 'field_fp_intro_rubrik',
                'label'       => 'Rubrik',
                'name'        => 'fp_intro_rubrik',
                'type'        => 'text',
                'placeholder' => 'Hitta ditt nästa hem',
            ],
            [
                'key'         => 'field_fp_intro_text',
                'label'       => 'Text',
                'name'        => 'fp_intro_text',
                'type'        => 'textarea',
                'rows'        => 4,
            ],
            [
                'key'         => 'field_fp_intro_knapp_text',
                'label'       => 'Knapp-text',
                'name'        => 'fp_intro_knapp_text',
                'type'        => 'text',
                'placeholder' => 'Se alla objekt',
            ],
            [
                'key'         => 'field_fp_intro_knapp_url',
                'label'       => 'Knapp-länk',
                'name'        => 'fp_intro_knapp_url',
                'type'        => 'url',
            ],
            // Objekt-sektion
            [
                'key'   => 'field_fp_tab_listings',
                'label' => 'Objekt-sektion',
                'type'  => 'tab',
            ],
            [
                'key'         => 'field_fp_listings_rubrik',
                'label'       => 'Rubrik',
                'name'        => 'fp_listings_rubrik',
                'type'        => 'text',
                'placeholder' => 'Aktuella objekt',
            ],
            [
                'key'          => 'field_fp_listings_antal',
                'label'        => 'Antal objekt att visa',
                'name'         => 'fp_listings_antal',
                'type'         => 'number',
                'default_value' => 6,
                'min'          => 1,
                'max'          => 12,
            ],
            // Värdering-sektion
            [
                'key'   => 'field_fp_tab_valuation',
                'label' => 'Värdering-sektion',
                'type'  => 'tab',
            ],
            [
                'key'     => 'field_fp_valuation_visa',
                'label'   => 'Visa värderingssektion',
                'name'    => 'fp_valuation_visa',
                'type'    => 'true_false',
                'default_value' => 1,
                'ui'      => 1,
            ],
            [
                'key'         => 'field_fp_valuation_rubrik',
                'label'       => 'Rubrik',
                'name'        => 'fp_valuation_rubrik',
                'type'        => 'text',
                'placeholder' => 'Gratis värdebedömning',
            ],
            [
                'key'         => 'field_fp_valuation_text',
                'label'       => 'Text',
                'name'        => 'fp_valuation_text',
                'type'        => 'textarea',
                'rows'        => 3,
            ],
            [
                'key'         => 'field_fp_valuation_knapp',
                'label'       => 'Knapp-text',
                'name'        => 'fp_valuation_knapp',
                'type'        => 'text',
                'placeholder' => 'Boka värdering',
            ],
        ],
        'location' => [
            [['param' => 'page_type', 'operator' => '==', 'value' => 'front_page']],
        ],
        'menu_order' => 0,
    ]);

    // =====================
    // OM OSS
    // =====================
    acf_add_local_field_group([
        'key'    => 'group_om_oss',
        'title'  => 'Om oss',
        'fields' => [
            [
                'key'   => 'field_oo_tab_hero',
                'label' => 'Hero',
                'type'  => 'tab',
            ],
            [
                'key'         => 'field_oo_hero_rubrik',
                'label'       => 'Hero-rubrik',
                'name'        => 'oo_hero_rubrik',
                'type'        => 'text',
                'placeholder' => 'Om oss',
            ],
            [
                'key'         => 'field_oo_hero_underrubrik',
                'label'       => 'Hero-underrubrik',
                'name'        => 'oo_hero_underrubrik',
                'type'        => 'text',
                'placeholder' => 'Erfarna mäklare med lokal kännedom',
            ],
            [
                'key'           => 'field_oo_hero_bild',
                'label'         => 'Hero-bild (override)',
                'name'          => 'oo_hero_bild',
                'type'          => 'image',
                'return_format' => 'array',
                'instructions'  => 'Lämna tom för att använda standard-bildspelet',
            ],
            [
                'key'   => 'field_oo_tab_intro',
                'label' => 'Intro',
                'type'  => 'tab',
            ],
            [
                'key'         => 'field_oo_intro_rubrik',
                'label'       => 'Rubrik',
                'name'        => 'oo_intro_rubrik',
                'type'        => 'text',
                'placeholder' => 'Vi hittar rätt hem för dig',
            ],
            [
                'key'         => 'field_oo_intro_text',
                'label'       => 'Introtext',
                'name'        => 'oo_intro_text',
                'type'        => 'wysiwyg',
                'tabs'        => 'visual',
                'toolbar'     => 'basic',
            ],
            [
                'key'          => 'field_oo_blocks',
                'label'        => 'Informationsblock',
                'name'         => 'oo_blocks',
                'type'         => 'repeater',
                'min'          => 0,
                'max'          => 6,
                'button_label' => 'Lägg till block',
                'sub_fields'   => [
                    [
                        'key'   => 'field_oo_block_rubrik',
                        'label' => 'Rubrik',
                        'name'  => 'rubrik',
                        'type'  => 'text',
                    ],
                    [
                        'key'   => 'field_oo_block_text',
                        'label' => 'Text',
                        'name'  => 'text',
                        'type'  => 'textarea',
                        'rows'  => 3,
                    ],
                    [
                        'key'           => 'field_oo_block_ikon',
                        'label'         => 'Ikon (valfri bild)',
                        'name'          => 'ikon',
                        'type'          => 'image',
                        'return_format' => 'array',
                    ],
                ],
            ],
            [
                'key'   => 'field_oo_tab_values',
                'label' => 'Värderingar',
                'type'  => 'tab',
            ],
            [
                'key'         => 'field_oo_values_rubrik',
                'label'       => 'Rubrik',
                'name'        => 'oo_values_rubrik',
                'type'        => 'text',
                'placeholder' => 'Våra värderingar',
            ],
            [
                'key'          => 'field_oo_values',
                'label'        => 'Värderingar',
                'name'         => 'oo_values',
                'type'         => 'repeater',
                'min'          => 0,
                'max'          => 6,
                'button_label' => 'Lägg till värdering',
                'sub_fields'   => [
                    [
                        'key'   => 'field_oo_value_rubrik',
                        'label' => 'Rubrik',
                        'name'  => 'rubrik',
                        'type'  => 'text',
                    ],
                    [
                        'key'   => 'field_oo_value_text',
                        'label' => 'Text',
                        'name'  => 'text',
                        'type'  => 'textarea',
                        'rows'  => 2,
                    ],
                ],
            ],
            [
                'key'   => 'field_oo_tab_team',
                'label' => 'Team-sektion',
                'type'  => 'tab',
            ],
            [
                'key'     => 'field_oo_team_visa',
                'label'   => 'Visa team-sektion',
                'name'    => 'oo_team_visa',
                'type'    => 'true_false',
                'default_value' => 1,
                'ui'      => 1,
            ],
            [
                'key'         => 'field_oo_team_rubrik',
                'label'       => 'Team-rubrik',
                'name'        => 'oo_team_rubrik',
                'type'        => 'text',
                'placeholder' => 'Vårt team',
            ],
        ],
        'location' => [
            [['param' => 'page', 'operator' => '==', 'value' => '63']],
        ],
        'menu_order' => 0,
    ]);

    // =====================
    // KONTAKT
    // =====================
    acf_add_local_field_group([
        'key'    => 'group_kontakt',
        'title'  => 'Kontakt',
        'fields' => [
            [
                'key'   => 'field_k_tab_hero',
                'label' => 'Hero',
                'type'  => 'tab',
            ],
            [
                'key'         => 'field_k_hero_rubrik',
                'label'       => 'Hero-rubrik',
                'name'        => 'k_hero_rubrik',
                'type'        => 'text',
                'placeholder' => 'Kontakt',
            ],
            [
                'key'         => 'field_k_hero_underrubrik',
                'label'       => 'Hero-underrubrik',
                'name'        => 'k_hero_underrubrik',
                'type'        => 'text',
                'placeholder' => 'Vi finns här för dig',
            ],
            [
                'key'   => 'field_k_tab_content',
                'label' => 'Innehåll',
                'type'  => 'tab',
            ],
            [
                'key'         => 'field_k_intro_rubrik',
                'label'       => 'Intro-rubrik',
                'name'        => 'k_intro_rubrik',
                'type'        => 'text',
                'placeholder' => 'Kontakta oss',
            ],
            [
                'key'         => 'field_k_intro_text',
                'label'       => 'Intro-text',
                'name'        => 'k_intro_text',
                'type'        => 'textarea',
                'rows'        => 3,
            ],
            [
                'key'     => 'field_k_visa_karta',
                'label'   => 'Visa karta',
                'name'    => 'k_visa_karta',
                'type'    => 'true_false',
                'default_value' => 0,
                'ui'      => 1,
            ],
            [
                'key'         => 'field_k_karta_embed',
                'label'       => 'Google Maps embed-kod',
                'name'        => 'k_karta_embed',
                'type'        => 'textarea',
                'rows'        => 3,
                'instructions' => 'Klistra in embed-kod från Google Maps',
                'conditional_logic' => [[['field' => 'field_k_visa_karta', 'operator' => '==', 'value' => '1']]],
            ],
            [
                'key'   => 'field_k_tab_form',
                'label' => 'Formulär',
                'type'  => 'tab',
            ],
            [
                'key'         => 'field_k_form_rubrik',
                'label'       => 'Formulär-rubrik',
                'name'        => 'k_form_rubrik',
                'type'        => 'text',
                'placeholder' => 'Skicka ett meddelande',
            ],
            [
                'key'         => 'field_k_form_text',
                'label'       => 'Formulär-text',
                'name'        => 'k_form_text',
                'type'        => 'textarea',
                'rows'        => 2,
            ],
            [
                'key'         => 'field_k_form_mottagare',
                'label'       => 'Formulär-mottagare (e-post)',
                'name'        => 'k_form_mottagare',
                'type'        => 'email',
                'instructions' => 'Hit skickas formuläret',
            ],
        ],
        'location' => [
            [['param' => 'page', 'operator' => '==', 'value' => '64']],
        ],
        'menu_order' => 0,
    ]);

    // =====================
    // TILL SALU
    // =====================
    acf_add_local_field_group([
        'key'    => 'group_till_salu',
        'title'  => 'Till salu',
        'fields' => [
            [
                'key'   => 'field_ts_tab_hero',
                'label' => 'Hero',
                'type'  => 'tab',
            ],
            [
                'key'         => 'field_ts_hero_rubrik',
                'label'       => 'Hero-rubrik',
                'name'        => 'ts_hero_rubrik',
                'type'        => 'text',
                'placeholder' => 'Hem till salu',
            ],
            [
                'key'         => 'field_ts_hero_underrubrik',
                'label'       => 'Hero-underrubrik',
                'name'        => 'ts_hero_underrubrik',
                'type'        => 'text',
                'placeholder' => 'Linköping och Östergötland',
            ],
            [
                'key'   => 'field_ts_tab_filter',
                'label' => 'Filter',
                'type'  => 'tab',
            ],
            [
                'key'         => 'field_ts_filter_visa',
                'label'       => 'Visa filterknappar',
                'name'        => 'ts_filter_visa',
                'type'        => 'true_false',
                'default_value' => 1,
                'ui'          => 1,
            ],
            [
                'key'          => 'field_ts_filter_knappar',
                'label'        => 'Filter-knappar',
                'name'         => 'ts_filter_knappar',
                'type'         => 'repeater',
                'instructions' => 'Anpassa vilka filter som visas',
                'button_label' => 'Lägg till filter',
                'sub_fields'   => [
                    [
                        'key'   => 'field_ts_filter_label',
                        'label' => 'Etikett',
                        'name'  => 'label',
                        'type'  => 'text',
                    ],
                    [
                        'key'     => 'field_ts_filter_value',
                        'label'   => 'Värde',
                        'name'    => 'value',
                        'type'    => 'select',
                        'choices' => [
                            'alla'      => 'Alla',
                            'kommande'  => 'Kommande',
                            'tillsalu'  => 'Till salu',
                            'budgivning' => 'Budgivning pågår',
                            'sald'      => 'Sålda',
                        ],
                    ],
                ],
            ],
        ],
        'location' => [
            [['param' => 'page', 'operator' => '==', 'value' => '62']],
        ],
        'menu_order' => 0,
    ]);

});

// Registrera ACF options-sida
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
