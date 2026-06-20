<?php

namespace Database\Seeders;

use App\Enums\SpaceType;
use App\Models\AcousticRule;
use App\Models\BlackoutWindow;
use App\Models\BuildingLevel;
use App\Models\FacilityProfile;
use App\Models\InfrastructureSpec;
use App\Models\OccupancyStandard;
use App\Models\Space;
use App\Models\Tenant;
use App\Models\ZoneOperatingRule;
use Illuminate\Database\Seeder;

/**
 * Seeds the full "Data Schema & Operational Constraints Appendix" for the
 * Pyramid of Tirana: the facility profile, occupancy standards, the 48 room
 * boxes, level statistics, zone operating rules, blackout windows, acoustic
 * rules, and the infrastructure matrix. Idempotent.
 */
class PyramidFacilitySeeder extends Seeder
{
    public function run(): void
    {
        $this->seedFacilityProfile();
        $this->seedOccupancyStandards();
        $this->seedRooms();
        $this->seedBuildingLevels();
        $this->seedZoneOperatingRules();
        $this->seedBlackoutWindows();
        $this->seedAcousticRules();
        $this->seedInfrastructureSpecs();

        $this->command?->info('Seeded Pyramid facility appendix: '.Space::whereNotNull('room_code')->count().' rooms.');
    }

    private function seedFacilityProfile(): void
    {
        FacilityProfile::updateOrCreate(
            ['name' => 'Pyramid of Tirana'],
            [
                'total_footprint_sqm' => 11835,
                'height_m' => 24.5,
                'levels' => 6,
                'access_points' => 'Western Lift Matrix & 130 Exterior Terraced Steps',
                'allocation_rule' => '50% Non-Profit Youth Technology Education Space / 50% Commercial Rental',
                'active_box_area_sqm' => 5012,
                'total_boxes' => 48,
                'tumo_nodes' => 24,
                'public_nodes' => 24,
                'max_human_load' => 2549,
                'reference_baseline' => 'Module Core v1.3',
                'source' => 'Derived strictly from MVRDV structural layout indices and TUMO Tirana programmatic facility frameworks.',
            ],
        );
    }

    private function seedOccupancyStandards(): void
    {
        $rows = [
            ['Active Technology Labs', 2.0, 'Hardware development, robotics assembly, interactive workshops'],
            ['Event Space / Exhibition', 1.5, 'General public assembly, technology showcases, open networking'],
            ['Classrooms / Workshops', 3.5, 'Structured learning environments, desktop computing, lectures'],
            ['Startup Offices / Co-working', 4.0, 'Dedicated administrative workspaces and circulation channels'],
            ['Cafes / Reception Hubs', 2.5, 'Service spaces, public lounges, refreshment infrastructure'],
        ];

        foreach ($rows as [$category, $metric, $rule]) {
            OccupancyStandard::updateOrCreate(
                ['functional_category' => $category],
                ['area_metric_sqm' => $metric, 'allocation_rule' => $rule],
            );
        }
    }

    private function seedRooms(): void
    {
        foreach ($this->rooms() as $room) {
            [$code, $box, $floor, $zone, $type, $area, $cap, $workload] = $room;

            Space::updateOrCreate(
                ['room_code' => $code],
                [
                    'box_ref' => $box,
                    'zone_class' => $zone,
                    'tenant_id' => Tenant::resolveSpaceTenantId($zone, $type),
                    'name' => $type.' ('.$box.')',
                    'floor' => $floor,
                    'capacity' => $cap,
                    'type' => $this->spaceType($type)->value,
                    'functional_type' => $type,
                    'area_sqm' => $area,
                    'workload_target' => $workload,
                ],
            );
        }
    }

    private function seedBuildingLevels(): void
    {
        $rows = [
            [-1, 'Basement Level', 12, 1388, 6, 6, 728],
            [0, 'Ground Level', 12, 1336, 0, 12, 738],
            [1, 'First Floor', 12, 1188, 12, 0, 593],
            [2, 'Second Floor', 6, 520, 6, 0, 259],
            [3, 'Third Floor', 3, 315, 0, 3, 77],
            [4, 'Roof Level', 3, 265, 0, 3, 154],
        ];

        foreach ($rows as [$level, $label, $boxes, $footprint, $tumo, $public, $load]) {
            BuildingLevel::updateOrCreate(
                ['level' => $level],
                [
                    'label' => $label,
                    'active_boxes' => $boxes,
                    'box_footprint_sqm' => $footprint,
                    'tumo_nodes' => $tumo,
                    'public_nodes' => $public,
                    'max_human_load' => $load,
                ],
            );
        }
    }

