<?php

declare(strict_types=1);

namespace Platform\Kernel\Security;

use Platform\Contracts\Clock\ClockInterface;
use Platform\Contracts\Security\TokenIssuerInterface;

/**
 * Stateless signed tokens: base64url(payload).base64url(HMAC-SHA256).
 * Not a JWT; intentionally minimal for session/confirm/license-cache use.
 */
final class HmacTokenIssuer implements TokenIssuerInterface
{
    public function __construct(
        private readonly string $secret,
        private readonly ClockInterface $clock,
    ) {
        if ($this->secret === '') {
            throw new \InvalidArgumentException('HmacTokenIssuer requires a non-empty secret.');
        }
    }

    public function issue(array $claims, ?\DateTimeImmutable $expiresAt = null): string
    {
        if ($expiresAt !== null) {
            $claims['exp'] = $expiresAt->getTimestamp();
        }
        $claims['iat'] = $this->clock->now()->getTimestamp();

        $payload = $this->encode($this->toJson($claims));
        $signature = $this->encode($this->sign($payload));

        return $payload . '.' . $signature;
    }

    public function verify(string $token): array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 2) {
            throw new \RuntimeException('Malformed token.');
        }

        [$payload, $signature] = $parts;
        $expected = $this->encode($this->sign($payload));

        if (!hash_equals($expected, $signature)) {
            throw new \RuntimeException('Invalid token signature.');
        }

        $decoded = $this->decode($payload);
        /** @var array<string, mixed> $claims */
        $claims = json_decode($decoded, true, 512, JSON_THROW_ON_ERROR);

        if (isset($claims['exp']) && is_numeric($claims['exp'])) {
            if ($this->clock->now()->getTimestamp() > (int) $claims['exp']) {
                throw new \RuntimeException('Token expired.');
            }
        }

        return $claims;
    }

    /**
     * @param array<string, mixed> $claims
     */
    private function toJson(array $claims): string
    {
        return json_encode($claims, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
    }

    private function sign(string $payload): string
    {
        return hash_hmac('sha256', $payload, $this->secret, true);
    }

    private function encode(string $raw): string
    {
        return rtrim(strtr(base64_encode($raw), '+/', '-_'), '=');
    }

    private function decode(string $encoded): string
    {
        $decoded = base64_decode(strtr($encoded, '-_', '+/'), true);
        if ($decoded === false) {
            throw new \RuntimeException('Invalid token encoding.');
        }

        return $decoded;
    }
}
