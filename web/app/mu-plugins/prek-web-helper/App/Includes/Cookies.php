<?php

namespace PrekWebHelper\Includes;

class Cookies
{
    protected $loader;
    private $varsFilename = 'cookies-and-content-security-policy-vars.php';

    /*
     * If a domain is added to $defaultDomains, add usage explanation in $domainUsage, strip http:// https:// www. and *.
     */
    private $domainUsage = [
        'crm.fasad.eu'                        => 'FasAd',
        'cdn.jsdelivr.net/npm/vue'            => 'Javascript-ramverk, används bland annat för presentation av objekt på listningssidor',
        'cdn.jsdelivr.net/npm/lodash@4.17.15' => 'Javascript-ramverk, används bland annat för sortering och filtrering av objekt',
        'process.fasad.eu'                    => 'Bildhanteraren i FasAd, skalar och cachear bilder',
        'dev-process.fasad.prek.srv'          => 'Bildhanteraren i FasAd, skalar och cachear bilder, för utvecklingsmiljöer',
        'unpkg.com'                           => '',
        'ajax.googleapis.com'                 => '',
        'code.jquery.com'                     => '',
        'use.fontawesome.com'                 => 'Ikonfonter',
        'google.com/recaptcha'                => 'reCAPTCHA för formulär',
        'gstatic.com/recaptcha'               => 'reCAPTCHA för formulär',
        'cdn.fasad.eu'                        => 'Cachning av bilder',
        'images01.fasad.eu'                   => 'Bildserver i FasAd',
        'images02.fasad.eu'                   => 'Bildserver i FasAd',
        'images03.fasad.eu'                   => 'Bildserver i FasAd',
        'images04.fasad.eu'                   => 'Bildserver i FasAd',
        'images05.fasad.eu'                   => 'Bildserver i FasAd',
        'scontent-arn2-1.cdninstagram.com'    => '',
        'scontent-arn2-2.cdninstagram.com'    => '',
        'api.mapbox.com'                      => '',
        'tile.openstreetmap.fr'               => '',
        'google-analytics.com'                => '',
        'googletagmanager.com'                => '',
        's0-cdn.hittahem.se/objecttracker'    => '',
        'facebook.com'                        => '',
        'counter.fasad.eu'                    => 'Räknare som rapporterar antal besök på ett objekt till FasAd',
        'maps.googleapis.com'                 => 'Kartor från Google',
        'maps.googleapis.com/maps/api'        => 'Kartor från Google',
        'maps.google.com'                     => 'Kartor från Google',
        'maps.googleapis.com/maps-api-v3'     => 'Kartor från Google',
        'maps.gstatic.com'                    => 'Kartor från Google',
        'ausi.github.io/respimagelint'        => 'Verktyg för att kontrollera responsiva bilder, används vid utveckling',
        'ausi.github.io'                      => 'Verktyg för att kontrollera responsiva bilder, används vid utveckling',
        'connect.facebook.net'                => '',
    ];

