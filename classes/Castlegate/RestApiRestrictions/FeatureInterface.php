<?php

namespace Castlegate\RestApiRestrictions;

interface FeatureInterface
{
    /**
     * Initialise the functionality
     *
     * @return void
     */
    public static function init(): void;

    /**
     * Load the functionality
     *
     * @return void
     */
    public static function run(): void;

    /**
     * Check the feature's status
     *
     * @return bool
     */
    public static function active(): bool;
}