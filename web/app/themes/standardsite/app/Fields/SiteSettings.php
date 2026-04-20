<?php

namespace App\Fields;

use Log1x\AcfComposer\Field;
use StoutLogic\AcfBuilder\FieldsBuilder;

class SiteSettings extends Field
{
    public function fields(): FieldsBuilder
    {
        $settings = new FieldsBuilder('site_settings', [
            'title' => 'Webbplatsinställningar',
        ]);

        $settings
            ->setLocation('options_page', '==', 'acf-options')

            ->addTab('Logotyper')
            ->addImage('prek_logo', [
                'label' => 'Logotyp (header)',
                'return_format' => 'array',
                'preview_size' => 'medium',
                'instructions' => 'Rekommenderad storlek: 200x60px',
            ])
            ->addImage('prek_logo_footer', [
                'label' => 'Logotyp (footer)',
                'return_format' => 'array',
                'preview_size' => 'medium',
            ])

            ->addTab('Kontaktinfo')
            ->addText('prek_phone', ['label' => 'Telefon', 'placeholder' => '013-12 34 56'])
            ->addEmail('prek_email', ['label' => 'E-post', 'placeholder' => 'info@prek.se'])
            ->addText('prek_address', ['label' => 'Gatuadress', 'placeholder' => 'Storgatan 1'])
            ->addText('prek_city', ['label' => 'Ort', 'placeholder' => 'Linköping'])
            ->addTextarea('prek_opening_hours', ['label' => 'Öppettider', 'rows' => 3])

            ->addTab('Sociala medier')
            ->addUrl('prek_instagram', ['label' => 'Instagram URL'])
            ->addUrl('prek_facebook', ['label' => 'Facebook URL'])
            ->addUrl('prek_linkedin', ['label' => 'LinkedIn URL'])

            ->addTab('Footer')
            ->addText('prek_footer_text', ['label' => 'Footer-tagline'])
            ->addTextarea('prek_footer_extra', ['label' => 'Extra footer-text', 'rows' => 3])
            ->addText('prek_org_nr', ['label' => 'Organisationsnummer'])

            ->addTab('SEO')
            ->addTextarea('prek_meta_description', ['label' => 'Meta description', 'rows' => 2, 'maxlength' => 160])
            ->addImage('prek_og_image', ['label' => 'Delningsbild (OG image)', 'return_format' => 'array', 'preview_size' => 'medium']);

        return $settings;
    }
}
