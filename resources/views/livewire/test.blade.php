<x-ui-page>
    <x-slot name="navbar">
        <x-ui-page-navbar title="Test" icon="heroicon-o-beaker" />
    </x-slot>

    <x-ui-page-container>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-[var(--ui-secondary)]">Test-Seite</h1>
                    <p class="text-[var(--ui-muted)] mt-1">Training Module Test</p>
                </div>
            </div>

            <x-ui-panel title="UI-Komponenten Test" subtitle="Verschiedene UI-Komponenten zum Testen">
                <div class="space-y-6">
                    <div>
                        <h3 class="text-sm font-semibold text-[var(--ui-secondary)] mb-3">Buttons</h3>
                        <div class="flex flex-wrap gap-2">
                            <x-ui-button variant="primary" size="sm">Primary</x-ui-button>
                            <x-ui-button variant="secondary" size="sm">Secondary</x-ui-button>
                            <x-ui-button variant="success" size="sm">Success</x-ui-button>
                            <x-ui-button variant="danger" size="sm">Danger</x-ui-button>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold text-[var(--ui-secondary)] mb-3">Test Action</h3>
                        <x-ui-button variant="primary" wire:click="testAction">
                            Test-Aktion ausführen
                        </x-ui-button>
                    </div>
                </div>
            </x-ui-panel>
        </div>
    </x-ui-page-container>

    <x-slot name="sidebar">
        <x-ui-page-sidebar title="Schnellzugriff" width="w-80" :defaultOpen="true">
            <div class="p-6 space-y-6">
                <div>
                    <h3 class="text-sm font-bold text-[var(--ui-secondary)] uppercase tracking-wider mb-3">Navigation</h3>
                    <div class="space-y-2">
                        <x-ui-button variant="secondary-outline" size="sm" :href="route('training.dashboard')" wire:navigate class="w-full">
                            <span class="flex items-center gap-2">
                                @svg('heroicon-o-home', 'w-4 h-4')
                                Dashboard
                            </span>
                        </x-ui-button>
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
