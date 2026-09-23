<?php

namespace App\Domain\Principal;

use App\Models\User;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Session\Session;
use Spatie\Permission\Models\Role;

/**
 * Resolves the active Principal for the current context.
 *
 * Order of resolution:
 *  1. Explicit web Session role (active role switching) — session-coupled,
 *     shimmed for BC.
 *  2. Queue/CLI — system principal, never a guessed role.
 *  3. Fallback — user's stored default_role.
 *
 * One Principal per request / queue batch (cached in instance state, reset via
 * Principal::resetBetweenJobs() at the start of queue payloads).
 */
final class PrincipalResolver
{
    private ?Principal $resolved = null;

    public function __construct(
        private readonly Container $container,
        private readonly AuthFactory $auth,
    ) {}

    public function resolve(?User $user = null): Principal
    {
        if ($user === null && $this->resolved !== null) {
            return $this->resolved;
        }

        $user = $user ?? $this->auth->guard()->user();

        if (! $user instanceof User) {
            return $this->resolved = Principal::system(self::detectContext());
        }

        $role = $this->resolveRole($user);

        return $this->resolved = Principal::forUser(
            (int) $user->getKey(),
            $role,
            self::detectContext(),
        );
    }

    public function reset(): void
    {
        $this->resolved = null;
    }

    private function resolveRole(User $user): ?string
    {
        // Queue/CLI: never read the web session; system principal instead.
        if ($this->runningInConsole()) {
            return null;
        }

        $activeRoleId = $this->session()->get('active_role_id'.$user->getKey());

        if (! empty($activeRoleId)) {
            $role = Role::find($activeRoleId)?->name;

            if ($role !== null) {
                return $role;
            }
        }

        return $user->default_role;
    }

    private function session(): Session
    {
        return $this->container->make(Session::class);
    }

    private function runningInConsole(): bool
    {
        return $this->container->make('app')->runningInConsole();
    }

    private static function detectContext(): string
    {
        if (app()->runningInConsole()) {
            return Principal::CONTEXT_CLI;
        }

        $request = request();
        if ($request instanceof Request && $request->bearerToken() !== null) {
            return Principal::CONTEXT_API;
        }

        return Principal::CONTEXT_WEB;
    }
}
