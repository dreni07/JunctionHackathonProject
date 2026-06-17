<?php

use App\Enums\AssetStatus;
use App\Enums\AssetType;
use App\Models\Asset;
use App\Models\OperationalChange;
use App\Models\User;

test('creating an observed model records an operational change', function () {
    $asset = Asset::query()->create([
        'name' => 'Demo chair',
        'type' => AssetType::Chair,
        'qr_code' => 'QR-DEMO-001',
        'status' => AssetStatus::Available,
    ]);

    $change = OperationalChange::query()->first();

    expect($change)->not->toBeNull()
        ->and($change->model_type)->toBe('Asset')
        ->and($change->model_id)->toBe($asset->id)
        ->and($change->action)->toBe('created')
        ->and($change->summary)->toContain('Demo chair');
});

test('updating an observed model records changed attributes', function () {
    $asset = Asset::query()->create([
        'name' => 'Demo chair',
        'type' => AssetType::Chair,
        'qr_code' => 'QR-DEMO-002',
        'status' => AssetStatus::Available,
    ]);

    OperationalChange::query()->delete();

    $asset->update(['status' => AssetStatus::InUse]);

    $change = OperationalChange::query()->sole();

    expect($change->action)->toBe('updated')
        ->and($change->payload['changes'])->toHaveKey('status')
        ->and($change->summary)->toContain('status');
});

test('authenticated users can poll operational changes since a cursor', function () {
    $user = User::factory()->create();

    $asset = Asset::query()->create([
        'name' => 'Poll chair',
        'type' => AssetType::Chair,
        'qr_code' => 'QR-POLL-001',
        'status' => AssetStatus::Available,
    ]);

    $firstChange = OperationalChange::query()->where('model_id', $asset->id)->first();

    $asset->update(['status' => AssetStatus::Reserved]);

    $secondChange = OperationalChange::query()
        ->where('model_id', $asset->id)
        ->where('action', 'updated')
        ->first();

    $initialPoll = $this->actingAs($user)
        ->getJson(route('operational.changes.poll'))
        ->assertSuccessful()
        ->json();

    expect($initialPoll['success'])->toBeTrue()
        ->and($initialPoll['changes'])->not->toBeEmpty()
        ->and(collect($initialPoll['changes'])->pluck('id'))->toContain($firstChange->id);

    $sincePoll = $this->actingAs($user)
        ->getJson(route('operational.changes.poll', ['since' => $firstChange->id]))
        ->assertSuccessful()
        ->json();

    expect($sincePoll['changes'])->toHaveCount(1)
        ->and($sincePoll['changes'][0]['id'])->toBe($secondChange->id)
        ->and($sincePoll['last_id'])->toBe($secondChange->id);
});

test('guests cannot poll operational changes', function () {
    $this->get(route('operational.changes.poll'))
        ->assertRedirect(route('login'));
});
