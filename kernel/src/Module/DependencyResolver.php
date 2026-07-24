<?php

declare(strict_types=1);

namespace Platform\Kernel\Module;

use Platform\Contracts\Module\ModuleInterface;

/**
 * Topologically sorts modules by manifest requires.
 */
final class DependencyResolver
{
    /**
     * @param list<ModuleInterface> $modules
     * @return list<ModuleInterface>
     */
    public function resolve(array $modules): array
    {
        /** @var array<string, ModuleInterface> $byId */
        $byId = [];
        foreach ($modules as $module) {
            $id = (string) $module->manifest()->id;
            if (isset($byId[$id])) {
                throw new \InvalidArgumentException(sprintf('Duplicate module id "%s".', $id));
            }
            $byId[$id] = $module;
        }

        foreach ($byId as $module) {
            foreach ($module->manifest()->requires as $requirement) {
                $requiredId = $requirement['module'];
                if (!isset($byId[$requiredId])) {
                    throw new \RuntimeException(sprintf(
                        'Module "%s" requires missing module "%s".',
                        $module->manifest()->id,
                        $requiredId,
                    ));
                }
            }
        }

        $visiting = [];
        $visited = [];
        $ordered = [];

        $visit = function (string $id) use (&$visit, &$visiting, &$visited, &$ordered, $byId): void {
            if (isset($visited[$id])) {
                return;
            }
            if (isset($visiting[$id])) {
                throw new \RuntimeException(sprintf('Circular module dependency involving "%s".', $id));
            }

            $visiting[$id] = true;
            $module = $byId[$id];
            foreach ($module->manifest()->requires as $requirement) {
                $visit($requirement['module']);
            }
            unset($visiting[$id]);
            $visited[$id] = true;
            $ordered[] = $module;
        };

        foreach (array_keys($byId) as $id) {
            $visit($id);
        }

        return $ordered;
    }
}
