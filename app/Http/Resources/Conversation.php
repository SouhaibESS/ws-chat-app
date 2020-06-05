<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\User as UserResource;
use App\Http\Resources\Message as MessageResource;
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
        // return parent::toArray($request);
        $users = $this->users;
        foreach ($users as $u) {
            if($u->id != 1)
                $user = $u;
        }


        return [
            'id' => $this->id, 
            'updated_at' => $this->updated_at,
            'messages' => MessageResource::collection($this->messages), 
            'other_user' => new UserResource($user)
        ];
    }
}
