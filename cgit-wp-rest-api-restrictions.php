<?php

/**
 * Plugin Name: Castlegate IT WP Rest API Restrictions
 * Plugin URI:  https://github.com/castlegateit/cgit-wp-rest-api-restrictions
 * Description: Disable REST API endpoints and apply additional callbacks
 * Version:     2.2.1
 * Author:      Castlegate IT
 * Author URI:  https://www.castlegateit.co.uk/
 * Update URI:  https://github.com/castlegateit/cgit-wp-rest-api-restrictions
 */

use Castlegate\RestApiRestrictions\Plugin;

if (!defined('ABSPATH')) {
    exit;
}

define('CGIT_WP_REST_API_RESTRICTIONS_VERSION', '2.2.1');
define('CGIT_WP_REST_API_RESTRICTIONS_PLUGIN_FILE', __FILE__);
define('CGIT_WP_REST_API_RESTRICTIONS_PLUGIN_DIR', __DIR__);
define('CGIT_WP_REST_API_RESTRICTIONS_URL', plugins_url('', __FILE__));

require_once __DIR__ . '/classes/autoload.php';

Plugin::init();
