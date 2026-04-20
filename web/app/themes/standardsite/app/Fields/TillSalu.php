<?php

namespace App\Fields;

use Log1x\AcfComposer\Field;
use StoutLogic\AcfBuilder\FieldsBuilder;

class TillSalu extends Field
{
    public function fields(): FieldsBuilder
    {
        $tillSalu = new FieldsBuilder('till_salu', [
            'title' => 'Till salu',
        ]);

        $tillSalu
            ->setLocation('page', '==', '62')

            ->addTab('Hero')
            ->addText('ts_hero_rubrik', ['label' => 'Hero-rubrik', 'placeholder' => 'Hem till salu'])
            ->addText('ts_hero_underrubrik', ['label' => 'Hero-underrubrik'])

            ->addTab('Filter')
            ->addTrueFalse('ts_filter_visa', ['label' => 'Visa filterknappar', 'default_value' => 1, 'ui' => 1])
            ->addRepeater('ts_filter_knappar', ['label' => 'Filter-knappar', 'button_label' => 'Lägg till filter'])
                ->addText('label', ['label' => 'Etikett'])
                ->addSelect('value', [
                    'label' => 'Värde',
                    'choices' => [
                        'alla' => 'Alla',
                        'kommande' => 'Kommande',
                        'tillsalu' => 'Till salu',
                        'budgivning' => 'Budgivning pågår',
                        'sald' => 'Sålda',
                    ],
                ])
            ->endRepeater();

        return $tillSalu;
    }
}
