<?php

declare(strict_types=1);

namespace Platform\Modules\Moderation\Application;

use Platform\Contracts\Capability\CapabilityGateInterface;
use Platform\Contracts\Clock\ClockInterface;
use Platform\Contracts\Event\EventBusInterface;
use Platform\Contracts\Logging\AuditLoggerInterface;
use Platform\Contracts\Moderation\Events\MemberModerated;
use Platform\Contracts\Moderation\ModerationAction;
use Platform\Contracts\Moderation\ModerationServiceInterface;

/**
 * In-memory moderation state. Capability ("moderation" feature) is enforced here so
 * every caller — Telegram, API, jobs — is protected uniformly. Emits domain events + audit.
 */
final class InMemoryModerationService implements ModerationServiceInterface
{
    private const FEATURE = 'moderation';

    /** @var array<string, int> "groupId:userId" => warning count */
    private array $warnings = [];

    /** @var array<string, true> "groupId:userId" */
    private array $muted = [];

    /** @var array<string, true> "groupId:userId" */
    private array $banned = [];

    public function __construct(
        private readonly CapabilityGateInterface $capabilities,
        private readonly EventBusInterface $events,
        private readonly AuditLoggerInterface $audit,
        private readonly ClockInterface $clock,
    ) {
    }

    public function warn(
        string $tenantId,
        string $groupId,
        string $targetUserId,
        string $actorId,
        string $reason = '',
    ): int {
        $this->capabilities->assertFeature($tenantId, self::FEATURE);

        $key = $this->key($groupId, $targetUserId);
        $count = ($this->warnings[$key] ?? 0) + 1;
        $this->warnings[$key] = $count;

        $this->emit($tenantId, $groupId, $targetUserId, $actorId, ModerationAction::Warn, $reason, [
            'warnings' => $count,
        ]);

        return $count;
    }

    public function mute(
        string $tenantId,
        string $groupId,
        string $targetUserId,
        string $actorId,
        string $reason = '',
        int $seconds = 0,
    ): void {
        $this->capabilities->assertFeature($tenantId, self::FEATURE);

        $this->muted[$this->key($groupId, $targetUserId)] = true;

        $this->emit($tenantId, $groupId, $targetUserId, $actorId, ModerationAction::Mute, $reason, [
            'seconds' => $seconds,
        ]);
    }

    public function ban(
        string $tenantId,
        string $groupId,
        string $targetUserId,
        string $actorId,
        string $reason = '',
    ): void {
        $this->capabilities->assertFeature($tenantId, self::FEATURE);

        $key = $this->key($groupId, $targetUserId);
        $this->banned[$key] = true;
        unset($this->muted[$key]);

        $this->emit($tenantId, $groupId, $targetUserId, $actorId, ModerationAction::Ban, $reason, []);
    }

    public function isMuted(string $groupId, string $targetUserId): bool
    {
        return isset($this->muted[$this->key($groupId, $targetUserId)]);
    }

    public function isBanned(string $groupId, string $targetUserId): bool
    {
        return isset($this->banned[$this->key($groupId, $targetUserId)]);
    }

    public function warningCount(string $groupId, string $targetUserId): int
    {
        return $this->warnings[$this->key($groupId, $targetUserId)] ?? 0;
    }

    /**
     * @param array<string, scalar|null> $extra
     */
    private function emit(
        string $tenantId,
        string $groupId,
        string $targetUserId,
        string $actorId,
        ModerationAction $action,
        string $reason,
        array $extra,
    ): void {
        $this->audit->record(
            'moderation.' . $action->value,
            actorId: $actorId,
            tenantId: $tenantId,
            groupId: $groupId,
            context: ['targetUserId' => $targetUserId, 'reason' => $reason] + $extra,
        );

        $this->events->dispatch(new MemberModerated(
            tenantId: $tenantId,
            groupId: $groupId,
            targetUserId: $targetUserId,
            action: $action,
            actorId: $actorId,
            reason: $reason,
            occurredAt: $this->clock->now(),
        ));
    }

    private function key(string $groupId, string $userId): string
    {
        return $groupId . ':' . $userId;
    }
}
