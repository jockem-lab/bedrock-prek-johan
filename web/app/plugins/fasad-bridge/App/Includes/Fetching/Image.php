<?php

namespace FasadBridge\Includes\Fetching;

class Image {

    protected static $defaultImageSettings = [
        "m" => "strict",
        "w" => 0,
        "h" => 0,
        "i" => 1,
        "c" => 70,
        "u" => 0
    ];

    /**
     * Get first image
     */
    public static function getListImage($images, $variantName = "highres", $fallbackToFirstSize = false)
    {
        $listImage = null;

        if (isset($images[0])) {
            $listImage = self::getImageUrlByVariant($images[0], $variantName, $fallbackToFirstSize);
        }

        return $listImage;
    }

    /**
     * Get first image variant
     * @param $image
     * @return mixed|null
     */
    public static function getFirstImageVariant($image)
    {
        $imageVariant = null;
        if (isset($image->variants[0])) {
            $imageVariant = $image->variants[0];
        }
        return $imageVariant;
    }


    /**
     * Get image variant by variant name
     *
     * @param        $image
     * @param string $variantName
     * @param bool $fallbackToFirstSize
     * @return mixed|null
     */
    public static function getImageUrlByVariant($image, $variantName = "highres", $fallbackToFirstSize = false)
    {
        $imageUrl = null;

        foreach ($image->variants as $variantKey => $variant) {
            if ($fallbackToFirstSize && is_null($imageUrl)) {
                $imageUrl = $variant;
            }
            if ($variant->type == $variantName) {
                $imageUrl = $variant;
                break;
            }
        }

        return $imageUrl;
    }

    /**
     * Get process image url
     * @param       $url
     * @param array $settings
     * @return string
     */
    public static function processImage($url, array $settings = [])
    {
        $params = self::$defaultImageSettings;

        foreach ($settings as $key => $setting) {
            $params[$key] = $setting;
        }
        if (apply_filters('process_use_webp', false)) {
            $params['webp'] = 1;
        }
        $httpQuery = http_build_query($params);
        return apply_filters('fasad_process_image', IMAGE_PROCESS_URL . ($url) . "&" . $httpQuery);
    }

}
