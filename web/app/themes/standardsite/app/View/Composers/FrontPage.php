<?php

namespace App\View\Composers;

class FrontPage extends PrekComposer
{
    protected static $views = [
        'front-page',
    ];

    public function with()
    {
        $listings = [];

        $query = new \WP_Query([
            'post_type'      => 'fasad_listing',
            'post_status'    => 'publish',
            'posts_per_page' => 6,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ]);

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();

                // Hämta location (serialiserat objekt)
                $location_raw = get_post_meta($post_id, '_fasad_location', true);
                $location = $location_raw ? @unserialize($location_raw) : null;
                $address = $location->address ?? get_the_title();

                // Hämta economy/pris
                $economy_raw = get_post_meta($post_id, '_fasad_economy', true);
                $economy = $economy_raw ? @unserialize($economy_raw) : null;
                $price = $economy->price->primary->amount ?? '';
                if ($price) {
                    $price = number_format($price, 0, ',', ' ') . ' kr';
                }

                // Hämta bilder
                $images_raw = get_post_meta($post_id, '_fasad_images', true);
                $images = $images_raw ? @unserialize($images_raw) : [];
                $image = !empty($images[0]->path) ? $images[0]->path : '';

                // Hämta fakta
                $size_raw = get_post_meta($post_id, '_fasad_size', true);
                $size = $size_raw ? @unserialize($size_raw) : null;
                $area = $size->livingArea ?? '';

                $facts_raw = get_post_meta($post_id, '_fasad_facts', true);
                $facts = $facts_raw ? @unserialize($facts_raw) : null;
                $rooms = $facts->rooms ?? '';

                $type_raw = get_post_meta($post_id, '_fasad_descriptionType', true);
                $type_obj = $type_raw ? @unserialize($type_raw) : null;
                $type = $type_obj->alias ?? '';

                $status_raw = get_post_meta($post_id, '_fasad_status', true);
                $status = $status_raw ? @unserialize($status_raw) : null;
                $status_alias = $status->alias ?? '';

                $listings[] = (object)[
                    'id'      => $post_id,
                    'slug'    => get_post_field('post_name', $post_id),
                    'address' => $address,
                    'price'   => $price,
                    'type'    => $type,
                    'rooms'   => $rooms ? $rooms . ' rum' : '',
                    'area'    => $area ? $area . ' kvm' : '',
                    'image'   => $image,
                    'status'  => $status_alias,
                ];
            }
            wp_reset_postdata();
        }

        return [
            'listings' => $listings,
        ];
    }
}
