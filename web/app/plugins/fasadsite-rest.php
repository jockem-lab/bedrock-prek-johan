<?php

/*
 * Requires the "FasAd Standardsite" theme
 * and the PrekWeb plugin
 *
 * @wordpress-plugin
 * Plugin Name:       Fasad REST
 * Plugin URI:        https://prek.se/
 * Description:       Expose listings from WordPress db in WP REST API.
 * Version:           1.0.0
 * Author:            Dennis Germundal
 * Author URI:        https://prek.se/
 * License:           Apache
 * Text Domain:       fasad-api-connect
*/

use PrekWeb\Includes\Fasad;

/*
 * Setup routes:
 * /wp-json/api/v1/listing
 * /wp-json/api/v1/listing/{listing_id}
 */
add_action('rest_api_init', function () {
    register_rest_route('api/v1', '/listing', [
        'methods'  => 'GET',
        'callback' => 'getListings',
        'permission_callback' => '__return_true',
    ]);
    register_rest_route('api/v1', '/listing/(?P<listing_id>\d+)', [
        'methods'  => 'GET',
        'callback' => 'getListing',
        'permission_callback' => '__return_true',
    ]);
});

function getListings($request)
{
    $results = [];
    $listings = Fasad::listings(['postsperpage' => -1, 'sold' => -1]);
    foreach ($listings as $listing) {
        // Using a theme function in a plugin, ok for now...
        $listingJson = \App\Common::listingToJson($listing);
        if ($listingJson) {
            $listingJson['price'] = 0;
            $listingJson['rooms'] = 0;
            $listingJson['area']  = 0;
            $listingJson['date']  = '1970-01-01 00:00:00';
            if (!empty($listing->meta->price)) {
                $listingJson['price'] = (int)preg_replace('/\D/', '', $listing->meta->price);
            }
            if (isset($listing->meta->formattedFacts['rooms'])) {
                $listingJson['rooms'] = $listing->meta->formattedFacts['rooms']['value'];
            }
            if (!empty($listing->meta->livingAreaStr)) {
                // take 180 from "180 + 1 101 kvm"
                $area = explode('+', $listing->meta->livingAreaStr);
                $listingJson['area'] = (int)preg_replace('/\D/', '', $area[0]);
            }
            if (!empty($listing->meta->firstPublished)) {
                $listingJson['date'] = $listing->meta->firstPublished;
            }
            $results[] = $listingJson;
        }
    }
    return $results;
}

function getListing($request)
{
    $listingId = $request['listing_id'];
    $postId  = Fasad::getWpId($listingId);
    $listing = Fasad::expandObject($postId, $listingId);

    // Remove potentially sensitive data
    unset($listing->orderNumber);
    unset($listing->bids);
    unset($listing->sellers);
    unset($listing->buyers);

    return $listing;
}
