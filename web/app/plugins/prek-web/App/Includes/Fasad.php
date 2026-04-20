<?php

namespace PrekWeb\Includes;

use FasadApiConnect\Includes\ApiConnectionHandler;
use FasadBridge\Includes\PublicSettings;
use http\Env;
use PrekWeb\PrekWeb;
use FasadBridge\FasadBridge;
use FasadBridge\Includes\Fetching\{ Database, Api, Image, ListingQueryBuilder };
use FasadBridge\Includes\Synchronization\Realtor;
use FasadBridge\Includes\Synchronization\Office;

class Fasad
{

    protected $loader;
    protected $options;
    protected $scripts = [];
    public static $prefix = '_fasad_';
    private static $objectTypes = [
        'IS_APARTMENT_LOCAL'      => [19],
        'IS_GOODS'                => [39],
        'IS_BUILDING_ONLY'        => [38],
        'IS_OWNER_APARTMENT'      => [4, 35],
        'IS_NEW_CONSTRUCTION'     => [22, 23, 41, 42, 43, 44, 45, 46, 47, 49],
        'IS_COMMERCIAL'           => [5, 25, 27],
        'IS_FARM'                 => [28, 29],
        'IS_LOCAL'                => [8],
        'IS_LEASE_HOLD'           => [1, 15, 16, 17, 25],
        'IS_LEASED'               => [6, 8, 20],
        'IS_TENANCY'              => [3, 21, 27, 29],
        'IS_LOT'                  => [14, 17, 40],
        'IS_COMMERCIAL_PORTFOLIO' => [24],
        'IS_HOLIDAY_COTTAGE'      => [12, 15, 18, 21, 34],
    ];
    public $syncSetting = 'yes';

    public function __construct(\PrekWeb\Includes\Loader $loader, \PrekWeb\Includes\Options $options)
    {
        $this->loader  = $loader;
        $this->options = $options;
    }

    public function run()
    {
        add_filter(
            'acf/init',
            function () {
                if (Helpers::isPrekUser()) {
                    if (function_exists('acf_add_options_sub_page')) {
                        acf_add_options_sub_page(
                            [
                                'page_title' => 'FasAd',
                                'menu_title' => 'FasAd',
                                'parent_slug' => 'acf-options-prek-installningar'
                            ]
                        );
                        $this->addOptions();
                    }
                }
            }
        );

        add_action('acf/init', function() {
            $this->officeSyncSetting();
        });

        $this->loader->addAction('wp_enqueue_scripts', $this, 'doEnqueueScripts');
        $this->loader->addAction('wp_head', $this, 'wpHeadAddFasadData');

        $this->setUpPreview();
        $this->fasadStats();
        $this->setOgImages();
        $this->registerEndpoints();
        $this->setupInquiry();
        $this->setupSecureBids();
        $this->shareImage();
    }

    public function doEnqueueScripts()
    {
        $prekWebData = PrekWeb::getInstance()->getPluginData();
        foreach ($this->scripts as $script) {
            wp_enqueue_script($script->handle, plugins_url('assets/scripts/' . $script->file, dirname(__FILE__)), $script->deps, $prekWebData->version ?? 1.0, true);

            if (!empty($script->localize)) {
                wp_localize_script( $script->handle, $script->localize['objectName'], $script->localize['data']);
            }
            if (!empty($script->inline)) {
                wp_add_inline_script($script->handle, $script->inline);
            }
        }
    }

    private function setUpPreview()
    {
        $this->loader->addFilter('preview_body_class', $this, 'previewBodyClass', 10, 2);
        $this->loader->addFilter('custom_preview_template', $this, 'customPreviewTemplate', 10, 5);
        $this->loader->addFilter('preview_post_link', $this, 'extendedPreviewLinks', 10, 2);
    }

    public function previewBodyClass($classes, $isPreview)
    {
        if($isPreview){
            $classes[] = 'app-data';
            $classes[] = 'single';
            $classes[] = 'single-fasad_listing';
            $classes[] = 'single-fasad_listing-data';
        }
        return $classes;
    }

    public function customPreviewTemplate($path){
        if(Helpers::isSage10()){
            $path = str_replace(['index.blade.php'], ['single-fasad_listing.blade.php'], $path); //Todo: Maybe this can be done better. Sage 10 has a ViewFinder, but to much hassle right now
        }
        if(Helpers::isSage9()){
            $path = \App\locate_template(["single-fasad_listing.blade.php"]);
        }
        return $path;
    }

    private function addOptions()
    {
        if( function_exists('acf_add_local_field_group') ):

            acf_add_local_field_group(array(
                                          'key' => 'group_5e8b281e91860',
                                          'title' => 'Inställningar',
                                          'fields' => array(
                                              array(
                                                  'key' => 'field_5e8b283f6097b',
                                                  'label' => 'Synkronisera mäklare',
                                                  'name' => '_fasad_synchronize_realtors',
                                                  'type' => 'radio',
                                                  'instructions' => '',
                                                  'required' => 0,
                                                  'conditional_logic' => 0,
                                                  'wrapper' => array(
                                                      'width' => '',
                                                      'class' => '',
                                                      'id' => '',
                                                  ),
                                                  'choices' => array(
                                                      'yes' => 'Ja',
                                                      'extended' => 'Ja, utökad',
                                                      'no' => 'Nej',
                                                  ),
                                                  'allow_null' => 0,
                                                  'other_choice' => 0,
                                                  'default_value' => 'yes',
                                                  'layout' => 'vertical',
                                                  'return_format' => 'value',
                                                  'save_other_choice' => 0,
                                              ),
                                              array(
                                                  'key' => 'field_5e8b283f6097c',
                                                  'label' => 'Synkronisera kontor',
                                                  'name' => '_fasad_synchronize_offices',
                                                  'type' => 'radio',
                                                  'instructions' => '',
                                                  'required' => 0,
                                                  'conditional_logic' => 0,
                                                  'wrapper' => array(
                                                      'width' => '',
                                                      'class' => '',
                                                      'id' => '',
                                                  ),
                                                  'choices' => array(
                                                      'yes' => 'Ja',
                                                      'extended' => 'Ja, utökad',
                                                      'no' => 'Nej',
                                                  ),
                                                  'allow_null' => 0,
                                                  'other_choice' => 0,
                                                  'default_value' => 'yes',
                                                  'layout' => 'vertical',
                                                  'return_format' => 'value',
                                                  'save_other_choice' => 0,
                                              ),
                                              array(
                                                  'key' => 'field_5e9a02eb02430',
                                                  'label' => 'Antal og-bilder',
                                                  'name' => '_fasad_ogimagenum',
                                                  'type' => 'number',
                                                  'instructions' => '',
                                                  'required' => 0,
                                                  'conditional_logic' => 0,
                                                  'wrapper' => array(
                                                      'width' => '',
                                                      'class' => '',
                                                      'id' => '',
                                                  ),
                                                  'default_value' => 5,
                                                  'placeholder' => '',
                                                  'prepend' => '',
                                                  'append' => '',
                                                  'min' => 0,
                                                  'max' => 20,
                                                  'step' => 1,
                                              ),
                                          ),
                                          'location' => array(
                                              array(
                                                  array(
                                                      'param' => 'options_page',
                                                      'operator' => '==',
                                                      'value' => 'acf-options-fasad',
                                                  ),
                                              ),
                                          ),
                                          'menu_order' => 0,
                                          'position' => 'normal',
                                          'style' => 'default',
                                          'label_placement' => 'top',
                                          'instruction_placement' => 'label',
                                          'hide_on_screen' => '',
                                          'active' => true,
                                          'description' => '',
                                      ));

        endif;
    }

    private function officeSyncSetting()
    {
        $this->syncSetting = function_exists('get_field') ? get_field(self::$prefix . 'synchronize_offices', 'option') : false;
        if ($this->syncSetting === 'extended') {
            add_action('init', function(){
                $args = [
                    "labels" => ["name" => __("Butik", "fasad-bridge")],
                    "description" => '',
                    "public" => true,
                    "has_archive" => false,
                    "rewrite" => [
                        "slug" => PublicSettings::posttypesSlugs(PublicSettings::FASAD_OFFICE_EXTENDED_POST_TYPE)
                    ],
                    "menu_position" => 21,
                    "menu_icon" => "dashicons-groups"
                ];
                register_post_type(
                    PublicSettings::FASAD_OFFICE_EXTENDED_POST_TYPE,
                    apply_filters('fasad_bridge_register_posttype', $args, PublicSettings::FASAD_OFFICE_EXTENDED_POST_TYPE)
                );
            });
            add_action('fasad_bridge_office_complete', function($postId, $officeItem){
                $officeInstance = new Office(new ApiConnectionHandler());
                $existingPost    = $officeInstance->getByFasadId($officeItem->id, 'id', PublicSettings::FASAD_OFFICE_EXTENDED_POST_TYPE, true);
                if ($existingPost) {
                    $postId = $existingPost->ID;
                } else {
                    $postId = $officeInstance->createPost($officeItem, PublicSettings::FASAD_OFFICE_EXTENDED_POST_TYPE);
                    $officeInstance->savePostMeta(['id' => $officeItem->id], $postId);
                }
                $sequence = (!empty($officeItem->sequence)) ? $officeItem->sequence : 0;
                $officeInstance->savePostMeta(['sequence' => $sequence], $postId);
            }, 10, 2);

            $this->loader->addAction('save_post_fasad_office', $this, 'savePostFasadOffice', 10, 3);
        }

        if ($this->syncSetting === 'no') {
            add_filter('fasad_synchronize_offices', function($syncOffices){
                return false;
            });
        }
    }

    public static function isPreview($previewId = null)
    {
        $previewObjectId = !is_null($previewId) ? (int)$previewId : (int)get_query_var(PublicSettings::FASAD_LISTING_POST_TYPE);
        return apply_filters('prekweb_is_preview', ($previewObjectId > 0) ? $previewObjectId : false);
    }

    public static function getObjectFetcher($postId = null, $previewId = null)
    {
        $postId = $postId ?: get_the_ID();
        $fetcher = null;
        if ($postId && get_post_status($postId) &&
            (in_array(get_post_type($postId), [
                PublicSettings::FASAD_LISTING_POST_TYPE,
                PublicSettings::FASAD_PROTECTED_POST_TYPE
            ])))
        {
            $fetcher = new Database($postId);
        } elseif ($previewId = self::isPreview($previewId)) {
            // Underhand previews goes here too, both are on /objekt/<ID>
            $fetcher = new Api($previewId);
        }
        return $fetcher;
    }

    public static function expandObjects($posts)
    {
        foreach ($posts as &$listingPost) {
            $listingPost->meta = self::expandObject($listingPost->ID);
        }

        return $posts;
    }

