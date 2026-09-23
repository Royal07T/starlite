<?php

namespace App\Domain\Principal;

use App\Models\User;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;

/**
 * Resolves the active Principal for the current context.
 *
 * Order of resolution:
 *  1. If the Request carries an explicit role claim (API), use it.
 *  2. For web sessions which store an "active role" (multi-role switching),
 *     read that from the session.
 *  3. Otherwise fall back to the user's stored default_role.
 *
 * No hidden globals: one Principal per request, resolved once and cached
 * (reset via reset() at the start of queue/console work).
 */
class PrincipalResolver
{
    private ?Principal $resolved = null;

    public function __construct(
        private readonly Container $container,
        private readonly AuthFactory $auth,
    ) {
    }

    public function resolve(?User $user = null): Principal
    {
        if ($this->resolved !== null && $user === null) {
            return $this->resolved;
        }

        $context = $this->detectContext();

        $user = $user ?? $this->auth->guard()->user();

        if (! $user instanceof User) {
            return $this->resolved = Principal::system($context);
        }

        if ($context === Principal::CONTEXT_WEB
            && $this->container->bound(Session::class)
            && ($session = $this->container->make(Session::class)) !== null
            && ! empty($activeRoleId = $session->get('active_role_id' . $user->getKey()))
        ) {
            $role = \Spatie\Permission\Models\Role::find($activeRoleId)?->name;
            if ($role !== null) {
                return $this->resolved = Principal::forUser((int) $user->getKey(), $role, $context);
            }
        }

        return $this->resolved = Principal::forUser((int) $user->getKey(), $user->default_role, $context);
    }

    public function reset(): void
    {
        $this->resolved = null;
    }

    private function detectContext(): string
    {
        if ($this->container->runningInConsole()) {
            return Principal::CONTEXT_CLI;
        }

        $request = $this->container->bound('request')
            ? $this->container->make('request')
            : null;

        if ($request instanceof Request) {
            if ($request->bearerToken() !== null || $request->is('api/*')) {
                return Principal::CONTEXT_API;
            }

            return Principal::CONTEXT_WEB;
        }

        return Principal::CONTEXT_CLI;
    }
}