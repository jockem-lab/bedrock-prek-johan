<?php $this->options = get_option(self::SETTINGS_OPTION_NAME); ?>
<div class="wrap">
    <h2><?= __('FasAd Bridge', 'fasad-bridge'); ?></h2>
    <form method="post" action="options.php">
        <?php

        do_settings_sections(self::SETTINGS_SYNC_SLUG_NAME);
        submit_button(__('Synkronisera', 'fasad-bridge'), 'primary', 'synchronize_listings', true);

        do_settings_sections(self::SETTINGS_CLEAR_SLUG_NAME);
        submit_button(__('Rensa', 'fasad-bridge'), 'primary', 'clear_listings', true);
        ?>
    </form>
</div>
