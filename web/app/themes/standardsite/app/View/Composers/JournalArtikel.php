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

        // Relaterade artiklar — samma kategori, exkl. aktuell
        $kategori = get_field('j_kategori', $post->ID);
        $rel_args = [
            'post_type'      => 'journal',
            'posts_per_page' => 3,
            'post__not_in'   => [$post->ID],
            'orderby'        => 'date',
            'order'          => 'DESC',
        ];
        if ($kategori) {
            $rel_args['meta_query'] = [[
                'key'     => 'j_kategori',
                'value'   => $kategori,
                'compare' => '=',
            ]];
        }
        $rel_query = new \WP_Query($rel_args);
        // Om inga träffar med kategori — visa senaste artiklar oavsett kategori
        if ($rel_query->post_count === 0) {
            unset($rel_args['meta_query']);
            $rel_query = new \WP_Query($rel_args);
        }
        $relaterade = [];
        foreach ($rel_query->posts as $rp) {
            $rbild = get_field('j_hero_bild', $rp->ID);
            if (is_array($rbild)) $rbild = $rbild['url'] ?? '';
            elseif (is_numeric($rbild)) $rbild = wp_get_attachment_image_url($rbild, 'large') ?: '';
            else $rbild = get_the_post_thumbnail_url($rp->ID, 'large') ?: '';
            $relaterade[] = [
                'titel'    => $rp->post_title,
                'url'      => home_url('/journal/' . $rp->post_name),
                'bild'     => $rbild,
                'kategori' => get_field('j_kategori', $rp->ID),
                'lasttid'  => get_field('j_lasttid', $rp->ID),
            ];
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
            'relaterade' => $relaterade,
        ];
    }
}
