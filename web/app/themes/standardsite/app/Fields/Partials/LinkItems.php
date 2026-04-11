<?php

namespace App\Fields\Partials;

use Log1x\AcfComposer\Partial;
use StoutLogic\AcfBuilder\FieldsBuilder;

class LinkItems extends Partial
{
    /**
     * The partial field group.
     *
     * @return array
     */
    public function fields()
    {
        $builder = new FieldsBuilder('link_items');

        $builder
            ->addRepeater(
                'links',
                [
                    'label'        => 'Länkar',
                    'button_label' => 'Lägg till länk'
                ]
            )
            ->addLink(
                'link',
                [
                    'label' => 'Länk'
                ]
            )
            ->endRepeater();

        return $builder;
    }
}
