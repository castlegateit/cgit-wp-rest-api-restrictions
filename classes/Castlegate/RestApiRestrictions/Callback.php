<?php

namespace Castlegate\RestApiRestrictions;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

final class Callback
{
    /**
     * A sample callback method for ensuring that a route is only accessible by
     * a privileged user. Returns a custom response or null to allow the request
     * to execute as normal
     *
     * @param WP_REST_Request $request
     * @param string $route_pattern
     * @param array $handler
     * @return WP_REST_Response|WP_Error|null
     */
    public static function requirePrivilegedUser(WP_REST_Request $request, string $route_pattern, array $handler): WP_REST_Response|WP_Error|null
    {
        if (!Condition::isLoggedIn()) {
            return Error::notAuthenticated();
        }

        if (Condition::isPrivilegedUser()) {
            return null;
        }

        return Error::insufficientPermissions();
    }

    /**
     * A sample callback method for `/wp-json/wp/v2/users/1?context=edit`
     * requests ensuring that the route is only accessible by a privileged user
     * or the user themselves. Returns a custom response or null to allow the
     * request to execute as normal
     *
     * @param WP_REST_Request $request
     * @param string $route_pattern
     * @param array $handler
     * @return WP_REST_Response|WP_Error|null
     */
    public static function userRequestRequirePrivilegedOrSelf(WP_REST_Request $request, string $route_pattern, array $handler): WP_REST_Response|WP_Error|null
    {
        if (!Condition::isLoggedIn()) {
            return Error::notAuthenticated();
        }

        // Get the request context and ID
        $context = $request->get_param('context');
        $url_id  = isset($request->get_url_params()['id']) ? intval($request->get_url_params()['id']) : 0;

        // If the user is requesting their own user data with context=edit, allow it
        if ($context === 'edit' && $url_id === get_current_user_id()){
            return null;
        }

        if (Condition::isPrivilegedUser()) {
            return null;
        }

        return Error::insufficientPermissions();
    }

    /**
     * A sample callback method for ensuring that a route is only accessible by
     * any authenticated user. Returns a custom response or null to allow the
     * request to execute as normal
     *
     * @param WP_REST_Request $request
     * @param string $route_pattern
     * @param array $handler
     * @return WP_REST_Response|WP_Error|null
     */
    public static function requireAuthenticatedUser(WP_REST_Request $request, string $route_pattern, array $handler): WP_REST_Response|WP_Error|null
    {
        if (!Condition::isLoggedIn()) {
            return Error::notAuthenticated();
        }

        return null;
    }
}