    private $defaultDomains = [
        'always'     => [
            'scripts' => [
                'https://crm.fasad.eu/',
                'https://cdn.jsdelivr.net/npm/vue/',
                'https://cdn.jsdelivr.net/npm/lodash@4.17.15/',
                'https://process.fasad.eu/',
                'http://dev-process.fasad.prek.srv',
                'https://unpkg.com',
                'http://ajax.googleapis.com/',
                'https://ajax.googleapis.com/',
                'http://code.jquery.com/',
                'https://code.jquery.com/',
                'https://use.fontawesome.com/',
                'https://www.google.com/recaptcha/',
                'https://www.gstatic.com/recaptcha/',
            ],
            'images'  => [
                'https://crm.fasad.eu/',
                'https://cdn.fasad.eu/',
                'https://process.fasad.eu/',
                'http://dev-process.fasad.prek.srv/',
                'https://images01.fasad.eu/',
                'https://images02.fasad.eu/',
                'https://images03.fasad.eu/',
                'https://images04.fasad.eu/',
                'https://images05.fasad.eu/',
                'https://scontent-arn2-1.cdninstagram.com/',
                'https://scontent-arn2-2.cdninstagram.com/',
                'https://unpkg.com',
                'https://api.mapbox.com',
                'https://*.tile.openstreetmap.fr',
            ],
            'frames'  => [
            ],
        ],
        'statistics' => [
            'scripts' => [
                'http://www.google-analytics.com',
                'https://google-analytics.com/',
                'https://*.google-analytics.com/',
                'https://googletagmanager.com/',
                'https://*.googletagmanager.com/',
                'https://s0-cdn.hittahem.se/objecttracker/',
            ],
            'images'  => [
                'https://www.facebook.com/',
                'https://counter.fasad.eu/',
                'https://google-analytics.com/',
                'https://*.google-analytics.com/',
            ],
            'frames'  => [
            ],
        ],
        'experience' => [
            'scripts' => [
                'http://maps.googleapis.com/',
                'https://maps.googleapis.com/',
                'http://maps.googleapis.com/maps/api/',
                'http://maps.google.com/',
                'https://maps.google.com/',
                'https://ausi.github.io/respimagelint/',
            ],
            'images'  => [
                'http://maps.google.com/',
                'https://maps.google.com/',
                'http://maps.googleapis.com/',
                'https://maps.googleapis.com/',
                'http://maps.googleapis.com/maps-api-v3/',
                'http://maps.gstatic.com/',
                'https://maps.gstatic.com/',
            ],
            'frames'  => [
                'http://maps.google.com/',
                'https://maps.google.com/',
                'http://www.google.com/',
                'https://www.google.com/',
                'http://maps.googleapis.com/maps/api/',
                'https://ausi.github.io/',
            ],
        ],
        'marketing'  => [
            'scripts' => [
                'https://connect.facebook.net/',
            ],
            'images'  => [
                'https://www.facebook.com/',
            ],
            'frames'  => [
                'https://www.facebook.com/',
            ],
        ],
    ];

    public function __construct(Loader $loader)
    {
        $this->loader = $loader;
    }

    public function run()
    {
        $cookiePlugin = 'cookies-and-content-security-policy/cookies-and-content-security-policy.php';
        add_action('activate_' . $cookiePlugin, function () {
            $this->setDefaultValues();
        });
        if (is_plugin_active($cookiePlugin)) {
            $this->createVarsFile();
            $this->policyPage();
            $this->loader->addAction('current_screen', $this, 'saveOptions');
            $this->loader->addAction('admin_footer', $this, 'disableInputs');
            $this->loader->addAction('admin_notices', $this, 'showErrors');
            $this->loader->addAction('admin_footer', $this, 'addFilterInput');
            $this->loader->addAction('admin_footer', $this, 'addTab');
            $this->loader->addAction('template_redirect', $this, 'checkReset');
        }

        $this->loader->addAction('plugins_loaded', $this, 'disableCookieHeaderInCron', 9);
    }

    public function checkReset()
    {
        if (isset($_GET['cacsp_reset']) && isset($_COOKIE['cookies_and_content_security_policy'])) {
            setcookie('cookies_and_content_security_policy', $_COOKIE['cookies_and_content_security_policy'], time() - (60 * 60 * 24)); //Expire cookie (make it a day old)
            wp_safe_redirect(remove_query_arg('cacsp_reset'));
        }
    }

    private function sanitizeDomainName($domainName): string
    {
        return rtrim(str_replace(['www.', '*.', 'http://', 'https://'], '', $domainName), '/');
    }

