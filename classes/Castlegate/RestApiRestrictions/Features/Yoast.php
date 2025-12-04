<?php

declare(strict_types=1);

namespace Castlegate\RestApiRestrictions\Features;

use Castlegate\RestApiRestrictions\FeatureInterface;
use Castlegate\RestApiRestrictions\Notification;
use Castlegate\RestApiRestrictions\Plugin;

final class Yoast implements FeatureInterface
{
    /**
     * Feature title
     *
     * @var string
     */
    public static string $title = 'Yoast Hardening';

    /**
     * Feature description
     *
     * @var string
     */
    public static string $description = 'This feature disables a Yoast-enabled REST API endpoint designed for headless WordPress which exposes author information. It also prevents the same information from being added to post, page and custom post type endpoints.';

    /**
     * Initialise
     *
     * @return void
     */
    public static function init(): void
    {
        Notification::add(self::$title, self::$description, self::active());
    }

    /**
     * Load the Yoast functionality
     *
     * @return void
     */
    public static function run(): void
    {
        // Run as early as possible so Yoast sees our filtered value
        add_action('plugins_loaded', [__CLASS__, 'disableHeadlessRestEndpoint'], 0);
    }

    /**
     * Check if the Yoast feature is enabled
     *
     * @return bool
     */
    public static function active(): bool
    {
        return apply_filters('cgit-rest-api-restrictions//config/enable-feature-yoast', true);
    }

    /**
     * Add filters to prevent the enabling of the Yoast REST API endpoint
     * that is designed for headless WordPress. It exposes author information.
     * This disables a new Yoast endpoint as well as removes fields from
     * standard endpoints
     *
     * @return void
     */
    public static function disableHeadlessRestEndpoint(): void
    {
        // Yoast stores most settings in the single 'wpseo' option array.
        // We force the specific key off when that array is read.
        add_filter('option_wpseo', [self::class, 'forceDisableHeadless'], 999);
        add_filter('site_option_wpseo', [self::class, 'forceDisableHeadless'], 999);

        // Belt and braces: if Yoast ever splits this into its own option,
        // pre-emptively return false for likely keys.
        foreach (['enable_headless_rest_endpoints', 'headless_rest_endpoints_enabled'] as $key) {
            add_filter("pre_option_".$key, '__return_false', 999);
            add_filter("pre_site_option_".$key, '__return_false', 999);
        }
    }

    /**
     * Ensure 'enable_headless_rest_endpoints' is false in Yoast's main options array.
     *
     * @param mixed $opts
     * @return array
     */
    public static function forceDisableHeadless($opts): array
    {
        if (!is_array($opts)) {
            $opts = [];
        }

        // Force the flag off
        $opts['enable_headless_rest_endpoints'] = false;

        return $opts;
    }
}
