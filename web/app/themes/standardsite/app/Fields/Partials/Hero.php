<?php

namespace App\Fields\Partials;

use Log1x\AcfComposer\Partial;
use StoutLogic\AcfBuilder\FieldsBuilder;
//use App\Fields\Partials\{ImagePosition};

class Hero extends Partial
{
    /**
     * The partial field group.
     *
     * @return array
     */
    public function fields()
    {
        $builder = new FieldsBuilder('hero');
        $builder
            ->addTab('hero_tab', [
                'label' => 'Hero',
            ])
            ->addGroup('hero_group', [
                'label' => '',
            ])
            ->addButtonGroup('hero_type', [
                'label' => 'Typ av hero',
            ])->addChoices(['none' => 'Ingen'], ['slides' => 'Bildspel'], ['video' => 'Video'])
            ->addRepeater('hero_slides', [
                'label' => 'Bildspel',
                'button_label' => 'Lägg till bild',
                'layout' => 'block',
            ])
            ->conditional('hero_type', '==', 'slides')
            ->addGroup('hero_slides_image_group', [
                'label' => 'Bild',
                'layout' => 'table',
            ])
            ->addImage('hero_slides_image',[
                'label' => 'Bild'
            ])
            ->addFields(
                $this->get(ImagePosition::class)
            )
            ->endGroup()
            ->addTextarea('title', [
                'label' => 'Huvudrubrik',
                'new_lines' => 'br'
            ])
            ->addWysiwyg('subtitle', [
                'label' => 'Underrubrik',
                'toolbar' => 'basic',
            ])
//            ->addFields(
//                $this->get(LinkItems::class)
//            )
            ->endRepeater()
            ->addGroup('hero_video', [
                'label' => 'Video',
                'layout' => 'table',
                'instructions' => 'För att visa video på både desktop och mobil behöver man välj video i båda fälten. Är något av fälten tomt så visas posterbild där i stället',
            ])
            ->conditional('hero_type', '==', 'video')
            ->addFile('hero_video_file', [
                'label' => 'Video'
            ])
            ->addFile('hero_video_file_mobile', [
                'label' => 'Video, mobil',
            ])
            ->addImage('hero_video_poster', [
                'label' => 'Posterbild',
                'required' => true,
            ])
            ->endGroup()
            ->endGroup()
        ;
//            ->addTrueFalse('show_arrow', [
//                'label' => 'Visa pil i desktop',
//                'ui' => true,
//                'default_value' => false,
//            ]);
        return $builder;
    }
}
