<?php

namespace App\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Services\Status;

class ListenerBase
{
    /**
     * The Statut instance.
     *
     * @var App\Services\Status
     */
    protected $status;

    /**
     * Create the event listener.
     *
     * @param App\Services\Status $status  
     * @return void
     */
    public function __construct(Status $status)
    {
        $this->status = $status;
    }
}