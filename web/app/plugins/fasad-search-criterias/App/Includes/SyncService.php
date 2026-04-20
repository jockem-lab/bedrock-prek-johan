<?php

namespace FasadSearchCriterias\Includes;

use FasadApiConnect\Includes\ApiConnectionHandler;
use FasadSearchCriterias\FasadSearchCriterias;

class SyncService
{
    protected $loader;
    protected $textDomain;
    protected $metaPrefix;
    protected $apiConnectionHandler;

    public function __construct(Loader $loader, string $textDomain, string $metaPrefix)
    {
        $this->loader = $loader;
        $this->textDomain = $textDomain;
        $this->metaPrefix = $metaPrefix;
        $this->apiConnectionHandler = new ApiConnectionHandler();

        $this->loader->addAction("fasad_bridge_synchronize_complete", $this, "sync");
    }

    public function sync()
    {
        $this->delete();
        $searchCriterias = $this->fetch();
        $stats = $this->save($searchCriterias);
        do_action('prek_log_message', sprintf(
            __("Fasad Search Criterias synchronized: %d object types, %d search criterias and %d districts.",
                $this->textDomain
            ),
            $stats[FasadSearchCriterias::FASAD_SEARCH_OBJECT_TYPE_NAME],
            $stats[FasadSearchCriterias::FASAD_SEARCH_CRITERIA_NAME],
            $stats[FasadSearchCriterias::FASAD_SEARCH_DISTRICT_NAME]
        ));
        do_action('fasad_search_criterias_synchronized', $searchCriterias);
    }

    public function delete()
    {
        // Delete all existing posts
        $args = [
            'post_type'      => [
                                    FasadSearchCriterias::FASAD_SEARCH_OBJECT_TYPE_POST_TYPE,
                                    FasadSearchCriterias::FASAD_SEARCH_CRITERIA_POST_TYPE,
                                    FasadSearchCriterias::FASAD_SEARCH_DISTRICT_POST_TYPE
                                ],
            'posts_per_page' => -1,
            'post_status'    => 'any',
        ];
        $posts = get_posts($args);
        foreach ($posts as $post) {
            wp_delete_post($post->ID, true);
        }
    }

    public function fetch()
    {
        try {
            $searchCriterias = $this->apiConnectionHandler->getSearchCriterias();
        } catch (\Exception $e) {
            do_action('prek_log_message', "Error fetching search criterias: " . $e->getMessage());
            error_log("Error fetching search criterias: " . $e->getMessage());
            return [];
        }
        return apply_filters('fasad_search_criterias_fetch', $searchCriterias);
    }

    public function save($searchCriterias): array
    {
        $stats = [
            FasadSearchCriterias::FASAD_SEARCH_OBJECT_TYPE_NAME => 0,
            FasadSearchCriterias::FASAD_SEARCH_CRITERIA_NAME    => 0,
            FasadSearchCriterias::FASAD_SEARCH_DISTRICT_NAME    => 0,
        ];
        if (empty($searchCriterias)) {
            return $stats;
        }

        // Save Object Types
        if (!empty($searchCriterias->objecttypes)) {
            $sequence = 0;
            foreach ($searchCriterias->objecttypes as $objectType) {
                $post = [
                    'post_title'  => wp_strip_all_tags($objectType->alias),
                    'post_type'   => FasadSearchCriterias::FASAD_SEARCH_OBJECT_TYPE_POST_TYPE,
                    'post_status' => 'publish',
                    'menu_order'  => $sequence,
                ];
                $postId = wp_insert_post($post);
                update_post_meta($postId, $this->metaPrefix . 'id', $objectType->id);
                $sequence++;
                $stats[FasadSearchCriterias::FASAD_SEARCH_OBJECT_TYPE_NAME]++;
            }
        }

        // Save Search Criterias
        if (!empty($searchCriterias->criterias)) {
            $sequence = 0;
            foreach ($searchCriterias->criterias as $criteria) {
                $post = [
                    'post_title'  => wp_strip_all_tags($criteria->alias),
                    'post_type'   => FasadSearchCriterias::FASAD_SEARCH_CRITERIA_POST_TYPE,
                    'post_status' => 'publish',
                    'menu_order'  => $sequence,
                ];
                $postId = wp_insert_post($post);
                update_post_meta($postId, $this->metaPrefix . 'id', $criteria->id);
                update_post_meta($postId, $this->metaPrefix . 'isrequired', $criteria->isrequired);
                $sequence++;
                $stats[FasadSearchCriterias::FASAD_SEARCH_CRITERIA_NAME]++;
            }
        }

        // Save Search Districts
        if (!empty($searchCriterias->districts)) {
            $sequence = 0;
            foreach ($searchCriterias->districts as $districts) {
                $post = [
                    'post_title'  => wp_strip_all_tags($districts->alias),
                    'post_type'   => FasadSearchCriterias::FASAD_SEARCH_DISTRICT_POST_TYPE,
                    'post_status' => 'publish',
                    'menu_order'  => $sequence,
                ];
                $postId = wp_insert_post($post);
                update_post_meta($postId, $this->metaPrefix . 'id', $districts->id);
                update_post_meta($postId, $this->metaPrefix . 'polygon', $districts->polygon);
                $sequence++;
                $stats[FasadSearchCriterias::FASAD_SEARCH_DISTRICT_NAME]++;
            }
        }

        return $stats;
    }
}
