<?php

declare(strict_types=1);

namespace Platform\Contracts\Storage;

/**
 * Runs forward-only migrations for a module or the platform.
 */
interface MigrationRunnerInterface
{
    /**
     * @param list<string> $migrationPaths Absolute or module-relative paths
     */
    public function up(string $moduleId, array $migrationPaths): void;
}
