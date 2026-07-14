<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Contracts\FinancialYearClosingServiceInterface;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CloseFinancialYearJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 1800;

    public function __construct(
        public string $financialYear,
        public int $actorId,
        public ?string $ipAddress = null
    ) {}

    public function handle(FinancialYearClosingServiceInterface $service): void
    {
        $actor = User::findOrFail($this->actorId);
        $service->close($this->financialYear, $actor, $this->ipAddress);
    }
}
