<?php

namespace App\View\Composers;

use FasadBridge\Includes\Fetching\Image;
use PrekWeb\Includes\Fasad;
use PrekWeb\Includes\Helpers;

use PrekWebHelper\PrekWebHelper;

use function App\attributesToString;
use function App\formatFact;
use function App\formatImages;
use function App\formatCoworker;
use function App\getAttribute;


class SingleListing extends PrekComposer
{
    protected static $views = [
        'fasad.content-single-fasad_listing',
        'partials.hero',
        'forms.*',
    ];
    protected $imageSettings = ["w" => 1400, "h" => 760, "m" => "inside"];
    protected $images = null;
    protected $media = null;
    protected $videos = null;
    protected $documents = null;
    protected $floorplans = null;
    protected $facts = null;
    protected $realtors = null;
    protected $bidSettings = null;

    public function with()
    {
        return [
            'listing'     => $this->listing,
            'salestexts'  => $this->listing ? $this->salesTexts() : '',
            'realtors'    => $this->listing ? $this->realtors() : '',
            'shortfacts'  => $this->listing ? $this->shortfacts() : '',
            'videos'      => $this->listing ? $this->videos() : '',
            'images'      => $this->listing ? $this->images() : '',
            'floorplans'  => $this->listing ? $this->floorplans() : '',
            'objectFacts' => $this->listing ? $this->objectFacts() : '',
            'showings'    => $this->listing ? $this->showings() : '',
            'map'         => $this->listing ? $this->map() : '',
        ];
    }

    public function override()
    {
        $data   = [];
        $slides = [];
        if ($this->listing && $this->listing->objectImages) {
            $count         = 0;
            $heroImages    = App::getOption('listing-hero_images', 1);
            $heroPosition  = App::getOption('listing-hero_images_position');
            $attributes    = [
                'class' => "object-position-" . $heroPosition,
            ];
            $imageSettings = App::getSettings()['heroSettings'];
            foreach ($this->listing->objectImages as $objectImage) {
                if ($count >= $heroImages) {
                    break;
                }
                $slides[]['image'] = Hero::transformImage($objectImage->path, $objectImage->path, $imageSettings, $attributes);
                $count++;
            }
        }
        if (count($slides)) {
            $data['hero']['slides']  = $slides;
            $data['hero']['menu']    = $this->getMenu();
            $data['hero']['content'] = $this->getHeroContent();
            $data['hero']['floats']  = $this->getHeroFloats();
        }
        return $data;
    }

    private function bidSettings()
    {
        if (!is_null($this->bidSettings)) {
            return $this->bidSettings;
        }
        $bidSettings = [
            'show' => [
                'label'   => false,
                'highest' => false,
                'list'    => false,
            ],
        ];
        if (!empty($this->listing->biddingPolicy)) {
            if ($this->listing->biddingPolicy->id >= 2) {
                $bidSettings['show']['label'] = true;
            }
            if ($this->listing->biddingPolicy->id >= 3 && !empty($this->listing->bids)) {
                $bidSettings['show']['highest'] = true;
            }
            if ($this->listing->biddingPolicy->id >= 5 && !empty($this->listing->bids)) {
                $bidSettings['show']['list'] = true;
            }
        }
        $this->bidSettings = $bidSettings;
        return $this->bidSettings;
    }

    private function getHeroFloats(): array
    {
        $floats       = [];
        $biddingFloat = $this->getBiddingFloat();
        $showingFloat = $this->getShowingFloat();
        if ($biddingFloat) {
            $floats[] = $biddingFloat;
        }
        if ($showingFloat) {
            $floats[] = $showingFloat;
        }
        return $floats;
    }

    private function getBiddingFloat(): array
    {
        $content     = [];
        if ($this->listing->sold) {
            return $content;
        }
        if ($this->listing->has->biddings) {
            $bidSettings = $this->bidSettings();
            if ($bidSettings['show']['label']) {
                $content[] = 'Budgivning pågår';
            }
            if ($bidSettings['show']['highest']) {
                $unit      = !empty($this->listing->economy->price->primary->currency->alias) ? $this->listing->economy->price->primary->currency->alias : 'kr';
                $content[] = 'Aktuellt bud: ' . \PrekWebHelper\Includes\Helpers::numberFormat($this->listing->bids[0]->amount, 0, $unit);
            }
            if ($bidSettings['show']['list']) {
                $content[] = '<a class="anchor-link underline" href="#bids">Se budhistorik</a>';
            }
        }
        return $content;
    }

