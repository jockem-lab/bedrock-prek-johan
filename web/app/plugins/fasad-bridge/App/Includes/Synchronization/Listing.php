<?php


namespace FasadBridge\Includes\Synchronization;

use FasadApiConnect\Includes\ApiConnectionHandler;
use FasadBridge\FasadBridge;
use FasadBridge\Includes\Interfaces\OutputInterface;
use FasadBridge\Includes\PublicSettings;

/**
 * Class Listing
 *
 * @package FasadBridge\Includes\Synchronization
 */
class Listing extends Synchronizer
{
    protected $postType = [PublicSettings::FASAD_LISTING_POST_TYPE, PublicSettings::FASAD_PROTECTED_POST_TYPE];
    protected $typeTaxName = "fasad_listing_type";
    protected $districtTaxName = "fasad_listing_district";
    protected $districtInformationTaxName = "fasad_listing_districtinfo";
    protected $cityTaxName = "fasad_listing_city";
    protected $communeTaxName = "fasad_listing_commune";
    protected $tagTaxName = "fasad_listing_tag";

    private $syncResults = [
        'regular'   => [
            'fetched'   =>
                [
                    'published' => -1,
                    'sold'      => -1,
                    'leased'    => -1
                ],
            'updated'   => 0,
            'created'   => 0,
            'skipped'   => 0,
            'deleted'   => 0,
            'synced'    => 0,
            'syncedIds' => [],
            'deleteCheck' => [],
        ],
        'protected' => [
            'fetched'   =>
                [
                    'published' => -1
                ],
            'updated'   => 0,
            'created'   => 0,
            'skipped'   => 0,
            'deleted'   => 0,
            'synced'    => 0,
            'syncedIds' => [],
            'deleteCheck' => [],
        ]
    ];

    public $handledPostIds = [
        PublicSettings::FASAD_LISTING_POST_TYPE   => [],
        PublicSettings::FASAD_PROTECTED_POST_TYPE => []
    ];

    public function __construct(ApiConnectionHandler $apiConnectionHandler, OutputInterface $formatter = null, array $params = [])
    {
        parent::__construct($apiConnectionHandler, $formatter, $params);
    }

    /**
     * Try to get listing type based on status or activitycategory
     *
     * @param $listing
     * @return string
     */
    private function getListingType($listing)
    {
        $listingType = '';
        if (
            property_exists($listing, 'activityCategory') &&
            property_exists($listing->activityCategory, 'id') &&
            in_array($listing->activityCategory->id, [22, 23])
        ) {
            $listingType = 'protected';
        } elseif (property_exists($listing, 'leased') && $listing->leased == 1) {
            $listingType = 'leased';
        } elseif (property_exists($listing, 'published') && $listing->published == 1) {
            $listingType = 'published';
        } elseif (property_exists($listing, 'published') && $listing->sold == 1) {
            $listingType = 'sold';
        }
        return $listingType;
    }

