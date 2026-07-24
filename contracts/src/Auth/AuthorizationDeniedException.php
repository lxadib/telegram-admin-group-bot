<?php

declare(strict_types=1);

namespace Platform\Contracts\Auth;

final class AuthorizationDeniedException extends \RuntimeException
{
    public function __construct(
        public readonly string $permission,
        public readonly string $actorId,
        string $message = '',
    ) {
        parent::__construct($message !== '' ? $message : sprintf(
            'Actor %s is not allowed to perform "%s".',
            $this->actorId,
            $this->permission,
        ));
    }
}
