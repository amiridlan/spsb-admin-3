<?php

namespace App\Models;

use Modules\Staff\Models\Staff as ModuleStaff;

/**
 * Backwards compatibility alias
 * @deprecated Use Modules\Staff\Models\Staff instead
 */
class Staff extends ModuleStaff
{
    // All functionality inherited from module model
}