    private function setDefaultValues()
    {
        $options = [
            'banner'                   => 1, //Banner instead of modal
            'hide_unused_settings_row' => 1, //Hide option types in settings if empty (example: don't show marketing if no domains are set)
            'show_refuse_button'       => 1, //Show button for refusing all cookies
            'settings_policy_link'     => 0, //Page id for integrity policy page
            'text_link_text'           => 'Läs vår integritetspolicy', //Text on link to page
        ];

        foreach (['integritetspolicy', 'cookiepolicy', 'datapolicy'] as $slug) {
            $integrityPolicyPage = get_page_by_path($slug);
            if ($integrityPolicyPage) {
                $options['settings_policy_link'] = $integrityPolicyPage->ID;
                break;
            }
        }
        if (!$options['settings_policy_link']) {
            unset($options['settings_policy_link']);
        }
        foreach ($options as $name => $value) {
            $optionName = $this->optionName($name);
            if (get_option($optionName) === false) {
                update_option($optionName, $value);
            }
        }
    }

    private function policyPage()
    {
        $policy = [
            'slug' => 0,
            'text'   => ''
        ];
        //Cacsp use default option if not edited in wp (but we might need to change this stuff, so ordinary filter too)
        add_filter('default_option_' . $this->optionName('settings_policy_link'), function ($pageID) use ($policy) {
            $policy = apply_filters('prek_cacsp_settings_policy', $policy);
            if (!empty($policy['slug'])) {
                if($page = get_page_by_path($policy['slug'])){
                    $pageID = $page->ID;
                }
            }
            return $pageID;
        });
        add_filter('option_' . $this->optionName('settings_policy_link'), function ($pageID) use ($policy) {
            $policy = apply_filters('prek_cacsp_settings_policy', $policy);
            if (!empty($policy['slug'])) {
                if($page = get_page_by_path($policy['slug'])){
                    $pageID = $page->ID;
                }
            }
            return $pageID;
        });

        //Cacsp use default option if not edited in wp
        add_filter('default_option_' . $this->optionName('text_link_text'), function ($text) use ($policy) {
            $policy = apply_filters('prek_cacsp_settings_policy', $policy);
            if (!empty($policy['text'])) {
                $text = $policy['text'];
            }
            return $text;
        });
    }

    /*
     * Saving options domains if on cscsp settings page, is a prek user and has filter setup in theme
     * This means that if there is no filter in theme cookie settings are handled "the old way"
     * Hopefully not breaking any sites
     */
    public function saveOptions()
    {
        if ($this->cacspSettingsScreen() && Helpers::isPrekUser() && $this->hasDomainFilter()) {
            $domains = $this->defaultDomains;
            foreach ($domains as $level => $types) {
                foreach ($types as $type => $values) {
                    update_option($this->domainOptionName($level, $type), $this->getValue($level, $type));
                }
            }
            if (function_exists('cacsp_save_error_message_js')) {
                cacsp_save_error_message_js();
            }
        }
    }

    /*
     * create vars file if it doesnt exist
     */
    private function createVarsFile()
    {
        if (!$this->varsFileExists()) {
            $content = '<?php $wp_load_path = __DIR__ . "/../../wp/wp-load.php";';
            file_put_contents(trailingslashit(WP_PLUGIN_DIR) . $this->varsFilename, $content);
        }
    }

    /*
     * Check if filter is active in theme
     */
    private function hasDomainFilter()
    {
        return has_filter('prek_cacsp_settings_domains');
    }

    private function optionName($option): string
    {
        return 'cacsp_option_' . $option;
    }

    /*
     * formats option name and corrects misspelling
     */
    private function domainOptionName($key, $type): string
    {
        if ($key == 'marketing') {
            $key = 'markerting';
        }
        return $this->optionName($key . '_' . $type);
//        return 'cacsp_option_' . $key . '_' . $type;
    }

