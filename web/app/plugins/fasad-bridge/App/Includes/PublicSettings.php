<?php

namespace FasadBridge\Includes;

use FasadApiConnect\Includes\ApiConnectionHandler;
use FasadApiConnect\Includes\CacheTokenHandler;
use FasadBridge\FasadBridge;
use FasadBridge\Includes\Fetching\Api;
use FasadBridge\Includes\Fetching\Database;
use FasadBridge\Includes\Fetching\ListingQueryBuilder;
use FasadBridge\Includes\Synchronization\EchoOutput;
use FasadBridge\Includes\Synchronization\Listing;
use FasadBridge\Includes\Synchronization\LogOutput;
use FasadBridge\Includes\Synchronization\NullOutput;
use FasadBridge\Includes\Synchronization\Office;
use FasadBridge\Includes\Synchronization\Realtor;

class PublicSettings
{
    /**
     * The ID of this plugin.
     *
     * @var string $pluginName The ID of this plugin.
     */
    private $pluginName;

    /**
     * @var mixed Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * The version of this plugin.
     *
     * @var string $version The current version of this plugin.
     */
    private $version;

    /**
     * @var bool Holds the value if an user is trying to reach a preview object url
     */
    private $isPreview;

    /**
     * @var array Sync result. (fetched, deleted, updated etc)
     */
    private $syncResult = [];

    /**
     * @var Listing
     */
    protected $listing;

    /**
     * @var Office
     */
    protected $office;

    /**
     * @var Realtor
     */
    protected $realtor;

    // Note: coworker and office_extend should maybe be in PrekWeb instead
    const FASAD_LISTING_POST_TYPE         = "fasad_listing";
    const FASAD_PROTECTED_POST_TYPE       = "fasad_protected";
    const FASAD_REALTOR_POST_TYPE         = "fasad_realtor";
    const FASAD_COWORKER_POST_TYPE        = "fasad_coworker";
    const FASAD_OFFICE_POST_TYPE          = "fasad_office";
    const FASAD_OFFICE_EXTENDED_POST_TYPE = "fasad_office_extend";
    const ENDPOINT_SYNC_ALL               = "_sync";
    const ENDPOINT_COMPACT_LIST           = "_compact_list";
    const SKIP_ALL                        = 1;
    const SKIP_UNDERHAND                  = 2;
    const SKIP_REGULAR                    = 3;

    static $posttypesSlugs = [
        self::FASAD_LISTING_POST_TYPE         => 'objekt',
        self::FASAD_PROTECTED_POST_TYPE       => 'underhandsobjekt',
        self::FASAD_REALTOR_POST_TYPE         => 'personal',
        self::FASAD_COWORKER_POST_TYPE        => 'medarbetare',
        self::FASAD_OFFICE_POST_TYPE          => 'kontor',
        self::FASAD_OFFICE_EXTENDED_POST_TYPE => 'butik'
    ];

    static function posttypesSlugs($type)
    {
        return apply_filters('fasad_bridge_posttype_slug', self::$posttypesSlugs[$type], $type);
    }

    /**
     * Initialize the class and set its properties.
     *
     * @param string $pluginName The name of this plugin.
     * @param string $version The version of this plugin.
     */
    public function __construct($pluginName, $version)
    {
        $this->pluginName = $pluginName;
        $this->version = $version;
    }

    /**
     * Register the public plugin endpoints
     */
    public function registerEndpoints()
    {
        //        add_rewrite_endpoint('_sync', EP_PERMALINK);
        //        add_rewrite_endpoint('all-images', EP_PERMALINK);
    }

    public function notifySyncState($state, $params)
    {
        if (!empty($state)) {
            do_action_ref_array('fasad_bridge_sync_state', [['state' => $state, 'callbackParams' => $params]]);
        }
    }

    public function getAllWithShowings()
    {
        $listingIds           = [];
        $posts                = [];
        $listingsQueryBuilder = new ListingQueryBuilder(PublicSettings::FASAD_LISTING_POST_TYPE);
        $listingsQueryBuilder->meta('showings', '!=', '');
        $query = $listingsQueryBuilder->getQuery();
        if ($query->have_posts()) {
            $posts = array_merge($posts, $query->posts);
        }
        $listingsQueryBuilder = new ListingQueryBuilder(PublicSettings::FASAD_PROTECTED_POST_TYPE);
        $listingsQueryBuilder->meta('showings', '!=', '');
        $query = $listingsQueryBuilder->getQuery();
        if ($query->have_posts()) {
            $posts = array_merge($posts, $query->posts);
        }
        foreach ($posts as $listing) {
            $fetcher      = new Database($listing->ID);
            $listingIds[] = $fetcher->getAttribute('id');
        }
        return $listingIds;
    }

