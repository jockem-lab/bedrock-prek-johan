<?php

namespace PrekWeb\Includes;

class Image
{

    protected $loader;
    protected $options;
    protected $processServer;
    protected $CDNUrl;
    protected $queryVar = 'redirectProcessImage';
    //                           server                    corp      office    object    image     size      width     height     quality      mode         intermediate
    protected $pattern = '((?:archived-)?images(?:[0-9]+))\/([0-9]+)\/([0-9]+)\/([0-9]+)\/([0-9]+)\/([a-z]+)\/([0-9]+)\/([0-9]+)(\/([0-9]+))?(\/([a-z]+))?(\/(0|1))?\.(?:jpe?g|png|gif)$';

    public function __construct(\PrekWeb\Includes\Loader $loader, \PrekWeb\Includes\Options $options)
    {
        $this->loader  = $loader;
        $this->options = $options;
        if (\WP_ENV === 'development') {
            $this->processServer = 'http://dev-process.fasad.prek.srv/';
            $this->CDNUrl = 'http://dev-process.fasad.prek.srv/';
        } else {
            $this->processServer = 'https://process.fasad.eu/';
            $this->CDNUrl = 'https://cdn.fasad.eu/';
        }
    }

    public function run()
    {
        $this->loader->addAction('init', $this, 'addRewrites');
        $this->loader->addAction('parse_query', $this, 'redirectImages');
        $this->loader->addFilter('query_vars', $this, 'addQueryVars');
        $this->loader->addFilter('fasad_process_image', $this, 'rewriteImages');
        $this->loader->addFilter('fasad_process_image', $this, 'filterProcessImage');
    }

    public function addRewrites()
    {
        add_rewrite_rule($this->pattern, 'index.php?redirectProcessImage=1', 'top');
    }

    public function addQueryVars($vars)
    {
        $vars[] = $this->queryVar;
        return $vars;
    }

    public function redirectImages()
    {
        try {
            $redirect = intval(get_query_var($this->queryVar, 0));
        } catch (\Throwable $e) {
            $redirect = 0;
        }
        if ($redirect === 1) {
            $path = preg_replace('/^\//', '', $_SERVER['REQUEST_URI']);
            preg_match('/'.$this->pattern.'/', $path, $parts);
            preg_match('/\.(jpe?g|png|gif)$/', $parts[0], $ext);

            $server       = $parts[1];
            $corporation  = $parts[2];
            $office       = $parts[3];
            $object       = $parts[4];
            $image        = $parts[5];
            $size         = $parts[6];
            $width        = $parts[7];
            $height       = $parts[8];
            $quality      = !empty($parts[10]) ? $parts[10] : 70;
            $mode         = !empty($parts[12]) ? $parts[12] : 'strict';
            $intermediate = !empty($parts[14]) ? $parts[14] : 1;

            $url  = 'https://' . $server . '.fasad.eu/' . $corporation . '/' . $office . '/' . $object . '/' . $size . '/' . $image . '.' . $ext[1];
            $dest = (apply_filters('CDN_is_active', false)) ? $this->CDNUrl : $this->processServer;
            $dest .= 'rimage.php?url=' .
                urlencode($url) . '&i=' . $intermediate . '&m=' . $mode . '&w=' . $width . '&h=' . $height . '&c=' . $quality;

            wp_redirect($dest, 301);
            die();
        }
    }

    /**
     * @param object|string $image
     * @return string
     */
    public function rewriteImages($image)
    {
        if (!apply_filters('PrettyImages_is_active', false)) {
            return $image;
        }

        if (is_object($image)) {
            if (strpos($image->path, 'fasad.eu') === false) {
                return $image;
            }
            $imageParts = $this->parseFasadImage($image->path, $image->width, $image->height);
        } elseif (strpos($image, 'process.fasad.eu') || strpos($image, 'cdn.fasad.eu')) {
            $processParts = parse_url($image);
            parse_str($processParts['query'], $params);
            $imageParts = $this->parseFasadImage($params['url'], $params['w'], $params['h']);
            $imageParts = array_merge($imageParts, [
                'quality'      => $params['c'],
                'mode'         => $params['m'],
                'intermediate' => $params['i']
            ]);
        } else {
            return $image;
        }

        $extension = $imageParts['extension'];
        unset($imageParts['extension']);

        $image = '/' . implode('/', $imageParts) . '.' . $extension;

        return $image;
    }

