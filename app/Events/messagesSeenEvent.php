<?php

namespace App\Events;

use App\Http\Resources\ConversationOnly;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

class messagesSeenEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $conversation;
    public $targetUser;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($conversation)
    {
        $this->conversation = $conversation;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        $users = $this->conversation->users;
        foreach ($users as $user) {
            if ($user->id != Auth::id())
                $this->targetUser = $user;
        }
        return new PrivateChannel('user.' . $this->targetUser->id);
    }

    public function broadcastWith()
    {
        return ['conversation_update' => new ConversationOnly($this->conversation)];
    }
}