    /*
     * get value from domainsetting,
     * returns value from theme if set,
     * otherwise returns value from cacsp option
     */
    private function getValue($level, $type)
    {
        $domains = apply_filters('prek_cacsp_settings_domains', $this->defaultDomains);
        $value   = isset($domains[$level][$type]) ? $domains[$level][$type] : '';
        if (is_array($value)) {
            if (end($value) !== '') {
                //last element not empty, push one empty value to the end of array, representing new line
                $value[] = '';
            }
            $value = implode("\r\n", $value);
        }
        return $value;
    }

    /*
     * Disable inputs if theme has filter
     */
    public function disableInputs()
    {
        if ($this->cacspSettingsScreen() && $this->hasDomainFilter()):
            ?>
            <script type="text/javascript">
              (function() {
                <?php foreach ($this->defaultDomains as $key => $types): ?>
                  <?php foreach($types as $type => $value): ?>
                    var element = document.getElementById("<?= $this->domainOptionName($key, $type); ?>");
                    if (element) {
                      element.setAttribute('disabled', true);
                    }
                  <?php endforeach; ?>
                <?php endforeach; ?>
                const elements = document.getElementsByName("save_cacsp_settings_domains");
                if (elements.length > 0) {
                  elements.forEach((element) => (element.setAttribute("disabled", true)));
                }
              })();
            </script>
        <?php
        endif;
    }

    public function addTab()
    {
        if (Helpers::isPrekUser()):
            if ($this->cacspSettingsScreen()):
                $class = 'nav-tab';
                if($this->isPrekTab()){
                    $class .= ' nav-tab-active';
                }
                ?>
                <script type="text/javascript">
                  (function() {
                    var $navTabWrapper = jQuery(".nav-tab-wrapper");
                    if($navTabWrapper.length){
                      var $tab = jQuery("<a />");
                      $tab.attr('href', '?page=cacsp_settings&tab=prek');
                      $tab.html('Prek');
                      $tab.addClass("<?= $class; ?>");
                      $tab.appendTo($navTabWrapper);
                    }
                  })();
                </script>
            <?php
            endif;
        endif;
    }
    private function isPrekTab(): bool
    {
        if($this->cacspSettingsScreen()){
            return !empty($_GET['page']) && $_GET['page'] === 'cacsp_settings' && !empty($_GET['tab']) && $_GET['tab'] === 'prek';
        }
        return false;
    }
    public function addFilterInput()
    {
        if (Helpers::isPrekUser()):
            if($this->isPrekTab()):
                ?>
                <style>
                    .prek-cacsp-wrapper .instructions {
                        font-style: italic;
                    }

                    .prek-cacsp-wrapper .textarea {
                        width: 100%;
                        margin-top: 10px;
                        margin-bottom: 20px;
                    }

                    .prek-cacsp-wrapper .textarea.selectable {
                        cursor: pointer;
                    }

                    .prek-cacsp-wrapper table {
                        margin-bottom: 10px;
                    }

                    .prek-cacsp-wrapper th {
                        text-align: left;
                    }

                    .prek-cacsp-wrapper th,
                    .prek-cacsp-wrapper td {
                        padding: 2px;
                    }
                </style>
                <script type="text/javascript">
                  (function() {
                    var $form = jQuery(".nav-tab-wrapper").parent("form");
                    if($form.length){
                      var $prekWrapper = jQuery("<div />");
                      $prekWrapper.attr('class', 'prek-cacsp-wrapper');
                      var $h1 = jQuery('<h1 />');
                      $h1.html('Filter');
                      $prekWrapper.append($h1);

                      var $h2 = jQuery('<h2 />');
                      $h2.html('Domäner');
                      $prekWrapper.append($h2);

                      var $infoDiv = jQuery("<div />");
                      $infoDiv.html('Kopiera detta filter till temat, och justera efter behov');
                      $prekWrapper.append($infoDiv);

                      var $textarea = jQuery("<textarea />");
                      $textarea.attr("rows", 18);
                      // $textarea.attr("style", "width: 100%; margin-top: 10px; margin-bottom: 20px; cursor: pointer");
                      $textarea.attr("readonly", "readonly");
                      $textarea.attr('class', 'textarea selectable');
                      $textarea.on("click", function(){this.select();});
                      $textarea.html("<?= $this->getDomainFilterAsString(['withDomains' => true]); ?>");
                      $prekWrapper.append($textarea);

                      var $infoDiv = jQuery("<div />");
                      $infoDiv.html('Eller börja med ett tomt');
                      $prekWrapper.append($infoDiv);

                      var $textarea = jQuery("<textarea />");
                      $textarea.attr("rows", 18);
                      // $textarea.attr("style", "width: 100%; margin-top: 10px; margin-bottom: 20px; cursor: pointer");
                      $textarea.attr("readonly", "readonly");
                      $textarea.attr('class', 'textarea selectable');
                      $textarea.on("click", function(){this.select();});
                      $textarea.html("<?= $this->getDomainFilterAsString(['withDomains' => false]); ?>");
                      $prekWrapper.append($textarea);

                      var $h2 = jQuery('<h2 />');
                      $h2.html('Policysida');
                      $prekWrapper.append($h2);

                      var $infoDiv = jQuery("<div />");
                      $infoDiv.html('Kopiera detta filter till temat, och justera efter behov');
                      $prekWrapper.append($infoDiv);

                      var $textarea = jQuery("<textarea />");
                      $textarea.attr("rows", 9);
                      $textarea.attr('class', 'textarea selectable');
                      $textarea.attr("readonly", "readonly");
                      $textarea.on("click", function(){this.select();});
                      $textarea.html("<?= $this->getPolicyFilterAsString(); ?>");
                      $prekWrapper.append($textarea);

                      var $h1 = jQuery('<h1 />');
                      $h1.html('Info');
                      $prekWrapper.append($h1);

                      var $h2 = jQuery('<h2 />');
                      $h2.html('Aktiva domäner');
                      $prekWrapper.append($h2);

                      var $p = jQuery('<p />');
                      $p.attr('class', 'instructions');
                      $p.html('Kopiera för att få en tabell med domäninställningar att skicka till kund eller dylikt');
                      $prekWrapper.append($p);

                      var $div = jQuery("<div />");
                      $div.attr('class', 'textarea');
                      $div.html("<?= $this->getActiveDomainsAsString(); ?>");
                      $prekWrapper.append($div);

                      $form.append($prekWrapper);
                    }
                  })();
                </script>
            <?php
            endif;
        endif;
    }

