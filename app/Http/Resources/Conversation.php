<?php

namespace App\Http\Resources;

use App\Contact;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\User as UserResource;
use App\Http\Resources\Message as MessageResource;
use App\Http\Resources\Contact as ContactResource;
use Illuminate\Support\Facades\Auth;

class Conversation extends JsonResource
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

        return [
            'id' => $this->id, 
            'messages' => MessageResource::collection($this->messages), 
            'other_user' => $other_user
        ];
    }
}
