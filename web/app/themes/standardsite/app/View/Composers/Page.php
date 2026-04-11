<?php

namespace App\View\Composers;

use PrekWeb\Includes\Fasad;

//use App\View\Components\{Usp, Alert, Quote, Pagedata, LatestListings};

//use App\View\Components\Usp;
//use Roots\Acorn\View\Composer;
use Roots\Acorn\View\Composers\Concerns\AcfFields;

use function App\toCamelCase;
use function App\getAcfGroup;

class Page extends PrekComposer
{
    use AcfFields;

    protected static $views = [
        'page',
    ];
    /**
     * List of views served by this composer.
     *
     * @var array
     */
    private $ns = "\\App\\View\\Components\\Layouts\\";

    public function with()
    {
        $layouts = [];
        $fields  = getAcfGroup($this->fields(), 'flexible_content');
        if ($fields) {
            $layouts = collect($fields)->toArray();
            foreach ($layouts as $key => &$layout) {
                if (!isset($layout['acf_fc_layout'])) {
                    unset($layouts[$key]);
                    continue;
                }
                $component = $this->ns . ucfirst(toCamelCase($layout['acf_fc_layout']));
                if (!class_exists($component)) {
                    unset($layouts[$key]);
                    continue;
                }
                $layout = new $component($layout);
            }
        }
        return [
            'layouts' => $layouts
        ];
    }
}
