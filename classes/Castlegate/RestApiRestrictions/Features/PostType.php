<?php

declare(strict_types=1);

namespace Castlegate\RestApiRestrictions\Features;

use Castlegate\RestApiRestrictions\FeatureInterface;
use Castlegate\RestApiRestrictions\Notification;
use Castlegate\RestApiRestrictions\Plugin;
use WP_Post;
use WP_REST_Request;
use WP_REST_Response;

final class PostType implements FeatureInterface
{
    /**
     * Feature title
     *
     * @var string
     */
    public static string $title = 'Post Type Protection';

    /**
     * Feature description
     *
     * @var string
     */
    public static string $description = 'This feature removes author details from post, page and custom post type endpoints. By default, WordPress provides links to related resources in post, page and custom post type endpoints, potentially exposing username.';

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
     * Run the post-related REST restrictions
     *
     * @return void
     */
    public static function run(): void
    {
        // Attach to all REST enabled post-types present at init
        add_action('init', [__CLASS__, 'attachToExistingTypes']);

        // Also catch post-types registered later by other plugins
        add_action('registered_post_type', [__CLASS__, 'onRegisteredPostType'], 10, 2);
    }

    /**
     * Check if we should redact author details in post-feeds
     *
     * @return bool
     */
    public static function active(): bool
    {
        return apply_filters('cgit-rest-api-restrictions/config/enable-feature-post-type', true);
    }

    /**
     * Permission check to determine if authors should be redacted for the
     * current user or not
     *
     * @return mixed|null
     */
    public static function maybeRedactAuthors()
    {
        $redact = !current_user_can('list_users');

        return apply_filters('cgit-rest-api-restrictions/config/redact-post-authors-check', $redact);
    }

    /**
     * Attach our redaction checks to existing post-types
     *
     * @return void
     */
    public static function attachToExistingTypes(): void
    {
        $post_types = get_post_types(['show_in_rest' => true], 'names');

        foreach ($post_types as $post_type) {
            // Allow overriding author redaction on a per post-type basis
            $enable = apply_filters('cgit-rest-api-restrictions//config/redact-post-authors-'.$post_type, true);

            // Allow overriding of the required permissions to view authors on a per post-type basis
            $redact = apply_filters('cgit-rest-api-restrictions/config/redact-post-authors-permission-'.$post_type, self::maybeRedactAuthors());

            if ($enable && $redact) {
                add_filter("rest_prepare_".$post_type, [__CLASS__, 'redactPost'], 10, 3);
            }
        }
    }

    /**
     * Attach our redaction checks to post-types that are not registered at the
     * time when the plugin runs
     *
     * @param string $post_type
     * @param object $args
     * @return void
     */
    public static function onRegisteredPostType(string $post_type, object $args): void
    {
        if (!empty($args->show_in_rest)) {
            // Allow overriding author redaction on a per post-type basis
            $enable = apply_filters('cgit-rest-api-restrictions/config/redact-post-authors-'.$post_type, true);

            // Allow overriding of the required permissions to view authors on a per post-type basis
            $redact = apply_filters('cgit-rest-api-restrictions/config/redact-post-authors-permission-'.$post_type, self::maybeRedactAuthors());

            if ($enable && $redact) {
                add_filter("rest_prepare_{$post_type}", [__CLASS__, 'redactPost'], 10, 3);
            }
        }
    }

    /**
     * Redact author information on any post like REST response.
     *
     * @param mixed $response WP_REST_Response expected
     * @param WP_Post $post
     * @param WP_REST_Request $request
     * @return mixed
     */
    public static function redactPost($response, $post, $request)
    {
        if (!$response instanceof WP_REST_Response) {
            return $response;
        }

        // Remove the author from `_links` which also prevents it being included
        // in `_embedded`
        $response = self::redactPostAuthorLink($response);

        $data = $response->get_data();

        $response->set_data($data);

        return $response;
    }

    /**
     * Remove the author item from `_links` in a post REST API response. This
     * also prevents it from being included in an `_embed` call.
     *
     * @param WP_REST_Response $response
     * @return WP_REST_Response
     */
    private static function redactPostAuthorLink(WP_REST_Response $response): WP_REST_Response
    {
        if (method_exists($response, 'remove_link')) {
            $response->remove_link('author');

            return $response;
        }

        return $response;
    }
}
