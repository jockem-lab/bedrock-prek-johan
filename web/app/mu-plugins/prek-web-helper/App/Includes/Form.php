<?php

namespace PrekWebHelper\Includes;

class Form
{

    public const DEBUG_NONE = 0;
    public const DEBUG_LOG = 1;
    public const DEBUG_LOG_SLACK = 2;
    public const DEBUG_LOG_MAIL = 3;

    protected $loader;

    public function __construct(Loader $loader)
    {
        $this->loader = $loader;
    }

    public function run()
    {
        $this->honeypot();
        $this->contactForm7FromFiles();
    }

    private function getDebugLevel()
    {
        return apply_filters('prek_web_helper_form_debug_level', self::DEBUG_NONE);
    }

    public function honeypotField(): string
    {
        $honeypotField = $this->getHoneypotFieldName();
        return '<style>.ohnohoney{opacity:0;position:absolute!important;top:0;left:0;height:0;width:0!important;z-index:-1!important;}</style>
                <label class="ohnohoney" aria-hidden="true">' . bin2hex(random_bytes(8)) . ':
                    <input type="text" name="' . $honeypotField . '" value="" class="wpcf7-form-control wpcf7-text" autocomplete="noho" aria-invalid="false">
                </label>';
    }

    /**
     * Use {{honeypot}} in a form to get less spam. Submissions with that field entered will be marked as spam,
     * not sent to queue in FasAd and have no email sent.
     * Currently only supporting CF7.
     */
    private function honeypot()
    {

        /*
         * If size of POST array is larger than form field count, Html Forms will mark the submission as spam.
         * This disables that since we add fields through blade or with extra fields for honeypot/fkobject etc..
         */
        add_filter('hf_validate_form_request_size', '__return_false');

        $honeypotField = $this->getHoneypotFieldName();

        /*
         * Replace {{honeypot}} with a hidden honeypot field in a CF7 form
         */
        add_filter('do_shortcode_tag', function($output, $tag, $atts, $m) {
            if (in_array($tag, ['contact-form-7', 'hf_form'])) {
                $honeypotReplace = $this->honeypotField();
                $output = preg_replace('/<p>\s*{{honeypot}}\s*<\/p>/', '{{honeypot}}', $output); //Try to strip p-tags
                $output = preg_replace('/{{honeypot}}\s*<br(?: \/)*>/', '{{honeypot}}', $output); //Try to strip appending row break
                $output = str_replace('{{honeypot}}', $honeypotReplace, $output); //Replace honeypot
            }
            return $output;
        }, 10, 4);

        add_filter('hf_validate_form', [$this, 'hfValidateForm'], 10, 3);

        /**
         * CF7 docs recommends using $_POST instead of $submission->get_posted_data() for honeypot checks:
         * https://contactform7.com/2020/07/28/accessing-user-input-data/
         *
         */
        add_filter('wpcf7_validate', function(\WPCF7_Validation $result, $tags) use ($honeypotField) {
            if ($this->isHoneypotSpam($_POST)) {
                $result->invalidate(['name' => $honeypotField], 'Försök igen');
                $this->updateSpamCount();

                $debugLevel = $this->getDebugLevel();
                $submission = \WPCF7_Submission::get_instance();
                $contact_form = $submission->get_contact_form();

                if ($debugLevel >= self::DEBUG_LOG) {
                    // Manually save to Flamingo as spam
                    if (function_exists('wpcf7_flamingo_submit')) {
                        $submission->add_spam_log([
                            'agent'  => 'Prek Web Helper',
                            'reason' => 'Honeypot-fältet är ifyllt',
                        ]);
                        \wpcf7_flamingo_submit($contact_form, ['status' => 'spam']);
                    }
                }

                $this->sendDebugMessage($contact_form->title(), $submission->get_posted_data());
            }
            return $result;
        }, 10, 2);

        /*
         * Expose honeypot field name to js.
         */
        add_filter('fasad_bridge_inquiryLocalize', function($data) use ($honeypotField) {
            $data['data']['honeypotField'] = $honeypotField;
            return $data;
        });

        /*
         * Show honeypot spam count in forms admin overview
         */
        add_action('admin_notices', function () {
            $page    = isset($_REQUEST['page']) ? $_REQUEST['page'] : '';
            $post_id = isset($_REQUEST['post']) ? (int) $_REQUEST['post'] : 0;
            $form_id = isset($_REQUEST['form_id']) ? (int) $_REQUEST['form_id'] : 0;
            $hfList  = $page === 'html-forms' && !$form_id;
            $flamingoList = $page === 'flamingo_inbound' && !$post_id;
            if (($hfList || $flamingoList) && $count = $this->showSpamCount()): ?>
                <div class="notice notice-info">
                    <p><?= $count; ?></p>
                </div>
            <?php endif;
        });

        /*
         * Show spam reason on Flamingo single post screen.
         */
        add_action('current_screen', function(){
            $page    = isset($_REQUEST['page']) ? $_REQUEST['page'] : '';
            $action  = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
            $post_id = isset($_REQUEST['post']) ? (int) $_REQUEST['post'] : 0;
            if ($page === 'flamingo_inbound' && $action === 'edit' && $post_id) {
                $post = new \Flamingo_Inbound_Message($post_id);
                if ($post->spam) {
                    add_meta_box('inboundspamdiv', 'Spam',
                        [$this, 'flamingoSpamReason'], null, 'normal', 'default', $post);
                }
            }
        });

        /*
         * If honeypot field is filled in, mark as spam in Html Forms.
         * "eman" too to catch old honeypot field submissions.
         */
        add_filter('admin_print_styles', function() use ($honeypotField) {
            $js = <<<JS
const \$cells = jQuery(".widefat td.column-eman, .widefat td.column-$honeypotField");
\$cells.each(function(i, e){
  if (jQuery(e).text()) {
    jQuery(e).parent().addClass('spam');
  }
        });
JS;
            wp_add_inline_script('html-forms-admin', $js);
            wp_add_inline_style('html-forms-admin', '#tab-submissions tr.spam {background-color: rgba(255,0,0,.3) !important;}');
        }, 11);

    }

