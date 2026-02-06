<?php

namespace Platform\Training\Livewire\Training;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Platform\Training\Models\Training;
use Platform\Training\Models\TrainingGroup;
use Platform\Training\Models\TrainingSession;
use Platform\Training\Models\Instructor;

class Index extends Component
{
    public $modalShow = false;
    public $editMode = false;
    public $editId = null;

    public $name = '';
    public $description = '';
    public $codePreview = '';
    public $group_id = null;
    public $is_active = true;
    public $prerequisite_ids = [];
    public $instructor_ids = [];

    public $search = '';
    public $groupFilter = null;
    public $sortField = 'name';
    public $sortDirection = 'asc';

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'group_id' => 'nullable|exists:training_groups,id',
            'is_active' => 'boolean',
            'prerequisite_ids' => 'array',
            'prerequisite_ids.*' => 'exists:trainings,id',
            'instructor_ids' => 'array',
            'instructor_ids.*' => 'exists:training_instructors,id',
        ];
    }

    public function updatedGroupId($value)
    {
        if (!$this->editMode) {
            $user = Auth::user();
            $team = $user->currentTeam;
            $this->codePreview = Training::generateCode($value ?: null, $team->id);
        }
    }

    public function render()
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        $trainings = Training::with(['group', 'prerequisites', 'sessions', 'instructors'])
            ->where('team_id', $team->id)
            ->when(trim($this->search) !== '', function ($q) {
                $q->where(function ($q2) {
                    $q2->where('name', 'like', '%' . trim($this->search) . '%')
                       ->orWhere('code', 'like', '%' . trim($this->search) . '%');
                });
            })
            ->when(!empty($this->groupFilter), function ($q) {
                $q->where('group_id', $this->groupFilter);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->get();

        $groups = TrainingGroup::where('team_id', $team->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $allTrainings = Training::where('team_id', $team->id)
            ->orderBy('name')
            ->get();

        $allInstructors = Instructor::where('team_id', $team->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('training::livewire.training.index', [
            'trainings' => $trainings,
            'groups' => $groups,
            'allTrainings' => $allTrainings,
            'allInstructors' => $allInstructors,
        ])->layout('platform::layouts.app');
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->editMode = false;
        $this->editId = null;

        $user = Auth::user();
        $team = $user->currentTeam;
        $this->codePreview = Training::generateCode(null, $team->id);

        $this->modalShow = true;
    }

    public function openEditModal($id)
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        $training = Training::with(['prerequisites', 'instructors'])->where('team_id', $team->id)->findOrFail($id);

        $this->editMode = true;
        $this->editId = $training->id;
        $this->name = $training->name;
        $this->description = $training->description ?? '';
        $this->codePreview = $training->code ?? '';
        $this->group_id = $training->group_id;
        $this->is_active = $training->is_active;
        $this->prerequisite_ids = $training->prerequisites->pluck('id')->toArray();
        $this->instructor_ids = $training->instructors->pluck('id')->toArray();
        $this->modalShow = true;
    }

    public function save()
    {
        $this->validate();

        $user = Auth::user();
        $team = $user->currentTeam;

        if ($this->editMode && $this->editId) {
            $training = Training::where('team_id', $team->id)->findOrFail($this->editId);

            $newGroupId = $this->group_id ?: null;
            $oldGroupId = $training->group_id;

            $training->update([
                'name' => $this->name,
                'description' => $this->description ?: null,
                'group_id' => $newGroupId,
                'is_active' => $this->is_active,
            ]);

            if ($newGroupId !== $oldGroupId) {
                $training->update([
                    'code' => Training::generateCode($newGroupId, $team->id),
                ]);
            }

            $filteredPrerequisites = array_filter($this->prerequisite_ids, fn($id) => $id != $training->id);
            $training->prerequisites()->sync($filteredPrerequisites);
            $training->instructors()->sync($this->instructor_ids);

            $this->dispatch('notifications:store', [
                'title' => 'Schulung aktualisiert',
                'message' => "Die Schulung \"{$this->name}\" wurde aktualisiert.",
                'notice_type' => 'success',
                'noticable_type' => Training::class,
                'noticable_id' => $training->id,
            ]);
        } else {
            $training = Training::create([
                'name' => $this->name,
                'description' => $this->description ?: null,
                'group_id' => $this->group_id ?: null,
                'is_active' => $this->is_active,
                'team_id' => $team->id,
                'created_by_user_id' => $user->id,
                'owned_by_user_id' => $user->id,
            ]);

            if (!empty($this->prerequisite_ids)) {
                $filteredPrerequisites = array_filter($this->prerequisite_ids, fn($id) => $id != $training->id);
                $training->prerequisites()->sync($filteredPrerequisites);
            }

            if (!empty($this->instructor_ids)) {
                $training->instructors()->sync($this->instructor_ids);
            }

            $this->dispatch('notifications:store', [
                'title' => 'Schulung erstellt',
                'message' => "Die Schulung \"{$this->name}\" wurde erstellt.",
                'notice_type' => 'success',
                'noticable_type' => Training::class,
                'noticable_id' => $training->id,
            ]);
        }

        $this->resetForm();
        $this->modalShow = false;
    }

    public function delete($id)
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        $training = Training::where('team_id', $team->id)->findOrFail($id);
        $name = $training->name;
        $training->delete();

        $this->dispatch('notifications:store', [
            'title' => 'Schulung gelöscht',
            'message' => "Die Schulung \"{$name}\" wurde gelöscht.",
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
        $this->reset(['name', 'description', 'codePreview', 'group_id', 'is_active', 'prerequisite_ids', 'instructor_ids', 'editMode', 'editId']);
        $this->is_active = true;
        $this->prerequisite_ids = [];
        $this->instructor_ids = [];
        $this->resetValidation();
    }
}
