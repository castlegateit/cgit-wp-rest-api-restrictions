<?php

declare(strict_types=1);

namespace Castlegate\RestApiRestrictions;

use ReflectionFunction;

final class AdminPage
{
    /**
     * Parent menu page slug
     *
     * @var string
     */
    private const PARENT = 'options-general.php';

    /**
     * Sub-menu page title
     *
     * @var string
     */
    private const TITLE = 'REST API Restrictions';

    /**
     * The default capability to view the admin page and change settings
     *
     * @var string
     */
    public const ADMIN_CAPABILITY = 'manage_options';

    /**
     * REST API routes before plugin modifications
     *
     * @var array
     */
    private static array $routes = [];

    /**
     * Initialisation
     *
     * @return void
     */
    public static function init(): void
    {
        // Register the submenu page
        add_action('admin_menu', [__CLASS__, 'register']);

        // Don't load anything else unless required
        if (!self::isAdminPage()) {
            return;
        }

        // Fetch the REST routes for use on the admin page
        // Must be run after other "init" actions in the plugin
        add_action('init', [__CLASS__, 'fetchEarlyRoutes'], 99998);

        // Enqueue admin page scripts
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueueStyles']);
    }

    /**
     * Register sub-menu page
     *
     * @return void
     */
    public static function register(): void
    {
        add_submenu_page(
            self::PARENT,
            self::TITLE,
            self::TITLE,
            self::getAdminCapability(),
            Plugin::NAME,
            [__CLASS__, 'render']
        );
    }

    /**
     * Capture early REST API routes before the plugin makes any changes
     *
     * @return void
     */
    public static function fetchEarlyRoutes(): void
    {
        self::$routes = rest_get_server()->get_routes();
    }

    /**
     * Get all routes loaded by the plugin
     *
     * @return array
     */
    public static function getAllRoutes(): array
    {
        return self::$routes;
    }

    /**
     * Render sub-menu page
     *
     * @return void
     */
    public static function render(): void
    {
        $all_routes = array_keys(self::$routes);

        include realpath(CGIT_WP_REST_API_RESTRICTIONS_PLUGIN_DIR . '/views/page.php');
    }

    /**
     * Enqueue styles
     *
     * @return void
     */
    public static function enqueueStyles(): void
    {
        wp_enqueue_style(
            'cgit-wp-rest-api-restrictions-style',
            path_join(CGIT_WP_REST_API_RESTRICTIONS_URL, 'assets/css/style.css'),
            [],
            CGIT_WP_REST_API_RESTRICTIONS_VERSION
        );
    }

    /**
     * Role required to access the admin page and change settings
     *
     * @return string
     */
    public static function getAdminCapability(): string
    {
        return apply_filters(
            'cgit-rest-api-restrictions/admin-capability',
            self::ADMIN_CAPABILITY
        );
    }

    /**
     * Check if the current page is the plugin page
     *
     * @return bool
     */
    public static function isAdminPage(): bool
    {
        $page = isset($_GET['page']) ? sanitize_key((string) $_GET['page']) : '';

        return $page === Plugin::NAME;
    }

    /**
     * Convert a PHP callable into a readable PHP-style string.
     *
     * Examples:
     *   - 'my_function'                     → 'my_function()'
     *   - ['MyClass', 'myMethod']           → 'MyClass::myMethod()'
     *   - [new MyClass, 'myMethod']         → 'MyClass->myMethod()'
     *   - static::class, 'myMethod'         → 'MyClass::myMethod()'
     *   - Closure                           → 'Closure'
     *   - Invokable object                  → 'MyClass::__invoke()'
     * @throws \ReflectionException
     */
    public static function formatCallable(mixed $callback): string
    {
        // Simple function name string
        if (is_string($callback)) {
            return $callback . '()';
        }

        // Static method reference [ClassName, 'method']
        if (is_array($callback) && count($callback) === 2) {
            [$objectOrClass, $method] = $callback;

            if (is_object($objectOrClass)) {
                return get_class($objectOrClass) . '->' . $method . '()';
            }

            if (is_string($objectOrClass)) {
                return $objectOrClass . '::' . $method . '()';
            }
        }

        // Closure
        if ($callback instanceof Closure) {
            $ref = new ReflectionFunction($callback);
            $location = $ref->getFileName() . ':' . $ref->getStartLine();
            return 'Closure(' . basename($location) . ')';
        }

        // Invokable object
        if (is_object($callback) && method_exists($callback, '__invoke')) {
            return get_class($callback) . '::__invoke()';
        }

        // Fallback
        return 'Unknown callback type';
    }
}