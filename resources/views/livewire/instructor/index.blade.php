<x-ui-page>
    <x-slot name="navbar">
        <x-ui-page-navbar title="Referenten" icon="heroicon-o-user-group" />
    </x-slot>

    <x-slot name="sidebar">
        <x-ui-page-sidebar title="Schnellzugriff" width="w-80" :defaultOpen="true" side="left">
            <div class="p-6 space-y-6">
                <div>
                    <h3 class="text-sm font-bold text-[var(--ui-secondary)] uppercase tracking-wider mb-3">Aktionen</h3>
                    <div class="space-y-2">
                        <x-ui-button variant="success" size="sm" wire:click="openCreateModal" class="w-full justify-start">
                            @svg('heroicon-o-plus', 'w-4 h-4')
                            <span class="ml-2">Neuer Referent</span>
                        </x-ui-button>
                    </div>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-[var(--ui-secondary)] uppercase tracking-wider mb-3">Suche</h3>
                    <x-ui-input-text
                        name="search"
                        placeholder="Referent suchen..."
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
                    <h1 class="text-2xl font-bold text-[var(--ui-secondary)]">Referenten</h1>
                    <p class="text-[var(--ui-muted)] mt-1">Verwalten Sie Ihre Schulungsreferenten und Dozenten</p>
                </div>
                <x-ui-button variant="success" size="sm" wire:click="openCreateModal">
                    <span class="inline-flex items-center gap-2">
                        @svg('heroicon-o-plus', 'w-4 h-4')
                        <span>Neuer Referent</span>
                    </span>
                </x-ui-button>
            </div>

            @if($instructors->count() === 0)
                <div class="rounded-lg border border-[var(--ui-border)] bg-[var(--ui-surface)] p-6 text-sm text-[var(--ui-muted)] text-center">
                    Keine Referenten vorhanden. Erstellen Sie den ersten Referenten.
                </div>
            @else
                <x-ui-table compact="true">
                    <x-ui-table-header>
                        <x-ui-table-header-cell compact="true" sortable="true" sortField="name" :currentSort="$sortField" :sortDirection="$sortDirection">Name</x-ui-table-header-cell>
                        <x-ui-table-header-cell compact="true">Kontakt</x-ui-table-header-cell>
                        <x-ui-table-header-cell compact="true">Schulungen</x-ui-table-header-cell>
                        <x-ui-table-header-cell compact="true">Termine</x-ui-table-header-cell>
                        <x-ui-table-header-cell compact="true">Status</x-ui-table-header-cell>
                        <x-ui-table-header-cell compact="true" class="text-right">Aktionen</x-ui-table-header-cell>
                    </x-ui-table-header>

                    <x-ui-table-body>
                        @foreach($instructors as $instructor)
                            <x-ui-table-row compact="true">
                                <x-ui-table-cell compact="true">
                                    <div class="font-medium text-[var(--ui-secondary)]">{{ $instructor->name }}</div>
                                    @if($instructor->description)
                                        <div class="text-xs text-[var(--ui-muted)] mt-0.5 truncate max-w-xs">{{ $instructor->description }}</div>
                                    @endif
                                </x-ui-table-cell>
                                <x-ui-table-cell compact="true">
                                    <div class="text-sm space-y-0.5">
                                        @if($instructor->email)
                                            <div class="flex items-center gap-1 text-[var(--ui-muted)]">
                                                @svg('heroicon-o-envelope', 'w-3.5 h-3.5')
                                                <span>{{ $instructor->email }}</span>
                                            </div>
                                        @endif
                                        @if($instructor->phone)
                                            <div class="flex items-center gap-1 text-[var(--ui-muted)]">
                                                @svg('heroicon-o-phone', 'w-3.5 h-3.5')
                                                <span>{{ $instructor->phone }}</span>
                                            </div>
                                        @endif
                                        @if(!$instructor->email && !$instructor->phone)
                                            <span class="text-[var(--ui-muted)] text-xs">–</span>
                                        @endif
                                    </div>
                                </x-ui-table-cell>
                                <x-ui-table-cell compact="true">
                                    <span class="text-sm">{{ $instructor->trainings->count() }}</span>
                                </x-ui-table-cell>
                                <x-ui-table-cell compact="true">
                                    <span class="text-sm">{{ $instructor->sessions->count() }}</span>
                                </x-ui-table-cell>
                                <x-ui-table-cell compact="true">
                                    @if($instructor->is_active)
                                        <x-ui-badge variant="success" size="sm">Aktiv</x-ui-badge>
                                    @else
                                        <x-ui-badge variant="secondary" size="sm">Inaktiv</x-ui-badge>
                                    @endif
                                </x-ui-table-cell>
                                <x-ui-table-cell compact="true" class="text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <x-ui-button variant="secondary-outline" size="xs" wire:click="openEditModal({{ $instructor->id }})">
                                            @svg('heroicon-o-pencil-square', 'w-4 h-4')
                                        </x-ui-button>
                                        <x-ui-button variant="danger-outline" size="xs" wire:click="delete({{ $instructor->id }})" wire:confirm="Referent &quot;{{ $instructor->name }}&quot; wirklich löschen?">
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
            {{ $editMode ? 'Referent bearbeiten' : 'Neuen Referenten anlegen' }}
        </x-slot>

        <div class="space-y-4">
            <x-ui-input-text
                name="name"
                label="Name"
                wire:model="name"
                placeholder="Vor- und Nachname..."
                required
            />

            <x-ui-form-grid :cols="2" :gap="4">
                <x-ui-input-text
                    name="email"
                    label="E-Mail"
                    wire:model="email"
                    type="email"
                    placeholder="referent@beispiel.de"
                />

                <x-ui-input-text
                    name="phone"
                    label="Telefon"
                    wire:model="phone"
                    placeholder="+49 ..."
                />
            </x-ui-form-grid>

            <x-ui-input-textarea
                name="description"
                label="Beschreibung"
                wire:model="description"
                placeholder="Qualifikationen, Fachgebiete..."
                rows="3"
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
