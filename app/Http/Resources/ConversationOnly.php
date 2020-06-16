<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\User as UserResource;
use App\Http\Resources\Message as MessageResource;
use App\Http\Resources\Contact as ContactResource;
use Illuminate\Support\Facades\Auth;
use App\Contact;

class ConversationOnly extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $users = $this->users;
        foreach ($users as $u) {
            if($u->id != Auth::id())
                $user = $u;
        }

        $contact = Contact::where('email', $user->email)->first();

        if($contact)
            $other_user = new ContactResource($contact);
        else 
            $other_user = new UserResource($user);

        $lastMessage = $this->messages()->latest()->first();

        return [
            'id' => $this->id, 
            'last_message' => new MessageResource($lastMessage), 
            'other_user' => $other_user
        ];
    }
}