    private function getShowingFloat(): array
    {
        $content = [];
        if ($this->listing->sold) {
            return $content;
        }
        if ($this->listing->anyBookable) {
            $firstShowing = !empty($this->listing->showingsFormatted[0]) ? $this->listing->showingsFormatted[0] : false;
            if ($firstShowing) {
                $content[] = 'Nästa visning';
                $content[] = $firstShowing->readable;
                $content[] = '<a class="anchor-link underline" href="#visningsanmalan">Visningsanmälan</a>';
            }
        } else {
            if ($this->listing->noShowingDefaultText) {
                $content[] = $this->listing->noShowingDefaultText;
            }
        }
        return $content;
    }

    private function getHeroContent()
    {
        $content = [];
//        if (!empty($this->listing->price)) {
//            $content['subtitle'] = $this->listing->price;
//        }
        if (!empty($this->listing->formattedFacts['address']['value'])) {
            $content['title'] = $this->listing->formattedFacts['address']['value'];
        }
        return $content;
    }

    private function getMenu()
    {
        $menu = [];
        if ($this->images()) {
            $menu[] = (object)[
                'href'          => '#bilder',
                'attributesStr' => '',
                'title'         => 'Bilder',
            ];
        }
        if ($this->floorplans()) {
            $menu[] = (object)[
                'href'          => '#planritning',
                'attributesStr' => '',
                'title'         => 'Planritning',
            ];
        }
        if ($this->facts()) {
            $menu[] = (object)[
                'href'          => '#fakta',
                'attributesStr' => '',
                'title'         => 'Fakta',
            ];
        }
        if($this->documents()) {
            $menu[] = (object)[
                'href'          => '#documents',
                'attributesStr' => '',
                'title'         => 'Dokument',
            ];

        }
//        error_log(print_r($this->images(), true));
//        if($this->images())
        return $menu;
    }

    private function salesTexts()
    {
        return [
            'salesTitle'     => $this->listing->salesTitle ?: 'Om bostaden',
            'salesText'      => nl2br($this->listing->salesText),
            'salesTextShort' => $this->listing->salesTextShort,
        ];
    }

