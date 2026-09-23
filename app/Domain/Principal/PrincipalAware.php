<?php

namespace App\Domain\Principal;

/**
 * Lets domain services optionally carry an explicit Principal instead of
 * reading session-coupled user state. Services that receive a Principal are
 * safe to run in API and queue contexts with identical behaviour.
 */
trait PrincipalAware
{
    protected ?Principal $principal = null;

    public function withPrincipal(?Principal $principal): static
    {
        $this->principal = $principal;

        return $this;
    }

    public function principal(): ?Principal
    {
        if ($this->principal === null && app()->bound(PrincipalResolver::class)) {
            return app(PrincipalResolver::class)->resolve();
        }

        return $this->principal;
    }

    public function hasPrincipalRole(string $role): bool
    {
        return $this->principal()?->hasRole($role) ?? false;
    }
}
