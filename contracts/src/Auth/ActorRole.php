<?php

declare(strict_types=1);

namespace Platform\Contracts\Auth;

/**
 * Actor roles in the ownership hierarchy.
 */
enum ActorRole: string
{
    case SuperOwner = 'super_owner';
    case Owner = 'owner';
    case Customer = 'customer';
    case GroupAdmin = 'group_admin';
    case Moderator = 'moderator';
    case Member = 'member';
    case Guest = 'guest';
}