    public function compactList($params = [])
    {
        $list          = [];
        $posts         = [];
        $listingsQuery = (new ListingQueryBuilder(PublicSettings::FASAD_LISTING_POST_TYPE))->getQuery();
        if ($listingsQuery->have_posts()) {
            $posts = array_merge($posts, $listingsQuery->posts);
        }
        $listingsQuery = (new ListingQueryBuilder(PublicSettings::FASAD_PROTECTED_POST_TYPE))->getQuery();
        if ($listingsQuery->have_posts()) {
            $posts = array_merge($posts, $listingsQuery->posts);
        }
        foreach ($posts as $post) {
            $fetcher = new Database($post->ID);
            $list[]  = (object)[
                'id' => $fetcher->getAttribute('id'),
            ];
        }
        $formatter = new EchoOutput();
        if (in_array('ids', $params)) {
            $formatter->output('<ul id="listings" style="list-style: none; margin: 0; padding: 0;">');
            foreach ($list as $item) {
                $formatter->output('<li>' . $item->id . '</li>');
            }
            $formatter->output('</ul>');
        } else {
            $formatter->output('<pre id="listings">');
            $formatter->output(print_r($list, true));
            $formatter->output('</pre>');
        }
        $formatter->output('<span class="status">');
        $formatter->output('Done');
        $formatter->output('</span>');
        status_header(200); // To prevent wordpress to go to page not found
        exit(0);
    }

    public function doSync($params = [])
    {
        if (is_string($params)) {
            $params = [];
        }
        $timeStart = microtime(true);
        $params = $this->getParams($params);
        FasadBridge::log('doSync called', $params, $params);
        $this->notifySyncState('received', $params);
        if ($params['output'] == 'null') {
            $formatter = new NullOutput();
        } elseif ($params['output'] == 'log') {
            $formatter = new LogOutput();
        } else {
            $formatter = new EchoOutput();
        }

        if (!ob_get_level()) {
            ob_start();
        }

        //Send early response
        if ($params['source'] == 'fasad') {
            echo "Callback received, fetching data...";
            $this->doFlush();
            /*$size = ob_get_length();
            header("Content-Encoding: none");
            header("Content-Length: {$size}");
            header("Connection: close\r\n");
            ob_end_flush();
            ob_flush();
            flush();
            if (session_id()) {
                session_write_close();
            }*/
        }

        $formatter->output('<pre>', 'EchoOutput');

        $runSync = $this->preSync($params);
        if ($runSync) {
            try {
                $this->notifySyncState('started', $params);
                $apiConnectionHandler = new ApiConnectionHandler();
                if (!$params['skipListings'] || $params['skipListings'] > self::SKIP_ALL) {
                    $this->listing = new Listing($apiConnectionHandler, $formatter, $params);
                    $this->syncResult['listings'] = $this->listing->synchronize();
                }
                if (!$params['skipOffices']) {
                    $this->office = new Office($apiConnectionHandler, $formatter, $params);
                    $this->syncResult['offices'] = $this->office->synchronize();
                }
                do_action('fasad_bridge_synchronize_complete');
                FasadBridge::log('Sync complete', $params);
                $this->postSync($params);
                $formatter->output('Inläsning klar');
                $this->notifySyncState('completed', $params);
            } catch (\Exception $e) {
                $this->notifySyncState('error', array_merge($params, ['errorMessage' => $e->getMessage()]));
                do_action('prek_log_slack', $e, 'exception');
                do_action('prek_log_exception', $e, 'error');
                error_log(print_r($e, true));
                echo __('Kunde inte hämta data från FasAd API. Kolla dina inställningar.', 'fasad-bridge');
            }
        } else {
            $formatter->output('Synkronisering pågår redan...');
        }
        $formatter->output('</pre>', 'EchoOutput');

        while(ob_get_level() > 0) {
            ob_end_clean();
        }
        FasadBridge::log('doSync complete', $params, ['totalTime' => microtime(true) - $timeStart]);
        if (!$params['monitor']) {
            status_header(200); // To prevent wordpress to go to page not found
            exit(0);
        }
    }

