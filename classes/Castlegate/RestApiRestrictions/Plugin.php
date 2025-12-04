<?php

declare(strict_types=1);

namespace Castlegate\RestApiRestrictions;

use Castlegate\RestApiRestrictions\Features\PostType;
use Castlegate\RestApiRestrictions\Features\Yoast;
use WP_REST_Request;

final class Plugin
{
    /**
     * Plugin name
     *
     * @var string
     */
    public const NAME = 'cgit-wp-rest-api-restrictions';

    /**
     * Default configuration option to prevent the `Link` header from being
     * sent in non-REST-API request responses
     *
     * @var bool
     */
    private const CONFIG_DISABLE_LINK_HEADER = true;

    /**
     * Default configuration option to disable references to the REST API in
     * the `<head>` tag
     *
     * @var bool
     */
    private const CONFIG_DISABLE_HEAD_REFERENCES = true;

    /**
     * Default configuration option to harden Yoast
     *
     * @var bool
     */
    private const CONFIG_HARDEN_YOAST = true;

    /**
     * Default configuration option to disable REST API related headers in
     * HTTP responses
     *
     * @var bool
     */
    private const CONFIG_DISABLE_HEADERS = true;

    /**
     * Initialise
     *
     * @return void
     */
    public static function init(): void
    {
        AdminPage::init();
        Config::init();
        Yoast::init();
        PostType::init();

        // Harden post and page endpoints
        if (PostType::active()) {
            PostType::run();
        }

        // Harden Yoast
        if (Yoast::active()) {
            Yoast::run();
        }

        // Disable the `Link` header
        if (self::disableLinkHeader()) {
            add_action('init', [__CLASS__, 'doDisableLinkHeader']);
        }

        // Disable REST API <head> references
        if (self::disableHeadReferences()) {
            add_action('init', [__CLASS__, 'doDisableHeadTagReferences']);
        }

        // Disable REST API response headers
        if (self::disableHeaders()) {
            add_action('init', [__CLASS__, 'doDisableHeaders']);
        }

        // Apply restrictions
        add_action('init', [__CLASS__, 'applyRestRestrictions'], 99999);
    }

    /**
     * Apply restrictions to disable or restrict REST API routes
     *
     * @return void
     */
    public static function applyRestRestrictions(): void
    {
        // Restrict REST API endpoints based on our configuration
        add_filter(
            'rest_endpoints',
            [get_called_class(), 'removeEndpoints'],
            99999, 1
        );

        add_filter(
            'rest_dispatch_request',
            [get_called_class(), 'addEndpointCallbacks'],
            99999, 4
        );
    }

    /**
     * Filters the REST API endpoints to remove any that we've configured
     *
     * @param array $endpoints
     * @return array
     */
    public static function removeEndpoints(array $endpoints): array
    {
        // Remove any wildcard matches
        foreach ($endpoints as $route_pattern => $callback) {
            if (self::shouldRemoveEndpoint($route_pattern)) {
                unset($endpoints[$route_pattern]);
            }
        }

        return $endpoints;
    }

    /**
     * Check if an endpoint should be removed due to an exact march or regex
     * match
     *
     * @param string $route_pattern
     * @return bool
     */
    public static function shouldRemoveEndpoint(string $route_pattern): bool
    {
        // Check for exact matches
        if (in_array($route_pattern, Config::getRemoveRoute())) {
            return true;
        }

        // Compare the endpoint route against our own configuration
        return in_array($route_pattern, Config::getRemoveRoute(), true);
    }

    /**
     * Filter the REST request response to add in additional role-based checks
     *
     * @param mixed $dispatch_result
     * @param WP_REST_Request $request
     * @param string $route_pattern
     * @param array $handler
     * @return mixed
     */
    public static function addEndpointCallbacks(mixed $dispatch_result, WP_REST_Request $request, string $route_pattern, array $handler): mixed
    {
        // Fetch the callback for the route pattern
        $callback = self::fetchCallback($route_pattern);

        // Abandon and return the default response if no matching callback found
        if (false === $callback) {
            return null;
        }

        // Check the callback is callable
        if (!is_callable($callback)) {
            _doing_it_wrong(
                __METHOD__,
                sprintf('Invalid callback for route %s', $route_pattern),
                CGIT_WP_REST_API_RESTRICTIONS_VERSION
            );

            return Error::misconfiguration();
        } else {
            return $callback($request, $route_pattern, $handler);
        }
    }

    /**
     * Check if a callback should be added for a route. Returns the callback
     * if there's a match, else false
     *
     * @param string $route_pattern
     * @return mixed
     */
   public static function fetchCallback(string $route_pattern): mixed
    {
        // Get the configuration
        $config = Config::getAddCallbackRoute();

        // Check our config for any match
        foreach ($config as $config_route_pattern => $callback) {
            if ($config_route_pattern === $route_pattern) {
                return $callback;
            }
        }

        return false;
    }

    /**
     * Check if we should disable all references to the REST API in the
     * `<head>` tag
     *
     * @return bool
     */
    public static function disableHeadReferences(): bool
    {
        $default = self::CONFIG_DISABLE_HEAD_REFERENCES;

        return apply_filters('cgit-rest-api-restrictions/config/disable-head-references', $default);
    }

    /**
     * Check if we should disable all REST API related headers in HTTP responses
     *
     * @return bool
     */
    public static function disableHeaders(): bool
    {
        $default = self::CONFIG_DISABLE_HEADERS;

        return apply_filters('cgit-rest-api-restrictions/config/disable-headers', $default);
    }

    /**
     * Disable all references to the REST API in the `<head>` tag
     *
     * @return void
     */
    public static function doDisableHeadTagReferences(): void
    {
        // Prevent outputting the REST API link tag into page header
        remove_action(
            'wp_head',
            'rest_output_link_wp_head'
        );

        // Prevent adding oEmbed discovery links in the head element
        remove_action(
            'wp_head',
            'wp_oembed_add_discovery_links'
        );

        remove_action(
            'wp_head',
            'wp_oembed_add_host_js'
        );
    }
    /**
     * Disable all references to the REST API in the response headers
     *
     * @return void
     */
    public static function doDisableHeaders(): void
    {
        remove_action(
            'template_redirect',
            'rest_output_link_header',
            11
        );
    }

    /**
     * Check if we should prevent the `Link` header from being sent with all
     * request responses
     *
     * @return bool
     */
    public static function disableLinkHeader(): bool
    {
        $default = self::CONFIG_DISABLE_LINK_HEADER;

        return apply_filters('cgit-rest-api-restrictions/config/disable-link-header', $default);
    }

    /**
     * Prevent the `Link` header from being sent with all request responses
     *
     * @return void
     */
    public static function doDisableLinkHeader(): void
    {
        remove_action(
            'template_redirect',
            'rest_output_link_header',
            11, 0
        );
    }
}