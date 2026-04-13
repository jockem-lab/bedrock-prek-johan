<?php

namespace App\Fields\Partials;

use Log1x\AcfComposer\Partial;
use StoutLogic\AcfBuilder\FieldsBuilder;

class ImagePosition extends Partial
{
    /**
     * The partial field group.
     *
     * @return array
     */
    public function fields()
    {
        $builder = new FieldsBuilder('image_position');

        $builder
            ->addRadio('object-position-horizontal', [
                'label' => 'Bildposition, horisontell',
                'instructions' => 'Vilken del av bilden som ska behållas vid eventuell beskärning',
                'default_value' => 'center',
            ])->addChoices(['top' => 'Övre'], ['center' => 'Mitten'], ['bottom' => 'Nedre']);

        return $builder;
    }
}