    public function doFlush()
    {
        if (!headers_sent()) {
            // Disable gzip in PHP.
            ini_set('zlib.output_compression', 0);

            // Force disable compression in a header.
            // Required for flush in some cases (Apache + mod_proxy, nginx, php-fpm).
            header('Content-Encoding: none');
            $size = ob_get_length();
            header("Content-Length: {$size}");
            header("Connection: close\r\n");
        }

        // Fill-up 4 kB buffer (should be enough in most cases).
        echo str_pad('', 4 * 1024);

        // Flush all buffers.
        do {
            $flushed = @ob_end_flush();
        } while ($flushed);

        @ob_flush();
        flush();
    }

    /**
     * Responsible for redirecting to relevant code when triggers are matched.
     *
     * @param \WP_Query $query
     */
    public function trigger(\WP_Query $query)
    {
        if ($query->is_main_query()) {
            // Make sure that we're on the sync page.
            if (isset($query->query['pagename'])) {
                $pagename = $query->query['pagename'];
                if (in_array($pagename, [self::ENDPOINT_SYNC_ALL, self::ENDPOINT_COMPACT_LIST])) {
                    if (get_query_var('monitor')): ?>
                        <?php status_header(200); ?>
                        <!doctype html>
                        <html lang="sv-SE">
                        <head>
                            <?php wp_head(); ?>
                        </head>
                        <body>
                        <?php
                        if ($pagename === self::ENDPOINT_SYNC_ALL):
                            $this->doSync();
                        else:
                            $params = [];
                            if (isset($_GET['ids'])) {
                                $params[] = 'ids';
                            }
                            $this->compactList($params);
                        endif;
                        ?>
                        <?php wp_footer(); ?>
                        </body>
                        </html>
                        <?php die();?>
                    <?php else:
                        if ($pagename === self::ENDPOINT_SYNC_ALL):
                            $this->doSync();
                        else:
                            $params = [];
                            if (isset($_GET['ids'])) {
                                $params[] = 'ids';
                            }
                            $this->compactList($params);
                        endif;
                    endif;
                }
            }
        }
    }

    /**
     * If user tries to reach to for example: /objekt/12345, redirect to /objekt/skogsvagen-6
     * If not possible, the user is trying to reach a preview listing
     */
    public function urlRedirect()
    {
        $postType = get_query_var('post_type');
        if (in_array($postType, [self::FASAD_LISTING_POST_TYPE, self::FASAD_PROTECTED_POST_TYPE])) {
            $fasadId = get_query_var($postType);
            if (is_numeric($fasadId)) {
                // Check if someone is trying to reach a object that is synced in WordPress Database
                $existingListing = get_posts(
                    [
                        "post_type"      => $postType,
                        "posts_per_page" => 1,
                        "meta_query"     => [
                            'compare' => 'AND',
                            [
                                "key"   => "_fasad_id",
                                "value" => $fasadId,
                            ],
                            [
                                'key'   => '_fasad_minilist',
                                'value' => '0',
                                'compare' => '=',
                            ]
                        ],
                    ]
                );

                if ($existingListing && isset($existingListing[0])) {
                    $url = get_permalink($existingListing[0]);
                    if (strpos($url, $_SERVER['REQUEST_URI']) === false) {
                        // Avoid redirect loop
                        if (wp_redirect($url, 301)) {
                            exit;
                        }
                    }
                } else {
                    //todo: underhand
                    $fetcher = new Api(get_query_var(self::FASAD_LISTING_POST_TYPE));
                    //We have a preview, unset 404
                    if (!empty($fetcher->getData())) {
                        status_header(200); // Todo: Needed?
                        global $wp_query; // Todo: Needed?
                        $wp_query->is_404 = false; // Todo: Needed?
                        $this->isPreview  = true;
                    }
                }
            }
        }
    }

    /**
     * If object is minilist redirect to 404.
     * Wordpress seems to automagically populate posts with based on tax query
     * on tax archive. Because of this, archivepage will get a 404 if first (?)
     * post is a minilist, so we need to check all taxes
     *
     * @param $template
     * @return mixed
     */
    public function redirectMinilist($template) {
        if (get_post_type() === self::FASAD_LISTING_POST_TYPE
            && !is_tax('fasad_listing_type')
            && !is_tax('fasad_listing_district')
            && !is_tax('fasad_listing_districtinfo')
            && !is_tax('fasad_listing_city')
            && !is_tax('fasad_listing_commune')
            && !is_tax('fasad_listing_tag')
            && get_post_meta(get_the_ID(), '_fasad_minilist', true)
        ) {
            global $wp_query;
            $wp_query->set_404();
            $wp_query->is_404 = true;
            $wp_query->post_count = 0;
            $wp_query->posts = [];
            $wp_query->post = false;
            status_header(404);
            nocache_headers();
            $template = get_404_template();
        }
        return $template;
    }

