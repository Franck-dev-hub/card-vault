# Frontend architecture

Nothing is built yet beyond the Angular shell.\
This page describes the intended design.

## Project structure

Layout under `apps/frontend/src/app/`:

- `core/`: cross-cutting concerns (HTTP client, auth guard, environment).
- `features/`: one folder of standalone components per feature, one lazy-loaded
  route per feature.
- `shared/`: reusable UI components and services.

## State and data management

Injectable Angular services for all API calls, RxJS for reactive state.\
No NgRx planned.

## Routing and security

One lazy-loaded route per feature, protected by an auth guard that checks the
session.

## API communication

A central HTTP service, configured with `withCredentials` so the session cookie
goes with every request.

## Design system

Not decided yet.
