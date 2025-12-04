<?php

use Castlegate\RestApiRestrictions\AdminPage;
use Castlegate\RestApiRestrictions\Config;
use Castlegate\RestApiRestrictions\Notification;
use Castlegate\RestApiRestrictions\Plugin;

$capability = AdminPage::getAdminCapability();

if (!current_user_can($capability)) {
    wp_die('Access denied');
}

?>
<div class="wrap cgit-wp-rest-api-restrictions">

    <h1>REST API Restrictions</h1>

    <h2>Features</h2>

    <?php if (count(Notification::getAll()) > 0) : ?>
        <div class="cgit-wp-rest-api-restrictions__notifications">
            <?php foreach (Notification::getAll() as $notification) : ?>
                <div class="cgit-wp-rest-api-restrictions__notification cgit-wp-rest-api-restrictions-color--<?= $notification->active() ? 'success' : 'warning' ?>">
                    <p><span class="cgit-wp-rest-api-restrictions__tag cgit-wp-rest-api-restrictions__tag--<?= $notification->active() ? 'active' : 'inactive' ?>"><?= $notification->active() ? 'Active' : 'Inactive' ?></span> <strong><?= esc_html($notification->title()) ?></strong></p>
                    <p><?= esc_html($notification->description()) ?></p>
                </div>
            <?php endforeach ?>
        </div>
    <?php endif; ?>

    <h2>Configuration</h2>

    <div class="metabox-holder">
        <div class="postbox-container cgit-wp-rest-api-restrictions__col cgit-wp-rest-api-restrictions__col-left">
            <div class="postbox">
                <div class="inside cgit-wp-rest-api-restrictions__mb-0">
                    <h3>Remove configuration</h3>

                    <?php if (count(Config::getRemoveRoute()) > 0) : ?>
                        <ul class="cgit-wp-rest-api-restrictions__list cgit-wp-rest-api-restrictions__mb-0">
                            <?php foreach (Config::getRemoveRoute() as $config) : ?>
                                <li><code class="cgit-wp-rest-api-restrictions-color--danger"><?= esc_html($config) ?></code></li>
                            <?php endforeach ?>
                        </ul>
                    <?php else : ?>
                        <p style="cgit-wp-rest-api-restrictions__mb-0"><em>No configuration rules found.</em></p>
                    <?php endif ?>
                </div>
            </div>
        </div>
        <div class="postbox-container cgit-wp-rest-api-restrictions__col cgit-wp-rest-api-restrictions__col-right">
            <div class="postbox">
                <div class="inside cgit-wp-rest-api-restrictions__mb-0">
                    <h3>Callback configuration</h3>
                    <?php if (count(Config::getAddCallbackRoute()) > 0) : ?>
                        <ul class="cgit-wp-rest-api-restrictions__list cgit-wp-rest-api-restrictions__mb-0">
                            <?php foreach (Config::getAddCallbackRoute() as $route_pattern => $config) : ?>
                                <li>
                                    <code class="cgit-wp-rest-api-restrictions-color--warning"><?= esc_html($route_pattern) ?></code>
                                    <code><?= esc_html(AdminPage::formatCallable($config)) ?></code>
                                </li>
                            <?php endforeach ?>
                        </ul>
                    <?php else : ?>
                        <p style="cgit-wp-rest-api-restrictions__mb-0"><em>No configuration rules found.</em></p>
                    <?php endif ?>
                </div>
            </div>
        </div>
        <hr>
        <div class="postbox-container cgit-wp-rest-api-restrictions__col cgit-wp-rest-api-restrictions__col">
            <div class="postbox">
                <div class="inside cgit-wp-rest-api-restrictions__mb-0">
                    <h3>Route overview</h3>
                    <?php if (count(AdminPage::getAllRoutes()) > 0) : ?>
                        <ul class="cgit-wp-rest-api-restrictions__list cgit-wp-rest-api-restrictions__mb-0">
                            <?php foreach (AdminPage::getAllRoutes() as $route_pattern => $config) : ?>
                                <?php
                                    $color = 'neutral';
                                    $callback = null;
                                    if (Plugin::shouldRemoveEndpoint($route_pattern)) {
                                        $color = 'danger';
                                    } else {
                                        $callback = Plugin::fetchCallback($route_pattern);
                                        if ($callback) {
                                            $color = 'warning';
                                        }
                                    }
                                ?>
                                <li>
                                    <code class="cgit-wp-rest-api-restrictions-color--<?= esc_attr($color) ?>">
                                        <?= esc_html($route_pattern) ?>
                                    </code>
                                    <?php if ($callback) : ?>
                                        - <code><?= esc_html(AdminPage::formatCallable($callback)) ?></code>
                                    <?php endif ?>
                                </li>
                            <?php endforeach ?>
                        </ul>
                    <?php else : ?>
                        <p style="cgit-wp-rest-api-restrictions__mb-0"><em>No remaining routes found.</em></p>
                    <?php endif ?>
                </div>
            </div>
        </div>
    </div>
</div>
