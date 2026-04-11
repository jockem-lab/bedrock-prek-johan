<?php

namespace App\Fields\Partials\Layouts;

use App\Fields\Partials\{AdvancedLayoutSettings, FluidSettings, WidthSettings};
use Log1x\AcfComposer\Partial;
use Roots\Acorn\Application;
use StoutLogic\AcfBuilder\FieldsBuilder;


class Coworkers extends Partial
{
    private $name = 'coworkers';

    private $groupConfig = [
        'label' => 'Medarbetare',
    ];

    public function __construct(Application $app)
    {
        parent::__construct($app);
    }

    public function fields()
    {
        $builder = new FieldsBuilder(
            $this->name,
            $this->groupConfig
        );
        $builder->addMessage('Visa medarbetare', 'Visar en lista med medarbetare');

        $builder->addFields(
            $this->get(WidthSettings::class)
        );
        $builder->addFields(
            $this->get(FluidSettings::class)
        );
        $builder->addText(
                'heading',
                [
                    'label' => 'Rubrik',
                ]
            );

        $builder->addFields(
            $this->get(AdvancedLayoutSettings::class)
        );
        return $builder;
    }
}
