<?php

declare(strict_types=1);

namespace Platform\Contracts\Navigation;

/**
 * Registry of Telegram UI menus contributed by modules.
 */
interface MenuRegistryInterface
{
    public function register(MenuNode $node): void;

    public function get(string $id): ?MenuNode;

    /**
     * @return list<MenuNode>
     */
    public function roots(): array;
}
