<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;


Schedule::command('telemetry:prune --days=30')->dailyAt('02:00');
Schedule::command('telemetry:check-stale --minutes=10')->everyMinute();


Schedule::command('telemetry:prune --days=30')->dailyAt('02:00'); // Runs every night at 2 AM
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

$backupScheduleFile = storage_path('app/backup-schedule.json');
if (file_exists($backupScheduleFile)) {
    $backupConfig = json_decode(file_get_contents($backupScheduleFile), true);
    if (!empty($backupConfig['enabled']) && !empty($backupConfig['time'])) {
        $freq = $backupConfig['frequency'] ?? 'daily';
        $cmd = Schedule::command('backup:run --disable-notifications');
        
        if ($freq === 'weekly') {
            $cmd->weeklyOn($backupConfig['dayOfWeek'] ?? 1, $backupConfig['time']);
        } elseif ($freq === 'monthly') {
            $cmd->monthlyOn($backupConfig['dayOfMonth'] ?? 1, $backupConfig['time']);
        } else {
            $cmd->dailyAt($backupConfig['time']);
        }
    }
}