    /**
     * Do synchronize.
     *
     * Will sync published, sold and leased listings.
     * Will delete the rest.
     */
    public function synchronize()
    {
        $listingsToSync = [];
        if (is_array($this->params['force']) && in_array($this->params['action'], ['update', 'publish'])) {
            //we have an objectsync and it's an update or publish.
            foreach ($this->params['force'] as $listingId) {
                //fetch listing from api
                try {
                    $listing = $this->apiConnectionHandler->getListing($listingId);
                    if (property_exists($listing, 'data')) {
                        $listingData = $listing->data;
                        $listingType = $this->getListingType($listingData);
                        if (in_array($listingType, ['leased', 'published', 'sold'])) {
                            /**
                             * these listingstypes are syncable for single, protected not ready yet
                             * we got a listing type, sync single
                             */
                            $listingsToSync[] = [
                                'listing'     => $listingData,
                                'listingType' => $listingType
                            ];
                        }
                    }
                } catch (\Exception $e) {
                    do_action('prek_log_exception', $e);
                    continue;
                }
            }
            if (count($listingsToSync) !== count($this->params['force'])) {
                /**
                 * $listingsToSync and force doesn't match,
                 * propably more listings to sync than we can handle,
                 * empty $listingsToSync to run a regular fetchsync
                 */
                $listingsToSync = [];
            }
        }
        if (!empty($listingsToSync)) {
            FasadBridge::log('Single Sync initiated for listings: ' . implode(", ", $this->params['force']), $this->params);
            /**
             * Not the most intuitive way to do this, but we need to reset the counter for all
             * publishtypes in syncresults otherwise we will get false error for the publishtypes
             * not synced here
             */
            foreach ($this->syncResults as &$syncResult) {
                if (isset($syncResult['fetched'])) {
                    foreach ($syncResult['fetched'] as $publishType => $value) {
                        $syncResult['fetched'][$publishType] = 0;
                    }
                }
            }
            foreach ($listingsToSync as $listingToSync) {
                FasadBridge::log('SaveSingleListing start', $this->params, [['listingId' => $listingToSync['listing']->id]]);
                $this->saveSingleListing($listingToSync['listing'], $listingToSync['listingType']);
                FasadBridge::log('SaveSingleListing end', $this->params);
            }
        } else {
            FasadBridge::log('RegularSync start', $this->params);
            if ($this->params['skipListings'] !== PublicSettings::SKIP_REGULAR) {
                //fetch, save and clean regular listings
                $this->savePublishedListings();
                $this->saveSoldListings();
                $this->saveLeasedListings();
                $removablePosts = $this->getAllExceptIds($this->handledPostIds[PublicSettings::FASAD_LISTING_POST_TYPE], PublicSettings::FASAD_LISTING_POST_TYPE, true, true);
                $fetchedPosts = $this->syncResults['regular']['fetched']['published'] + $this->syncResults['regular']['fetched']['sold'] + $this->syncResults['regular']['fetched']['leased'];
                $deleteCheck = $this->deleteCheck($removablePosts, $fetchedPosts, 10);
                $this->syncResults['regular']['deleteCheck'] = $deleteCheck;
                if ($deleteCheck['doDelete']) {
                    $this->syncResults['regular']['deleted'] = $this->deleteByPosts($removablePosts);
                }
            }

            if ($this->params['skipListings'] !== PublicSettings::SKIP_UNDERHAND) {
                //fetch, save and clean protected listings
                $this->saveProtectedListings();
                $removablePosts = $this->getAllExceptIds($this->handledPostIds[PublicSettings::FASAD_PROTECTED_POST_TYPE], PublicSettings::FASAD_PROTECTED_POST_TYPE, true, true);
                $fetchedPosts = $this->syncResults['protected']['fetched']['published'];
                $deleteCheck = $this->deleteCheck($removablePosts, $fetchedPosts);
                $this->syncResults['protected']['deleteCheck'] = $deleteCheck;
                if ($deleteCheck['doDelete']) {
                    $this->syncResults['protected']['deleted'] = $this->deleteByPosts($removablePosts);
                }
            }
            FasadBridge::log('RegularSync end', $this->params);
        }

        return $this->syncResults;
    }

    /**
     * Get the listing title
     *
     * @param $listing
     * @param $postType
     * @return string
     */
    public function getTitle($listing, $postType)
    {
        if ($postType === PublicSettings::FASAD_PROTECTED_POST_TYPE) {
            $title = $listing->id;
        } elseif (isset($listing->location->address) && isset($listing->location->district)) {
            $title = sprintf('%s, %s', $listing->location->address, $listing->location->district);
        } elseif (isset($listing->location->address)) {
            $title = $listing->location->address;
        } elseif (isset($listing->location->district)) {
            $title = $listing->location->district;
        } else {
            $title = $listing->id;
        }

        return trim($title);
    }

