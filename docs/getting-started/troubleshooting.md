# FAQ and troubleshooting

## Environment

### `make env` fails or `.env.local` is missing

`.env.local` is generated once and gitignored. Delete it and re-run `make env`,
which regenerates secrets automatically.

### The ML models are not downloading

The ML service needs `HF_TOKEN` on first run. Fill it in `.env.local`
(found in your Hugging Face account settings) and restart the stack:
`make dev/build/ml`. The model cache lives in the `ml_hf_cache` volume.

### Ports already in use

The dev stack exposes ports 80, 5432, 6379, 5050, 5540, 8025, 1025. If one is
taken, stop the conflicting service or change the host port in
`docker/compose.dev.yaml`.

## Inside the app

### The app responds but the frontend shows errors

The API, ML service and database must all be healthy. Check with
`docker compose ps`, then look at service logs with
`docker compose logs -f <service>`.

### A search returns nothing for one game

Check the licence's upstream API status (TCGdex, Scryfall). Card data is
fetched live, so an upstream outage surfaces as an empty or erroring search.

## Development

### `make ci` fails while lint/tests pass individually

`make ci` runs lint plus security audits plus all three test suites. The
security steps (`pnpm audit`, `composer audit`, `pip-audit`) find issues that
lint and tests do not. Read the failing step's output and fix the dependency
or the code it flags.

### phpstan/php-cs-fixer are slow in the CI image

They run inside the container on every invocation. That is expected; use
`make lint/backend` only when you change PHP code.

## See also

- [Installation](installation.md)
- [Configuration](configuration.md)
- [Contributing guidelines](../contributing/guidelines.md)