    /**
     * Add single listing classes when listing is a preview
     *
     * @param $classes
     *
     * @return mixed|void
     */
    public function bodyClass($classes)
    {
        $classes = apply_filters('preview_body_class', $classes, $this->isPreview);

        return $classes;
    }

    /**
     * Try to avoid previews getting indexed by Google
     * Note: If Bedrock and not production, the Disallow Indexing plugin
     * will also add this (but for all pages). So in those cases this
     * line will be duplicated.
     *
     */
    public function metaTags()
    {
        if ($this->isPreview) {
            echo '<meta name="robots" content="noindex, nofollow" />' . PHP_EOL;
        }
    }

    /**
     * Register source as query var
     *
     * @param array $vars
     *
     * @return array
     */
    public function registerQueryVars($vars){
        $vars[] = 'source';
        $vars[] = 'lock';
        $vars[] = 'force';
        $vars[] = 'updatetype';
        $vars[] = 'action';
        $vars[] = 'objectid';
        $vars[] = 'skiplistings';
        $vars[] = 'skipoffices';
        $vars[] = 'verbose';
        $vars[] = 'monitor';
        return $vars;
    }

    /**
     * Load the themes/THEME_NAME/fasad/single-preview.php when it is a fasad preview.
     * Or load a custom path
     *
     *
     * @param $path
     *
     * @return string
     */
    public function previewTemplate($path)
    {
        if ($this->isPreview) {
            if (locate_template("fasad/single-preview.php")) {
                $path = locate_template("fasad/single-preview.php");
            }

            $path = apply_filters('custom_preview_template', $path);
        }

        return $path;
    }

    /**
     * Register the custom post types
     */
    public function registerCustomPostTypes()
    {
        // Listing
        $args = [
            "labels"        => ["name" => __("Objekt", "fasad-bridge")],
            "description"   => __("Objekt från FasAd", "fasad-bridge"),
            "public"        => true,
            "rewrite"       => [
                "slug" => self::posttypesSlugs(self::FASAD_LISTING_POST_TYPE)
            ],
            "has_archive"   => true,
            "menu_position" => 20,
            "menu_icon"     => plugins_url('', __DIR__) . "/assets/img/fasad-icon-16.png"
        ];
        register_post_type(
            self::FASAD_LISTING_POST_TYPE,
            apply_filters('fasad_bridge_register_posttype', $args, self::FASAD_LISTING_POST_TYPE)
        );

        $this->registerListingTaxonomies();

        // Protected listing
        $args = [
            "labels"        => ["name" => __("Underhandsobjekt", "fasad-bridge")],
            "description"   => __("Objekt från FasAd", "fasad-bridge"),
            "public"        => true,
            "exclude_from_search" => true,
            "publicly_queryable"  => false,
            "rewrite"       => [
                "slug" => self::posttypesSlugs(self::FASAD_PROTECTED_POST_TYPE)
            ],
            "has_archive"         => false,
            "menu_position" => 21,
            "menu_icon"     => plugins_url('', __DIR__) . "/assets/img/fasad-icon-16.png"
        ];
        register_post_type(
            self::FASAD_PROTECTED_POST_TYPE,
            apply_filters('fasad_bridge_register_posttype', $args, self::FASAD_PROTECTED_POST_TYPE)
        );

        // Office
        $args = [
            "labels"        => ["name" => __("Kontor", "fasad-bridge")],
            "description"   => __("Kontor från FasAd", "fasad-bridge"),
            "public"        => true,
            "rewrite"       => [
                "slug" => self::posttypesSlugs(self::FASAD_OFFICE_POST_TYPE)
            ],
            "has_archive"   => true,
            "menu_position" => 22,
            "menu_icon"     => plugins_url('', __DIR__) . "/assets/img/fasad-icon-16.png"
        ];
        register_post_type(
            self::FASAD_OFFICE_POST_TYPE,
            apply_filters('fasad_bridge_register_posttype', $args, self::FASAD_OFFICE_POST_TYPE)
        );

        // Realtor
        $args = [
            "labels"        => ["name" => __("Personal", "fasad-bridge")],
            "description"   => __("Personal från FasAd", "fasad-bridge"),
            "public"        => true,
            "rewrite"       => [
                "slug" => self::posttypesSlugs(self::FASAD_REALTOR_POST_TYPE)
            ],
            "has_archive"   => true,
            "menu_position" => 23,
            "menu_icon"     => plugins_url('', __DIR__) . "/assets/img/fasad-icon-16.png"
        ];
        register_post_type(
            self::FASAD_REALTOR_POST_TYPE,
            apply_filters('fasad_bridge_register_posttype', $args, self::FASAD_REALTOR_POST_TYPE)
        );
    }

