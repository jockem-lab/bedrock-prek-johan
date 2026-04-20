<?php

namespace FasadSearchCriterias\Includes;

use FasadSearchCriterias\FasadSearchCriterias;
use FasadSearchCriterias\Includes\Loader;

class Form
{
    protected $loader;
    protected $repository;
    protected $metaPrefix;

    public function __construct(Loader $loader, Repository $repository, string $metaPrefix)
    {
        $this->loader = $loader;
        $this->repository = $repository;
        $this->metaPrefix = $metaPrefix;
    }

    public function run()
    {
        $this->shortcodes();
    }

    public function sections() : array
    {
        return [
            FasadSearchCriterias::FASAD_SEARCH_OBJECT_TYPE_NAME => __('Objekttyper', $this->metaPrefix),
            FasadSearchCriterias::FASAD_SEARCH_CRITERIA_NAME    => __('Sökkriterier', $this->metaPrefix),
            FasadSearchCriterias::FASAD_SEARCH_DISTRICT_NAME    => __('Sökdistrikt', $this->metaPrefix),
            FasadSearchCriterias::FASAD_SEARCH_PRICE_NAME       => __('Pris', $this->metaPrefix),
            FasadSearchCriterias::FASAD_SEARCH_ROOMS_NAME       => __('Antal rum', $this->metaPrefix),
            FasadSearchCriterias::FASAD_SEARCH_SIZE_NAME        => __('Storlek', $this->metaPrefix),
        ];
    }

    public function outputArray(string $fieldType = '') : array
    {
        $searchCriterias = $this->repository->selectAll($fieldType);
        $staticFields    = $this->getStaticFields();

        if ($fieldType) {
            if (array_key_exists($fieldType, $searchCriterias)) {
                return $searchCriterias[$fieldType];
            }
            if (array_key_exists($fieldType, $staticFields)) {
                return $staticFields[$fieldType];
            }
        }

        return array_merge($searchCriterias, $staticFields);
    }

    public function outputHtml(string $fieldType = '', bool $showLegend = true) : string
    {
        $output = '';
        $searchCriterias = $this->outputArray($fieldType);
        foreach ($searchCriterias as $section => $elements) {
            if (!empty($elements)) {
                $elementOut = sprintf(
                    '<fieldset class="fasad-search-criterias-%s">',
                    esc_attr($section)
                );
                if ($showLegend) {
                    $elementOut .= sprintf(
                        '<legend>%s</legend>',
                        $this->sections()[$section]
                    );
                }
                foreach ($elements as $element) {
                    if (in_array($element['element'], ['checkbox', 'radio'])) {
                        $format = '<div class="fasad-search-criterias-element fasad-search-criterias-element-%s fasad-search-criterias-element-%s"><label><input type="%s" name="%s" value="%s"> %s</label></div>';
                        $args = [
                            esc_attr($element['element']),
                            esc_attr($element['value']),
                            esc_attr($element['element']),
                            esc_attr($element['name']),
                            esc_attr($element['value'] ?? ''),
                            esc_html($element['label'])
                        ];
                        $format = apply_filters('fasad_search_criterias_html_element_format', $format, $element, $section, $fieldType);
                        $args = apply_filters('fasad_search_criterias_html_element_args', $args, $element, $section, $fieldType);
                        $elementOut .= apply_filters('fasad_search_criterias_html_element', vsprintf($format, $args), $format, $args, $element, $section, $fieldType);
                    } else {
                        $format = '<div class="fasad-search-criterias-element fasad-search-criterias-element-%s fasad-search-criterias-element-%s"><label for="fasad-search-%s">%s</label><input id="fasad-search-%s" type="%s" name="%s" value="%s" placeholder="%s"></div>';
                        $args = [
                            esc_attr($element['element']),
                            esc_attr($element['name']),
                            esc_attr($element['name']),
                            esc_html($element['label']),
                            esc_attr($element['name']),
                            esc_attr($element['element']),
                            esc_attr($element['name']),
                            esc_attr($element['value'] ?? ''),
                            esc_attr($element['placeholder'] ?? '')
                        ];
                        $format = apply_filters('fasad_search_criterias_html_element_format', $format, $element, $section, $fieldType);
                        $args = apply_filters('fasad_search_criterias_html_element_args', $args, $element, $section, $fieldType);
                        $elementOut .= apply_filters('fasad_search_criterias_html_element', vsprintf($format, $args), $format, $args, $element, $section, $fieldType);
                    }
                }
                $elementOut .= '</fieldset>';
                $output .= $elementOut;
            }
        }
        return apply_filters('fasad_search_criterias_html_elements', $output, $fieldType);
    }

    public function getStaticFields() : array
    {
        return apply_filters('fasad_search_criterias_fields', [
            'rooms' => [
                [
                    'name'    => 'minrooms',
                    'label'   => __('Min', $this->metaPrefix),
                    'element' => 'number',
                ],
                [
                    'name'    => 'maxrooms',
                    'label'   => __('Max', $this->metaPrefix),
                    'element' => 'number',
                ]
            ],
            'size' => [
                [
                    'name'    => 'minarea',
                    'label'   => __('Min', $this->metaPrefix),
                    'element' => 'number',
                ],
                [
                    'name'    => 'maxarea',
                    'label'   => __('Max', $this->metaPrefix),
                    'element' => 'number',
                ]
            ],
            'price' => [
                [
                    'name'    => 'minprice',
                    'label'   => __('Min', $this->metaPrefix),
                    'element' => 'number',
                ],
                [
                    'name'    => 'maxprice',
                    'label'   => __('Max', $this->metaPrefix),
                    'element' => 'number',
                ]
            ]
        ]);
    }

    public function shortcode($atts)
    {
        $atts = shortcode_atts(
            [
                'type'   => '',
                'legend' => 'true'
            ],
            $atts,
            'searchcriterias'
        );

        $showLegend = filter_var($atts['legend'], FILTER_VALIDATE_BOOLEAN);

        return $this->outputHtml($atts['type'], $showLegend);
    }

    public function shortcodes()
    {
        /*
         * Created shortcode
         * [searchcriterias type="" legend="true"]
         */
        add_shortcode('searchcriterias', function ($atts) {
            $atts = shortcode_atts(
                [
                    'type'   => '',
                    'legend' => 'true'
                ],
                $atts,
                'searchcriterias'
            );
            $showLegend = filter_var($atts['legend'], FILTER_VALIDATE_BOOLEAN);
            return $this->outputHtml($atts['type'], $showLegend);
        });

        /*
         * Replace {{searchcriterias}} with the search criteria fields in a CF7 form
         */
        add_filter('do_shortcode_tag', function ($output, $tag, $atts, $m) {
            if (in_array($tag, ['contact-form-7', 'hf_form'])) {
                $outputReplace = $this->outputHtml();
                $output = preg_replace('/<p>\s*{{searchcriterias}}\s*<\/p>/', '{{searchcriterias}}', $output); //Try to strip p-tags
                $output = preg_replace('/{{searchcriterias}}\s*<br(?: \/)*>/', '{{searchcriterias}}', $output); //Try to strip appending row break
                $output = str_replace('{{searchcriterias}}', $outputReplace, $output);
            }
            return $output;
        }, 10, 4);
    }

}
