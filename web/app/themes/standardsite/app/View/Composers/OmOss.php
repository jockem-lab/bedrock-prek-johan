<?php

namespace App\View\Composers;

class OmOss extends PrekComposer
{
    protected static $views = ['page-om-oss'];

    public function with()
    {
        return [
            'oo_hero_rubrik'      => get_field('oo_hero_rubrik') ?: 'Om oss',
            'oo_hero_underrubrik' => get_field('oo_hero_underrubrik') ?: 'Erfarna mäklare med lokal kännedom',
            'oo_intro_rubrik'     => get_field('oo_intro_rubrik') ?: 'Med hjärtat i varje affär',
            'oo_intro_text'       => get_field('oo_intro_text') ?: 'På PREK kombinerar vi lång erfarenhet av bostadsmarknaden med ett genuint engagemang för varje kund.',
            'oo_blocks'           => get_field('oo_blocks') ?: [
                ['rubrik' => 'Lokal expertis', 'text' => 'Med djup kännedom om den lokala marknaden ger vi dig rätt underlag för att fatta välgrundade beslut.'],
                ['rubrik' => 'Personlig service', 'text' => 'Varje affär är unik. Vi lyssnar, förstår dina behov och anpassar vår service efter dig.'],
                ['rubrik' => 'Trygghet i processen', 'text' => 'Vi ser till att du känner dig trygg och välinformerad i varje steg.'],
            ],
            'oo_values_rubrik'    => get_field('oo_values_rubrik') ?: 'Våra värderingar',
            'oo_values'           => get_field('oo_values') ?: [
                ['rubrik' => 'Ärlighet', 'text' => 'Vi ger alltid en ärlig bild av marknaden och objektet.'],
                ['rubrik' => 'Engagemang', 'text' => 'Vi engagerar oss fullt ut i varje affär.'],
                ['rubrik' => 'Tillgänglighet', 'text' => 'Vi finns tillgängliga när du behöver oss.'],
            ],
            'oo_team_visa'        => get_field('oo_team_visa') !== false ? get_field('oo_team_visa') : true,
            'oo_team_rubrik'      => get_field('oo_team_rubrik') ?: 'Vårt team',
        ];
    }
}
