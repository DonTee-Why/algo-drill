# AlgoDrill

AlgoDrill is a Socratic coding interview coach. It guides you through a gated problem-solving flow (clarify → approach → brute force → optimize → pseudocode → code) with AI feedback that asks questions and critiques your thinking—never writes code for you. Code execution runs in a sandboxed runner (Piston).

The monorepo is made of three services:

| Service | Stack | Role |
|---------|-------|------|
| `backend-api` | Laravel | Web app, sessions, state machine, API |
| `coach` | FastAPI | Internal LLM critique sidecar |
| `piston` | Piston | Sandboxed code execution |

## Table of Contents

- [AlgoDrill](#algodrill)
  - [Table of Contents](#table-of-contents)
  - [How to Install and Run the Project](#how-to-install-and-run-the-project)
    - [Prerequisites](#prerequisites)
    - [Quick start](#quick-start)
    - [Useful commands](#useful-commands)
    - [Service URLs](#service-urls)

## How to Install and Run the Project

### Prerequisites

- Docker and Docker Compose
- Make

### Quick start

From the repository root:

```bash
# Optional: create .env, start stack, generate app key, migrate
./backend-api-start.sh

# Or start the stack only
make up
```

`make up` will:

1. Create the shared Docker network `algo-drill-network` if it does not exist
2. Start `backend-api` (and wait for frontend assets to build)
3. Start `coach`
4. Start `piston`

Stop everything with:

```bash
make down
```

### Useful commands

```bash
make help                 # List root targets
make logs                 # Follow backend app logs
make logs SERVICE=coach   # Follow coach logs
make restart              # Restart all services
make shell                # Shell into the Laravel app container
make test                 # Run Laravel tests
make migrate              # Run database migrations
make coach-shell          # Shell into the coach container
make coach-logs           # Follow coach logs
```

Laravel-only workflows still work from `backend-api/` (for example `make test`, `make pint`). Prefer root `make up` / `make down` for the full stack.

### Service URLs

| Service | URL |
| --------- | ----- |
| Application | [http://localhost](http://localhost) |
| Coach (local debug) | [http://localhost:8000](http://localhost:8000) |
| Piston | [http://localhost:2000](http://localhost:2000) |
| PostgreSQL | localhost:5432 |
| Redis | localhost:6379 |

The coach is intended as an internal sidecar (`COACH_URL=http://coach:8000` from Laravel). Port `8000` is published for local debugging only.
