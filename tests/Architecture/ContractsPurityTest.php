<?php

declare(strict_types=1);

namespace Platform\Tests\Architecture;

use PHPUnit\Framework\TestCase;

/**
 * Contracts must remain a pure dependency: no Kernel/Modules/Adapters/Apps imports, no IO side effects.
 */
final class ContractsPurityTest extends TestCase
{
    public function testContractsDoNotImportOuterLayers(): void
    {
        $root = dirname(__DIR__, 2) . '/contracts/src';
        $forbidden = [
            'Platform\\Kernel\\',
            'Platform\\Modules\\',
            'Platform\\Adapters\\',
            'Platform\\Apps\\',
            'PDO',
            'Redis',
            'Curl',
        ];

        $violations = [];

        foreach ($this->phpFiles($root) as $file) {
            $contents = (string) file_get_contents($file);
            foreach ($forbidden as $needle) {
                if (str_contains($contents, $needle)) {
                    $violations[] = sprintf('%s references %s', $file, $needle);
                }
            }
        }

        self::assertSame([], $violations, implode(PHP_EOL, $violations));
    }

    public function testContractsContainOnlyAllowedTypeKinds(): void
    {
        $root = dirname(__DIR__, 2) . '/contracts/src';
        $violations = [];

        foreach ($this->phpFiles($root) as $file) {
            $contents = (string) file_get_contents($file);
            // Concrete service classes with mutable state are forbidden; allow interfaces, enums, readonly, exceptions, Package marker.
            if (preg_match('/\bclass\s+(\w+)/', $contents, $m) === 1) {
                $class = $m[1];
                $allowedConcrete = str_ends_with($class, 'Exception')
                    || $class === 'Package'
                    || str_contains($contents, 'final readonly class ' . $class);

                if (!$allowedConcrete && !str_contains($contents, 'interface ') && !str_contains($contents, 'enum ')) {
                    $violations[] = $file . ' defines concrete class ' . $class;
                }
            }
        }

        self::assertSame([], $violations, implode(PHP_EOL, $violations));
    }

    /**
     * @return list<string>
     */
    private function phpFiles(string $root): array
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS),
        );

        $files = [];
        /** @var \SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }

        sort($files);

        return $files;
    }
}
