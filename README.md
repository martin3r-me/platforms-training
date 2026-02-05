# Platform Training Module

Modul zur Verwaltung von Schulungen, Schulungsgruppen und Schulungsterminen.

## Struktur

```
training/
├── composer.json
├── config/
│   └── training.php
├── database/
│   └── migrations/
│       ├── 2026_02_05_000001_create_training_groups_table.php
│       ├── 2026_02_05_000002_create_trainings_table.php
│       ├── 2026_02_05_000003_create_training_prerequisites_table.php
│       └── 2026_02_05_000004_create_training_sessions_table.php
├── resources/
│   └── views/
│       └── livewire/
│           ├── dashboard.blade.php
│           ├── sidebar.blade.php
│           └── test.blade.php
├── routes/
│   └── web.php
└── src/
    ├── TrainingServiceProvider.php
    ├── Livewire/
    │   ├── Dashboard.php
    │   ├── Sidebar.php
    │   └── Test.php
    └── Models/
        ├── TrainingGroup.php
        ├── Training.php
        └── TrainingSession.php
```

## Entitäten

### TrainingGroup (Schulungsgruppen)
- Hierarchische Gruppenstruktur (Eltern-Kind via `parent_id`)
- Tabelle: `training_groups`

### Training (Schulungen)
- Gehört zu einer Gruppe (`group_id`)
- Kann Voraussetzungen haben (many-to-many self-referencing via `training_prerequisites`)
- Tabelle: `trainings`

### TrainingSession (Schulungstermine)
- Gehört zu einer Schulung (`training_id`)
- Eigenständiges Model mit: `starts_at`, `ends_at`, `location`, `min_participants`, `max_participants`, `status`
- Tabelle: `training_sessions`

## Beziehungen

- `TrainingGroup` -> parent/children (self-referencing)
- `TrainingGroup` -> hasMany `Training`
- `Training` -> belongsTo `TrainingGroup`
- `Training` -> belongsToMany `Training` (prerequisites/dependents)
- `Training` -> hasMany `TrainingSession`
- `TrainingSession` -> belongsTo `Training`

## Setup

In `composer.json` der Hauptanwendung:
```json
{
  "repositories": [
    {
      "type": "path",
      "url": "../platform/modules/training"
    }
  ],
  "require": {
    "martin3r/platform-training": "dev-main"
  }
}
```

Dann: `composer update && php artisan migrate`
