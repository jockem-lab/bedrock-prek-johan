<?php

namespace App\Fields;

use Log1x\AcfComposer\Field;
use StoutLogic\AcfBuilder\FieldsBuilder;

class Kontakt extends Field
{
    public function fields(): FieldsBuilder
    {
        $kontakt = new FieldsBuilder('kontakt', [
            'title' => 'Kontakt',
        ]);

        $kontakt
            ->setLocation('page', '==', '64')

            ->addTab('Hero')
            ->addText('k_hero_rubrik', ['label' => 'Hero-rubrik', 'placeholder' => 'Kontakt'])
            ->addText('k_hero_underrubrik', ['label' => 'Hero-underrubrik'])

            ->addTab('Innehåll')
            ->addText('k_intro_rubrik', ['label' => 'Intro-rubrik', 'placeholder' => 'Kontakta oss'])
            ->addTextarea('k_intro_text', ['label' => 'Intro-text', 'rows' => 3])
            ->addTrueFalse('k_visa_karta', ['label' => 'Visa karta', 'default_value' => 0, 'ui' => 1])
            ->addTextarea('k_karta_embed', [
                'label' => 'Google Maps embed-kod',
                'rows' => 3,
                'conditional_logic' => [[['field' => 'field_k_visa_karta', 'operator' => '==', 'value' => '1']]],
            ])

            ->addTab('Formulär')
            ->addText('k_form_rubrik', ['label' => 'Formulär-rubrik', 'placeholder' => 'Skicka ett meddelande'])
            ->addTextarea('k_form_text', ['label' => 'Formulär-text', 'rows' => 2])
            ->addEmail('k_form_mottagare', ['label' => 'Formulär-mottagare (e-post)']);

        return $kontakt;
    }
}
