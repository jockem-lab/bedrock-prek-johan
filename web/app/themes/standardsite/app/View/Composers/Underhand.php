<?php

namespace App\View\Composers;

class Underhand extends PrekComposer
{
    protected static $views = ['page-underhand'];

    public function with()
    {
        // Manuella underhand-objekt från CPT
        $manuella = get_posts([
            'post_type'      => 'underhand',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'orderby'        => 'date',
            'order'          => 'DESC',
        ]);

        $objekt = [];
        foreach ($manuella as $post) {
            $bilder = \get_field('uh_bilder', $post->ID) ?: [];
            $punkter = \get_field('uh_beskrivning', $post->ID) ?: [];
            $objekt[] = (object)[
                'typ'      => 'manuell',
                'omrade'   => \get_field('uh_omrade', $post->ID) ?: get_the_title($post),
                'kvm'      => \get_field('uh_kvm', $post->ID),
                'rum'      => \get_field('uh_rum', $post->ID),
                'punkter'  => array_column($punkter, 'punkt'),
                'bilder'   => array_slice(array_map(fn($b) => $b['url'], $bilder), 0, 5),
                'maklare'  => [
                    'namn'     => \get_field('uh_maklare_namn', $post->ID),
                    'email'    => \get_field('uh_maklare_email', $post->ID),
                    'telefon'  => \get_field('uh_maklare_telefon', $post->ID),
                ],
            ];
        }

        // FasAD-objekt med minilist-flagga
        $fasad_query = new \WP_Query([
            'post_type'      => 'fasad_listing',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'orderby'        => 'date',
            'order'          => 'DESC',
            'meta_query'     => [
                ['key' => '_fasad_minilist', 'value' => '1', 'compare' => '='],
            ],
        ]);

        foreach ($fasad_query->posts as $post) {
            $images = get_post_meta($post->ID, '_fasad_images', true);
            $imgs   = maybe_unserialize($images);
            $bilder = [];
            if (is_array($imgs)) {
                foreach ($imgs as $img) {
                    foreach (($img->variants ?? []) as $v) {
                        if ($v->type === 'large') { $bilder[] = $v->path; break; }
                    }
                }
            }
            $location = maybe_unserialize(get_post_meta($post->ID, '_fasad_location', true));
            $size     = maybe_unserialize(get_post_meta($post->ID, '_fasad_size', true));
            $realtors = maybe_unserialize(get_post_meta($post->ID, '_fasad_realtors', true));
            $realtor  = is_array($realtors) && !empty($realtors) ? $realtors[0] : null;

            $objekt[] = (object)[
                'typ'     => 'fasad',
                'omrade'  => is_object($location) ? ($location->district ?? $location->city ?? '') : '',
                'kvm'     => is_object($size) ? ($size->livingArea ?? '') : '',
                'rum'     => is_object($size) ? ($size->rooms ?? '') : '',
                'punkter' => [],
                'bilder'  => array_slice($bilder, 0, 5),
                'maklare' => [
                    'namn'    => $realtor ? ($realtor->name ?? '') : '',
                    'email'   => $realtor ? ($realtor->email ?? '') : '',
                    'telefon' => $realtor ? ($realtor->phone ?? '') : '',
                ],
                'slug'    => $post->post_name,
            ];
        }

        return ['objekt' => $objekt];
    }
}