    public static function expandObject($postId = null, $previewId = null)
    {
        $postId = $postId ?: get_the_ID();
        $settings = self::getSettings();
        $fetcher = self::getObjectFetcher($postId, $previewId);
        if (!$fetcher) {
            return [];
        }
        $isPreview = self::isPreview();
        $isUnderhand = ($postId && get_post_status($postId) && get_post_type($postId) == PublicSettings::FASAD_PROTECTED_POST_TYPE);

        $meta = $fetcher->getData();
        if (empty($meta)) {
            return [];
        }

        if ($meta->activityCategory && $meta->activityCategory->alias == 'Underhand') {
            $isUnderhand = true;
        }

        // Type
        $meta->is = (object)[
            'apartment'      => !empty($meta->formOfOwnership->alias)  ? self::isApartment($meta->formOfOwnership->alias) : 0,
            'house'          => !empty($meta->formOfOwnership->alias)  ? self::isHouse($meta->formOfOwnership->alias) : 0,
            'lot'            => !empty($meta->formOfOwnership->alias)  ? self::isLot($meta->formOfOwnership->alias) : 0,
            'farm'           => !empty($meta->formOfOwnership->alias)  ? self::isFarm($meta->formOfOwnership->alias) : 0,
            'local'          => !empty($meta->formOfOwnership->id)     ? self::isLocal($meta->formOfOwnership->id) : 0,
            'localApartment' => !empty($meta->formOfOwnership->id)     ? self::isLocalApartment($meta->formOfOwnership->id) : 0,
            'localRent'      => !empty($meta->formOfOwnership->id)     ? self::isLocalRent($meta->formOfOwnership->id) : 0,
            'commercial'     => !empty($meta->formOfOwnership->id)     ? self::isCommerical($meta->formOfOwnership->id) : 0,
            'project'        => !empty($meta->formOfOwnership->alias)  ? self::isProject($meta->formOfOwnership->alias) : 0,
            'reserved'       => !empty($meta->activityCategory->alias) ? self::isReserved($meta->activityCategory->alias) : 0,
            'booked'         => !empty($meta->activityCategory->alias) ? self::isBooked($meta->activityCategory->alias) : 0,
            'retired'        => !empty($meta->activityCategory->alias) ? self::isRetired($meta->activityCategory->alias) : 0,
            'resting'        => !empty($meta->activityCategory->alias) ? self::isResting($meta->activityCategory->alias) : 0,
        ];
        // END Type

        if (empty($meta->has)) {
            $meta->has = new \stdClass();
        }
        if (empty($meta->has->images)) {
            $meta->has->images = null;
        }
        if (empty($meta->has->documents)) {
            $meta->has->documents = null;
        }
        if (empty($meta->has->biddings)) {
            $meta->has->biddings = null;
        }
        if (empty($meta->has->showings)) {
            $meta->has->showings = null;
        }
        if (empty($meta->has->upcomingShowings)) {
            $meta->has->upcomingShowings = null;
        }

        $meta->preview   = $isPreview ?: '';
        $meta->underhand = $isUnderhand ?: '';

        $meta->objectImages = [];
        $meta->floorplanImages = [];
        $meta->listImage = null;
        $meta->label = '';
        $meta->listlabel = '';
        $tmpListImage = Image::getListImage($fetcher->getAttribute("images"), "highres", true);
        $meta->price = $fetcher->getNestedAttribute('economy.price.primary.amount') > 0 ? Helpers::numberFormat(
            $fetcher->getNestedAttribute('economy.price.primary.amount'),
            0,
            $fetcher->getNestedAttribute('economy.price.primary.currency.alias')
        ) : '';

        /* Areas */
        $fetchedAreas = $fetcher->getNestedAttribute('size.area.areas');
        $areaStrings  = [
            'livingAreaStr' => [
                'areas' => [
                    [
                        'type' => 'Boarea',
                        'name' => 'livingArea',
                    ],
                    [
                        'type' => 'Biarea',
                        'name' => 'secondaryArea',
                    ],
                ],
            ],
            'plotAreaStr'   => [
                'areas' =>
                    [
                        [
                            'type' => 'Tomtarea',
                            'name' => 'plotArea',
                        ],
                    ],
            ],
            'waterAreaStr'  => [
                'areas' => [
                    [
                        'type' => 'Vatten',
                        'name' => 'waterArea',
                    ],
                ],
            ],
            'localAreaStr'  => [
                'areas' => [
                    [
                        'type' => 'Lokal, minsta valbara area',
                        'name' => 'localMinArea',
                    ],
                    [
                        'type' => 'Lokal, största valbara area',
                        'name' => 'localMaxArea',
                    ],
                ],
            ],
        ];
        foreach ($areaStrings as $metaReadable => $values) {
            $units = apply_filters('fasad_bridge_' . $metaReadable . 'Units', [
                'unit'         => $values['unit'] ?? ' kvm',
                'sep'          => $values['sep'] ?? ' + ',
                'decimals'     => 0,
                'decimalSep'   => ',',
                'thousandsSep' => ' '
            ]);

            foreach ($values['areas'] as $area) {
                $size = self::getAreaByType($fetchedAreas, $area['type'], false, false);
                if (is_numeric($size)) {
                    $size = Helpers::numberFormat($size, $units['decimals']);
                }
                $meta->{$area['name']} = $size;

                // "create" readable string
                if (!isset($meta->{$metaReadable})) {
                    $meta->{$metaReadable} = '';
                }

                // append value
                if ($meta->{$area['name']}) {
                    // prepend separator if readable string is not empty
                    if (!empty($meta->{$metaReadable})) {
                        $meta->{$metaReadable} .= $units['sep'];
                    }
                    $meta->{$metaReadable} .= $meta->{$area['name']};
                }
            }

            // append unit
            if (!empty($meta->{$metaReadable})) {
                $meta->{$metaReadable} .= $units['unit'];
            }
        }
        /* end Areas */

        $meta->permalink = $isPreview ? trailingslashit(get_home_url() . '/objekt/' . $fetcher->getAttribute('id')) :
            ($isUnderhand ? trailingslashit(get_home_url() . '/underhandsobjekt/' . $fetcher->getAttribute('id')) :
                get_permalink($postId));
        // Showing
        $upcoming = [];
        $noShowingComment = !empty($meta->noShowingDefaultText) ? $meta->noShowingDefaultText : apply_filters('fasad_bridge_noShowingComment', 'Kontakta mäklaren för visning');
        $showingComment = '';
        $anyOpen = 0;
        $anyBookable = 0;
        $today = date("Y-m-d");
        if ($meta->has->showings == 1 && is_array($meta->showings)): //todo: investigate whitescreen on gofab. meta->has->showings was 1, but meta->showings wasnt an array. quickfix to check for array
            foreach ($meta->showings as $showingItem):
                if (!($today > $showingItem->startDate)
                    && (
                        $showingItem->openForRegistration || !apply_filters('fasad_showing_hideNotOpenForRegistration', false)
                    )
                    && (
                        is_null($showingItem->simultaneously)
                        || (!$showingItem->fullyBooked || apply_filters('fasad_showing_showFullybooked', false))
                    )
                ):
                    if (!$anyOpen) {
                        $anyOpen = 1;
                    }

                    if (
                        $showingItem->openForRegistration
                        && (
                            is_null($showingItem->simultaneously) || (!$showingItem->fullyBooked))
                    )
                    {
                        // Same as $anyOpen but without filters
                        $anyBookable = apply_filters('fasad_showing_anyBookable', 1, $showingItem);
                    }

                    $slots = [];
                    $simultaneously = is_null($showingItem->simultaneously) ? 0 : $showingItem->simultaneously;
                    if(!empty($showingItem->Slots)){
                        foreach($showingItem->Slots as $showingItemSlot){
                            $startTime = strtotime($showingItemSlot->startTime);
                            $endTime = strtotime($showingItemSlot->endTime);
                            $slot['id'] = $showingItemSlot->id;
                            $slot['starttime'] = (!$showingItem->hideStartTime && !$showingItem->hideEndTime) ? ucfirst(date_i18n('H:i', $startTime)) : '';
                            $slot['endtime'] = (!$showingItem->hideStartTime && $showingItemSlot->endTime && !$showingItem->hideEndTime) ? ucfirst(
                                date_i18n('H:i', $endTime)
                            ) : '';
                            $slot['fullybooked'] = $simultaneously > 0 ? $showingItemSlot->fullyBooked : 0; // api says that one "slot" with no intervals are fullybooked when 1 is attending
                            $slots[] = (object)$slot;
                        }
                    }
                    $startTime = strtotime($showingItem->startDate);
                    $endTime = strtotime($showingItem->endDate);
                    $item['starttime'] = !$showingItem->hideStartTime ? ucfirst(date_i18n('H:i', $startTime)) : '';
                    $item['endtime'] = !$showingItem->hideStartTime && $showingItem->endDate && !$showingItem->hideEndTime ? ucfirst(
                        date_i18n('H:i', $endTime)
                    ) : '';
                    $timeFormatted = !empty($item['starttime']) ? $item['starttime'] : '';
                    $timeFormatted .= (!empty($timeFormatted) && !empty($item['endtime'])) ? ' - ' . $item['endtime'] : '';
                    $item['showingid'] = $showingItem->id;
                    $item['timeformatted'] = $timeFormatted;
                    $item['daynum'] = date('j', $startTime);
                    $item['dayshort'] = ucfirst(wp_date('D', $startTime));
                    $item['day'] = ucfirst(wp_date('l', $startTime));
                    $item['monthnum'] = ucfirst(wp_date('n', $startTime));
                    $item['month'] = wp_date('F', $startTime);
                    $item['monthshort'] = wp_date('M', $startTime);
                    $item['year'] = wp_date('Y', $startTime);
                    $item['fulldate'] = wp_date('Y/n/j H:i:s', $startTime);
                    $item['openforregistration'] = $showingItem->openForRegistration;
                    $item['fullybooked'] = $showingItem->fullyBooked;
                    $item['comment'] = $showingItem->comment;
                    $readable = $item['day'];
                    $readable .= ' ' . $item['daynum'];
                    $readable .= ' ' . $item['month'];
                    $readable .= ' ' . $timeFormatted;
                    $item['readable'] = $readable;
                    $item['slots'] = $slots;
                    if (!empty($showingItem->comment)) {
                        $showingComment = $showingItem->comment;
                    }
                    $upcoming[] = (object)apply_filters('fasad_bridge_showing', $item);
                endif;
            endforeach;
        endif;
        $meta->showingsFormatted = array_reverse($upcoming);
        $meta->showingComment = $showingComment;
        $meta->noShowingComment = $noShowingComment;
        $meta->has->upcomingShowings = !empty($meta->showingsFormatted) ? true : '';
        $meta->anyShowingOpen = $anyOpen;
        $meta->anyBookable = $anyBookable;
        // END Showing

        if (!empty($meta->images)) {
            foreach ($meta->images as $image) {
                if ($firstVariant = Image::getImageUrlByVariant($image, 'highres', true)) {
                    if ($firstVariant->path) {
                        $firstVariant->category = isset($image->category->alias) ? $image->category->alias : '';
                        $firstVariant->id = $image->id;
                        $firstVariant->text = $image->text;
                        if (in_array($image->category->alias, $settings['imageCategories']['floorplans'])) {
                            $meta->floorplanImages[] = $firstVariant;
                        } else {
                            if (empty($meta->has->images)) {
                                $meta->has->images = true;
                            }
                            $meta->objectImages[] = $firstVariant;
                        }
                    }
                }
            }
        }

        // Static map
        $keys = PrekWeb::getInstance()->options->getOption('keys');
        $meta->mapSrc = '';
        if (!empty($keys) && $keys['google_maps']) {
            if (!empty($meta->location->lat) && !empty($meta->location->lon)) {
                $mapsApiKey = $keys['google_maps'];
                $mapStyle = '';
                $width = 640;
                $height = 500;
                $meta->mapSrc = 'https://maps.googleapis.com/maps/api/staticmap?key=' . $mapsApiKey . '&zoom=16&size=' . $width . 'x' . $height . $mapStyle
                    . '&markers=color:0x828385%7C' . $meta->location->lat . ',' . $meta->location->lon;
            }
        }
        // End Static map

        // ListImage / HeroImage
        $meta->heroImage = '';
        $meta->listImage = '';
        if ($tmpListImage && isset($tmpListImage->path)) {
            $meta->listImage = Image::processImage($tmpListImage->path, ['w' => 850, 'h' => 580]);
            $meta->heroImage = Image::processImage($tmpListImage->path, ['w' => 3000, 'h' => 2000]);
        }
        // END ListImage / HeroImage

        // Realtors
        if (!empty($meta->realtors)) {
            foreach ($meta->realtors as $realtor) {
                $realtor->phone = Helpers::formatPhone($realtor->phoneString);
                $realtor->phoneString = Helpers::formatPhoneString($realtor->phoneString, 'phone');
                $realtor->cellphone = Helpers::formatPhone($realtor->cellphoneString);
                $realtor->cellphoneString = Helpers::formatPhoneString($realtor->cellphoneString, 'cellphone');
            }
        }
        // END Realtors

        // Facts
        $meta->formattedFacts = self::facts($meta, false);
        // END Facts

        $meta->newconstructionFormatted = self::newConstruction($meta);

        // Label
        if (!empty($meta->sold) && $meta->sold == 1) {
            $label = [
                'text' => 'Såld',
                'class' => [
                    'listing-label',
                    'sold'
                ]
            ];
            $meta->label = $label;
        } elseif (has_term('Budgivning pågår', 'fasad_listing_tag', $postId)) {
            $label = [
                'text' => 'Budgivning pågår',
                'class' => [
                    'listing-label',
                    'object_bidding'
                ]
            ];
            $meta->label = $label;
            $meta->listlabel = $label;
        }

        // END Label

        return apply_filters('fasad_bridge_expandObject', $meta);
    }

