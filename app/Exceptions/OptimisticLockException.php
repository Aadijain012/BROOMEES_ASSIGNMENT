<?php

namespace App\Exceptions;

class OptimisticLockException extends ApiDomainException
{
    public function __construct()
    {
        parent::__construct('OPTIMISTIC_LOCK_CONFLICT', 'The resource was modified by another request.', 409);
    }
}