    /**
     *  Get published listings from api and save them
     */
    public function savePublishedListings()
    {
        $saveStart = microtime(true);
        FasadBridge::log('savePublishedListings start', $this->params);
        $listingType = 'published';
        $this->formatter->output(PHP_EOL . "- Laddar in publicerade objekt -" . PHP_EOL);
        do_action_ref_array('fasad_bridge_objects_begin', [$listingType, $this->params]);
        $fetchStart = microtime(true);
        $listings = $this->apiConnectionHandler->getPublishedListings();
        $fetchEnd = microtime(true) - $fetchStart;
        $this->syncResults['regular']['fetched']['published'] = count($listings);
        $this->save($listings, $listingType);
        do_action_ref_array('fasad_bridge_objects_complete', [$listings, $listingType, $this->params]);
        $saveEnd = microtime(true) - $saveStart;
        FasadBridge::log('savePublishedListings end', $this->params, ['totalTime' => $saveEnd, 'fetchTime' => $fetchEnd]);
    }

    /**
     *  Get protected listings from api and save them
     */
    public function saveProtectedListings()
    {
        $saveStart = microtime(true);
        FasadBridge::log('saveProtectedListings start', $this->params);
        $listingType = 'protected';
        $this->formatter->output(PHP_EOL . "- Laddar in objekt som är publicerade som underhand -" . PHP_EOL);
        do_action_ref_array('fasad_bridge_objects_begin', [$listingType, $this->params]);
        $fetchStart = microtime(true);
        $listings = $this->apiConnectionHandler->getProtectedListings();
        $fetchEnd = microtime(true) - $fetchStart;
        $this->syncResults['protected']['fetched']['published'] = count($listings);
        $this->save($listings, $listingType);
        do_action_ref_array('fasad_bridge_objects_complete', [$listings, $listingType, $this->params]);
        $saveEnd = microtime(true) - $saveStart;
        FasadBridge::log('saveProtectedListings end', $this->params, ['totalTime' => $saveEnd, 'fetchTime' => $fetchEnd]);
    }

    /**
     * Save single listing
     */
    public function saveSingleListing($listing, $listingType)
    {
        $listings = [$listing];
        do_action_ref_array('fasad_bridge_objects_begin', [$listingType, $this->params]);
        $this->syncResults['regular']['fetched'][$listingType] += count($listings);
        $this->save($listings, $listingType);
        do_action_ref_array('fasad_bridge_objects_complete', [$listings, $listingType, $this->params]);
    }

    /**
     * Get sold listings from api and save them
     */
    public function saveSoldListings()
    {
        $saveStart = microtime(true);
        FasadBridge::log('saveSoldListings start', $this->params);
        $listingType = 'sold';
        $this->formatter->output(PHP_EOL . "- Laddar in objekt som är publicerade som sålda -" . PHP_EOL);
        do_action_ref_array('fasad_bridge_objects_begin', [$listingType, $this->params]);
        $fetchStart = microtime(true);
        $listings = $this->apiConnectionHandler->getSoldListings();
        $fetchEnd = microtime(true) - $fetchStart;
        $this->syncResults['regular']['fetched'][$listingType] = count($listings);
        $this->save($listings, $listingType);
        do_action_ref_array('fasad_bridge_objects_complete', [$listings, $listingType, $this->params]);
        $saveEnd = microtime(true) - $saveStart;
        FasadBridge::log('saveSoldListings end', $this->params, ['totalTime' => $saveEnd, 'fetchTime' => $fetchEnd]);
    }

    /**
     * Get leased listings from api and save them
     */
    public function saveLeasedListings()
    {
        $saveStart = microtime(true);
        FasadBridge::log('saveLeasedListings start', $this->params);
        $listingType = 'leased';
        $this->formatter->output(PHP_EOL . "- Laddar in objekt som är publicerade som uthyrda -" . PHP_EOL);
        do_action_ref_array('fasad_bridge_objects_begin', [$listingType, $this->params]);
        $fetchStart = microtime(true);
        $listings = $this->apiConnectionHandler->getLeasedListings();
        $fetchEnd = microtime(true) - $fetchStart;
        $this->syncResults['regular']['fetched'][$listingType] = count($listings);
        $this->save($listings, $listingType);
        do_action_ref_array('fasad_bridge_objects_complete', [$listings, $listingType, $this->params]);
        $saveEnd = microtime(true) - $saveStart;
        FasadBridge::log('saveLeasedListings end', $this->params, ['totalTime' => $saveEnd, 'fetchTime' => $fetchEnd]);
    }

