<?php

namespace PrekWebHelper\Includes;

class Helpers
{
    /**
     * Checks if current logged in user is PREK user
     * Will return true of email ends with "@prek.se"
     * Must run after the 'init' hook for user object to be available
     *
     * @return bool
     */
    public static function isPrekUser(): bool
    {
        if (isset($_GET['usertype']) && $_GET['usertype'] === 'customer') {
            return false;
        }
        /** @var WP_User $user */
        $user = wp_get_current_user();
        return !empty($user)
            && !empty($user->data)
            && property_exists($user->data, 'user_email')
            && preg_match('/@prek.se$/', $user->data->user_email);
    }

    public static function getPostTypes(): array
    {
        $postTypes = [
            'listing' => [
                'name' => '',
                'slug' => '',
            ],
        ];

        if (self::hasBridge()) {
            $postTypes['listing']['name'] = \FasadBridge\Includes\PublicSettings::FASAD_LISTING_POST_TYPE;
            $postTypes['listing']['slug'] = \FasadBridge\Includes\PublicSettings::posttypesSlugs($postTypes['listing']['name']);
        } elseif (self::hasStarter()) {
            global $fasadStarter;
            $listingPostType              = $fasadStarter->cptName;
            $postTypeData                 = get_post_type_object($listingPostType);
            $postTypes['listing']['name'] = $listingPostType;
            $postTypes['listing']['slug'] = $postTypeData->rewrite['slug'];
        }

        return $postTypes;
    }

    public static function hasBridge(): bool
    {
        return class_exists('FasadBridge\FasadBridge');
    }

    public static function hasStarter(): bool
    {
        return class_exists('FasadStarter');
    }

    /*
     * Returns environmentvariable
     */
    public static function getEnv($variable)
    {
        if (function_exists('Roots\env')) {
            return \Roots\env($variable);
        }
        return getenv($variable);
    }

    /**
     * @param $number
     * @param int $decimals
     * @param string $unit
     * @param string $unitSpace
     * @return string
     */
    public static function numberFormat($number, $decimals = 0, $unit = '', $unitSpace = ' ')
    {
        if (is_null($number)) {
            return '';
        }
        $numberOrig = trim($number);
        try {
            $number = str_replace([' ', ','], ['', '.'], $numberOrig);
            if (!is_numeric($number)) {
                $number = $numberOrig;
                throw new \TypeError();
            }
            $number = number_format($number, $decimals, ',', ' ');
            $number = preg_replace('/,0+$/', '', $number); // Remove ,00
            $number = preg_replace('/(,[0-9]*[^0])0+$/', '$1', $number); // ,4500 => ,45
            if ($unit) {
                $number .= $unitSpace . $unit;
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

}
