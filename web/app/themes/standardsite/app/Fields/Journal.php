<?php

namespace App\Fields;

use Log1x\AcfComposer\Field;
use StoutLogic\AcfBuilder\FieldsBuilder;

class Journal extends Field
{
    public function fields(): FieldsBuilder
    {
        $journal = new FieldsBuilder('journal', [
            'title' => 'Journalartikel',
        ]);

        $journal
            ->setLocation('post_type', '==', 'journal')

            ->addRadio('j_hero_typ', [
                'label' => 'Hero-typ',
                'choices' => ['bild' => 'Bild', 'video' => 'Video'],
                'default_value' => 'bild',
                'layout' => 'horizontal',
            ])
            ->addImage('j_hero_bild', [
                'label' => 'Hero-bild',
                'return_format' => 'array',
                'conditional_logic' => [[['field' => 'field_j_hero_typ', 'operator' => '==', 'value' => 'bild']]],
            ])
            ->addUrl('j_hero_video', [
                'label' => 'Video-URL (YouTube/Vimeo)',
                'conditional_logic' => [[['field' => 'field_j_hero_typ', 'operator' => '==', 'value' => 'video']]],
            ])
            ->addNumber('j_lasttid', ['label' => 'Lästid (minuter)'])
            ->addText('j_kategori', ['label' => 'Kategori']);

        return $journal;
    }
}
