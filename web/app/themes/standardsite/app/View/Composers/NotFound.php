<?php

namespace App\View\Composers;

class NotFound extends PrekComposer
{
    protected static $views = [
        '404'
    ];

    public function with()
    {
        return [
            'isListing'    => class_exists('FasadBridge\\Includes\\PublicSettings') ? get_query_var(\FasadBridge\Includes\PublicSettings::FASAD_LISTING_POST_TYPE) : false,
            'listingsPage' => App::getListingsPage(),
        ];
    }


}
