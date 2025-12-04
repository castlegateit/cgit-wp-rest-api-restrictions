<?php

namespace Castlegate\RestApiRestrictions;

final class Notification
{
    /**
     * Store all notifications
     *
     * @var array
     */
    private static array $notifications = [];

    /**
     * Notification title
     *
     * @var string
     */
    private string $title = '';

    /**
     * Notification description
     *
     * @var string
     */
    private string $description = '';

    /**
     * Feature active status
     *
     * @var bool
     */
    private bool $active = false;

    /**
     * Add a notification to the list
     *
     * @param string $title
     * @param string $description
     * @param bool $active
     */
    public function __construct(string $title, string $description, bool $active = false)
    {
        $this->title = $title;
        $this->description = $description;
        $this->active = $active;
    }

    /**
     * Return the reference
     *
     * @return string
     */
    public function reference(): string
    {
        return $this->ref;
    }

    /**
     * Return the title
     *
     * @return string
     */
    public function title(): string
    {
        return $this->title;
    }

    /**
     * Return the description
     *
     * @return string
     */
    public function description(): string
    {
        return $this->description;
    }

    /**
     * Return whether the feature is active or not
     *
     * @return bool
     */
    public function active(): bool
    {
        return $this->active;
    }

    /**
     * Add a new notification to the list
     *
     * @param string $title
     * @param string $description
     * @param bool $active
     * @return void
     */
    public static function add(string $title, string $description, bool $active = false): void
    {
        $notification = new Notification($title, $description, $active);

        self::$notifications[] = $notification;
    }

    /**
     * Fetch all notifications
     *
     * @return array
     */
    public static function getAll(): array
    {
        return self::$notifications;
    }
}