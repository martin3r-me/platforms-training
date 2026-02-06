<x-ui-page>
    <x-slot name="navbar">
        <x-ui-page-navbar title="Schulungsgruppen" icon="heroicon-o-folder" />
    </x-slot>

    <x-slot name="sidebar">
        <x-ui-page-sidebar title="Schnellzugriff" width="w-80" :defaultOpen="true" side="left">
            <div class="p-6 space-y-6">
                <div>
                    <h3 class="text-sm font-bold text-[var(--ui-secondary)] uppercase tracking-wider mb-3">Aktionen</h3>
                    <div class="space-y-2">
                        <x-ui-button variant="success" size="sm" wire:click="openCreateModal" class="w-full justify-start">
                            @svg('heroicon-o-plus', 'w-4 h-4')
                            <span class="ml-2">Neue Gruppe</span>
                        </x-ui-button>
                    </div>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-[var(--ui-secondary)] uppercase tracking-wider mb-3">Suche</h3>
                    <x-ui-input-text
                        name="search"
                        placeholder="Gruppe suchen..."
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
                    <h1 class="text-2xl font-bold text-[var(--ui-secondary)]">Schulungsgruppen</h1>
                    <p class="text-[var(--ui-muted)] mt-1">Verwalten Sie Ihre Schulungsgruppen</p>
                </div>
                <x-ui-button variant="success" size="sm" wire:click="openCreateModal">
                    <span class="inline-flex items-center gap-2">
                        @svg('heroicon-o-plus', 'w-4 h-4')
                        <span>Neue Gruppe</span>
                    </span>
                </x-ui-button>
            </div>

            @if($groups->count() === 0)
                <div class="rounded-lg border border-[var(--ui-border)] bg-[var(--ui-surface)] p-6 text-sm text-[var(--ui-muted)] text-center">
                    Keine Schulungsgruppen vorhanden. Erstellen Sie die erste Gruppe.
                </div>
            @else
                <x-ui-table compact="true">
                    <x-ui-table-header>
                        <x-ui-table-header-cell compact="true" sortable="true" sortField="code" :currentSort="$sortField" :sortDirection="$sortDirection">Kürzel</x-ui-table-header-cell>
                        <x-ui-table-header-cell compact="true" sortable="true" sortField="name" :currentSort="$sortField" :sortDirection="$sortDirection">Name</x-ui-table-header-cell>
                        <x-ui-table-header-cell compact="true">Elterngruppe</x-ui-table-header-cell>
                        <x-ui-table-header-cell compact="true">Schulungen</x-ui-table-header-cell>
                        <x-ui-table-header-cell compact="true">Status</x-ui-table-header-cell>
                        <x-ui-table-header-cell compact="true" class="text-right">Aktionen</x-ui-table-header-cell>
                    </x-ui-table-header>

                    <x-ui-table-body>
                        @foreach($groups as $group)
                            <x-ui-table-row compact="true">
                                <x-ui-table-cell compact="true">
                                    @if($group->code)
                                        <code class="text-xs font-bold px-1.5 py-0.5 bg-[var(--ui-muted-5)] rounded">{{ $group->code }}</code>
                                    @else
                                        <span class="text-[var(--ui-muted)] text-xs">–</span>
                                    @endif
                                </x-ui-table-cell>
                                <x-ui-table-cell compact="true">
                                    <div class="font-medium text-[var(--ui-secondary)]">{{ $group->name }}</div>
                                    @if($group->description)
                                        <div class="text-xs text-[var(--ui-muted)] mt-0.5 truncate max-w-xs">{{ $group->description }}</div>
                                    @endif
                                </x-ui-table-cell>
                                <x-ui-table-cell compact="true">
                                    @if($group->parent)
                                        <x-ui-badge variant="secondary" size="sm">{{ $group->parent->name }}</x-ui-badge>
                                    @else
                                        <span class="text-[var(--ui-muted)] text-xs">–</span>
                                    @endif
                                </x-ui-table-cell>
                                <x-ui-table-cell compact="true">
                                    <span class="text-sm">{{ $group->trainings->count() }}</span>
                                </x-ui-table-cell>
                                <x-ui-table-cell compact="true">
                                    @if($group->is_active)
                                        <x-ui-badge variant="success" size="sm">Aktiv</x-ui-badge>
                                    @else
                                        <x-ui-badge variant="secondary" size="sm">Inaktiv</x-ui-badge>
                                    @endif
                                </x-ui-table-cell>
                                <x-ui-table-cell compact="true" class="text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <x-ui-button variant="secondary-outline" size="xs" wire:click="openEditModal({{ $group->id }})">
                                            @svg('heroicon-o-pencil-square', 'w-4 h-4')
                                        </x-ui-button>
                                        <x-ui-button variant="danger-outline" size="xs" wire:click="delete({{ $group->id }})" wire:confirm="Gruppe &quot;{{ $group->name }}&quot; wirklich löschen?">
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
            {{ $editMode ? 'Gruppe bearbeiten' : 'Neue Gruppe anlegen' }}
        </x-slot>

        <div class="space-y-4">
            <x-ui-form-grid :cols="1" :gap="4">
                <x-ui-form-grid :cols="2" :gap="4">
                    <x-ui-input-text
                        name="name"
                        label="Name"
                        wire:model="name"
                        placeholder="Gruppenname eingeben..."
                        required
                    />

                    <x-ui-input-text
                        name="code"
                        label="Kürzel"
                        wire:model="code"
                        placeholder="z.B. BK, IT, HR"
                        required
                    />
                </x-ui-form-grid>

                <x-ui-input-textarea
                    name="description"
                    label="Beschreibung"
                    wire:model="description"
                    placeholder="Optionale Beschreibung..."
                    rows="3"
                />

                <x-ui-input-select
                    name="parent_id"
                    label="Elterngruppe"
                    wire:model="parent_id"
                    :nullable="true"
                    nullLabel="– Keine (Hauptgruppe) –"
                >
                    @foreach($allGroups as $g)
                        @if(!$editMode || $g->id !== $editId)
                            <option value="{{ $g->id }}">{{ $g->name }}</option>
                        @endif
                    @endforeach
                </x-ui-input-select>

                <x-ui-input-checkbox
                    model="is_active"
                    checked-label="Aktiv"
                    unchecked-label="Inaktiv"
                />
            </x-ui-form-grid>
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
