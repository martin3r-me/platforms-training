<x-ui-page>
    <x-slot name="navbar">
        <x-ui-page-navbar title="Teilnehmer" icon="heroicon-o-users" />
    </x-slot>

    <x-slot name="sidebar">
        <x-ui-page-sidebar title="Schnellzugriff" width="w-80" :defaultOpen="true" side="left">
            <div class="p-6 space-y-6">
                <div>
                    <h3 class="text-sm font-bold text-[var(--ui-secondary)] uppercase tracking-wider mb-3">Aktionen</h3>
                    <div class="space-y-2">
                        <x-ui-button variant="success" size="sm" wire:click="openCreateModal" class="w-full justify-start">
                            @svg('heroicon-o-plus', 'w-4 h-4')
                            <span class="ml-2">Neuer Teilnehmer</span>
                        </x-ui-button>
                    </div>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-[var(--ui-secondary)] uppercase tracking-wider mb-3">Suche</h3>
                    <x-ui-input-text
                        name="search"
                        placeholder="Teilnehmer suchen..."
                        class="w-full"
                        size="sm"
                        wire:model.live.debounce.300ms="search"
                    />
                </div>
            </div>
        </x-ui-page-sidebar>
    </x-slot>

    <x-ui-page-container>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-[var(--ui-secondary)]">Teilnehmer</h1>
                    <p class="text-[var(--ui-muted)] mt-1">Verwalten Sie Ihre Schulungsteilnehmer</p>
                </div>
                <x-ui-button variant="success" size="sm" wire:click="openCreateModal">
                    <span class="inline-flex items-center gap-2">
                        @svg('heroicon-o-plus', 'w-4 h-4')
                        <span>Neuer Teilnehmer</span>
                    </span>
                </x-ui-button>
            </div>

            @if($participants->count() === 0)
                <div class="rounded-lg border border-[var(--ui-border)] bg-[var(--ui-surface)] p-6 text-sm text-[var(--ui-muted)] text-center">
                    Keine Teilnehmer vorhanden. Erstellen Sie den ersten Teilnehmer.
                </div>
            @else
                <x-ui-table compact="true">
                    <x-ui-table-header>
                        <x-ui-table-header-cell compact="true">Name</x-ui-table-header-cell>
                        <x-ui-table-header-cell compact="true">Quelle</x-ui-table-header-cell>
                        <x-ui-table-header-cell compact="true">Kontakt</x-ui-table-header-cell>
                        <x-ui-table-header-cell compact="true">Unternehmen</x-ui-table-header-cell>
                        <x-ui-table-header-cell compact="true">Buchungen</x-ui-table-header-cell>
                        <x-ui-table-header-cell compact="true">Status</x-ui-table-header-cell>
                        <x-ui-table-header-cell compact="true" class="text-right">Aktionen</x-ui-table-header-cell>
                    </x-ui-table-header>

                    <x-ui-table-body>
                        @foreach($participants as $participant)
                            <x-ui-table-row compact="true">
                                <x-ui-table-cell compact="true">
                                    <div class="font-medium text-[var(--ui-secondary)]">{{ $participant->full_name ?? '–' }}</div>
                                    @if($participant->notes)
                                        <div class="text-xs text-[var(--ui-muted)] mt-0.5 truncate max-w-xs">{{ $participant->notes }}</div>
                                    @endif
                                </x-ui-table-cell>
                                <x-ui-table-cell compact="true">
                                    @if($participant->hcm_employee_id)
                                        <x-ui-badge variant="info" size="sm">HCM</x-ui-badge>
                                    @else
                                        <x-ui-badge variant="secondary" size="sm">CRM</x-ui-badge>
                                    @endif
                                </x-ui-table-cell>
                                <x-ui-table-cell compact="true">
                                    <div class="text-sm space-y-0.5">
                                        @if($participant->email)
                                            <div class="flex items-center gap-1 text-[var(--ui-muted)]">
                                                @svg('heroicon-o-envelope', 'w-3.5 h-3.5')
                                                <span>{{ $participant->email }}</span>
                                            </div>
                                        @endif
                                        @if($participant->phone)
                                            <div class="flex items-center gap-1 text-[var(--ui-muted)]">
                                                @svg('heroicon-o-phone', 'w-3.5 h-3.5')
                                                <span>{{ $participant->phone }}</span>
                                            </div>
                                        @endif
                                        @if(!$participant->email && !$participant->phone)
                                            <span class="text-[var(--ui-muted)] text-xs">–</span>
                                        @endif
                                    </div>
                                </x-ui-table-cell>
                                <x-ui-table-cell compact="true">
                                    @if($participant->company_name)
                                        <span class="text-sm">{{ $participant->company_name }}</span>
                                    @else
                                        <span class="text-[var(--ui-muted)] text-xs">–</span>
                                    @endif
                                </x-ui-table-cell>
                                <x-ui-table-cell compact="true">
                                    <span class="text-sm">{{ $participant->enrollments_count }}</span>
                                </x-ui-table-cell>
                                <x-ui-table-cell compact="true">
                                    @if($participant->is_active)
                                        <x-ui-badge variant="success" size="sm">Aktiv</x-ui-badge>
                                    @else
                                        <x-ui-badge variant="secondary" size="sm">Inaktiv</x-ui-badge>
                                    @endif
                                </x-ui-table-cell>
                                <x-ui-table-cell compact="true" class="text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <x-ui-button variant="secondary-outline" size="xs" wire:click="openEditModal({{ $participant->id }})">
                                            @svg('heroicon-o-pencil-square', 'w-4 h-4')
                                        </x-ui-button>
                                        <x-ui-button variant="danger-outline" size="xs" wire:click="delete({{ $participant->id }})" wire:confirm="Teilnehmer &quot;{{ $participant->full_name }}&quot; wirklich löschen?">
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
            {{ $editMode ? 'Teilnehmer bearbeiten' : 'Neuen Teilnehmer anlegen' }}
        </x-slot>

        <div class="space-y-4">
            @if(!$editMode)
                {{-- Source Type Toggle --}}
                <div>
                    <label class="block text-sm font-medium text-[var(--ui-secondary)] mb-2">Quelle</label>
                    <div class="flex gap-2">
                        <button type="button" wire:click="$set('sourceType', 'crm')"
                            class="flex-1 px-3 py-2 text-sm rounded-md border transition-colors
                                {{ $sourceType === 'crm'
                                    ? 'bg-[var(--ui-primary)] text-white border-[var(--ui-primary)]'
                                    : 'bg-[var(--ui-surface)] text-[var(--ui-secondary)] border-[var(--ui-border)] hover:bg-[var(--ui-muted-5)]' }}">
                            @svg('heroicon-o-user-circle', 'w-4 h-4 inline-block mr-1')
                            CRM Kontakt
                        </button>
                        <button type="button" wire:click="$set('sourceType', 'hcm')"
                            class="flex-1 px-3 py-2 text-sm rounded-md border transition-colors
                                {{ $sourceType === 'hcm'
                                    ? 'bg-[var(--ui-primary)] text-white border-[var(--ui-primary)]'
                                    : 'bg-[var(--ui-surface)] text-[var(--ui-secondary)] border-[var(--ui-border)] hover:bg-[var(--ui-muted-5)]' }}">
                            @svg('heroicon-o-briefcase', 'w-4 h-4 inline-block mr-1')
                            HCM Mitarbeiter
                        </button>
                    </div>
                </div>

                {{-- Search --}}
                <x-ui-input-text
                    name="sourceSearch"
                    placeholder="{{ $sourceType === 'crm' ? 'CRM Kontakt suchen...' : 'Mitarbeiter suchen...' }}"
                    wire:model.live.debounce.300ms="sourceSearch"
                    size="sm"
                />

                {{-- CRM Contact Selection --}}
                @if($sourceType === 'crm')
                    @if($availableContacts->count() > 0)
                        <div class="max-h-64 overflow-y-auto space-y-1 border border-[var(--ui-border)] rounded-md p-2">
                            @foreach($availableContacts as $contact)
                                <label class="flex items-center gap-3 p-2 rounded hover:bg-[var(--ui-muted-5)] cursor-pointer {{ $selectedContactId == $contact->id ? 'bg-[var(--ui-muted-5)] ring-1 ring-[var(--ui-primary)]' : '' }}">
                                    <input type="radio" value="{{ $contact->id }}" wire:model="selectedContactId"
                                        class="text-[var(--ui-primary)] focus:ring-[var(--ui-primary)]">
                                    <div>
                                        <div class="text-sm font-medium text-[var(--ui-secondary)]">{{ $contact->full_name }}</div>
                                        <div class="text-xs text-[var(--ui-muted)]">
                                            {{ $contact->emailAddresses()->where('is_active', true)->orderByDesc('is_primary')->first()?->email_address ?? '' }}
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    @else
                        <div class="text-sm text-[var(--ui-muted)] text-center py-4 border border-[var(--ui-border)] rounded-md">
                            @if(trim($sourceSearch) !== '')
                                Keine passenden CRM-Kontakte gefunden.
                            @else
                                Keine verfügbaren CRM-Kontakte.
                            @endif
                        </div>
                    @endif
                    @error('selectedContactId')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                @endif

                {{-- HCM Employee Selection --}}
                @if($sourceType === 'hcm')
                    @if($availableEmployees->count() > 0)
                        <div class="max-h-64 overflow-y-auto space-y-1 border border-[var(--ui-border)] rounded-md p-2">
                            @foreach($availableEmployees as $employee)
                                <label class="flex items-center gap-3 p-2 rounded hover:bg-[var(--ui-muted-5)] cursor-pointer {{ $selectedEmployeeId == $employee->id ? 'bg-[var(--ui-muted-5)] ring-1 ring-[var(--ui-primary)]' : '' }}">
                                    <input type="radio" value="{{ $employee->id }}" wire:model="selectedEmployeeId"
                                        class="text-[var(--ui-primary)] focus:ring-[var(--ui-primary)]">
                                    <div>
                                        <div class="text-sm font-medium text-[var(--ui-secondary)]">
                                            {{ $employee->getContact()?->full_name ?? 'Kein CRM-Kontakt' }}
                                        </div>
                                        <div class="text-xs text-[var(--ui-muted)]">
                                            @if($employee->employee_number)
                                                <span>Nr. {{ $employee->employee_number }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    @else
                        <div class="text-sm text-[var(--ui-muted)] text-center py-4 border border-[var(--ui-border)] rounded-md">
                            @if(trim($sourceSearch) !== '')
                                Keine passenden Mitarbeiter gefunden.
                            @else
                                Keine verfügbaren Mitarbeiter.
                            @endif
                        </div>
                    @endif
                    @error('selectedEmployeeId')
                        <span class="text-xs text-red-500">{{ $message }}</span>
                    @enderror
                @endif
            @endif

            <x-ui-input-textarea
                name="notes"
                label="Notizen"
                wire:model="notes"
                placeholder="Besonderheiten, Allergien, etc..."
                rows="2"
            />

            <x-ui-input-checkbox
                model="is_active"
                checked-label="Aktiv"
                unchecked-label="Inaktiv"
            />
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
