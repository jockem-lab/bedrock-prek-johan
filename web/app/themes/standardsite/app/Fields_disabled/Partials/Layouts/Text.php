<?php

namespace app\Fields\Partials\Layouts;

use App\Fields\Partials\{AdvancedLayoutSettings, FluidSettings, WidthSettings};
use Log1x\AcfComposer\Partial;
use StoutLogic\AcfBuilder\FieldsBuilder;
use App\Fields\Partials\LinkItems;

class Text extends Partial
{
    private $name = 'text';

    private $groupConfig = [
        'label' => 'Text / bild'
    ];

    public function fields()
    {
        $builder = new FieldsBuilder(
            $this->name,
            $this->groupConfig,
        );
        $builder->addFields(
            $this->get(WidthSettings::class)
        );
        $builder->addFields(
            $this->get(FluidSettings::class)
        );
        $builder
            ->addText('heading', [
                'label' => 'Rubrik',
            ])
            ->addWysiwyg('content', [
                'label' => 'Text',
            ])
            ->addImage('image', [
                'label' => 'Bild'
            ])
            ->addRadio('imageposition', [
                'label' => 'Ordning',
                'default_value' => 'right',
            ])->addChoices(
                ['right' => 'Text till vänster, bild till höger'],
                ['left' => 'Text till höger, bild till vänster']
            )
            ->addFields(
                $this->get(LinkItems::class)
            );
        $builder->addFields(
            $this->get(AdvancedLayoutSettings::class)
        );
        return $builder;
    }
}