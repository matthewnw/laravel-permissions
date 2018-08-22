<?php

namespace Matthewnw\Permissions\Exceptions;

use InvalidArgumentException;

class RoleDoesNotExist extends InvalidArgumentException
{
    public static function withIdentity(string $roleIdentity)
    {
        return new static("There is no role identified by `{$roleIdentity}`.");
    }

    public static function withId(int $roleId)
    {
        return new static("There is no [permission] with id `{$roleId}`.");
    }
}
