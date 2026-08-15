<?php

namespace App\Exceptions;

class UserDeletionConflictException extends ApiDomainException
{
    public function __construct(string $message)
    {
        parent::__construct('USER_DELETION_CONFLICT', $message, 409);
    }
}
