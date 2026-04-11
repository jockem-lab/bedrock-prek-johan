<?php

namespace App;

use FasadBridge\Includes\Fetching\Image;
use PrekWeb\Includes\Fasad;
use PrekWeb\PrekWeb;
use Roots\Acorn\View\Composers\Concerns\AcfFields;

function getAcfGroup($fields, $groupName, $default = [])
{
    return $fields[$groupName] ?? $default;
}

function toCamelCase($str): string
{
    return lcfirst(str_replace(' ', '', ucwords(str_replace(['-', '_'], [' ', ' '], $str))));
}

function formatCoworker($coworker)
{
    if (empty($coworker->meta['image'])) {
        return false;
    }

    if (!empty($coworker->meta['image'])) {
        $prekWeb                        = PrekWeb::getInstance();
        $srcTmp                         = $coworker->meta['image']->url;
        $src_sm                         = $prekWeb->image->processImage($srcTmp, 290, 392, 90);
        $src                            = $prekWeb->image->processImage($srcTmp, 455, 615, 90);
        $srcset                         = $prekWeb->image->processImageSrcset($srcTmp, 400, 455, 540, 615, 3, 90);
        $coworker->meta['image']->src    = $src;
        $coworker->meta['image']->src_sm = $src_sm;
        $coworker->meta['image']->srcset = $srcset;
    }
    return $coworker;
}
function formatImage($imageUrl, $imageSettings): array
{
    if (empty($imageSettings['w']) || empty($imageSettings['h'])) {
        return [];
    }
    $prekWeb   = PrekWeb::getInstance();
    $maxWidth  = $imageSettings['w'];
    $maxHeight = $imageSettings['h'];
    $ratio     = !empty($imageSettings['r']) ? $imageSettings['r'] : ($maxWidth / $maxHeight);
    $minWidth  = 414;
    $minHeight = round($minWidth / $ratio);
    $quality   = !empty($imageSettings['q']) ?: 70;
    $src       = $prekWeb->image->processImage($imageUrl, $maxWidth, $maxHeight, $quality);
    $srcset    = $prekWeb->image->processImageSrcset($imageUrl, $minWidth, $maxWidth, $minHeight, $maxHeight, quality: $quality);
    $srcXs     = $prekWeb->image->processImage($imageUrl, $minWidth, $minHeight, $quality);
    return [
        'src'    => $src,
        'srcset' => $srcset,
        'src_xs' => $srcXs,
    ];
}
function formatImages($images, $imageSettings): array
{
    $tmpImages    = [
        'total'  => 0,
        'images' => []
    ];
    $noOfPotraits = 0;
    $single       = true;
    foreach ($images as $key => $image) {
        $portraitType = '';
        if ($image->height > $image->width) {
            $orientation = 'portrait';
            $noOfPotraits++;
            $width = round($imageSettings['w'] / 2);
        } else {
            $orientation = 'landscape';
            $width       = $imageSettings['w'];
        }
        $ratio     = $image->height / $image->width;
        $height    = round($width * $ratio);
        $minWidth  = 414;
        $minHeight = round($minWidth * $ratio);

        $imageClass = [
            $orientation,
        ];

        if ($orientation === 'landscape') {
            if ($noOfPotraits > 0 && ($noOfPotraits % 2 !== 0)) {
                //previous portrait was single
                $lastKey = $key;
                --$lastKey;
                if (isset($tmpImages['images'][$lastKey])) {
                    $tmpImages['images'][$lastKey]['class'][] = 'single';
                    $tmpImages['images'][$lastKey]['single']  = true;
                }
            }
            //reset counter
            $noOfPotraits = 0;
        } elseif ($orientation === 'portrait') {
            if (($noOfPotraits > 0)) {
                if ($noOfPotraits % 2 === 0) {
                    $portraitType = 'last';
                    $imageClass[] = $portraitType;
                    // Second portrait. Use same size as previous image because height will differ otherwise.
                    $width  = $lastWidth;
                    $height = $lastHeight;
                    $single = false;
                } else {
                    $portraitType = 'first';
                    $imageClass[] = $portraitType;
                    $single       = false;
                    if (end($images) == $image) {
                        //This is last image AND portrait
                        $imageClass[] = 'single';
                        $single       = true;
                    }
                }
            }
        }
        $search                = 'strict';
        $replace               = in_array($image->category, ['Planlösningar']) ? 'inside' : 'strict';
        $tmpImage              = [
            'src'          => str_replace($search, $replace, Image::processImage($image->path, $imageSettings)),
            'srcset'       => str_replace($search, $replace, Fasad::getFasadSrcset($image->path, $minWidth, $width, $minHeight, $height, 5)),
            'width'        => $width,
            'height'       => $height,
            'width_orig'   => $image->width,
            'height_orig'  => $image->height,
            'src_orig'     => $image->path,
            'text'         => !empty($image->text) ? $image->text : '',
            'class'        => $imageClass,
            'type'         => 'image',
            'orientation'  => $orientation,
            'portraitType' => $portraitType,
            'single'       => $single,
        ];
        $tmpImages['images'][] = $tmpImage;
        $tmpImages['total']++;
        $lastWidth  = $width;
        $lastHeight = $height;
    }
    return $tmpImages;
}

function formatFact($fact, $value, &$return)
{
    if (in_array($fact, ['areas', 'energy', 'totalOperatingCosts', 'association'])) {
        foreach ($value as $key => $item) {
            $compositeKey = $fact . '_' . $key;
            if (!isset($return[$compositeKey])) {
                $return[$compositeKey] = [
                    'label' => $item['label'],
                    'value' => $item['value'],
                ];
            }
        }
    }
}

function attributesToString($attributes, $glue = ' ', $strtolower = true, $asData = false)
{
    $attributesStr = '';
    if (is_array($attributes) && ! empty($attributes)):
        foreach ($attributes as $attribute => $value):
            $value = is_array($value) ? implode($glue, $value) : $value;
            $value = $strtolower ? mb_strtolower($value) : $value;
            $attributesStr .= $asData ? " data-" : " ";
            $attributesStr .= "$attribute=\"$value\"";
        endforeach;
    endif;

    return $attributesStr;
}

function getPrivacyPolicyPage() {
    $page = false;
    $policyPageID = (int) get_option( 'wp_page_for_privacy_policy' );

    if ( ! empty( $policyPageID ) && get_post_status( $policyPageID ) === 'publish' ) {
        $page = get_post($policyPageID);
    }
    return $page;
}

function getAttribute(string $attr, $data) {
    foreach (explode(".", $attr) as $index => $key) {
        if (is_object($data) && property_exists($data, $key)) {
            $data = $data->{$key};
        } elseif(is_array($data) && isset($data[$key])){
            $data = $data[$key];
        } else {
            $data = '';
            break;
        }
    }
    return $data;
}

function attributeLoop($keys, $data)
{
    foreach ($keys as $key) {
        if (is_object($data) && property_exists($data, $key)) {
            $data = $data->{$key};
        } elseif(is_array($data) && isset($data[$key])){
            $data = $data[$key];
        } else {
            $data = '';
            break;
        }
    }
    return $data;
}