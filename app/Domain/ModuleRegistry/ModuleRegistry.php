<?php

namespace App\Domain\ModuleRegistry;

use Nwidart\Modules\Facades\Module;

/**
 * Capability map: platform capability -> module gate.
 *
 * Core domains must NEVER hard-code module names (golden rule R3). Instead
 * they ask "is the bundle/course/quiz capability available?" via
 * ModuleRegistry::capabilityEnabled('course_bundles'); ModuleRegistry keeps the
 * single map and can route a capability to a NoOp/alternate provider without
 * touching domain code.
 */
final class ModuleRegistry
{
    /** capability => module facade key */
    private const CAPABILITY_MODULES = [
        'course_bundles' => 'CourseBundles',
        'course' => 'Courses',
        'quiz' => 'Quiz',
        'meetings' => 'MeetFusion',
        'meet_fusion' => 'MeetFusion',
        'commerce' => 'LaraPayease',
        'payments' => 'LaraPayease',
        'subscriptions' => 'Subscriptions',
        'notifications' => 'Notifications',
    ];

    public static function capabilityModule(string $capability): ?string
    {
        return self::CAPABILITY_MODULES[strtolower($capability)] ?? null;
    }

    /**
     * Is the capability available (module present AND enabled)?
     * Unknown capabilities default to disabled (fail closed) so domains never
     * silently take a "present" path for a feature we no longer ship.
     */
    public static function capabilityEnabled(string $capability): bool
    {
        $module = self::capabilityModule($capability);
        if ($module === null) {
            return false;
        }

        return Module::has($module) && Module::isEnabled($module);
    }

    /**
     * Short-hand mirroring the legacy "Module has + enabled" pattern used at
     * old call sites: expectedModule is the module key.
     */
    public static function hasAndEnabled(string $module): bool
    {
        return Module::has($module) && Module::isEnabled($module);
    }
}
