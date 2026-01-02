<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'client_name' => $this->client_name,
            'client_email' => $this->client_email,
            'client_phone' => $this->client_phone,
            'start_date' => $this->start_date->toDateString(),
            'end_date' => $this->end_date->toDateString(),
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'status' => $this->status,
            'event_space' => new EventSpaceResource($this->whenLoaded('eventSpace')),
<<<<<<< HEAD
            'staff' => $this->when($this->relationLoaded('staff'), function () {
                return $this->staff->map(function ($staffMember) {
                    return [
                        'id' => $staffMember->id,
                        'name' => $staffMember->user->name,
                        'position' => $staffMember->position,
                    ];
                });
            }),
            'staff_count' => $this->when($this->relationLoaded('staff'), $this->staff->count()),
=======
>>>>>>> parent of bcd2403 (push for reference cc)
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
