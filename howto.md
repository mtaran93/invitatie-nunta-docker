# How to enable auto-deploy on merge to `main`

This repo ships with two new pieces:

- `deploy.sh` — the script that runs on the server to pull latest `main`, rebuild containers, rebuild frontend assets, run migrations, and restart workers.
- `.github/workflows/deploy.yml` — a GitHub Actions workflow that SSHes into the server and runs `deploy.sh` whenever something lands on `main`.

Follow the steps below in order. **Do not push these files to `main` until you've manually verified `deploy.sh` works on the server and the GitHub secrets are in place.**

---

## 1. Commit the files to a feature branch (not main yet)

```bash
git checkout -b ci/auto-deploy
git add deploy.sh .github/workflows/deploy.yml howto.md
git commit -m "Add auto-deploy workflow"
git push -u origin ci/auto-deploy
```

The workflow only runs on push to `main`, so the branch is inert until you merge.

---

## 2. SSH to the server and test `deploy.sh` manually

```bash
ssh user@server
cd ~/invitatie-nunta-docker
git fetch
git checkout ci/auto-deploy
chmod +x deploy.sh
./deploy.sh
```

Watch the output. If anything breaks, fix it now — you're the one running the command, no surprises. Things to verify:

- `docker compose -f docker-compose.yml` is the file prod actually uses (vs. `.local.yml`).
- `npm run build` succeeds inside the app container.
- The site still loads in the browser after the script finishes.

If it fails, edit `deploy.sh` on the feature branch locally, push, `git pull` on the server, retry. **Do not move on until a manual run is green.**

---

## 3. Set up an SSH key dedicated to GitHub Actions

On your laptop (or any machine):

```bash
ssh-keygen -t ed25519 -f ~/deploy_key -C "github-actions-deploy" -N ""
```

- Copy the contents of `~/deploy_key.pub` into the server's `~/.ssh/authorized_keys` for the same user that owns `~/invitatie-nunta-docker`.
- Verify it works:

  ```bash
  ssh -i ~/deploy_key user@server "echo ok"
  ```

  Should print `ok` with no password prompt.

---

## 4. Add the GitHub repository secrets

In the repo on GitHub: **Settings → Secrets and variables → Actions → New repository secret**.

| Secret      | Value                                                                                  |
| ----------- | -------------------------------------------------------------------------------------- |
| `SSH_HOST`  | server IP or hostname                                                                  |
| `SSH_USER`  | server username (owns `~/invitatie-nunta-docker`)                                      |
| `SSH_KEY`   | full contents of `~/deploy_key` private key, including `BEGIN`/`END` lines             |
| `SSH_PORT`  | only if not 22                                                                         |

---

## 5. Test the workflow manually before relying on merges

Open a PR from `ci/auto-deploy` → `main` but **don't merge yet**. Instead, go to **Actions tab → "Deploy to production" → Run workflow → select `ci/auto-deploy` branch → Run**.

This uses the `workflow_dispatch` trigger we added — it runs the full SSH-deploy flow without touching `main`.

Caveat: `deploy.sh` does `git reset --hard origin/main` on the server, so a manual run on the feature branch will still deploy `main`'s code. Two options:

- **Easier:** merge to `main` first (step 6), then use `workflow_dispatch` for future tests.
- **Stricter:** temporarily change `git reset --hard origin/main` to `origin/ci/auto-deploy` on the server, test, then revert.

---

## 6. Merge the PR

At this point:

- The script is proven (step 2).
- The SSH key is proven (step 3 ssh test).
- The secrets exist (step 4).

Merging triggers `on: push` → real auto-deploy. Watch the Actions tab.

---

## 7. Rollback plan (have this ready before merging the first time)

If the first auto-deploy goes sideways, SSH in and run:

```bash
cd ~/invitatie-nunta-docker
git reset --hard <previous-commit-sha>
docker compose -f docker-compose.yml up -d --build
```

Keep that one-liner pasted in another terminal before clicking merge.

---

## Why this order is safe

At every step, the new thing being introduced is the only thing that can fail, and the failure is contained:

- Step 2 fails → script bug; nothing automated yet, edit & retry.
- Step 3 ssh test fails → key/permissions issue; no GitHub involvement yet.
- Step 5 fails → workflow YAML or secrets issue; `main` untouched.
- Step 6 fails → script and SSH path are already proven, so a failure here is most likely transient and reproducible via `workflow_dispatch`.

---

## What `deploy.sh` actually does

```bash
#!/usr/bin/env bash
set -euo pipefail
cd ~/invitatie-nunta-docker

git fetch --all
git reset --hard origin/main

docker compose -f docker-compose.yml build
docker compose -f docker-compose.yml up -d

# Rebuild frontend assets into the public_build named volume.
# Dockerfile already runs npm run build, but the named volume is only
# seeded from the image on first creation; subsequent deploys would
# otherwise serve stale assets. Running it inside the live app
# container writes through to the volume nginx serves.
docker compose -f docker-compose.yml exec -T app npm run build

docker compose -f docker-compose.yml exec -T app php artisan migrate --force
docker compose -f docker-compose.yml exec -T app php artisan optimize
docker compose -f docker-compose.yml exec -T app php artisan queue:restart
```

### `set -euo pipefail`

- `-e` — exit on any non-zero command (no silent half-deploys).
- `-u` — error on undefined variables (catches typos like `$DEPLOY_DIRR`).
- `-o pipefail` — pipelines fail if *any* stage fails, not just the last one.

### `queue:restart` and the workers

`queue:restart` writes a timestamp to the shared file cache. Each running `queue:work` process checks it between jobs and exits gracefully when it sees a newer timestamp. With `restart: unless-stopped` in compose, Docker immediately starts a fresh worker container that loads the new code. Worst-case latency is ~`--timeout=120` seconds before all 4 replicas have cycled — usually faster.

In-flight jobs finish on the *old* code, which is fine unless a deploy includes a breaking change to a job class (renamed method, removed property). For those, drain the queue first or accept that some jobs may fail and rely on `--tries=3` to retry on the new code.
