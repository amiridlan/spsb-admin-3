<?php

namespace App\Console\Commands;

use Modules\Events\Console\Commands\CompletePassedEvents as ModuleCompletePassedEvents;

/**
 * Backwards compatibility alias
 * @deprecated Use Modules\Events\Console\Commands\CompletePassedEvents instead
 */
class CompletePassedEvents extends ModuleCompletePassedEvents
{
    // All functionality inherited from module command
}
