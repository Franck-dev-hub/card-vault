# FAQ and troubleshooting

## Environment

### `castor setup:env` fails or `.env.local` is missing

`.env.local` is generated once and gitignored.\
Delete it and re-run `castor setup:env`, which regenerates secrets automatically.

### The ML models are not downloading

The ML service needs `HF_TOKEN` on first run.\
Copy it from your Hugging Face account settings into `.env.local`, then rebuild the service with `castor docker:build --service=ml`.\
The model cache lives in the `ml_hf_cache` volume.

### Ports already in use

The dev stack exposes ports 80, 5432, 6379 (redis-cache), 6380 (redis-session), 5050, 5540, 8025, 1025.\
If one is taken, stop the conflicting service or change the host port in `docker/compose.dev.yaml`.

## Inside the app

### The app responds but the frontend shows errors

The API, ML service and database must all be healthy.\
The compose files live in `docker/`, so pass them explicitly:

```bash
docker compose -f docker/compose.yaml -f docker/compose.dev.yaml \
  --env-file .env --env-file .env.local ps
```

Then read a service's logs with the same flags and `logs -f <service>`.

### A search returns nothing for one game

Check whether the upstream API (TCGdex, Scryfall) is up.\
Card data is fetched live, so an upstream outage shows up as an empty or failing search.

## Development

### `castor ci` fails while lint/tests pass individually

`castor ci` runs lint plus security audits plus all three test suites.\
The security steps (`pnpm audit`, `composer audit`, `pip-audit`) find issues that lint and tests do not.\
Read the failing step's output and fix the dependency or the code it flags.

### `castor` lists no task

Castor is older than 1.7, the minimum `castor.php` accepts.\
Upgrade it.

### `castor lint:backend` fails with "service api is not running"

App tasks run inside the dev containers.\
Start the stack with `castor up`.

## See also

- [Installation](installation.md)
- [Configuration](configuration.md)
- [Contributing guidelines](../../CONTRIBUTING.md)