    public static function protectedListings($params)
    {
        $listingsQueryBuilder = new ListingQueryBuilder(PublicSettings::FASAD_PROTECTED_POST_TYPE);
        $posts = [];
//        if (!empty($params['sold']) && $params['sold'] == 1) {
//            $listingsQueryBuilder
//                ->tax("tag", "slug", "sald")
//                ->orderBy("contractDate", "desc");
//        } else {
//            $listingsQueryBuilder->tax("tag", "slug", "sald", "NOT IN")->orderBy("firstPublished", "desc");
//            if ( ! empty($params['tag'])) {
//                if (is_numeric($params['tag'])) {
//                    $listingsQueryBuilder->tax("tag", "id", $params['tag']);
//                } else {
//                    $listingsQueryBuilder->tax("tag", "name", $params['tag']);
//                }
//            }
//        }

        self::handleTagParams($listingsQueryBuilder, $params);

        if ( ! empty($params['postsperpage'])) {
            $listingsQueryBuilder->postsPerPage($params['postsperpage']);
        }

        $defaultSort = [
            'orderby' => 'firstPublished',
            'order'   => 'desc',
        ];
        $listingsQueryBuilder->orderBy($defaultSort['orderby'], $defaultSort['order']);

//        if ( ! empty($params['biddings'])) {
//            $listingsQueryBuilder->tax("tag", "slug", "budgivning-pagar");
//        }
        $listingsQuery = $listingsQueryBuilder->getQuery();
        if ($listingsQuery->have_posts()) {
            $posts = self::expandObjects($listingsQuery->posts);
            /*
             * todo: denna kommer inte få ut nån data för att expandobjects går på "fasad_listing"
             * osäker på om vi ska hantera expandobjects eller om vi ska ska en ny typ expandprotectedobjects
             */
        }
        if ( ! empty($params['showings'])) {
            foreach ($posts as $key => $listing) {
                if ( ! $listing->meta->has->upcomingShowings == 1) {
                    unset($posts[$key]);
                }
            }
        }

        return array_values($posts);
    }

    public static function listings($params = [])
    {
        /*
         * $params
         * sold: [0|1|-1] empty or 0 for for sale, 1 for sold, -1 for both
         */
        $listingsQueryBuilder = new ListingQueryBuilder();
        $posts = [];
        if (!empty($params['sold']) && $params['sold'] == 1) {
            $listingsQueryBuilder->tax("tag", "slug", "sald");
            $defaultSort = [
                'orderby' => 'contractDate',
                'order'   => 'desc',
            ];
        } else {
            if (empty($params['sold']) || $params['sold'] !== -1) {
                $listingsQueryBuilder->tax("tag", "slug", "sald", "NOT IN");
            }
            $defaultSort = [
                'orderby' => 'firstPublished',
                'order'   => 'desc',
            ];
        }

        self::handleTagParams($listingsQueryBuilder, $params);

        if (!empty($params['sort'])) {
            $defaultSort = $params['sort'];
        }
        if (!empty($params['postsperpage'])) {
            $listingsQueryBuilder->postsPerPage($params['postsperpage']);
        }
        if (!empty($params['biddings'])) {
            $listingsQueryBuilder->tax("tag", "slug", "budgivning-pagar");
        }
        if (!empty($params['meta'])) {
            foreach ($params['meta'] as $i => $meta) {
                if (array_key_exists('key', $meta)) {
                    $listingsQueryBuilder->meta($meta['key'], ($meta['compare'] ?? '='), $meta['value'] ?? null, $meta['type'] ?? null);
                } else {
                    $listingsQueryBuilder->metaNested($meta, $i);
                }
            }
        }

        if (empty($params['unsorted']) || $params['unsorted'] == 0) {
            $listingsQueryBuilder->orderBy($defaultSort['orderby'], $defaultSort['order']);
        }

        $listingsQuery = $listingsQueryBuilder->getQuery();
        if ($listingsQuery->have_posts()) {
            $posts = self::expandObjects($listingsQuery->posts);
        }
        if (!empty($params['showings'])) {
            foreach ($posts as $key => $listing) {
                if ( ! $listing->meta->has->upcomingShowings == 1) {
                    unset($posts[$key]);
                }
            }
        }

        return array_values($posts);
    }

    public static function handleTagParams(ListingQueryBuilder $listingsQueryBuilder, array $params)
    {
        /*
         * Tag can come in either 'tag' or 'tags' and have values in different forms.
         * Possible values include
         * 1. 'Tagname'
         * 2. [0] => 'Tagname'
         * 3. ['name' => 'Tagname']
         * 4. [['name' => 'Tagname'], ['name' => 'Tagname 2']]
         * 5. ['slug' => 'slugname']
         *
         * Try to normalize input so it works regardless
         */
        if (!empty($params['tag'])) {
            if (is_string($params['tag']) || (is_array($params['tag']) && array_key_exists('name', $params['tag']))) {
                $params['tag'] = [$params['tag']];
            }
            if (!empty($params['tags'])) {
                if (is_string($params['tags']) || (is_array($params['tags']) && array_key_exists('name', $params['tags']))) {
                    $params['tags'] = [$params['tags']];
                }
                $params['tags'] = array_merge($params['tag'], $params['tags']);
            } else {
                $params['tags'] = $params['tag'];
            }
            unset($params['tag']);
        }
        if (!empty($params['tags'])) {
            if (!is_array($params['tags'])) {
                $params['tags'][] = [
                    'name' => $params['tags']
                ];
            } elseif (array_key_exists('name', $params['tags'])) {
                $params['tags'][] = $params['tags'];
                unset($params['tags']['name']);
            }
            foreach ($params['tags'] as $key => $tag) {
                if (!is_array($tag)) {
                    $params['tags'][$key] = [
                        'name' => $tag
                    ];
                }
            }
        }
        if (!empty($params['tags'])) {
            foreach ($params['tags'] as $tag) {
                // Todo: Currently creating 1 JOIN per tag, should include multiple in the same IN()
                if (array_key_exists('slug', $tag)) {
                    $listingsQueryBuilder->tax("tag", "slug", strtolower($tag['slug']), $tag['operator'] ?? 'IN');
                } elseif (is_numeric($tag['name'])) {
                    $listingsQueryBuilder->tax("tag", "id", $tag['name'], $tag['operator'] ?? 'IN');
                } else {
                    $listingsQueryBuilder->tax("tag", "name", $tag['name'], $tag['operator'] ?? 'IN');
                }
            }
        }
    }

    public static function getRealtors($fasadId = null, $wpId = null)
    {
        $hasExtendedCoworker = Coworker::hasExtendedCoworker();
        $realtorInstance = new Realtor(new ApiConnectionHandler());
        $postType = PublicSettings::FASAD_REALTOR_POST_TYPE;
        $orderby = [ 'fasad_sequence' => 'ASC' ];
        $metaQuery = [
            'fasad_sequence' => [
                'key' => self::$prefix . 'sequence',
                'compare' => 'EXISTS',
                'type' => 'NUMERIC'
            ]
        ];
        if ($hasExtendedCoworker) {
            $postType = PublicSettings::FASAD_COWORKER_POST_TYPE;
            if (!apply_filters('prekweb-coworkerFasadSequence', true)) {
                /*
                 * Use fasad sequence as default.
                 * Use add_filter('prekweb-coworkerFasadSequence', '__return_false') if using a custom sort plug
                 */
                $metaQuery = [];
                $orderby   = 'menu_order';
            }
        }
        $params = [
            'posts_per_page' => -1,
            'post_type'      => $postType,
            'order'          => 'ASC',
            'meta_query'     => $metaQuery,
            'orderby'        => $orderby
        ];

        if ($wpId) {
            $params['include'] = [$wpId];
        } elseif($fasadId) {
            $params['meta_query'] = [
                [
                    'key' => self::$prefix . 'id',
                    'value' => $fasadId,
                ]
            ];
        }

        $params = apply_filters('fasad_get_realtors_params', $params);
        $realtors = get_posts($params);
        if (!empty($realtors)) {
            foreach($realtors as $index => &$realtor){
                $meta = [];
                $extended_meta = [];
                if ($hasExtendedCoworker) {
                    if ($fasad_realtor = $realtorInstance->getByFasadId(get_post_meta($realtor->ID, self::$prefix . 'id', true), 'id', PublicSettings::FASAD_REALTOR_POST_TYPE)) {
                        $meta = get_post_meta($fasad_realtor->ID);
                    } else {
                        $meta = get_post_meta($realtor->ID);
                        foreach ($meta as $key => $value) {
                            if (strpos($key, self::$prefix) !== 0) {
                                unset($meta[$key]);
                            }
                        }
                    }
                    $extended_meta = (function_exists('get_field') ? get_fields($realtor->ID) : null);
                } else {
                    $meta = get_post_meta($realtor->ID);
                }
                $realtor->meta = Coworker::mergeMeta(self::filterExtendedMeta($meta), $extended_meta);
                $realtor->extended_meta = $extended_meta;
                if (!$realtor->meta || ($realtor->meta && (empty($realtor->meta['firstname']) || empty($realtor->meta['lastname'])))) {
                    unset($realtors[$index]);
                }
            }
            if ($realtors) {
                $realtors = array_values($realtors);
                if ($fasadId || $wpId) {
                    $realtors = $realtors[0];
                }
            }
        }
        return $realtors;
    }


    public static function filterExtendedMeta($meta = []){
        if(!empty($meta)){
            foreach ($meta as $key => $value){
                if(is_array($value) && sizeof($value) === 1){
                    $meta[str_replace(self::$prefix, '', $key)] = maybe_unserialize(maybe_unserialize($value[0]));
                    unset($meta[$key]);
                }
            }
        }

        if(!empty($meta[self::$prefix.'extraTitle'])){
            if(!empty($meta[self::$prefix.'title'])){
                $meta[self::$prefix.'title'] .= '/'.$meta[self::$prefix.'extraTitle'];
            } else {
                $meta[self::$prefix.'title'] = $meta[self::$prefix.'extraTitle'];
            }
        }

        // Format phone to realtor and offices
        if (!empty($meta)) {
            foreach (['phone', 'cellphone', 'switchboard'] as $phone) {
                if (array_key_exists($phone, $meta)) {
                    $meta[$phone]            = !empty($meta[$phone]) ? Helpers::formatPhone($meta[$phone]) : '';
                    $meta[$phone . 'String'] = !empty($meta[$phone]) ? Helpers::formatPhoneString($meta[$phone], $phone) : '';
                }
                if (!empty($meta['officeData'])) {
                    foreach ($meta['officeData'] as $officeId => $office) {
                        if (property_exists($office, $phone)) {
                            $office->{$phone}            = !empty($office->{$phone}) ? Helpers::formatPhone($office->{$phone}) : '';
                            $office->{$phone . 'String'} = !empty($office->{$phone}) ? Helpers::formatPhoneString($office->{$phone}, $phone) : '';
                        }
                    }
                }
            }
        }

        return $meta;
    }

