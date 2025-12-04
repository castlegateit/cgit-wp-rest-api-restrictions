<?php

declare(strict_types=1);

namespace Castlegate\RestApiRestrictions;

use WP_Error;

final class Error
{
    /**
     * Returns a WP_Error instance for when a user is not authenticated
     *
     * @return WP_Error
     */
    public static function misconfiguration(): WP_Error
    {
        $code = 'rest_configuration_error';
        $message = __('Endpoint misconfigured', Plugin::NAME);
        $data = ['status' => 500];

        return self::error($code, $message, $data);
    }

    /**
     * Returns a WP_Error instance for when a user is not authenticated
     *
     * @return WP_Error
     */
    public static function notAuthenticated(): WP_Error
    {
        $code = 'rest_not_logged_in';
        $message = __('Unauthenticated request', Plugin::NAME);
        $data = ['status' => rest_authorization_required_code()];

        return self::error($code, $message, $data);
    }

    /**
     * Returns a WP_Error instance for when a user is missing a required role
     *
     * @return WP_Error
     */
    public static function insufficientPermissions(): WP_Error
    {
        $code = 'rest_insufficient_permissions';
        $message = __('Sorry, you do not have the required permissions.', Plugin::NAME);
        $data = ['status' => rest_authorization_required_code()];

        return self::error($code, $message, $data);
    }

    /**
     * Returns a filterable WP_Error instance
     *
     * @param string $code
     * @param string $message
     * @param array $data
     * @return WP_Error
     */
    private static function error(
        string $code,
        string $message,
        array $data
    ): WP_Error
    {
        $code = apply_filters(
            'cgit-rest-api-restrictions/error/'.$code.'/code',
            $code
        );

        $message = apply_filters(
            'cgit-rest-api-restrictions/error/'.$code.'/message',
            $message
        );

        $data = apply_filters(
            'cgit-rest-api-restrictions/error/'.$code.'/data',
            $data
        );

        return new WP_Error($code, $message, $data);
    }
}