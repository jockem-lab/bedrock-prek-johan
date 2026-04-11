<?php

namespace App\View\Composers;

use PrekWeb\PrekWeb;
use Roots\Acorn\View\Composer;

use function App\attributeLoop;
use function App\getAttribute;

class App extends PrekComposer
{
    /**
     * List of views served by this composer.
     *
     * @var array
     */
    protected static $views = [
        '*',
    ];


    /**
     * Data to be passed to view before rendering.
     *
     * @return array
     */
    public function with()
    {
        return [
            'siteName'      => $this->siteName(),
            'siteOptions'   => self::getOptions(),
            'corporationID' => $this->corporationID,
        ];
    }

    /**
     * Returns the site name.
     *
     * @return string
     */
    public function siteName()
    {
        return get_bloginfo('name', 'display');
    }


    public static function getSettings()
    {
        return [
            'heroSettings'  => [
                'minWidth'        => 414,
                'maxWidth'        => 2560,
                'minHeight'       => 275,
                'maxHeight'       => 1300,
                'nrOfSizes'       => 3,
                'quality'         => 90,
                'nrOfImageSlides' => 2
            ],
        ];
    }

    public static function getOptions()
    {
        return function_exists('get_fields') ? (get_fields('option') ?? []) : [];
    }

    public static function getOption($optionName, $default = false)
    {
        if (!function_exists('get_field')) {
            return $default;
        }
        $optionName = explode('.', $optionName);
        if (is_array(($optionName)) && count($optionName) > 1) {
            $field = getAttribute($optionName[1], get_field($optionName[0], 'option'));
        } elseif (is_array($optionName)) {
            $field = get_field($optionName[0], 'option');
        } else {
            $field = get_field($optionName, 'option');
        }
        return ($field !== "" && !is_null($field)) ? $field : $default;
    }

    public static function getListingsPage()
    {
        $listingsPage    = [];
        $tmpListingsPage = App::getOption('default_listings_page', false);
        if ($tmpListingsPage) {
            $listingsPage['url']   = get_permalink($tmpListingsPage);
            $listingsPage['title'] = get_the_title($tmpListingsPage);
        } else {
            $pages = get_pages();
            //try to find listings page
            foreach ($pages as $page) {
                if ($page->post_name === 'till-salu') {
                    $listingsPage['url']   = get_permalink($page);
                    $listingsPage['title'] = get_the_title($page);
                    break;
                }
            }
        }
        return $listingsPage;
    }

    public static function isListingTax($tax = null): bool
    {
        if ($tax == null) {
            $queriedObject = get_queried_object();
            if (!$queriedObject instanceof \WP_Term) {
                return false;
            }
            $tax = $queriedObject->taxonomy;
        } else {
            if (!str_contains($tax, 'fasad_listing_')) {
                $tax = 'fasad_listing_' . $tax;
            }
        }
        return in_array($tax, [
                'fasad_listing_type',
                'fasad_listing_district',
                'fasad_listing_districtinfo',
                'fasad_listing_city',
                'fasad_listing_commune',
                'fasad_listing_tag',
            ]) && taxonomy_exists($tax);
    }

    public static function isListingOrPreview(): bool
    {
        if (!class_exists('FasadBridge\\Includes\\PublicSettings')) {
            return false;
        }
        return (
                get_post_type() == \FasadBridge\Includes\PublicSettings::FASAD_LISTING_POST_TYPE
                || get_query_var(\FasadBridge\Includes\PublicSettings::FASAD_LISTING_POST_TYPE)
            )
            && !get_query_var('taxonomy')
            && !is_search();
    }
}
