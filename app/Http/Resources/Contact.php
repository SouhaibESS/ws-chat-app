<?php

namespace App\Http\Resources;

use App\User;
use Illuminate\Http\Resources\Json\JsonResource;

class Contact extends JsonResource
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
        if ($this->is_registered) {
            $user = User::where('email', $this->email)->first();
            return [
                'contact_id' => $this->id,
                'name' => $this->name,
                'email' => $this->email,
                'id' => $user->id,
                'is_registered' => $this->is_registered,
                'avatar' => $user->avatar
            ];
        } else {
            $noAvatar = env('IMAGES_FOLDER') . '/users/no_image.png';
            return [
                'contact_id' => $this->id,
                'name' => $this->name,
                'email' => $this->email,
                'id' => null,
                'is_registered' => $this->is_registered,
                'avatar' => $noAvatar
            ];
        }
    }
}
