<?php

namespace App\Http\Resources;

use Modules\Events\Http\Resources\EventSpaceResource as ModuleEventSpaceResource;

/**
 * Backwards compatibility alias
 * @deprecated Use Modules\Events\Http\Resources\EventSpaceResource instead
 */
class EventSpaceResource extends ModuleEventSpaceResource
{
    // All functionality inherited from module resource
}
