<?php

namespace Platform\Training\Livewire;

use Livewire\Component;

class Sidebar extends Component
{
    public function render()
    {
        $user = auth()->user();

        if (!$user) {
            return view('training::livewire.sidebar', []);
        }

        return view('training::livewire.sidebar', []);
    }
}
