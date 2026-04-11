<?php
namespace app\View\Components\Layouts;

use App\View\Components\PrekComponent;

use function App\formatImage;

class Text extends PrekComponent
{
    public $data;
    public $partial = 'text';

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
//        $this->als = $this->getAdvancedLayoutSettings();
        $image               = [];
        $textContainerClass  = [
            'order-2',
            'col-span-12',
            'flex',
            'flex-col',
        ];
        $imageContainerClass = [
            'order-1',
            'col-span-12'
        ];
        $this->data['has'] = [
            'image' => false,
            'content' => false,
        ];
        if (!empty($this->data['image'])) {
            $tmpImage                   = $this->data['image'];
            $ratio                      = $tmpImage['width'] / $tmpImage['height'];
            $width                      = min($tmpImage['width'], 600);
            $height                     = round($width / $ratio);
            $image                      = formatImage(wp_get_attachment_image_url($tmpImage['ID'], 'full'), ['w' => $width, 'h' => $height, 'r' => $ratio]);
            $image['alt']               = $tmpImage['alt'];
            $image['org']               = $tmpImage;
            $this->data['has']['image'] = true;
        }
        $this->data['image'] = $image;
        if(!empty($this->data['content']) || !empty($this->data['heading']) || !empty($this->data['links'])){
            $this->data['has']['content'] = true;
        }
        if ($this->data['has']['content'] && $this->data['has']['image']) {

            $isLandscapeImage = $this->data['image']['org']['width'] > $this->data['image']['org']['height'];
            if ($isLandscapeImage && $this->data['width'] === 'max-w-xl') {
                //Landscape and not full width
                $textContainerClass[]  = 'sm:col-span-5';
                $imageContainerClass[] = 'sm:col-span-7';
            } else {
                //Portrait
                $textContainerClass[]  = 'sm:col-span-6';
                $imageContainerClass[] = 'sm:col-span-6';
            }
        }
        if($this->data['imageposition'] === 'right'){
            $imageContainerClass[] = 'sm:order-2';
            $textContainerClass[] = 'sm:order-1';
        }
        $this->componentClass(
            [
                'textContainer'  => $textContainerClass,
                'imageContainer' => $imageContainerClass
            ]
        );
        $this->componentAttributes();
    }
}
