<?php

namespace App\View\Composers;

use PrekWeb\Includes\Fasad;
use Roots\Acorn\View\Composer;

//use function App\format_images;


abstract class PrekComposer extends Composer
{
    protected static $views = [
        '*'
    ];
//    protected PrekWeb $prekWeb;
    protected $listing;
    protected $corporationID;

    protected static $prekData = null;

    public function __construct()
    {
        $this->setPrekData();
    }

    public function with()
    {
        return [
            'listing' => $this->listing,
            'corporationID' => $this->corporationID,
        ];
    }

    private function setPrekData()
    {
        if (is_null(self::$prekData)) {
            $prekData = [];

            //Set listingdata
            $listing = class_exists('PrekWeb\\Includes\\Fasad') ? \PrekWeb\Includes\Fasad::expandObject() : false;
            if (empty($listing) || !is_object($listing) || $listing->minilist || is_tax()) {
                $listing = false;
            }
            $prekData['listing'] = $listing;

            //Set corporationdata
            $prekData['corporationID'] = App::getOption('corporation_id', 0);

            self::$prekData = $prekData;
        }

        $this->listing       = self::$prekData['listing'] ?? false;
        $this->corporationID = self::$prekData['corporationID'] ?? 0;
    }
}
