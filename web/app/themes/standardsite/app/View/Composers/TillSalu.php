<?php

namespace App\View\Composers;

use Roots\Acorn\View\Composer;

class TillSalu extends PrekComposer
{
    protected static $views = [
        'page-objekt',
    ];

    public function with()
    {
        // Hero
        $hero_rubrik      = get_field('ts_hero_rubrik') ?: 'Hem till salu';
        $hero_underrubrik = get_field('ts_hero_underrubrik') ?: 'Linköping och Östergötland';

        // Filter-knappar från ACF repeater, fallback till standard
        $acf_knappar = get_field('ts_filter_knappar');
        if ($acf_knappar && is_array($acf_knappar)) {
            $filter_knappar = array_map(function($k) {
                return ['text' => strtoupper($k['label'] ?? ''), 'filter' => $k['value'] ?? 'alla'];
            }, $acf_knappar);
        } else {
            $filter_knappar = [
                ['text' => 'ALLA',             'filter' => 'alla'],
                ['text' => 'KOMMANDE',         'filter' => 'kommande'],
                ['text' => 'TILL SALU',        'filter' => 'tillsalu'],
                ['text' => 'BUDGIVNING PÅGÅR', 'filter' => 'budgivning'],
                ['text' => 'SÅLDA',            'filter' => 'sald'],
            ];
        }

        return [
            'ts_hero_rubrik'      => $hero_rubrik,
            'ts_hero_underrubrik' => $hero_underrubrik,
            'ts_filter_knappar'   => $filter_knappar,
        ];
    }
}
