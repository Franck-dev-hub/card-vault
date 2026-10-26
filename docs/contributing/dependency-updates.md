# Dependency updates

Every lock file update comes with a diff report: the packages it changed.\
The report goes in the same commit as its lock file.

## Report

There is one report per lock file, so each report matches one lock state.

| App      | Lock file                      | Report folder         |
|----------|--------------------------------|-----------------------|
| API      | `apps/api/composer.lock`       | `docs/composer-diff/` |
| Frontend | `apps/frontend/pnpm-lock.yaml` | `docs/pnpm-diff/`     |
| ML       | `apps/ml/uv.lock`              | `docs/uv-diff/`       |

Name it `<YYYY-MM-DD>-<hash>.md`, where the hash is the first 8 characters of
the new lock's SHA-1.\
It holds a date heading, then one table per dependency type:

```markdown
# 2026-10-26

| Prod Packages | Operation | Base    | Target  |
|---------------|-----------|---------|---------|
| symfony/yaml  | Upgraded  | v7.4.20 | v7.4.21 |
```

When no package changed, write no report.\
For example, `composer update --lock` only refreshes the lock's hash.

castor runs each tool in a terminal, which ends every line with `\r`: strip it
with `tr -d '\r'` before writing a file.

## API

[composer-diff](https://github.com/IonBazan/composer-diff) writes the tables.\
The container only sees `apps/`, has no git, and keeps `var/` in a volume, so
the base lock goes in the gitignored `apps/api/composer.lock.base`.

```sh
git show HEAD:apps/api/composer.lock > apps/api/composer.lock.base
castor backend:composer update
date=$(date +%F)
hash=$(sha1sum apps/api/composer.lock | cut -c1-8)
mkdir -p docs/composer-diff
{ echo "# $date"; echo; castor backend:composer diff --no-ansi \
    --base composer.lock.base | tr -d '\r'; } \
    > "docs/composer-diff/$date-$hash.md"
rm apps/api/composer.lock.base
```

If the report holds only its heading, no package changed: delete it.

## Frontend

pnpm has no diff command: compare the direct dependencies before and after.\
`--lockfile-only` reads the lock, not `node_modules`, which can lag behind it.

```sh
before=$(castor frontend:pnpm list --depth 0 --lockfile-only)
castor frontend:pnpm update
diff <(echo "$before") <(castor frontend:pnpm list --depth 0 --lockfile-only)
```

Write each changed package as a row: `dependencies` go in `Prod Packages`,
`devDependencies` in `Dev Packages`.\
Transitive packages stay out: the lock holds hundreds, and their changes follow
the direct ones.

## ML

```sh
castor ml:uv lock --upgrade --dry-run
castor ml:uv lock --upgrade
```

`--dry-run` previews the changes without writing the lock.\
uv prints one line per changed package, such as
`Updated numpy v2.5.3 -> v2.5.4`, or `Added` and `Removed` lines.\
Write each as a row of a single `Packages` table: `uv.lock` mixes runtime, extra
and dev packages.

## Renovate

Renovate opens no pull request (see
[configuration](../getting-started/configuration.md#versions)), so every update
is manual.\
If it ever does, add the report to its branch: its pull request only describes
direct dependencies.
