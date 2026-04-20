<?php

namespace PrekWeb\Includes;

use PrekWeb\PrekWeb;

class Options {

    protected $loader;
    protected $prefix = 'prekweb_';
    protected $optiongroups = [
        'contact',
        'social',
        'keys'
    ];

    protected $optionpages;

    public function __construct(\PrekWeb\Includes\Loader $loader)
    {
        $this->loader = $loader;
    }

    public function run()
    {
        if( function_exists('acf_add_options_page') ) {
            $this->addOptionPages();
            $this->setMenuOptions();
            $this->loader->addFilter('prekweb_addOptions', $this, 'filterOptions', 10, 2);
        }
    }
    public function filterOptions($options, $title){
        //Trying to append added site specific settings
        if($title == sanitize_title(get_option('blogname') . ', Inställningar')){
            //Fetch all acf_field_groups ("Egna fält")
            $query = new \WP_Query([
                                       'post_type' => 'acf-field-group',
                                       'posts_per_page' => -1
                                   ]);
            if($query->have_posts()){
                //Try to find field group named "$sitename, Inställningar"
                $fieldKey = false;
                foreach($query->posts as $fieldGroup){
                    if($title == $fieldGroup->post_excerpt){
                        $fieldKey = $fieldGroup->post_name;
                        break;
                    }
                }
                if($fieldKey){
                    //Append a tab and clone field to existing field group with found site specific field group
                    $options['fields'][] = [
                        'key' => 'field_site_settings_group',
                        'label' => get_option('blogname'),
                        'name' => '',
                        'type' => 'tab',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'placement' => 'top',
                        'endpoint' => 0,
                    ];
                    $options['fields'][] = [
                        'key' => 'field_site_settings_clone',
                        'label' => '',
                        'name' => '',
                        'type' => 'clone',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'clone' => array(
                            0 => $fieldKey,
                        ),
                        'display' => 'seamless',
                        'layout' => 'block',
                        'prefix_label' => 0,
                        'prefix_name' => 0,
                    ];
                }
            }
        }
        return $options;
    }

    private function addOptionPages()
    {
        /*
         * Adding 2 option pages:
         * One for us -> Prek, Inställningar
         * One for site admins -> $sitename, Inställningar
         *
         * Using json-files. When updating any of the generic settings, export the new groups and replace {x}-settings.json
         * Some settings in json files are overwritten, such as locations
         */
        add_filter(
            'acf/init',
            function () {
                $this->optionpages = [
                    'protected' => [
                        [
                            'title' => 'Prek, Inställningar',
                            'json'  => 'prek-settings.json',
                        ],
                    ],
                    'public'    => [
                        [
                            'title' => get_option('blogname') . ', Inställningar',
                            'json'  => 'site-settings.json',
                        ],
                    ]
                ];

                foreach ($this->optionpages as $type => $optionpages) {
                    foreach ($optionpages as $optionpage) {
                        $this->addOptionsPage($optionpage, $type);
                    }
                }
            }
        );
    }

    private function addOptionsPage($optionpage, $type)
    {
        if (!apply_filters('prekweb_addOptionsPage', true, $optionpage)) {
            return;
        }
        //Create options page for each group
        $this->addOptions($optionpage['json'], sanitize_title($optionpage['title']));

        //Only add adminpage if public or prekuser and protected
        if ($type == 'public' || ($type == 'protected' && Helpers::isPrekUser())) {
            acf_add_options_page(
                [
                    'page_title' => $optionpage['title'],
                    'menu_title' => $optionpage['title'],
                    'capability' => 'edit_posts',
                    'redirect'   => false,
                ]
            );
        }
    }

    private function addOptions($filename, $title)
    {
        //Add field groups from jsonfile, add it to the correct options page (overwrite location params)
        if( function_exists('acf_add_local_field_group') ):
            $file = dirname(__DIR__) . "/assets/acf/" . $filename;
            if(file_exists($file)){
                $options = json_decode(file_get_contents($file), true);
                if(is_array($options) && count($options) == 1){
                    $options = $options[0];
                }
                $options['location'] = [];
                $options['location'][][] = [
                    'param' => 'options_page',
                    'operator' => '==',
                    'value' => 'acf-options-' . $title
                ];
                acf_add_local_field_group(apply_filters('prekweb_addOptions', $options, $title));
            }

        endif;
    }

    public function setMenuOptions()
    {
        if (!is_admin() || wp_doing_ajax()) {
            return;
        }

        add_filter('acf/load_field/name='.$this->prefix.'hidemenus', function ( $field ) {

            $field['choices'] = [];
            $fullMenu         = $GLOBALS[ 'menu' ];
            $fullSubMenu      = $GLOBALS[ 'submenu' ];
            foreach ($fullMenu as $menuItem) {
                if ($menuItem[4] == 'wp-menu-separator') {
                    // Skip separators
                    continue;
                }
                // Top level menu item (regexp to skip tags)
                $field['choices'][$menuItem[2]] = '<b>'.preg_replace('/^([^<]+)(<.*)$/', '$1', $menuItem[0]).'</b>';
                if (isset($fullSubMenu[$menuItem[2]])) {
                    foreach ($fullSubMenu[$menuItem[2]]  as $subMenuItem) {
                        if (!isset($field['choices'][$subMenuItem[2]])) {
                            //Sub level item, only if not a top item too
                            $field['choices'][$menuItem[2].'=='.$subMenuItem[2]] = '&nbsp;&nbsp;&nbsp;&nbsp;' . preg_replace('/^([^<]+)(<.*)$/', '$1', $subMenuItem[0]);
                        }
                    }
                }
            }
            return $field;
        });
    }

    public function getOption($key)
    {
        $keys = explode(".", $key);

        $data = get_field($this->prefix . $keys[0], 'option');
        array_shift($keys);

        if (count($keys) > 0) {
            return $this->optionsLoop($keys, $data);
        } else {
            return $data;
        }
    }

    private function optionsLoop($keys, $data)
    {
        foreach ($keys as $key) {
            if (isset($data[$key])) {
                $data = $data[$key];
            } else {
                $data = null;
                break;
            }
        }
        return $data;
    }

    public function getAllOptions()
    {
        $return = [];
        foreach ($this->optiongroups as $group) {
            $return[$group] = $this->getOption($group);
        }

        return $return;
    }

}