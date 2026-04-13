<?php

namespace App\Fields\Partials;

use Log1x\AcfComposer\Partial;
use StoutLogic\AcfBuilder\FieldsBuilder;

class AdvancedLayoutSettings extends Partial
{
    public function fields()
    {
        $builder = new FieldsBuilder('als');
        $builder->addTrueFalse('als-show', [
            'label' => 'Avancerade alternativ'
        ]);
        $builder->addGroup('als_group', [
            'label' => ''
        ])
        ->conditional('als-show', '==', '1')
            ->addRadio('visibility', [
                'label' => 'Synlighet',
                'default_value' => '',
            ])->addChoices(
                [''        => 'Visa alltid'],
                ['desktop' => 'Visa endast på dator'],
                ['mobile'  => 'Visa endast på mobil']
            )
            ->addText('als_id', [
                'label' => 'Id på blocket'
            ])
        ->addCheckbox('als_cssclasses', [
            'label' => 'Extra css-klasser',
            'allow_custom' => 1,
        ]);
//        $builder->addRadio('advance', [
//            'label' => 'Bredd',
//            'layout' => 'horizontal',
//            'default_value' => 'max-w-xl',
//        ])->addChoices(
////            ['max-w-xs' => 'Extrasmall'],
////            ['max-w-sm' => 'Small'],
////            ['max-w-md' => 'Medium'],
////            ['max-w-lg' => 'Large'],
//            ['max-w-xl' => 'Halv'],
//            ['max-w-none' => 'Full']
//        );

        return $builder;
    }
}
