<?php

namespace App\Fields;

use Log1x\AcfComposer\Field;
use StoutLogic\AcfBuilder\FieldsBuilder;

class SiteSettings extends Field
{
    public function fields()
    {
        $builder = new FieldsBuilder(
            'theme_options',
            [
                'title' => 'Inställningar',
            ]

        );
        //Common settings
        $builder->addTab('Allmänt');
        $builder->addAccordion('general_accordion', [
            'label' => 'Allmänt']
        );
        $builder->addNumber('corporation_id', [
            'label' => 'BolagsID'
        ]);
        $builder->addImage('favicon', [
            'label' => 'Favicon',
            'instructions' => 'Bör vara kvadratisk'
        ]);
        $builder->addPostObject('default_listings_page', [
            'label'        => 'Listningssida',
            'instructions' => 'Ibland behöver det länkas till listningssidan (på 404-sidan till exempel), välj vilken som ska användas här.',
            'post_type'    => ['page'],
            'allow_null'   => true,
        ]);
        $builder->addRadio('font-family', [
            'label' => 'Font',
        ])->addChoices(['pt-sans' => 'PT Sans', 'open-sans' => 'Open Sans'])
            ->setDefaultValue('pt-sans');
        $builder->addAccordion('header_accordion', [
            'label' => 'Header',
        ]);
        $builder->addImage('logo', [
            'label'   => 'Logga',
            'wrapper' => [
                'width' => '50%'
            ]
        ]);
        $builder->addGroup('group_logo_settings', [
            'label'   => 'Extra inställningar',
            'wrapper' => [
                'width' => '50%'
            ]
        ])
        ->addNumber('logo_extra_height', [
            'label'        => 'Extrahöjd',
            'instructions' => 'Offsethöjd (i pixlar) på logotyp<br>(låter loggan flöda utanför header)',
            'min'          => 0,
        ]);
        $builder->addRadio('header_menu_type', [
            'label' => 'Menytyp'
        ])->addChoices(['burgeronly' => 'Hamburgermeny'], ['horizontal' => 'Liggande meny, hamburgermeny om den blir för bred'])
            ->setDefaultValue('burgeronly');
        /*
         * Not used, left as poc for dynamic cssclasses, needs more work though
         * Tailwinds JIT needs classes to exist somewhere
         * @see FASADWEB-15
         * ->addCheckbox('logo_cssclasses', [
         *   'label' => 'Klasser',
         *   'allow_custom' => 1,
         * ])
         * ->addTrueFalse('logo_cssclasses_overwrite', [
         *   'label' => 'Byt ut eller lägg till klasser',
         *   'default_value' => 0,
         *   'ui_off_text' => 'Lägg till',
         *   'ui_on_text' => 'Byt ut',
         * ]);
         */
        $builder->addAccordion('footer_accordion', [
            'label' => 'Footer',
        ]);
        $builder->addImage('logo_footer', [
            'label'        => 'Logga, footer',
            'instructions' => 'Om man vill ha en annan logga i footern så laddar man upp den här, annars används vanliga loggan'
        ]);
        $builder->addTextarea('footer_text', [
            'label'        => 'Text, footer',
            'instructions' => 'Text som syns under loggan i footern'
        ]);
        $builder->addAccordion('color_accordion', [
            'label' => 'Färger'
        ]);
        //Disable this for now, only crm forms
//        $builder->addGroup('form', ['label' => 'Formulär'])
//        ->addText('message_success',[
//            'label' => 'Meddelande vid skickat',
//            'instructions' => 'Visas när formuläret har skickats in'
//        ])
//        ->addEmail('email', [
//            'label' => 'E-postadress som formuläret ska skickas till'
//        ]);
        //Listing settings
        $builder->addGroup('theme-colors', [
            'label'  => 'Färger',
            'layout' => 'row',
        ])
        ->addColorPicker('theme-colors_theme-custom-background', [
            'label' => 'Accentfärg, bakgrund'
        ])
        ->addColorPicker('theme-colors_theme-custom-color', [
            'label' => 'Accentfärg, text'
        ])
        ->addMessage('<i>Sidhuvud</i>', '')
        ->addColorPicker('theme-colors_theme-custom-header-background', [
            'label' => 'Sidhuvud, bakgrund'
        ])
        ->addColorPicker('theme-colors_theme-custom-header-menu-color', [
            'label' => 'Sidhuvud, menytext'
        ])
        ->addMessage('<i>Sidfot</i>', '')
        ->addColorPicker('theme-colors_theme-custom-footer-background', [
            'label' => 'Sidfot, bakgrund'
        ])
        ->addColorPicker('theme-colors_theme-custom-footer-menu-color', [
            'label' => 'Sidfot, menytext'
        ])
        ->addColorPicker('theme-colors_theme-custom-footer-color', [
            'label' => 'Sidfot, text'
        ])
        ->addColorPicker('theme-colors_theme-custom-footer-border-top-color', [
            'label' => 'Sidfot, linjefärg'
        ]);
        $builder->addAccordion('color_accordion_end')->endpoint();
        $builder->addTab('Objekt')
//            Might be good with accordion here, its seems safe to add this even after initiated fields. but group is not possible to add
//            ->addAccordion('test_accordion', [
//                'label' => 'Bilder',
//            ])
            ->addNumber('listing-hero_images', [
                'label'         => 'Herobilder, antal',
                'instructions'  => 'Hur många bilder som ska synas i heron, 0-5',
                'default_value' => '1',
                'min'           => '0',
                'max'           => '5',
            ])
            ->addRadio('listing-hero_images_position', [
                'label'        => 'Herobilder, beskärning',
                'instructions' => 'Vilken del av bilden som behålls vid eventuell beskärning.',
            ])->addChoices(['top' => 'Övre'], ['center' => 'Mitten'], ['bottom' => 'Nedre'])
            ->setDefaultValue('center')
            ->addTrueFalse('listing-show_realtor_type', [
                'label' => 'Visa mäklartyp',
                'instructions' => 'Skriver ut mäklartypen över mäklarbilden (Ansvarig/Biträdande/Assistent)',
                'default_value' => true,
            ])
            ->addNumber('listing-visible_images', [
                'label'         => 'Bilder som syns',
                'instructions'  => 'Hur många <i>block</i> av bilder som ska synas, <b>-1 för att visa alla.</b> <br>(Observera att det kan bli fler bilder beroende på om det är stående bilder och om dessa är bredvid varandra eller ej)',
                'default_value' => '2',
                'min'           => '-1',
            ])
            ->addNumber('listing-map_zoom', [
                'label'         => 'Zoom på kartan',
                'instructions'  => '0-21 där 0 är maximalt utzoomat (hela världen), 21 är maximalt inzoomat',
                'min'           => 0,
                'max'           => 21,
                'default_value' => 14
            ]);
        $builder->setLocation('options_page', '==', 'acf-options-sitesettings');
        return $builder->build();
    }
}
