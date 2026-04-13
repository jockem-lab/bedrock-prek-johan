<?php

namespace app\Fields\Partials\Layouts;

use Log1x\AcfComposer\Partial;
use StoutLogic\AcfBuilder\FieldsBuilder;
use App\Fields\Partials\{AdvancedLayoutSettings, FluidSettings, WidthSettings};

class Form extends Partial
{
    private $name = 'form';

    private $groupConfig = [
        'label' => 'Formulär'
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
        $builder->addText('heading', [
            'label' => 'Rubrik',
        ]);
        $builder->addWysiwyg('content', [
            'label' => 'Text',
        ]);

        $builder
            ->addPostObject(
                'form',
                [
                    'label' => 'Formulär',
                    'post_type' => [
                        'html-form',
                    ],
                ]
            );
        $builder->addFields(
            $this->get(AdvancedLayoutSettings::class)
        );
        return $builder;
    }
}