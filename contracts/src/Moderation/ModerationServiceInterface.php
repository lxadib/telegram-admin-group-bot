<?php

declare(strict_types=1);

namespace Platform\Contracts\Moderation;

/**
 * Applies moderation to members of a bound group. Bound by the Moderation module.
 *
 * Implementations enforce the tenant license "moderation" feature (capability gate)
 * before acting; RBAC (who may call) is enforced by the caller.
 */
interface ModerationServiceInterface
{
    /**
     * Record a warning and return the member's new warning total for the group.
     *
     * @throws \Platform\Contracts\Capability\CapabilityDeniedException
     */
    public function warn(
        string $tenantId,
        string $groupId,
        string $targetUserId,
        string $actorId,
        string $reason = '',
    ): int;

    /**
     * @throws \Platform\Contracts\Capability\CapabilityDeniedException
     */
    public function mute(
        string $tenantId,
        string $groupId,
        string $targetUserId,
        string $actorId,
        string $reason = '',
        int $seconds = 0,
    ): void;

    /**
     * @throws \Platform\Contracts\Capability\CapabilityDeniedException
     */
    public function ban(
        string $tenantId,
        string $groupId,
        string $targetUserId,
        string $actorId,
        string $reason = '',
    ): void;

    public function isMuted(string $groupId, string $targetUserId): bool;

    public function isBanned(string $groupId, string $targetUserId): bool;

    public function warningCount(string $groupId, string $targetUserId): int;
}
