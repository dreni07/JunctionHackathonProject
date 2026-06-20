<?php

namespace Database\Seeders;

use App\Models\PricingReference;
use Illuminate\Database\Seeder;

/**
 * Seeds the historical event pricing dataset (mock_pyramid_events_dataset.csv).
 * These rows are the pricing agent's initial training data. Idempotent.
 */
class PricingReferenceSeeder extends Seeder
{
    public function run(): void
    {
        // [organizer, event_type, venue_name, floor, area_sqm, duration_days, attendees, price_eur, notes]
        $rows = [
            ['NovaTech Solutions', 'Product launch', 'Rooftop Deck', 'Roof (L+4)', 250, 1, 180, 2150, 'Premium rooftop rate; AV setup included'],
            ['Balkan FinTech Forum', 'Conference', 'Box B2', '3rd Floor', 180, 2, 220, 2000, 'Multi-day conference; standard interior rate'],
            ['CodeSpark Hackathon Series', 'Hackathon', 'Box A4', 'Ground Floor (L0)', 110, 2, 90, 1250, 'Overnight stay allowance factored into fee'],
            ['Adriatic Fashion Week', 'Runway show', 'Rooftop Deck', 'Roof (L+4)', 300, 1, 250, 2700, 'Largest rooftop booking; high setup complexity'],
            ['GreenLeaf Sustainability Expo', 'Exhibition', 'Exterior Box Cluster', 'Exterior Boxes', 240, 3, 400, 2300, 'Multi-box exterior rental; high foot traffic'],
            ['Tirana Art Collective', 'Gallery opening', 'Box C2', 'Ground Floor (L0)', 95, 1, 120, 650, 'Low complexity; single evening event'],
            ['Skyline Ventures', 'Investor networking gala', 'Rooftop Deck', 'Roof (L+4)', 150, 1, 100, 1400, 'Evening event; catering required'],
            ['EduTech Albania', 'Workshop series', 'Box D1', '-1 Floor', 130, 4, 60, 2050, 'Recurring multi-day educational workshops'],
            ['Prishtina Startup Network', 'Pitch competition', 'Box B5', '3rd Floor', 160, 1, 150, 1150, 'Single-day competitive event'],
            ['Lumea Wellness Brand', 'Pop-up retail activation', 'Exterior Box E3', 'Exterior Boxes', 70, 5, 0, 1150, 'Foot-traffic based activation; no fixed attendee count'],
        ];

        foreach ($rows as $r) {
            [$organizer, $type, $venue, $floor, $area, $days, $attendees, $price, $notes] = $r;

            PricingReference::updateOrCreate(
                ['source' => 'dataset', 'organizer' => $organizer, 'event_type' => $type],
                [
                    'venue_name' => $venue,
                    'floor' => $floor,
                    'area_sqm' => $area,
                    'duration_days' => $days,
                    'attendees' => $attendees,
                    'price_eur' => $price,
                    'price_per_sqm' => round($price / $area, 2),
                    'notes' => $notes,
                ],
            );
        }

        $this->command?->info('Seeded '.count($rows).' pricing reference events.');
    }
}
