# Frontend architecture

## Project structure

Not implemented yet. The intended layout under `apps/frontend/src/app/`:

- `core/`: cross-cutting concerns (HTTP client, auth guard, environment).
- `features/`: feature modules, one lazy-loaded route per module.
- `shared/`: reusable UI components and services.

## State and data management

Not implemented yet. The intended approach is injectable Angular services for
all API calls, with RxJS for reactive state. No NgRx planned.

## Routing and security

Not implemented yet. Intended: one lazy-loaded route per feature module,
protected by an auth guard that checks the session.

## API communication

Not implemented yet. Intended: a central HTTP service injected everywhere,
configured with credentials (`withCredentials`) so the session cookie is sent
with every request.

## Design system

Not decided yet.
