<?php

namespace App\Listeners;

use App\Events\NewNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class BroadcastNewNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(NewNotification $event): void
    {
        // The broadcasting is handled by the ShouldBroadcast interface on the event
    }
}