    public static function getFasadOffices($fasadId = null, $wpId = null)
    {
        $hasOfficePostType = post_type_exists(PublicSettings::FASAD_OFFICE_EXTENDED_POST_TYPE);
        $officeInstance = new Office(new ApiConnectionHandler());
        $post_type = $hasOfficePostType ? PublicSettings::FASAD_OFFICE_EXTENDED_POST_TYPE : PublicSettings::FASAD_OFFICE_POST_TYPE;
        $params = array(
            'posts_per_page' => -1,
            'post_type'      => $post_type,
            'orderby'        => [
                'fasad_sequence' => 'ASC',
            ]
        );
        if ($wpId) {
            $params['include'] = [$wpId];
        } elseif ($fasadId) {
            $params['meta_query'] = [
                [
                    'key'   => self::$prefix . 'id',
                    'value' => $fasadId,
                ]
            ];
        }

        $params = apply_filters('fasad_get_offices_params', $params);
        $offices = get_posts($params);
        if (!empty($offices)) {
            foreach ($offices as $index => &$office) {
                $meta = [];
                $extended_meta = [];
                if ($hasOfficePostType) {
                    if ($fasad_office = $officeInstance->getByFasadId(get_post_meta($office->ID, self::$prefix . 'id', true), 'id', PublicSettings::FASAD_OFFICE_POST_TYPE)) {
                        $meta = get_post_meta($fasad_office->ID);
                    }
                    $extended_meta = (function_exists('get_field') ? get_fields($office->ID) : null);
                } else {
                    $meta = get_post_meta($office->ID);
                }
                $office->meta = self::filterExtendedMeta($meta);
                $office->extended_meta = $extended_meta;
            }
            if ($fasadId || $wpId) {
                $offices = $offices[0];
            }
        }
        return $offices;
    }

    public static function getSettings()
    {
        $settings = [
//            'sizes'                 => [
            //                'flexible_image_width'  => 980,
            //                'flexible_image_height' => 520,
            //                'single_width'          => 1600,
            //                'single_height'         => 0,
            //                'single_lazy_size'      => 200,
            //                'portrait_width'        => 0,
            //                'portrait_height'       => 650,
            //                'list_width'            => 920,
            //                'list_height'           => 365,
            //                'list_npp_width'        => 510,
            //                'list_npp_height'       => 412,
            //                'small_width'           => 512,
            //                'small_height'          => 496,
            //                'realtor_width'         => 90,
            //                'realtor_height'        => 90,
            //                'realtor_single_width'  => 250,
            //                'realtor_single_height' => 350,
            //                'hero_width'            => 3000,
//            ],
            'imageCategories'       => [
                'floorplans' => ['Planlösningar'],
                //                'objectImages' => ['Objekt, fullskärm']
            ],
//            'mediaCategories'       => [
//                'videohero'    => ['hero'],
//                'videorealtor' => ['mäklarfilm'],
//            ],
//            'descriptionCategories' => [
//                'inspection' => ['Besiktning']
//                'quoteNewproduction' => 'Nyproduktionscitat',
//                'contractor'         => 'Byggherretext',
//                'association'        => 'Förening'
//            ],
//            'documentAliases' => [
//                'prospekt' => ['prospekt.pdf'],
//            ],
//            'mapsApi' => 'AIzaSyAS21vcPtGKbI8jAeZQPT-HI_34K8Y9BCA',
        ];

        return apply_filters('fasad_bridge_settings', $settings);
    }

    public static function getAreaByType($areas, $type, $single = true, $default = '-')
    {
        $areasArray = is_array($areas) ? $areas : [$areas];
        $size = 0;
        foreach ($areasArray as $area) {
            if ( ! empty($area->size) && mb_strtolower($area->type) == mb_strtolower($type) && $area->size):
                $size += $area->size;
                if ($single) {
                    break;
                }
            endif;
        }

        return $size > 0 ? $size : $default;
    }

    public static function getIncludedListings($expandedListing, $visibility = 'project')
    {
        if (!$expandedListing->newconstructionFormatted['is']['project'] && !$expandedListing->newconstructionFormatted['is']['category']) {
            return [];
        }
        if (!$expandedListing->newconstructionFormatted['has']['listings']) {
            return [];
        }
        $listings = [];
        foreach ($expandedListing->newconstructionFormatted['includedListings'] as $listingId) {
            $postId    = null;
            $previewId = null;
            if ('published' === $visibility) {
                //Only published included listings
                $postId = self::getWpId($listingId);
            } elseif ('preview' === $visibility) {
                //Only previewed included listings (if project is previewed, all included listings will be shown)
                $previewId = $listingId;
            } else {
                /*
                 * Defaults to 'project'
                 * Same visibility as project:
                 * if project is published, only published included listings
                 * if project is preview, show all included listings
                 */
                if (1 == $expandedListing->published) {
                    //Project is published, fetch all published listings
                    $postId = self::getWpId($listingId);
                } elseif ($expandedListing->preview) {
                    //Project is a preview, fetch all included listings
                    $previewId = $listingId;
                }
            }
            if ($postId || $previewId) {
                $listings[] = self::expandObject($postId, $previewId);
            }
        }
        return $listings;
    }

    public static function newConstruction($meta): array
    {
        $newConstruction = [
            'is'                 => [
                'project'  => property_exists($meta, 'newConstruction') && !empty($meta->newConstruction) ? 1 : 0,
                'category' => property_exists($meta, 'newConstructionCategory') && !empty($meta->newConstructionCategory->includedListings) ? 1 : 0,
                'object'   => property_exists($meta, 'belongsToNewConstruction') && !empty($meta->belongsToNewConstruction) ? 1 : 0,
            ],
            'has'                => [
                'categories' => property_exists($meta, 'newConstruction') && !empty($meta->newConstruction->includedCategories) ? 1 : 0,
                'listings'   => property_exists($meta, 'newConstruction') && !empty($meta->newConstruction->includedListings) ? 1 : 0,
            ],
            'includedListings'   => [],
            'includedCategories' => [],
        ];
        if (($newConstruction['is']['project'] || $newConstruction['is']['category']) && $newConstruction['has']['listings']) {
            $newConstruction['includedListings'] = $meta->newConstruction->includedListings;
        }
        if ($newConstruction['is']['project'] && $newConstruction['has']['categories']) {
            $newConstruction['includedCategories'] = $meta->newConstruction->includedCategories;
        }

        return $newConstruction;
    }

