<?php

namespace App\Events\InternalCompetition;

use App\Models\InternalCompetition\InternalCompetitionWinner;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PrizeClaimed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly InternalCompetitionWinner $winner
    ) {}
}
