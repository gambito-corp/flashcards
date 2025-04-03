<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\Product;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $exams = Exam::query()
            ->with('examResults')
            ->whereHas('examResults', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->latest()
            ->take(5)
            ->get();

        $userId = auth()->id();
        $results = ExamResult::query()->where('user_id', $userId)->orderBy('created_at')->get();

        // Agrupación diaria
        $daily = $results->groupBy(function($item) {
            return \Carbon\Carbon::parse($item->created_at)->format('d/m/Y');
        })->map(function($group) {
            return round($group->avg('total_score'), 2);
        });

        // Agrupación semanal (por semana del año y año)
        $weekly = $results->groupBy(function($item) {
            return \Carbon\Carbon::parse($item->created_at)->format('W/Y');
        })->map(function($group) {
            return round($group->avg('total_score'), 2);
        });

        // Agrupación mensual
        $monthly = $results->groupBy(function($item) {
            return \Carbon\Carbon::parse($item->created_at)->format('m/Y');
        })->map(function($group) {
            return round($group->avg('total_score'), 2);
        });

        $dailyLabels = $daily->keys();
        $dailyData    = $daily->values();

        $weeklyLabels = $weekly->keys();
        $weeklyData   = $weekly->values();

        $monthlyLabels = $monthly->keys();
        $monthlyData   = $monthly->values();
        return view('index.dashboard', compact('exams', 'dailyLabels', 'dailyData', 'weeklyLabels', 'weeklyData', 'monthlyLabels', 'monthlyData'));
    }

    public function landing()
    {
        return view('index.landing');
    }
}