    private function registerListingTaxonomies()
    {
        // Taxonomy Type for Listings
        register_taxonomy(
            "fasad_listing_type",
            [self::FASAD_LISTING_POST_TYPE, self::FASAD_PROTECTED_POST_TYPE],
            [
                "hierarchical"      => false,
                "show_ui"           => true,
                "show_admin_column" => true,
                "query_var"         => true,
                "public"            => true,
                "labels"            => [
                    "name" => __("Typ", "fasad-bridge")
                ],
                "rewrite"           => ["slug" => "typ"],
            ]
        );

        // Taxonomy District for Listings
        register_taxonomy(
            "fasad_listing_district",
            [self::FASAD_LISTING_POST_TYPE, self::FASAD_PROTECTED_POST_TYPE],
            [
                "hierarchical"      => false,
                "show_ui"           => true,
                "show_admin_column" => true,
                "query_var"         => true,
                "public"            => true,
                "labels"            => [
                    "name" => __("Distrikt", "fasad-bridge")
                ],
                "rewrite"           => ["slug" => "distrikt"],
            ]
        );

        if (apply_filters('fasad_bridge_register_district_info_taxonomy', false)) {
            // Taxonomy District Info for Listings
            register_taxonomy(
                "fasad_listing_districtinfo",
                [self::FASAD_LISTING_POST_TYPE, self::FASAD_PROTECTED_POST_TYPE],
                [
                    "hierarchical"      => false,
                    "show_ui"           => true,
                    "show_admin_column" => true,
                    "query_var"         => true,
                    "public"            => true,
                    "labels"            => [
                        "name" => __("Område", "fasad-bridge")
                    ],
                    "rewrite"           => ["slug" => "omrade"],
                ]
            );
        }
        // Taxonomy City for Listings
        register_taxonomy(
            "fasad_listing_city",
            [self::FASAD_LISTING_POST_TYPE, self::FASAD_PROTECTED_POST_TYPE],
            [
                "hierarchical"      => false,
                "show_ui"           => true,
                "show_admin_column" => true,
                "query_var"         => true,
                "public"            => true,
                "labels"            => [
                    "name" => __("Stad", "fasad-bridge")
                ],
                "rewrite"           => ["slug" => "stad"],
            ]
        );

        // Taxonomy Commune for Listings
        register_taxonomy(
            "fasad_listing_commune",
            [self::FASAD_LISTING_POST_TYPE, self::FASAD_PROTECTED_POST_TYPE],
            [
                "hierarchical"      => false,
                "show_ui"           => true,
                "show_admin_column" => true,
                "query_var"         => true,
                "public"            => true,
                "labels"            => [
                    "name" => __("Kommun", "fasad-bridge")
                ],
                "rewrite"           => ["slug" => "kommun"],
            ]
        );

        // Taxonomy Tags for Listings
        register_taxonomy(
            "fasad_listing_tag",
            [self::FASAD_LISTING_POST_TYPE, self::FASAD_PROTECTED_POST_TYPE],
            [
                "hierarchical"      => false,
                "show_ui"           => true,
                "show_admin_column" => true,
                "query_var"         => true,
                "public"            => true,
                "labels"            => [
                    "name" => __("Taggar", "fasad-bridge")
                ],
                "rewrite"           => ["slug" => "taggar"],
            ]
        );
    }

