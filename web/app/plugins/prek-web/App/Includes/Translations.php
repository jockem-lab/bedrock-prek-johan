<?php

namespace PrekWeb\Includes;

/*
 *
 * In Polylang settings under "Custom post types and Taxonomies", "Objekt" must be checked
 * "The front page url contains the language code instead of the page name or page id" must also be checked
 *
 * protected $acf = true; in the Controllers will NOT work, must pass get_the_ID() to get_field() to override data
 * from default language post.
 *
 */

use FasadBridge\Includes\PublicSettings;

class Translations
{

    protected $loader;
    protected $options;
    protected $polylangActive = false;
    protected $defaultLanguage;
    protected $postTypesFasad = [];
    protected $registeredStrings = [];

    public function __construct(\PrekWeb\Includes\Loader $loader, \PrekWeb\Includes\Options $options)
    {
        $this->loader  = $loader;
        $this->options = $options;
        $this->postTypesFasad = [
            PublicSettings::FASAD_LISTING_POST_TYPE,
            PublicSettings::FASAD_REALTOR_POST_TYPE
        ];
    }

    public function run()
    {
        $this->polylangActive = is_plugin_active('polylang/polylang.php');
        if ($this->polylangActive) {
            $this->setUpLanguage();
            $this->setUpJS();
            $this->setUpPosts();
        }
    }

    private function getPostTypesFasad()
    {
        return apply_filters('fasad_bridge_posttype_translate', $this->postTypesFasad);
    }

    public function setUpLanguage()
    {
        add_action('plugins_loaded', function () {
            if (function_exists('pll_default_language')) {
                $this->defaultLanguage = pll_default_language();
            }
        });
    }

    public function setUpJS()
    {
        $this->loader->addAction('wp_print_scripts', $this, 'doJS');
        // We must re-add this action that is removed by Soil
        add_action('wp_head', 'wp_print_head_scripts', 10);
    }

    /**
     * Create a JSON object with all strings, and a nifty function for translation in js.
     * Strings need 'js' => true when passed to registerStrings() to show up here.
     */
    public function doJS()
    {
        $prekLanguageObject = [
            'current_language' => self::pll_current_language(),
            'translations' => []
        ];

        if (!empty($this->registeredStrings)) {
            foreach ($this->registeredStrings as $string) {
                if ($string['js']) {
                    $prekLanguageObject['translations'][$string['string']] = [
                        'sv' => self::pll__($string['string']),
                        'en' => $string['string']
                    ];
                }
            }
            ?>
            <script>
                /* <![CDATA[ */
                var prekLanguageObject = <?= wp_json_encode($prekLanguageObject) . PHP_EOL;?>
                window.prek_pll__ = function(string){
                    const current_language = window.prekLanguageObject.current_language;
                    const translations = window.prekLanguageObject.translations;

                    let translation = string;
                    if (translations[string]) {
                        translation = translations[string][current_language];
                    }

                    return translation;
                };
                /* ]]> */
            </script>
            <?php
        }
    }

    public function registerStrings($name, $string, $group, $multiline = false, $js = false)
    {
        $this->registeredStrings[] = [
            'string'    => $string,
            'multiline' => $multiline,
            'js'        => $js
        ];
        pll_register_string( $name, $string, $group, $multiline );
    }

    public function getTranslations()
    {
        $option       = get_option('polylang');
        $default      = $option['default_lang'];

        $term         = get_term_by( 'slug' , $default , 'language' );
        $posts        = get_posts(array( 'name' => 'polylang_mo_'.$term->term_id , 'post_type' => 'polylang_mo' , 'post_status' => 'private' , 'posts_per_page' =>1 ));
        $post_id      = $posts[0]->ID;
        $translations = get_post_meta( $post_id , '_pll_strings_translations' , true );

        $translationsTmp = [];
        if (!empty($translations)) {
            foreach ($translations as $translation) {
                $translationsTmp[$translation[0]] = [
                    'en' => $translation[0],
                    $default => $translation[1],
                ];
            }
            $translations = $translationsTmp;
        }
        return $translations;
    }

    public function setUpPosts()
    {
        $this->loader->addAction('pre_get_posts', $this, 'ignoreLanguageOnPosts', 11, 1);
        $this->loader->addAction('set_object_terms', $this, 'preventLanguageSetOnPosts', 10, 6);
        $this->loader->addAction('post_type_link', $this, 'fixListingLinks', 21, 4);
        $this->loader->addFilter('acf/pre_load_post_id', $this, 'loadFieldsFromDefaultLanguagePost', 10, 2);
        $this->loader->addFilter('do_shortcode_tag', $this, 'replaceTranslatePlaceholders', 10, 4);
    }

    /*
     * If Polylang, ignore the language parameter when querying listings
     * since the listing posts only exist in one language
     */
    public function ignoreLanguageOnPosts($query) {
        $qv = &$query->query_vars;
        if (array_key_exists('post_type', $qv)) {
            $postType = (is_array($qv['post_type']) && !empty($qv['post_type'][0])) ? $qv['post_type'][0] : $qv['post_type'];
            if (in_array($postType, $this->getPostTypesFasad())){
                if (array_key_exists('tax_query', $qv) && is_array($qv['tax_query'])){
                    foreach ($qv['tax_query'] as $key => $terms){
                        if (!empty($terms['taxonomy']) && $terms['taxonomy'] == 'language'){
                            unset($qv['tax_query'][$key]);
                        }
                    }
                }
            }
        }
    }