    /**
     * @param $path   string https://images05.fasad.eu/691/400480/1384205/highres/12804141.jpg
     * @return array
     */
    private function parseFasadImage($path, $width, $height)
    {
        if (strpos($path, 'http') === false) {
            $path = 'https://' . $path;
        }
        $imageParts = parse_url($path);
        $path       = preg_replace('/^\//', '', $imageParts['path']);
        $pathParts  = explode('/', $path);
        $imageFile  = explode('.', $pathParts[4]);

        return [
            'server'      => explode('.', $imageParts['host'])[0],
            'corporation' => $pathParts[0],
            'office'      => $pathParts[1],
            'object'      => $pathParts[2],
            'imageFile'   => $imageFile[0],
            'size'        => $pathParts[3],
            'width'       => round($width),
            'height'      => round($height),
            'extension'   => $imageFile[1]
        ];
    }

    /**
     * @param object|string $image
     * @return string
     */
    public function filterProcessImage($image)
    {
        if (apply_filters('CDN_is_active', false)) {
            $image = $this->setImageUrlCDN($image);
        }
        return $image;
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
        if (class_exists('\PrekWebHelper\Includes\Image')) {
            $helper = \PrekWebHelper\PrekWebHelper::getInstance();
            return $helper->image->processImage($path, $width, $height, $quality, $sharpen);
        }

        if (strpos($path, 'http') === false) {
            $path = home_url() . $path;
        }

        if (function_exists('wp_get_environment_type') && wp_get_environment_type() === 'staging' && env('HTACCESS_USER') && env('HTACCESS_PASS')) {
            $path = str_replace('http://', 'http://' . env('HTACCESS_USER') . ':' . env('HTACCESS_PASS') . '@', $path);
        }

        $path = $this->processServer . sprintf('rimage.php?b=0&w=%d&h=%d&m=strict&r=0&s=0&i=1&c=%d&u=%d&t=&url=%s',
                                               round($width), round($height), $quality, $sharpen, $path);

        // Set filter in theme if images should be fetched with CDN.
        if (apply_filters('CDN_is_active', false)) {
            $path = $this->setImageUrlCDN($path);
        }

        return apply_filters('prekweb_resize_url', $path);
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
        if (class_exists('\PrekWebHelper\Includes\Image')) {
            $helper = \PrekWebHelper\PrekWebHelper::getInstance();
            return $helper->image->processImageSrcset($path, $minWidth, $maxWidth, $minHeight, $maxHeight, $nrOfSizes, $quality, $sharpen);
        }

        $stepWidthSize  = ($maxWidth - $minWidth) / ($nrOfSizes - 1);
        $stepHeightSize = ($maxHeight - $minHeight) / ($nrOfSizes - 1);
        $srcset = [];

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
        if (class_exists('\PrekWebHelper\Includes\Image')) {
            $helper = \PrekWebHelper\PrekWebHelper::getInstance();
            return $helper->image->processAttributes($path, $minWidth, $maxWidth, $minHeight, $maxHeight, $nrOfSizes, $quality, $sharpen);
        }

        $src    = $this->processImage($path, $maxWidth, $maxHeight, $quality, $sharpen);
        $srcset = $this->processImageSrcset($path, $minWidth, $maxWidth, $minHeight, $maxHeight, $nrOfSizes, $quality, $sharpen);
        return ' src="'.$src.'" srcset="'.$srcset.'" ';
    }

    /**
     * Function for getting images through CDN
     *
     * @param string $url
     * @return string
     */
    public function setImageUrlCDN(string $url) : string
    {
        if ((isset($_GET['cdn']) && $_GET['cdn'] === '0') || strpos($url, $this->processServer) === false) {
            return $url;
        }
        // Change i=0 to i=1 to avoid redirects to cache (less overhead for server)
        $url = str_replace(['&i=0', '&amp;i=0', '?i=0'], ['&i=1', '&amp;i=1', '?i=1'], $url);
        if (str_replace(['&i=1', '&amp;i=1', '?i=1'], '', $url) == $url){
            // i=1 is not present in any form, let's add it
            if (strpos($url, '&amp;')) {
                $url .= '&amp;i=1';
            } else {
                $url .= '&i=1';
            }
        }
        return str_replace($this->processServer, $this->CDNUrl, $url);
    }
}
