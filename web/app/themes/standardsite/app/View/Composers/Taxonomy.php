<?php

namespace App\View\Composers;

use App\Common;

class Taxonomy extends PrekComposer
{
    protected static $views = [
        'taxonomy'
    ];

    private $queriedObject = null;
    private $isListingsTerm = null;
    private $isListingsTaxonomy = null;
    private $terms = null;

    public function with()
    {
        return [
            'isListingsTerm'     => $this->isListingsTerm(),
            'isListingsTaxonomy' => $this->isListingsTaxonomy(),
            'heading'            => $this->getHeading(),
            'titles'             => $this->getTitles(),
            'content'            => $this->getContent(),
            'listings'           => $this->getListings(),
            'queriedObject'      => $this->queriedObject(),
            'terms'              => $this->terms(),
            'listingsPage'       => App::getListingsPage(),
        ];
    }

    private function isListingsTaxonomy()
    {
        if (!is_null($this->isListingsTaxonomy)) {
            return $this->isListingsTaxonomy;
        }
        if (!is_null($this->queriedObject()) && $this->queriedObject() instanceof \WP_Taxonomy) {
            if (in_array($this->queriedObject()->name, [
                'fasad_listing_city',
                'fasad_listing_type',
                'fasad_listing_district',
                'fasad_listing_districtinfo',
                'fasad_listing_commune',
            ])) {
                return true;
            }
        }
        return false;
    }
    private function isListingsTerm()
    {
        if (!is_null($this->isListingsTerm)) {
            return $this->isListingsTerm;
        }
        if (!is_null($this->queriedObject()) && $this->queriedObject() instanceof \WP_Term) {
            if (in_array($this->queriedObject()->taxonomy, [
                'fasad_listing_city',
                'fasad_listing_type',
                'fasad_listing_district',
                'fasad_listing_districtinfo',
                'fasad_listing_commune',
            ])) {
                return true;
            }
        }
        return false;
    }

    private function getListings()
    {
        $listings = [];
        if (!$this->isListingsTerm() && !$this->isListingsTaxonomy()) {
            return [];
        }
        foreach ($this->terms() as $term) {
            $listingsType         = 'forsale';
            $postsPerPage = $this->isListingsTaxonomy() ? 2 : 6;
            $listingsQueryBuilder = new \FasadBridge\Includes\Fetching\ListingQueryBuilder();
            $listingsQueryBuilder->tax("tag", "slug", "sald", "NOT IN"); //exclude sold
            $tax = str_replace("fasad_listing_", "", $term->taxonomy);
            $listingsQueryBuilder->tax($tax, "slug", $term->slug, "IN");
            $listingsQueryBuilder->orderBy("firstPublished", "desc");
            $listingsQueryBuilder->postsPerPage($postsPerPage);
            //Fetch forsale
            $listingsQuery = $listingsQueryBuilder->getQuery();
            if (!$listingsQuery->have_posts()) {
                //No posts in forsale, fetch sold
                $listingsType = 'sold';
                $listingsQueryBuilder->tax("tag", "slug", "sald", "IN");
                $listingsQuery = $listingsQueryBuilder->getQuery();
            }
            if ($listingsQuery->have_posts()) {
                $posts   = \PrekWeb\Includes\Fasad::expandObjects($listingsQuery->posts);
                $objects = [];
                foreach ($posts as $listing) {
                    if ($listingJson = Common::listingToJson($listing)) {
                        $objects[] = $listingJson;
                    }
                }
                $listings[$term->slug]['listings']     = $objects;
                $listings[$term->slug]['listingsType'] = $listingsType;
            }
        }
        return $listings;
    }

    private function getHeading()
    {
        $heading = '';
        if ($this->isListingsTerm()) {
            $heading = $this->queriedObject()->name;
        } elseif ($this->isListingsTaxonomy()) {
            $heading = $this->queriedObject()->label;
        }
        return $heading;
    }

    private function getTitles()
    {
        $titles = [];
        foreach ($this->terms() as $term) {
            $forsaleTitle = 'Objekt till salu';
            $soldTitle    = 'Sålda objekt';
            if ($this->isListingsTaxonomy()) {
                $forsaleTitle .= ' - ' . $term->name;
                $soldTitle    .= ' - ' . $term->name;
            }
            $titles[$term->slug] = [
                'forsale' => $forsaleTitle,
                'sold'    => $soldTitle,
            ];
        }

        return $titles;
    }

    private function getContent()
    {
        $contents = [];
        foreach ($this->terms() as $term) {
            $contents[$term->slug] = apply_filters('the_content', $term->description);
        }
        return $contents;
    }

    private function queriedObject()
    {
        if (!is_null($this->queriedObject)) {
            return $this->queriedObject;
        }
        $queriedObject       = get_queried_object();
        if(is_null($queriedObject)) {
            $taxonomies = [
                'stad'     => 'fasad_listing_city',
                'typ'      => 'fasad_listing_type',
                'distrikt' => 'fasad_listing_district',
                'omrade'   => 'fasad_listing_districtinfo',
                'kommun'   => 'fasad_listing_commune',
            ];
            if(array_key_exists(get_query_var('pagename'), $taxonomies)){

                $taxonomy = get_taxonomy($taxonomies[get_query_var('pagename')]);
                if(!is_wp_error($taxonomy)) {
                    $queriedObject = $taxonomy;
                }
            }
        }
        $this->queriedObject = ($queriedObject instanceof \WP_Term || $queriedObject instanceof \WP_Taxonomy) ? $queriedObject : null;
        return $this->queriedObject;
    }

    private function terms()
    {
        if (!is_null($this->terms)) {
            return $this->terms;
        }
        if ($this->isListingsTaxonomy()) {
            $this->terms = get_terms([
                                      'taxonomy'   => $this->queriedObject()->name,
                                      'hide_empty' => false,
                                  ]);
        } elseif($this->isListingsTerm()) {
            $this->terms[] = $this->queriedObject();
        }
        return $this->terms;
    }
}
