<?php

declare(strict_types=1);

namespace App\Events\Leave;

use App\Models\LeaveApply;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LeaveHRApproved
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public LeaveApply $leave,
        public ?int $actorId = null,
        public ?string $remarks = null
    ) {
    }
}
