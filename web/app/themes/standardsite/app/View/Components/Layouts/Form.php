<?php
namespace app\View\Components\Layouts;

use App\View\Components\PrekComponent;

class Form extends PrekComponent
{
    public $data;
    public $partial = 'form';

    protected $classes = [
        'wrapper'   => [
            'wrapper',
        ],
        'container' => [
            'container',
        ],
        'inner'     => [
        ],
    ];

    public function __construct($data)
    {
        /*
         * todo:
         * here we might be able to check individual settings on form later on to
         * decide if it should submit to crm or not, defaults to true right now
         */
        $this->data = array_merge($data, [
            'slug' => '',
            'isCorporationForm' => true,
        ]);
        parent::__construct($data);
        if(!empty($this->data['form'])){
            $this->data['slug'] = $this->data['form']->post_name;
        }
        $this->componentClass();
        $this->componentAttributes();
    }
}
