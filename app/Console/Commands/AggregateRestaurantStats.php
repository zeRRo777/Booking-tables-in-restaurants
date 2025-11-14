<?php

namespace App\Console\Commands;

use App\Models\DailyOccupancyStat;
use App\Models\HourlyOccupancyStat;
use App\Models\MonthlyOccupancyStat;
use App\Models\Restaurant;
use App\Models\YearlyOccupancyStat;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class AggregateRestaurantStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stats:aggregate {--date=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Aggregate restaurant statistics for a given day.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $date = $this->option('date')
            ? Carbon::parse($this->option('date'))
            : Carbon::yesterday();

        $this->info("Starting aggregation for: {$date->toDateString()}");

        Restaurant::whereHas('status', function ($q) {
            $q->where('name', 'active');
        })->chunk(50, function ($restaurants) use ($date) {
            foreach ($restaurants as $restaurant) {
                $this->aggregateHourlyStats($restaurant, $date);
                $this->aggregateDailyStats($restaurant, $date);
                $this->aggregateMonthlyStats($restaurant, $date->year, $date->month);
                $this->aggregateYearlyStats($restaurant, $date->year);
            }
        });

        $this->info('Aggregation completed successfully.');
    }

    private function aggregateHourlyStats(Restaurant $restaurant, Carbon $date): void
    {
        $schedule = $this->getRestaurantSchedule($restaurant, $date);

        if (!$schedule || $schedule->is_closed) {
            return;
        }

        $totalTablesCount = $restaurant->tables()->count();
        if ($totalTablesCount === 0) {
            return;
        }

        $allDayReservations = $restaurant->reservations()
            ->whereHas('status', fn(Builder $q) => $q->where('name', 'Completed'))
            ->whereDate('starts_at', $date)
            ->with('table:id,restaurant_id')
            ->get();

        $opensAt = Carbon::parse($schedule->opens_at);
        $closesAt = Carbon::parse($schedule->closes_at);

        for ($hour = $opensAt->hour; $hour < $closesAt->hour; $hour++) {

            $hourStart = $date->copy()->setTime($hour, 0, 0);
            $hourEnd = $hourStart->copy()->addHour();

            $overlappingReservations = $allDayReservations->filter(function ($reservation) use ($hourStart, $hourEnd) {
                return $reservation->starts_at < $hourEnd && $reservation->ends_at > $hourStart;
            });

            $occupiedTablesCount = $overlappingReservations->pluck('table_id')->unique()->count();

            $occupancyPercent = ($occupiedTablesCount / $totalTablesCount) * 100;

            $reservationsStartedInHour = $allDayReservations->filter(function ($reservation) use ($hour) {
                return $reservation->starts_at->hour === $hour;
            });

            $reservationsCount = $reservationsStartedInHour->count();
            $guestsCount = $reservationsStartedInHour->sum('count_people');

            HourlyOccupancyStat::updateOrCreate(
                [
                    'restaurant_id' => $restaurant->id,
                    'date' => $date->toDateString(),
                    'hour' => $hour,
                ],
                [
                    'reservations_count' => $reservationsCount,
                    'guests_count' => $guestsCount,
                    'occupancy_percent' => round($occupancyPercent)
                ]
            );
        }
    }

    private function aggregateDailyStats(Restaurant $restaurant, Carbon $date): void
    {
        $schedule = $this->getRestaurantSchedule($restaurant, $date);

        if (!$schedule || $schedule->is_closed) {
            return;
        }

        $hourlyStats = HourlyOccupancyStat::where('restaurant_id', $restaurant->id)
            ->where('date', $date->toDateString())
            ->get();

        if ($hourlyStats->isEmpty()) {
            return;
        }

        $totalReservations = $hourlyStats->sum('reservations_count');
        $totalGuests = $hourlyStats->sum('guests_count');
        $averageOccupancyPercent = $hourlyStats->avg('occupancy_percent');

        $peakHourStat = $hourlyStats->sortByDesc('occupancy_percent')->first();
        $offPeakHourStat = $hourlyStats->sortBy('occupancy_percent')->first();

        DailyOccupancyStat::updateOrCreate(
            [
                'restaurant_id' => $restaurant->id,
                'date' => $date->toDateString(),
            ],
            [
                'total_reservations' => $totalReservations,
                'total_guests' => $totalGuests,
                'average_occupancy_percent' => round($averageOccupancyPercent),
                'peak_hour' => $peakHourStat ? Carbon::createFromTime($peakHourStat->hour)->toTimeString() : null,
                'off_peak_hour' => $offPeakHourStat ? Carbon::createFromTime($offPeakHourStat->hour)->toTimeString() : null,
            ]
        );
    }
    private function aggregateMonthlyStats(Restaurant $restaurant, int $year, int $month): void
    {
        $dailyStats = DailyOccupancyStat::where('restaurant_id', $restaurant->id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get();

        if ($dailyStats->isEmpty()) {
            return;
        }

        $totalReservations = $dailyStats->sum('total_reservations');
        $totalGuests = $dailyStats->sum('total_guests');
        $averageOccupancyPercent = $dailyStats->avg('average_occupancy_percent');

        $peakDayStat = $dailyStats->sortByDesc('average_occupancy_percent')->first();
        $offPeakDayStat = $dailyStats->sortBy('average_occupancy_percent')->first();

        MonthlyOccupancyStat::updateOrCreate(
            [
                'restaurant_id' => $restaurant->id,
                'year' => $year,
                'month' => $month,
            ],
            [
                'total_reservations' => $totalReservations,
                'total_guests' => $totalGuests,
                'average_occupancy_percent' => round($averageOccupancyPercent),
                'peak_day' => $peakDayStat?->date,
                'off_peak_day' => $offPeakDayStat?->date,
            ]
        );
    }
    private function aggregateYearlyStats(Restaurant $restaurant, int $year): void
    {
        $monthlyStats = MonthlyOccupancyStat::where('restaurant_id', $restaurant->id)
            ->where('year', $year)
            ->get();

        if ($monthlyStats->isEmpty()) {
            return;
        }

        $totalReservations = $monthlyStats->sum('total_reservations');
        $totalGuests = $monthlyStats->sum('total_guests');
        $averageOccupancyPercent = $monthlyStats->avg('average_occupancy_percent');

        $peakMonthStat = $monthlyStats->sortByDesc('average_occupancy_percent')->first();
        $offPeakMonthStat = $monthlyStats->sortBy('average_occupancy_percent')->first();

        YearlyOccupancyStat::updateOrCreate(
            [
                'restaurant_id' => $restaurant->id,
                'year' => $year,
            ],
            [
                'total_reservations' => $totalReservations,
                'total_guests' => $totalGuests,
                'average_occupancy_percent' => round($averageOccupancyPercent),
                'peak_month' => $peakMonthStat?->month,
                'off_peak_month' => $offPeakMonthStat?->month,
            ]
        );
    }

    private function getRestaurantSchedule(Restaurant $restaurant, Carbon $date): ?object
    {
        $specialSchedule = $restaurant->schedules()->where('date', $date->toDateString())->first();

        if ($specialSchedule) {
            return $specialSchedule;
        }

        if ($date->isWeekend()) {
            return (object)[
                'opens_at' => $restaurant->weekend_opens_at,
                'closes_at' => $restaurant->weekend_closes_at,
                'is_closed' => false,
            ];
        }

        return (object)[
            'opens_at' => $restaurant->weekdays_opens_at,
            'closes_at' => $restaurant->weekdays_closes_at,
            'is_closed' => false,
        ];
    }
}