    public function getHoneypotFieldName(): string
    {
        // Random string, must start with a letter to pass \wpcf7_is_name()
        $fieldName = 'f48f4c46331f0e2f';
        return apply_filters('prek_web_helper_honeypot_field', $fieldName);
    }

    public function isHoneypotSpam($data): bool
    {
        $honeypotField = $this->getHoneypotFieldName();
        if (is_object($data)) {
            $data = (array) $data;
        }
        $value = isset($data[$honeypotField]) ? $data[$honeypotField] : '';
        return !empty($value);
    }

    public function hfValidateForm($error_code, $form, $data) {
        if ($this->isHoneypotSpam($data)) {
            $error_code = 'error';
            $this->updateSpamCount();

            $debugLevel = $this->getDebugLevel();
            if ($debugLevel >= self::DEBUG_LOG) {
                // Manually save to HTML Forms submissions
                if (class_exists('\HTML_Forms\Submission')) {
                    unset(
                        $data['_wpnonce'],
                        $data['_wp_http_referer'],
                        $data['_hf_form_id'],
                        $data['_hf_h' . $form->id],
                    );
                    $submission = new \HTML_Forms\Submission();
                    $submission->form_id      = $form->id;
                    $submission->data         = $data;
                    $submission->ip_address   = !empty($_SERVER['REMOTE_ADDR'])     ? sanitize_text_field($_SERVER['REMOTE_ADDR']) : '';
                    $submission->user_agent   = !empty($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field($_SERVER['HTTP_USER_AGENT']) : '';
                    $submission->referer_url  = !empty($_SERVER['HTTP_REFERER'])    ? sanitize_text_field($_SERVER['HTTP_REFERER']) : '';
                    $submission->submitted_at = gmdate('Y-m-d H:i:s');
                    $submission->save();
                }
            }

            $this->sendDebugMessage($form->title, $data);
        }
        return $error_code;
    }

    public function flamingoSpamReason($post)
    {
        ?>
        <table class="widefat message-fields striped">
            <tbody>
            <?php foreach ($post->spam_log as $value): ?>
                <tr>
                    <td class="field-title"><?= $value['agent']; ?></td>
                    <td class="field-value"><?= $value['reason']; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    }

    private function updateSpamCount()
    {
        $spamCount = intval(get_option('prek_spam_count', 0));
        update_option('prek_spam_count', ++$spamCount);
    }

    public function showSpamCount()
    {
        $count = number_format(intval(get_option('prek_spam_count', 0)), 0, ',', ' ');
        return $count ? 'Honeypot total count: ' . $count : '';
    }

    private function sendDebugMessage(string $formTitle, $formData): void
    {
        switch ($this->getDebugLevel()) {
            case self::DEBUG_LOG_SLACK:
                do_action_ref_array('prek_log_slack', [array_merge(['Honeypot', $formTitle], $formData)]);
                break;
            case self::DEBUG_LOG_MAIL:
                $recipient = apply_filters('prek_web_helper_honeypot_debug_mail_recipient', '');
                if ($recipient) {
                    wp_mail($recipient, 'Honeypot - ' . get_bloginfo('name') . ': ' . $formTitle, print_r($formData, true));
                }
                break;
        }
    }

    public function contactForm7FromFiles()
    {
        add_filter('wpcf7_contact_form_properties', function($properties, \WPCF7_ContactForm $val) {
            $replacement = $this->getFormFileContent($properties['form']);
            if (!empty($replacement)) {
                if (!empty($replacement['form'])) {
                    $properties['form'] = $replacement['form'];
                }
                if (!empty($replacement['mail'])) {
                    $properties['mail']['body'] = $replacement['mail'];
                }
                if (!empty($replacement['settings'])) {
                    $properties['additional_settings'] = $replacement['settings'];
                }
                if (!empty($replacement['mailsettings'])) {
                    $properties['mail'] = array_merge($properties['mail'], $replacement['mailsettings']);
                }
            }

            if (!is_admin() && is_user_logged_in()) {
                $editLink = sprintf('<div><a href="%s?page=wpcf7&post=%d" class="editCF7link">Redigera formulär</a></div>', admin_url('admin.php'), $val->id());
                $properties['form'] .= $editLink;
            }
            return $properties;
        }, 10, 2);

        // Show warning above form in admin if form is read from file
        add_action('wpcf7_admin_warnings', function ($page, $action, $object) {
            if ($object instanceof \WPCF7_ContactForm) {
                $contact_form = $object;
            } else {
                return;
            }

            if ($this->getFormFileName($contact_form->get_properties()['form'])) {
                $message = 'GÖR INGA ÄNDRINGAR HÄR, DET GÖRS I FILER I TEMAT';

                echo sprintf(
                    '<div class="notice notice-warning"><p>%s</p></div>',
                    esc_html( $message )
                );
            }
        }, 10, 3);
    }

    /*
     * Find tag like {prekform_kontakt} in the form field
     * so we know to read content from files in /themes/my-theme/forms/kontakt/
     */
    public function getFormFileName($content)
    {
        preg_match('/\{prekform_([^}]+)\}/', $content, $match);
        return $match[1] ?? '';
    }

    /*
     * Get contents from form fields in the theme
     * Example: Using {prekform_kontakt} in the form field
     * and having files in /themes/my-theme/forms/kontakt/
     * will replace all content with the content of those file.
     *
     * Possible files:
     * /themes/my-theme/forms/kontakt/form.html
     * /themes/my-theme/forms/kontakt/mail.html
     * /themes/my-theme/forms/kontakt/settings.html
     * /themes/my-theme/forms/kontakt/mailsettings.json
     */
    public function getFormFileContent($content)
    {
        $replacement = [];
        if ($folderName = $this->getFormFileName($content)) {
            $root     = get_template_directory() . '/forms/';
            $fileForm = $root . $folderName . '/form.html';
            if (file_exists($fileForm)) {
                $replacement['form'] = file_get_contents($fileForm);
                // Append original formtag so we can check what file was even after replacement
                $replacement['form'] .= PHP_EOL . '<!--{prekform_'.$folderName.'}-->';
            }
            $fileMail = $root . $folderName . '/mail.html';
            if (file_exists($fileMail)) {
                $replacement['mail'] = file_get_contents($fileMail);
            }
            $fileSettings = $root . $folderName . '/settings.html';
            if (file_exists($fileSettings)) {
                $replacement['settings'] = file_get_contents($fileSettings);
            }
            $fileMailSettings = $root . $folderName . '/mailsettings.json';
            if (file_exists($fileMailSettings)) {
                $replacement['mailsettings'] = json_decode(file_get_contents($fileMailSettings), true);
            }
        }
        return $replacement;
    }

}
