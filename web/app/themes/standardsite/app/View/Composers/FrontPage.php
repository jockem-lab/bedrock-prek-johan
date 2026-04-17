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
            'orderby'        => 'meta_value',
            'meta_key'       => '_fasad_firstPublished',
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
                $images_s1 = $images_raw ? @unserialize($images_raw) : [];
                $images = is_string($images_s1) ? @unserialize($images_s1) : $images_s1;
                $image = '';
                if (is_array($images) && !empty($images[0]->variants)) {
                    foreach ($images[0]->variants as $v) {
                        if (($v->type ?? '') === 'large') { $image = $v->path; break; }
                    }
                }

                // Hämta fakta
                $size_raw = get_post_meta($post_id, '_fasad_size', true);
                $size_s1 = $size_raw ? @unserialize($size_raw) : [];
                $size = is_string($size_s1) ? @unserialize($size_s1) : $size_s1;
                $rooms = ($size && !empty($size->rooms)) ? $size->rooms . ' ' . ($size->roomsInformation ?? 'rum') : '';
                $area = '';
                if (!empty($size->area->areas) && is_array($size->area->areas)) {
                    foreach ($size->area->areas as $a) {
                        if (!empty($a->type) && $a->type === 'Boarea' && !empty($a->size)) {
                            $area = $a->size . ' ' . strtolower($a->unit ?? 'kvm');
                            break;
                        }
                    }
                }

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
                    'rooms'   => $rooms,
                    'area'    => $area,
                    'image'   => $image,
                    'status'  => $status_alias,
                ];
            }
            wp_reset_postdata();
        }

        return [
            'listings'         => $listings,
            'fp_intro_rubrik'  => get_field('fp_intro_rubrik') ?: '',
            'fp_intro_text'    => get_field('fp_intro_text') ?: '',
            'fp_intro_knapp'   => [
                'text' => get_field('fp_intro_knapp_text') ?: 'Se alla objekt',
                'url'  => get_field('fp_intro_knapp_url') ?: get_permalink(get_page_by_path('objekt')),
            ],
            'fp_listings_rubrik' => get_field('fp_listings_rubrik') ?: 'Aktuella objekt',
            'fp_valuation'     => [
                'visa'   => get_field('fp_valuation_visa') !== false ? get_field('fp_valuation_visa') : true,
                'rubrik' => get_field('fp_valuation_rubrik') ?: 'Gratis värdebedömning',
                'text'   => get_field('fp_valuation_text') ?: '',
                'knapp'  => get_field('fp_valuation_knapp') ?: 'Boka värdering',
            ],
        ];
    }
}
