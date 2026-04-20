<?php

namespace PrekWeb\Includes;

class Helpers {

    /**
     * Checks if current logged in user is PREK user
     * Will return true of email ends with "@prek.se"
     * Must run after the 'init' hook for user object to be available
     *
     * @return bool
     */
    public static function isPrekUser()
    {
        if (class_exists('PrekWebHelper\Includes\Helpers')) {
            return \PrekWebHelper\Includes\Helpers::isPrekUser();
        } else {
            /** @var WP_User $user */
            $user = wp_get_current_user();
            return !empty($user)
                && !empty($user->data)
                && property_exists($user->data, 'user_email')
                && preg_match('/@prek.se$/', $user->data->user_email);
        }
    }

    public static function getImgTag($image_id, $alt = '')
    {
        $src = \wp_get_attachment_image_src($image_id);
        $img = '';
        if ($src) {
            $image_src    = $src[0];
            $image_srcset = \wp_get_attachment_image_srcset($image_id);
            $img = '<img src="' . $image_src . '" srcset="' . $image_srcset . '" alt="' . $alt . '">';
        }
        return $img;
    }

    /**
     * @param $number
     * @param int $decimals
     * @param string $unit
     * @return string
     */
    public static function numberFormat($number, $decimals = 0, $unit = '')
    {
        if (class_exists('PrekWebHelper\Includes\Helpers')) {
            return \PrekWebHelper\Includes\Helpers::numberFormat($number, $decimals, $unit);
        }
        try {
            $number = str_replace([' ', ','], ['', '.'], $number);
            if (!is_numeric($number)) {
                throw new \TypeError();
            }
            $number = number_format($number, $decimals, ',', ' ');
            $number = preg_replace('/,(0)+$/', '', $number);
            if ($unit) {
                $number .= ' ' . $unit;
            }
        } catch (\TypeError $e) {
        }
        return $number;
    }

    /*
     * Takes a number in any format:
     * 46736402544
     * +46(0)736402544
     * 070-154 12 45
     *
     * And returns it like +46701541245
     *
     * @param string $phoneString
     * @return string
     */
    public static function formatPhone($phoneString){
        $phoneString = self::normalizePhone($phoneString);
        if ($phoneString) {
            $phoneString = '+46' . $phoneString;
        }
        return apply_filters('fasad_formatPhone', $phoneString);
    }

    /**
     * /*
     * Takes a number in any format:
     * 46736402544
     * +46(0)736402544
     * 070-154 12 45
     *
     * And returns it like 0701-54 12 45
     *
     * @param string $phoneString
     * @param string $type         landline or cellphone
     * @return string
     */
    public static function formatPhoneString($phoneString, $type = 'cellphone'){
        $phoneString = self::normalizePhone($phoneString, true, $type);
        if ($type == 'cellphone') {
            $phoneString = preg_replace(
                '/(\\d{3})(\\d{2})(\\d{2})(\\d{2})/',
                '0$1-$2 $3 $4',
                $phoneString
            );
        } else {
            $phoneString = '0'.$phoneString;
        }
        return apply_filters('fasad_formatPhoneString', $phoneString, $type);
    }

    /**
     * Takes a cellphone number in any format:
     * 46736402544
     * +46(0)736402544
     * 070-154 12 45
     *
     * And returns it like 701541245
     *
     * Or a landline number:
     * 013-13 31 42
     * +46(0)13-13 31 42
     *
     * And returns it like 13-13 31 42
     *
     * so we can prepend with 0 or +46
     * depending on context
     *
     * @param string $phoneString
     * @param bool $outputString
     * @param string $type         landline or cellphone
     * @return string
     */
    private static function normalizePhone($phoneString, $outputString = false, $type = 'cellphone')
    {
        if (is_null($phoneString)) {
            return '';
        }
        $phoneString = trim($phoneString);
        if ($outputString && $type != 'cellphone') {
            // For non cellphone and output as a string,
            // we can't know the length of area code or number
            // so we keep the format as entered
            // only removing "+46(0)" from the beginning
            $patterns = [
                'characters' => '/[+()]/',
                'beginning'  => '/^(460?|0)/',
            ];
        } else {
            $patterns = [
                'characters' => '/\s+|[+\-()]/',
                'beginning'  => '/^(460?|0)/',
            ];
        }
        $phoneString = preg_replace($patterns['characters'], '', $phoneString);
        $phoneString = preg_replace($patterns['beginning'], '', $phoneString);
        return $phoneString;
    }

