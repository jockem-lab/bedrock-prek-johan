<?php

namespace PrekWeb\Includes;

class Common {

    protected $loader;
    protected $options;

    public function __construct(\PrekWeb\Includes\Loader $loader, \PrekWeb\Includes\Options $options)
    {
        $this->loader  = $loader;
        $this->options = $options;
    }

    public function run()
    {
        $this->disableXmlRcp();
        $this->uploads();
        $this->templates();
        $this->cleanUp();
        $this->addSoil();
        $this->addCode();
        $this->buildDetails();
        $this->prekAvatar();
    }

    private function cleanUp()
    {
        // Remove standard descriptions so they don't show up in the <title>
        add_action('option_blogdescription', function($option){
            if (preg_match('/(Just another .+ site)|(En till .+webbplats)/', $option)) {
                $option = '';
            };
            return $option;
        });
        $this->removeEmoji();
    }

    private function addSoil()
    {
        add_action('after_setup_theme', function () {
            // Soil 4
            add_theme_support('soil', 'js-to-footer');
            // Soil 3
            add_theme_support('soil-js-to-footer');
            //add_theme_support('soil-disable-rest-api');
            //add_theme_support('soil-disable-asset-versioning');
            //add_theme_support('soil-disable-trackbacks');
        });
    }

    private function addCode()
    {
        add_action('wp_head', function () {
            $code = $this->options->getOption('code');
            if (!empty($code['head'])) {
                echo $code['head'];
            }

            if (apply_filters('prekweb_load_analytics', false)) {
                $keys = $this->options->getOption('keys');
                if (!empty($keys) && $keys['google_analytics']) {
                    $options = [
                        'should_load' => (WP_ENV === 'production' && !current_user_can('manage_options')),
                        'google_analytics_id' => $keys['google_analytics'],
                        'anonymize_ip' => true,
                    ];
                    extract($options);
                    ob_start();
                    require __DIR__ . "/../views/google-analytics-ua.php";
                    echo ob_get_clean();
                }
            }
        }, 1000);

        add_action('wp_footer', function () {
            $code = $this->options->getOption('code');
            if (!empty($code['footer'])) {
                echo $code['footer'];
            }
        }, 1000);
    }

    private function buildDetails()
    {
        $this->loader->addAction('wp_footer', $this, 'addBuildDetails');
    }

    private function prekAvatar()
    {
        $this->loader->addAction('get_avatar', $this, 'prekAvatarAction', 10, 6);
    }

    public function addBuildDetails()
    {
        $version = get_option('build');
        if (!empty($version)) {
            $build = $version['build'] ?? '';
            $time  = $version['time'] ?? '';
            echo PHP_EOL . '<!-- Build ' . $build . ': ' . $time . ' -->' . PHP_EOL;
        }
    }

    private function disableXmlRcp()
    {
        add_filter( 'xmlrpc_enabled', '__return_false' );
    }

    private function uploads()
    {
        if (!class_exists('PrekWebHelper\Includes\Common')) {
            $this->loader->addFilter('upload_mimes', $this, 'overrideFiletypes');
            $this->loader->addFilter('sanitize_file_name', $this, 'sanitizeFilename');
        }
    }

    private function templates()
    {
        $this->loader->addAction('get_template_part_404', $this, 'sage404template', 10, 2);
    }

    public function overrideFiletypes($mimes)
    {
        $mimes['svg'] = 'image/svg+xml';
        return $mimes;
    }

    // Sanitize file upload filenames
    public function sanitizeFilename($filename)
    {

        $sanitized_filename = remove_accents($filename); // Convert to ASCII

        // Standard replacements
        $invalid = array(
            ' ' => '-',
            '%20' => '-',
            '_' => '-'
        );
        $sanitized_filename = str_replace(array_keys($invalid), array_values($invalid), $sanitized_filename);

        $sanitized_filename = preg_replace('/[^A-Za-z0-9-\. ]/', '', $sanitized_filename); // Remove all non-alphanumeric except .
        $sanitized_filename = preg_replace('/\.(?=.*\.)/', '', $sanitized_filename); // Remove all but last .
        $sanitized_filename = preg_replace('/-+/', '-', $sanitized_filename); // Replace any more than one - in a row
        $sanitized_filename = str_replace('-.', '.', $sanitized_filename); // Remove last - if at the end
        $sanitized_filename = strtolower($sanitized_filename); // Lowercase

        return $sanitized_filename;
    }

    private function removeEmoji()
    {
        if (!class_exists('PrekWebHelper\Includes\Common')) {
            remove_action( 'admin_print_styles', 'print_emoji_styles' );
            remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
            remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
            remove_action( 'wp_print_styles', 'print_emoji_styles' );
            remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
            remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
            remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
        }
    }

    /**
     * Load correct 404 template in Sage
     *
     * @param $slug
     * @param string $name
     */
    public function sage404template($slug, $name = '')
    {
        if (Helpers::isSage9()) {
            global $wp_query;
            $wp_query->is_404 = true;

            add_filter('body_class', function (array $classes) {
                return ['error404', 'app-data', 'index-data', '404-data'];
            });

            $template = \App\locate_template(["404.blade.php"]);
            $data = collect(get_body_class())->reduce(function ($data, $class) use ($template) {
                return apply_filters("sage/template/{$class}/data", $data, $template);
            }, []);
            echo \App\template($template, $data);
        }
    }

    public function prekAvatarAction(string $avatar, $id_or_email, int $size, string $default, string $alt, array $args)
    {

        if (
            class_exists('\PrekWebHelper\Includes\Common') &&
            method_exists('\PrekWebHelper\Includes\Common', 'prekAvatarAction')
        ) {
            $helper = \PrekWebHelper\PrekWebHelper::getInstance();
            return $helper->common->prekAvatarAction($avatar, $id_or_email, $size, $default, $alt, $args);
        }

        $email = '';
        if (is_numeric($id_or_email)) {
            $user = get_user_by('id', $id_or_email);
            $email = $user->data->user_email;
        } elseif (is_string($id_or_email)){
            $email = $id_or_email;
        } elseif ($id_or_email instanceof \WP_User){
            $email = $id_or_email->data->user_email;
        }

        if (preg_match('/@prek\.se$/', $email)) {
            $avatar = '<img src="' . plugin_dir_url(__DIR__) . 'assets/images/avatar.png" alt="" class="avatar avatar-32 photo" height="32" width="32" loading="lazy">';
        }
        return $avatar;
    }

}
