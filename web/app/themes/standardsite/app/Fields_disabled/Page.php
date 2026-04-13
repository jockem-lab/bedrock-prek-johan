<?php

namespace App\Fields;

use Log1x\AcfComposer\Field;
use StoutLogic\AcfBuilder\FieldsBuilder;
use App\Fields\Partials\{Hero, Content};

class Page extends Field
{
    /**
     * The field group.
     *
     * @return array
     */
    public function fields()
    {
        $builder = new FieldsBuilder(
            'page', [
                      'title'          => 'Sida',
                      'hide_on_screen' => [
                          'the_content'
                      ],
                      'show_in_rest' => 1
                  ]
        );

        $builder->setLocation('post_type', '==', 'page');

        $builder->addFields($this->get(Content::class));

        $builder->addFields($this->get(Hero::class));

        return $builder->build();
    }
}
