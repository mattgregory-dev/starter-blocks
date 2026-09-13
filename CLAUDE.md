# Starter Blocks — conventions

Read this before working in this repo. Starter Blocks is a Full Site Editing
starter theme meant to be cloned and reseeded per project (see
[docs/PIPELINE.md](docs/PIPELINE.md), Stage 0). The conventions below are what a
clone inherits and must not drift from.

## Permanent, shared identifiers

These are shared across **every** cloned project and are **append-only** — never
rename or repurpose an existing one; only add:

| Concern | Value |
|---|---|
| Block namespace | `starter-blocks/*` |
| Generated block class | `wp-block-starter-blocks-*` |
| PHP function prefix | `sb_*` |
| Text domain / `@package` | `starter-blocks` |
| Pattern category | slug `starter-blocks`, label "Starter Blocks" |

A rename silently breaks downstream references (inserted patterns forked into the
DB, inline classes, translations), which is why the rule is absolute.

## Design tokens

The `theme.json` palette is **role-based, not descriptive** — slugs name what a
color does, not what it is. A clone changes the *values*, never the *slugs*.

`base`, `contrast`, `contrast-2`, `surface-1`, `surface-2`, `surface-3`,
`surface-dark`, `border`, `muted`, `link`, `link-hover`, `error`.

- **Append-only**, same as the identifiers above.
- **Surfaces are luminance-ordered, lightest first** (`surface-1` lightest of the
  light set → `surface-3` darkest of it; `surface-dark` is the dark surface).
  New surfaces slot into that order.
- **Markup references tokens only.** All pattern/block markup uses preset slugs
  and `var(--wp--preset--*)` values — no literal hex, no raw px. This holds even
  in throwaway prototype markup, because prototype markup becomes production
  markup in this pipeline.

The font families are roles too (a serif heading stack, a sans body stack); swap
values, keep roles.

## Build & styling

- **Two pipelines, both git-ignored outputs:** Vite (`src/` → `dist/`) and
  `@wordpress/scripts` (`blocks/` → `build/`). WordPress registers blocks from
  `build/`, so **editing `blocks/` does nothing until `npm run build:blocks`.**
- **Styling priority:** `theme.json` → block markup/attributes → block style
  variations (`is-style-*`) → `src/style.scss` (escape hatch only: pseudo-
  elements, `:has()`, keyframes, JS-state classes). The SCSS layer always
  references tokens and never redefines one.
- **A SCSS partial rides with its feature.** New block/variation/pattern → its
  stylesheet and a matching `@use` line land in the same change.
- Run `npm run lint` (eslint + stylelint + phpcs + block-grammar audit) before
  considering a change done.

## Blocks vs. patterns

A custom block is justified by **propagation, logic, or controls** — many
instances sharing a centrally-updated structure, `render.php` computation/
sanitization, or typed editor controls beyond core + block bindings. Breakable
structure **alone** is a pattern (use `templateLock` / block bindings for a safe
editing surface). See [docs/PIPELINE.md](docs/PIPELINE.md), Stage 4.

## Commit discipline

- **Conventional Commits, spec types only:** `feat`, `fix`, `refactor`, `chore`,
  `build`, `ci`, `perf`, `docs`. One idea per commit; the history is part of the
  deliverable.
- Commit **bodies only when the diff can't tell the story alone** (a non-obvious
  why or a gotcha) — no filler bodies.
- **No em dashes in marketing prose.** Docs, docblocks, and pattern descriptions
  may use them freely; visitor-facing copy may not.

## Editing page content

Templates, patterns and blocks are in git. **Page content is not** — it lives in
`post_content`, edited in the block editor, and nothing sends it back to the
repo.

**Anything writing page content programmatically goes through
`scripts/sb-pull.php` and `scripts/sb-push.php`. Never a raw `wp post update` or
`wp_update_post`.** Same transport, but the scripts add guards the raw commands
skip:

- **Stale-push guard.** The pull records a baseline hash; the push refuses to
  write if the database has changed since. **There is no override flag, on
  purpose** — re-pull, reconcile, then push. This is what stops a stale write
  clobbering whatever was edited in the block editor meanwhile.
- **Fidelity.** The push runs as an administrator with `unfiltered_html` and
  slashes the content. Raw WP-CLI runs as user 0 with kses active, which silently
  strips `<iframe>`, `<script>` and inline SVG — and unslashed input eats the
  backslashes in escaped block attributes, so `sb-trust` becomes
  `su002dtrust` and the editor then reports invalid content.
- **Backup.** Every push copies the current database content to
  `.work/backups/` first.

Run from the **project root**:

```
docker compose run --rm -T wpcli wp eval-file \
  wp-content/themes/starter-blocks/scripts/sb-pull.php <post-id> > wp/.work/<slug>.html

# edit wp/.work/<slug>.html

docker compose run --rm -T wpcli wp eval-file \
  wp-content/themes/starter-blocks/scripts/sb-push.php <slug>
```

**Re-pull immediately before every push — including for a page authored in the
same session.** The database is the only source of truth; someone may be editing
it in the block editor right now.

`wp/.work/` must be group-writable (`chmod 2775`): the `wpcli` container writes
as uid 33, and a directory created by the WSL user is not writable by it.

## Where things live

- Conventions & mental model → this file, then [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md).
- How to build/lint/run → [docs/BUILD.md](docs/BUILD.md).
- When something behaves wrong on a right-looking file → [docs/GOTCHAS.md](docs/GOTCHAS.md).
- The end-to-end project process → [docs/PIPELINE.md](docs/PIPELINE.md).