    private function shortfacts()
    {
        $formattedFacts = $this->listing->formattedFacts;
        $shortfacts     = [];
        $facts          = [];
        $wrapperClass   = [
            'shortfacts',
            'col-span-12',
        ];
        $realtors       = $this->realtors();
        if ($realtors && count($realtors['realtors']) === 2) {
            $wrapperClass[] = 'md:col-span-6';
        } else {
            $wrapperClass[] = 'md:col-span-9';
        }

        if (!empty($formattedFacts['address'])) {
            $facts['address'] = $formattedFacts['address'];
        }
        if (!empty($formattedFacts['district'])) {
            $facts['district'] = $formattedFacts['district'];
        }
        if (!empty($formattedFacts['descriptionType'])) {
            $facts['descriptionType'] = [
                'label' => $this->listing->is->local || $this->listing->is->commercial ? 'Objekttyp' : 'Bostadstyp',
                'value' => $formattedFacts['descriptionType']['value']
            ];
        }
        if (!empty($formattedFacts['rooms'])) {
            $facts['rooms'] = ['label' => 'Antal rum', 'value' => $formattedFacts['rooms']['value']];
        }
        if (!empty($this->listing->livingArea)) {
            $label = 'Boarea';
            $value = $this->listing->livingAreaStr; //Boarea and biarea are both in here
            if (!empty($this->listing->secondaryArea)) {
                $label .= ' & biarea';
            }
            /*if (!empty($formattedFacts['livingAreaUnit']) && $formattedFacts['livingAreaUnit']['value']) {
                // Use different unit if set in fasad_bridge_facts filter
                $value = str_ireplace('kvm', $formattedFacts['livingAreaUnit']['value'], $value);
            }*/
            $facts['livingarea'] = ['label' => $label, 'value' => $value];
        }

        if (!empty($this->listing->local->totalBuildingArea->size)) {
            $label = 'Lokalarea';
            $value = \PrekWebHelper\Includes\Helpers::numberFormat($this->listing->local->totalBuildingArea->size, 0, mb_strtolower($this->listing->local->totalBuildingArea->unit));
            $facts['localarea'] = ['label' => $label, 'value' => $value];
        } elseif (!empty($this->listing->localMinArea) && !empty($this->listing->localMaxArea)) {
            $label = 'Lokalarea';
            $value = \PrekWebHelper\Includes\Helpers::numberFormat($this->listing->localMinArea);
            $value .= ' - ';
            $value .= \PrekWebHelper\Includes\Helpers::numberFormat($this->listing->localMaxArea, 0, 'kvm');
            $facts['localarea'] = ['label' => $label, 'value' => $value];
        }

        if (!empty($this->listing->plotAreaStr)) {
            $plotAreaStr = $this->listing->plotAreaStr;
            foreach ($formattedFacts['areas'] as $area) {
                if ($area['label'] === 'Tomtarea') {
                    $plotAreaStr = strtolower($area['value']);
                }
            }
            $facts['plot'] = ['label' => 'Tomtareal', 'value' => $plotAreaStr];
        }

        if ($this->listing->is->house) {
            if (!empty($formattedFacts['built'])) {
                $facts['built'] = ['label' => 'Byggnadsår', 'value' => $formattedFacts['built']['value']];
            }
        }

        $priceComment = '';
        $currencyAlias = !empty($this->listing->economy->price->primary->currency->alias) ? $this->listing->economy->price->primary->currency->alias : 'kr';
        if (!empty($this->listing->price) && !$this->listing->sold) {
            if (!empty($this->listing->economy->price->primary->amount)) {
                $priceComment = $this->listing->economy->price->primary->comment;
            } elseif (!empty($this->listing->economy->price->secondary->amount)) {
                $priceComment = $this->listing->economy->price->secondary->comment;
            }

            $facts['price'] = ['label' => 'Pris', 'value' => $this->listing->price . ' ' . $priceComment];
        }
        if (!empty($this->listing->economy->price->final) && $this->listing->sold) {
            $facts['price'] = [
                'label' => 'Slutpris',
                'value' => \PrekWebHelper\Includes\Helpers::numberFormat($this->listing->economy->price->final, 0, $currencyAlias, ' ')
            ];
        }
        if (!empty($formattedFacts['fee']) && !$this->listing->sold) {
            $fee          = \PrekWebHelper\Includes\Helpers::numberFormat($formattedFacts['fee']['value'], 0, $currencyAlias, ' ') . '/mån';
            $facts['fee'] = [
                'label' => $this->listing->is->local ? 'Hyra' : 'Månadsavgift',
                'value' => $fee
            ];
        }
        if (!empty($formattedFacts['floor'])) {
            $facts['floor'] = ['label' => 'Våningsplan', 'value' => $formattedFacts['floor']['value']];
        }
        $shortfacts['facts']        = $facts;
        $shortfacts['wrapperClass'] = implode(' ', $wrapperClass);
        return !empty($shortfacts['facts']) ? $shortfacts : [];
    }

