<?php

namespace App\Exceptions\Competition;

use Exception;

class CompetitionClosedException extends Exception
{
    public function __construct(string $message = 'Competition is closed')
    {
        parent::__construct($message);
    }
}
