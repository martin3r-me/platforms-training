<x-ui-page>
    <x-slot name="navbar">
        <x-ui-page-navbar title="Training Dashboard" icon="heroicon-o-academic-cap" />
    </x-slot>

    <x-ui-page-container>
        <div class="space-y-6">
            <x-ui-panel title="Schulungsverwaltung" subtitle="Willkommen im Training-Modul">
                <div class="p-6 text-center">
                    <div class="mb-4">
                        @svg('heroicon-o-academic-cap', 'w-16 h-16 text-[var(--ui-primary)] mx-auto')
                    </div>
                    <h2 class="text-xl font-semibold text-[var(--ui-secondary)] mb-2">
                        Schulungsverwaltung
                    </h2>
                    <p class="text-[var(--ui-muted)]">
                        Verwalten Sie Schulungen, Gruppen und Termine.
                    </p>
                </div>
            </x-ui-panel>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <x-ui-dashboard-tile
                    title="Gruppen"
                    :count="$totalGroups"
                    subtitle="Schulungsgruppen"
                    icon="folder"
                    variant="secondary"
                    size="lg"
                />
                <x-ui-dashboard-tile
                    title="Schulungen"
                    :count="$totalTrainings"
                    subtitle="Gesamt"
                    icon="academic-cap"
                    variant="secondary"
                    size="lg"
                />
                <x-ui-dashboard-tile
                    title="Aktiv"
                    :count="$activeTrainings"
                    subtitle="Aktive Schulungen"
                    icon="check-circle"
                    variant="secondary"
                    size="lg"
                />
                <x-ui-dashboard-tile
                    title="Termine"
                    :count="$upcomingSessions"
                    subtitle="Kommende Termine"
                    icon="calendar-days"
                    variant="secondary"
                    size="lg"
                />
            </div>
        </div>
    </x-ui-page-container>

    <x-slot name="sidebar">
        <x-ui-page-sidebar title="Schnellzugriff" width="w-80" :defaultOpen="true">
            <div class="p-6 space-y-6">
                <div>
                    <h3 class="text-sm font-bold text-[var(--ui-secondary)] uppercase tracking-wider mb-3">Schnellstatistiken</h3>
                    <div class="space-y-3">
                        <div class="p-3 bg-[var(--ui-muted-5)] rounded-lg border border-[var(--ui-border)]/40">
                            <div class="text-xs text-[var(--ui-muted)]">Schulungsgruppen</div>
                            <div class="text-lg font-bold text-[var(--ui-secondary)]">{{ $totalGroups }}</div>
                        </div>
                        <div class="p-3 bg-[var(--ui-muted-5)] rounded-lg border border-[var(--ui-border)]/40">
                            <div class="text-xs text-[var(--ui-muted)]">Schulungen</div>
                            <div class="text-lg font-bold text-[var(--ui-secondary)]">{{ $totalTrainings }}</div>
                        </div>
                        <div class="p-3 bg-[var(--ui-muted-5)] rounded-lg border border-[var(--ui-border)]/40">
                            <div class="text-xs text-[var(--ui-muted)]">Kommende Termine</div>
                            <div class="text-lg font-bold text-[var(--ui-secondary)]">{{ $upcomingSessions }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </x-ui-page-sidebar>
    </x-slot>

    <x-slot name="activity">
        <x-ui-page-sidebar title="Aktivitäten" width="w-80" :defaultOpen="false" storeKey="activityOpen" side="right">
            <div class="p-4 space-y-4">
                <div class="text-sm text-[var(--ui-muted)]">Letzte Aktivitäten</div>
            </div>
        </x-ui-page-sidebar>
    </x-slot>
</x-ui-page>
