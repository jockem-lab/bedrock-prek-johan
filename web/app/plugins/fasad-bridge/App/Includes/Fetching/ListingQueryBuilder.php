<?php

namespace FasadBridge\Includes\Fetching;

use FasadBridge\Includes\PublicSettings;

/**
 * A simple query listing builder.
 *
 * Class ListingQueryBuilder
 *
 * @package FasadBridge\Includes\Fetching
 */
class ListingQueryBuilder
{
    private $postType;
    // Query variables
    private $queryArgs;
    private $taxArgs;
    private $metaArgs;

    private $postsPerPage = -1;
    private $orderBy;
    private $paged = 1;
    private $exclude = [];

    // Prefixes
    private $metaPrefix = "_fasad_";
    private $taxPrefix;

    public function __construct($type = PublicSettings::FASAD_LISTING_POST_TYPE)
    {
        if ($type === PublicSettings::FASAD_PROTECTED_POST_TYPE) {
            $this->postType = PublicSettings::FASAD_PROTECTED_POST_TYPE;
            $this->taxPrefix = PublicSettings::FASAD_LISTING_POST_TYPE . '_';
        } else {
            $this->postType = PublicSettings::FASAD_LISTING_POST_TYPE;
            $this->taxPrefix = PublicSettings::FASAD_LISTING_POST_TYPE . '_';
        }
    }

    public function postsPerPage($postsPerPage)
    {
        $this->postsPerPage = $postsPerPage;
        return $this;
    }

    public function metaNested($metaArr, $i)
    {
        if (!empty($metaArr)) {
            $key = 'nested_' . $i;
            $arr = [];
            foreach ($metaArr as $metaKey => $meta) {
                if ($metaKey === 'relation') {
                    $arr[$metaKey] = $meta;
                } else {
                    $arr[] = [
                        "key"     => $this->metaPrefix . $meta['key'],
                        "compare" => $meta['compare'] ?? '=',
                        "value"   => $meta['value']
                    ];
                }
            }
            $this->metaArgs[$key] = $arr;
        }

        return $this;
    }

    public function meta($key, $compare, $value, $type = null)
    {
        $this->metaArgs[$key] = [
            "key"     => $this->metaPrefix . $key,
            "compare" => $compare,
            "value"   => $value
        ];
        if (!is_null($type)) {
            $this->metaArgs[$key]['type'] = $type;
        }

        return $this;
    }

    public function tax($tax, $field, $terms, $operator = "IN")
    {
        $key = $this->makeTaxKey($terms);

        $this->taxArgs[$key] = [
            "taxonomy" => $this->taxPrefix . $tax,
            "field"    => $field,
            "terms"    => $terms,
            "operator" => $operator
        ];

        return $this;
    }

    private function makeTaxKey($terms)
    {
        $termsArray = is_array($terms) ? $terms : [$terms];
        $key = implode("-", $termsArray);

        return $key;
    }

    public function orderBy($key, $direction)
    {
        $this->orderBy[$key] = $direction;

        // Only for sorting, do not override existing meta query key.
        if (!isset($this->metaArgs[$key])) {
            $this->metaArgs[$key] = [
                'key'     => $this->metaPrefix . $key,
                'compare' => 'EXISTS',
            ];
        }

        return $this;
    }

    public function exclude(array $postIds)
    {
        $this->exclude = array_merge($this->exclude, $postIds);
        return $this;
    }

    public function paged($value)
    {
        $this->paged = $value;
        return $this;
    }

    public function getQuery(): \WP_Query
    {
        $this->queryArgs = [
            "post_type"      => $this->postType,
            "posts_per_page" => $this->postsPerPage,
            "meta_query"     => $this->metaArgs,
            "tax_query"      => $this->taxArgs,
            "orderby"        => $this->orderBy ?? [],
            "paged"          => $this->paged,
            "post__not_in"   => $this->exclude,
        ];

        $listQuery = new \WP_Query($this->queryArgs);
        return $listQuery;
    }
}