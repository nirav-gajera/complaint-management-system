<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ComplaintResource;
use App\Models\Complaint;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DashboardController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'period' => ['nullable', 'string', Rule::in(['day', 'week', 'month', 'year'])],
        ]);

        $baseQuery = Complaint::query();
        $period = $validated['period'] ?? 'month';

        return response()->json([
            'stats' => [
                'total' => (clone $baseQuery)->count(),
                'pending' => (clone $baseQuery)->where('status', Complaint::STATUS_PENDING)->count(),
                'in_progress' => (clone $baseQuery)->where('status', Complaint::STATUS_IN_PROGRESS)->count(),
                'resolved' => (clone $baseQuery)->where('status', Complaint::STATUS_RESOLVED)->count(),
                'rejected' => (clone $baseQuery)->where('status', Complaint::STATUS_REJECTED)->count(),
            ],
            'selected_period' => $period,
            'trend' => $this->buildTrend($period),
            'recent_complaints' => ComplaintResource::collection(
                Complaint::query()
                    ->with(['category', 'assignee', 'attachments', 'user'])
                    ->latest()
                    ->take(5)
                    ->get()
            ),
        ]);
    }

    protected function buildTrend(string $period): array
    {
        return match ($period) {
            'day' => $this->buildDayTrend(),
            'week' => $this->buildWeekTrend(),
            'year' => $this->buildYearTrend(),
            default => $this->buildMonthTrend(),
        };
    }

    protected function buildDayTrend(): array
    {
        $start = CarbonImmutable::now()->startOfDay();
        $totals = $this->countComplaintsByBucket(
            $start,
            $start->endOfDay(),
            fn(CarbonImmutable $date) => $date->startOfHour()->format('Y-m-d H:00')
        );
        $buckets = [];

        for ($hour = 0; $hour < 24; $hour++) {
            $point = $start->addHours($hour);
            $key = $point->format('Y-m-d H:00');

            $buckets[] = [
                'key' => $key,
                'label' => $point->format('H:00'),
                'total' => $totals[$key] ?? 0,
            ];
        }

        return $buckets;
    }

    protected function buildWeekTrend(): array
    {
        $start = CarbonImmutable::now()->subDays(6)->startOfDay();
        $end = CarbonImmutable::now()->endOfDay();
        $totals = $this->countComplaintsByBucket(
            $start,
            $end,
            fn(CarbonImmutable $date) => $date->toDateString()
        );
        $buckets = [];

        for ($day = 0; $day < 7; $day++) {
            $point = $start->addDays($day);
            $key = $point->toDateString();

            $buckets[] = [
                'key' => $key,
                'label' => $point->format('D'),
                'total' => $totals[$key] ?? 0,
            ];
        }

        return $buckets;
    }

    protected function buildMonthTrend(): array
    {
        $start = CarbonImmutable::now()->startOfMonth();
        $end = $start->endOfMonth();
        $totals = $this->countComplaintsByBucket(
            $start,
            $end,
            fn(CarbonImmutable $date) => $date->toDateString()
        );
        $buckets = [];

        for ($day = 0; $day < $start->daysInMonth; $day++) {
            $point = $start->addDays($day);
            $key = $point->toDateString();

            $buckets[] = [
                'key' => $key,
                'label' => $point->format('j M'),
                'total' => $totals[$key] ?? 0,
            ];
        }

        return $buckets;
    }

    protected function buildYearTrend(): array
    {
        $start = CarbonImmutable::now()->startOfYear();
        $totals = $this->countComplaintsByBucket(
            $start,
            $start->endOfYear(),
            fn(CarbonImmutable $date) => $date->format('Y-m')
        );
        $buckets = [];

        for ($month = 0; $month < 12; $month++) {
            $point = $start->addMonths($month);
            $key = $point->format('Y-m');

            $buckets[] = [
                'key' => $key,
                'label' => $point->format('M'),
                'total' => $totals[$key] ?? 0,
            ];
        }

        return $buckets;
    }

    protected function countComplaintsByBucket(CarbonImmutable $start, CarbonImmutable $end, callable $resolver): array
    {
        $totals = [];

        Complaint::query()
            ->whereBetween('created_at', [$start, $end])
            ->pluck('created_at')
            ->each(function ($createdAt) use (&$totals, $resolver): void {
                $key = $resolver(CarbonImmutable::parse($createdAt));
                $totals[$key] = ($totals[$key] ?? 0) + 1;
            });

        return $totals;
    }
}
