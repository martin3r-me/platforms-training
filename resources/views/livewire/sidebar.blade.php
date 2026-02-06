<div>
    <div x-show="!collapsed" class="p-3 text-sm italic text-[var(--ui-secondary)] uppercase border-b border-[var(--ui-border)] mb-2">
        Training
    </div>

    <x-ui-sidebar-list label="Allgemein">
        <x-ui-sidebar-item :href="route('training.dashboard')">
            @svg('heroicon-o-home', 'w-4 h-4 text-[var(--ui-secondary)]')
            <span class="ml-2 text-sm">Dashboard</span>
        </x-ui-sidebar-item>
    </x-ui-sidebar-list>

    <x-ui-sidebar-list label="Verwaltung">
        <x-ui-sidebar-item :href="route('training.groups.index')">
            @svg('heroicon-o-folder', 'w-4 h-4 text-[var(--ui-secondary)]')
            <span class="ml-2 text-sm">Schulungsgruppen</span>
        </x-ui-sidebar-item>
        <x-ui-sidebar-item :href="route('training.trainings.index')">
            @svg('heroicon-o-academic-cap', 'w-4 h-4 text-[var(--ui-secondary)]')
            <span class="ml-2 text-sm">Schulungen</span>
        </x-ui-sidebar-item>
        <x-ui-sidebar-item :href="route('training.sessions.index')">
            @svg('heroicon-o-calendar-days', 'w-4 h-4 text-[var(--ui-secondary)]')
            <span class="ml-2 text-sm">Schulungstermine</span>
        </x-ui-sidebar-item>
        <x-ui-sidebar-item :href="route('training.instructors.index')">
            @svg('heroicon-o-user-group', 'w-4 h-4 text-[var(--ui-secondary)]')
            <span class="ml-2 text-sm">Referenten</span>
        </x-ui-sidebar-item>
        <x-ui-sidebar-item :href="route('training.participants.index')">
            @svg('heroicon-o-users', 'w-4 h-4 text-[var(--ui-secondary)]')
            <span class="ml-2 text-sm">Teilnehmer</span>
        </x-ui-sidebar-item>
    </x-ui-sidebar-list>

    <div x-show="collapsed" class="px-2 py-2 border-b border-[var(--ui-border)]">
        <div class="flex flex-col gap-2">
            <a href="{{ route('training.dashboard') }}" wire:navigate class="flex items-center justify-center p-2 rounded-md text-[var(--ui-secondary)] hover:bg-[var(--ui-muted-5)]">
                @svg('heroicon-o-home', 'w-5 h-5')
            </a>
            <a href="{{ route('training.groups.index') }}" wire:navigate class="flex items-center justify-center p-2 rounded-md text-[var(--ui-secondary)] hover:bg-[var(--ui-muted-5)]">
                @svg('heroicon-o-folder', 'w-5 h-5')
            </a>
            <a href="{{ route('training.trainings.index') }}" wire:navigate class="flex items-center justify-center p-2 rounded-md text-[var(--ui-secondary)] hover:bg-[var(--ui-muted-5)]">
                @svg('heroicon-o-academic-cap', 'w-5 h-5')
            </a>
            <a href="{{ route('training.sessions.index') }}" wire:navigate class="flex items-center justify-center p-2 rounded-md text-[var(--ui-secondary)] hover:bg-[var(--ui-muted-5)]">
                @svg('heroicon-o-calendar-days', 'w-5 h-5')
            </a>
            <a href="{{ route('training.instructors.index') }}" wire:navigate class="flex items-center justify-center p-2 rounded-md text-[var(--ui-secondary)] hover:bg-[var(--ui-muted-5)]">
                @svg('heroicon-o-user-group', 'w-5 h-5')
            </a>
            <a href="{{ route('training.participants.index') }}" wire:navigate class="flex items-center justify-center p-2 rounded-md text-[var(--ui-secondary)] hover:bg-[var(--ui-muted-5)]">
                @svg('heroicon-o-users', 'w-5 h-5')
            </a>
        </div>
    </div>
</div>