    public static function getPageByTemplate($template){
        global $wpdb;

        $page = '';

        $sql = "
                SELECT id
                FROM {$wpdb->prefix}posts wposts,
                {$wpdb->prefix}postmeta wpostmeta,
                {$wpdb->prefix}term_relationships wtermrelationships,
                {$wpdb->prefix}terms wterms
                WHERE wposts.ID = wpostmeta.post_id
                AND wpostmeta.meta_key = '_wp_page_template'
                AND wpostmeta.meta_value = '".$template."'
                AND wposts.post_type = 'page'
                AND wposts.post_status = 'publish'
                AND wposts.ID = wtermrelationships.object_id
                AND wterms.term_id = wtermrelationships.term_taxonomy_id
                AND wterms.slug = '".App::getLang()."'";

        $result = $wpdb->get_row($sql);

        if(!empty($result) && $result->id){
            $page = get_post($result->id);
        }

        return $page;
    }

    public static function getPageByPath($path){
        $page = get_page_by_path($path);
        return $page;
    }

    public static function isSage9()
    {
        return class_exists('Roots\Sage\Container');
    }

    public static function isSage10()
    {
        return class_exists('Roots\Acorn\Application');
    }

    /**
     * @deprecated Use getYoutubeVideoSrc() instead
     */
    public static function getYoutubeVideoId($url)
    {
        return self::getYoutubeVideoSrc($url);
    }

    /**
     * Get the YouTube embed url to add to a iframe src attribute.
     *
     * @param string $url The url of the media link
     * @return int|string
     */
    public static function getYoutubeVideoSrc($url, $settings = [])
    {
        // Check if the url is matching a youtube link.
        $url = str_replace('&amp;', '&', $url);
        if (preg_match('#^https?:\/\/(?:www\.)?(?:youtube\.com\/watch\?v=|youtu\.be\/)([\w\d\-\_]+)(?:&t=([\d]+s))?#',
                       $url, $matches)) {

            $params = array_merge([
                'autoplay'       => 1,
                'controls'       => 0,
                'fs'             => 0,
                'loop'           => 1,
                'modestbranding' => 1,
                'mute'           => 1,
                'playlist'       => $matches[1],
                'start'          => ($matches[2] ?? '0s'),
                'playsinline'    => 1,
                'rel'            => 0,
                'showinfo'       => 0
            ], $settings);

            return 'https://www.youtube.com/embed/' . $matches[1] . '?' . http_build_query($params);
        }

        return 0;
    }

    /**
     * Get the Vimeo embed url to add to a iframe src attribute.
     *
     * @param string $url The url of the media link
     * @return int|string
     */
    public static function getVimeoVideoSrc($url, $settings = [])
    {
        // Check if the url is matching a Vimeo link.
        $url = str_replace('&amp;', '&', $url);
        if (preg_match('#^https?:\/\/(?:player\.)?(?:vimeo\.com\/)(?:video\/)?([\w\d\-\_]+)(?:\#t=([\d]+s))?#',
                       $url, $matches)) {

            $params = array_merge([
                'autoplay'       => 1,
                'loop'           => 1,
                'muted'          => 1,
                'byline'         => 0,
                'portrait'       => 0,
                'title'          => 0,
                'controls'       => 0,
                'background'     => 1,
                'playsinline'    => 1
            ], $settings);

            return 'https://player.vimeo.com/video/' . $matches[1] . '?' . http_build_query($params);
        }

        return 0;
    }

}