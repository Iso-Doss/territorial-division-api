<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * User account event.
 */
class UserAccountEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new user account event instance.
     *
     * @param User $user The user.
     * @param array $data The data.
     */
    public function __construct(public User $user, public array $data = [])
    {
        //
    }
}
