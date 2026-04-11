<?php

namespace App\View\Composers;

use function App\attributesToString;
use function App\getAttribute;
use function App\getPrivacyPolicyPage;

class Form extends PrekComposer
{
    protected static $views = [
        'forms.*'
    ];

    public function with()
    {
        $privacyPolicyPage = getPrivacyPolicyPage();
        $siteTitle         = get_bloginfo('name');
        $gdpr              = [];
        if ($privacyPolicyPage) {
            if (!str_ends_with($siteTitle, "s")) {
                $siteTitle .= "s";
            }
            $url           = get_permalink($privacyPolicyPage->ID);
            $pageTitle     = mb_strtolower($privacyPolicyPage->post_title);
            $label         = sprintf('Jag har tagit del av %s <a href="%s" target="_blank" class="underline">%s</a>', $siteTitle, $url, $pageTitle);
            $gdpr['url']   = $url;
            $gdpr['label'] = $label;
        }
        $inputs = [
            'interestform' => self::formInputs('interestform'),
            'showingform'  => self::formInputs('showingform'),
            'corporationform'  => self::formInputs('corporationform'),
        ];
        return [
            'inputs' => $inputs,
            'gdpr'   => $gdpr,
        ];
    }

    public static function formInputs($slug): array
    {
        $inputs   = [];
        $inputs[] = self::formField('firstname', placeholder: 'Förnamn*');
        $inputs[] = self::formField('lastname', placeholder: 'Efternamn*');
        $inputs[] = self::formField('mail', placeholder: 'E-post*', type: 'email');
        $inputs[] = self::formField('cellphone', placeholder: 'Telefon*');
        $inputs[] = self::formField('message', placeholder: 'Meddelande', type: 'textarea', required: false);
        if ($slug === 'corporationform') {
            $corporationID = self::$prekData['corporationID'];
            if ($corporationID) {
                $inputs[] = self::formField('fkcorporation', type: 'hidden', required: false, value: $corporationID);
            }
        }
        return array_filter($inputs, function($input){
            return !empty($input);
        });
    }

    private static function formField($name = '', $placeholder = '', $type = 'text', $required = true, $value = '', $attributes = []): array
    {
        if (in_array($type, ['text', 'number', 'email', 'hidden'])) {
            $fieldType = 'input';
        } elseif ($type === 'textarea') {
            $fieldType = 'textarea';
        } else {
            return [];
        }
        $input = [
            'fieldType' => $fieldType,
            'name'      => $name,
            'type'      => $type,
        ];
        if ($required) {
            $input['required'] = true;
        }
        if (!empty($placeholder)) {
            $input['placeholder'] = $placeholder;
        }
        if(!empty($value)) {
            $input['value'] = $value;
        }
        if ($fieldType === 'textarea') {
            if (!isset($attributes['cols'])) {
                $attributes['cols'] = 40;
            }
            if (!isset($attributes['rows'])) {
                $attributes['rows'] = 10;
            }
        }
        $input['attributes'] = attributesToString($attributes);
        return $input;
    }

    public static function getFormById(int $id): false|int|\WP_Post
    {
        $forms = get_posts([
                               'post_type'   => 'html-form',
                               'numberposts' => -1,
                               'include'     => $id,
                           ]);
        if (count($forms) > 0) {
            return $forms[0];
        }
        return false;
    }

    public static function getFormBySlug(string $slug): false|int|\WP_Post
    {
        $forms = get_posts([
                               'post_type'   => 'html-form',
                               'numberposts' => -1,
                               'name'        => $slug,

                           ]);
        if (count($forms) > 0) {
            return $forms[0];
        }
        return false;
    }
}