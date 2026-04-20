<?php

namespace App\Fields;

use Log1x\AcfComposer\Field;
use StoutLogic\AcfBuilder\FieldsBuilder;

class Underhand extends Field
{
    public function fields(): FieldsBuilder
    {
        $underhand = new FieldsBuilder('underhand', [
            'title' => 'Underhandsobjekt',
        ]);

        $underhand
            ->setLocation('post_type', '==', 'underhand')

            ->addText('uh_omrade', ['label' => 'Område'])
            ->addNumber('uh_kvm', ['label' => 'Kvadratmeter'])
            ->addNumber('uh_rum', ['label' => 'Antal rum'])
            ->addRepeater('uh_beskrivning', ['label' => 'Beskrivningspunkter', 'button_label' => 'Lägg till punkt'])
                ->addText('punkt', ['label' => 'Punkt'])
            ->endRepeater()
            ->addGallery('uh_bilder', ['label' => 'Bilder', 'return_format' => 'array', 'preview_size' => 'medium'])
            ->addText('uh_maklare_namn', ['label' => 'Mäklarens namn'])
            ->addEmail('uh_maklare_email', ['label' => 'Mäklarens e-post'])
            ->addText('uh_maklare_telefon', ['label' => 'Mäklarens telefon']);

        return $underhand;
    }
}
