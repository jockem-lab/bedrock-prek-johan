<?php

namespace App\Fields\Partials;

use Log1x\AcfComposer\Partial;
use StoutLogic\AcfBuilder\FieldsBuilder;

class FluidSettings extends Partial
{
    public function fields()
    {
        $builder = new FieldsBuilder('fluidsettings');
        $builder->addRadio('background', [
            'label' => 'Bakgrund',
            'layout' => 'horizontal',
            'default_value' => 'white',
        ])->addChoices(
            ['white' => 'Vit'],
            ['quill-grey' => 'Ljusgrå'],
            ['theme-secondary' => 'Mörkgrå'],
            ['custom' => 'Egen']
        );
        $builder->addGroup('custom-colors', [
            'label' => 'Färger',
            'layout' => 'row'
        ])
        ->conditional('background', '==', 'custom')
        ->addColorPicker('background', [
            'label' => 'Bakgrund'
        ])
        ->addColorPicker('color', [
            'label' => 'Text'
        ]);

        return $builder;
    }
}