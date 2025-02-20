<?php

namespace App\Listeners;

use App\Events\TableScanned;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyTableScanned
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
    public function handle(TableScanned $event): void
    {
        //
    }
}