    private function realtors()
    {
        if (!is_null($this->realtors)) {
            return $this->realtors;
        }
        $realtors     = [];
        $coworkers    = [];
        $wrapperClass = [
            'realtors',
            'grid',
            'gap-3',
            'col-span-12',
//            'grid-cols-12',
//            'md:col-span-6',
        ];
        $showType = App::getOption('listing-show_realtor_type', true);
        foreach ($this->listing->realtors as $realtor) {
            if ($coworker = formatCoworker(Fasad::getRealtors($realtor->id))) {
                $type = $showType ? $realtor->type : '';
                $coworkers[] = (object)[
                    'name'            => getAttribute('meta.firstname', $coworker) . ' ' . getAttribute('meta.lastname', $coworker),
                    'cellphone'       => getAttribute('meta.cellphone', $coworker),
                    'cellphoneString' => getAttribute('meta.cellphoneString', $coworker),
                    'phone'           => getAttribute('meta.phone', $coworker),
                    'phoneString'     => getAttribute('meta.phoneString', $coworker),
                    'email'           => getAttribute('meta.email', $coworker),
                    'title'           => getAttribute('meta.title', $coworker),
                    'titleExtra'      => getAttribute('meta.titleExtra', $coworker),
                    'image'           => getAttribute('meta.image', $coworker),
                    'type'            => $type,
                    'permalink'       => '',
                ];
            }
        }
        if (count($coworkers) === 1) {
            $wrapperClass[] = 'grid-cols-6';
            $wrapperClass[] = 'md:col-span-3';
        } elseif (count($coworkers) === 2) {
            $wrapperClass[] = 'grid-cols-12';
            $wrapperClass[] = 'md:col-span-6';
        } else {
            $wrapperClass[] = 'grid-cols-12';
            $wrapperClass[] = 'md:col-span-12';
        }
        $realtors['realtors']     = $coworkers;
        $realtors['wrapperClass'] = implode(' ', $wrapperClass);
        $this->realtors = !empty($realtors['realtors']) ? $realtors : [];
        return $this->realtors;
    }

    private function videos()
    {
        if (!is_null($this->videos)) {
            return $this->videos;
        }
        $videos    = [];
        $videosTmp = [];
        $class     = ['col-span-12'];
        $media     = $this->media();
        $videoSettings = [
            'controls' => 1,
            'autoplay' => 0,
            'mute' => 0,
            'background' => 0
        ];
        if ($media) {
            foreach ($media as $mediaItem) {
                if (in_array($mediaItem->alias, ['Film']) && !in_array($mediaItem->url, ['http://', 'https://'])) {
                    if ($youtubeUrl = Helpers::getYoutubeVideoSrc($mediaItem->url, $videoSettings)) {
                        $videosTmp[] = [
                            'class' => $class,
                            'src'   => $youtubeUrl,
                            'type'  => 'embed',
                        ];
                    } elseif ($vimeoUrl = Helpers::getVimeoVideoSrc($mediaItem->url, $videoSettings)) {
                        $videosTmp[] = [
                            'class' => $class,
                            'src'   => $vimeoUrl,
                            'type'  => 'embed',
                        ];
                    } else {
                        $videosTmp[] = [
                            'class'   => $class,
                            'sources' => ['default' => $mediaItem->url],
                            'type'    => 'selfhosted',
                        ];
                    }
                }
            }
        }
        if (!empty($videosTmp)) {
            $objectImages = $this->images();
            foreach ($videosTmp as $key => &$video) {
                $poster          = !empty($objectImages['images'][$key]['src']) ? $objectImages['images'][$key]['src'] : '';
                $video['poster'] = $poster;
            }
        }
        if (!empty($videosTmp)) {
            $videos['heading'] = count($videosTmp) > 1 ? 'Filmer' : 'Film';
            $videos['videos']  = $videosTmp;
        }
        $this->videos = $videos;
        return $this->videos;
    }
    private function images()
    {
        if (!is_null($this->images)) {
            return $this->images;
        }
        $this->images = $this->getImages($this->listing->objectImages, ['visibleImages' => App::getOption('listing-visible_images', 3)]);
        return $this->images;
    }

    private function floorplans()
    {
        if (!is_null($this->floorplans)) {
            return $this->floorplans;
        }
        $this->floorplans = $this->getImages($this->listing->floorplanImages);
        return $this->floorplans;
    }

    private function objectFacts()
    {
        return [
            'facts'        => $this->facts(),
            'descriptions' => $this->descriptions(),
            'documents'    => $this->documents(),
            'links'        => $this->links(),
            'bids'         => $this->bids(),
        ];
    }

