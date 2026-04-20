<?php

namespace App\Fields;

use Log1x\AcfComposer\Field;
use StoutLogic\AcfBuilder\FieldsBuilder;

class FrontPage extends Field
{
    public function fields(): FieldsBuilder
    {
        $fp = new FieldsBuilder('front_page', [
            'title' => 'Startsida',
        ]);

        $fp
            ->setLocation('page_type', '==', 'front_page')

            ->addTab('Hero')
            ->addSelect('hero_type', [
                'label' => 'Hero-typ',
                'choices' => ['slides' => 'Bildspel', 'video' => 'Video'],
                'default_value' => 'slides',
            ])
            ->addRepeater('hero_slides', [
                'label' => 'Bilder',
                'button_label' => 'Lägg till bild',
                'conditional_logic' => [[['field' => 'field_hero_type', 'operator' => '==', 'value' => 'slides']]],
            ])
                ->addImage('image', ['label' => 'Bild', 'return_format' => 'array', 'preview_size' => 'medium'])
                ->addText('title', ['label' => 'Rubrik'])
                ->addText('subtitle', ['label' => 'Underrubrik'])
            ->endRepeater()
            ->addUrl('hero_video', [
                'label' => 'Video URL',
                'conditional_logic' => [[['field' => 'field_hero_type', 'operator' => '==', 'value' => 'video']]],
            ])

            ->addTab('Intro-sektion')
            ->addText('fp_intro_rubrik', ['label' => 'Rubrik', 'placeholder' => 'Hitta ditt nästa hem'])
            ->addTextarea('fp_intro_text', ['label' => 'Text', 'rows' => 4])
            ->addText('fp_intro_knapp_text', ['label' => 'Knapp-text'])
            ->addUrl('fp_intro_knapp_url', ['label' => 'Knapp-länk'])

            ->addTab('Objekt-sektion')
            ->addText('fp_listings_rubrik', ['label' => 'Rubrik', 'placeholder' => 'Aktuella objekt'])
            ->addNumber('fp_listings_antal', ['label' => 'Antal objekt att visa', 'default_value' => 6, 'min' => 1, 'max' => 12])

            ->addTab('Värdering-sektion')
            ->addTrueFalse('fp_valuation_visa', ['label' => 'Visa värderingssektion', 'default_value' => 1, 'ui' => 1])
            ->addText('fp_valuation_rubrik', ['label' => 'Rubrik', 'placeholder' => 'Gratis värdebedömning'])
            ->addTextarea('fp_valuation_text', ['label' => 'Text', 'rows' => 3])
            ->addText('fp_valuation_knapp', ['label' => 'Knapp-text', 'placeholder' => 'Boka värdering']);

        return $fp;
    }
}
