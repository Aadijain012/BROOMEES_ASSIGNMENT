<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('reputation:recalculate')->dailyAt('02:00')->withoutOverlapping();
