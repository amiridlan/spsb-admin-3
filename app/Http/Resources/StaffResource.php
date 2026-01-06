<?php

namespace App\Http\Resources;

use Modules\Staff\Http\Resources\StaffResource as ModuleStaffResource;

/**
 * Backwards compatibility alias
 * @deprecated Use Modules\Staff\Http\Resources\StaffResource instead
 */
class StaffResource extends ModuleStaffResource
{
    // All functionality inherited from module resource
}