    /**
     * Creating and saving of listing post
     *
     * @param $data
     */
    public function save($data, $listingType)
    {
        $force = $this->params['force'];
        $updateType = $this->params['updateType'];
        foreach ($data as $listing) {
            $postType = PublicSettings::FASAD_LISTING_POST_TYPE;
            $syncResultType = 'regular';
            $dateField = 'lastPublished';
            if ($listingType === 'protected') {
                $postType = PublicSettings::FASAD_PROTECTED_POST_TYPE;
                $syncResultType = $listingType;
                $dateField = 'modifiedAt';
            }
            if ($listing->archived) {
                $dateField = 'modifiedAt';
            }
            $dateField = apply_filters('fasad_bridge_datefield_sold', $dateField, $listing);
            $this->formatter->output("-- FasAd ID $listing->id");
            $existingPost = $this->getByFasadId($listing->id, "id", $postType, true, true); //todo: Kan vi hämta draft och private här, eller bör vi bara hämta det vid protected?
            if ($existingPost) {
                $postId = $existingPost->ID;

                $postLastUpdated = get_post_meta($postId, $this->prefix . $dateField, true);
                if (($postLastUpdated != $listing->{$dateField}) || $force === 'all' || (is_array($force) && in_array($listing->id, $force))) {
                    FasadBridge::log($listing->id . ' exists and needs update (or is forced)', $this->params);
                    $this->saveDataToPost($listing, $postId, $listingType, $updateType);
                    $action = 'updated';
                    // Todo: If old and new titles are different (or always?),
                    // set post_name to empty string to generate a new slug
                } else {
                    $this->formatter->output("Ingen ny data. Hoppar över... ");
                    $action = 'skipped';
                }
            } else {
                $postId = $this->createPost($listing, $postType);
                $this->saveDataToPost($listing, $postId, $listingType, 'full'); //always full sync here
                $action = 'created';
            }
            $this->syncResults[$syncResultType][$action]++;
            $this->syncResults[$syncResultType]['synced']++;
            $this->handledPostIds[$postType][] = $postId;
            $this->syncResults[$syncResultType]['syncedIds'][] = $listing->id;
            do_action_ref_array('fasad_bridge_object_complete', [$postId, $action]);
            $this->formatter->output(PHP_EOL);
        }
    }