    public static function facts($meta, $includeEmpty = true)
    {
        $factsToShow = [];

        $factsToShow['formOfOwnership'] = [
            'label' => 'Ägandeform',
            'value' => self::ownershipFilter($meta->formOfOwnership->alias)
        ];

        if (!empty($meta->descriptionType)) {
            $factsToShow['descriptionType'] = [
                'label' => 'Typ',
                'value' => $meta->descriptionType->alias
            ];
        }

        if (!empty($meta->additionalDescriptionTypes)) {
            $factsToShow['additionalDescriptionTypes'] = [
                'label' => 'Kan även användas som',
                'value' => []
            ];
            foreach ($meta->additionalDescriptionTypes as $type) {
                if ($type->alias != $meta->descriptionType->alias) {
                    $factsToShow['additionalDescriptionTypes']['value'][] = $type->alias;
                }
            }
            if (empty($factsToShow['additionalDescriptionTypes']['value'])) {
                unset($factsToShow['additionalDescriptionTypes']);
            }
        }

        if (!empty($meta->location)) {
            $factsToShow['propertyDesignation'] = [
                'label' => 'Fastighetsbeteckning',
                'value' => $meta->location->propertyDesignation,
            ];

            $factsToShow['country'] = [
                'label' => 'Land',
                'value' => $meta->location->country->alias
            ];

            $factsToShow['commune'] = [
                'label' => 'Kommun',
                'value' => $meta->location->commune->alias
            ];

            $factsToShow['city'] = [
                'label' => 'Postort',
                'value' => $meta->location->city
            ];

            $factsToShow['district'] = [
                'label' => 'Område',
                'value' => $meta->location->district
            ];

            if (!empty($meta->location->parish->alias) && $meta->location->parish->alias !== "Ej angiven") {
                $factsToShow['parish'] = [
                    'label' => 'Församling',
                    'value' => $meta->location->parish->alias
                ];
            }

            $factsToShow['address'] = [
                'label' => 'Adress',
                'value' => $meta->location->address
            ];

            $factsToShowValue = "";
            if (!empty($meta->location->zipCode)) {
                $factsToShowValue = substr($meta->location->zipCode, 0, 3) . ' ' . substr($meta->location->zipCode, 3);
            }

            $factsToShow['zipcode'] = [
                'label' => 'Postnummer',
                'value' => $factsToShowValue
            ];
        }

        if (!empty($meta->size)) {
            $factsToShow['rooms'] = [
                'label' => 'Rum',
                'value' => $meta->size->rooms
            ];

            $factsToShow['minBedrooms'] = [
                'label' => '',
                'value' => '',
            ];

            $factsToShow['bathrooms'] = [
                'label' => 'Badrum',
                'value' => $meta->size->bathrooms
            ];

            $factsToShow['guestToilet'] = [
                'label' => '',
                'value' => '',
            ];
        }

        $factsToShow['areas'] = self::areas($meta, $includeEmpty);

        if (!empty($meta->facts)) {
            $factsToShow['apartmentnumber'] = [
                'label' => 'Lägenhetsnummer',
                'value' => $meta->facts->apartmentNumber,
            ];

            $factsToShow['apartmentnumberLMV'] = [
                'label' => 'Lägenhetsnummer (LMV)',
                'value' => $meta->facts->apartmentNumberLmv,
            ];

            $factsToShow['built'] = [
                'label' => 'Byggnadsår',
                'value' => $meta->facts->built
            ];

            $factsToShow['builtComment'] = [
                'label' => 'Byggnadsår kommentar',
                'value' => $meta->facts->builtComment
            ];

            $factsToShow['inspected'] = [
                'label' => 'Besiktigad',
                'value' => $meta->facts->energyInspected
            ];

            $factsToShow['window'] = [
                'label' => 'Fönster',
                'value' => $meta->facts->window
            ];

            $factsToShow['balcony'] = [
                'label' => 'Balkong',
                'value' => $meta->facts->balcony
            ];

            $factsToShow['storageRoom'] = [
                'label' => 'Förråd',
                'value' => $meta->facts->storageRoom
            ];

            $factsToShow['fireplace'] = [
                'label' => 'Eldstad',
                'value' => $meta->facts->fireplace
            ];

            $factsToShow['floor'] = [
                'label' => 'Våning',
                'value' => $meta->facts->floor
            ];

            $value = $meta->facts->elevator;
            if ($value === 1) {
                $value = 'Ja';
            } elseif ($value === 0) {
                $value = 'Nej';
            } else {
                $value = '';
            }
            $factsToShow['elevator'] = [
                'label' => 'Hiss',
                'value' => $value
            ];

            $factsToShow['elevatorComment'] = [
                'label' => 'Hiss kommentar',
                'value' => $meta->facts->elevatorComment
            ];

            $factsToShow['tvInternet'] = [
                'label' => 'Tv- internetutbud',
                'value' => $meta->facts->tvInternet
            ];

            $value = $meta->facts->acquisition;
            if ($value === 1) {
                $value = 'Ja';
            } elseif ($value === 0) {
                $value = 'Nej';
            } else {
                $value = '';
            }
            $factsToShow['acquisition'] = [
                'label' => 'Förvärvstillstånd krävs',
                'value' => $value
            ];
        }

        $factsToShow['sellerChangedate'] = [
            'label' => '',
            'value' => '',
        ];

        if (!empty($meta->building)) {
            $factsToShow['buildingtype'] = [
                'label' => 'Byggnadstyp',
                'value' => $meta->building->buildingType
            ];

            $factsToShow['facade'] = [
                'label' => 'Fasad',
                'value' => $meta->building->facade
            ];

            $factsToShow['roof'] = [
                'label' => 'Tak',
                'value' => $meta->building->roof
            ];

            $factsToShow['framework'] = [
                'label' => 'Stomme',
                'value' => $meta->building->framework
            ];

            $factsToShow['loftfloor'] = [
                'label' => 'Bjälklag',
                'value' => $meta->building->loftFloor
            ];

            $factsToShow['foundation'] = [
                'label' => 'Grundläggning',
                'value' => $meta->building->foundation
            ];

            $factsToShow['chimney'] = [
                'label' => 'Grundmur',
                'value' => $meta->building->chimney
            ];

            $factsToShow['chimneypot'] = [
                'label' => 'Murstock',
                'value' => $meta->building->chimneyPot
            ];

            $factsToShow['metalWork'] = [
                'label' => 'Plåtarbete',
                'value' => $meta->building->metalWork
            ];

            $factsToShow['energyVentilation'] = [
                'label' => 'Ventilation',
                'value' => $meta->building->energyVentilation
            ];

            $factsToShow['heatingType'] = [
                'label' => 'Uppvärmning',
                'value' => $meta->building->heatingType
            ];

            $factsToShow['municipalWater'] = [
                'label' => 'Vatten och avlopp',
                'value' => $meta->building->municipalWater
            ];

            $factsToShow['radon'] = [
                'label' => 'Radon',
                'value' => $meta->building->radon
            ];

            $factsToShow['compassDirection'] = [
                'label' => 'Huvudsakligt väderstreck',
                'value' => $meta->building->compassDirection
            ];

            $factsToShow['generalCondition'] = [
                'label' => 'Allmänt skick',
                'value' => $meta->building->generalCondition
            ];
        }

        if (!empty($meta->economy->apartment)) {
            $fee = $meta->economy->apartment->fee;
            if ($meta->is->localRent) {
                $fee = round($meta->economy->apartment->fee / 12); //Fee is returned yearly on local
            }
            $factsToShow['fee'] = [
                'label' => $meta->is->local ? 'Hyra' : 'Avgift',
                'value' => Helpers::numberFormat($fee)
            ];

            $factsToShow['includedInFee'] = [
                'label' => 'Ingår i avgiften',
                'value' => $meta->economy->apartment->includedInFee
            ];

            $factsToShow['excludedInFee'] = [
                'label' => 'Ingår ej i avgiften',
                'value' => $meta->economy->apartment->excludedInFee
            ];

            $factsToShow['knownFeeChange'] = [
                'label' => 'Känd avgiftsförändring',
                'value' => $meta->economy->apartment->knownFeeChange
            ];

            $factsToShow['repairFund'] = [
                'label' => 'Reparationsfond',
                'value' => $meta->economy->apartment->repairFund
            ];

            if (property_exists($meta->economy->apartment, 'buyerNetDebt') && is_numeric($meta->economy->apartment->buyerNetDebt)) {
                $factsToShow['buyerNetDebt'] = [
                    'label' => 'Nettoskuldsättning',
                    'value' => Helpers::numberFormat($meta->economy->apartment->buyerNetDebt, 1, 'kr')
                ];
            }

            if (property_exists($meta->economy->apartment, 'buyerNetDebtComment') && $meta->economy->apartment->buyerNetDebtComment) {
                $factsToShow['buyerNetDebtComment'] = [
                    'label' => 'Nettoskuldsättning, kommentar',
                    'value' => $meta->economy->apartment->buyerNetDebtComment
                ];
            }
        }

        if (!empty($meta->facts->part)) {
            // Andelstal ägande
            $value = $meta->facts->part->owner->value;
            if(!empty($value)){
                $value .= ($meta->facts->part->owner->percent == 1) ? '%' : '';
            }
            $factsToShow['partPartFee'] = [
                'label' => 'Andelstal, förening',
                'value' => $value
            ];
            $value = $meta->facts->part->fee->value;
            if(!empty($value)){
                $value .= ($meta->facts->part->fee->percent == 1) ? '%' : '';
            }
            $factsToShow['partFee'] = [
                'label' => 'Andelstal, avgift',
                'value' => $value
            ];
        }

        if (!empty($meta->facts->planRegulation)) {
            $value = $meta->facts->planRegulation->type;
            if(!empty($value)){
                $value .= (!empty($meta->facts->planRegulation->date)) ? ' (' . $meta->facts->planRegulation->date . ')' : '';
            }
            $factsToShow['planRegulations'] = [
                'label' => 'Planbestämmelser',
                'value' => $value
            ];
        }

        if (!empty($meta->economy->house)) {
            $factsToShow['taxTotal'] = [
                'label' => 'Taxeringsvärde totalt',
                'value' => Helpers::numberFormat($meta->economy->house->taxTotal, 0, 'kr')
            ];

            $factsToShow['taxHouse'] = [
                'label' => 'Taxeringsvärde fastighet',
                'value' => Helpers::numberFormat($meta->economy->house->taxHouse, 0, 'kr')
            ];

            $factsToShow['taxGround'] = [
                'label' => 'Taxeringsvärde tomt',
                'value' => Helpers::numberFormat($meta->economy->house->taxGround, 0, 'kr')
            ];

            $factsToShow['taxYear'] = [
                'label' => 'Taxeringsår',
                'value' => $meta->economy->house->taxYear
            ];

            $factsToShow['taxCode'] = [
                'label' => 'Taxeringskod',
                'value' => $meta->economy->house->taxCode
            ];

            $factsToShow['taxComment'] = [
                'label' => 'Taxeringsvärde kommentar',
                'value' => $meta->economy->house->taxComment
            ];

            $factsToShow['taxPrelim'] = [
                'label' => 'Taxeringen är preliminär',
                'value' => $meta->economy->house->taxPrelim
            ];

            if (property_exists($meta->economy->house, 'propertyFee') && $meta->economy->house->propertyFee) {
                $factsToShow['propertyFee'] = [
                    'label' => 'Total fastighetsskatt/-avgift',
                    'value' => Helpers::numberFormat($meta->economy->house->propertyFee, 0, 'kr')
                ];
            }

            if (property_exists($meta->economy->house, 'titleDeed') && $meta->economy->house->titleDeed) {
                $factsToShow['titleDeed'] = [
                    'label' => 'Kostnad för lagfart',
                    'value' => Helpers::numberFormat($meta->economy->house->titleDeed)
                ];
            }

            $factsToShow['groundRent'] = [
                'label' => 'Tomträttsavgäld',
                'value' => $meta->economy->house->groundRent
            ];

            $factsToShow['groundRentExpiry'] = [
                'label' => 'Tomträttsavgäld, löptid',
                'value' => $meta->economy->house->groundRentExpiry
            ];

            $factsToShow['lease'] = [
                'label' => 'Arrende',
                'value' => $meta->economy->house->lease
            ];

            $factsToShow['valueYear'] = [
                'label' => 'Värdeår',
                'value' => $meta->economy->house->valueYear
            ];
        }

        if (!empty($meta->economy->farm)) {
            $factsToShow['taxField'] = [
                'label' => 'Taxeringsvärde åkermark',
                'value' => Helpers::numberFormat($meta->economy->farm->taxField, 0, 'kr')
            ];
            $factsToShow['taxPasture'] = [
                'label' => 'Taxeringsvärde betesmark',
                'value' => Helpers::numberFormat($meta->economy->farm->taxPasture, 0, 'kr')
            ];
            $factsToShow['taxForest'] = [
                'label' => 'Taxeringsvärde skogsmark',
                'value' => Helpers::numberFormat($meta->economy->farm->taxForest, 0, 'kr')
            ];
            $factsToShow['taxForestImpediment'] = [
                'label' => 'Taxeringsvärde skogsimpediment',
                'value' => Helpers::numberFormat($meta->economy->farm->taxForestImpediment, 0, 'kr')
            ];
            $factsToShow['taxCommercialBuilding'] = [
                'label' => 'Taxeringsvärde ekonomibyggnad',
                'value' => Helpers::numberFormat($meta->economy->farm->taxCommercialBuilding, 0, 'kr')
            ];

            $factsToShow['taxOther'] = [
                'label' => 'Taxeringsvärde övrigt',
                'value' => Helpers::numberFormat($meta->economy->farm->taxOther, 0, 'kr')
            ];
        }

        $factsToShow['taxRate'] = [
            'label' => 'Skattesats',
            'value' => ''
        ];

        if (!empty($meta->economy)) {
            $value = $meta->economy->pledged;
            if ($value === 1) {
                $value = 'Ja';
            } elseif ($value === 0) {
                $value = 'Nej';
            } else {
                $value = '';
            }
            $factsToShow['pledged'] = [
                'label' => 'Pantsatt',
                'value' => $value
            ];

            $factsToShow['noOfPledge'] = [
                'label' => 'Pantbrev, antal',
                'value' => $meta->economy->noOfPledge
            ];

            $factsToShow['totalMortgagesSum'] = [
                'label' => 'Pantbrev, summa',
                'value' => Helpers::numberFormat($meta->economy->totalMortgagesSum, 0, 'kr')
            ];

            if ($meta->economy->pledgeFee) {
                $factsToShow['pledgeFee'] = [
                    'label' => 'Pantsättningsavgift',
                    'value' => Helpers::numberFormat($meta->economy->pledgeFee, 1, 'kr')
                ];
            }

            if ($meta->economy->transferFee) {
                $factsToShow['transferFee'] = [
                    'label' => 'Överlåtelseavgift',
                    'value' => Helpers::numberFormat($meta->economy->transferFee, 1, 'kr')
                ];
                $value = $meta->economy->transferFeePaidBy;
                if ($value == 1) {
                    $value = 'Säljaren';
                } elseif ($value == 0) {
                    $value = 'Köparen';
                } else {
                    $value = 'Vet ej';
                }
                $factsToShow['transferFeePaidBy'] = [
                    'label' => 'Överlåtelseavgift, betalas av',
                    'value' => $value
                ];
            }

            //Caution. Seems to return "" if default, and default seems to be "Nej"
            $value = $meta->economy->insuranceFullValue;
            if ($value === 1) {
                $value = 'Ja';
            } elseif ($value === 0) {
                $value = 'Nej';
            } else {
                $value = '';
            }
            $factsToShow['insuranceFullValue'] = [
                'label' => 'Försäkring, fullvärde',
                'value' => $value
            ];
        }

        if (!empty($meta->energy)) {
            $energy = [
                'status'              => [
                    'label' => 'Energistatus',
                    'value' => $meta->energy->inspectionStatus
                ],
                'performance'         => [
                    'label' => 'Energiprestanda',
                    'value' => ($meta->energy->performance ? $meta->energy->performance . ' kWh per kvm och år' : '')
                ],
                'class'               => [
                    'label' => 'Energiklass',
                    'value' => $meta->energy->class
                ],
                'certificateDate'     => [
                    'label' => 'Registrerad',
                    'value' => $meta->energy->certificateDate
                ],
                'declarationAddress'  => [
                    'label' => 'Energideklarationsadress',
                    'value' => $meta->energy->energyDeclarationAddress
                ],
                'electricitySupplier' => [
                    'label' => 'Nuvarande elleverantör',
                    'value' => $meta->energy->electricitySupplier
                ],

            ];

            $factsToShow['energy'] = $energy;
        } else {
            $factsToShow['energy'] = [];
        }

        // Run operatingCosts() to set correct values for amountMonth and amountYear
        // in $meta->economy->operatingCost->operatingCosts
        self::operatingCosts($meta, false, false);

        $factsToShow['totalOperatingCosts'] = self::totalOperatingCosts($meta, $includeEmpty);

        // Association
        $factsToShow['association'] = self::association($meta, $includeEmpty);
        // END Association

        if (!empty($factsToShow['areas']) && !empty($meta->size) && !empty($meta->size->area) && !empty($meta->size->area->comment)) {
            $factsToShow['areasComment'] = [
                'label' => 'Kommentar',
                'value' => $meta->size->area->comment,
            ];
        }

        if ( ! $includeEmpty && !empty($factsToShow)) {
            $factsToShow['energy'] = array_filter(
                $factsToShow['energy'],
                self::class . '::factFilter'
            );
            $factsToShow = array_filter(
                $factsToShow,
                self::class . '::factFilter',
                ARRAY_FILTER_USE_BOTH
            );
        }

        return apply_filters('fasad_bridge_facts', $factsToShow, $meta);
    }

