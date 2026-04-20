<?php

namespace FasadBridge\Includes\Fetching;

use FasadApiConnect\Includes\ApiConnectionHandler;

class Api extends Fetcher
{
    /**
     * @var array Listing data
     */
    protected $listing;

    /**
     * Api constructor.
     *
     * @param $id
     */
    public function __construct($id)
    {
        parent::__construct();
        $this->listing = $this->getListingPreviewDetails($id);
    }

    /**
     * Get data from API
     *
     * @param $id
     * @return array
     */
    private function getListingPreviewDetails($id)
    {
        $apiConnectionHandler = new ApiConnectionHandler();
        try {
            $listing = $apiConnectionHandler->getListing($id);
            if (!$listing || !property_exists($listing, 'data')) {
                throw new \Exception('Not a preview object');
            }
            $listingData    = $listing->data;
            $isValidPreview = $this->isValidPreview($listingData);
            if (!apply_filters('fasad_bridge_showPreview', $isValidPreview, $listingData)) {
                throw new \Exception('Don\'t want to show preview object');
            }

            $listingData->documents = '';
            $listingData->showings = '';
            $listingData->bids = '';
            $listingData->biddingPolicy = '';

            if ($listingData->has->documents == 1) {
                $documents = $apiConnectionHandler->getDocuments($id);
                if (!empty($documents->data)) {
                    $listingData->documents = $documents->data;
                }
            }

            if ($listingData->has->showings == 1) {
                $showings = $apiConnectionHandler->getShowings($id);
                if (!empty($showings->data->showings)) {
                    $listingData->showings = $showings->data->showings;
                }
            }

            if ($listingData->has->biddings == 1) {
                $biddings = $apiConnectionHandler->getBiddings($id);
                $listingData->biddingStarted = $biddings->data->started;
                if (!empty($biddings->data->bids)) {
                    $listingData->bids = $biddings->data->bids;
                }
                if (!empty($biddings->data->biddingPolicy)) {
                    $listingData->biddingPolicy = $biddings->data->biddingPolicy;
                }
                if (!empty($biddings->data->cancelledBids)) {
                    $listingData->cancelledBids = $biddings->data->cancelledBids;
                }
            }

            $servitudes = $apiConnectionHandler->getServitudes($id);
            if (!empty($servitudes->data)) {
                $listingData->servitudes = $servitudes->data;
            }

        } catch (\Exception $e) {
            $listingData = [];
            do_action('prek_log_exception', $e);
        }

        return $listingData;
    }

    private function isValidPreview($listingData): bool
    {
        $isValidPreview = false;
        if (!empty($listingData->preview)) {
            if ($listingData->preview->activated == 1) {
                $timezone           = new \DateTimeZone('Europe/Stockholm');
                $now                = new \DateTime('NOW', $timezone);
                $previewActivatedAt = new \DateTime($listingData->preview->activatedAt, $timezone);
                $lastPublishedAt    = !empty($listingData->lastPublished) ? new \DateTime($listingData->lastPublished, $timezone) : false;
                $limit              = $lastPublishedAt ? 7 : 21;
                if ($previewActivatedAt->diff($now)->days <= $limit) {
                    if (!$lastPublishedAt || $previewActivatedAt > $lastPublishedAt) {
                        $isValidPreview = true;
                    }
                }
            }
        }
        if (!$isValidPreview && !empty($listingData->belongsToNewConstruction)) {
            $project = new Api($listingData->belongsToNewConstruction->masterId);

            if (!empty($project->getData())) {
                $isValidPreview = true;
            }
        }
        return $isValidPreview;
    }

    /**
     * Use like ->getAttribute("economy")
     *
     * @param string $attr
     * @return mixed|null
     */
    public function getAttribute(string $attr)
    {
        $value = null;
        if (isset($this->listing->{$attr})) {
            $value = $this->listing->{$attr};
        }
        return $value;
    }

    /**
     * Use like ->getAttribute("economy.apartment.fee")
     *
     * @param string $attr
     * @return mixed|null
     */
    public function getNestedAttribute($attr)
    {
        $keys = explode(".", $attr);

        $data = $this->listing;
        $value = $this->attributeLoop($keys, $data);

        return $value;
    }

    /** Get all data for listing
     *
     * @return array
     */
    public function getData()
    {
        return $this->listing;
    }


}