    /**
     *
     * Make sure listings don't get a language set
     *
     * @param $object_id
     * @param $terms
     * @param $tt_ids
     * @param $taxonomy
     * @param $append
     * @param $old_tt_ids
     */
    public function preventLanguageSetOnPosts($object_id, $terms, $tt_ids, $taxonomy, $append, $old_tt_ids)
    {
        if (in_array($taxonomy, ['language', 'post_translations']) && in_array(get_post_type($object_id), $this->getPostTypesFasad())){
            wp_remove_object_terms($object_id, $terms, $taxonomy);
            wp_cache_delete( $object_id, $taxonomy . '_relationships' );
            wp_cache_delete( 'last_changed', 'terms' );
        }
    }

    /*
     * If Polylang, prepend listing links with '/<lang>/'
     */
    public function fixListingLinks($post_link, \WP_Post $post, $leavename, $sample) {
        if (in_array($post->post_type, $this->getPostTypesFasad())){
            //$post_link = pll_home_url(self::pll_current_language()) . 'objekt/'.$post->post_name.'/';
            $post_link = $this->translateFasAdPostLink($post_link, $post);
        }
        return $post_link;
    }

    public function translateFasAdPostLink($postLink, $post, $lang = null)
    {
        if (in_array($post->post_type, $this->getPostTypesFasad())){
            if (!$lang) {
                $lang = self::pll_current_language();
            }
            $base = $this->translateFasadSlugs($post->post_type, $lang);
            $postLink = pll_home_url($lang) . $base . '/' . $post->post_name . '/';
        }
        return $postLink;
    }

    public function translateFasadSlugs($postType, $lang)
    {
        return apply_filters('fasad_bridge_posttype_slug_' . $lang, PublicSettings::posttypesSlugs($postType), $postType);
    }

    /*
     * When loading fields from a translated post, load the from the default language post instead.
     * This way we can setup all layout fields for one language only.
     */
    public function loadFieldsFromDefaultLanguagePost($return, $post_id)
    {
        if (!function_exists('pll_get_post_language')) {
            // Happens on deactivaton
            return $return;
        }
        $post = null;
        if ($post_id instanceof \WP_Post) {
            $post = $post_id;
            $post_id = $post->ID;
        }
        if (pll_get_post_language($post_id) && pll_get_post_language($post_id) !== $this->defaultLanguage) {
            if (is_integer($post_id)) {
                $return = pll_get_post($post_id, $this->defaultLanguage);
                if ($post) {
                    $return = get_post($return);
                }
            }
        }
        return $return;
    }

    public function linkToTranslatedPost($string)
    {
        if (function_exists('pll_the_languages')) {
            $lang = pll_the_languages([
                                          'raw' => 1
                                      ]);

            $currentLang = self::pll_current_language();
            unset($lang[$currentLang]);
            if (empty($lang)) {
                return $string;
            }

            $translationLang = array_key_first($lang);
            $translatedPost = pll_get_post(get_the_ID(), $translationLang);

            // Name of other language
            $title = $lang[$translationLang]['name'];
            if (in_array(get_post_type(get_the_ID()), $this->getPostTypesFasad())) {
                // FasAd listing, just add lang param
                $url = $this->translateFasAdPostLink('', get_post(), $translationLang);
            } elseif (!empty($translatedPost)) {
                // Link to post in other language
                $url = get_permalink($translatedPost);
            } else {
                // Link to startpage in other language
                $url = pll_home_url($translationLang);
            }
        }

        return str_replace(['TRANSLATION_NAME', 'http://TRANSLATION_URL'], [$title, $url], $string);
    }

    /**
     * Replaces "@@Firstname@@" with "Förnamn" etc in shortcode output
     *
     * @param string $output
     * @param string $tag
     * @param array|string $attr
     * @param array $m
     * @return string
     */
    public function replaceTranslatePlaceholders($output, $tag, $attr, $m)
    {
        preg_match_all('/@@(.*?)@@/', $output, $matches);
        if (!empty($matches)) {
            foreach ($matches[1] as $match) {
                $output = str_replace('@@'.$match.'@@', self::pll__($match), $output);
            }
        }
        return $output;
    }

    public static function pll__($string, $newline = 'br', $forceLang = null)
    {
        if( function_exists('pll__') ) {
            if ($forceLang !== null && $forceLang != self::pll_current_language()) {
                $string = pll_translate_string( $string, $forceLang );
            } else {
                $string = pll__( $string );
            }
            switch($newline){
                case 'space':
                    $string = str_replace(['\r\n', '\r', '\n'], ' ', $string );
                    break;
                default:
                    $string = nl2br( $string );
                    break;
            }
        }
        return $string;
    }

    public static function pll_e($string, $newline = 'br')
    {
        echo self::pll__($string, $newline);
    }

    public static function pll_current_language()
    {
        $lang = '';
        if (function_exists('pll_current_language')) {
            $lang = pll_current_language();
        }
        if (!$lang) {
            $lang = get_locale();
        }
        return $lang;
    }

    public static function pll_get_post($postId, $forceLang = '')
    {
        return (function_exists('pll_get_post')) ? pll_get_post($postId, $forceLang) : get_post($postId);
    }

    public static function pll_home_url()
    {
        return (function_exists('pll_home_url')) ? pll_home_url(self::pll_current_language()) : home_url();
    }

    /**
     * Appends current language to an object key
     *
     * Before: $object->header
     * After:  $object->header_en
     *
     * @param object $object
     * @param string $key
     * @param string $default
     * @return string
     */
    public static function getTranslatedText($object, $key, $default = '')
    {
        if (is_array($object)) {
            $object = (object) $object;
        }
        $translatedKey = $key . '_'.self::pll_current_language();
        if (property_exists($object, $translatedKey)) {
            $text = $object->{$translatedKey};
        } elseif (property_exists($object, $key)) {
            $text = $object->{$key};
        } else {
            $text = '';
        }
        return $text ? $text : $default;
    }

}
