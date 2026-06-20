<?php

namespace Database\Seeders;

use App\Enums\BookingStatus;
use App\Models\Reservation;
use App\Models\Space;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Seeds the booking calendar with existing reservations so the scheduling agent
 * has real conflicts to route around. Idempotent (keyed by space + start time).
 */
class ReservationSeeder extends Seeder
{
    public function run(): void
    {
        $base = Carbon::now()->startOfWeek()->addWeek(); // next Monday

        // [room_code, days from next Monday, start hour, end hour, status]
        $bookings = [
            ['EV-B1-007', 5, 18, 22, BookingStatus::Confirmed],  // Sat evening — main event hall
            ['EV-B1-008', 4, 14, 18, BookingStatus::Tentative],  // Fri afternoon
            ['TL-G0-019', 5, 9, 17, BookingStatus::Confirmed],   // Sat all-day hackathon — top tech lab
            ['TL-G0-020', 5, 9, 17, BookingStatus::Tentative],
            ['EX-B1-009', 6, 10, 16, BookingStatus::Confirmed],  // Sun exhibition
            ['EV-B1-011', 4, 18, 21, BookingStatus::Tentative],
            ['CF-G0-013', 2, 12, 15, BookingStatus::Confirmed],
            ['LN-G0-018', 3, 17, 20, BookingStatus::Tentative],
            ['IN-L3-043', 1, 9, 18, BookingStatus::Confirmed],
            ['VP-L4-046', 5, 19, 22, BookingStatus::Tentative],  // rooftop Sat evening
            ['EV-L4-047', 6, 18, 22, BookingStatus::Confirmed],
            ['TL-G0-021', 12, 9, 17, BookingStatus::Tentative],
        ];

        $spaces = Space::query()->whereNotNull('room_code')->get()->keyBy('room_code');
        $count = 0;

        foreach ($bookings as [$roomCode, $days, $startHour, $endHour, $status]) {
            $space = $spaces->get($roomCode);

            if ($space === null) {
                continue;
            }

            $startAt = $base->copy()->addDays($days)->setTime($startHour, 0);

            Reservation::updateOrCreate(
                ['space_id' => $space->id, 'start_at' => $startAt],
                [
                    'end_at' => $base->copy()->addDays($days)->setTime($endHour, 0),
                    'status' => $status->value,
                ],
            );

            $count++;
        }

        $this->command?->info("Seeded {$count} calendar reservations.");
    }
}
