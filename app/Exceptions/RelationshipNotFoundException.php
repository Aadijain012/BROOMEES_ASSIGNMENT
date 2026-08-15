<?php

namespace App\Exceptions;

class RelationshipNotFoundException extends ApiDomainException
{
    public function __construct()
    {
        parent::__construct('RELATIONSHIP_NOT_FOUND', 'The relationship does not exist.', 404);
    }
}
