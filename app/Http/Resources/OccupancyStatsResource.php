<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OccupancyStatsResource extends JsonResource
{
    private int $restaurantId;
    private string $period;
    private Carbon $date;

    public function __construct($resource, int $restaurantId, string $period, Carbon $date)
    {
        parent::__construct($resource);
        $this->restaurantId = $restaurantId;
        $this->period = $period;
        $this->date = $date;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $summaryData = $this['summary'];
        $detailsData = $this['details'];

        return [
            'restaurant_id' => $this->restaurantId,
            'period' => $this->period,
            'date' => $this->formatDate(),
            'summary' => $this->formatSummary($summaryData),
            'details' => $this->formatDetails($detailsData),
        ];
    }

    private function formatDate(): string
    {
        return match ($this->period) {
            'day' => $this->date->format('Y-m-d'),
            'month' => $this->date->format('Y-m'),
            'year' => $this->date->format('Y'),
        };
    }

    private function formatSummary($data): array
    {
        if (!$data) {
            return [
                'total_reservations' => 0,
                'total_guests' => 0,
                'average_occupancy_percent' => 0,
            ];
        }

        $summary = [
            'total_reservations' => $data->total_reservations,
            'total_guests' => $data->total_guests,
            'average_occupancy_percent' => $data->average_occupancy_percent,
        ];

        switch ($this->period) {
            case 'day':
                $summary['peak_hour'] = $data->peak_hour;
                $summary['off_peak_hour'] = $data->off_peak_hour;
                break;
            case 'month':
                $summary['peak_day'] = $data->peak_day;
                $summary['off_peak_day'] = $data->off_peak_day;
                break;
            case 'year':
                $summary['peak_month'] = Carbon::create()->month($data->peak_month)->format('F');
                $summary['off_peak_month'] = Carbon::create()->month($data->off_peak_month)->format('F');
                break;
        }

        return $summary;
    }

    private function formatDetails($data): array
    {
        return match ($this->period) {
            'day' => $this->formatDayDetails($data),
            'month' => $this->formatMonthDetails($data),
            'year' => $this->formatYearDetails($data),
        };
    }

    private function formatDayDetails($details): array
    {
        return $details->map(fn($item) => [
            'hour' => str_pad($item->hour, 2, '0', STR_PAD_LEFT) . ':00',
            'occupancy_percent' => $item->occupancy_percent,
            'reservations_count' => $item->reservations_count,
            'guests_count' => $item->guests_count,
        ])->toArray();
    }

    private function formatMonthDetails($details): array
    {
        return $details->map(fn($item) => [
            'date' => $item->date,
            'day_of_week' => Carbon::parse($item->date)->format('l'),
            'occupancy_percent' => $item->average_occupancy_percent,
            'reservations_count' => $item->total_reservations,
            'guests_count' => $item->total_guests,
        ])->toArray();
    }

    private function formatYearDetails($details): array
    {
        return $details->map(fn($item) => [
            'month' => Carbon::create()->month($item->month)->format('F'),
            'occupancy_percent' => $item->average_occupancy_percent,
            'reservations_count' => $item->total_reservations,
            'guests_count' => $item->total_guests,
        ])->toArray();
    }
}
