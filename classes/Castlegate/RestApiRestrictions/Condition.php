<?php

declare(strict_types=1);

namespace Castlegate\RestApiRestrictions;

final class Condition
{
    /**
     * Check if the current user is logged in
     *
     * @return bool
     */
    public static function isLoggedIn(): bool
    {
        return apply_filters('cgit-rest-api-restrictions/condition/is_user_logged_in', is_user_logged_in());
    }

    /**
     * Check if a user has a role that deems them a privileged user
     *
     * @return bool
     */
    public static function isPrivilegedUser(): bool
    {
        $default_roles = [
            'administrator',
            'wpseo_editor',
            'wpseo_manager',
            'cgit_site_manager',
            'shop_manager',
            'editor',
        ];

        $roles = apply_filters('cgit-rest-api-restrictions/config/privileged_user_roles', $default_roles);

        $result = self::hasRole($roles);

        return apply_filters('cgit-rest-api-restrictions/condition/is_privileged_user', $result);
    }

    /**
     * Check if a user has a specific role
     *
     * @param array $required_roles
     * @return bool
     */
    public static function hasRole(array $required_roles): bool
    {
        // Perform a login check
        if (!is_user_logged_in()) {
            return false;
        }

        $user = wp_get_current_user();

        $roles = (array) $user->roles;

        $required_roles = array_map('strval', $required_roles);

        foreach ((array) $user->roles as $role) {
            if (in_array((string) $role, $required_roles, true)) {
                return true;
            }
        }

        return false;
    }
}