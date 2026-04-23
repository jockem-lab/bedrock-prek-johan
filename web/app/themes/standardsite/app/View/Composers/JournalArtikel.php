<?php

namespace App\View\Composers;

class JournalArtikel extends PrekComposer
{
    protected static $views = ['single-journal'];

    public function with()
    {
        $post = get_post();
        if (!$post) return [];

        $bild  = \get_field('j_hero_bild', $post->ID);
        $thumb = get_the_post_thumbnail_url($post->ID, 'full');

        if (is_array($bild)) {
            $hero_bild = $bild['url'] ?? $thumb;
        } elseif (is_numeric($bild)) {
            $hero_bild = wp_get_attachment_image_url($bild, 'full') ?: $thumb;
        } else {
            $hero_bild = $thumb;
        }

        return [
            'titel'      => $post->post_title,
            'innehall'   => apply_filters('the_content', $post->post_content),
            'datum'      => get_the_date('j F Y', $post),
            'lasttid'    => \get_field('j_lasttid', $post->ID),
            'kategori'   => \get_field('j_kategori', $post->ID),
            'hero_typ'   => \get_field('j_hero_typ', $post->ID) ?: 'bild',
            'hero_bild'  => $hero_bild,
            'hero_video' => \get_field('j_hero_video', $post->ID),
        ];
    }
}
