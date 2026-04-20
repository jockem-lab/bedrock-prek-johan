<?php

namespace PrekWeb\Includes;

use FasadApiConnect\Includes\ApiConnectionHandler;
use FasadBridge\Includes\PublicSettings;
use FasadBridge\Includes\Synchronization\Realtor;

class Coworker
{
    protected $loader;
    protected $options;
    protected $syncSetting;
    /*
     * ACF fields to be created,
     * type defaults to 'text',
     * if type is image, return format defaults to url and url is added to coworker as image.url
     */
    private static $acfFields = [
        'firstname'  => [
            'label' => 'Förnamn',
        ],
        'lastname'   => [
            'label' => 'Efternamn',
        ],
        'email'      => [
            'label' => 'Mejl',
            'type'  => 'email',
        ],
        'cellphone'  => [
            'label' => 'Mobiltelefon',
        ],
        'phone'      => [
            'label' => 'Direkttelefon',
        ],
        'title'      => [
            'label' => 'Titel',
        ],
        'titleExtra' => [
            'label' => 'Titel (Extra)',
        ],
        'sequence' => [
            'label' => 'Ordning',
        ],
        'image'      => [
            'label'         => 'Porträtt',
            'type'          => 'image',
            'hide'          => true,
            'return_format' => 'id',
        ],
    ];

    public function __construct(\PrekWeb\Includes\Loader $loader, \PrekWeb\Includes\Options $options)
    {
        $this->loader  = $loader;
        $this->options = $options;
    }

    public static function getAcfFields()
    {
        foreach (self::$acfFields as $key => &$field) {
            if (!isset($field['type'])) {
                $field['type'] = 'text';
            }
            if (!isset($field['return_type'])) {
                $field['return_type'] = $key;
            }
            if ($field['type'] === 'image') {
                if (!isset($field['return_format'])) {
                    $field['return_format'] = 'url';
                }
            }
        }
        return self::$acfFields;
    }

    public static function hasExtendedCoworker()
    {
        return post_type_exists(PublicSettings::FASAD_COWORKER_POST_TYPE);
    }

    public function run()
    {
        $this->loader->addAction('acf/include_fields', $this, 'includeFields');
        $this->loader->addAction('acf/init', $this, 'realtorSyncSetting');
        $this->loader->addAction('save_post_fasad_coworker', $this, 'savePostFasadCoworker', 10, 3);
        $this->loader->addFilter('acf/load_value', $this, 'acfLoadValue', 10, 3);
        $this->loader->addFilter('acf/prepare_field', $this, 'acfPrepareField');
    }

    /*
     * If coworker post has a fasadid, we will disable the fields
     */
    public function acfPrepareField($field)
    {
        if (get_post_type() === PublicSettings::FASAD_COWORKER_POST_TYPE) {
            $acfFields = self::getAcfFields();
            $metaKey   = str_replace('field_prek-web_fasad_coworker_', '', $field['key']);
            if (get_post_meta(get_the_ID(), Fasad::$prefix . 'id', true)) {
                if (array_key_exists($metaKey, $acfFields)) {
                    if (isset($acfFields[$metaKey]['hide'])) {
                        return false;
                    }
                    $field['disabled'] = 1;
                }
            } else {
                if ($metaKey === 'sequence') {
                    $field['disabled'] = 1;
                }
            }
        }
        return $field;
    }

    /*
     * If coworker post has a fasadid, we will load the values from realtor to the corresponding values on this post
     */
    public function acfLoadValue($value, $post_id, $field)
    {
        if (get_post_type($post_id) === PublicSettings::FASAD_COWORKER_POST_TYPE) {
            $acfFields = self::getAcfFields();
            $metaKey   = str_replace('field_prek-web_fasad_coworker_', '', $field['key']);
            if ($fasadId = get_post_meta($post_id, Fasad::$prefix . 'id', true)) {
                if (array_key_exists($metaKey, $acfFields)) {
                    if ($realtor = Fasad::getRealtors($fasadId)) {
                        if (!empty($realtor->meta[$metaKey])) {
                            $value = $realtor->meta[$metaKey];
                        }
                    }
                }
            } else {
                if ($metaKey === 'sequence') {
                    if ($sequence = get_post_meta($post_id, Fasad::$prefix . 'sequence', true)) {
                        $value = $sequence;
                    }
                }
            }
        }
        return $value;
    }

