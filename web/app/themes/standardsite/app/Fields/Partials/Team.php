<?php

namespace App\Fields\Partials;

use Log1x\AcfComposer\Partial;
use StoutLogic\AcfBuilder\FieldsBuilder;

class Team extends Partial
{
    public function fields(): FieldsBuilder
    {
        $team = new FieldsBuilder('team');

        $team
            ->addRepeater('oo_team', [
                'label' => 'Team',
                'button_label' => 'Lägg till mäklare',
                'layout' => 'table',
            ])
                ->addText('namn', ['label' => 'Namn'])
                ->addText('titel', ['label' => 'Titel'])
                ->addImage('bild', ['label' => 'Bild', 'return_format' => 'id', 'preview_size' => 'medium'])
                ->addEmail('email', ['label' => 'E-post'])
                ->addText('telefon', ['label' => 'Telefon'])
                ->addUrl('instagram', ['label' => 'Instagram URL'])
            ->endRepeater();

        return $team;
    }
}
