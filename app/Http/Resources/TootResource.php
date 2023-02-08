<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TootResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            ...parent::toArray($request),
            'user' => new UserResource($this->whenLoaded('user')),
            'replies' => TootResource::collection($this->whenLoaded('replies')),
        ];
    }
}
