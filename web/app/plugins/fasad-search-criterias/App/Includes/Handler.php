<?php

namespace FasadSearchCriterias\Includes;

use FasadSearchCriterias\FasadSearchCriterias;

class Handler
{
    protected $loader;
    protected string $textDomain;

    public function __construct(Loader $loader, string $textDomain)
    {
        $this->loader = $loader;
        $this->textDomain = $textDomain;
    }

    public function run()
    {
        $this->loader->addAction("init", $this, "registerCustomPostTypes");
    }

    public function registerCustomPostTypes()
    {
        // Object Types Post Type
        $args = [
            "labels"        => ["name" => __("Sök objekttyper", $this->textDomain)],
            "description"   => __("Objekttyper från Fasad", $this->textDomain),
            "public"        => false,
            "show_ui"       => true,
            "rewrite"       => false,
            "has_archive"   => false,
            "menu_icon"     => plugins_url('', __DIR__) . "/assets/img/fasad-icon-16.png"
        ];
        register_post_type(
            FasadSearchCriterias::FASAD_SEARCH_OBJECT_TYPE_POST_TYPE,
            apply_filters('fasad_search_criterias_register_posttype', $args, FasadSearchCriterias::FASAD_SEARCH_OBJECT_TYPE_POST_TYPE)
        );

        // Search Criteria Post Type
        $args = [
            "labels"        => ["name" => __("Sökkriterier", $this->textDomain)],
            "description"   => __("Sökkriterier från Fasad", $this->textDomain),
            "public"        => false,
            "show_ui"       => true,
            "rewrite"       => false,
            "has_archive"   => false,
            "menu_icon"     => plugins_url('', __DIR__) . "/assets/img/fasad-icon-16.png"
        ];
        register_post_type(
            FasadSearchCriterias::FASAD_SEARCH_CRITERIA_POST_TYPE,
            apply_filters('fasad_search_criterias_register_posttype', $args, FasadSearchCriterias::FASAD_SEARCH_CRITERIA_POST_TYPE)
        );

        // Search Districts Post Type
        $args = [
            "labels"        => ["name" => __("Sökdistrikt", $this->textDomain)],
            "description"   => __("Sökdistrikt från Fasad", $this->textDomain),
            "public"        => false,
            "show_ui"       => true,
            "rewrite"       => false,
            "has_archive"   => false,
            "menu_icon"     => plugins_url('', __DIR__) . "/assets/img/fasad-icon-16.png"
        ];
        register_post_type(
            FasadSearchCriterias::FASAD_SEARCH_DISTRICT_POST_TYPE,
            apply_filters('fasad_search_criterias_register_posttype', $args, FasadSearchCriterias::FASAD_SEARCH_DISTRICT_POST_TYPE)
        );
    }
}
