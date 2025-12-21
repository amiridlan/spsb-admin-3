<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventSpaceResource;
use App\Models\EventSpace;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class EventSpaceController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        $spaces = EventSpace::where('is_active', true)
            ->orderBy('name')
            ->get();

        return $this->success(EventSpaceResource::collection($spaces));
    }

    public function show(EventSpace $eventSpace): JsonResponse
    {
        if (!$eventSpace->is_active) {
            return $this->error('Event space not available', 404);
        }

        return $this->success(new EventSpaceResource($eventSpace));
    }
}
