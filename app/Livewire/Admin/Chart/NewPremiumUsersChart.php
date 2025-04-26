<?php

namespace App\Livewire\Admin\Chart;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NewPremiumUsersChart extends Component
{
    public $timeRange = 'week'; // day/week/month
    public $chartData = [];

    public function mount()
    {
        $this->loadChartData();
    }

    public function render()
    {
        return view('livewire.admin.chart.new-premium-users-chart');
    }

    public function updatedTimeRange()
    {
        $this->loadChartData();
    }

    private function loadChartData()
    {
        $data = [];
        $labels = [];

        switch ($this->timeRange) {
            case 'day':
                // Datos de últimos 8 días
                $results = User::select(DB::raw('DATE(premium_at) as date'), DB::raw('count(*) as count'))
                    ->where('status', 1)
                    ->whereNotNull('premium_at')
                    ->whereDate('premium_at', '>=', now()->subDays(7)->startOfDay())
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get();

                foreach ($results as $result) {
                    $labels[] = Carbon::parse($result->date)->format('d M');
                    $data[] = $result->count;
                }
                break;

            case 'week':
                // Datos de últimas 8 semanas
                $results = User::select(DB::raw('YEARWEEK(premium_at, 1) as week'), DB::raw('count(*) as count'))
                    ->where('status', 1)
                    ->whereNotNull('premium_at')
                    ->whereDate('premium_at', '>=', now()->subWeeks(7)->startOfWeek())
                    ->groupBy('week')
                    ->orderBy('week')
                    ->get();

                foreach ($results as $result) {
                    $labels[] = 'Semana ' . substr($result->week, -2);
                    $data[] = $result->count;
                }
                break;

            case 'month':
                // Datos de últimos 8 meses
                $results = User::select(DB::raw('DATE_FORMAT(premium_at, "%Y-%m") as month'), DB::raw('count(*) as count'))
                    ->where('status', 1)
                    ->whereNotNull('premium_at')
                    ->whereDate('premium_at', '>=', now()->subMonths(7)->startOfMonth())
                    ->groupBy('month')
                    ->orderBy('month')
                    ->get();

                foreach ($results as $result) {
                    $date = Carbon::createFromFormat('Y-m', $result->month);
                    $labels[] = $date->format('M Y');
                    $data[] = $result->count;
                }
                break;
        }

        $this->chartData = [
            'labels' => $labels,
            'data' => $data
        ];

        $this->dispatch('updatePremiumUserChart', $this->chartData);
    }
}
