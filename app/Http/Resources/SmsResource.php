<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SmsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'phone_number' => $this->phone_number,
            'user_name' => $this->user_name,
            'server_name' => $this->server_name,
      
        ];
    }
}
