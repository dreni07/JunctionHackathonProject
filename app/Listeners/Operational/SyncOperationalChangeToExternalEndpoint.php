<?php

declare(strict_types=1);

namespace App\Listeners\Operational;

use App\Events\Operational\OperationalModelChanged;

/**
 * Placeholder for a future Pyramid / external digital-twin sync.
 *
 * Wire HTTP calls to a third-party endpoint here when an API becomes
 * available. The poll feed is populated separately by RecordOperationalChange.
 */
class SyncOperationalChangeToExternalEndpoint
{
    public function handle(OperationalModelChanged $event): void
    {
        //
    }
}
