<?php

namespace App\Fields\Partials;

//use App\Fields\Partials\Layouts\Quote;
use Log1x\AcfComposer\Partial;
use StoutLogic\AcfBuilder\FieldsBuilder;
use StoutLogic\AcfBuilder\FlexibleContentBuilder;

class Content extends Partial
{
    private $ns = "\\App\\Fields\\Partials\\Layouts\\";

    private $layouts = [
        'Listings',
        'Text',
        'Form',
//        'LatestListings',
//        'Image',
//        'Pagedata',
        'Coworkers',
//        'DisplayReco',
//        'Offices',
//        'ImageText',
//        'IconText',
//        'TwoColumnList',
//        'Wysiwyg',
    ];

    private $configFields = [
        'display'
    ];

    /**
     * The partial field group.
     *
     * @return array
     */
    public function fields()
    {
        $builder = new FieldsBuilder('content');
        $builder->addTab(
            'content_tab',
            [
                'label' => 'Innehåll',
            ]
        );
        if (!empty($this->layouts)) {
            $flexibleContent = $builder->addFlexibleContent(
                'flexible_content',
                [
                    'label'        => 'Innehåll',
                    'button_label' => 'Lägg till layout'
                ]
            );
            foreach ($this->layouts as $layout) {
                $layoutClass = $this->ns . $layout;
                if (!class_exists($layoutClass)) {
                    continue;
                }
                $config = [];
                $fieldsbuilder = $this->get($layoutClass);
                foreach ($this->configFields as $key) {
                    if (!is_null($fieldsbuilder->getGroupConfig($key))) {
                        $config[$key] = $fieldsbuilder->getGroupConfig($key);
                    }
                }
                $flexibleContent->addLayout($fieldsbuilder, $config);
            }
            $flexibleContent->endFlexibleContent();
        }
        return $builder;
    }
}
