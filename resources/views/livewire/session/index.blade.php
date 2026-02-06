<x-ui-page>
    <x-slot name="navbar">
        <x-ui-page-navbar title="Schulungstermine" icon="heroicon-o-calendar-days" />
    </x-slot>

    <x-slot name="sidebar">
        <x-ui-page-sidebar title="Schnellzugriff" width="w-80" :defaultOpen="true" side="left">
            <div class="p-6 space-y-6">
                @if($activeTraining)
                    <div>
                        <h3 class="text-sm font-bold text-[var(--ui-secondary)] uppercase tracking-wider mb-3">Aktionen</h3>
                        <div class="space-y-2">
                            <x-ui-button variant="success" size="sm" wire:click="openCreateModal" class="w-full justify-start">
                                @svg('heroicon-o-plus', 'w-4 h-4')
                                <span class="ml-2">Neuer Termin</span>
                            </x-ui-button>
                        </div>
                    </div>
                @endif
                <div>
                    <h3 class="text-sm font-bold text-[var(--ui-secondary)] uppercase tracking-wider mb-3">Suche</h3>
                    <x-ui-input-text
                        name="search"
                        placeholder="Termin suchen..."
                        class="w-full"
                        size="sm"
                        wire:model.live.debounce.300ms="search"
                    />
                </div>
                <div>
                    <h3 class="text-sm font-bold text-[var(--ui-secondary)] uppercase tracking-wider mb-3">Filter</h3>
                    <div class="space-y-3">
                        <x-ui-input-select
                            name="trainingFilter"
                            label="Schulung"
                            wire:model.live="trainingFilter"
                            :nullable="true"
                            nullLabel="– Alle Schulungen –"
                            size="sm"
                        >
                            @foreach($trainings as $t)
                                <option value="{{ $t->id }}">{{ $t->code ? $t->code . ' – ' : '' }}{{ $t->name }}</option>
                            @endforeach
                        </x-ui-input-select>

                        <x-ui-input-select
                            name="statusFilter"
                            label="Status"
                            wire:model.live="statusFilter"
                            :nullable="true"
                            nullLabel="– Alle Status –"
                            size="sm"
                        >
                            <option value="planned">Geplant</option>
                            <option value="confirmed">Bestätigt</option>
                            <option value="cancelled">Abgesagt</option>
                            <option value="completed">Abgeschlossen</option>
                        </x-ui-input-select>
                    </div>
                </div>
            </div>
        </x-ui-page-sidebar>
    </x-slot>

    <x-ui-page-container>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    @if($activeTraining)
                        <a href="{{ route('training.trainings.index') }}" wire:navigate class="inline-flex items-center gap-1 text-sm text-[var(--ui-primary)] hover:underline mb-1">
                            @svg('heroicon-o-arrow-left', 'w-3.5 h-3.5')
                            Zurück zu Schulungen
                        </a>
                        <h1 class="text-2xl font-bold text-[var(--ui-secondary)]">Termine: {{ $activeTraining->name }}</h1>
                        @if($activeTraining->code)
                            <code class="text-xs px-1.5 py-0.5 bg-[var(--ui-muted-5)] rounded">{{ $activeTraining->code }}</code>
                        @endif
                    @else
                        <h1 class="text-2xl font-bold text-[var(--ui-secondary)]">Schulungstermine</h1>
                        <p class="text-[var(--ui-muted)] mt-1">Verwalten Sie Termine für Ihre Schulungen</p>
                    @endif
                </div>
                @if($activeTraining)
                    <x-ui-button variant="success" size="sm" wire:click="openCreateModal">
                        <span class="inline-flex items-center gap-2">
                            @svg('heroicon-o-plus', 'w-4 h-4')
                            <span>Neuer Termin</span>
                        </span>
                    </x-ui-button>
                @endif
            </div>

            @if($sessions->count() === 0)
                <div class="rounded-lg border border-[var(--ui-border)] bg-[var(--ui-surface)] p-6 text-sm text-[var(--ui-muted)] text-center">
                    Keine Schulungstermine vorhanden. Erstellen Sie den ersten Termin.
                </div>
            @else
                <x-ui-table compact="true">
                    <x-ui-table-header>
                        <x-ui-table-header-cell compact="true">Schulung</x-ui-table-header-cell>
                        <x-ui-table-header-cell compact="true" sortable="true" sortField="starts_at" :currentSort="$sortField" :sortDirection="$sortDirection">Datum</x-ui-table-header-cell>
                        <x-ui-table-header-cell compact="true">Ort</x-ui-table-header-cell>
                        <x-ui-table-header-cell compact="true">Teilnehmer</x-ui-table-header-cell>
                        <x-ui-table-header-cell compact="true">Referenten</x-ui-table-header-cell>
                        <x-ui-table-header-cell compact="true" sortable="true" sortField="status" :currentSort="$sortField" :sortDirection="$sortDirection">Status</x-ui-table-header-cell>
                        <x-ui-table-header-cell compact="true" class="text-right">Aktionen</x-ui-table-header-cell>
                    </x-ui-table-header>

                    <x-ui-table-body>
                        @foreach($sessions as $session)
                            <x-ui-table-row compact="true">
                                <x-ui-table-cell compact="true">
                                    <div class="font-medium text-[var(--ui-secondary)]">{{ $session->training->name }}</div>
                                    @if($session->training->code)
                                        <code class="text-xs px-1 bg-[var(--ui-muted-5)] rounded">{{ $session->training->code }}</code>
                                    @endif
                                    @if($session->title)
                                        <div class="text-xs text-[var(--ui-muted)] mt-0.5">{{ $session->title }}</div>
                                    @endif
                                </x-ui-table-cell>
                                <x-ui-table-cell compact="true">
                                    <div class="text-sm font-medium">{{ $session->starts_at->format('d.m.Y') }}</div>
                                    <div class="text-xs text-[var(--ui-muted)]">
                                        {{ $session->starts_at->format('H:i') }}
                                        @if($session->ends_at)
                                            – {{ $session->ends_at->format('H:i') }}
                                        @endif
                                    </div>
                                </x-ui-table-cell>
                                <x-ui-table-cell compact="true">
                                    @if($session->location)
                                        <span class="text-sm">{{ $session->location }}</span>
                                    @else
                                        <span class="text-[var(--ui-muted)] text-xs">–</span>
                                    @endif
                                </x-ui-table-cell>
                                <x-ui-table-cell compact="true">
                                    <a href="{{ route('training.enrollments.index', ['session' => $session->id]) }}" wire:navigate
                                       class="inline-flex items-center gap-1 text-sm text-[var(--ui-primary)] hover:underline">
                                        <span class="font-medium {{ $session->max_participants && $session->enrollments_count >= $session->max_participants ? 'text-red-500' : '' }}">
                                            {{ $session->enrollments_count }}
                                        </span>
                                        @if($session->max_participants)
                                            <span class="text-[var(--ui-muted)]">/ {{ $session->max_participants }}</span>
                                        @endif
                                        @svg('heroicon-o-users', 'w-3.5 h-3.5')
                                    </a>
                                    @if($session->enrollments->count() > 0)
                                        <div class="mt-1 space-y-0.5">
                                            @foreach($session->enrollments->take(5) as $enrollment)
                                                <div class="text-xs text-[var(--ui-muted)] flex items-center gap-1">
                                                    @svg('heroicon-o-user', 'w-3 h-3 shrink-0')
                                                    <span class="truncate max-w-[10rem]">{{ $enrollment->participant->full_name ?? '–' }}</span>
                                                    @if($enrollment->status === 'cancelled')
                                                        <span class="text-red-400 text-[10px]">(storniert)</span>
                                                    @elseif($enrollment->status === 'confirmed')
                                                        <span class="text-green-500 text-[10px]">(bestätigt)</span>
                                                    @elseif($enrollment->status === 'attended')
                                                        <span class="text-blue-500 text-[10px]">(teilgenommen)</span>
                                                    @elseif($enrollment->status === 'no_show')
                                                        <span class="text-amber-500 text-[10px]">(nicht erschienen)</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                            @if($session->enrollments->count() > 5)
                                                <div class="text-xs text-[var(--ui-muted)] italic">
                                                    +{{ $session->enrollments->count() - 5 }} weitere
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </x-ui-table-cell>
                                <x-ui-table-cell compact="true">
                                    @if($session->instructors->count() > 0)
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($session->instructors as $inst)
                                                <x-ui-badge variant="info" size="sm">{{ $inst->name }}</x-ui-badge>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-[var(--ui-muted)] text-xs">–</span>
                                    @endif
                                </x-ui-table-cell>
                                <x-ui-table-cell compact="true">
                                    @switch($session->status)
                                        @case('planned')
                                            <x-ui-badge variant="secondary" size="sm">Geplant</x-ui-badge>
                                            @break
                                        @case('confirmed')
                                            <x-ui-badge variant="success" size="sm">Bestätigt</x-ui-badge>
                                            @break
                                        @case('cancelled')
                                            <x-ui-badge variant="danger" size="sm">Abgesagt</x-ui-badge>
                                            @break
                                        @case('completed')
                                            <x-ui-badge variant="primary" size="sm">Abgeschlossen</x-ui-badge>
                                            @break
                                    @endswitch
                                </x-ui-table-cell>
                                <x-ui-table-cell compact="true" class="text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <x-ui-button variant="primary-outline" size="xs" :href="route('training.enrollments.index', ['session' => $session->id])">
                                            @svg('heroicon-o-user-plus', 'w-4 h-4')
                                        </x-ui-button>
                                        <x-ui-button variant="secondary-outline" size="xs" wire:click="openEditModal({{ $session->id }})">
                                            @svg('heroicon-o-pencil-square', 'w-4 h-4')
                                        </x-ui-button>
                                        <x-ui-button variant="danger-outline" size="xs" wire:click="delete({{ $session->id }})" wire:confirm="Termin wirklich löschen?">
                                            @svg('heroicon-o-trash', 'w-4 h-4')
                                        </x-ui-button>
                                    </div>
                                </x-ui-table-cell>
                            </x-ui-table-row>
                        @endforeach
                    </x-ui-table-body>
                </x-ui-table>
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

    {{-- Create / Edit Modal --}}
    <x-ui-modal wire:model="modalShow" size="lg">
        <x-slot name="header">
            {{ $editMode ? 'Termin bearbeiten' : 'Neuen Termin anlegen' }}
        </x-slot>

        <div class="space-y-4">
            @if($activeTraining)
                <div>
                    <label class="block text-sm font-medium text-[var(--ui-secondary)] mb-1">Schulung</label>
                    <div class="px-3 py-2 bg-[var(--ui-muted-5)] border border-[var(--ui-border)] rounded-md text-sm">
                        <span class="font-medium">{{ $activeTraining->name }}</span>
                        @if($activeTraining->code)
                            <code class="text-xs px-1 bg-[var(--ui-surface)] rounded ml-1">{{ $activeTraining->code }}</code>
                        @endif
                    </div>
                </div>
            @endif

            <x-ui-input-text
                name="title"
                label="Titel (optional)"
                wire:model="title"
                placeholder="z.B. Auffrischungskurs März 2026"
            />

            <x-ui-form-grid :cols="2" :gap="4">
                <div>
                    <label class="block text-sm font-medium text-[var(--ui-secondary)] mb-1">Beginn *</label>
                    <input type="datetime-local" wire:model="starts_at"
                        class="w-full rounded-md border border-[var(--ui-border)] bg-[var(--ui-surface)] px-3 py-2 text-sm text-[var(--ui-secondary)] focus:ring-[var(--ui-primary)] focus:border-[var(--ui-primary)]"
                        required>
                    @error('starts_at') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-[var(--ui-secondary)] mb-1">Ende</label>
                    <input type="datetime-local" wire:model="ends_at"
                        class="w-full rounded-md border border-[var(--ui-border)] bg-[var(--ui-surface)] px-3 py-2 text-sm text-[var(--ui-secondary)] focus:ring-[var(--ui-primary)] focus:border-[var(--ui-primary)]">
                    @error('ends_at') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>
            </x-ui-form-grid>

            <x-ui-input-text
                name="location"
                label="Ort"
                wire:model="location"
                placeholder="z.B. Raum A1, Online, etc."
            />

            <x-ui-form-grid :cols="3" :gap="4">
                <x-ui-input-text
                    name="min_participants"
                    label="Min. Teilnehmer"
                    wire:model="min_participants"
                    type="number"
                    placeholder="0"
                />

                <x-ui-input-text
                    name="max_participants"
                    label="Max. Teilnehmer"
                    wire:model="max_participants"
                    type="number"
                    placeholder="0"
                />

                <x-ui-input-select
                    name="status"
                    label="Status"
                    wire:model="status"
                    required
                >
                    <option value="planned">Geplant</option>
                    <option value="confirmed">Bestätigt</option>
                    <option value="cancelled">Abgesagt</option>
                    <option value="completed">Abgeschlossen</option>
                </x-ui-input-select>
            </x-ui-form-grid>

            <x-ui-input-textarea
                name="description"
                label="Beschreibung"
                wire:model="description"
                placeholder="Optionale Beschreibung..."
                rows="2"
            />

            @if($availableInstructors->count() > 0)
                <div>
                    <label class="block text-sm font-medium text-[var(--ui-secondary)] mb-2">Referenten</label>
                    <p class="text-xs text-[var(--ui-muted)] mb-2">Welche Referenten leiten diesen Termin?</p>
                    <div class="max-h-48 overflow-y-auto space-y-1 border border-[var(--ui-border)] rounded-md p-2">
                        @foreach($availableInstructors as $inst)
                            <label class="flex items-center gap-2 p-1.5 rounded hover:bg-[var(--ui-muted-5)] cursor-pointer">
                                <input type="checkbox" value="{{ $inst->id }}" wire:model="instructor_ids"
                                    class="rounded border-[var(--ui-border)] text-[var(--ui-primary)] focus:ring-[var(--ui-primary)]">
                                <span class="text-sm text-[var(--ui-secondary)]">{{ $inst->name }}</span>
                                @if($inst->email)
                                    <span class="text-xs text-[var(--ui-muted)]">{{ $inst->email }}</span>
                                @endif
                            </label>
                        @endforeach
                    </div>
                </div>
            @elseif($activeTraining)
                <div class="text-xs text-[var(--ui-muted)] italic">
                    Dieser Schulung sind noch keine Referenten zugeordnet.
                    <a href="{{ route('training.trainings.index') }}" wire:navigate class="text-[var(--ui-primary)] hover:underline">Referenten zuweisen</a>
                </div>
            @endif
        </div>

        <x-slot name="footer">
            <div class="flex justify-end gap-2">
                <x-ui-button type="button" variant="secondary-outline" wire:click="closeModal">
                    Abbrechen
                </x-ui-button>
                <x-ui-button type="button" variant="primary" wire:click="save">
                    {{ $editMode ? 'Speichern' : 'Anlegen' }}
                </x-ui-button>
            </div>
        </x-slot>
    </x-ui-modal>
</x-ui-page>
