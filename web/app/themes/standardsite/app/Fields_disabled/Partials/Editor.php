<?php
//Not used
namespace App\Fields\Partials;

use Log1x\AcfComposer\Partial;
use StoutLogic\AcfBuilder\FieldsBuilder;

class Editor extends Partial
{
    public function fields()
    {
        $builder = new FieldsBuilder('content');
        $builder->addRadio('editor_type', [
            'label' => 'Redigeringsvy',
            'layout' => 'horizontal',
            'default_value' => 'simple',
        ])->addChoices(
            ['simple' => 'Enkel'],
            ['wysiwyg' => 'Utökad']
        );
        $builder->addWysiwyg('wysiwyg', [
            'label' => 'Text'
        ])->conditional('editor_type', '==', 'wysiwyg');
        $builder->addTextarea('textarea', [
            'label' => 'Text'
        ])->conditional('editor_type', '==', 'simple');

        return $builder;
    }
}