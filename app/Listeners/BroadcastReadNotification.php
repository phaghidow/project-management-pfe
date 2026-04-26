<?php

namespace App\Listeners;

use App\Events\NotificationRead;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class BroadcastReadNotification
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
    public function handle(NotificationRead $event): void
    {
        // Broadcasting handled by ShouldBroadcast on event
    }
}