    private function saveDataToPost($listing, $postId, $listingType, $updateType)
    {
        if ($updateType == 'full' || in_array('showings', $updateType)) {
            $saveStart = microtime(true);
            FasadBridge::log('start saveShowings for ' . $listing->id, $this->params, ['updateType' => is_array($updateType) ? implode(', ', $updateType) : $updateType]);
            $this->saveShowings($listing, $postId);
            FasadBridge::log('end saveShowings for ' . $listing->id, $this->params, ['totalTime' => microtime(true) - $saveStart]);
        }
        if ($updateType == 'full' || in_array('bids', $updateType)) {
            $saveStart = microtime(true);
            FasadBridge::log('start saveBids for ' . $listing->id, $this->params, ['updateType' => is_array($updateType) ? implode(', ', $updateType) : $updateType]);
            $this->saveBids($listing, $postId);
            FasadBridge::log('end saveBids for ' . $listing->id, $this->params, ['totalTime' => microtime(true) - $saveStart]);
        }
        if ($updateType == 'full' || in_array('documents', $updateType)) {
            $saveStart = microtime(true);
            FasadBridge::log('start saveDocuments for ' . $listing->id, $this->params, ['updateType' => is_array($updateType) ? implode(', ', $updateType) : $updateType]);
            $this->saveDocuments($listing, $postId);
            FasadBridge::log('end saveDocuments for ' . $listing->id, $this->params, ['totalTime' => microtime(true) - $saveStart]);
        }
        if ($updateType == 'full' || in_array('servitudes', $updateType)) {
            $saveStart = microtime(true);
            FasadBridge::log('start saveServitudes for ' . $listing->id, $this->params, ['updateType' => is_array($updateType) ? implode(', ', $updateType) : $updateType]);
            $this->saveServitudes($listing, $postId);
            FasadBridge::log('end saveServitudes for ' . $listing->id, $this->params, ['totalTime' => microtime(true) - $saveStart]);
        }
        if ($updateType == 'full' || in_array('postmeta', $updateType)) {
            $saveStart = microtime(true);
            FasadBridge::log('start savePostmeta for ' . $listing->id, $this->params, ['updateType' => is_array($updateType) ? implode(', ', $updateType) : $updateType]);
            $this->savePostMeta($listing, $postId);
            FasadBridge::log('end savePostmeta for ' . $listing->id, $this->params, ['totalTime' => microtime(true) - $saveStart]);
        }

        if ($updateType == 'full' || in_array('tags', $updateType)) {
            $saveStart = microtime(true);
            FasadBridge::log('start saveTags for ' . $listing->id, $this->params, ['updateType' => is_array($updateType) ? implode(', ', $updateType) : $updateType]);
            $this->saveTags($listing, $postId);
            FasadBridge::log('end saveTags for ' . $listing->id, $this->params, ['totalTime' => microtime(true) - $saveStart]);
        }
        if ($updateType == 'full' || in_array('descriptiontype', $updateType)) {
            if (!empty($listing->descriptionType)) {
                $saveStart = microtime(true);
                FasadBridge::log('start saveDescriptions for ' . $listing->id, $this->params, ['updateType' => is_array($updateType) ? implode(', ', $updateType) : $updateType]);
                $this->saveTaxonomyRelation($listing, $postId, $listing->descriptionType->alias, $this->typeTaxName);
                FasadBridge::log('end saveDescriptions for ' . $listing->id, $this->params, ['totalTime' => microtime(true) - $saveStart]);
            }
        }
        if ($updateType == 'full' || in_array('location', $updateType)) {
            if (!empty($listing->location)) {
                $saveStart = microtime(true);
                FasadBridge::log('start saveLocation for ' . $listing->id, $this->params, ['updateType' => is_array($updateType) ? implode(', ', $updateType) : $updateType]);
                $this->saveTaxonomyRelation($listing, $postId, $listing->location->district, $this->districtTaxName);
                $this->saveTaxonomyRelation($listing, $postId, $listing->location->city, $this->cityTaxName);
                $this->saveTaxonomyRelation($listing, $postId, $listing->location->commune->alias, $this->communeTaxName);
                $this->saveDistrictInformation($listing, $postId);
                FasadBridge::log('end saveLocation for ' . $listing->id, $this->params, ['totalTime' => microtime(true) - $saveStart]);
            }
        }
    }

    private function saveShowings($listing, $postId)
    {
        if ($listing->has->showings) {
            $showings = $this->apiConnectionHandler->getShowings($listing->id);
            $showings = (!empty($showings->data->showings)) ? serialize($showings->data->showings) : '';
        } else {
            $showings = '';
        }

        update_post_meta($postId, $this->prefix . "showings", $showings);
    }

    private function saveBids($listing, $postId)
    {
        if ($listing->has->biddings) {
            $biddings      = $this->apiConnectionHandler->getBiddings($listing->id);
            $bids          = (!empty($biddings->data->bids)) ? serialize($biddings->data->bids) : '';
            $biddingPolicy = (!empty($biddings->data->biddingPolicy)) ? serialize($biddings->data->biddingPolicy) : '';
        } else {
            $bids          = '';
            $biddingPolicy = '';
        }

        update_post_meta($postId, $this->prefix . "biddingPolicy", $biddingPolicy);
        update_post_meta($postId, $this->prefix . "bids", $bids);
    }

    private function saveDocuments($listing, $postId)
    {
        if ($listing->has->documents) {
            $documents = $this->apiConnectionHandler->getDocuments($listing->id);
            $documents = (!empty($documents->data)) ? serialize($documents->data) : '';
        } else {
            $documents = '';
        }
        update_post_meta($postId, $this->prefix . "documents", $documents);
    }

    private function saveServitudes($listing, $postId)
    {
        $servitudes = $this->apiConnectionHandler->getServitudes($listing->id);

        $servitudes = (!empty($servitudes->data)) ? serialize($servitudes->data) : '';
        update_post_meta($postId, $this->prefix . "servitudes", $servitudes);
    }


