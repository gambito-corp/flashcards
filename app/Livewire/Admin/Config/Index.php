<?php

namespace App\Livewire\Admin\Config;

use App\Models\Config;
use Livewire\Component;

class Index extends Component
{
    public $paginate = 10;
    public $search = '';

    public function render()
    {
        $data = Config::query()->where('tipo', 'like', '%'.$this->search.'%')->paginate($this->paginate);
        return view('livewire.admin.config.index', compact('data'));
    }
}
