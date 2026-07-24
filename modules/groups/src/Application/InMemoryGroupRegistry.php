<?php

declare(strict_types=1);

namespace Platform\Modules\Groups\Application;

use Platform\Contracts\Capability\CapabilityGateInterface;
use Platform\Contracts\Clock\ClockInterface;
use Platform\Contracts\Event\EventBusInterface;
use Platform\Contracts\Groups\Events\GroupBound;
use Platform\Contracts\Groups\GroupRecord;
use Platform\Contracts\Groups\GroupRegistryInterface;
use Platform\Contracts\Identity\IdGeneratorInterface;

final class InMemoryGroupRegistry implements GroupRegistryInterface
{
    /** @var array<string, GroupRecord> */
    private array $byId = [];

    /** @var array<string, string> telegramChatId => groupId */
    private array $telegramIndex = [];

    public function __construct(
        private readonly ClockInterface $clock,
        private readonly IdGeneratorInterface $ids,
        private readonly EventBusInterface $events,
        private readonly CapabilityGateInterface $capabilities,
    ) {
    }

    public function get(string $groupId): ?GroupRecord
    {
        return $this->byId[$groupId] ?? null;
    }

    public function findByTelegramChatId(string $telegramChatId): ?GroupRecord
    {
        $groupId = $this->telegramIndex[$telegramChatId] ?? null;

        return $groupId === null ? null : ($this->byId[$groupId] ?? null);
    }

    public function bind(string $tenantId, string $telegramChatId, ?string $title = null, ?string $groupId = null): GroupRecord
    {
        if ($tenantId === '' || $telegramChatId === '') {
            throw new \InvalidArgumentException('tenantId and telegramChatId are required.');
        }

        $existing = $this->findByTelegramChatId($telegramChatId);
        if ($existing !== null) {
            if ($existing->tenantId !== $tenantId) {
                throw new \RuntimeException(sprintf(
                    'Telegram chat "%s" is already bound to tenant "%s".',
                    $telegramChatId,
                    $existing->tenantId,
                ));
            }

            return $existing;
        }

        $this->capabilities->assertWithinGroupLimit($tenantId, $this->countForTenant($tenantId));

        $groupId ??= $this->ids->generate();
        $record = new GroupRecord(
            groupId: $groupId,
            tenantId: $tenantId,
            telegramChatId: $telegramChatId,
            title: $title,
            active: true,
            boundAt: $this->clock->now(),
        );

        $this->byId[$groupId] = $record;
        $this->telegramIndex[$telegramChatId] = $groupId;

        $this->events->dispatch(new GroupBound(
            groupId: $groupId,
            tenantId: $tenantId,
            telegramChatId: $telegramChatId,
        ));

        return $record;
    }

    public function deactivate(string $groupId): void
    {
        $existing = $this->get($groupId);
        if ($existing === null) {
            return;
        }

        $this->byId[$groupId] = new GroupRecord(
            groupId: $existing->groupId,
            tenantId: $existing->tenantId,
            telegramChatId: $existing->telegramChatId,
            title: $existing->title,
            active: false,
            boundAt: $existing->boundAt,
        );
    }

    public function listForTenant(string $tenantId): array
    {
        return array_values(array_filter(
            $this->byId,
            static fn (GroupRecord $g): bool => $g->tenantId === $tenantId,
        ));
    }

    public function countForTenant(string $tenantId): int
    {
        $count = 0;
        foreach ($this->byId as $group) {
            if ($group->tenantId === $tenantId && $group->active) {
                ++$count;
            }
        }

        return $count;
    }
}