    private function showings()
    {
        $data = [];

        if ($this->listing->sold) {
            return $data;
        }

        $this->listing = Fasad::loadProjectData($this->listing, 'showings');

        if ($this->listing->anyBookable) {
            foreach ($this->listing->showingsFormatted as $index => $showing) {
                $slots = [];
                $date  = $showing->day . ' ' . $showing->daynum . '/' . $showing->monthnum;

                if (!empty($showing->slots)) {
                    foreach ($showing->slots as $key => $slot) {
                        $value = 'Visningsgrupp ' . ++$key;
                        if (!empty($slot->starttime)) {
                            $value = $slot->starttime;
                            if (!empty($slot->endtime)) {
                                $value .= ' - ' . $slot->endtime;
                            }
                        }
                        if ($value) {
                            $slots[] = (object)[
                                'id'       => $slot->id,
                                'label'    => $value,
                                'disabled' => $slot->fullybooked == 1 ? true : false,
                            ];
                        }
                    }
                }
                $data['showings'][$index] = (object)[
                    'date'          => $date,
                    'day'           => $showing->day,
                    'daynum'        => $showing->daynum,
                    'month'         => $showing->month,
                    'showingid'     => $showing->showingid,
                    'timeformatted' => $showing->timeformatted,
                    'comment'       => $showing->comment,
                    'slots'         => $slots,
                ];
            }
        } else {
            if ($this->listing->noShowingDefaultText) {
                $data['no_showings_text'] = $this->listing->noShowingDefaultText;
            }
        }
        return $data;
    }


    private function facts()
    {
        if (!is_null($this->facts)) {
            return $this->facts;
        }
        $tmpFacts = [];
        $facts    = [];
        foreach ($this->listing->formattedFacts as $key => $formattedFact) {
            if ($key === 'fee') {
                $currencyAlias = !empty($this->listing->economy->price->primary->currency->alias) ? $this->listing->economy->price->primary->currency->alias : 'kr';
                $formattedFact['value'] = $formattedFact['value'] . ' ' . $currencyAlias . '/mån';
            }
            //Simple fact
            if (array_key_exists('label', $formattedFact) && array_key_exists('value', $formattedFact)) {
                $tmpFacts[$key]['label'] = $formattedFact['label'];
                $tmpFacts[$key]['value'] = is_array($formattedFact['value']) ? implode(', ', $formattedFact['value']) : $formattedFact['value']; //additionalDescriptionTypes seems to be array
            } else {
                formatFact($key, $formattedFact, $tmpFacts);
            }
        }
        $this->facts = $tmpFacts;
        return $this->facts;
        //maybe do this?
        if (count($tmpFacts) > 0) {
            $half           = ceil(count($tmpFacts) / 2);
            $count          = 0;
            $facts['left']  = [];
            $facts['right'] = [];
            foreach ($tmpFacts as $key => $fact) {
                if ($count < $half) {
                    $facts['left'][$key] = $fact;
                } else {
                    $facts['right'][$key] = $fact;
                }
                $count++;
            }
        }
        return $facts;
    }

    private function descriptions()
    {
        $descriptions = [];
        if (!empty($this->listing->descriptions)) {
            foreach ($this->listing->descriptions as $key => $description) {
                if (strip_tags(trim($description->content)) === '') {
                    continue;
                }
                // use title if set, or alias if title is not set
                $title = $description->category->alias ?? '';
                if (!empty($description->title)) {
                    $title = $description->title;
                }
                $descriptions['description_' . $key] = ['title' => $title, 'content' => $description->content];
            }
        }
        return $descriptions;
    }

    private function documents()
    {
        if (!is_null($this->documents)) {
            return $this->documents;
        }
        $documents = [];
        if (property_exists($this->listing->documents, 'listingDocuments')) {
            foreach ($this->listing->documents->listingDocuments as $document) {
                $documents[] = $document;
            }
        }
        if (property_exists($this->listing->documents, 'apartmentCooperative')) {
            foreach ($this->listing->documents->apartmentCooperative as $document) {
                $documents[] = $document;
            }
        }
        $this->documents = $documents;
        return $this->documents;
    }

    private function media()
    {
        if (!is_null($this->media)) {
            return $this->media;
        }
        $media = [];
        if ($this->listing->media) {
            foreach ($this->listing->media as $mediaItem) {
                $media[] = $mediaItem;
            }
        }
        $this->media = $media;
        return $this->media;
    }

    private function links()
    {
        $links = [];
        $media = $this->media();
        if ($media) {
            foreach ($media as $mediaItem) {
                if (!in_array($mediaItem->alias, ['Film'])) {
                    $links[] = $mediaItem;
                }
            }
        }
        return $links;
    }

