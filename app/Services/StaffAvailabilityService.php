<?php

namespace App\Services;

use Modules\Staff\Services\StaffAvailabilityService as ModuleStaffAvailabilityService;

/**
 * Backwards compatibility alias
 * @deprecated Use Modules\Staff\Services\StaffAvailabilityService instead
 */
class StaffAvailabilityService extends ModuleStaffAvailabilityService
{
    // All functionality inherited from module service
}
