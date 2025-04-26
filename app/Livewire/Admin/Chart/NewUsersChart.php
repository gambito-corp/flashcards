<?php

namespace App\Livewire\Admin\Chart;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class NewUsersChart extends Component
{
    public $timeRange = 'week'; // day/week/month
    public $chartData = [];

    public function mount()
    {
        $this->loadChartData();
    }

    public function render()
    {
        return view('livewire.admin.chart.new-users-chart');
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
                // Datos de últimos 8 días (incluyendo hoy)
                $results = User::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
                    ->whereDate('created_at', '>=', now()->subDays(7)->startOfDay())
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
                $results = User::select(DB::raw('YEARWEEK(created_at, 1) as week'), DB::raw('count(*) as count'))
                    ->whereDate('created_at', '>=', now()->subWeeks(7)->startOfWeek()) // 7 semanas atrás + actual = 8 semanas
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
                $results = User::select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('count(*) as count'))
                    ->whereDate('created_at', '>=', now()->subMonths(7)->startOfMonth()) // 7 meses atrás + actual = 8 meses
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

        $this->dispatch('updateUserChart', $this->chartData);
    }
}
