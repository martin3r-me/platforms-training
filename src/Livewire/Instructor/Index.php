<?php

namespace Platform\Training\Livewire\Instructor;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Platform\Training\Models\Instructor;

class Index extends Component
{
    public $modalShow = false;
    public $editMode = false;
    public $editId = null;

    public $name = '';
    public $email = '';
    public $phone = '';
    public $description = '';
    public $is_active = true;

    public $search = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ];
    }

    public function render()
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        $instructors = Instructor::with(['trainings', 'sessions'])
            ->where('team_id', $team->id)
            ->when(trim($this->search) !== '', function ($q) {
                $q->where(function ($q2) {
                    $q2->where('name', 'like', '%' . trim($this->search) . '%')
                       ->orWhere('email', 'like', '%' . trim($this->search) . '%');
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->get();

        return view('training::livewire.instructor.index', [
            'instructors' => $instructors,
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

        $instructor = Instructor::where('team_id', $team->id)->findOrFail($id);

        $this->editMode = true;
        $this->editId = $instructor->id;
        $this->name = $instructor->name;
        $this->email = $instructor->email ?? '';
        $this->phone = $instructor->phone ?? '';
        $this->description = $instructor->description ?? '';
        $this->is_active = $instructor->is_active;
        $this->modalShow = true;
    }

    public function save()
    {
        $this->validate();

        $user = Auth::user();
        $team = $user->currentTeam;

        if ($this->editMode && $this->editId) {
            $instructor = Instructor::where('team_id', $team->id)->findOrFail($this->editId);

            $instructor->update([
                'name' => $this->name,
                'email' => $this->email ?: null,
                'phone' => $this->phone ?: null,
                'description' => $this->description ?: null,
                'is_active' => $this->is_active,
            ]);

            $this->dispatch('notifications:store', [
                'title' => 'Referent aktualisiert',
                'message' => "Der Referent \"{$this->name}\" wurde aktualisiert.",
                'notice_type' => 'success',
                'noticable_type' => Instructor::class,
                'noticable_id' => $instructor->id,
            ]);
        } else {
            $instructor = Instructor::create([
                'name' => $this->name,
                'email' => $this->email ?: null,
                'phone' => $this->phone ?: null,
                'description' => $this->description ?: null,
                'is_active' => $this->is_active,
                'team_id' => $team->id,
                'created_by_user_id' => $user->id,
                'owned_by_user_id' => $user->id,
            ]);

            $this->dispatch('notifications:store', [
                'title' => 'Referent erstellt',
                'message' => "Der Referent \"{$this->name}\" wurde erstellt.",
                'notice_type' => 'success',
                'noticable_type' => Instructor::class,
                'noticable_id' => $instructor->id,
            ]);
        }

        $this->resetForm();
        $this->modalShow = false;
    }

    public function delete($id)
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        $instructor = Instructor::where('team_id', $team->id)->findOrFail($id);
        $name = $instructor->name;
        $instructor->delete();

        $this->dispatch('notifications:store', [
            'title' => 'Referent gelöscht',
            'message' => "Der Referent \"{$name}\" wurde gelöscht.",
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
        $this->reset(['name', 'email', 'phone', 'description', 'is_active', 'editMode', 'editId']);
        $this->is_active = true;
        $this->resetValidation();
    }
}
