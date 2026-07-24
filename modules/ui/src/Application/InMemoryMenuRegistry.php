<?php

declare(strict_types=1);

namespace Platform\Modules\Ui\Application;

use Platform\Contracts\Navigation\MenuNode;
use Platform\Contracts\Navigation\MenuRegistryInterface;

final class InMemoryMenuRegistry implements MenuRegistryInterface
{
    /** @var array<string, MenuNode> */
    private array $nodes = [];

    public function register(MenuNode $node): void
    {
        $this->nodes[$node->id] = $node;
    }

    public function get(string $id): ?MenuNode
    {
        return $this->nodes[$id] ?? null;
    }

    public function roots(): array
    {
        $childIds = [];
        foreach ($this->nodes as $node) {
            foreach ($node->children as $child) {
                $childIds[$child->id] = true;
            }
        }

        $roots = [];
        foreach ($this->nodes as $node) {
            if (!isset($childIds[$node->id])) {
                $roots[] = $node;
            }
        }

        return $roots;
    }
}
