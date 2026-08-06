# Architecture Document (Architecture.md)

## 1. System Overview
A monolith Laravel application serving server-rendered Blade templates, executing pure PHP AHP math, and displaying spatial data via Leaflet.js on the client side.

## 2. Directory Structure
```text
skripsi/
├── PRD.md
├── AGENTS.md
├── Architecture.md
└── web/ (Laravel Application Root)
    ├── app/
    │   ├── Models/
    │   │   ├── Criterion.php
    │   │   ├── Alternative.php
    │   │   └── AlternativeScore.php
    │   ├── Http/
    │   │   └── Controllers/
    │   │       ├── MapController.php
    │   │       └── AhpController.php
    │   └── Services/
    │       └── AhpService.php (Core Math Logic)
    ├── database/
    │   ├── migrations/
    │   └── seeders/
    ├── resources/
    │   └── views/
    │       ├── layout/
    │       ├── map/
    │       └── admin/
    └── routes/
        └── web.php
```

## 3. Data Flow (AHP + GIS)
1. User visits Map UI.
2. User selects priority weights (Pairwise comparison form).
3. Form submitted -> `AhpController`.
4. `AhpController` passes raw form data to `AhpService`.
5. `AhpService`:
   - Builds pairwise comparison matrix.
   - Calculates Priority Vectors (eigen).
   - Calculates Consistency Ratio (CR). If CR > 0.1, throws error/flag.
   - Calculates final score for each `Alternative` based on `AlternativeScore` data multiplied by criterion weights.
6. Returns sorted list of `Alternative` objects (with lat/lng and scores) to Controller.
7. Controller passes JSON/Array to Blade `map.index`.
8. Blade renders Leaflet map. JS iterates over array, placing markers. Highest score gets distinct marker color/icon.

## 4. Database Schema Guidelines
- `criteria`: `id`, `name`, `type` (cost/benefit).
- `alternatives`: `id`, `name`, `latitude` (decimal), `longitude` (decimal), `description`.
- `alternative_scores`: `id`, `alternative_id`, `criterion_id`, `score` (decimal).
