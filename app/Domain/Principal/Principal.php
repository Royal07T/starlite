<?php

namespace App\Domain\Principal;

/**
 * Explicit role/tenant context resolved per request (web, API, queue or CLI).
 *
 * Domain code should depend on this object instead of reading the session
 * coupled User::role accessor, so behaviour is identical across transports.
 */
final class Principal
{
    public const CONTEXT_WEB = 'web';
    public const CONTEXT_API = 'api';
    public const CONTEXT_QUEUE = 'queue';
    public const CONTEXT_CLI = 'cli';

    public function __construct(
        public readonly ?int $userId,
        public readonly ?string $role,
        public readonly string $context,
    ) {
    }

    public static function system(string $context = self::CONTEXT_CLI): self
    {
        return new self(null, null, $context);
    }

    public static function forUser(int $userId, ?string $role, string $context): self
    {
        return new self($userId, $role, $context);
    }

    public function isAuthenticated(): bool
    {
        return $this->userId !== null;
    }

    public function hasRole(string $role): bool
    {
        return $this->role !== null && $this->role === $role;
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }
}