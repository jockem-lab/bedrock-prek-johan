<?php

namespace App\View\Composers;

use Roots\Acorn\View\Composers\Concerns\AcfFields;
use PrekWebHelper\PrekWebHelper;

use function App\attributesToString;
use function App\getAcfGroup;

class Hero extends PrekComposer
{
    use AcfFields;

    private $heroGroup;
    protected static $views = [
        'partials.hero',
    ];

    public function with()
    {
        // Hoppa över hero på objektdetaljsidor
        if (get_query_var('fasad_listing') && get_query_var('fasad_listing') !== '1') {
            return ['hero' => null];
        }

        $hero = [
            'slides' => [],
            'video' => [],
        ];
        $slides = [];
        $this->heroGroup = getAcfGroup($this->fields(), 'hero_group');
        if (getAcfGroup($this->heroGroup, 'hero_type', '') === 'slides') {
            $slides = $this->getSlides();
        } elseif (getAcfGroup($this->heroGroup, 'hero_type', '') === 'video') {
            $video = $this->getVideo();
        }
        if(!empty($slides)){
            $hero['slides'] = $slides;
        }
        if(!empty($video)){
            $hero['video'] = $video;
        }
        // Fallback: om inga slides konfigurerade, använd Unsplash-bilder
        if (empty($hero['slides']) && empty($hero['video'])) {
            $hero['slides'] = [
                ['image' => ['src' => 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=1600', 'srcset' => '', 'attributes' => 'class="slide-image"'], 'title' => '', 'subtitle' => ''],
                ['image' => ['src' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=1600', 'srcset' => '', 'attributes' => 'class="slide-image"'], 'title' => '', 'subtitle' => ''],
                ['image' => ['src' => 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=1600', 'srcset' => '', 'attributes' => 'class="slide-image"'], 'title' => '', 'subtitle' => ''],
            ];
        }
        return [
            'hero' => $hero,
        ];
    }

    private function getVideo()
    {
        $heroFilm = getAcfGroup($this->heroGroup, 'hero_video');
        $src = !empty($heroFilm['hero_video_file']) ? $heroFilm['hero_video_file']['url'] : '';
        $srcmobile = !empty($heroFilm['hero_video_file_mobile']) ? $heroFilm['hero_video_file_mobile']['url'] : '';
        return [
            'sources' => [
                'default' => $srcmobile,
                'md' => $src,
            ],
            'poster' => wp_get_attachment_image_url($heroFilm['hero_video_poster']['ID'], 'full'),
        ];
    }

    private function getSlides()
    {
        $slides = [];
        $heroSlides = getAcfGroup($this->heroGroup, 'hero_slides');
        if(is_array($heroSlides)) {
            foreach ($heroSlides as $slide) {
                $imageGroup = $slide['hero_slides_image_group'];
                $class = [];
                if (empty($imageGroup) || empty($imageGroup['hero_slides_image'])) {
                    continue;
                }
                $objectPositions = [
                    'top'    => 'object-top',
                    'center' => 'object-center',
                    'bottom' => 'object-bottom',
                ];
                $class[] = $objectPositions[$imageGroup['object-position-horizontal']];
                $class[] = 'slide-image';
                $imageUrl = wp_get_attachment_image_url($imageGroup['hero_slides_image']['ID'], 'full');
                $slides[] = [
                    'image' => self::transformImage($imageUrl, $imageUrl, App::getSettings()['heroSettings'], ['class' => $class]),
                    'title' => $slide['title'],
                    'subtitle'  => $slide['subtitle'],
                ];
            }
        }
        return $slides;
    }

    public static function transformImage($src, $srcSmall, $imageSettings, $attributes = []) {
        $prekWebHelper = PrekWebHelper::getInstance();
        $image = [
            'attributes' => attributesToString(array_merge(['class' => '', 'loading' => 'lazy'], $attributes)),
            'src' => $prekWebHelper->image->processImage($src, $imageSettings['maxWidth'], $imageSettings['maxHeight'], $imageSettings['quality']),
            'srcset' => $prekWebHelper->image->processImageSrcset($src, $imageSettings['minWidth'], $imageSettings['maxWidth'], $imageSettings['minHeight'], $imageSettings['maxHeight'], $imageSettings['nrOfSizes'], $imageSettings['quality']),
            'srcSmall' => $prekWebHelper->image->processImage($srcSmall, $imageSettings['minWidth'], $imageSettings['minHeight'], $imageSettings['quality']),
//            'sizes' => '(min-width: 1100px) 992px, 100vw',
        ];
        return $image;
    }
}