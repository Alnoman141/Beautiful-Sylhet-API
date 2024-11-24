<?php

namespace App\Jobs;

use App\Notifications\PasswordSentNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Notification;

class PasswordSentJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    protected $data;
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //TODO @fa 11/24/24  notification mail send to user
         Notification::route('mail', $this->data['email'])
            ->notify(new PasswordSentNotification($this->data));
    }
}
