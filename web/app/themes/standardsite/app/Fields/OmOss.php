<?php

namespace App\Fields;

use App\Fields\Partials\Team;
use Log1x\AcfComposer\Field;
use StoutLogic\AcfBuilder\FieldsBuilder;

class OmOss extends Field
{
    public function fields(): FieldsBuilder
    {
        $omOss = new FieldsBuilder('om_oss', [
            'title' => 'Om oss',
        ]);

        $omOss
            ->setLocation('page', '==', '63')

            ->addTab('Hero')
            ->addText('oo_hero_rubrik', ['label' => 'Hero-rubrik', 'placeholder' => 'Om oss'])
            ->addText('oo_hero_underrubrik', ['label' => 'Hero-underrubrik'])
            ->addImage('oo_hero_bild', ['label' => 'Hero-bild (override)', 'return_format' => 'array'])

            ->addTab('Intro')
            ->addText('oo_intro_rubrik', ['label' => 'Rubrik', 'placeholder' => 'Med hjärtat i varje affär'])
            ->addWysiwyg('oo_intro_text', ['label' => 'Introtext', 'tabs' => 'visual', 'toolbar' => 'basic'])
            ->addRepeater('oo_blocks', ['label' => 'Informationsblock', 'button_label' => 'Lägg till block', 'min' => 0, 'max' => 6])
                ->addText('rubrik', ['label' => 'Rubrik'])
                ->addTextarea('text', ['label' => 'Text', 'rows' => 3])
                ->addImage('ikon', ['label' => 'Ikon', 'return_format' => 'array'])
            ->endRepeater()

            ->addTab('Värderingar')
            ->addText('oo_values_rubrik', ['label' => 'Rubrik', 'placeholder' => 'Våra värderingar'])
            ->addRepeater('oo_values', ['label' => 'Värderingar', 'button_label' => 'Lägg till värdering', 'min' => 0, 'max' => 6])
                ->addText('rubrik', ['label' => 'Rubrik'])
                ->addTextarea('text', ['label' => 'Text', 'rows' => 2])
            ->endRepeater()

            ->addTab('Team-sektion')
            ->addTrueFalse('oo_team_visa', ['label' => 'Visa team-sektion', 'default_value' => 1, 'ui' => 1])
            ->addText('oo_team_rubrik', ['label' => 'Team-rubrik', 'placeholder' => 'Vårt team'])
            ->addFields($this->get(Team::class));

        return $omOss;
    }
}
