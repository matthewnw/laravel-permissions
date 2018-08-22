<?php

namespace Matthewnw\Permissions\Exceptions;

use InvalidArgumentException;

class PermissionDoesNotExist extends InvalidArgumentException
{
    public static function withIdentity(string $permissionIdentity)
    {
        return new static("There is no permission identified by `{$permissionIdentity}`.");
    }

    public static function withId(int $permissionId)
    {
        return new static("There is no [permission] with id `{$permissionId}`.");
    }
}
