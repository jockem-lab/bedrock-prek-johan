<?php

namespace App\View\Composers;

class Journal extends PrekComposer
{
    protected static $views = ['page-journal'];

    public function with()
    {
        $query = new \WP_Query([
            'post_type'      => 'journal',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'orderby'        => 'date',
            'order'          => 'DESC',
        ]);

        $artiklar = [];
        foreach ($query->posts as $post) {
            $bild = \get_field('j_hero_bild', $post->ID);
            $thumb = get_the_post_thumbnail_url($post->ID, 'large');
            $artiklar[] = (object)[
                'id'        => $post->ID,
                'slug'      => $post->post_name,
                'titel'     => $post->post_title,
                'excerpt'   => get_the_excerpt($post),
                'datum'     => get_the_date('j F Y', $post),
                'lasttid'   => \get_field('j_lasttid', $post->ID),
                'kategori'  => \get_field('j_kategori', $post->ID),
                'hero_typ'  => \get_field('j_hero_typ', $post->ID) ?: 'bild',
                'hero_bild' => is_array($bild) ? ($bild['url'] ?? $thumb) : (is_numeric($bild) ? (wp_get_attachment_image_url($bild, 'large') ?: $thumb) : $thumb),
                'hero_video'=> \get_field('j_hero_video', $post->ID),
            ];
        }

        return ['artiklar' => $artiklar];
    }
}
