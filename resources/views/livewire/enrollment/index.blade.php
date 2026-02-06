<x-ui-page>
    <x-slot name="navbar">
        <x-ui-page-navbar title="Teilnehmerverwaltung" icon="heroicon-o-clipboard-document-list" />
    </x-slot>

    <x-slot name="sidebar">
        <x-ui-page-sidebar title="Termin-Info" width="w-80" :defaultOpen="true" side="left">
            <div class="p-6 space-y-6">
                @if($activeSession)
                    <div>
                        <h3 class="text-sm font-bold text-[var(--ui-secondary)] uppercase tracking-wider mb-3">Schulung</h3>
                        <div class="text-sm font-medium text-[var(--ui-secondary)]">{{ $activeSession->training->name }}</div>
                        @if($activeSession->training->code)
                            <code class="text-xs px-1 bg-[var(--ui-muted-5)] rounded">{{ $activeSession->training->code }}</code>
                        @endif
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-[var(--ui-secondary)] uppercase tracking-wider mb-3">Termin</h3>
                        <div class="text-sm space-y-1">
                            <div class="flex items-center gap-2">
                                @svg('heroicon-o-calendar-days', 'w-4 h-4 text-[var(--ui-muted)]')
                                <span>{{ $activeSession->starts_at->format('d.m.Y H:i') }}</span>
                                @if($activeSession->ends_at)
                                    <span>– {{ $activeSession->ends_at->format('H:i') }}</span>
                                @endif
                            </div>
                            @if($activeSession->location)
                                <div class="flex items-center gap-2">
                                    @svg('heroicon-o-map-pin', 'w-4 h-4 text-[var(--ui-muted)]')
                                    <span>{{ $activeSession->location }}</span>
                                </div>
                            @endif
                            @if($activeSession->title)
                                <div class="text-xs text-[var(--ui-muted)] mt-1">{{ $activeSession->title }}</div>
                            @endif
                        </div>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-[var(--ui-secondary)] uppercase tracking-wider mb-3">Auslastung</h3>
                        <div class="text-sm">
                            <span class="font-bold text-lg {{ $isFull ? 'text-red-500' : 'text-[var(--ui-secondary)]' }}">{{ $enrollmentCount }}</span>
                            @if($activeSession->max_participants)
                                <span class="text-[var(--ui-muted)]">/ {{ $activeSession->max_participants }}</span>
                            @endif
                            <span class="text-[var(--ui-muted)] text-xs ml-1">Teilnehmer</span>
                        </div>
                        @if($activeSession->min_participants && $enrollmentCount < $activeSession->min_participants)
                            <div class="mt-1 text-xs text-amber-500">
                                Minimum {{ $activeSession->min_participants }} – noch {{ $activeSession->min_participants - $enrollmentCount }} benötigt
                            </div>
                        @endif
                        @if($isFull)
                            <div class="mt-1 text-xs text-red-500 font-medium">Termin ist voll</div>
                        @endif
                    </div>
                @endif
            </div>
        </x-ui-page-sidebar>
    </x-slot>

    <x-ui-page-container>
        <div class="space-y-6">
            @if($activeSession)
                <div class="flex items-center justify-between">
                    <div>
                        <a href="{{ route('training.sessions.index', ['training' => $activeSession->training_id]) }}" wire:navigate class="inline-flex items-center gap-1 text-sm text-[var(--ui-primary)] hover:underline mb-1">
                            @svg('heroicon-o-arrow-left', 'w-3.5 h-3.5')
                            Zurück zu Terminen
                        </a>
                        <h1 class="text-2xl font-bold text-[var(--ui-secondary)]">Teilnehmer: {{ $activeSession->training->name }}</h1>
                        <p class="text-[var(--ui-muted)] mt-1">
                            {{ $activeSession->starts_at->format('d.m.Y H:i') }}
                            @if($activeSession->location) – {{ $activeSession->location }} @endif
                        </p>
                    </div>
                    @if(!$isFull)
                        <x-ui-button variant="success" size="sm" wire:click="openEnrollModal">
                            <span class="inline-flex items-center gap-2">
                                @svg('heroicon-o-user-plus', 'w-4 h-4')
                                <span>Teilnehmer hinzufügen</span>
                            </span>
                        </x-ui-button>
                    @endif
                </div>

                @if($enrollments->count() === 0)
                    <div class="rounded-lg border border-[var(--ui-border)] bg-[var(--ui-surface)] p-6 text-sm text-[var(--ui-muted)] text-center">
                        Noch keine Teilnehmer angemeldet.
                    </div>
                @else
                    <x-ui-table compact="true">
                        <x-ui-table-header>
                            <x-ui-table-header-cell compact="true">Teilnehmer</x-ui-table-header-cell>
                            <x-ui-table-header-cell compact="true">Kontakt</x-ui-table-header-cell>
                            <x-ui-table-header-cell compact="true">Unternehmen</x-ui-table-header-cell>
                            <x-ui-table-header-cell compact="true">Status</x-ui-table-header-cell>
                            <x-ui-table-header-cell compact="true">Angemeldet am</x-ui-table-header-cell>
                            <x-ui-table-header-cell compact="true" class="text-right">Aktionen</x-ui-table-header-cell>
                        </x-ui-table-header>

                        <x-ui-table-body>
                            @foreach($enrollments as $enrollment)
                                <x-ui-table-row compact="true">
                                    <x-ui-table-cell compact="true">
                                        <div class="font-medium text-[var(--ui-secondary)]">{{ $enrollment->participant->full_name ?? '–' }}</div>
                                        @if($enrollment->notes)
                                            <div class="text-xs text-[var(--ui-muted)] mt-0.5">{{ $enrollment->notes }}</div>
                                        @endif
                                    </x-ui-table-cell>
                                    <x-ui-table-cell compact="true">
                                        <div class="text-sm space-y-0.5">
                                            @if($enrollment->participant->email)
                                                <div class="text-[var(--ui-muted)]">{{ $enrollment->participant->email }}</div>
                                            @endif
                                            @if($enrollment->participant->phone)
                                                <div class="text-[var(--ui-muted)]">{{ $enrollment->participant->phone }}</div>
                                            @endif
                                            @if(!$enrollment->participant->email && !$enrollment->participant->phone)
                                                <span class="text-[var(--ui-muted)] text-xs">–</span>
                                            @endif
                                        </div>
                                    </x-ui-table-cell>
                                    <x-ui-table-cell compact="true">
                                        @if($enrollment->participant->company_name)
                                            <span class="text-sm">{{ $enrollment->participant->company_name }}</span>
                                        @else
                                            <span class="text-[var(--ui-muted)] text-xs">–</span>
                                        @endif
                                    </x-ui-table-cell>
                                    <x-ui-table-cell compact="true">
                                        <x-ui-input-select
                                            name="status_{{ $enrollment->id }}"
                                            wire:change="updateStatus({{ $enrollment->id }}, $event.target.value)"
                                            size="sm"
                                        >
                                            <option value="registered" @selected($enrollment->status === 'registered')>Angemeldet</option>
                                            <option value="confirmed" @selected($enrollment->status === 'confirmed')>Bestätigt</option>
                                            <option value="attended" @selected($enrollment->status === 'attended')>Teilgenommen</option>
                                            <option value="cancelled" @selected($enrollment->status === 'cancelled')>Storniert</option>
                                            <option value="no_show" @selected($enrollment->status === 'no_show')>Nicht erschienen</option>
                                        </x-ui-input-select>
                                    </x-ui-table-cell>
                                    <x-ui-table-cell compact="true">
                                        <span class="text-sm text-[var(--ui-muted)]">
                                            {{ $enrollment->enrolled_at?->format('d.m.Y H:i') ?? '–' }}
                                        </span>
                                    </x-ui-table-cell>
                                    <x-ui-table-cell compact="true" class="text-right">
                                        <x-ui-button variant="danger-outline" size="xs" wire:click="removeEnrollment({{ $enrollment->id }})" wire:confirm="Anmeldung von &quot;{{ $enrollment->participant->full_name }}&quot; wirklich entfernen?">
                                            @svg('heroicon-o-user-minus', 'w-4 h-4')
                                        </x-ui-button>
                                    </x-ui-table-cell>
                                </x-ui-table-row>
                            @endforeach
                        </x-ui-table-body>
                    </x-ui-table>
                @endif
            @else
                <div class="rounded-lg border border-[var(--ui-border)] bg-[var(--ui-surface)] p-6 text-sm text-[var(--ui-muted)] text-center">
                    Kein Termin ausgewählt. Bitte wählen Sie einen Termin aus der Terminübersicht.
                </div>
            @endif
        </div>
    </x-ui-page-container>

    <x-slot name="activity">
        <x-ui-page-sidebar title="Aktivitäten" width="w-80" :defaultOpen="false" storeKey="activityOpen" side="right">
            <div class="p-4 space-y-4">
                <div class="text-sm text-[var(--ui-muted)]">Letzte Aktivitäten</div>
            </div>
        </x-ui-page-sidebar>
    </x-slot>

    {{-- Enroll Participant Modal --}}
    <x-ui-modal wire:model="enrollModalShow" size="lg">
            <x-slot name="header">
                Teilnehmer hinzufügen
            </x-slot>

            <div class="space-y-4">
                <x-ui-input-text
                    name="participantSearch"
                    placeholder="Teilnehmer suchen..."
                    wire:model.live.debounce.300ms="search"
                    size="sm"
                />

                @if($availableParticipants->count() > 0)
                    <div class="max-h-64 overflow-y-auto space-y-1 border border-[var(--ui-border)] rounded-md p-2">
                        @foreach($availableParticipants as $p)
                            <label class="flex items-center gap-3 p-2 rounded hover:bg-[var(--ui-muted-5)] cursor-pointer {{ $selectedParticipantId == $p->id ? 'bg-[var(--ui-muted-5)] ring-1 ring-[var(--ui-primary)]' : '' }}">
                                <input type="radio" value="{{ $p->id }}" wire:model="selectedParticipantId"
                                    class="text-[var(--ui-primary)] focus:ring-[var(--ui-primary)]">
                                <div>
                                    <div class="text-sm font-medium text-[var(--ui-secondary)]">{{ $p->full_name ?? '–' }}</div>
                                    <div class="text-xs text-[var(--ui-muted)]">
                                        {{ $p->email ?? '' }}
                                        @if($p->company_name)
                                            {{ $p->email ? '·' : '' }} {{ $p->company_name }}
                                        @endif
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                @else
                    <div class="text-sm text-[var(--ui-muted)] text-center py-4 border border-[var(--ui-border)] rounded-md">
                        @if(trim($search) !== '')
                            Keine passenden Teilnehmer gefunden.
                        @else
                            Alle aktiven Teilnehmer sind bereits angemeldet.
                        @endif
                        <div class="mt-2">
                            <a href="{{ route('training.participants.index') }}" wire:navigate class="text-[var(--ui-primary)] hover:underline text-xs">
                                Teilnehmer verwalten
                            </a>
                        </div>
                    </div>
                @endif

                @error('selectedParticipantId')
                    <span class="text-xs text-red-500">{{ $message }}</span>
                @enderror

                <x-ui-input-textarea
                    name="enrollmentNotes"
                    label="Notizen (optional)"
                    wire:model="enrollmentNotes"
                    placeholder="Anmerkungen zur Buchung..."
                    rows="2"
                />
            </div>

            <x-slot name="footer">
                <div class="flex justify-end gap-2">
                    <x-ui-button type="button" variant="secondary-outline" wire:click="$set('enrollModalShow', false)">
                        Abbrechen
                    </x-ui-button>
                    <x-ui-button type="button" variant="primary" wire:click="enroll">
                        Anmelden
                    </x-ui-button>
                </div>
            </x-slot>
    </x-ui-modal>
</x-ui-page>
