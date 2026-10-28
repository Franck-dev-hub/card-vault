# Deployment

The prod server pulls its releases: nothing in GitHub can reach it.\
A tag builds the images ([ADR 0008](../adr/0008-tag-as-release-artefact.md)),
promoting it tags them `prod`, and the server deploys what `prod` points to.

```
tag vX.Y --> Build and Push to GHCR --> <service>/prod:vX.Y
                                               |
                  Promote to prod (approval) --+--> <service>/prod:prod
                                                          |
                         server, every 5 minutes: digest changed?
                                                          |
         git checkout vX.Y, compose pull, up -d, /health, Telegram
```

## Promote a release

1. Run the `Promote to prod` workflow from the Actions tab, with the tag
   (`v0.3`).
2. Approve it: the `prod` environment requires a reviewer.
3. Within 5 minutes, Telegram says the release is live, or that it failed.

To roll back, promote the previous tag.\
Migrations do not roll back: a release that changed the schema needs a fix
forward.

## What the server does

`docker/deploy/pull-deploy.sh`, run by launchd every 5 minutes:

1. Reads the digest of `api/prod:prod` on GHCR, anonymously.
2. Stops there, silently, if that digest is already deployed or already failed.
3. Reads the release from the image labels and waits until the `frontend` and
   `ml` images of the same commit exist.
4. Checks out the tag, refuses placeholder secrets in `.env.prod.local`, pulls
   the images pinned to that commit and restarts the stack.
5. Probes `/health` from inside the `api` container for up to 5 minutes, then
   reports to Telegram.

A failed release is not retried: the next promotion replaces it.\
Only tags that contain the script can be deployed this way: the checkout
replaces the script with the tag's version.

## Install on the server

Only once prod replaces the current site (#69): the prod stack publishes ports
80 and 443.

1. Clone the repository into `~/Server/card-vault` and create
   `.env.prod.local` there.
2. Create a Telegram bot with @BotFather, then write its token and the chat id
   in `~/.config/card-vault-deploy/env`, readable by you only (`chmod 600`):

   ```
   TELEGRAM_BOT_TOKEN=<token>
   TELEGRAM_CHAT_ID=<chat id>
   ```

3. Install the agent:

   ```bash
   sed "s|__HOME__|$HOME|g" docker/deploy/fr.card-vault.deploy.plist \
     > ~/Library/LaunchAgents/fr.card-vault.deploy.plist
   ```

   ```bash
   launchctl bootstrap gui/$(id -u) ~/Library/LaunchAgents/fr.card-vault.deploy.plist
   ```

It runs as a LaunchAgent, in the session of the user who runs OrbStack: no
`sudo`.

In the repository settings, create the `prod` environment with a required
reviewer.\
Without it, the first promotion creates the environment unprotected.

## Operate

| Task                        | Command                                                 |
|-----------------------------|---------------------------------------------------------|
| Read the log                | `tail ~/Library/Logs/card-vault-deploy.log`             |
| See the decision, no change | `DRY_RUN=1 bash docker/deploy/pull-deploy.sh`           |
| Retry a failed release      | `rm ~/.local/state/card-vault-deploy/failed`            |
| Run now                     | `launchctl kickstart gui/$(id -u)/fr.card-vault.deploy` |
| Stop                        | `launchctl bootout gui/$(id -u)/fr.card-vault.deploy`   |
