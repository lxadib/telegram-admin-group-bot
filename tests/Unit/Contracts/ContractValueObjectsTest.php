<?php

declare(strict_types=1);

namespace Platform\Tests\Unit\Contracts;

use PHPUnit\Framework\TestCase;
use Platform\Contracts\Auth\ActorContext;
use Platform\Contracts\Auth\ActorRole;
use Platform\Contracts\Capability\CapabilitySnapshot;
use Platform\Contracts\Module\ModuleId;
use Platform\Contracts\Module\ModuleManifest;
use Platform\Contracts\Navigation\MenuNode;
use Platform\Contracts\Navigation\ViewModel;
use Platform\Contracts\Package;
use Platform\Contracts\Queue\JobInterface;
use Platform\Contracts\Queue\ScheduleDefinition;

final class ContractValueObjectsTest extends TestCase
{
    public function testPackageMetadata(): void
    {
        self::assertStringContainsString('contracts', Package::NAME);
        self::assertMatchesRegularExpression('/^\d+\.\d+\.\d+$/', Package::VERSION);
    }

    public function testModuleIdRejectsInvalidValues(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new ModuleId('Users');
    }

    public function testModuleManifestAcceptsValidSemver(): void
    {
        $manifest = new ModuleManifest(
            id: new ModuleId('users'),
            version: '1.0.0',
            displayName: 'Users',
            permissions: ['users.view'],
        );

        self::assertSame('users', (string) $manifest->id);
        self::assertSame(['users.view'], $manifest->permissions);
    }

    public function testActorContextRoleChecks(): void
    {
        $actor = new ActorContext(
            actorId: 'user-1',
            roles: [ActorRole::Customer, ActorRole::GroupAdmin],
            tenantId: 'tenant-1',
            groupId: 'group-1',
        );

        self::assertTrue($actor->hasRole(ActorRole::Customer));
        self::assertTrue($actor->hasRole(ActorRole::GroupAdmin));
        self::assertFalse($actor->isSuperOwner());
    }

    public function testCapabilitySnapshotUsability(): void
    {
        $now = new \DateTimeImmutable('2026-07-24 12:00:00');
        $snapshot = new CapabilitySnapshot(
            tenantId: 'tenant-1',
            licenseId: 'lic-1',
            state: 'active',
            features: ['moderation' => true, 'automation' => false],
            maxGroups: 10,
            expiresAt: new \DateTimeImmutable('2026-08-01 00:00:00'),
        );

        self::assertTrue($snapshot->allows('moderation'));
        self::assertFalse($snapshot->allows('automation'));
        self::assertTrue($snapshot->isUsable($now));
        self::assertFalse($snapshot->isUsable(new \DateTimeImmutable('2026-09-01 00:00:00')));
    }

    public function testCapabilityGraceWindow(): void
    {
        $snapshot = new CapabilitySnapshot(
            tenantId: 'tenant-1',
            licenseId: 'lic-1',
            state: 'grace',
            expiresAt: new \DateTimeImmutable('2026-07-01 00:00:00'),
            graceUntil: new \DateTimeImmutable('2026-07-31 00:00:00'),
        );

        self::assertTrue($snapshot->isUsable(new \DateTimeImmutable('2026-07-15 00:00:00')));
        self::assertFalse($snapshot->isUsable(new \DateTimeImmutable('2026-08-01 00:00:00')));
    }

    public function testMenuNodeAndViewModel(): void
    {
        $node = new MenuNode(id: 'root.settings', labelKey: 'menu.settings', route: 'settings.index');
        $view = new ViewModel(
            text: 'Settings',
            inlineKeyboard: [[['text' => 'Back', 'callback_data' => 'nav:back']]],
        );

        self::assertSame('root.settings', $node->id);
        self::assertSame('Settings', $view->text);
        self::assertTrue($view->editMessage);
    }

    public function testScheduleDefinitionRequiresIdentifiers(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new ScheduleDefinition(
            id: '',
            moduleId: 'automation',
            cronExpression: '0 * * * *',
            jobClass: FakeJob::class,
        );
    }
}

/**
 * @internal
 */
final class FakeJob implements JobInterface
{
    public function queue(): string
    {
        return 'default';
    }

    public function priority(): int
    {
        return 0;
    }

    public function maxAttempts(): int
    {
        return 3;
    }

    public function delaySeconds(): int
    {
        return 0;
    }

    public function payload(): array
    {
        return [];
    }

    public function jobName(): string
    {
        return 'fake';
    }
}
