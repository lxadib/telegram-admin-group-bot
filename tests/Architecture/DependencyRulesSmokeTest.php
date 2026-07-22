<?php

declare(strict_types=1);

namespace Platform\Tests\Architecture;

use PHPUnit\Framework\TestCase;

/**
 * Phase 1: document that Deptrac owns layer rules.
 * Additional PHPUnit architecture assertions are added as modules appear.
 */
final class DependencyRulesSmokeTest extends TestCase
{
    public function testDeptracConfigExists(): void
    {
        $path = dirname(__DIR__, 2) . '/deptrac.yaml';

        self::assertFileExists($path);
    }
}
