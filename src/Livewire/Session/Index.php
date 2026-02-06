<?php

namespace Platform\Training\Livewire\Session;

use Livewire\Component;
use Livewire\Attributes\Url;
use Illuminate\Support\Facades\Auth;
use Platform\Training\Models\Training;
use Platform\Training\Models\TrainingSession;
use Platform\Training\Models\Instructor;

class Index extends Component
{
    public $modalShow = false;
    public $editMode = false;
    public $editId = null;

    public $training_id = null;
    public $title = '';
    public $description = '';
    public $starts_at = '';
    public $ends_at = '';
    public $location = '';
    public $min_participants = null;
    public $max_participants = null;
    public $status = 'planned';
    public $instructor_ids = [];

    public $search = '';
    #[Url(as: 'training')]
    public $trainingFilter = null;
    public $statusFilter = null;
    public $sortField = 'starts_at';
    public $sortDirection = 'asc';

    protected function rules(): array
    {
        return [
            'training_id' => 'required|exists:trainings,id',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'starts_at' => 'required|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'location' => 'nullable|string|max:255',
            'min_participants' => 'nullable|integer|min:0',
            'max_participants' => 'nullable|integer|min:1',
            'status' => 'required|in:planned,confirmed,cancelled,completed',
            'instructor_ids' => 'array',
            'instructor_ids.*' => 'exists:training_instructors,id',
        ];
    }

    public function render()
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        $sessions = TrainingSession::with(['training.group', 'training.instructors', 'instructors', 'enrollments.participant.crmContactLinks.contact'])
            ->withCount('enrollments')
            ->whereHas('training', function ($q) use ($team) {
                $q->where('team_id', $team->id);
            })
            ->when(trim($this->search) !== '', function ($q) {
                $q->where(function ($q2) {
                    $q2->where('title', 'like', '%' . trim($this->search) . '%')
                       ->orWhere('location', 'like', '%' . trim($this->search) . '%');
                });
            })
            ->when(!empty($this->trainingFilter), function ($q) {
                $q->where('training_id', $this->trainingFilter);
            })
            ->when(!empty($this->statusFilter), function ($q) {
                $q->where('status', $this->statusFilter);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->get();

        $trainings = Training::where('team_id', $team->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $activeTraining = $this->trainingFilter
            ? Training::with('instructors')->find($this->trainingFilter)
            : null;

        $availableInstructors = $activeTraining
            ? $activeTraining->instructors()->where('is_active', true)->orderBy('name')->get()
            : collect();

        return view('training::livewire.session.index', [
            'sessions' => $sessions,
            'trainings' => $trainings,
            'activeTraining' => $activeTraining,
            'availableInstructors' => $availableInstructors,
        ])->layout('platform::layouts.app');
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->editMode = false;
        $this->editId = null;

        if ($this->trainingFilter) {
            $this->training_id = $this->trainingFilter;
        }

        $this->modalShow = true;
    }

    public function openEditModal($id)
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        $session = TrainingSession::with('instructors')->whereHas('training', function ($q) use ($team) {
            $q->where('team_id', $team->id);
        })->findOrFail($id);

        $this->editMode = true;
        $this->editId = $session->id;
        $this->training_id = $session->training_id;
        $this->title = $session->title ?? '';
        $this->description = $session->description ?? '';
        $this->starts_at = $session->starts_at ? $session->starts_at->format('Y-m-d\TH:i') : '';
        $this->ends_at = $session->ends_at ? $session->ends_at->format('Y-m-d\TH:i') : '';
        $this->location = $session->location ?? '';
        $this->min_participants = $session->min_participants;
        $this->max_participants = $session->max_participants;
        $this->status = $session->status;
        $this->instructor_ids = $session->instructors->pluck('id')->toArray();
        $this->modalShow = true;
    }

    public function save()
    {
        $this->validate();

        $user = Auth::user();
        $team = $user->currentTeam;

        // Only allow instructors that are assigned to the training
        $training = Training::with('instructors')->findOrFail($this->training_id);
        $allowedInstructorIds = $training->instructors->pluck('id')->toArray();
        $this->instructor_ids = array_values(array_intersect($this->instructor_ids, $allowedInstructorIds));

        if ($this->editMode && $this->editId) {
            $session = TrainingSession::whereHas('training', function ($q) use ($team) {
                $q->where('team_id', $team->id);
            })->findOrFail($this->editId);

            $session->update([
                'training_id' => $this->training_id,
                'title' => $this->title ?: null,
                'description' => $this->description ?: null,
                'starts_at' => $this->starts_at,
                'ends_at' => $this->ends_at ?: null,
                'location' => $this->location ?: null,
                'min_participants' => $this->min_participants ?: null,
                'max_participants' => $this->max_participants ?: null,
                'status' => $this->status,
            ]);

            $session->instructors()->sync($this->instructor_ids);

            $this->dispatch('notifications:store', [
                'title' => 'Termin aktualisiert',
                'message' => 'Der Schulungstermin wurde aktualisiert.',
                'notice_type' => 'success',
                'noticable_type' => TrainingSession::class,
                'noticable_id' => $session->id,
            ]);
        } else {
            $session = TrainingSession::create([
                'training_id' => $this->training_id,
                'title' => $this->title ?: null,
                'description' => $this->description ?: null,
                'starts_at' => $this->starts_at,
                'ends_at' => $this->ends_at ?: null,
                'location' => $this->location ?: null,
                'min_participants' => $this->min_participants ?: null,
                'max_participants' => $this->max_participants ?: null,
                'status' => $this->status,
                'is_active' => true,
                'team_id' => $team->id,
                'created_by_user_id' => $user->id,
                'owned_by_user_id' => $user->id,
            ]);

            if (!empty($this->instructor_ids)) {
                $session->instructors()->sync($this->instructor_ids);
            }

            $this->dispatch('notifications:store', [
                'title' => 'Termin erstellt',
                'message' => 'Der Schulungstermin wurde erstellt.',
                'notice_type' => 'success',
                'noticable_type' => TrainingSession::class,
                'noticable_id' => $session->id,
            ]);
        }

        $this->resetForm();
        $this->modalShow = false;
    }

    public function delete($id)
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        $session = TrainingSession::whereHas('training', function ($q) use ($team) {
            $q->where('team_id', $team->id);
        })->findOrFail($id);

        $session->delete();

        $this->dispatch('notifications:store', [
            'title' => 'Termin gelöscht',
            'message' => 'Der Schulungstermin wurde gelöscht.',
            'notice_type' => 'success',
            'noticable_type' => \stdClass::class,
            'noticable_id' => 0,
        ]);
    }

    public function closeModal()
    {
        $this->modalShow = false;
        $this->resetForm();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    private function resetForm()
    {
        $this->reset([
            'training_id', 'title', 'description', 'starts_at', 'ends_at',
            'location', 'min_participants', 'max_participants', 'status',
            'instructor_ids', 'editMode', 'editId',
        ]);
        $this->status = 'planned';
        $this->instructor_ids = [];
        $this->resetValidation();
    }
}
