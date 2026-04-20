<?php

namespace FasadSearchCriterias\Includes;

use FasadSearchCriterias\FasadSearchCriterias;

class Repository
{
    protected $loader;
    protected $metaPrefix;

    public function __construct(Loader $loader, string $metaPrefix)
    {
        $this->loader = $loader;
        $this->metaPrefix = $metaPrefix;
    }

    public function selectAll(string $postType = '')
    {
        $postTypes = [
            FasadSearchCriterias::FASAD_SEARCH_OBJECT_TYPE_POST_TYPE,
            FasadSearchCriterias::FASAD_SEARCH_CRITERIA_POST_TYPE,
            FasadSearchCriterias::FASAD_SEARCH_DISTRICT_POST_TYPE
        ];
        $fieldNames = \FasadSearchCriterias\FasadSearchCriterias::fieldNames();
        if ($postType) {
            if (in_array($postType, $postTypes)) {
                $postTypes = [$postType];
            } else {
                return [];
            }
        }
        $return = array_fill_keys($fieldNames, []);

        foreach ($postTypes as $postType) {
            $return[$fieldNames[$postType]] = $this->select($postType);
        }

        return apply_filters('fasad_search_criterias_fields', $return);
    }

    public function select(string $postType)
    {
        $fieldNames = FasadSearchCriterias::fieldNames();
        $args = [
            'post_type'      => $postType,
            'posts_per_page' => -1,
            'post_status'    => 'any',
            'orderby'        => 'menu_order',
            'order'          => 'ASC'
        ];
        $posts = get_posts($args);

        return array_map(function ($post) use ($postType, $fieldNames) {
            $data = [
                'name'    => $fieldNames[$postType] . '[]',
                'value'   => get_post_meta($post->ID, $this->metaPrefix . 'id', true),
                'label'   => $post->post_title,
                'element' => 'checkbox'
            ];

            if ($postType === FasadSearchCriterias::FASAD_SEARCH_CRITERIA_POST_TYPE) {
                $data['isrequired'] = get_post_meta($post->ID, $this->metaPrefix . 'isrequired', true);
            } elseif ($postType === FasadSearchCriterias::FASAD_SEARCH_DISTRICT_POST_TYPE) {
                $data['polygon'] = get_post_meta($post->ID, $this->metaPrefix . 'polygon', true);
            }

            return $data;
        }, $posts);
    }

}
