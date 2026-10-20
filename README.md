# ProcessArc

ProcessArc is a Laravel 12 MVP for enterprise process discovery and operational improvement. It includes a public product website, a separate AI technology showcase, a role-aware operations workspace, and a SQL-backed data model.

## Application architecture

```text
Browser
  ├── Public website (Blade, responsive CSS, inline SVG, SEO metadata)
  └── Authenticated operations console (Blade, session authentication)
        ↓
Laravel routes → controllers → validation / authorization
        ↓
Eloquent domain models
  ├── Process ↔ prerequisite processes
  ├── Process → workflow stages
  ├── Process → cycle-time and exception snapshots
  ├── Process → optimization opportunities
  └── User → activity log
        ↓
SQL database (SQLite by default; MySQL supported)

Optional local AI:
Laravel → configured Ollama HTTP endpoint → locally hosted open model (for example Qwen 2.5)
```

### Main modules

- **Marketing site:** product positioning, enterprise use cases, structured SEO metadata, and an application preview.
- **Technology showcase:** proposed NVIDIA NeMo, NIM, RAPIDS and Triton architecture options, with an explicit non-integration/non-endorsement notice.
- **Operations console:** dashboard, process registry and filtering, workflow stages, metric history, improvement tracking, analysis, and activity log.
- **Access and governance:** Laravel session authentication; only `admin` and `manager` roles can create or change operational records. Authenticated users can view records.
- **Persistence:** `users`, `processes`, `process_dependencies`, `workflow_stages`, `process_metrics`, `optimization_opportunities`, and `activity_logs`; process-owned records cascade when a process is deleted.
- **AI extension:** optional local Ollama inference; no hosted AI provider is called by default. Recommendations are reviewable suggestions, not autonomous changes.

## Local setup

Requirements: PHP 8.2+, Composer, Node.js/npm, and SQLite (or MySQL).

```powershell
Copy-Item .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

Open `http://127.0.0.1:8000`. The demo account is:

- **Email:** `demo@processarc.net`
- **Password:** `ProcessArcDemo!2026`

The seeded account is intentionally privileged for local product exploration. Production seeding skips the demo account and sample records unless `PROCESSARC_ENABLE_DEMO=true`; only enable that flag in an isolated demo environment, and change the password before use. The sample workspace contains fictional business-process data; do not enter confidential operational records into the demo.

### Database configuration

SQLite is the default for local development. For MySQL, set `DB_CONNECTION=mysql`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` in `.env`, then run:

```powershell
php artisan migrate --seed
```

### Optional open-model recommendations

Install and run Ollama locally, download an appropriate Qwen 2.5 model, then set the model name in `.env`:

```dotenv
OLLAMA_URL=http://127.0.0.1:11434
OLLAMA_MODEL=qwen2.5:7b
```

The operations dashboard exposes process-specific recommendations to users with the `manager` or `admin` role. If Ollama is unconfigured or unavailable, the application displays an actionable error and does not report a successful result. Review Qwen model and Ollama licenses, hardware requirements, and deployment policies for your chosen version.

## Key routes

- `/` — ProcessArc marketing website
- `/platform/nvidia` — proposed NVIDIA technology showcase
- `/architecture` — application and data architecture
- `/login` — demo login
- `/app` — authenticated operations dashboard
- `/app/processes` — process registry
- `/app/workflows` — workflow overview
- `/app/analysis` — performance and metric analysis
- `/app/optimizations` — optimization workspace
- `/app/activity` — traceable activity history

## Quality checks

```powershell
php artisan test
npm run build
php artisan route:list
```

NVIDIA products shown on the showcase page are architectural options only. This project does not claim a completed NVIDIA integration, certification, endorsement, or partnership.