    public static function operatingCosts($meta, $includeEmpty = false, $filter = true)
    {

        if (empty($meta->economy) || empty($meta->economy->operatingCost)) {
            return [];
        }

        $operatingCosts = $meta->economy->operatingCost->operatingCosts;

        // Operating costs for Commercial and Farm come in a strange way from API.
        // Until that is fixed, we handle it here.
        if (!empty($operatingCosts)) {
            foreach ($operatingCosts as $value) {
                if (is_object($value)
                    && property_exists($value, 'amount')
                    && property_exists($value, 'amountYear')
                    && property_exists($value, 'amountApartment')
                    && property_exists($value, 'amountPremises')
                    && ($value->amount === '' || $value->amount === 0)
                ) {
                    $value->amount = $value->amountPremises + $value->amountApartment;
                    if ($value->unit === 'PERYEAR') {
                        $value->amountYear  = $value->amount;
                        $value->amountMonth = round($value->amount / 12);
                    } else {
                        $value->amountYear  = $value->amount * 12;
                        $value->amountMonth = $value->amount;
                    }
                    unset($value->amount_L);
                    unset($value->amount_B);
                    unset($value->amount);
                }
            }
        }

        if (!$includeEmpty && !empty($operatingCosts)) {
            $operatingCosts = array_filter(
                $operatingCosts,
                function ($value) {
                    if (is_object($value)) {
                        $value = (array)$value;
                    }
                    if (is_array($value) && array_key_exists('amountYear', $value) && array_key_exists('amountMonth', $value)) {
                        return !empty($value['amountMonth'])
                            && !is_null($value['amountMonth'])
                            && $value['amountMonth'] !== 0
                            && !empty($value['amountYear'])
                            && !is_null($value['amountYear'])
                            && $value['amountYear'] !== 0;
                    }
                    return !empty($value);
                }
            );
        }

        if ($filter) {
            $operatingCosts = apply_filters('fasad_operatingCosts', $operatingCosts);
        }
        return $operatingCosts;
    }

    public static function totalOperatingCosts($meta, $includeEmpty = false)
    {
        if (empty($meta->economy) || empty($meta->economy->operatingCost)) {
            return [];
        }
        $fields = apply_filters('fasad_totalOperatingCostsFields', [
            [
                'key'   => 'numberOfPersons',
                'label' => 'Personer i hushåll',
            ],
            [
                'key'   => 'totalAmountMonth',
                'label' => 'Driftskostnader totalt',
                'money' => true,
                'unit'  => 'kr/mån'
            ],
            [
                'key'   => 'totalAmountYear',
                'label' => 'Driftskostnader totalt',
                'money' => true,
                'unit'  => 'kr/år'
            ],
            [
                'key'   => 'comment',
                'label' => 'Kommentar',
            ]
        ]);

        $operatingCosts = [];
        foreach ($fields as $field) {
            if ($includeEmpty || $meta->economy->operatingCost->{$field['key']}) {
                $operatingCosts['operatingCost_'.$field['key']]['label'] = $field['label'];
                if (!empty($field['money']) && $field['money']) {
                    $value = Helpers::numberFormat($meta->economy->operatingCost->{$field['key']}, 0, $field['unit']);
                } else {
                    $value = $meta->economy->operatingCost->{$field['key']};
                }
                $operatingCosts['operatingCost_'.$field['key']]['value'] = $value;
            }
        }

        return apply_filters('fasad_totalOperatingCosts', $operatingCosts);
    }

    public static function association($meta, $includeEmpty = true)
    {

        if (empty($meta->association)) {
            return [];
        }

        $tmpAssociation = [
            //            'genuine'              => [
            //                'label' =>'',
            //                'value' => ''
            //            ],
            //            'legalPersonalAllowed' => [
            //                'label' =>'',
            //                'value' =>''
            //            ],
            //            'landowners'           => [
            //                'label' =>'',
            //                'value' =>''
            //            ],
            'name'              => [
                'label' => 'Förening',
                'value' => ''
            ],
            //            'visitAddress'         => [
            //                'label' =>'',
            //                'value' =>''
            //            ],
            //            'webpage'              => [
            //                'label' =>'',
            //                'value' =>''
            //            ],
            //            'address'              => [
            //                'label' =>'',
            //                'value' =>''
            //            ],
            //            'zipCode'              => [
            //                'label' =>'',
            //                'value' =>''
            //            ],
            //            'city'                 => [
            //                'label' =>'',
            //                'value' =>''
            //            ],
            //            'mail'                 => [
            //                'label' =>'',
            //                'value' =>''
            //            ],
            'contactPersonName' => [
                'label' => 'Kontakt',
                'value' => ''
            ],
            'contactPhone'      => [
                'label' => 'Telefon',
                'value' => ''
            ],
            //            'propertyDesignation'  => [
            //                'label' =>'',
            //                'value' =>''
            //            ],
            //            'propertyDescription'  => [
            //                'label' =>'',
            //                'value' =>''
            //            ],
            //            'xCoordinate'          => [
            //                'label' =>'',
            //                'value' =>''
            //            ],
            //            'yCoordinate'          => [
            //                'label' =>'',
            //                'value' =>''
            //            ],
            //            'phoneString'          => [
            //                'label' =>'',
            //                'value' =>''
            //            ],
            'contactPhoneString'   => [
                'label' => 'Telefon, kontaktpersson',
                'value' =>''
            ],
            'organizationNo'    => [
                'label' => 'Org.nr',
                'value' => ''
            ],
        ];
        $association = $meta->association;
        if ($association && count($association) > 0) {
            if ( ! empty($association[0]->name)) {
                $tmpAssociation['name']['value'] = $association[0]->name;
            }
            if ( ! empty($association[0]->organizationNo)) {
                $tmpAssociation['organizationNo']['value'] = $association[0]->organizationNo;
            }
            if ( ! empty($association[0]->contactPersonName)) {
                $tmpAssociation['contactPersonName']['value'] = $association[0]->contactPersonName;
            }
            if ( ! empty($association[0]->contactPhoneString)) {
                // We can't really know if this is cellphone or landline
                $tmpAssociation['contactPhoneString']['value'] = Helpers::formatPhoneString($association[0]->contactPhoneString, 'landline');
            }
        }
        if ( ! $includeEmpty && !empty($tmpAssociation)) {
            $tmpAssociation = array_filter(
                $tmpAssociation,
                self::class . '::factFilter'
            );
        }

        return $tmpAssociation;
    }

    public static function areas($meta, $includeEmpty = true)
    {
        $areas = [];
        if (
            !empty($meta->size) &&
            !empty($meta->size->area) &&
            !empty($meta->size->area->areas)
        ){
            foreach ($meta->size->area->areas as $area) {
                if ($includeEmpty || $area->size) {
                    preg_match('/[.,]/', $area->size, $separator); // Keep the decimal separator entered in FasAd
                    if (empty($separator)) {
                        $separator = ',';
                    } else {
                        $separator = $separator[0];
                    }
                    $areas[] = [
                        'label' => $area->type,
                        'value' => ($area->prefix ? $area->prefix . ' ' : '') . str_replace($separator . '0', '', number_format($area->size, 1, $separator, ' ')) . ' ' . $area->unit,
                        'basedOn' => $area->basedOn,
                        'prefix' => $area->prefix,
                        'unit' => $area->unit
                    ];
                }
            }
        }
        return $areas;
    }

    /**
     * @param object $listing
     * @param string $value
     * @param string $mode 'overwrite' or 'append'
     * @return object
     */
    public static function loadProjectData(object $listing, string $value, string $mode = 'overwrite') : object
    {
        // Add more fields to the array as needed, like images...
        if (in_array($value, ['showings', 'documents'])) {
            if (!empty($listing->belongsToNewConstruction) && $listing->belongsToNewConstruction->masterId) {
                $masterId = $listing->belongsToNewConstruction->masterId;
                $projectPostId = Fasad::getWpId($masterId);
                $project = Fasad::expandObject($projectPostId);
            }
        }

        if (empty($project)) {
            return $listing;
        }

        switch ($value) {
            case 'showings':
                if ($mode == 'overwrite') {
                    $listing->showings = $project->showings;
                    $listing->showingsFormatted = $project->showingsFormatted;
                    $listing->has->showings = $project->has->showings;
                    $listing->has->upcomingShowings = $project->has->upcomingShowings;
                    $listing->showingsObject = $project->id; // Important to use this id in showings form
                }
                break;
            case 'documents':
                if ($mode == 'overwrite') {
                    $listing->documents = $project->documents;
                    $listing->has->documents = $project->has->documents;
                } else {
                    $documents = [];
                    if (!empty($project->documents->listingDocuments)) {
                        if (!empty($listing->documents->listingDocuments)) {
                            // Too avoid duplicates
                            foreach ($listing->documents->listingDocuments as $document) {
                                $documents[] = $document->id;
                            }
                        }
                        foreach ($project->documents->listingDocuments as $document) {
                            if (!in_array($document->id, $documents)) {
                                $listing->documents->listingDocuments[] = $document;
                            }
                        }
                        $listing->has->documents = true;
                    }
                }
                break;
        }

        return $listing;
    }

    public static function factFilter($value, $key = '')
    {
        if (apply_filters('fasad_bridge_factFilter_includeEmpty_' . $key, false)) {
            return $value;
        }

        if (is_array($value) && array_key_exists('value', $value)) {
            if (is_array($value['value'])) {
                return !empty($value['value']);
            }

            if (is_string($value['value'])) {
                return !empty(trim($value['value']));
            }

            if (is_null($value['value'])) {
                return false;
            }
        }

        return !empty($value);
    }

    public static function ownershipFilter($formOfOwnership)
    {
        switch ($formOfOwnership) {
            case 'Lokal - Hyresrätt':
                $formOfOwnership = 'Lokal';
                break;
        }
        return apply_filters('fasad_bridge_ownership', $formOfOwnership);
    }

    /**
     *
     * @param \FasadBridge\Includes\Fetching\Fetcher $fetcher
     * @param $image
     * @param int $maxWidth
     * @param int $maxHeight
     * @param int $nrOfSizes
     * @return string
     */
    static function getFasadSrcset($image, $minWidth = 300, $maxWidth = 1920, $minHeight = 300, $maxHeight = 1280, $nrOfSizes = 2, $quality = 70, $sharpen = 0)
    {
        $srcset = [];

        $stepWidthSize  = ($maxWidth - $minWidth) / ($nrOfSizes - 1);
        $stepHeightSize = ($maxHeight - $minHeight) / ($nrOfSizes - 1);

        $widths  = range($maxWidth, $minWidth, $stepWidthSize);
        $heights = range($maxHeight, $minHeight, $stepHeightSize);

        for ($i = 0; $i < $nrOfSizes; $i++) {
            $srcset[] = Image::processImage($image, ["w" => round($widths[$i]), "h" => round($heights[$i]), 'c' => $quality, 'u' => $sharpen]) . " " . round($widths[$i]) . "w";
        }
        $result = implode(", ", $srcset);
        return $result;
    }

    public static function getFasadId($wp_id)
    {
        $fetcher = new Database($wp_id);
        return $fetcher->getNestedAttribute('id');
    }

    public static function getWpId($fasad_id) {
        global $wpdb;
        $post = $wpdb->get_results($wpdb->prepare("SELECT post_id FROM ".$wpdb->postmeta." WHERE meta_key=%s AND meta_value=%s", [self::$prefix . 'id', $fasad_id]));
        $post_id = null;
        if (!empty($post) && is_object($post[0])) {
            $post_id = $post[0]->post_id;
        }
        return $post_id;
    }

    public static function isSingleListing()
    {
        return is_singular(PublicSettings::FASAD_LISTING_POST_TYPE) || is_numeric(get_query_var(PublicSettings::FASAD_LISTING_POST_TYPE));
    }