    private function getParams($params)
    {
        $output = 'echo';
        $uuid = uniqid();
        $source = '';
        $lock = 1;
        $force = [];
        $updateType = 'full';
        $action = '';
        $skipListings = '';
        $skipOffices = '';
        $monitor = '';
        $verbose = '';
        $logLevel = 'info';
        $allowedListingsUpdate = ['showings','bids', 'documents','servitudes', 'postmeta', 'tags', 'descriptiontype', 'location'];

        $sourceVar       = get_query_var('source');
        $lockVar         = get_query_var('lock');
        $forceVar        = get_query_var('force');
        $updateTypeVar   = get_query_var('updatetype');
        $actionTypeVar   = get_query_var('action');
        $objectIdVar     = get_query_var('objectid');
        $skipListingsVar = get_query_var('skiplistings');
        $skipOfficesVar  = get_query_var('skipoffices');
        $verboseVar      = apply_filters('prek_log_verbose', get_query_var('verbose'));
        $monitorVar      = get_query_var('monitor');
        if ($sourceVar !== '') {
            if ($sourceVar === 'fasad') {
                $source = 'fasad';
                $output = 'null';
            }
        }
        if ($lockVar !== '') {
            if (intval($lockVar) === 0) {
                $lock = 0;
            }
        }
        if ($objectIdVar !== '') {
            if (is_numeric($objectIdVar)) {
                $objectId = $objectIdVar;
                $forceVar = $objectIdVar; // Passed an objectid => force sync of that listing
            }
        }
        if ($forceVar !== '') {
            if ($forceVar === 'all') {
                $force = $forceVar;
            } elseif (is_numeric($forceVar)) {
                $force = [$forceVar];
            } else {
                $forceArray = explode(',', $forceVar);
                if (is_array($forceArray) && !empty($forceArray)) {
                    //Get only numeric values
                    $forceArray = array_filter($forceArray, function ($item) {
                        return is_numeric($item);
                    });
                    if (!empty($forceArray)) {
                        $force = array_map(function ($item) {
                            return intval($item);
                        }, $forceArray);
                    }
                }
            }
        }

        if ($updateTypeVar !== '') {
            if ($updateTypeVar === 'full') {
                $updateType = $updateTypeVar;
            } else {
                $updateTypeVar = explode(',', $updateTypeVar);
                if (is_array($updateTypeVar) && !empty($updateTypeVar)) {
                    $updateType = $updateTypeVar;
                }
            }
        }

        if ($actionTypeVar !== '') {
            if (in_array($actionTypeVar, ['update', 'publish', 'unpublish'])) {
                $action = $actionTypeVar;
            }
        }

        if ($skipListingsVar !== '') {
            $skipListings = intval($skipListingsVar);
        }

        if ($skipOfficesVar !== '') {
            if (intval($skipOfficesVar) === 1) {
                $skipOffices = 1;
            }
        }

        if ($verboseVar !== '') {
            if (intval($verboseVar) === 1) {
                $verboseVar = 'simplehistory';
            }
            if(in_array($verboseVar, ['simplehistory', 'slack'])) {
                $verbose = $verboseVar;
            }
        }

        if ($monitorVar !== '') {
            if (intval($monitorVar) === 1) {
                $monitor = 1;
            }
        }

        if ($monitorVar !== '') {
            if (intval($monitorVar) === 1) {
                $monitor = 1;
            }
        }

        $params = array_merge([
                                  'output'       => $output,
                                  'lock'         => $lock,
                                  'force'        => $force,
                                  'updateType'   => $updateType,
                                  'action'       => $action,
                                  'skipListings' => $skipListings,
                                  'skipOffices'  => $skipOffices,
                                  'uuid'         => $uuid,
                                  'source'       => $source,
                                  'verbose'      => $verbose,
                                  'monitor'      => $monitor,
                                  '_logLevel'    => $logLevel
                              ], $params);
        $updateType = !is_array($params['updateType']) ? explode(',', $params['updateType']) : $params['updateType'];
        $updateType = array_intersect($updateType, $allowedListingsUpdate);
        $updateType = empty($updateType) ? 'full' : $updateType;
        $params['updateType'] = $updateType;
        if (($params['force'] !== 'all') && !is_array($params['force'])) {
            $params['force'] = '';
        }

        return apply_filters('fasad_bridge_params', $params);
    }

