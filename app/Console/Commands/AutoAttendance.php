<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Attendance\AutoAttendanceService;
use Illuminate\Support\Facades\Log;

class AutoAttendance extends Command
{
    protected $signature = 'app:auto-attendance';
    protected $description = 'Automatically mark attendance for absent or holiday users.';

    public function __construct(protected AutoAttendanceService $autoAttendanceService)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $this->info('🕐 Starting Auto Attendance Process...');
        Log::info('Auto Attendance Job started');

        $this->autoAttendanceService->run();

        $this->info('✅ Auto Attendance Process Completed Successfully.');
        Log::info('Auto Attendance Job completed');
    }
}
