(function ($) {
    'use strict';

    /**
     * All of the code for your admin-facing JavaScript source
     * should reside in this file.
     *
     * Note: It has been assumed you will write jQuery code here, so the
     * $ function reference has been prepared for usage within the scope
     * of this function.
     *
     * This enables you to define handlers, for when the DOM is ready:
     *
     * $(function() {
     *
     * });
     *
     * When the window is loaded:
     *
     * $( window ).load(function() {
     *
     * });
     *
     * ...and/or other possibilities.
     *
     * Ideally, it is not considered best practise to attach more than a
     * single DOM-ready or window-load handler for a particular page.
     * Although scripts in the WordPress core, Plugins and Themes may be
     * practising this, we should strive to set a better example in our own work.
     */


    $(document).ready(function () {
        var $synchronizeButton = $("#synchronize_listings");
        var $clearButton = $("#clear_listings");

        $synchronizeButton.on('click', function (e) {
            e.preventDefault();

            $('#sync_spinner').addClass('is-active').show();

            $('#sync-log').css("height", "250px").attr('src', '/_sync').on('load', function () {
                $('#sync_spinner').removeClass('is-active').hide();
            });
        });

        $clearButton.on('click', function (e) {
            e.preventDefault();
            $('#clear_spinner').addClass('is-active').show();

            var nonce = $("#_wpfasadnonce").val();
            var data = {
                action: 'clear_listings',
                nonce: nonce
            };

            jQuery.post(ajaxurl, data, function (response) {
                $('#clear_spinner').removeClass('is-active').hide();
            });
        });

        var scrollBottom = function () {
            var $contents = jQuery('#sync-log').contents();
            $contents.scrollTop($contents.height());
        };

    });


})(jQuery);
