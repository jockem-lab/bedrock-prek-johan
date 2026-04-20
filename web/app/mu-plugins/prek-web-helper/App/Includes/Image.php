<?php

namespace PrekWebHelper\Includes;

class Image
{

    protected $loader;
    protected $processServer;

    public function __construct(\PrekWebHelper\Includes\Loader $loader)
    {
        $this->loader = $loader;
        $this->processServer = 'https://process.fasad.eu/';
    }

    /**
     * Takes an image path and prepends it with process server and width/height arguments
     *
     * @param string $path http://HOST/app/uploads/2020/06/hd-bjorngardsgatan14-11051615.jpg
     * @param int $width
     * @param int $height
     * @param int $quality
     * @param int $sharpen 0-10
     * @return string
     */
    public function processImage($path, $width = 1920, $height = 1280, $quality = 70, $sharpen = 0)
    {
        if (strpos($path, 'http') === false) {
            $path = home_url() . $path;
        }
        if (function_exists('wp_get_environment_type') && wp_get_environment_type() === 'staging' && Helpers::getEnv('HTACCESS_USER') && Helpers::getEnv('HTACCESS_PASS')) {
            $path = str_replace('http://', 'http://' . Helpers::getEnv('HTACCESS_USER') . ':' . Helpers::getEnv('HTACCESS_PASS') . '@', $path);
        }
        $args = [
            round($width),
            round($height),
            $quality,
            $sharpen,
            $path,
            apply_filters('process_use_webp', false) ? 1 : 0,
        ];
        $path = $this->processServer . vsprintf('rimage.php?b=0&w=%d&h=%d&m=strict&r=0&s=0&i=1&c=%d&u=%d&t=&url=%s&webp=%s',
                                                $args);
        return apply_filters('prek_web_helper_resize_url', $path);
    }

    /**
     * @param $path
     * @param int $minWidth
     * @param int $maxWidth
     * @param int $minHeight
     * @param int $maxHeight
     * @param int $nrOfSizes
     * @param int $quality
     * @param int $sharpen 0-10
     * @return string
     */
    public function processImageSrcset($path, $minWidth = 300, $maxWidth = 1920, $minHeight = 300, $maxHeight = 1280, $nrOfSizes = 2, $quality = 70, $sharpen = 0)
    {
        $stepWidthSize  = floor(($maxWidth - $minWidth) / ($nrOfSizes - 1));
        $stepHeightSize = floor(($maxHeight - $minHeight) / ($nrOfSizes - 1));

        $widths  = range($maxWidth, $minWidth, $stepWidthSize);
        $heights = range($maxHeight, $minHeight, $stepHeightSize);

        for ($i = 0; $i < $nrOfSizes; $i++) {
            $srcset[] = $this->processImage($path, round($widths[$i]), round($heights[$i]), $quality, $sharpen) . " " . round($widths[$i]) . "w";
        }
        $srcset = implode(", ", $srcset);

        return $srcset;
    }

    /**
     * Outputs "src" and "srcset" attributes for an img tag
     * Usage: <img {!! $prek_web->image->processAttributes($puff->image, 210, 500, 210, 500, 3) !!} alt="" class="img">
     *
     * @param $path
     * @param int $minWidth
     * @param int $maxWidth
     * @param int $minHeight
     * @param int $maxHeight
     * @param int $nrOfSizes
     * @param int $quality
     * @param int $sharpen
     * @return string
     */
    public function processAttributes($path, $minWidth = 300, $maxWidth = 1920, $minHeight = 300, $maxHeight = 1280, $nrOfSizes = 2, $quality = 70, $sharpen = 0)
    {
        $src    = $this->processImage($path, $maxWidth, $maxHeight, $quality, $sharpen);
        $srcset = $this->processImageSrcset($path, $minWidth, $maxWidth, $minHeight, $maxHeight, $nrOfSizes, $quality, $sharpen);
        return ' src="'.$src.'" srcset="'.$srcset.'" ';
    }

}
