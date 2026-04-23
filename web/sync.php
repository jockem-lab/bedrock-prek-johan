<?php
$_SERVER['HTTP_HOST'] = 'localhost:8092';
$_SERVER['REQUEST_URI'] = '/';
define('DOING_CRON', true);
require_once __DIR__ . '/wp/wp-load.php';

// Säkerställ att API_URL är definierad i rätt namespace
if (!defined('API_URL')) {
    define('API_URL', 'https://api.fasad.eu');
}
if (!defined('FasadApiConnect\Includes\API_URL')) {
    define('FasadApiConnect\Includes\API_URL', 'https://api.fasad.eu');
}

do_action('sync_all_listings');
$count = wp_count_posts('fasad_listing');
echo json_encode(['publish' => $count->publish, 'draft' => $count->draft]);
