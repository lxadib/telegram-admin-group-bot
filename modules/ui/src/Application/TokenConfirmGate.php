<?php

declare(strict_types=1);

namespace Platform\Modules\Ui\Application;

use Platform\Contracts\Clock\ClockInterface;
use Platform\Contracts\Navigation\ConfirmGateInterface;
use Platform\Contracts\Security\TokenIssuerInterface;

/**
 * Two-step confirmations backed by signed one-time tokens.
 */
final class TokenConfirmGate implements ConfirmGateInterface
{
    /** @var array<string, true> consumed jti values */
    private array $consumed = [];

    public function __construct(
        private readonly TokenIssuerInterface $tokens,
        private readonly ClockInterface $clock,
    ) {
    }

    public function issue(string $action, array $payload, int $ttlSeconds = 120): string
    {
        if ($action === '') {
            throw new \InvalidArgumentException('Confirm action must be non-empty.');
        }

        $jti = bin2hex(random_bytes(8));
        $expires = $this->clock->now()->modify(sprintf('+%d seconds', max(1, $ttlSeconds)));

        return $this->tokens->issue([
            'typ' => 'confirm',
            'action' => $action,
            'payload' => $payload,
            'jti' => $jti,
        ], $expires);
    }

    public function consume(string $token): ?array
    {
        try {
            $claims = $this->tokens->verify($token);
        } catch (\Throwable) {
            return null;
        }

        if (($claims['typ'] ?? null) !== 'confirm') {
            return null;
        }

        $jti = $claims['jti'] ?? null;
        if (!is_string($jti) || $jti === '' || isset($this->consumed[$jti])) {
            return null;
        }

        $action = $claims['action'] ?? null;
        $payload = $claims['payload'] ?? null;
        if (!is_string($action) || !is_array($payload)) {
            return null;
        }

        $this->consumed[$jti] = true;

        /** @var array<string, mixed> $payload */
        return ['action' => $action, 'payload' => $payload];
    }
}
