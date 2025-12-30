<?php

namespace App\Events\InternalCompetition;

use App\Models\InternalCompetition\InternalCompetition;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class WinnersAnnounced
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly InternalCompetition $competition,
        public readonly Collection $winners
    ) {}
}
