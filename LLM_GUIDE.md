# LLM Guide - Training Module

## Modul-Key: `training`

### Namenskonventionen
- PascalCase: `Training`
- kebab-case: `training`
- snake_case: `training`
- Tabellen-Prefix: `training_`

### Models

| Model | Tabelle | Beschreibung |
|-------|---------|-------------|
| `TrainingGroup` | `training_groups` | Schulungsgruppen mit Hierarchie |
| `Training` | `trainings` | Schulungen mit Abhängigkeiten |
| `TrainingSession` | `training_sessions` | Schulungstermine |

### Pivot-Tabellen

| Tabelle | Beziehung |
|---------|-----------|
| `training_prerequisites` | Training <-> Training (Voraussetzungen) |

### Wichtige Beziehungen

```
TrainingGroup (parent_id -> self)
    └── Training (group_id -> training_groups)
            ├── prerequisites (training_prerequisites pivot)
            └── TrainingSession (training_id -> trainings)
```

### Namespace
```
Platform\Training\Models\TrainingGroup
Platform\Training\Models\Training
Platform\Training\Models\TrainingSession
Platform\Training\Livewire\Dashboard
Platform\Training\Livewire\Sidebar
```

### Status-Werte für TrainingSession
- `planned` (Standard)
- `confirmed`
- `cancelled`
- `completed`