    private function seedZoneOperatingRules(): void
    {
        $rows = [
            ['TUMO Educational Zone', '09:00 - 19:00', '09:00 - 17:00', 'Enforce absolute terminal lock; eject passive workloads'],
            ['Public Commercial Zone', '08:00 - 22:00', '09:00 - 21:00', 'Validate active commercial rental buffers during block'],
            ['Exterior Steps & Terraces', '24 / 7 Access', '24 / 7 Access', 'Inject environmental flag verification workflows'],
            ['Roof Oculus Platform', '10:00 - 20:00', '10:00 - 20:00', 'Limit to max 154 occupants; run continuous wind queries'],
        ];

        foreach ($rows as [$zone, $weekday, $weekend, $protocol]) {
            ZoneOperatingRule::updateOrCreate(
                ['zone_classification' => $zone],
                ['weekday_hours' => $weekday, 'weekend_hours' => $weekend, 'enforcement_protocol' => $protocol],
            );
        }
    }

    private function seedBlackoutWindows(): void
    {
        $rows = [
            ['TUMO Educational Zone', 'Weekdays', '19:00', '20:00', 'Automated student cleanup routines'],
            ['TUMO Educational Zone', 'Weekends', '17:00', '20:00', 'Three-hour weekend operational freeze'],
            ['Global Facility', 'Daily', '23:00', '06:00', 'Nightly global facility lock; overnight requests invalid unless admin override'],
        ];

        foreach ($rows as [$scope, $days, $start, $end, $reason]) {
            BlackoutWindow::updateOrCreate(
                ['scope' => $scope, 'days' => $days, 'start_time' => $start],
                ['end_time' => $end, 'reason' => $reason],
            );
        }
    }

    private function seedAcousticRules(): void
    {
        $rows = [
            ['Live Audio Tracking', 'Full-Stack Labs, Classrooms, Incubators', '1 Floor Vertical Delta OR 2 Room Horizontal Buffer'],
            ['Commercial Festival Staging', 'Audio Suites, Video Compositing, Offices', 'Absolute 1 Floor Vertical Structural Separation'],
            ['High-Density Hackathons', 'Audio Recording, Video Capture Rooms', 'Minimum 30-Meter Absolute Structural Radius'],
            ['Quiet Software Studios', 'Vocal Tracking, Robotics Prototyping, Cafes', 'Absolute Adjacency Block; Reject Shared Partition'],
        ];

        foreach ($rows as [$profile, $collision, $buffer]) {
            AcousticRule::updateOrCreate(
                ['event_target_profile' => $profile],
                ['collision_profile' => $collision, 'buffer_requirement' => $buffer],
            );
        }
    }

    private function seedInfrastructureSpecs(): void
    {
        $rows = [
            ['Educational Units', 'Laser projection array, mic array, 500 Mbps', 'Zoned Box HVAC', 'Western Lift Matrix Access', 15],
            ['Software Studios', 'Staged video conference rig, 1000 Mbps', 'Zoned Box HVAC', 'Western Lift Matrix Access', 25],
            ['Robotics Complexes', 'Multi-bus overhead outlet grid, 1000 Mbps', 'Zoned Box HVAC', 'Western Lift Matrix Access', 40],
            ['Animation Pipeline', 'Color-calibrated display nodes, 1000 Mbps', 'Zoned Box HVAC', 'Western Lift Matrix Access', 30],
            ['Audio Engine Suites', 'Balanced audio interfaces, studio monitors', 'Zoned Box HVAC', 'Western Lift Matrix Access', 25],
            ['Video Capture Units', 'Staged track lighting, virtual sets, 1000 Mbps', 'Zoned Box HVAC', 'Western Lift Matrix Access', 35],
            ['Commercial Tech Labs', 'Wall-mounted LED arrays, 1000 Mbps Fiber', 'Zoned Box HVAC', 'Western Lift Matrix Access', 30],
            ['Main Event Halls', 'Integrated matrix switchers, 500 Mbps Link', 'Zoned Box HVAC', 'Western Lift Matrix Access', 50],
            ['Hospitality Nodes', 'Digital signage nodes, kitchen support assets', 'Zoned Box HVAC', 'Western Lift Matrix Access', 35],
            ['Incubator Clusters', 'Shared conference rigs, 1000 Mbps Fiber', 'Zoned Box HVAC', 'Western Lift Matrix Access', 20],
        ];

        foreach ($rows as [$category, $assets, $climate, $ingress, $power]) {
            InfrastructureSpec::updateOrCreate(
                ['room_category' => $category],
                ['av_assets' => $assets, 'climate_support' => $climate, 'ingress_routing' => $ingress, 'power_kw' => $power],
            );
        }
    }

