<?php

namespace App\DTOs;

readonly class BudgetStatsDTO
{
    public function __construct(
        public float $totalApproved,
        public float $totalPending,
        public float $totalRejected,
        public int   $approvedCount,
        public int   $pendingCount,
        public int   $rejectedCount,
    ) {}
}
