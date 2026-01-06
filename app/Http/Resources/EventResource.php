<?php

namespace App\Http\Resources;

use Modules\Events\Http\Resources\EventResource as ModuleEventResource;

/**
 * Backwards compatibility alias
 * @deprecated Use Modules\Events\Http\Resources\EventResource instead
 */
class EventResource extends ModuleEventResource
{
    // All functionality inherited from module resource
}
