<?php

namespace App\Livewire\Exams;

use App\Models\ExamResult;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Index extends Component
{
    public $areas;
    public $overLimit = false;
    public function render()
    {
        $user = Auth::user();
        // Si el usuario tiene status 0, se verifica el límite de exámenes del mes actual.
        if ($user->status == 0) {
            $currentWeekExamCount = ExamResult::query()
                ->where('user_id', $user->id)
                ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->count();

            if ($currentWeekExamCount >= 3) {
                $this->overLimit = true;
            }

        }

        return view('livewire.exams.index');
    }
}
