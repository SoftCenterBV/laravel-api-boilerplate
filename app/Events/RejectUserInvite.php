<?php

namespace App\Events;

use App\Models\OrganizationUserInvite;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RejectUserInvite
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    protected OrganizationUserInvite $invite;

    /**
     * Create a new event instance.
     */
    public function __construct(OrganizationUserInvite $invite)
    {
        $this->invite = $invite;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
