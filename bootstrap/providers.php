<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\FortifyServiceProvider::class,

    // Module Service Providers
    Modules\Events\Providers\EventServiceProvider::class,
    Modules\Staff\Providers\StaffServiceProvider::class,
];
