<?php

namespace Platform\Training\Livewire\Group;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Platform\Training\Models\TrainingGroup;

class Index extends Component
{
    public $modalShow = false;
    public $editMode = false;
    public $editId = null;

    public $name = '';
    public $code = '';
    public $description = '';
    public $parent_id = null;
    public $is_active = true;

    public $search = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|alpha_num',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:training_groups,id',
            'is_active' => 'boolean',
        ];
    }

    public function render()
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        $groups = TrainingGroup::with(['parent', 'children', 'trainings'])
            ->where('team_id', $team->id)
            ->when(trim($this->search) !== '', function ($q) {
                $q->where(function ($q2) {
                    $q2->where('name', 'like', '%' . trim($this->search) . '%')
                       ->orWhere('code', 'like', '%' . trim($this->search) . '%');
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->get();

        $allGroups = TrainingGroup::where('team_id', $team->id)
            ->orderBy('name')
            ->get();

        return view('training::livewire.group.index', [
            'groups' => $groups,
            'allGroups' => $allGroups,
        ])->layout('platform::layouts.app');
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->editMode = false;
        $this->editId = null;
        $this->modalShow = true;
    }

    public function openEditModal($id)
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        $group = TrainingGroup::where('team_id', $team->id)->findOrFail($id);

        $this->editMode = true;
        $this->editId = $group->id;
        $this->name = $group->name;
        $this->code = $group->code ?? '';
        $this->description = $group->description ?? '';
        $this->parent_id = $group->parent_id;
        $this->is_active = $group->is_active;
        $this->modalShow = true;
    }

    public function save()
    {
        $this->validate();

        $user = Auth::user();
        $team = $user->currentTeam;

        if ($this->editMode && $this->editId) {
            $group = TrainingGroup::where('team_id', $team->id)->findOrFail($this->editId);

            if ($this->parent_id == $group->id) {
                $this->addError('parent_id', 'Eine Gruppe kann nicht ihr eigener Elternteil sein.');
                return;
            }

            $group->update([
                'name' => $this->name,
                'code' => strtoupper($this->code),
                'description' => $this->description ?: null,
                'parent_id' => $this->parent_id ?: null,
                'is_active' => $this->is_active,
            ]);

            $this->dispatch('notifications:store', [
                'title' => 'Gruppe aktualisiert',
                'message' => "Die Gruppe \"{$this->name}\" wurde aktualisiert.",
                'notice_type' => 'success',
                'noticable_type' => TrainingGroup::class,
                'noticable_id' => $group->id,
            ]);
        } else {
            $group = TrainingGroup::create([
                'name' => $this->name,
                'code' => strtoupper($this->code),
                'description' => $this->description ?: null,
                'parent_id' => $this->parent_id ?: null,
                'is_active' => $this->is_active,
                'team_id' => $team->id,
                'created_by_user_id' => $user->id,
                'owned_by_user_id' => $user->id,
            ]);

            $this->dispatch('notifications:store', [
                'title' => 'Gruppe erstellt',
                'message' => "Die Gruppe \"{$this->name}\" wurde erstellt.",
                'notice_type' => 'success',
                'noticable_type' => TrainingGroup::class,
                'noticable_id' => $group->id,
            ]);
        }

        $this->resetForm();
        $this->modalShow = false;
    }

    public function delete($id)
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        $group = TrainingGroup::where('team_id', $team->id)->findOrFail($id);
        $name = $group->name;
        $group->delete();

        $this->dispatch('notifications:store', [
            'title' => 'Gruppe gelöscht',
            'message' => "Die Gruppe \"{$name}\" wurde gelöscht.",
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
        $this->reset(['name', 'code', 'description', 'parent_id', 'is_active', 'editMode', 'editId']);
        $this->is_active = true;
        $this->resetValidation();
    }
}