    private function saveTaxonomyRelation($listing, $postId, &$attr, $taxName)
    {
        if (!taxonomy_exists($taxName)) return;
        if (empty($attr)) {
            // If the data attr is empty, we need to remove the relationship
            wp_delete_object_term_relationships($postId, $taxName);
        } else {
            wp_set_post_terms($postId, $attr, $taxName, false);
        }
    }

    private function saveDistrictInformation($listing, $postId)
    {
        //bail out early if taxonomy dont exist (this is an opt-int tax)
        if (!taxonomy_exists($this->districtInformationTaxName)) {
            return false;
        }
        // First remove all tag relations
        wp_delete_object_term_relationships($postId, $this->districtInformationTaxName);
        if (!empty($listing->location->districtInformation)) {
            $setTerms = false;
            // Update or create term
            if ($existingTerm = term_exists($listing->location->districtInformation->alias, $this->districtInformationTaxName)) {
                $update = wp_update_term(
                    $existingTerm['term_id'],
                    $this->districtInformationTaxName,
                    [
                        'description' => $listing->location->districtInformation->description
                    ]
                );
                if (!is_wp_error($update)) {
                    $setTerms = true;
                }
            } else {
                $create = wp_insert_term(
                    $listing->location->districtInformation->alias,
                    $this->districtInformationTaxName,
                    [
                        'description' => $listing->location->districtInformation->description
                    ]
                );
                if (!is_wp_error($create)) {
                    $setTerms = true;
                }
            }
            if ($setTerms) {
                wp_set_post_terms($postId, $listing->location->districtInformation->alias, $this->districtInformationTaxName, true);
            }
        }
    }

    private function saveTags($listing, $postId)
    {
        $this->deleteTaxonomyRelations($postId);

        $this->saveLifestyleTags($listing, $postId);

        $this->saveNewConstructionTags($listing, $postId);

        $this->saveHasTags($listing, $postId);

        $this->saveSoldTag($listing, $postId);

        $this->saveLeasedTag($listing, $postId);
    }

    private function saveLifestyleTags($listing, $postId)
    {
        if (!empty($listing->status)) {
            foreach ($listing->status as $lifestyle) {
                if (apply_filters('fasad_bridge_lifestyle_slug', false)) {
                    // If this filter returns true, we will use the tag in FasAd
                    // as term slug instead of the one created automatically from the name.
                    if (!term_exists($lifestyle->tag, $this->tagTaxName)) {
                        // Term not existing, we create it before wp_set_post_terms()
                        // so we can set the slug.
                        wp_insert_term(
                            $lifestyle->alias,
                            $this->tagTaxName,
                            [
                                'slug' => $lifestyle->tag
                            ]
                        );
                    }
                }
                wp_set_post_terms($postId, $lifestyle->alias, $this->tagTaxName, true);
            }
        }
    }

    private function saveNewConstructionTags($listing, $postId)
    {
        if (!empty($listing->belongsToNewConstruction->masterId)) {
            wp_set_post_terms($postId, "Projekt", $this->tagTaxName, true);
        }
    }

    private function saveSoldTag($listing, $postId)
    {
        if (!empty($listing->sold) && $listing->sold == 1) {
            wp_set_post_terms($postId, "Såld", $this->tagTaxName, true);
        }
    }

    private function saveLeasedTag($listing, $postId)
    {
        if (!empty($listing->leased) && $listing->leased == 1) {
            wp_set_post_terms($postId, "Uthyrd", $this->tagTaxName, true);
        }
    }

    private function saveHasTags($listing, $postId)
    {
        if (!empty($listing->has->biddings) && $listing->has->biddings == 1) {
            wp_set_post_terms($postId, "Budgivning pågår", $this->tagTaxName, true);
        }
    }

    private function deleteTaxonomyRelations($postId)
    {
        // First remove all tag relations - check taxonomy exists first
        if (taxonomy_exists($this->tagTaxName)) {
            wp_delete_object_term_relationships($postId, $this->tagTaxName);
        }
    }

}