    private function preSync($params)
    {
        FasadBridge::log('preSync', $params);
        $lock = $params['lock'];
        if (!empty($params['force'])) {
            (new CacheTokenHandler())->delete();
        }
        $timezone = new \DateTimeZone('Europe/Stockholm');
        $date     = new \DateTime('now', $timezone);
        $now      = $date->getTimestamp();
        $wait = 60 * 5;
        //Get saved timestamp, or default to "waittime" ago
        $locked = intval(get_option('fasad-sync-lock', $now - $wait));
        //locktime has passed (or is nonexisting)
        if ((($locked + $wait) <= $now) || !$lock) {
            FasadBridge::log('preSync: no lock, run', $params);
            do_action_ref_array('fasad_bridge_pre_sync', [array_merge($params, ['run' => true])]);
            update_option('fasad-sync-lock', $now);
            return true;
        } else {
            FasadBridge::log('preSync: locked, check schedule', $params);
            //Schedule a sync if there is none
            $timestamp = $now + $wait;
            $scheduled = false;
            $this->notifySyncState('blocked', $params);
            if (!wp_next_scheduled('fasadSync')) {
                FasadBridge::log('preSync: no sync scheduled, try to schedule', $params);
                $scheduled = true;
                $cronParams = array_merge($params, ['output' => 'null', 'source' => 'cron', 'scheduledFrom' => 'sync_lock']);
                $scheduled = wp_schedule_single_event(($timestamp), 'fasadSync', [$cronParams]);
                if(is_wp_error($scheduled)) {
                    FasadBridge::log('preSync: scheduled failed', $params);
                }else{
                    FasadBridge::log('preSync: scheduled succeeded', $params);
                }
                $this->notifySyncState('scheduled', array_merge($params, ['scheduledAt' => $timestamp]));
            }
            do_action_ref_array('fasad_bridge_pre_sync', [array_merge($params, ['run' => false, 'scheduled' => $scheduled, 'scheduledAt' => $timestamp])]);
            return false;
        }
    }

    private function postSync($params)
    {

        do_action_ref_array('fasad_bridge_post_sync', [$params, $this->syncResult]);
        //Remove lock from sync
        delete_option('fasad-sync-lock');
        FasadBridge::log('postSync: release lock', $params);
    }

    /**
     * Clear all fasad data
     */
    public function clearFasadDataCallback()
    {
        check_ajax_referer('clear-listings', 'nonce');
        set_time_limit(0);

        $apiConnectionHandler = new ApiConnectionHandler();
        $this->listing = new Listing($apiConnectionHandler);
        $this->office = new Office($apiConnectionHandler);
        $this->realtor = new Realtor($apiConnectionHandler);

        $this->listing->deleteAll();
        $this->office->deleteAll();
        $this->realtor->deleteAll();

        wp_die(); // this is required to terminate immediately and return a proper response
    }

    public function addCronInterval($schedules)
    {
        $schedules['15_minutes'] = [
            'interval' => 15 * 60,
            'display'  => esc_html__(__('Var 15:e minut', 'fasad-bridge')),
        ];
        return $schedules;
    }

    public function setupCron()
    {
        $timezone = new \DateTimeZone('Europe/Stockholm');
        $date     = new \DateTime('now', $timezone);
        /*
        $hookName = 'sync_listings_with_showings';
        add_filter($hookName, [$this, 'cronSyncListingsWithShowings']);
        $now      = $date->getTimestamp();
        if (!wp_next_scheduled($hookName)) {
            wp_schedule_event($now, '15_minutes', $hookName);
        }
        */
        $hookName = 'sync_all_listings';
        add_filter($hookName, [$this, 'cronSyncAllListings']);
        $date->setTime(5, 0);
        if (!wp_next_scheduled($hookName)) {
            wp_schedule_event($date->getTimestamp(), 'daily', $hookName);
        }
    }

    public function cronSyncAllListings()
    {
        $params = [
            'output'        => 'null',
            'source'        => 'cron',
            'scheduledFrom' => 'sync_all_listings'
        ];

        do_action_ref_array('prek_log_cron', ['sync_all_listings', ['params' => $params]]);
        $this->doSync($params);
    }

    public function cronSyncListingsWithShowings()
    {
        $listingsWithShowings = $this->getAllWithShowings();
        $params               = [
            'output'        => 'null',
            'source'        => 'cron',
            'scheduledFrom' => 'sync_listings_with_showings',
            'force'         => $listingsWithShowings,
            'action'        => 'update',
            'updateType'    => ['showings'],
        ];

        if (!empty($listingsWithShowings)) {
            do_action_ref_array('prek_log_cron', ['sync_listings_with_showings', ['run' => !empty($listingsWithShowings), 'params' => $params]]);
            $this->doSync($params);
        }
    }

}

