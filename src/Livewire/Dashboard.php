<?php

namespace Platform\Training\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Platform\Training\Models\TrainingGroup;
use Platform\Training\Models\Training;
use Platform\Training\Models\TrainingSession;

class Dashboard extends Component
{
    public function rendered()
    {
        $this->dispatch('comms', [
            'model' => null,
            'modelId' => null,
            'subject' => 'Training Dashboard',
            'description' => 'Schulungsverwaltung Dashboard',
            'url' => route('training.dashboard'),
            'source' => 'training.dashboard',
            'recipients' => [],
            'meta' => [
                'view_type' => 'dashboard',
            ],
        ]);
    }

    public function render()
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        $totalGroups = TrainingGroup::where('team_id', $team->id)->count();
        $totalTrainings = Training::where('team_id', $team->id)->count();
        $activeTrainings = Training::where('team_id', $team->id)->where('is_active', true)->count();
        $upcomingSessions = TrainingSession::whereHas('training', function ($q) use ($team) {
            $q->where('team_id', $team->id);
        })->where('starts_at', '>=', now())->count();

        return view('training::livewire.dashboard', [
            'totalGroups' => $totalGroups,
            'totalTrainings' => $totalTrainings,
            'activeTrainings' => $activeTrainings,
            'upcomingSessions' => $upcomingSessions,
        ])->layout('platform::layouts.app');
    }
}
