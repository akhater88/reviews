<?php

namespace App\Events\InternalCompetition;

use App\Models\InternalCompetition\InternalCompetition;
use App\Models\InternalCompetition\InternalCompetitionBranch;
use App\Models\InternalCompetition\InternalCompetitionTenant;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ParticipantWithdrawn
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly InternalCompetition $competition,
        public readonly InternalCompetitionTenant|InternalCompetitionBranch $participant,
        public readonly string $participantType, // 'tenant' or 'branch'
        public readonly ?string $reason = null
    ) {}
}
