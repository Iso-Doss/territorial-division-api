<?php

namespace App\Listeners;

use App\Events\UserAccountEvent;
use App\Notifications\UserAccountNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * User account listener.
 */
class UserAccountListener implements ShouldQueue
{
    /**
     * Create the user account event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the user account event.
     *
     * @param UserAccountEvent $event The user account event.
     * @return void
     */
    public function handle(UserAccountEvent $event): void
    {
        $event->user->notify(new UserAccountNotification($event->user, $event->data));
    }
}
