<?php

namespace App\Fields\Partials;

use Log1x\AcfComposer\Partial;
use StoutLogic\AcfBuilder\FieldsBuilder;

class WidthSettings extends Partial
{
    public function fields()
    {
        $builder = new FieldsBuilder('width');
        $builder->addRadio('width', [
            'label' => 'Bredd',
            'layout' => 'horizontal',
            'default_value' => 'max-w-none',
        ])->addChoices(
//            ['max-w-xs' => 'Extrasmall'],
//            ['max-w-sm' => 'Small'],
//            ['max-w-md' => 'Medium'],
//            ['max-w-lg' => 'Large'],
            ['max-w-xl' => 'Halv'],
            ['max-w-none' => 'Full'],
        );
        return $builder;
    }
}