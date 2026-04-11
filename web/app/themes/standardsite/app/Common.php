<?php

namespace App;

use FasadBridge\Includes\Fetching\Database;
use Illuminate\Support\Facades\DB;
use PrekWeb\Includes\Fasad;
use FasadBridge\Includes\Fetching\Image;
use PrekWeb\Includes\Helpers;
use PrekWebHelper\PrekWebHelper;

class Common
{
    public function saveJsonFiles($output = 'none')
    {
        $this->saveListings($output);
    }

    public function saveListings($output = 'none')
    {
        $path     = wp_get_upload_dir()['basedir'] . '/listings.json';
        $json     = [];
        $listings = Fasad::listings(['postsperpage' => -1, 'sold' => -1]);
        foreach ($listings as $listing) {
            $listingJson = self::listingToJson($listing);
            if($listingJson){
                $json[] = $listingJson;
            }
        }
        $this->saveJson($path, $json, $output);
    }

    public static function listingToJson($listing)
    {
        if (!$listing->meta->has->images) {
            return false; //skip if no images
        }
        $listingJson = [
            'element'        => 'a',
            'address'        => '',
            'location'       => [],
            'locationString' => '',
            'href'           => get_permalink($listing->ID),
            'sold'           => 0,
            'minilist'       => 0,
            'firstPublished' => $listing->meta->firstPublished ?: 0,
            'lastPublished'  => $listing->meta->lastPublished ?: 0,
            'sortPublished'  => '',
            'sort'           => '',
            'img'            => [
                'src' => '',
            ],
            'attributes'     => [
                'data-cy-showing' => 0,
                'data-listing-id' => $listing->meta->id,
                'data-cy' => 'listing-item',
            ],
            'facts'          => [],
            'data'           => [
                'listingId' => $listing->meta->id,
            ],
            'tags'           => [],
            'labels'         => [],
        ];
        if ($listing->meta->sold) {
            $listingJson['sold'] = 1;
            $listingJson['sortPublished'] = $listing->meta->contractDate;
        } else {
            $listingJson['sortPublished'] = $listingJson['firstPublished'];
        }

        if (!empty($listing->meta->minilist) && $listing->meta->minilist > 0) {
            $listingJson['minilist'] = 1;
            $listingJson['element'] = 'div';
            $listingJson['href'] = '';
        }
        if ($listing->meta->is->local || $listing->meta->is->commercial) {
            $listingJson['type'] = $listing->meta->formattedFacts['descriptionType']['value'];
        }
        if (!empty($listing->meta->formattedFacts['address'])) {
            $listingJson['address'] = $listing->meta->formattedFacts['address']['value'];
        }
        if (isset($listing->meta->formattedFacts['city'])) {
            $listingJson['location'][] = $listing->meta->formattedFacts['city']['value'];
        }
        if (isset($listing->meta->formattedFacts['district'])) {
            $listingJson['location'][] = $listing->meta->formattedFacts['district']['value'];
        }

        $currencyAlias = !empty($listing->economy->price->primary->currency->alias) ? $listing->economy->price->primary->currency->alias : 'kr';
        if ($listing->meta->sold) {
            if (!empty($listing->meta->economy->price->final)) {
                $listingJson['facts'][] = \PrekWebHelper\Includes\Helpers::numberFormat($listing->meta->economy->price->final, 0, $currencyAlias, ' ');
            }
        } else {
            if (!empty($listing->meta->price)) {
                $listingJson['facts'][] = $listing->meta->price;
            }
            if (isset($listing->meta->formattedFacts['fee'])) {
                $monthlyFee = $listing->meta->formattedFacts['fee']['value'];
                $value = $monthlyFee . ' ' . $currencyAlias . '/mån';
                $listingJson['facts'][] = $value;
            }
        }

        if (isset($listing->meta->formattedFacts['rooms'])) {
            $listingJson['facts'][] = $listing->meta->formattedFacts['rooms']['value'] . ' rum';
        }

        if (!empty($listing->meta->livingAreaStr)) {
            $listingJson['facts'][] = $listing->meta->livingAreaStr;
        } elseif (!empty($listing->meta->local->totalBuildingArea)) {
            $listingJson['facts'][] = \PrekWebHelper\Includes\Helpers::numberFormat($listing->meta->local->totalBuildingArea->size, 0, mb_strtolower($listing->meta->local->totalBuildingArea->unit));
        }
        if (!empty($listing->meta->plotAreaStr)) {
            $plotAreaStr = $listing->meta->plotAreaStr;
            foreach ($listing->meta->formattedFacts['areas'] as $area) {
                if ($area['label'] === 'Tomtarea') {
                    $plotAreaStr = strtolower($area['value']);
                }
            }
            $listingJson['facts'][] = $plotAreaStr;
        }

        if (!$listingJson['sold'] && !empty($listing->meta->anyBookable)) {
            $listingJson['attributes']['data-cy-showing'] = 1; //For cypress test
        }

        if ($listing->meta->has->images) {
            $image = Image::getImageUrlByVariant($listing->meta->images[0], 'large');
//                $listingJson['img']['src']        = Image::processImage($image->path, ['w' => 581, 'h' => 332]); //7:4 (as harald) large card, desktop
            $listingJson['img']['src'] = Image::processImage($image->path, ['w' => 600, 'h' => 400]); //8:4 (as pdf) large card, desktop
        }
        $tags                 = [
            'biddings'         => $listing->meta->has->biddings,
            'bookableShowings' => $listing->meta->anyBookable,
            'sold'             => $listing->meta->sold,
            'upcoming'         => 0,
        ];
        if($status = getAttribute('status', $listing->meta)) {
            if(is_array($status)) {
                foreach($status as $item) {
                    if($tag = getAttribute('tag', $item)) {
                        if(mb_strtolower($tag) === 'upcoming') {
                            $tags['upcoming'] = 1;
                            break;
                        }
                    }
                }
            }
        }
        $listingJson['labels'] = self::getLabels($listing, $tags);
        $listingJson['tags']   = $tags;
        $listingJson['sort']   = self::getSort($listingJson);
        $listingJson['locationString'] = !empty($listingJson['location']) ? implode(' - ', $listingJson['location']) : '';
        return $listingJson;
    }

