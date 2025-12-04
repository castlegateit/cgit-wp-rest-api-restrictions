<?php

declare(strict_types=1);

namespace Castlegate\RestApiRestrictions;

final class Config
{
    /**
     * Configuration of routes to be removed
     *
     * @var array|string[]
     */
    private static array $remove_route = [
        '/oembed/1.0/embed',
    ];

    /**
     * Configuration of routes to have additional callbacks
     *
     * @var array|string[]
    */
    private static array $add_callback_route = [
        '/wp/v2/users' => ['Castlegate\RestApiRestrictions\Callback', 'requirePrivilegedUser'],
        '/wp/v2/users/(?P<id>[\d]+)' => ['Castlegate\RestApiRestrictions\Callback', 'userRequestRequirePrivilegedOrSelf'],
        '/wp/v2/posts' => ['Castlegate\RestApiRestrictions\Callback', 'requirePrivilegedUser'],
        '/wp/v2/posts/(?P<id>[\d]+)' => ['Castlegate\RestApiRestrictions\Callback', 'requirePrivilegedUser'],
        '/wp/v2/pages' => ['Castlegate\RestApiRestrictions\Callback', 'requirePrivilegedUser'],
        '/wp/v2/pages/(?P<id>[\d]+)' => ['Castlegate\RestApiRestrictions\Callback', 'requirePrivilegedUser'],
    ];

    /**
     * Initialise the configuration
     *
     * @return void
     */
    public static function init(): void
    {
        // Apply configuration for removal for exact routes
        self::$remove_route = apply_filters('cgit-rest-api-restrictions/remove-route', self::$remove_route);

        // Apply configuration for additional callbacks on exact routes
        self::$add_callback_route = apply_filters('cgit-rest-api-restrictions/add-callback-route', self::$add_callback_route);
    }

    /**
     * Get routes to remove
     *
     * @return array
     */
    public static function getRemoveRoute(): array
    {
        return self::$remove_route;
    }

    /**
     * Get routes to have additional callbacks
     *
     * @return array
     */
    public static function getAddCallbackRoute(): array
    {
        return self::$add_callback_route;
    }
}