    public static function isSingleUnderhand()
    {
        return is_singular(PublicSettings::FASAD_PROTECTED_POST_TYPE) || is_numeric(get_query_var(PublicSettings::FASAD_PROTECTED_POST_TYPE));
    }

    public static function imageRoute()
    {
        global $wp_query;
        return isset($wp_query->query_vars['alla-bilder']);
    }

    public static function isApartment( $type, $obj = null ) {
        $isApartment = ( preg_match( '/lägenhet/is', $type ) || (!empty($obj) && !empty($obj->formOfOwnership) && preg_match( '/lägenhet/is', $obj->formOfOwnership )) ? 1 : 0 );
        return apply_filters( 'fasad_is_apartment', $isApartment, $type, $obj );
    }

    public static function isHouse( $type, $obj = null ) {
        $isHouse = ( (preg_match( '/villa/is', $type ) || preg_match('/hus/is', $type)) || (!empty($obj) && !empty($obj->formOfOwnership) && (preg_match( '/villa/is', $obj->formOfOwnership ) || preg_match( '/hus/is', $obj->formOfOwnership ))) ? 1 : 0 );
        return apply_filters( 'fasad_is_house', $isHouse, $type, $obj);
    }

    public static function isLot( $type, $obj = null ) {
        $isLot = ( preg_match( '/tomt/is', $type ) || (!empty($obj) && !empty($obj->formOfOwnership)  && preg_match( '/tomt/is', $obj->formOfOwnership )) ? 1 : 0 );
        return apply_filters( 'fasad_is_lot', $isLot, $type, $obj);
    }

    public static function isFarm( $type, $obj = null ) {
        $isFarm = ( preg_match( '/gård/is', $type ) || (!empty($obj) && !empty($obj->formOfOwnership)  && preg_match( '/gård/is', $obj->formOfOwnership )) ? 1 : 0 );
        return apply_filters( 'fasad_is_farm', $isFarm, $type, $obj);
    }

    /*
     * Implement objecttypecheck on id step by step. Every is{type} should have this numeric check
     */
    public static function isLocal( $type, $obj = null ) {
        if (is_numeric($type)) {
            $isLocal = self::isLocalRent($type, $obj) || self::isLocalApartment($type, $obj) ? 1 : 0;
        } else {
            $isLocal = (preg_match('/lokal/is', $type) || (!empty($obj) && !empty($obj->formOfOwnership) && preg_match('/lokal/is', $obj->formOfOwnership)) ? 1 : 0);
        }
        return apply_filters( 'fasad_is_local', $isLocal, $type, $obj);
    }

    public static function isLocalRent( $type, $obj = null ) {
        $isLocalRent = in_array($type, self::$objectTypes['IS_LOCAL']) ? 1 : 0;
        return apply_filters( 'fasad_is_localRent', $isLocalRent, $type, $obj);
    }

    public static function isLocalApartment( $type, $obj = null ) {
        $isLocalApartment = in_array($type, self::$objectTypes['IS_APARTMENT_LOCAL']) ? 1 : 0;
        return apply_filters( 'fasad_is_localApartment', $isLocalApartment, $type, $obj);
    }

    public static function isCommerical($type, $obj = null)
    {
        $isCommercial = in_array($type, self::$objectTypes['IS_COMMERCIAL']) ? 1 : 0;
        return apply_filters('fasad_is_commercial', $isCommercial, $type, $obj);
    }

    public static function isProject( $type, $obj = null ) {
        $isProject = ( preg_match( '/nyproduktionsprojekt/is', $type ) || (!empty($obj) && !empty($obj->formOfOwnership)  && preg_match( '/nyproduktionsprojekt/is', $obj->formOfOwnership )) ? 1 : 0 );
        return apply_filters( 'fasad_is_project', $isProject, $type, $obj);
    }

    public static function isReserved( $type, $obj = null ) {
        $isReserved = ( preg_match( '/reserverad/is', $type ) || (!empty($obj) && !empty($obj->activityCategory)  && preg_match( '/reserverad/is', $obj->activityCategory )) ? 1 : 0 );
        return apply_filters( 'fasad_is_reserved', $isReserved, $type, $obj);
    }

    public static function isBooked( $type, $obj = null ) {
        $isBooked = ( preg_match( '/bokad/is', $type ) || (!empty($obj) && !empty($obj->activityCategory)  && preg_match( '/bokad/is', $obj->activityCategory )) ? 1 : 0 );
        return apply_filters( 'fasad_is_booked', $isBooked, $type, $obj);
    }

    public static function isRetired( $type, $obj = null ) {
        $isRetired = ( preg_match( '/återtagen/is', $type ) || (!empty($obj) && !empty($obj->activityCategory)  && preg_match( '/återtagen/is', $obj->activityCategory )) ? 1 : 0 );
        return apply_filters( 'fasad_is_retired', $isRetired, $type, $obj);
    }

    public static function isResting( $type, $obj = null ) {
        $isResting = ( preg_match( '/vilande/is', $type ) || (!empty($obj) && !empty($obj->activityCategory)  && preg_match( '/vilande/is', $obj->activityCategory )) ? 1 : 0 );
        return apply_filters( 'fasad_is_resting', $isResting, $type, $obj);
    }

    public function fasadStats()
    {
        if ((!defined('WP_ENV') || \WP_ENV === 'production')) {
            add_action('wp_footer', function (){
                if ($this->isSingleListing()) {
                    $listingId = self::getFasadId(get_the_ID());
                    echo '<img style="display:none;height:0;" src="https://counter.fasad.eu/track.php?fkobject='.$listingId.'" alt="" title=""/>'.PHP_EOL;
                }
            });
        }
    }

    public function wpHeadAddFasadData()
    {
        if ($this->isSingleListing()) {
            if ($wpId = get_the_ID()) {
                $listingId = $this->getFasadId($wpId);
            } else {
                $listingId = self::isPreview();
            }
            if ($listingId) {
                echo '<meta name="listing" content="' . $listingId . '" />' . PHP_EOL;
            }
        }
    }

    public function setOgImages()
    {
        if(function_exists('is_plugin_active') && is_plugin_active('wordpress-seo/wp-seo.php')):
            //Make yoast handle this
            $this->loader->addAction('wpseo_add_opengraph_images', $this, 'yoastSetOgImages');
            $this->loader->addAction('wpseo_twitter_image', $this, 'yoastSetTwitterImage');
        else:
            $this->loader->addAction('wp_head', $this, 'wpHeadSetOgImages');
        endif;
    }

    public function yoastSetOgImages(\Yoast\WP\SEO\Values\Open_Graph\Images $image_container)
    {
        $ogImageNum = (function_exists('get_field') ? get_field(self::$prefix . 'ogimagenum', 'option') : null);
        $ogImageNum = ($ogImageNum === null ? 5 : $ogImageNum);
        if ($this->isSingleListing() && $ogImageNum) {
            $fetcher = Fasad::getObjectFetcher();
            $images = $fetcher->getAttribute('images');
            $imageClass = new \PrekWeb\Includes\Image($this->loader, $this->options);
            if (!empty($images)) {
                $width = 1920;
                $i = 0;
                foreach ($images as $image) {
                    $listImage = Image::getImageUrlByVariant($image, 'highres', true);
                    $height = floor($width / $listImage->width * $listImage->height);
                    $image = $imageClass->processImage($listImage->path, $width, $height);
                    $image_container->add_image(
                        [
                            'url'    => $image,
                            'width'  => $width,
                            'height' => $height,
                        ]
                    );
                    $i++;
                    if ($i >= $ogImageNum) break;
                }
            }
        }
        // Todo: Add realtor image
    }

    public function yoastSetTwitterImage($image)
    {
        $ogImageNum = (function_exists('get_field') ? get_field(self::$prefix . 'ogimagenum', 'option') : null);
        $ogImageNum = ($ogImageNum === null ? 5 : $ogImageNum);
        if ($this->isSingleListing() && $ogImageNum) {
            $fetcher = Fasad::getObjectFetcher();
            $images = $fetcher->getAttribute('images');
            $imageClass = new \PrekWeb\Includes\Image($this->loader, $this->options);
            if (!empty($images)) {
                $width = 1920;
                $listImage = Image::getImageUrlByVariant($images[0], 'highres', true);
                $height = floor($width / $listImage->width * $listImage->height);
                return $imageClass->processImage($listImage->path, $width, $height);
            }
        }
        // Todo: Add realtor image
        return $image;
    }

    public function wpHeadSetOgImages()
    {
        $ogImageNum = (function_exists('get_field') ? get_field(self::$prefix . 'ogimagenum', 'option') : null);
        $ogImageNum = ($ogImageNum === null ? 5 : $ogImageNum);
        if ($this->isSingleListing() && $ogImageNum) {
            $fetcher = Fasad::getObjectFetcher();
            $images = $fetcher->getAttribute('images');
            $imageClass = new \PrekWeb\Includes\Image($this->loader, $this->options);
            if ($images) {
                $width = 1920;
                ob_start();
                $i = 0;
                foreach ($images as $image) {
                    $listImage = Image::getImageUrlByVariant($image, 'highres', true);
                    $height = floor($width / $listImage->width * $listImage->height);
                    $image = $imageClass->processImage($listImage->path, $width, $height);
                    echo '<meta property="og:image" content="' . $image . '" />' . PHP_EOL;
                    echo '<meta property="og:image:width" content="' . $width . '" />' . PHP_EOL;
                    echo '<meta property="og:image:height" content="' . $height . '" />' . PHP_EOL;
                    $i++;
                    if ($i >= $ogImageNum) break;
                }
                $listImage = Image::getImageUrlByVariant($images[0], 'highres', true);
                if ($listImage) {
                    $height = floor($width / $listImage->width * $listImage->height);
                    $image = $imageClass->processImage($listImage->path, $width, $height);
                    echo '<meta property="twitter:card" content="summary_image_large">' . PHP_EOL;
                    echo '<meta property="twitter:image" content="' . $image . '">' . PHP_EOL;
                }
                echo ob_get_clean();
            }
        }
        // Todo: Add realtor image
    }

    public function registerEndpoints()
    {
        $this->loader->addFilter('init', $this, 'registerAllImagesEndpoint');
    }

    public function registerAllImagesEndpoint()
    {
        add_rewrite_endpoint(apply_filters('fasad_bridge_endpointAllImages', 'all-images'), EP_PERMALINK);
    }

    private function setupInquiry()
    {
        // Add hidden FasAd fields to forms with '{{fasadfields}}'
        $this->loader->addFilter('wpcf7_form_elements', $this, 'addFasadFieldsToForm');

        // Add fields as mail-tags in the help text
        $this->loader->addFilter('wpcf7_collect_mail_tags', $this, 'mailTags', 10, 3);

        // Add fields as mail-tags in the help text
        $this->loader->addFilter('wpcf7_is_email', $this, 'mailAddress', 10, 2);

        // Replace FasAd field placeholders with correct values in the form
        $this->loader->addFilter('do_shortcode_tag', $this, 'fasadCF7', 10, 4);

        // Setup script tag
        $this->loader->addAction('init', $this, 'inquiryScripts');
    }

    private function setupSecureBids()
    {
        // Return expanded listing
        $this->loader->addFilter('fasad_secure_bids_listing', $this, 'secureBidsListing');

        // Return correct theme directory
        $this->loader->addFilter('fasad_secure_bids_theme_directory', $this, 'secureBidsThemeDirectory');

        // Add action for updating bids on complete
        $this->loader->addAction('fasad_secure_bids_checkBidStatus_success', $this, 'secureBidsCheckBidStatusSuccess');
    }

    public function mailTags($mailtags, $args, $cf7Instance) {
        $mailtags[] = 'fkobject';
        $mailtags[] = 'fkcorporation';
        $mailtags[] = 'address_object';
        $mailtags[] = 'city_object';
        $mailtags[] = 'realtorname';
        $mailtags[] = 'realtortitle';
        $mailtags[] = 'realtoremail';
        return $mailtags;
    }

