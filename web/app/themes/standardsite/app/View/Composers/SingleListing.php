<?php

namespace App\View\Composers;

class SingleListing extends PrekComposer
{
    protected static $views = [
        'single-fasad_listing',
        'fasad.content-single-fasad_listing',
    ];

    public function with()
    {
        $post_id = get_the_ID();
        if (!$post_id) return ['listing' => null];

        // Location
        $location_raw = get_post_meta($post_id, '_fasad_location', true);
        $location = $location_raw ? unserialize($location_raw) : null;
        $address  = $location->address ?? get_the_title();
        $city     = $location->city ?? '';
        $zipCode  = $location->zipCode ?? '';

        // Economy/pris
        $economy_raw = get_post_meta($post_id, '_fasad_economy', true);
        $economy = $economy_raw ? unserialize($economy_raw) : null;
        $price_amount = $economy->price->primary->amount ?? 0;
        $price = $price_amount ? number_format($price_amount, 0, ',', ' ') . ' kr' : '';

        // Bilder
        $images_raw = get_post_meta($post_id, '_fasad_images', true);
        $images_data = $images_raw ? unserialize($images_raw) : [];
        $images = [];
        if (is_array($images_data)) {
            foreach ($images_data as $img) {
                if (!empty($img->path)) {
                    $images[] = $img->path;
                }
            }
        }

        // Fakta
        $size_raw = get_post_meta($post_id, '_fasad_size', true);
        $size = $size_raw ? unserialize($size_raw) : null;
        $living_area = $size->livingArea ?? '';

        $facts_raw = get_post_meta($post_id, '_fasad_facts', true);
        $facts = $facts_raw ? unserialize($facts_raw) : null;
        $rooms = $facts->rooms ?? '';

        $type_raw = get_post_meta($post_id, '_fasad_descriptionType', true);
        $type_obj = $type_raw ? unserialize($type_raw) : null;
        $type = $type_obj->alias ?? '';

        $status_raw = get_post_meta($post_id, '_fasad_status', true);
        $status_obj = $status_raw ? unserialize($status_raw) : null;
        $status = $status_obj->alias ?? '';

        // Säljtext
        $sales_title = get_post_meta($post_id, '_fasad_salesTitle', true) ?: $address;
        $sales_text  = get_post_meta($post_id, '_fasad_salesText', true) ?: '';

        // Byggnad/fakta
        $building_raw = get_post_meta($post_id, '_fasad_building', true);
        $building = $building_raw ? unserialize($building_raw) : null;
        $built_year = $building->constructionYear ?? '';

        $association_raw = get_post_meta($post_id, '_fasad_association', true);
        $association = $association_raw ? unserialize($association_raw) : null;
        $fee = $association->fee ?? '';

        return [
            'listing' => (object)[
                'id'          => $post_id,
                'address'     => $address,
                'city'        => $city,
                'zipCode'     => $zipCode,
                'price'       => $price,
                'images'      => $images,
                'livingArea'  => $living_area ? $living_area . ' kvm' : '',
                'rooms'       => $rooms ? $rooms . ' rum' : '',
                'type'        => $type,
                'status'      => $status,
                'salesTitle'  => $sales_title,
                'salesText'   => $sales_text,
                'builtYear'   => $built_year,
                'fee'         => $fee ? number_format($fee, 0, ',', ' ') . ' kr/mån' : '',
            ],
        ];
    }
}
