<?php
namespace app\View\Components\Layouts;

use App\View\Components\PrekComponent;
use PrekWeb\Includes\Fasad;

use function App\formatCoworker;
use function App\getAttribute;

class Coworkers extends PrekComponent
{
    public $data;
    public $partial = 'coworkers';

    protected $classes = [
        'wrapper'   => [
            'wrapper',
            'mx-auto'
        ],
        'container' => [
            'container',
        ],
        'inner'     => [
        ],
    ];

    public function __construct($data)
    {
        $this->data          = $data;
        parent::__construct($data);
        $coworkers = [];
        $tmpCoworkers    = Fasad::getRealtors();
        foreach ($tmpCoworkers as $tmpCoworker) {
            if ($coworker = formatCoworker($tmpCoworker)) {
                $permalink = true ? '' : get_the_permalink($coworker->ID); //shortcircuit this, option i admin?
                $coworkers[] = (object)[
                    'name'            => getAttribute('meta.firstname', $coworker) . ' ' . getAttribute('meta.lastname', $coworker),
                    'phone'           => getAttribute('meta.phone', $coworker),
                    'phoneString'     => getAttribute('meta.phoneString', $coworker),
                    'cellphone'       => getAttribute('meta.cellphone', $coworker),
                    'cellphoneString' => getAttribute('meta.cellphoneString', $coworker),
                    'email'           => getAttribute('meta.email', $coworker),
                    'title'           => getAttribute('meta.title', $coworker),
                    'titleExtra'      => getAttribute('meta.titleExtra', $coworker),
                    'image'           => getAttribute('meta.image', $coworker),
                    'permalink'       => $permalink,
                ];
            }
        }
        $this->data['coworkers'] = $coworkers;
        $coworkerContainerClass = [
            'flex',
            'col-span-12',
            'sm:col-span-4',
        ];
        $this->componentClass([
            'coworkerContainer' => $coworkerContainerClass,
                              ]);
        $this->componentAttributes();
    }
}
