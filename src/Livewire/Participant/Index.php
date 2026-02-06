<?php

namespace Platform\Training\Livewire\Participant;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Platform\Training\Models\Participant;
use Platform\Crm\Models\CrmContact;
use Platform\Hcm\Models\HcmEmployee;

class Index extends Component
{
    public $modalShow = false;
    public $editMode = false;
    public $editId = null;

    public $sourceType = 'crm'; // 'crm' or 'hcm'
    public $selectedContactId = null;
    public $selectedEmployeeId = null;
    public $notes = '';
    public $is_active = true;

    public $search = '';
    public $sourceSearch = '';
    public $sortField = 'id';
    public $sortDirection = 'desc';

    protected function rules(): array
    {
        $rules = [
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ];

        if (!$this->editMode) {
            if ($this->sourceType === 'crm') {
                $rules['selectedContactId'] = 'required|exists:crm_contacts,id';
            } else {
                $rules['selectedEmployeeId'] = 'required|exists:hcm_employees,id';
            }
        }

        return $rules;
    }

    protected $messages = [
        'selectedContactId.required' => 'Bitte wählen Sie einen CRM-Kontakt aus.',
        'selectedEmployeeId.required' => 'Bitte wählen Sie einen Mitarbeiter aus.',
    ];

    public function updatedSourceType()
    {
        $this->selectedContactId = null;
        $this->selectedEmployeeId = null;
        $this->sourceSearch = '';
    }

    public function render()
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        $participants = Participant::with(['crmContactLinks.contact', 'hcmEmployee.crmContactLinks.contact'])
            ->withCount('enrollments')
            ->where('team_id', $team->id)
            ->when(trim($this->search) !== '', function ($q) {
                $searchTerm = '%' . trim($this->search) . '%';
                $q->where(function ($q2) use ($searchTerm) {
                    $q2->whereHas('crmContactLinks.contact', function ($cq) use ($searchTerm) {
                        $cq->where('first_name', 'like', $searchTerm)
                           ->orWhere('last_name', 'like', $searchTerm);
                    })
                    ->orWhere('notes', 'like', $searchTerm);
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->get();

        // Already linked contact IDs (to exclude from selection)
        $linkedContactIds = $participants->map(fn ($p) => $p->getContact()?->id)->filter()->toArray();
        $linkedEmployeeIds = $participants->pluck('hcm_employee_id')->filter()->toArray();

        // Available CRM contacts for selection
        $availableContacts = collect();
        $availableEmployees = collect();

        if ($this->modalShow && !$this->editMode) {
            if ($this->sourceType === 'crm') {
                $availableContacts = CrmContact::where('team_id', $team->id)
                    ->where('is_active', true)
                    ->whereNotIn('id', $linkedContactIds)
                    ->when(trim($this->sourceSearch) !== '', function ($q) {
                        $s = '%' . trim($this->sourceSearch) . '%';
                        $q->where(function ($q2) use ($s) {
                            $q2->where('first_name', 'like', $s)
                               ->orWhere('last_name', 'like', $s);
                        });
                    })
                    ->orderBy('last_name')
                    ->orderBy('first_name')
                    ->limit(50)
                    ->get();
            } else {
                $availableEmployees = HcmEmployee::with('crmContactLinks.contact')
                    ->where('team_id', $team->id)
                    ->where('is_active', true)
                    ->whereNotIn('id', $linkedEmployeeIds)
                    ->when(trim($this->sourceSearch) !== '', function ($q) {
                        $s = '%' . trim($this->sourceSearch) . '%';
                        $q->where(function ($q2) use ($s) {
                            $q2->where('employee_number', 'like', $s)
                               ->orWhereHas('crmContactLinks.contact', function ($cq) use ($s) {
                                   $cq->where('first_name', 'like', $s)
                                      ->orWhere('last_name', 'like', $s);
                               });
                        });
                    })
                    ->limit(50)
                    ->get();
            }
        }

        return view('training::livewire.participant.index', [
            'participants' => $participants,
            'availableContacts' => $availableContacts,
            'availableEmployees' => $availableEmployees,
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

        $participant = Participant::where('team_id', $team->id)->findOrFail($id);

        $this->editMode = true;
        $this->editId = $participant->id;
        $this->notes = $participant->notes ?? '';
        $this->is_active = $participant->is_active;
        $this->modalShow = true;
    }

    public function save()
    {
        $this->validate();

        $user = Auth::user();
        $team = $user->currentTeam;

        if ($this->editMode && $this->editId) {
            $participant = Participant::where('team_id', $team->id)->findOrFail($this->editId);

            $participant->update([
                'notes' => $this->notes ?: null,
                'is_active' => $this->is_active,
            ]);

            $this->dispatch('notifications:store', [
                'title' => 'Teilnehmer aktualisiert',
                'message' => 'Der Teilnehmer wurde aktualisiert.',
                'notice_type' => 'success',
                'noticable_type' => Participant::class,
                'noticable_id' => $participant->id,
            ]);
        } else {
            $contactId = null;
            $employeeId = null;

            if ($this->sourceType === 'hcm') {
                $employee = HcmEmployee::with('crmContactLinks.contact')->findOrFail($this->selectedEmployeeId);
                $contact = $employee->getContact();
                if (!$contact) {
                    $this->addError('selectedEmployeeId', 'Dieser Mitarbeiter hat keinen verknüpften CRM-Kontakt.');
                    return;
                }
                $contactId = $contact->id;
                $employeeId = $employee->id;
            } else {
                $contactId = $this->selectedContactId;
            }

            $participant = Participant::create([
                'hcm_employee_id' => $employeeId,
                'notes' => $this->notes ?: null,
                'is_active' => $this->is_active,
                'team_id' => $team->id,
                'created_by_user_id' => $user->id,
                'owned_by_user_id' => $user->id,
            ]);

            // Link CRM contact via CrmContactLink
            $crmContact = CrmContact::findOrFail($contactId);
            $participant->linkContact($crmContact);

            $this->dispatch('notifications:store', [
                'title' => 'Teilnehmer hinzugefügt',
                'message' => "\"{$crmContact->full_name}\" wurde als Teilnehmer hinzugefügt.",
                'notice_type' => 'success',
                'noticable_type' => Participant::class,
                'noticable_id' => $participant->id,
            ]);
        }

        $this->resetForm();
        $this->modalShow = false;
    }

    public function delete($id)
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        $participant = Participant::with('crmContactLinks.contact')
            ->where('team_id', $team->id)
            ->findOrFail($id);
        $name = $participant->full_name ?? 'Teilnehmer';
        $participant->unlinkContact();
        $participant->delete();

        $this->dispatch('notifications:store', [
            'title' => 'Teilnehmer entfernt',
            'message' => "\"{$name}\" wurde entfernt.",
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
            'sourceType', 'selectedContactId', 'selectedEmployeeId',
            'notes', 'is_active', 'editMode', 'editId', 'sourceSearch',
        ]);
        $this->sourceType = 'crm';
        $this->is_active = true;
        $this->resetValidation();
    }
}
