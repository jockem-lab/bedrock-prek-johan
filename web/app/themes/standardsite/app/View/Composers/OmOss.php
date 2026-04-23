<?php

namespace App\View\Composers;

class OmOss extends PrekComposer
{
    protected static $views = ['page-om-oss'];

    public function with()
    {
        return [
            'oo_hero_rubrik'      => \get_field('oo_hero_rubrik') ?: 'Om oss',
            'oo_hero_underrubrik' => \get_field('oo_hero_underrubrik') ?: 'Erfarna mäklare med lokal kännedom',
            'oo_intro_rubrik'     => \get_field('oo_intro_rubrik') ?: 'Med hjärtat i varje affär',
            'oo_intro_text'       => \get_field('oo_intro_text') ?: 'Vi går in i varje uppdrag med samma genuina passion, oavsett storlek eller läge på ditt hem. Om du frågar oss märks det personliga engagemanget inte minst på sista raden.',
            'oo_blocks'           => \get_field('oo_blocks') ?: [
                ['rubrik' => 'Vårt kundregister', 'text' => 'Vi är stolta över vår framgångsrika historia med över 5 000 förmedlade hem. Genom att skapa nöjda säljare och köpare har Oscars byggt upp ett eftertraktat kundregister med både svenska och internationella kontakter.'],
                ['rubrik' => 'Kvalitet, noggrannhet och detaljer', 'text' => 'Vi lämnar inget åt slumpen i vår verksamhet. Vår strävan är att alltid prestera på högsta nivå och överträffa de redan högt ställda förväntningarna. Kvalitet, noggrannhet och förmågan att uppmärksamma de små detaljerna är självklara principer för oss.'],
                ['rubrik' => 'Kunden i fokus', 'text' => 'Vi har specialiserat oss på Östermalm, Vasastan och Stockholms innerstad. Med 25 års erfarenhet har vi genomfört framgångsrika förmedlingar och byggt upp en gedigen kunskap om marknaden.'],
            ],
            'oo_values_rubrik'    => \get_field('oo_values_rubrik') ?: 'Östermalm — här finns vårt hjärta',
            'oo_values'           => \get_field('oo_values') ?: [
                ['rubrik' => 'Sedan 2001', 'text' => 'Oscars Fastighetsmäkleri har sedan 2001 varit beläget på en av de bästa adresserna på Östermalm.'],
                ['rubrik' => 'Rekordförsäljningar', 'text' => 'Vår kompetens och skickliga förhandlingsteknik har resulterat i upprepade rekordförsäljningar.'],
                ['rubrik' => 'Personligt engagemang', 'text' => 'Inför varje försäljning skräddarsyr vi en strategi som är anpassad efter era specifika behov och önskemål.'],
            ],
            'oo_team_visa'        => \get_field('oo_team_visa') !== false ? \get_field('oo_team_visa') : true,
            'oo_team_rubrik'      => \get_field('oo_team_rubrik') ?: 'Vårt team',
            'oo_team'             => self::getTeam(),
        ];
    }

    private static function getTeam()
    {
        $uploads = content_url('uploads');
        return [
            (object)['namn' => 'Ted Bauer',     'titel' => 'VD / Fastighetsmäklare',       'email' => 'ted@oscarsmakleri.se',     'telefon' => '08 545 675 00', 'bild' => $uploads . '/maklare-ted.jpg', 'instagram' => 'https://www.instagram.com/tedbauer_oscarsmakleri/'],
            (object)['namn' => 'Simon Hedlund', 'titel' => 'Fastighetsmäklare',             'email' => 'simon@oscarsmakleri.se',   'telefon' => '',              'bild' => $uploads . '/maklare-simon.jpg', 'instagram' => ''],
            (object)['namn' => 'Jenny Östman',  'titel' => 'Kontorschef/Fastighetsmäklare', 'email' => 'jenny@oscarsmakleri.se',   'telefon' => '',              'bild' => $uploads . '/maklare-jenny.jpg', 'instagram' => ''],
            (object)['namn' => 'Fredrik Brunned','titel' => 'Fastighetsmäklare',            'email' => 'fredrik@oscarsmakleri.se', 'telefon' => '',              'bild' => $uploads . '/maklare-fredrik.jpg', 'instagram' => ''],
            (object)['namn' => 'Marcus Bile',   'titel' => 'Fastighetsmäklare',             'email' => 'marcus@oscarsmakleri.se',  'telefon' => '',              'bild' => $uploads . '/maklare-marcus.jpg', 'instagram' => ''],
            (object)['namn' => 'Malin Rosdahl', 'titel' => 'Fastighetsmäklare',             'email' => 'malin@oscarsmakleri.se',   'telefon' => '',              'bild' => $uploads . '/maklare-malin.jpg', 'instagram' => ''],
        ];
    }
}
