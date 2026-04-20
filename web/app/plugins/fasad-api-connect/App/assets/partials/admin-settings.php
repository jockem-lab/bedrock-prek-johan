<?php $this->options = get_option(self::SETTINGS_OPTION_NAME); ?>
<div class="wrap">
    <h2>FasAd Bridge</h2>
    <form method="post" action="options.php">
        <?php
        // This prints out all hidden setting fields
        settings_fields(self::SETTINGS_OPTION_GROUP);

        do_settings_sections(self::SETTINGS_GENERAL_SLUG_NAME);
        submit_button("Spara inställningar", "primary", "save_settings", true);
        ?>
    </form>
</div>
