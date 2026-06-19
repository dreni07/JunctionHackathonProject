<?php

use App\Models\Alert;
use App\Models\Approval;
use App\Models\Asset;
use App\Models\AssetMovement;
use App\Models\AssetReservation;
use App\Models\Conflict;
use App\Models\Event;
use App\Models\EventContact;
use App\Models\EventRequest;
use App\Models\FinalProposal;
use App\Models\QuotationLineItem;
use App\Models\Reservation;
use App\Models\Space;
use App\Models\Task;

return [

    /*
    |--------------------------------------------------------------------------
    | Operational sync models
    |--------------------------------------------------------------------------
    |
    | Models observed for create/update/delete. Each change dispatches
    | OperationalModelChanged for polling and future external sync listeners.
    |
    */

    'models' => [
        Asset::class,
        AssetMovement::class,
        AssetReservation::class,
        Space::class,
        Reservation::class,
        Event::class,
        EventRequest::class,
        FinalProposal::class,
        QuotationLineItem::class,
        Task::class,
        Conflict::class,
        Alert::class,
        Approval::class,
        EventContact::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Poll feed retention
    |--------------------------------------------------------------------------
    |
    | Changes older than this many hours may be pruned by a scheduled command.
    |
    */

    'retention_hours' => (int) env('OPERATIONAL_SYNC_RETENTION_HOURS', 48),

];
