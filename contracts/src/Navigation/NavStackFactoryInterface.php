<?php

declare(strict_types=1);

namespace Platform\Contracts\Navigation;

/**
 * Opens a per-session NavStack. Bound by the UI module.
 */
interface NavStackFactoryInterface
{
    public function forSession(string $sessionId): NavStackInterface;
}
