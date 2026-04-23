<?php

namespace App\View\Composers;

class Kontakt extends PrekComposer
{
    protected static $views = ['page-kontakt'];

    public function with()
    {
        return [
            'k_hero_rubrik'      => \get_field('k_hero_rubrik') ?: 'Kontakt',
            'k_hero_underrubrik' => \get_field('k_hero_underrubrik') ?: 'Vi finns här för dig',
            'k_intro_rubrik'     => \get_field('k_intro_rubrik') ?: 'Kontakta oss',
            'k_intro_text'       => \get_field('k_intro_text') ?: '',
            'k_visa_karta'       => \get_field('k_visa_karta') ?: false,
            'k_karta_embed'      => \get_field('k_karta_embed') ?: '',
            'k_form_rubrik'      => \get_field('k_form_rubrik') ?: 'Skicka ett meddelande',
            'k_form_text'        => \get_field('k_form_text') ?: '',
            'k_form_mottagare'   => \get_field('k_form_mottagare') ?: get_option('admin_email'),
            'site_phone'         => \get_field('prek_phone', 'option') ?: '',
            'site_email'         => \get_field('prek_email', 'option') ?: '',
            'site_address'       => \get_field('prek_address', 'option') ?: '',
            'site_city'          => \get_field('prek_city', 'option') ?: '',
            'site_opening_hours' => \get_field('prek_opening_hours', 'option') ?: '',
        ];
    }
}
