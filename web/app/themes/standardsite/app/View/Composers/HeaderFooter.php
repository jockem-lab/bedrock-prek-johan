<?php

namespace App\View\Composers;

use FasadBridge\Includes\PublicSettings;
use PrekWeb\PrekWeb;
use Roots\Acorn\View\Composer;

use function App\getAttribute;

class HeaderFooter extends PrekComposer
{
    protected static $views = [
        'sections.footer',
        'sections.header',
    ];
    protected $offices = null;

    private function getOffices()
    {
        if (!is_null($this->offices)) {
            return $this->offices;
        }
        if (!class_exists('PrekWeb\PrekWeb') || !class_exists('FasadBridge\Includes\PublicSettings')) {
            return [];
        }
        $prekWeb = PrekWeb::getInstance();
        //check if we have offices
        $officesPosts = get_posts(
            [
                'post_type'      => PublicSettings::FASAD_OFFICE_POST_TYPE,
                'posts_per_page' => 1,
            ]
        );
        if (empty($officesPosts)) {
            return [];
        }
        $this->offices = $prekWeb->fasad::getFasadOffices();
        return $this->offices;
    }

    private function officesInfo()
    {
        $offices      = $this->getOffices();
        $officesCount = count($offices);
        $officesInfo  = [];
        if ($offices) {
            foreach ($offices as $office) {
                $tmpOffice = [];
                if ($officesCount > 1 && !empty($office->meta['alias'])) {
                    $tmpOffice[] = $office->meta['alias'];
                }
                if (!empty($office->meta['address'])) {
                    $tmpOffice[] = $office->meta['address'];
                }
                if (!empty($office->meta['zipCode'])) {
                    $tmpOffice[] = $office->meta['zipCode'];
                }
                if (!empty($office->meta['city'])) {
                    $tmpOffice[] = $office->meta['city'];
                }
                if (!empty($tmpOffice)) {
                    $officesInfo[] = $tmpOffice;
                }
            }
        }
        return $officesInfo;
    }

    public function with()
    {
        $headerLogoOption        = App::getOption('logo');
        $headerGroupLogoSettings = App::getOption('group_logo_settings');
        $footerLogoOption        = App::getOption('logo_footer');
        $headerLogo              = isset($headerLogoOption['url']) ? $headerLogoOption : '';
        $headerLogoClass = [
            'h-full'
        ];
        if ($headerGroupLogoSettings && !empty($headerLogo)) {
            /*
             * Not used, left as poc for dynamic cssclasses, needs more work though
             * Tailwinds JIT needs classes to exist somewhere
             * @see FASADWEB-15
             *
             *   if (getAttribute('logo_cssclasses_overwrite', $headerGroupLogoSettings)) {
             *       $headerLogoClass = [];
             *   }
             *   $extraClasses = getAttribute('logo_cssclasses', $headerGroupLogoSettings);
             *   if (is_array($extraClasses)) {
             *       $headerLogoClass = array_merge($headerLogoClass, $extraClasses);
             *       $headerLogoClass[] = 'h-[calc(100%+20px)]';
             *   }
             */
            if ($extraHeight = getAttribute('logo_extra_height', $headerGroupLogoSettings)) {
                $headerLogo['style'] = 'height:calc(100% + ' . $extraHeight . 'px);';
            }
        }
        if (empty($headerLogo)) {
            //no header logo in wp, try to get (first) office logo
            $offices = $this->getOffices();
            if (count($offices) > 0) {
                if (isset($offices[0]->meta['images'])) {
                    foreach ($offices[0]->meta['images'] as $image) {
                        if ($image->category === 'Kontorslogo') {
                            $headerLogo        = [];
                            $headerLogo['url'] = $image->path;
                        }
                    }
                }
            }
        }

        $footerLogo = isset($footerLogoOption['url']) ? $footerLogoOption : $headerLogo;
        if(!empty($headerLogo)){
            $headerLogo['class'] = implode(' ', $headerLogoClass);
        }
        return [
            'logo'        => [
                'header' => $headerLogo,
                'footer' => $footerLogo,
            ],
            'menuType' => App::getOption('header_menu_type', 'burgeronly'),
            'footerText'  => App::getOption('footer_text'),
            'officesInfo' => $this->officesInfo(),
        ];
    }
}