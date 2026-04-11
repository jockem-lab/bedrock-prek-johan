<?php

namespace App\View\Components;

use App\View\Composers\App;
use Roots\Acorn\View\Component;

use function App\attributesToString;
use function App\getAttribute;

abstract class PrekComponent extends Component
{
    /**
     * The alert type.
     *
     * @var string
     */
    public $data;
    protected $classes = [
        'wrapper'   => ['acf-component'],
        'container' => ['lg:container'],
        'inner'     => ['acf-component-inner'],
    ];
    /**
     * Create the component instance.
     *
     * @param array $data
     * @return void
     */
    public function __construct($data)
    {
        $this->setAdvancedLayoutSettings();
    }
    protected function componentClass($classes = [], $override = false)
    {
        $tmpClasses = [
            'fluid'     => [],
            'wrapper'   => [],
            'container' => [],
            'inner'     => [],
        ];
        $this->classes['wrapper'][] = $this->data['acf_fc_layout'];
        if (!empty($this->data['width'])) {
            $this->classes['wrapper'][] = 'mx-auto';
            $this->classes['wrapper'][] = $this->data['width'];
        }
        if (!empty($this->data['background'])) {
            $this->classes['fluid'][] = $this->data['background'] . '-background';
        }
        if (!empty($this->data['advanced_layout_settings']['visibility'])) {
            $visibility = $this->data['advanced_layout_settings']['visibility'];
            if ($visibility === 'desktop') {
                $this->classes['fluid'][] = 'hidden';
                $this->classes['fluid'][] = 'md:block';
            } elseif ($visibility === 'mobile') {
                $this->classes['fluid'][] = 'block';
                $this->classes['fluid'][] = 'md:hidden';
            }
        }
        if ($override) {
            //override classes, but append acf layout
            foreach ($tmpClasses as $key => $tmpClass) {
                if (array_key_exists($key, $classes)) {
                    $tmpClasses[$key] = $classes[$key];
                }
            }
            $tmpClasses['wrapper'][] = $this->data['acf_fc_layout'];
        } else {
            $this->mergeClasses($tmpClasses, $this->classes);
            $this->mergeClasses($tmpClasses, $classes);
        }
        foreach ($tmpClasses as $key => $item) {
            if (is_array($item)) {
                $this->data['class'][$key] = implode(' ', $item);
            }
        }
    }

    protected function setAdvancedLayoutSettings() {
        $tmpAls = getAttribute('als_group', $this->data);
        $als = [];
        if($tmpAls) {
            unset($this->data['als-show']);
            unset($this->data['als_group']);
        }
        foreach($tmpAls as $key => $item){
         $als[str_replace('als_', '', $key)] = $item;
        }
        $this->data['advanced_layout_settings'] = $als;
    }

    protected function componentAttributes($attributes = [])
    {
        $tmpAttributes = [
            'fluid'     => [],
            'wrapper'   => [],
            'container' => [],
            'inner'     => [],
        ];
        if (!empty($this->data['advanced_layout_settings']['id'])) {
            $tmpAttributes['wrapper']['id'] = $this->data['advanced_layout_settings']['id'];
        }
        if ($this->data['background'] === 'custom' && !empty($this->data['custom-colors'])) {
            $style = '';
            if (!empty($this->data['custom-colors']['background'])) {
                $style .= 'background-color: ' . $this->data['custom-colors']['background'] . ';' . PHP_EOL;
            }
            if (!empty($this->data['custom-colors']['color'])) {
                $style .= 'color: ' . $this->data['custom-colors']['color'] . ';' . PHP_EOL;
            }
            if ($style !== '') {
                $tmpAttributes['fluid']['style'] = $style;
            }
        }
        foreach ($tmpAttributes as $key => $item) {
            $this->data['attributes'][$key] = attributesToString(array_merge($item, ($attributes[$key] ?? [])));
        }
    }

    private function mergeClasses(&$classes, $classes2){
        foreach($classes2 as $key => $items){
            if(!isset($classes[$key])) {
                $classes[$key] = $items;
            }else{
                if(!is_array($items)){
                 $items = [$items];
                }
                foreach($items as $item){
                    $classes[$key][] = $item;
                }
            }
        }
    }

    public function render()
    {
        return $this->view('components.layouts.' . $this->partial, ['data' => $this->data]);
    }
}
