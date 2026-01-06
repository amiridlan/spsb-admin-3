<?php

namespace App\Models;

use Modules\Events\Models\Event as ModuleEvent;

/**
 * Backwards compatibility alias
 * @deprecated Use Modules\Events\Models\Event instead
 */
class Event extends ModuleEvent
{
    // All functionality inherited from module model
}