    /*
     * Avoid "E-postadress med ogiltigt format har angivits." warning
     * by adding common email fields here
     */
    public function mailAddress($result, $email) {
        $mailAddresses = apply_filters('fasad_bridge_email_fields', [
            '[mail]',
            '[realtoremail]'
        ]);
        if (!$result && in_array($email, $mailAddresses)) {
            $result = true;
        }
        return $result;
    }

    public function addFasadFieldsToForm($elements)
    {
        return str_replace(
            '{{fasadfields}}',
            PHP_EOL . '{{fkobject}}' . '{{address}}' . '{{city}}' . '{{realtorname}}' . '{{realtortitle}}' . '{{realtoremail}}' . PHP_EOL,
            $elements
        );
    }

    public function fasadCF7($output, $tag, $atts, $m)
    {
        if ($tag === 'contact-form-7') {
            // Add fkcorporation to all forms
            // to work with corporation queue
            $fkcorporation = apply_filters('corporation_id', 0);
            $fkcorporation_replace = '';
            if ($fkcorporation) {
                $output = str_replace('</form>', '{{fkcorporation}}</form>', $output);
                $fkcorporation_replace = '<input type="hidden" name="fkcorporation" value="' . $fkcorporation .'">';
            }
        }
        if (
            $tag === 'contact-form-7' &&
            (
                strpos($output, '{{fkobject}}') !== false ||
                strpos($output, '{{fkcorporation}}') !== false ||
                strpos($output, '{{address}}') !== false ||
                strpos($output, '{{city}}') !== false ||
                strpos($output, '{{realtorname}}') !== false ||
                strpos($output, '{{realtortitle}}') !== false ||
                strpos($output, '{{realtoremail}}') !== false ||
                strpos($output, '{{showings}}') !== false
            )
        ):
            $fkobject_replace = $address_replace = $city_replace = $realtorname_replace = $realtortitle_replace = $realtoremail_replace = $showings_replace = '';
            $listingData = Fasad::expandObject();
            if ($listingData):
                if(!empty($listingData->id)){
                    $fkobject_replace = '<input type="hidden" name="fkobject" value="' . $listingData->id .'">';
                    if (strpos($output, '{{fkobject}}') !== false) {
                        // No fkcorporation if we have an object
                        $fkcorporation_replace = '';
                    }
                }
                if (!empty($listingData->location)) {
                    if (!empty($listingData->location->address)) {
                        // To be able to use [address_object] in the email from CF7 to user
                        $address_replace = '<input type="hidden" name="address_object" value="' . $listingData->location->address .'">';
                    }
                    if (!empty($listingData->location->city)) {
                        // To be able to use [city_object] in the email from CF7 to user
                        $city_replace = '<input type="hidden" name="city_object" value="' . $listingData->location->city .'">';
                    }
                }
                if (!empty($listingData->realtors)) {
                    // To be able to use [realtorname] in the email from CF7 to user
                    $realtorname_replace = '<input type="hidden" name="realtorname" value="' . $listingData->realtors[0]->firstname . ' ' . $listingData->realtors[0]->lastname .'">';

                    // To be able to use [realtortitle] in the email from CF7 to user
                    $realtortitle_replace = '<input type="hidden" name="realtortitle" value="' . $listingData->realtors[0]->title .'">';

                    // To be able to use [realtoremail] in the email from CF7 to user
                    $realtoremail_replace = '<input type="hidden" name="realtoremail" value="' . $listingData->realtors[0]->email .'">';
                }
                if($listingData->has->upcomingShowings):
                    $showings_replace .= '<div class="showings">';
                    foreach ($listingData->showingsFormatted as $key => $showing):
                        if($showing->openforregistration):
                            $options = [];
                            if(!empty($showing->slots)){
                                foreach($showing->slots as $slot){
                                    $value = 'Visningsgrupp ' . $slot->id;
                                    if(!empty($slot->starttime)){
                                        $value = $slot->starttime;
                                        if(!empty($slot->endtime)){
                                            $value .= ' - ' . $slot->endtime;
                                        }
                                    }
                                    $options[] = (object)[
                                        'id' => $slot->id,
                                        'value' => $value,
                                        'disabled' => $slot->fullybooked == 1 ? true : false,
                                    ];
                                }
                            }
                            $slot_select = '';
                            if(count($options) > 0){
                                $slot_select .= '<select name="slot" data-belongsto="showing-' . $showing->showingid . '">';
                                foreach($options as $option){
                                    $disabled = $option->disabled ? 'disabled' : '';
                                    $slot_select .= '<option value="' . $option->id .'" ' . $disabled . '>' . $option->value .'</option>';
                                }
                                $slot_select .= '</select>';
                            }
                            $showings_replace .= '<span class="wpcf7-form-control wpcf7-radio">';
                            $showings_replace .= '<input type="radio" id="showing-' . $showing->showingid . '" class="" name="showing" value="' . $showing->showingid . '" ' . ($key === 0 ? 'checked' : '') . ' data-cy="showing-radio">';
                            $showings_replace .= '</span>';
                            $showings_replace .= '<span class="wpcf7-list-item-label"><label for="showing-' . $showing->showingid .'">' . $showing->daynum . ' ' . $showing->month . '</label></span>';
                            $showings_replace .= '<span class="slot">' . $slot_select . '</span>';
                        endif;
                    endforeach;
                    $showings_replace .= '</div>';
                endif;
            endif;
            $output = str_replace('{{fkobject}}',      $fkobject_replace,      $output);
            $output = str_replace('{{fkcorporation}}', $fkcorporation_replace, $output);
            $output = str_replace('{{address}}',       $address_replace,       $output);
            $output = str_replace('{{city}}',          $city_replace,          $output);
            $output = str_replace('{{realtorname}}',   $realtorname_replace,   $output);
            $output = str_replace('{{realtortitle}}',  $realtortitle_replace,  $output);
            $output = str_replace('{{realtoremail}}',  $realtoremail_replace,  $output);
            $output = str_replace('{{showings}}',      $showings_replace,      $output);
        endif;
        return $output;
    }

    public function inquiryScripts()
    {
        // Add the js file
        // Will be enqueued in doEnqueueScripts()
        $deps = ['jquery'];
        if(class_exists('HTML_Forms\Forms')){
            $deps[] = 'html-forms';
        }
        if(class_exists('WPCF7_ContactForm')){
            $deps[] = 'contact-form-7';
        }
        $script = new \stdClass();
        $script->handle = 'prekweb/fasad-inquiry.js';
        $script->file   = 'fasad-inquiry.js';
        $script->deps   = $deps;
        $script->localize = apply_filters('fasad_bridge_inquiryLocalize', [
            'objectName' => 'fasadFormData',
            'data' => [
                'interestSuccessMessage'   => '<h2>Tack för din intresseanmälan!</h2><p>Ansvarig mäklare kommer att kontakta dig inom kort med förslag på tid för personlig visning.</p>',
                'speculatorSuccessMessage' => '<h2>Tack för visat intresse!</h2><p>Vi kommer att kontakta dig inom kort.</p>',
                'showingSuccessMessage'    => 'Välkommen till visningen!',
                'showingFullMessage'       => 'Den här visningen är fullbokad men du är välkommen att kontakta ansvarig mäklare och boka ytterligare visningar.',
            ]
        ]);

        // Setup js events to the scripts
        // Will use forms in the following containers:
        $formSelectors = apply_filters('fasad_bridge_formSelectors', [
            '.interest-form-wrapper',
            //'.showing-form-wrapper',
        ]);
        $formCallbacks = apply_filters('fasad_bridge_formCallbacks',
                                       array_fill(0, count($formSelectors), 'function(){}')
        );
        $script->inline = '';
        foreach ($formSelectors as $key => $selector) {
            $script->inline .= PHP_EOL . "Prek.FasadInquiry.init('".$selector."', $formCallbacks[$key]);";
        }
        $script->inline .= '';
        $this->scripts[] = apply_filters('fasad_bridge_inquiryScript', $script);
    }

    public function secureBidsListing()
    {
        $listing = Fasad::expandObject();
        return $listing;
    }

    public function secureBidsThemeDirectory($path)
    {
        if (Helpers::isSage9()) {
            return $path . '/views';
        }
        if (Helpers::isSage10()) {
            return $path; //TODO: fix for sage10
        }
        return $path;
    }

    public function secureBidsCheckBidStatusSuccess($response)
    {
        if (isset($response['status']) && $response['status'] === 'COMPLETE') {
            if (!empty($response['biddings']) && !empty($response['listing_id'])) {
                if (is_plugin_active('fasad-bridge/FasadBridge.php') || class_exists('FasadBridge\FasadBridge')) {
                    if ($postID = self::getWpId($response['listing_id'])) {
                        $fetcher = new Database($postID);
                        $biddings = $response['biddings'];
                        if (!empty($biddings->biddingPolicy) && !empty($biddings->bids)) {
                            update_post_meta($postID, $fetcher->getPrefix() . "biddingPolicy", serialize($biddings->biddingPolicy));
                            update_post_meta($postID, $fetcher->getPrefix() . "bids", serialize($biddings->bids));
                        }
                    }
                } elseif (is_plugin_active('fasad-starter/fasad-starter.php') || class_exists('FasadStarter')) {
                    global $fasadStarter;
                    //Todo: make this work for starter?
                }
            }
        }
    }


    /**
     * Save incremented sequence on newly created coworker posts
     * @param $postId
     * @param $post
     * @param $update
     */
    public function savePostFasadOffice($postId, $post, $update)
    {
        if(!$update){
            global $wpdb;
            $sequence = $wpdb->get_var($wpdb->prepare(
                "SELECT pm.meta_value from {$wpdb->postmeta} pm
                LEFT JOIN {$wpdb->posts} p ON p.id = pm.post_id
                WHERE pm.meta_key = %s
                AND p.post_status = %s
                AND p.post_type = %s
                ORDER BY pm.meta_value DESC
                LIMIT 1",
                self::$prefix . 'sequence',
                'publish',
                PublicSettings::FASAD_OFFICE_EXTENDED_POST_TYPE
            ));
            $nextSequence = is_numeric($sequence) ? ++$sequence : 1;
            update_post_meta($postId, self::$prefix . 'sequence', $nextSequence);
        }
    }

    function extendedPreviewLinks($preview_link, $post)
    {
        if (in_array($post->post_type, [
            PublicSettings::FASAD_OFFICE_EXTENDED_POST_TYPE,
            PublicSettings::FASAD_COWORKER_POST_TYPE
        ])) {
            $preview_link = sprintf('%s/?post_type=%s&p=%d&preview=true',
                                    home_url(),
                                    $post->post_type,
                                    $post->ID
            );
        }
        return $preview_link;
    }

    public function shareImage() {

        add_action('wp_head', function (){
            $image = '';
            $width = 1200;
            $height = 628;
            $share_image = (function_exists('get_field') ? get_field('share_image', 'option') : null);
            $imageClass = new \PrekWeb\Includes\Image($this->loader, $this->options);
            if(!has_post_thumbnail() && !is_singular('fasad_listing') && $share_image) {
                $image = $imageClass->processImage($share_image, 1200, 628);
            }
            if(has_post_thumbnail() && !is_singular('fasad_listing')) {
                $image = $imageClass->processImage(get_the_post_thumbnail_url(get_the_ID(), 'full'), 1200, 628);
            }

            if ($image) {
                ?>
                <meta property="og:image" content="<?= $image; ?>" />
                <meta name="twitter:image" content="<?= $image; ?>" />
                <meta property="og:image" content="<?= $image; ?>" />
                <meta property="og:image:width" content="<?= $width; ?>" />
                <meta property="og:image:height" content="<?= $height; ?>" />
                <meta property="twitter:card" content="summary_image_large">
                <meta property="twitter:image" content="<?= $image; ?>">
                <?php
            }
        });
    }

}
