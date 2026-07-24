<?php

declare(strict_types=1);

namespace Platform\Tests\Unit\Kernel\Infrastructure;

use PHPUnit\Framework\TestCase;
use Platform\Kernel\Security\EnvSecretVault;
use Platform\Kernel\Security\HmacTokenIssuer;
use Platform\Kernel\Security\InMemoryIdempotencyStore;
use Platform\Kernel\Security\InMemoryRateLimiter;
use Platform\Kernel\Security\NativeHasher;
use Platform\Tests\Unit\Kernel\Fixtures\MutableClock;

final class SecurityPrimitivesTest extends TestCase
{
    public function testNativeHasherRoundTrip(): void
    {
        $hasher = new NativeHasher();
        $hash = $hasher->hash('s3cret');

        self::assertNotSame('s3cret', $hash);
        self::assertTrue($hasher->verify('s3cret', $hash));
        self::assertFalse($hasher->verify('wrong', $hash));
    }

    public function testTokenIssuerIssuesAndVerifies(): void
    {
        $issuer = new HmacTokenIssuer('unit-secret', new MutableClock());
        $token = $issuer->issue(['sub' => 'user-1']);

        $claims = $issuer->verify($token);
        self::assertSame('user-1', $claims['sub']);
    }

    public function testTokenIssuerRejectsTampering(): void
    {
        $issuer = new HmacTokenIssuer('unit-secret', new MutableClock());
        $token = $issuer->issue(['sub' => 'user-1']);

        $this->expectException(\RuntimeException::class);
        $issuer->verify($token . 'x');
    }

    public function testTokenIssuerRejectsExpired(): void
    {
        $clock = new MutableClock();
        $issuer = new HmacTokenIssuer('unit-secret', $clock);
        $token = $issuer->issue(['sub' => 'user-1'], $clock->now()->modify('+10 seconds'));

        $clock->advance(20);
        $this->expectException(\RuntimeException::class);
        $issuer->verify($token);
    }

    public function testRateLimiterEnforcesWindow(): void
    {
        $clock = new MutableClock();
        $limiter = new InMemoryRateLimiter($clock);

        self::assertTrue($limiter->attempt('actor:1', 2, 60));
        self::assertTrue($limiter->attempt('actor:1', 2, 60));
        self::assertFalse($limiter->attempt('actor:1', 2, 60));
        self::assertSame(0, $limiter->remaining('actor:1', 2, 60));

        $clock->advance(61);
        self::assertTrue($limiter->attempt('actor:1', 2, 60));
    }

    public function testIdempotencyStoreClaimsOnce(): void
    {
        $clock = new MutableClock();
        $store = new InMemoryIdempotencyStore($clock);

        self::assertTrue($store->claim('op:1', 30));
        self::assertFalse($store->claim('op:1', 30));

        $clock->advance(31);
        self::assertTrue($store->claim('op:1', 30));
    }

    public function testSecretVaultOverlayAndEnv(): void
    {
        putenv('SECRET_BOT_TOKEN=from-env');
        $vault = new EnvSecretVault(['manual' => 'from-overlay']);

        self::assertSame('from-overlay', $vault->get('manual'));
        self::assertTrue($vault->has('bot.token'));
        self::assertSame('from-env', $vault->get('bot.token'));

        $vault->delete('manual');
        self::assertFalse($vault->has('manual'));

        putenv('SECRET_BOT_TOKEN');
    }
}
