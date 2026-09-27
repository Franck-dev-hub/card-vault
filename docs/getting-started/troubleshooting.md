# FAQ and troubleshooting

## Environment

### `castor setup:env` fails or `.env.local` is missing

`.env.local` is generated once and gitignored.\
Delete it and re-run `castor setup:env`, which regenerates secrets
automatically.

### The ML index is not downloading

The ML service needs `HF_TOKEN` on first run: the index sits in a private
Hugging Face dataset.\
Copy it from your Hugging Face account settings into `.env.local`, then run
`castor up`.\
The index is cached in `apps/ml/app/models/data_cache/`.

### Ports already in use

If a port of the dev stack is taken, stop the conflicting service or change its
host port under `ports:` in `docker/compose.dev.yaml`.

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
Card data is fetched live, so an upstream outage shows up as an empty or failing
search.

## Development

### `castor` lists no task

Castor is older than 1.7, the minimum `castor.php` accepts.\
Upgrade it.

### `castor lint:backend` fails with "service api is not running"

App tasks run inside the dev containers.\
Start the stack with `castor up`.
