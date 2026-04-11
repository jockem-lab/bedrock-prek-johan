<?php
/*
 * Inline component, no view associated
 */

namespace App\View\Components;

use Roots\Acorn\View\Component;

class Video extends Component
{

    public $sources;
    public $poster;
    public $videoAttributes;

    public function __construct($sources, $poster, $videoAttributes = '')
    {
        $tmpSources   = [];
        $allowedSizes = [
            'default' => 'default',
            'xs'      => 414,
            'sm'      => 640,
            'md'      => 768,
            'lg'      => 1024,
            'xl'      => 1280,
            '2xl'     => 1536,
        ];
        /*
         * Sources is an array of breakpoint => src
         * if default is '', no video will be used in default, to disable video in breakpoint and up use ''
         * example:
         *
         * $sources = [
         *      'xs' => '/path/to/src'
         * ];
         * video will be visible from xs and up
         * $sources = [
         *      'default' => '/path/to/src',
         *      'xs' => ''
         * ];
         * video will be visible to xs
         * $sources = [
         *      'xs' => '/path/to/src/for/xs',
         *      'sm' => '/path/to/src/for/sm',
         *      'lg' => '',
         * ];
         * video for xs will be visible from xs and up (no video before xs)
         * video for sm will be visible from sm and md (no video after lg)
         */


        foreach ($allowedSizes as $size => $pixel) {
            if (array_key_exists($size, $sources)) {
                $tmpSources[$pixel] = $sources[$size];
            }
        }
        $this->sources         = $tmpSources;
        $this->poster          = $poster;
        $this->videoAttributes = $videoAttributes;
    }

    public function render(): string
    {
        return <<<'blade'
            <div class="vue-component">
                <vuevideo poster="{{ $poster }}" :sources="{{ json_encode($sources) }}" attributes="{{ $videoAttributes }}"/>
            </div>
        blade;
    }
}