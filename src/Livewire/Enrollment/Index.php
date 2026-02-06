<?php

namespace Platform\Training\Livewire\Enrollment;

use Livewire\Component;
use Livewire\Attributes\Url;
use Illuminate\Support\Facades\Auth;
use Platform\Training\Models\Enrollment;
use Platform\Training\Models\Participant;
use Platform\Training\Models\TrainingSession;

class Index extends Component
{
    #[Url(as: 'session')]
    public $sessionFilter = null;

    public $enrollModalShow = false;
    public $selectedParticipantId = null;
    public $enrollmentNotes = '';

    public $search = '';

    protected function rules(): array
    {
        return [
            'selectedParticipantId' => 'required|exists:training_participants,id',
            'enrollmentNotes' => 'nullable|string',
        ];
    }

    public function render()
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        $activeSession = $this->sessionFilter
            ? TrainingSession::with(['training', 'enrollments.participant.crmContactLinks.contact'])->whereHas('training', function ($q) use ($team) {
                $q->where('team_id', $team->id);
            })->find($this->sessionFilter)
            : null;

        $enrollments = $activeSession
            ? $activeSession->enrollments()->with('participant.crmContactLinks.contact')->whereNull('training_enrollments.deleted_at')->get()
            : collect();

        $enrolledParticipantIds = $enrollments->pluck('participant_id')->toArray();

        $availableParticipants = $activeSession
            ? Participant::with(['crmContactLinks.contact'])
                ->where('team_id', $team->id)
                ->where('is_active', true)
                ->whereNotIn('id', $enrolledParticipantIds)
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
                ->get()
            : collect();

        $enrollmentCount = $enrollments->count();
        $maxParticipants = $activeSession?->max_participants;
        $isFull = $maxParticipants && $enrollmentCount >= $maxParticipants;

        return view('training::livewire.enrollment.index', [
            'activeSession' => $activeSession,
            'enrollments' => $enrollments,
            'availableParticipants' => $availableParticipants,
            'enrollmentCount' => $enrollmentCount,
            'isFull' => $isFull,
        ])->layout('platform::layouts.app');
    }

    public function openEnrollModal()
    {
        $this->selectedParticipantId = null;
        $this->enrollmentNotes = '';
        $this->search = '';
        $this->resetValidation();
        $this->enrollModalShow = true;
    }

    public function enroll()
    {
        $this->validate();

        $user = Auth::user();
        $team = $user->currentTeam;

        $session = TrainingSession::whereHas('training', function ($q) use ($team) {
            $q->where('team_id', $team->id);
        })->findOrFail($this->sessionFilter);

        // Check max participants
        $currentCount = $session->enrollments()->whereNull('training_enrollments.deleted_at')->count();
        if ($session->max_participants && $currentCount >= $session->max_participants) {
            $this->addError('selectedParticipantId', 'Maximale Teilnehmeranzahl erreicht.');
            return;
        }

        // Check duplicate
        $exists = Enrollment::where('training_session_id', $session->id)
            ->where('participant_id', $this->selectedParticipantId)
            ->whereNull('deleted_at')
            ->exists();

        if ($exists) {
            $this->addError('selectedParticipantId', 'Teilnehmer ist bereits angemeldet.');
            return;
        }

        $enrollment = Enrollment::create([
            'training_session_id' => $session->id,
            'participant_id' => $this->selectedParticipantId,
            'status' => 'registered',
            'notes' => $this->enrollmentNotes ?: null,
            'team_id' => $team->id,
            'created_by_user_id' => $user->id,
        ]);

        $participant = Participant::with('crmContactLinks.contact')->find($this->selectedParticipantId);

        $this->dispatch('notifications:store', [
            'title' => 'Teilnehmer angemeldet',
            'message' => "\"{$participant->full_name}\" wurde zum Termin angemeldet.",
            'notice_type' => 'success',
            'noticable_type' => Enrollment::class,
            'noticable_id' => $enrollment->id,
        ]);

        $this->enrollModalShow = false;
        $this->selectedParticipantId = null;
        $this->enrollmentNotes = '';
        $this->search = '';
    }

    public function updateStatus($enrollmentId, $status)
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        $enrollment = Enrollment::where('team_id', $team->id)->findOrFail($enrollmentId);
        $enrollment->update(['status' => $status]);

        $statusLabels = [
            'registered' => 'Angemeldet',
            'confirmed' => 'Bestätigt',
            'attended' => 'Teilgenommen',
            'cancelled' => 'Storniert',
            'no_show' => 'Nicht erschienen',
        ];

        $this->dispatch('notifications:store', [
            'title' => 'Status aktualisiert',
            'message' => "Status auf \"{$statusLabels[$status]}\" geändert.",
            'notice_type' => 'success',
            'noticable_type' => Enrollment::class,
            'noticable_id' => $enrollment->id,
        ]);
    }

    public function removeEnrollment($enrollmentId)
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        $enrollment = Enrollment::with('participant.crmContactLinks.contact')->where('team_id', $team->id)->findOrFail($enrollmentId);
        $name = $enrollment->participant->full_name ?? 'Teilnehmer';
        $enrollment->delete();

        $this->dispatch('notifications:store', [
            'title' => 'Anmeldung entfernt',
            'message' => "\"{$name}\" wurde vom Termin abgemeldet.",
            'notice_type' => 'success',
            'noticable_type' => \stdClass::class,
            'noticable_id' => 0,
        ]);
    }
}
