<?php

namespace App\Livewire\Admin\Chart;

use Livewire\Component;
use App\Models\ExamResult;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ExamsPerUserChart extends Component
{
    public $timeRange = 'week'; // day/week/month
    public $chartData = [];

    public function mount()
    {
        $this->loadChartData();
    }

    public function render()
    {
        return view('livewire.admin.chart.exams-per-user-chart');
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
                // Últimos 7 días
                $results = ExamResult::select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('count(*) as count')
                )
                    ->whereDate('created_at', '>=', now()->subDays(7))
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get();

                foreach ($results as $result) {
                    $labels[] = Carbon::parse($result->date)->format('d M');
                    $data[] = $result->count;
                }
                break;

            case 'week':
                // Últimas 7 semanas
                $results = ExamResult::select(
                    DB::raw('YEARWEEK(created_at, 1) as week'),
                    DB::raw('count(*) as count')
                )
                    ->whereDate('created_at', '>=', now()->subWeeks(7))
                    ->groupBy('week')
                    ->orderBy('week')
                    ->get();

                foreach ($results as $result) {
                    $labels[] = 'Sem ' . substr($result->week, -2);
                    $data[] = $result->count;
                }
                break;

            case 'month':
                // Últimos 7 meses
                $results = ExamResult::select(
                    DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                    DB::raw('count(*) as count')
                )
                    ->whereDate('created_at', '>=', now()->subMonths(7))
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

        // Rellenar con ceros si no hay datos
        if (empty($labels)) {
            $labels = array_fill(0, 8, 'Sin datos');
            $data = array_fill(0, 8, 0);
        }

        $this->chartData = [
            'labels' => $labels,
            'data' => $data
        ];

        $this->dispatch('updateExamChart', chartData: $this->chartData);
    }
}