    public static function mergeMeta($meta, $extendedMeta)
    {
        if (!$extendedMeta) {
            return $meta;
        }
        $coworkerMeta = [];
        $acfFields    = self::getAcfFields();
        foreach ($extendedMeta as $key => $value) {
            if (strpos($key, 'coworker_') !== false) {
                $coworkerMetaKey   = str_replace('prek-web_fasad_coworker_', '', $key);
                $coworkerMetaValue = '';
                if (array_key_exists($coworkerMetaKey, $acfFields)) {
                    if ($acfFields[$coworkerMetaKey]['return_type'] === 'image') {
                        switch ($acfFields[$coworkerMetaKey]['return_format']) {
                            case 'url':
                                $coworkerMetaValue = (object)['url' => $value];
                                break;
                            case 'array':
                                $coworkerMetaValue = (object)['url' => $value['url']];
                                break;
                            case 'id':
                                $coworkerMetaValue = (object)['url' => wp_get_attachment_image_url($value, 'full')];
                                break;
                        }
                    } elseif (in_array($acfFields[$coworkerMetaKey]['return_type'], ['cellphone', 'phone'])) {
                        $coworkerMeta[$coworkerMetaKey]            = Helpers::formatPhone($value);
                        $coworkerMeta[$coworkerMetaKey . 'String'] = Helpers::formatPhoneString($value, $acfFields[$coworkerMetaKey]['return_type']);
                    } else {
                        $coworkerMetaValue = $value;
                    }
                }
                if (!empty($coworkerMetaValue)) {
                    $coworkerMeta[$coworkerMetaKey] = $coworkerMetaValue;
                }
            }
        }
        if (!empty($meta['id'])) {
            // User in Fasad, Fasad fields take precendence
            $meta = array_merge($coworkerMeta, $meta);
        } else {
            // Coworker only in WP, ACF fields take precedence
            $meta = array_merge($meta, $coworkerMeta);
        }
        return $meta;
    }

    public function realtorSyncSetting()
    {
        $this->syncSetting = get_field(Fasad::$prefix . 'synchronize_realtors', 'option');
        if ($this->syncSetting === 'extended') {
            add_action('init', function () {
                $args = [
                    "labels"        => ["name" => __("Medarbetare", "fasad-bridge")],
                    "description"   => '',
                    "public"        => true,
                    "has_archive"   => false,
                    "rewrite"       => [
                        "slug" => PublicSettings::posttypesSlugs(PublicSettings::FASAD_COWORKER_POST_TYPE)
                    ],
                    "menu_position" => 20,
                    "menu_icon"     => "dashicons-groups"
                ];
                register_post_type(
                    PublicSettings::FASAD_COWORKER_POST_TYPE,
                    apply_filters('fasad_bridge_register_posttype', $args, PublicSettings::FASAD_COWORKER_POST_TYPE)
                );
            });

            add_action('fasad_bridge_realtor_complete', function ($postId, $realtorItem, $action, $params) {
                $realtorInstance = new Realtor(new ApiConnectionHandler(), $params['formatter']);
                $existingPost    = $realtorInstance->getByFasadId($realtorItem->id, 'id', PublicSettings::FASAD_COWORKER_POST_TYPE);
                if ($existingPost) {
                    $postId = $existingPost->ID;
                } else {
                    $postId = $realtorInstance->createPost($realtorItem, PublicSettings::FASAD_COWORKER_POST_TYPE);
                    $realtorInstance->savePostMeta(['id' => $realtorItem->id], $postId);
                    $realtorInstance->savePostMeta(['menu_order' => $realtorItem->sequence], $postId);
                    $realtorInstance->setOrder($postId, $realtorItem->sequence);
                }
                $realtorInstance->savePostMeta(['sequence' => $realtorItem->sequence], $postId);
            },         10, 4);
        }

        if ($this->syncSetting === 'no') {
            add_filter('fasad_synchronize_realtors', function ($syncRealtors) {
                return false;
            });
        }
    }

    /**
     * Save incremented sequence on newly created coworker posts
     *
     * @param $postId
     * @param $post
     * @param $update
     */
    public function savePostFasadCoworker($postId, $post, $update)
    {
        if (!$update) {
            global $wpdb;
            $sequence     = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT pm.meta_value from {$wpdb->postmeta} pm
                LEFT JOIN {$wpdb->posts} p ON p.id = pm.post_id
                WHERE pm.meta_key = %s
                AND p.post_status = %s
                AND p.post_type = %s
                ORDER BY pm.meta_value DESC
                LIMIT 1",
                    Fasad::$prefix . 'sequence',
                    'publish',
                    PublicSettings::FASAD_COWORKER_POST_TYPE
                )
            );
            $nextSequence = is_numeric($sequence) ? ++$sequence : 1;
            update_post_meta($postId, Fasad::$prefix . 'sequence', $nextSequence);
        }
    }

    public function includeFields()
    {
        if (!function_exists('acf_add_local_field_group')) {
            return;
        }
        $acfFields = self::getAcfFields();
        $fields    = [];
        foreach ($acfFields as $key => $acfField) {
            $fields[] = array_merge($acfField, [
                'key'  => 'field_prek-web_fasad_coworker_' . $key,
                'name' => 'prek-web_fasad_coworker_' . $key,
            ]);
        }
        acf_add_local_field_group(
            [
                'key'                   => 'group_prek-web_fasad_coworker',
                'title'                 => 'Medarbetare',
                'fields'                => $fields,
                'location'              => [
                    [
                        [
                            'param'    => 'post_type',
                            'operator' => '==',
                            'value'    => 'fasad_coworker',
                        ],
                    ],
                ],
                'menu_order'            => 0,
                'position'              => 'normal',
                'style'                 => 'default',
                'label_placement'       => 'top',
                'instruction_placement' => 'label',
                'hide_on_screen'        => '',
                'active'                => true,
                'description'           => '',
                'show_in_rest'          => 0,
            ]
        );
    }
}