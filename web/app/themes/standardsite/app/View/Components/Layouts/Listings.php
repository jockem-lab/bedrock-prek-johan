<?php

namespace App\View\Components\Layouts;

use App\View\Components\PrekComponent;

use function App\getAttribute;

class Listings extends PrekComponent
{
    public $data;
    public $partial = 'listings';

    protected $classes = [
        'wrapper' => [
            'wrapper'
        ],
        'container' => [
            'container',
//            'pt-2',
//            'pb-4',
//            'pt-md-0'
        ],
        'inner'     => [
            'vue-component',
        ],
    ];
    public function __construct($data)
    {
        $this->data = $data;
        parent::__construct($data);
        $attr = getAttribute('listings_sold', $this->data) ?: 'all';
        $publishTypes = [
            'all' => -1,
            'forsale' => 0,
            'sold' => 1
        ];
        $this->data['listings_sold'] = array_key_exists($attr, $publishTypes) ? $publishTypes[$attr] : -1;
//        $this->data['listings_sold'] = -1;
//        $this->data['listingscount'] = -1;
//        $this->data['upcoming'] = 0;
//        $this->data['newproduction'] = 0;
//        $this->data['sold'] = 0;
//        if (isset($this->data['show_objects']) && $this->data['show_objects']) {
//            $this->data[$this->data['show_objects']] = 1;
//        }
//
//        if (isset($this->data['placeholder']) && !$this->data['placeholder']) {
//            $this->data['placeholder'] = 'Det finns inga objekt här, prova en annan sökning.';
//        }

        $this->componentClass();
        $this->componentAttributes();
    }
}
