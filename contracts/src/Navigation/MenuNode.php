<?php

declare(strict_types=1);

namespace Platform\Contracts\Navigation;

/**
 * Declarative menu node contributed by a module.
 */
final readonly class MenuNode
{
    /**
     * @param list<MenuNode> $children
     * @param list<string> $requiredPermissions
     */
    public function __construct(
        public string $id,
        public string $labelKey,
        public ?string $route = null,
        public array $children = [],
        public array $requiredPermissions = [],
        public ?string $moduleId = null,
    ) {
        if ($this->id === '' || $this->labelKey === '') {
            throw new \InvalidArgumentException('MenuNode requires id and labelKey.');
        }
    }
}
