<?php
use Illuminate\Support\Facades\Schedule;
use App\Models\Task;

Schedule::command('check:task-deadlines')
    ->dailyAt('09:00')
    ->withoutOverlapping()
    ->runInBackground();
