<?php

namespace App\Exceptions\Competition;

use Exception;

class ParticipantBlockedException extends Exception
{
    public function __construct(string $message = 'Participant is blocked')
    {
        parent::__construct($message);
    }
}