    private function spacer($repeat = 1): string
    {
        return str_repeat("\t", $repeat);
    }

    private function getActiveDomainsAsString(): string
    {
        $content        = [];
        $domains        = $this->defaultDomains;
        $levelNicenames = [
            'always'     => 'Tillåt alltid',
            'statistics' => 'Statistik',
            'experience' => 'Upplevelse',
            'marketing'  => 'Marknadsföring',
        ];
        foreach ($domains as $level => $types) {
            $activeDomains = [];
            foreach ($types as $type => $values) {
                //Fetch domains used in theme (sanitize to make it look good)
                $tmpDomains = array_map(function ($item) {
                    return $this->sanitizeDomainName($item);
                }, explode("\r\n", $this->getValue($level, $type)));

                //Merge and remove duplicates (on a level basis. No need to show more than one domain of the same in each level)
                $activeDomains = array_unique(array_merge($activeDomains, $tmpDomains));
            }
            $activeDomains = array_filter($activeDomains, function ($item) {
                return $item !== '';
            });
            if (empty($activeDomains)) {
                continue; //Skip output if no domains in level
            }
            $content[] = '<h3>' . $levelNicenames[$level] . '</h3>';
            $content[] = '<table>';
            $content[] = '<thead><tr><th>Domän</th><th>Användning</th></tr></thead>';
            foreach ($activeDomains as $activeDomain) {
                $content[] = '<tr>';
                $content[] = '<td>';
                $content[] = $activeDomain;
                $content[] = '</td>';
                $content[] = '<td>';
                if (array_key_exists($activeDomain, $this->domainUsage) && !empty($this->domainUsage[$activeDomain])) {
                    $content[] = $this->domainUsage[$activeDomain];
                } else {
                    $content[] = '&nbsp;';
                }
                $content[] = '</td>';
                $content[] = '</tr>';
            }
            $content[] = '</table>';
        }
        return implode('\r\n', $content);
    }