    private static function getSort($listing)
    {
        /* order
        'bidding',
        'forsale',
        'upcoming',
        'sold',
        */
        if ($listing['sold']) {
            return 4;
        }
        if ($listing['tags']['upcoming'] == 1) {
            return 3;
        }
        if ($listing['tags']['biddings'] == 1) {
            return 1;
        }
        return 2; //default forsale
    }

    private static function getLabels($listing, $tags)
    {
        $labels = [];
        if (property_exists($listing->meta, 'sold') && $listing->meta->sold == 1) {
            $content = getAttribute('leased', $listing->meta) == 1 ? 'Uthyrd' : 'Såld';
            $labels[] =
                [
                    'type'    => 'sold',
                    'content' => $content,
                ];
            return $labels; //if sold, show only this label
        }
        if (property_exists($listing->meta->has, 'biddings') && $listing->meta->has->biddings == 1) {
            $labels[] = [
                'type'    => 'bidding',
                'content' => 'Budgivning pågår',
            ];
        }
        if (property_exists($listing->meta, 'anyBookable') && $listing->meta->anyBookable == 1) {
            $value = 'Visning';
            if (!empty($listing->meta->showingsFormatted[0])) {
                $firstShowing = $listing->meta->showingsFormatted[0];
                $value        .= '<br>' . $firstShowing->day . ' ' . $firstShowing->daynum . '/' . $firstShowing->monthnum;
                if (!empty($firstShowing->timeformatted)) {
                    $value .= ' ' . $firstShowing->timeformatted;
                }
            }
            $labels[] =
                [
                    'type'    => 'bookableShowings',
                    'content' => $value,
                ];
        }
        if (getAttribute('upcoming', $tags) === 1) {
            $labels[] = [
                'type'    => 'upcoming',
                'content' => 'Kommande'
            ];
        }
        return $labels;
    }

    public function saveJson($path, $content, $output = 'none'): bool
    {
        $status  = false;
        $tmpPath = $path . '-tmp';
        if ($output === 'verbose') {
            echo('<pre>' . print_r("Trying to save to $tmpPath with content", true) . '</pre>');
            echo('<pre>' . print_r($content, true) . '</pre>');
        }
        if (!file_put_contents($tmpPath, json_encode($content))) {
            if ($output !== 'none') {
                echo('<pre>' . print_r("Couldn't save $tmpPath", true) . '</pre>');
            }
            return false;
        }
        if ($output !== 'none') {
            echo('<pre>' . print_r("Saved $tmpPath", true) . '</pre>');
        }
        $doRename = false;
        if (file_exists($path)) {
            if ($output === 'verbose') {
                echo('<pre>' . print_r("$path exists", true) . '</pre>');
            }
            if (unlink($path)) {
                if ($output === 'verbose') {
                    echo('<pre>' . print_r("Unlinked $path", true) . '</pre>');
                }
                $doRename = true;
            }
        } else {
            if ($output === 'verbose') {
                echo('<pre>' . print_r("$path dont exist", true) . '</pre>');
            }
            $doRename = true;
        }
        if ($doRename) {
            if ($output === 'verbose') {
                echo('<pre>' . print_r("Trying to rename $tmpPath to $path", true) . '</pre>');
            }
            $status = rename($tmpPath, $path);
        }
        if ($status) {
            if ($output !== 'none') {
                echo('<pre>' . print_r("Renamed $tmpPath to $path", true) . '</pre>');
            }
            $optionKey = '_fasad_lastsync';
            $time      = time();
            if (update_option($optionKey, $time)) {
                if ($output === 'verbose') {
                    echo('<pre>' . print_r("Updated $optionKey with $time", true) . '</pre>');
                }
            } else {
                if ($output === 'verbose') {
                    echo('<pre>' . print_r("Couldn't update $optionKey", true) . '</pre>');
                }
            }
        }
        return $status;
    }
}

add_action('pre_get_posts', function ($query) {
    if ($query->is_main_query()) {
        if (isset($query->query['pagename'])) {
            if ($query->query['pagename'] === '_generate_json') {
                $output = isset($query->query['verbose']) ? 'verbose' : 'basic';
                $common = new Common();
                if (is_main_site()) {
                    foreach (get_sites() as $site) {
                        switch_to_blog($site->blog_id);
                        $common->saveListings($output);
                        restore_current_blog();
                    }
                } else {
                    $common->saveListings($output);
                }
                status_header(200); // To prevent wordpress to go to page not found
                exit(0);
            }
        }
    }
});
