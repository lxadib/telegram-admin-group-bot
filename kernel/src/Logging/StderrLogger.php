<?php

declare(strict_types=1);

namespace Platform\Kernel\Logging;

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

/**
 * Minimal stderr logger used until Phase 4 structured logging adapters land.
 */
final class StderrLogger extends AbstractLogger
{
    /**
     * @param mixed $level
     * @param array<mixed> $context
     */
    public function log($level, string|\Stringable $message, array $context = []): void
    {
        $line = sprintf(
            "[%s] %s %s%s\n",
            (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
            strtoupper((string) $level),
            (string) $message,
            $context === [] ? '' : ' ' . json_encode($context, JSON_UNESCAPED_SLASHES),
        );

        fwrite(STDERR, $line);
    }

    /**
     * @return list<string>
     */
    public static function levels(): array
    {
        return [
            LogLevel::EMERGENCY,
            LogLevel::ALERT,
            LogLevel::CRITICAL,
            LogLevel::ERROR,
            LogLevel::WARNING,
            LogLevel::NOTICE,
            LogLevel::INFO,
            LogLevel::DEBUG,
        ];
    }
}
