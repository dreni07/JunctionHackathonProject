<?php

use App\Services\PyramidKnowledgeIngestionService;
use App\Services\PyramidTableRegistry;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;

use function Pest\Laravel\seed;

beforeEach(function () {
    seed(RolePermissionSeeder::class);
});

describe('PyramidTableRegistry', function () {
    it('creates dynamic pyramid tables and inserts rows', function () {
        $registry = app(PyramidTableRegistry::class);

        $created = $registry->createTable('hall_capacities', [
            ['name' => 'hall_name', 'type' => 'string'],
            ['name' => 'capacity', 'type' => 'integer'],
        ]);

        expect($created['status'])->toBe('created')
            ->and($created['table'])->toBe('pyramid_data_hall_capacities')
            ->and(Schema::hasTable('pyramid_data_hall_capacities'))->toBeTrue();

        $inserted = $registry->insertRows('pyramid_data_hall_capacities', [
            ['hall_name' => 'Blue Hall', 'capacity' => 300],
            ['hall_name' => 'Orange Hall', 'capacity' => 200],
        ]);

        expect($inserted['inserted'])->toBe(2)
            ->and($inserted['row_count'])->toBe(2);
    });

    it('lists matching dynamic tables by topic', function () {
        $registry = app(PyramidTableRegistry::class);

        $registry->createTable('equipment_inventory', [
            ['name' => 'item_name', 'type' => 'string'],
        ]);

        $tables = $registry->listMatchingTables('equipment inventory');

        expect(collect($tables)->pluck('table'))->toContain('pyramid_data_equipment_inventory');
    });
});

describe('Pyramid ingest page', function () {
    it('is publicly accessible', function () {
        $this->get(route('pyramid.ingest.index'))
            ->assertOk();
    });

    it('shows ingested pyramid knowledge tables and rows', function () {
        $registry = app(PyramidTableRegistry::class);

        $registry->createTable('project_overview', [
            ['name' => 'official_name', 'type' => 'string'],
            ['name' => 'location', 'type' => 'string'],
        ]);

        $registry->insertRows('pyramid_data_project_overview', [
            ['official_name' => 'Pyramid of Tirana', 'location' => 'Tirana, Albania'],
        ]);

        $this->get(route('pyramid.knowledge.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('pyramid/explore')
                ->has('tables', 1)
                ->where('totalTables', 1)
                ->where('totalRows', 1)
                ->where('tables.0.table', 'pyramid_data_project_overview')
                ->where('tables.0.rows.0.official_name', 'Pyramid of Tirana'));
    });

    it('allows guests to ingest a pdf via json', function () {
        $this->mock(PyramidKnowledgeIngestionService::class, function ($mock): void {
            $mock->shouldReceive('ingest')
                ->once()
                ->andReturn([
                    'summary' => 'Created pyramid_data_halls with 2 rows.',
                    'extract_preview' => 'Blue Hall capacity 300.',
                    'character_count' => 120,
                    'tool_activity' => [
                        [
                            'name' => 'create_pyramid_table',
                            'input' => ['table_name' => 'pyramid_data_halls'],
                            'result' => '{"status":"created"}',
                        ],
                    ],
                ]);
        });

        $file = UploadedFile::fake()->create('pyramid-spaces.pdf', 100, 'application/pdf');

        $this->postJson(route('pyramid.ingest.store'), ['file' => $file])
            ->assertSuccessful()
            ->assertJsonPath('success', true)
            ->assertJsonPath('summary', 'Created pyramid_data_halls with 2 rows.');
    });
});
