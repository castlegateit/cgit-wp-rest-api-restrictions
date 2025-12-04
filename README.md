# Castlegate IT REST API Restrictions

The Castlegate REST API Restrictions plugin prevents sensitive REST API 
endpoints from being accessible by default, and includes filters to allow 
customisation of its default rule set. 

It includes the following features:
 - Complete removal of endpoints
 - Additional callbacks to apply extra conditions before serving responses
 - Removal of user data in post-type endpoints
 - Disables the Yoast Headless WordPress REST API endpoint which exposes author
information
 - Disables the "Link" header that exposes endpoints in page HTTP requests
 - Removes REST API references in the `<head>` tag

## Default configuration

- Endpoints removed:
  - `/oembed/1.0/embed`

- Additional callbacks
  - `/wp/v2/users` Requires a privileged user
  - `/wp/v2/users/(?P<id>[\d]+)` Requires a privileged user
  - `/wp/v2/posts` Requires a privileged user
  - `/wp/v2/posts/(?P<id>[\d]+)` Requires a privileged user
  - `/wp/v2/pages` Requires a privileged user
  - `/wp/v2/pages/(?P<id>[\d]+)` Requires a privileged user

- Features
  - Yoast Headless-WordPress REST API disabled
  - REST API HTTP headers are removed
  - REST API references in the `<head>` tag are removed
  - Author details are stripped from post-type REST API responses

## Endpoint removal

Endpoints can be removed to prevent an route from being accessible. The plugin
options page can be found in `Settings > REST API Restrictions` and will show
all available REST API route regular expressions.

Endpoints are configured by matching the exact regular expression used to 
define the route.

For exmaple:

 - /wp/v2/menu-items
 - /wp/v2/menu-items/(?P<id>[\d]+)
 - /wp/v2/menu-items/(?P<id>[\d]+)/autosaves
 - /wp/v2/menu-items/(?P<parent>[\d]+)/autosaves/(?P<id>[\d]+)

You can target these routes via a direct comparison or with another regular expression.

**Filter**
```
add_filter(
    'cgit-rest-api-restrictions/remove-route',
    [
        '/wp/v2/menu-items/(?P<id>[\d]+)'
    ]
);
```

**Result**

- /wp/v2/menu-items
- ~~/wp/v2/menu-items/(?P<id>[\d]+)~~
- /wp/v2/menu-items/(?P<id>[\d]+)/autosaves
- /wp/v2/menu-items/(?P<parent>[\d]+)/autosaves/(?P<id>[\d]+)

## Additional callbacks

Additional callbacks can be configured to run before the final REST API response
is sent. These run after the default permission checks, and so are used to apply
an additional condition to the request. These do not change the default 
permission checks.

The plugin options page can be found in `Settings > REST API Restrictions` and 
will show all available REST API route regular expressions.

Additional callbacks are configured by matching the exact regular expression 
used to define the route, along with a suitable callback method.

The plugin comes with some default callbacks that you may utilise, or you may 
create your own.

**Filter**
```
add_filter(
    'cgit-rest-api-restrictions/add-callback-route',
    [
        '/wp/v2/users/(?P<id>[\d]+)' => ['MyClass', 'my_method']
    ]
);
```

**Result**

When a request comes in that matches the endpoint regular expression, our
additional callback is executed.

- Example URL: `/wp/v2/users/1`

Callback utilised: ```MyClass::my_method```

## Creating callbacks

Callbacks should be defined with the following arguments

`function (WP_REST_Request $request, string $route_pattern, array $handler): WP_REST_Response|WP_Error|null`

Callbacks must return:
 - A `WP_REST_Response` object for sending a different response
 - A `WP_Error` object if you wish to send an error response
 - Or `null` if you wish to allow the original response to be sent

## Filters

### Disable head tag references

The REST API `<head>` references are disabled by default. You can turn off this 
feature to re-enable them using the following filter:

```php
add_filter('cgit-rest-api-restrictions/config/disable-head-references', '__return_false');
```

### Disable REST API headers

REST API headers in HTTP responses are disabled by default. You can turn off 
this feature and re-enable them using the following filter:

```php
add_filter('cgit-rest-api-restrictions/config/disable-headers', '__return_false');
```

### Disable REST API Link header

The REST API `Link` header in HTTP responses is disabled by default. You can 
turn off this feature and re-enable it using the following filter:

```php
add_filter('cgit-rest-api-restrictions/config/disable-link-header', '__return_false');
```

### Admin capability

The capability required to view the `Settings > REST API Restrictions` settings
page is `manage_options`.

This can be customised with the following filter:

```php
add_filter('cgit-rest-api-restrictions/admin-capability', function($capability) {
    return 'manage_users';
});
```
