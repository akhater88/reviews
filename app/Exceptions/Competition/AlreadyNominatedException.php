<?php

namespace App\Exceptions\Competition;

use App\Models\Competition\CompetitionNomination;
use Exception;

class AlreadyNominatedException extends Exception
{
    protected ?CompetitionNomination $nomination;

    public function __construct(string $message = 'Already nominated', ?CompetitionNomination $nomination = null)
    {
        parent::__construct($message);
        $this->nomination = $nomination;
    }

    public function getNomination(): ?CompetitionNomination
    {
        return $this->nomination;
    }
}
