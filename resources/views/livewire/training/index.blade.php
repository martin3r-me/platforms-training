<x-ui-page>
    <x-slot name="navbar">
        <x-ui-page-navbar title="Schulungen" icon="heroicon-o-academic-cap" />
    </x-slot>

    <x-slot name="sidebar">
        <x-ui-page-sidebar title="Schnellzugriff" width="w-80" :defaultOpen="true" side="left">
            <div class="p-6 space-y-6">
                <div>
                    <h3 class="text-sm font-bold text-[var(--ui-secondary)] uppercase tracking-wider mb-3">Aktionen</h3>
                    <div class="space-y-2">
                        <x-ui-button variant="success" size="sm" wire:click="openCreateModal" class="w-full justify-start">
                            @svg('heroicon-o-plus', 'w-4 h-4')
                            <span class="ml-2">Neue Schulung</span>
                        </x-ui-button>
                    </div>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-[var(--ui-secondary)] uppercase tracking-wider mb-3">Suche</h3>
                    <x-ui-input-text
                        name="search"
                        placeholder="Schulung suchen..."
                        class="w-full"
                        size="sm"
                        wire:model.live.debounce.300ms="search"
                    />
                </div>
                <div>
                    <h3 class="text-sm font-bold text-[var(--ui-secondary)] uppercase tracking-wider mb-3">Filter</h3>
                    <x-ui-input-select
                        name="groupFilter"
                        label="Gruppe"
                        wire:model.live="groupFilter"
                        :nullable="true"
                        nullLabel="– Alle Gruppen –"
                        size="sm"
                    >
                        @foreach($groups as $g)
                            <option value="{{ $g->id }}">{{ $g->name }}</option>
                        @endforeach
                    </x-ui-input-select>
                </div>
            </div>
        </x-ui-page-sidebar>
    </x-slot>

    <x-ui-page-container>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-[var(--ui-secondary)]">Schulungen</h1>
                    <p class="text-[var(--ui-muted)] mt-1">Verwalten Sie Ihre Schulungen und deren Abhängigkeiten</p>
                </div>
                <x-ui-button variant="success" size="sm" wire:click="openCreateModal">
                    <span class="inline-flex items-center gap-2">
                        @svg('heroicon-o-plus', 'w-4 h-4')
                        <span>Neue Schulung</span>
                    </span>
                </x-ui-button>
            </div>

            @if($trainings->count() === 0)
                <div class="rounded-lg border border-[var(--ui-border)] bg-[var(--ui-surface)] p-6 text-sm text-[var(--ui-muted)] text-center">
                    Keine Schulungen vorhanden. Erstellen Sie die erste Schulung.
                </div>
            @else
                <x-ui-table compact="true">
                    <x-ui-table-header>
                        <x-ui-table-header-cell compact="true" sortable="true" sortField="name" :currentSort="$sortField" :sortDirection="$sortDirection">Name</x-ui-table-header-cell>
                        <x-ui-table-header-cell compact="true" sortable="true" sortField="code" :currentSort="$sortField" :sortDirection="$sortDirection">Code</x-ui-table-header-cell>
                        <x-ui-table-header-cell compact="true">Gruppe</x-ui-table-header-cell>
                        <x-ui-table-header-cell compact="true">Referenten</x-ui-table-header-cell>
                        <x-ui-table-header-cell compact="true">Voraussetzungen</x-ui-table-header-cell>
                        <x-ui-table-header-cell compact="true">Termine</x-ui-table-header-cell>
                        <x-ui-table-header-cell compact="true">Status</x-ui-table-header-cell>
                        <x-ui-table-header-cell compact="true" class="text-right">Aktionen</x-ui-table-header-cell>
                    </x-ui-table-header>

                    <x-ui-table-body>
                        @foreach($trainings as $training)
                            <x-ui-table-row compact="true">
                                <x-ui-table-cell compact="true">
                                    <div class="font-medium text-[var(--ui-secondary)]">{{ $training->name }}</div>
                                    @if($training->description)
                                        <div class="text-xs text-[var(--ui-muted)] mt-0.5 truncate max-w-xs">{{ $training->description }}</div>
                                    @endif
                                </x-ui-table-cell>
                                <x-ui-table-cell compact="true">
                                    @if($training->code)
                                        <code class="text-xs px-1.5 py-0.5 bg-[var(--ui-muted-5)] rounded">{{ $training->code }}</code>
                                    @else
                                        <span class="text-[var(--ui-muted)] text-xs">–</span>
                                    @endif
                                </x-ui-table-cell>
                                <x-ui-table-cell compact="true">
                                    @if($training->group)
                                        <x-ui-badge variant="secondary" size="sm">{{ $training->group->name }}</x-ui-badge>
                                    @else
                                        <span class="text-[var(--ui-muted)] text-xs">–</span>
                                    @endif
                                </x-ui-table-cell>
                                <x-ui-table-cell compact="true">
                                    @if($training->instructors->count() > 0)
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($training->instructors as $inst)
                                                <x-ui-badge variant="info" size="sm">{{ $inst->name }}</x-ui-badge>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-[var(--ui-muted)] text-xs">–</span>
                                    @endif
                                </x-ui-table-cell>
                                <x-ui-table-cell compact="true">
                                    @if($training->prerequisites->count() > 0)
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($training->prerequisites as $prereq)
                                                <x-ui-badge variant="warning" size="sm">{{ $prereq->name }}</x-ui-badge>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-[var(--ui-muted)] text-xs">Keine</span>
                                    @endif
                                </x-ui-table-cell>
                                <x-ui-table-cell compact="true">
                                    <a href="{{ route('training.sessions.index', ['training' => $training->id]) }}" wire:navigate
                                       class="inline-flex items-center gap-1 text-sm text-[var(--ui-primary)] hover:underline">
                                        {{ $training->sessions->count() }}
                                        @svg('heroicon-o-calendar-days', 'w-3.5 h-3.5')
                                    </a>
                                </x-ui-table-cell>
                                <x-ui-table-cell compact="true">
                                    @if($training->is_active)
                                        <x-ui-badge variant="success" size="sm">Aktiv</x-ui-badge>
                                    @else
                                        <x-ui-badge variant="secondary" size="sm">Inaktiv</x-ui-badge>
                                    @endif
                                </x-ui-table-cell>
                                <x-ui-table-cell compact="true" class="text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <x-ui-button variant="primary-outline" size="xs" :href="route('training.sessions.index', ['training' => $training->id])">
                                            @svg('heroicon-o-calendar-days', 'w-4 h-4')
                                        </x-ui-button>
                                        <x-ui-button variant="secondary-outline" size="xs" wire:click="openEditModal({{ $training->id }})">
                                            @svg('heroicon-o-pencil-square', 'w-4 h-4')
                                        </x-ui-button>
                                        <x-ui-button variant="danger-outline" size="xs" wire:click="delete({{ $training->id }})" wire:confirm="Schulung &quot;{{ $training->name }}&quot; wirklich löschen?">
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
            {{ $editMode ? 'Schulung bearbeiten' : 'Neue Schulung anlegen' }}
        </x-slot>

        <div class="space-y-4">
            <x-ui-input-text
                name="name"
                label="Name"
                wire:model="name"
                placeholder="Schulungsname eingeben..."
                required
            />

            @if($codePreview)
                <div>
                    <label class="block text-sm font-medium text-[var(--ui-secondary)] mb-1">Code</label>
                    <div class="px-3 py-2 bg-[var(--ui-muted-5)] border border-[var(--ui-border)] rounded-md text-sm">
                        <code class="font-bold">{{ $codePreview }}</code>
                        <span class="text-xs text-[var(--ui-muted)] ml-2">(wird automatisch generiert)</span>
                    </div>
                </div>
            @endif

            <x-ui-input-textarea
                name="description"
                label="Beschreibung"
                wire:model="description"
                placeholder="Optionale Beschreibung..."
                rows="3"
            />

            <x-ui-form-grid :cols="2" :gap="4">
                <x-ui-input-select
                    name="group_id"
                    label="Gruppe"
                    wire:model.live="group_id"
                    :nullable="true"
                    nullLabel="– Keine Gruppe –"
                >
                    @foreach($groups as $g)
                        <option value="{{ $g->id }}">{{ $g->name }}</option>
                    @endforeach
                </x-ui-input-select>

                <x-ui-input-checkbox
                    model="is_active"
                    checked-label="Aktiv"
                    unchecked-label="Inaktiv"
                />
            </x-ui-form-grid>

            @if($allInstructors->count() > 0)
                <div>
                    <label class="block text-sm font-medium text-[var(--ui-secondary)] mb-2">Referenten</label>
                    <p class="text-xs text-[var(--ui-muted)] mb-2">Welche Referenten können diese Schulung durchführen?</p>
                    <div class="max-h-48 overflow-y-auto space-y-1 border border-[var(--ui-border)] rounded-md p-2">
                        @foreach($allInstructors as $inst)
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
            @endif

            @if($allTrainings->count() > 0)
                <div>
                    <label class="block text-sm font-medium text-[var(--ui-secondary)] mb-2">Voraussetzungen</label>
                    <p class="text-xs text-[var(--ui-muted)] mb-2">Welche Schulungen müssen vorher absolviert werden?</p>
                    <div class="max-h-48 overflow-y-auto space-y-1 border border-[var(--ui-border)] rounded-md p-2">
                        @foreach($allTrainings as $t)
                            @if(!$editMode || $t->id !== $editId)
                                <label class="flex items-center gap-2 p-1.5 rounded hover:bg-[var(--ui-muted-5)] cursor-pointer">
                                    <input type="checkbox" value="{{ $t->id }}" wire:model="prerequisite_ids"
                                        class="rounded border-[var(--ui-border)] text-[var(--ui-primary)] focus:ring-[var(--ui-primary)]">
                                    <span class="text-sm text-[var(--ui-secondary)]">{{ $t->name }}</span>
                                    @if($t->code)
                                        <code class="text-xs px-1 bg-[var(--ui-muted-5)] rounded">{{ $t->code }}</code>
                                    @endif
                                </label>
                            @endif
                        @endforeach
                    </div>
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
