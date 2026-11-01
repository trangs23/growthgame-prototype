# Growth Game — WordPress Playground blueprint

Browser demo of the Growth Game test site (Tutor LMS + WooCommerce + GamiPress).
**Not** the live site growthgame.co.

## After pushing these files to `trangs23/growthgame-prototype` under `playground/`

Open:

```
https://playground.wordpress.net/?blueprint-url=https://raw.githubusercontent.com/trangs23/growthgame-prototype/main/playground/blueprint.json
```

Files required next to `blueprint.json`:

- `gg-seed.zip` — seed plugin + sample PDF/logo
- `growthgame-theme.zip` — child theme (Twenty Twenty-Five) with CI colors

`blueprint.json` uses raw GitHub URLs for those zips.
`blueprint.local.json` uses bundled paths for the local CLI (`scripts/start-local.sh`).

## Demo logins (Playground public blueprint)

See `/workspace/growthgame-wp/CREDENTIALS.local.txt` (Playground demo section).
Do not use these on the live site.

## Local server on the box

```bash
/workspace/growthgame-wp/scripts/start-local.sh
# → http://127.0.0.1:9400
```

Uses `@wp-playground/cli` (PHP.wasm + SQLite). No Docker/MySQL required.