    private function bids()
    {
        $bids = [];
        if ($this->bidSettings()['show']['list']) {
            $unit = !empty($this->listing->economy->price->primary->currency->alias) ? $this->listing->economy->price->primary->currency->alias : 'kr';
            foreach ($this->listing->bids as $bid) {
                try {
                    $timezone = new \DateTimeZone('Europe/Stockholm');
                    $dateTime = new \DateTime($bid->bidDate, $timezone);
                    $bids[]   = [
                        'id'     => $bid->bidder->id,
                        'date'   => wp_date('j/n H:i', $dateTime->getTimestamp(), $timezone),
                        'amount' => \PrekWebHelper\Includes\Helpers::numberFormat($bid->amount, 0, $unit),
                    ];
                } catch (\Exception $e) {
                    continue;
                }
            }
        }
        return $bids;
    }

    private function getImages($imageArray, $settings = [])
    {
        $hiddenImages  = 0;
        $rows          = 0;
        $visibleImages = $settings['visibleImages'] ?? -1;
        $images        = [
            'total'         => 0,
            'hiddenImages'  => $hiddenImages,
            'visibleImages' => $visibleImages,
            'rows'          => $rows,
            'images'        => [],
        ];
        if (is_array($imageArray) && !empty($imageArray)) {
            $images = formatImages($imageArray, $this->imageSettings);
            foreach ($images['images'] as &$image) {

//                if($image['orientation'] === 'landscape' || ($image['orientation'] === 'portrait' && ($image['portraitType'] === 'last' || $image['single']))){
//                    $rows++;
//                }
//                $rows++;
                $class = [
                    'overflow-hidden',
                    'image-wrapper',
                    'col-span-12',
                ];
                if ($image['orientation'] === 'portrait') {
                    $class[] = 'md:col-span-6';
                    if ($image['single']) {
                        $class[] = 'md:col-start-4'; //center single portrait
                    }
                    $image['sizes'] = '(min-width:1400px) 650px, (min-width:768px) calc( ( 100vw - 60px ) - 2rem ), 100vw';
                } else {
                    $image['sizes'] = '(min-width:1400px) 1340px, (min-width:768px) calc( 100vw - 60px ), 100vw';
                }
                if ($visibleImages > 0 && $rows >= $visibleImages) {
                    $class[] = 'hidden';
                    $hiddenImages++;
                }
                $image['class'] = isset($image['class']) ? array_merge($class, $image['class']) : $class;

                //Image is landscape, or single portrait or last portrait. Count this as a row
                if($image['orientation'] === 'landscape' || ($image['orientation'] === 'portrait' && ($image['single'] || $image['portraitType'] === 'last'))){
                    $rows++;
                }
            }
            $images['hiddenImages']  = $hiddenImages;
            $images['rows']          = $rows;
            $images['visibleImages'] = $visibleImages;
        }
        return $images['total'] > 0 ? $images : [];
    }

    private function map()
    {
        $map = false;
        $lat = getAttribute('location.lat', $this->listing);
        $lon = getAttribute('location.lon', $this->listing);
        if ($lat && $lon) {
            $map          = [];
            $location     = [];
            $data         = [];
            $zoom         = App::getOption('listing-map_zoom', 14);
            $data['zoom'] = $zoom;
            $data['lat']  = $lat;
            $data['lon']  = $lon;
            $address      = getAttribute('location.address', $this->listing);
            if ($address) {
                $location['address'] = $address;
                if ($city = getAttribute('location.city', $this->listing)) {
                    if ($zipcode = getAttribute('location.zipCode', $this->listing)) {
                        $zipcode = str_replace(' ', '', $zipcode);
                        if (strlen($zipcode) === 5) {
                            $zipcode = substr_replace($zipcode, " ", 3, -strlen($zipcode));
                        }
                        $city = $zipcode . ', ' . $city;
                    }
                    $location['city'] = $city;
                }
            }
            if(!empty($location)) {
                $map['location'] = $location;
            }
            $map['data']     = attributesToString($data, asData: true);
        }
        return $map;
    }
}
