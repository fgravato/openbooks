<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function (): void {
    $this->comment('Build accounting software that users trust.');
})->purpose('Display an OpenBooks motivational quote');

Schedule::command('invoices:check-overdue')->dailyAt('00:00');
Schedule::command('invoices:process-recurring')->dailyAt('06:00');
Schedule::command('invoices:send-reminders')->dailyAt('09:00');
