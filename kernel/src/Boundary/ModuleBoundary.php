<?php

declare(strict_types=1);

namespace Platform\Kernel\Boundary;

use Psr\Log\LoggerInterface;

/**
 * Isolates module-owned callables so one failure cannot abort the platform.
 */
final class ModuleBoundary
{
    /** @var array<string, int> */
    private array $failureCounts = [];

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly int $failureThreshold = 10,
    ) {
    }

    /**
     * @template T
     * @param callable(): T $callback
     * @return T|null
     */
    public function run(string $moduleId, callable $callback): mixed
    {
        try {
            return $callback();
        } catch (\Throwable $e) {
            $this->failureCounts[$moduleId] = ($this->failureCounts[$moduleId] ?? 0) + 1;
            $this->logger->error('Module boundary caught throwable.', [
                'module_id' => $moduleId,
                'failures' => $this->failureCounts[$moduleId],
                'exception' => $e::class,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function failureCount(string $moduleId): int
    {
        return $this->failureCounts[$moduleId] ?? 0;
    }

    public function hasExceededThreshold(string $moduleId): bool
    {
        return $this->failureCount($moduleId) >= $this->failureThreshold;
    }

    public function reset(string $moduleId): void
    {
        unset($this->failureCounts[$moduleId]);
    }
}