    private function spaceType(string $functionalType): SpaceType
    {
        return match ($functionalType) {
            'Event Hall', 'Event Space', 'Exhibition Space', 'Roof Event' => SpaceType::Hall,
            'Cafe', 'Reception', 'Lounge', 'Roof Cafe' => SpaceType::HybridSpace,
            'Viewing Deck' => SpaceType::Outdoor,
            default => SpaceType::WorkshopRoom,
        };
    }

    /**
     * The 48 boxes from Table 1.2.
     * [room_code, box_ref, floor, zone_class, functional_type, area_sqm, max_cap, workload_target]
     *
     * @return list<array{0:string,1:string,2:int,3:string,4:string,5:int,6:int,7:string}>
     */
    private function rooms(): array
    {
        return [
            // Level -1 (Basement) — TUMO Education Zone (B01-B06)
            ['RM-B1-001', 'B01', -1, 'TUMO', 'Classroom/Workshop', 72, 20, 'Foundational software coding workshops'],
            ['RM-B1-002', 'B02', -1, 'TUMO', 'Classroom/Workshop', 72, 20, 'Structured backend programming classes'],
            ['RM-B1-003', 'B03', -1, 'TUMO', 'Classroom/Workshop', 70, 20, 'Web design and interface configurations'],
            ['RM-B1-004', 'B04', -1, 'TUMO', 'Classroom/Workshop', 68, 19, 'Digital vector animation fundamentals'],
            ['RM-B1-005', 'B05', -1, 'TUMO', 'Classroom/Workshop', 70, 20, 'Autonomous robotics and hardware labs'],
            ['RM-B1-006', 'B06', -1, 'TUMO', 'Classroom/Workshop', 68, 19, 'Game engine environment development'],
            // Level -1 (Basement) — Public Commercial Zone (B07-B12)
            ['EV-B1-007', 'B07', -1, 'Public', 'Event Hall', 180, 120, 'Technical showcases and live hackathons'],
            ['EV-B1-008', 'B08', -1, 'Public', 'Event Hall', 175, 116, 'Corporate network staging and forums'],
            ['EX-B1-009', 'B09', -1, 'Public', 'Exhibition Space', 165, 110, 'Interactive art and graphics displays'],
            ['EX-B1-010', 'B10', -1, 'Public', 'Exhibition Space', 160, 106, 'Public software demonstration galleries'],
            ['EV-B1-011', 'B11', -1, 'Public', 'Event Space', 155, 103, 'Multi-functional project presentations'],
            ['EV-B1-012', 'B12', -1, 'Public', 'Event Space', 150, 100, 'Enterprise launch staging environments'],
            // Level 0 (Ground) — Public Commercial Zone (B13-B24)
            ['CF-G0-013', 'B13', 0, 'Public', 'Cafe', 95, 38, 'Public social hub and reception node'],
            ['CF-G0-014', 'B14', 0, 'Public', 'Cafe', 90, 36, 'Commercial hospitality service infrastructure'],
            ['RP-G0-015', 'B15', 0, 'Public', 'Reception', 120, 80, 'Central facility ingress management hub'],
            ['CF-G0-016', 'B16', 0, 'Public', 'Cafe', 88, 35, 'Secondary structural hospitality space'],
            ['CF-G0-017', 'B17', 0, 'Public', 'Cafe', 85, 34, 'Express public workspace and cafe unit'],
            ['LN-G0-018', 'B18', 0, 'Public', 'Lounge', 110, 44, 'Corporate meetup and networking zone'],
            ['TL-G0-019', 'B19', 0, 'Public', 'Tech Lab', 140, 70, 'Primary rapid hackathon sandbox space'],
            ['TL-G0-020', 'B20', 0, 'Public', 'Tech Lab', 135, 67, 'Advanced open source engineering labs'],
            ['TL-G0-021', 'B21', 0, 'Public', 'Tech Lab', 130, 65, 'Tech community meetups and open labs'],
            ['TL-G0-022', 'B22', 0, 'Public', 'Tech Lab', 125, 62, 'Hardware interfacing sandboxes'],
            ['TL-G0-023', 'B23', 0, 'Public', 'Tech Lab', 120, 60, 'Collaborative data engineering tracks'],
            ['TL-G0-024', 'B24', 0, 'Public', 'Tech Lab', 115, 57, 'Incubated project testing facilities'],
            // Level 1 (First) — TUMO Education Zone (B25-B36)
            ['SS-L1-025', 'B25', 1, 'TUMO', 'Software Studio', 105, 52, 'Full-stack software lifecycle studios'],
            ['SS-L1-026', 'B26', 1, 'TUMO', 'Software Studio', 105, 52, 'Advanced compiler and data structure labs'],
            ['RL-L1-027', 'B27', 1, 'TUMO', 'Robotics Lab', 130, 65, 'Embedded systems and mechatronics assembly'],
            ['RL-L1-028', 'B28', 1, 'TUMO', 'Robotics Lab', 125, 62, 'Sensor network and microprocessor analysis'],
            ['RL-L1-029', 'B29', 1, 'TUMO', 'Robotics Lab', 120, 60, 'AI-driven machine vision development labs'],
            ['RL-L1-030', 'B30', 1, 'TUMO', 'Robotics Lab', 115, 57, 'Kinematic control configuration modules'],
            ['AN-L1-031', 'B31', 1, 'TUMO', 'Animation Room', 95, 47, '3D asset pipeline modeling studios'],
            ['AN-L1-032', 'B32', 1, 'TUMO', 'Animation Room', 95, 47, 'Digital narrative rendering clusters'],
            ['CS-L1-033', 'B33', 1, 'TUMO', 'Creative Studio', 90, 45, 'Interface and UI design sandboxes'],
            ['CS-L1-034', 'B34', 1, 'TUMO', 'Creative Studio', 90, 45, 'Digital storytelling production units'],
            ['CS-L1-035', 'B35', 1, 'TUMO', 'Creative Studio', 88, 44, 'Vector-based digital product architecture'],
            ['CS-L1-036', 'B36', 1, 'TUMO', 'Creative Studio', 85, 42, 'User journey and UX design workshop hubs'],
            // Level 2 (Second) — TUMO Education Zone (B37-B42)
            ['MS-L2-037', 'B37', 2, 'TUMO', 'Music Studio', 80, 40, 'Digital audio engineering work suites'],
            ['MS-L2-038', 'B38', 2, 'TUMO', 'Music Studio', 80, 40, 'Acoustic track processing and multi-track'],
            ['MS-L2-039', 'B39', 2, 'TUMO', 'Music Studio', 75, 37, 'Spatial audio mixing and synthesis labs'],
            ['FS-L2-040', 'B40', 2, 'TUMO', 'Film Studio', 100, 50, 'Cinematic video sequence capture staging'],
            ['FS-L2-041', 'B41', 2, 'TUMO', 'Film Studio', 95, 47, 'Non-linear frame processing workshops'],
            ['FS-L2-042', 'B42', 2, 'TUMO', 'Film Studio', 90, 45, 'Visual effects compositing and pipelines'],
            // Level 3 (Third) — Public Commercial Zone (B43-B45)
            ['IN-L3-043', 'B43', 3, 'Public', 'Incubator', 110, 27, 'Ecosystem acceleration for tech startups'],
            ['IN-L3-044', 'B44', 3, 'Public', 'Incubator', 110, 27, 'Early-stage software enterprise nodes'],
            ['OF-L3-045', 'B45', 3, 'Public', 'Startup Office', 95, 23, 'Private venture development workspaces'],
            // Level 4 (Roof) — Public Commercial Zone (B46-B48)
            ['VP-L4-046', 'B46', 4, 'Public', 'Viewing Deck', 100, 66, 'Oculus observation and viewing platform'],
            ['EV-L4-047', 'B47', 4, 'Public', 'Roof Event', 85, 56, 'High-altitude open event staging'],
            ['CF-L4-048', 'B48', 4, 'Public', 'Roof Cafe', 80, 32, 'Rooftop hospitality and beverage node'],
        ];
    }
}
