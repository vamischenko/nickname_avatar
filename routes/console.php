<?php

use App\Jobs\CleanupExpiredUsersJob;
use Illuminate\Support\Facades\Schedule;

$intervalMinutes = (int) config('app.cleanup_interval_minutes', 5);

Schedule::job(new CleanupExpiredUsersJob)->everyXMinutes($intervalMinutes);
