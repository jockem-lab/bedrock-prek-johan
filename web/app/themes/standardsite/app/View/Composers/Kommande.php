<?php

namespace App\View\Composers;

class Kommande extends PrekComposer
{
    protected static $views = ['page-kommande'];

    public function with()
    {
        $query = new \WP_Query([
            'post_type'      => 'fasad_listing',
            'posts_per_page' => -1,
            'meta_query'     => [
                'relation' => 'AND',
                ['key' => '_fasad_published', 'value' => '0', 'compare' => '='],
                ['key' => '_fasad_sold',      'value' => '0', 'compare' => '='],
            ],
            'orderby'  => 'meta_value',
            'meta_key' => '_fasad_firstPublished',
            'order'    => 'DESC',
        ]);

        $listings = [];
        foreach ($query->posts as $post) {
            $listings[] = $this->formatListing($post);
        }

        return [
            'listings' => $listings,
            'antal'    => count($listings),
        ];
    }

    private function formatListing($post)
    {
        $images = get_post_meta($post->ID, '_fasad_images', true);
        $imgs   = maybe_unserialize($images);
        $img    = '';
        if (is_array($imgs) && !empty($imgs)) {
            foreach (($imgs[0]->variants ?? []) as $v) {
                if (in_array($v->type, ['large', 'highres'])) { $img = $v->path ?? ''; break; }
            }
        }

        $economy = maybe_unserialize(get_post_meta($post->ID, '_fasad_economy', true));
        $price   = '';
        if (is_object($economy) && isset($economy->price->amount)) {
            $price = number_format($economy->price->amount, 0, ',', ' ') . ' kr';
        }

        $size  = maybe_unserialize(get_post_meta($post->ID, '_fasad_size', true));
        $rooms = is_object($size) ? ($size->rooms ?? '') : '';
        $area  = is_object($size) ? ($size->livingArea ?? '') : '';

        $location = maybe_unserialize(get_post_meta($post->ID, '_fasad_location', true));
        $address  = is_object($location) ? ($location->address ?? '') : '';

        return (object)[
            'slug'    => $post->post_name,
            'address' => $address ?: get_post_meta($post->ID, '_fasad_salesTitle', true),
            'price'   => $price,
            'rooms'   => $rooms ? $rooms . ' rok' : '',
            'area'    => $area ? $area . ' m²' : '',
            'image'   => $img,
            'status'  => 'kommande',
        ];
    }
}
