<?php

namespace App\Exceptions;

class RelationshipAlreadyExistsException extends ApiDomainException
{
    public function __construct()
    {
        parent::__construct('RELATIONSHIP_ALREADY_EXISTS', 'The relationship already exists.', 409);
    }
}