    /*
     * Return prek_cacsp_settings_policy-filter as a string
     * for simple copy paste
     */
    private function getPolicyFilterAsString(): string
    {
        $content   = [];
        $content[] = "add_filter('prek_cacsp_settings_policy', function (\$policy){";
        $content[] = $this->spacer() . "\$slug = '';";
        $content[] = $this->spacer() . "\$text = '';";
        $content[] = $this->spacer() . "return [";
        $content[] = $this->spacer(2) . "'slug' => \$slug,";
        $content[] = $this->spacer(2) . "'text' => \$text,";
        $content[] = $this->spacer() . "];";
        $content[] = '});';
        return implode('\r\n', $content);
    }

    /*
     * Return prek_cacsp_settings_domains-filter as a string
     * for simple copy paste
     */
    private function getDomainFilterAsString($params): string
    {
        $showDomains = $params['withDomains'];
        $content   = [];
        $content[] = "add_filter('prek_cacsp_settings_domains', function (\$values){";
        $content[] = $this->spacer() . 'return [';
        foreach ($this->defaultDomains as $key => $types) {
            $content[] = $this->spacer(2) . '\'' . $key . '\' => [';
            foreach ($types as $type => $values) {
                $content[] = $this->spacer(3) . '\'' . $type . '\' => [';
                if($showDomains){
                    foreach ($values as $value) {
                        $useAsDefault = (
                            strpos($value, "fasad.eu") !== false || //cdn, process, image servers, counter
                            strpos($value, "prek.srv") !== false || //dev-process
                            strpos($value, 'ausi.github.io') !== false //responsive image checker
                        );
                        //domains are commented, except those we really need
                        $content[] =  (!$useAsDefault ? "//" : "") . $this->spacer(4) . '\'' . $value . '\',';
                    }
                }else{
                    $content[] =  $this->spacer(4);
                }
                $content[] = $this->spacer(3) . '],';
            }
            $content[] = $this->spacer(2) . '],';
        }
        $content[] = $this->spacer() . '];';
        $content[] = '});';
        return implode('\r\n', $content);
    }

    public function showErrors()
    {
        if (Helpers::isPrekUser()):
            if ($this->cacspSettingsScreen()):
                foreach ($this->getErrors() as $error):
                    ?>
                    <div class="notice notice-error">
                        <p><?= $error; ?></p>
                    </div>
                <?php
                endforeach;
            endif;
        endif;
    }

    private function cacspSettingsScreen(): bool
    {
        return (function_exists('get_current_screen') && get_current_screen()->id == 'settings_page_cacsp_settings');
    }

    private function getErrors()
    {
        $errors = [];
        if (!$this->hasDomainFilter()) {
            $errors[] = 'Du måste lägga till ett filter i temat för domänerna';
        }
        if (!$this->varsFileExists()) {
            $errors[] = 'Filen ' . $this->varsFilename . ' verkar inte finnas';
        }
        return $errors;
    }

    private function varsFileExists()
    {
        return file_exists(trailingslashit(WP_PLUGIN_DIR) . $this->varsFilename);
    }

    /**
     * The plugin "Cookies and Content Security Policy" sets headers,
     * this leads to "Cannot modify header information - headers already sent"
     * in the errors logs, so we disable that when running a cron job.
     *
     * @return void
     */
    public function disableCookieHeaderInCron()
    {
        if (wp_doing_cron()) {
            remove_action('plugins_loaded', 'cacsp_init');
        }
    }

}
