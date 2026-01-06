<?php

namespace App\Models;

use Modules\Events\Models\EventSpace as ModuleEventSpace;

/**
 * Backwards compatibility alias
 * @deprecated Use Modules\Events\Models\EventSpace instead
 */
class EventSpace extends ModuleEventSpace
{
    // All functionality inherited from module model
}
