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
use App\Http\Resources\Message as MessageResource;
use App\Http\Resources\User as UserResource;
use Illuminate\Support\Facades\Auth;

class newMessageEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($message)
    {
        $this->message = $message;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        $conversation = $this->message->conversation;
        $users = $conversation->users;
        foreach ($users as $user) {
            if($user->id != Auth::id())
                $targetUserId = $user->id;
        }
        return new PrivateChannel('user.' . $targetUserId);
    }

    public function broadcastWith()
    {
        $conversation = $this->message->conversation;
        return [
            'conversation_update' =>[
                'id' => $conversation->id, 
                'updated_at' => $conversation->updated_at,
                'last_message' => new MessageResource($this->message), 
                'other_user' => new UserResource(Auth::user())
        ]];
    }
}
