<?php

namespace app\Fields\Partials\Layouts;

use App\Fields\Partials\{AdvancedLayoutSettings, FluidSettings, WidthSettings};
use Log1x\AcfComposer\Partial;
use StoutLogic\AcfBuilder\FieldsBuilder;

class Listings extends Partial
{
    private $name = 'listings';
    private $groupConfig = [
        'label' => 'Objektslistning'
    ];

    public function fields()
    {
        /*
         * For filter by tag
            $terms = get_terms(['hide_empty' => false]);
            $excludedTags = ['uthyrd', 'såld'];
            $tags = array_filter($terms, function($term) use($excludedTags){
                return $term->taxonomy === 'fasad_listing_tag' && !in_array(mb_strtolower($term->name),$excludedTags);
            });
            $tags = [];
         */
        $builder = new FieldsBuilder(
            $this->name,
            $this->groupConfig
        );
        $builder->addFields(
            $this->get(WidthSettings::class)
        );
        $builder->addFields(
            $this->get(FluidSettings::class)
        );
        $builder->addSelect('listings_sold', [
            'label'   => 'Publiceringstyp',
            'choices' => [
                'all'     => 'Alla',
                'forsale' => 'Till salu',
                'sold'    => 'Sålda',
            ]
        ]);
        /*
         * for filter by tag
        if ($tags) {
            $choices = [];
            foreach ($tags as $tag) {
                $choices[] = $tag->name;
            }
            $builder->addGroup('listings_tags', [
                'label'  => 'Specialannonsering',
                'layout' => 'table'
            ])
            ->addCheckbox('listings_tags_tags', [
                'label'   => 'Tagg',
                'choices' => $choices
            ])
            ->addTrueFalse('listing_tags_include', [
                'label'         => 'Inkludera eller exkludera',
                'instructions'  => 'Om valda specialannonseringar ska exkluderas eller inkluderas',
                'ui'            => true,
                'default_value' => true,
                'ui_on_text'    => 'Inkludera',
                'ui_off_text'   => 'Exkludera',
            ])
            ->endGroup();
        }
         */
        $builder->addNumber('listings_count', [
            'label' => 'Antal objekt',
            'instructions' => 'Antal objekt att visa innan "Visa mer"-knapp (-1 för att visa alla objekt)',
            'min' => '-1',
            'default_value' => '-1',
        ]);
        $builder->addFields(
            $this->get(AdvancedLayoutSettings::class)
        );
        return $builder;
    }
}