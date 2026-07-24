<?php

declare(strict_types=1);

namespace Platform\Adapters\Cache\Redis;

use Psr\SimpleCache\InvalidArgumentException as PsrInvalidArgumentException;

final class InvalidCacheKey extends \InvalidArgumentException implements PsrInvalidArgumentException
{
}
