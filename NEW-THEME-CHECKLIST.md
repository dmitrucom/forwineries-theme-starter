# Building theme #6 (and beyond) from this starter

This operationalizes `forwineries-theme-core`'s own plan for Phase 3
("new-theme generator") as a literal, step-by-step checklist. Follow it
in order — later steps assume earlier ones are done. Every "why" below
points at a real bug found building the first 5 themes in this family
(`forwineries-theme-core/docs/LESSONS.md`) — read that file in full at
least once before starting, not just this checklist.

## 0. Clone and rename the repo

- [ ] `git clone https://github.com/dmitrucom/forwineries-theme-starter.git your-winery-theme`
- [ ] `cd your-winery-theme && rm -rf .git && git init` (a fresh history —
      this isn't a fork you want to stay linked to the starter's own commits)
- [ ] Create the new GitHub repo (`gh repo create dmitrucom/your-winery-theme --public --source=. --remote=origin`)
      matching how the other 5 product repos and this starter are hosted.

## 1. Find/replace the placeholder slug

- [ ] Pick a short, unique, lowercase slug (2-4 letters, e.g. `abc` for
      "Alder Bend Cellars") — this becomes `wp_options` keys, cookie
      names, the event CPT slug, nav menu locations, and Elementor
      global-style ids on a LIVE site. **Pick it once, up front** —
      renaming it after a site has real saved settings silently reverts
      them to defaults (see `core/src/Config.php`'s docblock).
- [ ] Project-wide find/replace `newtheme` -> your lowercase slug, and
      `NEWTHEME` -> your uppercase slug, across every file. This covers
      function names, the theme-version constant, CSS classes/ids that
      are genuinely per-theme, the text domain, and config values.
- [ ] Rename the two event-CPT files together, using the SAME new slug:
      `archive-newtheme_event.php` -> `archive-{slug}_event.php`,
      `single-newtheme_event.php` -> `single-{slug}_event.php`. These
      must always match `$config->post_type('event')` — a mismatch
      doesn't fatal, it just silently falls back to `archive.php`/
      `single.php` and loses the event-specific date/RSVP markup, which
      is a much harder bug to notice than an error.

## 2. Fill in `functions.php`'s `$config` array

Every key has an inline one-line comment — read them as you go, not just
this list. In order of what to fill in first:

- [ ] `slug`, `brand_name`, `text_domain`
- [ ] All 26 tokens under `tokens` (9 colors, 3 fonts, 3 radius, 3
      shadow, 2 easing, 5 spacing, 1 container — see
      `forwineries-theme-core/docs/DESIGN-TOKEN-SCHEMA.md` for the
      canonical vocabulary and semantic meaning of each). Replace the
      placeholder greyscale/blue palette entirely — don't just tweak it.
- [ ] `fonts_url` — a real Google Fonts CSS2 embed URL for your 2-3 fonts.
- [ ] **`club.name` — READ THIS BEFORE FILLING IN.** It feeds
      `Commerce7\Integration::render_club_join()`'s default button text,
      which is ALWAYS `"Join the {name}"`. `name` must NEVER include a
      leading "The" — put that only in `eyebrow`. This is a real, live
      regression that shipped on a sibling site (Fault Line Cellars) in
      this exact family: `'name' => 'The Fault Line Collective'` shipped
      a button reading "Join the The Fault Line Collective." See
      `forwineries-theme-core/docs/LESSONS.md`, "club's default
      join-button text has a grammar trap."
- [ ] `events.seed`, `demo`, `contact.fields`
- [ ] **`age_gate.bg_image` — REQUIRED, not optional.** Core's own
      `AgeGate` default points at a file
      (`/assets/images/age-gate-bg.jpg`) that no theme in this family has
      ever actually shipped — leaving this blank means the age gate
      silently renders with NO background photo at all, with no error
      anywhere. This bit 3 of the first 5 themes for real. Point it at a
      real, brand-safety-verified photo (step 5 below) before going live.
- [ ] `pages.home.hero` / `pages.home.reservation` (read by
      `front-page.php` directly) and every `pages.*` image path used by
      core's page templates (estate hero/history/winemaking, visit,
      gift-cards, wines).
- [ ] `menus` — replace with your real nav structure, or leave the
      converged 4-item default if it fits.
- [ ] `elementor_kit.color_names`, `design.color_tokens` /
      `palette_presets` / `font_presets` (optional — presets can stay
      empty until you have real brand alternates worth offering).

## 3. Fill in `inc/content-fields.php`

- [ ] Replace every placeholder default string (`inc/content-fields.php`)
      with real brand-voice copy — homepage sections first (hero, story,
      stats, wines, club, reservation, visit), then the marketing pages
      (estate, team, visit, gift-cards, club, reservation, profile,
      contact, trade-press), then `age_gate_global`.
- [ ] Note the `visit_hours` / `visit_page_hours` pair — two separate
      fields, kept in sync by having identical default text (see the
      comment on `visit_page_hours` in that file for why). If you ever
      change your visiting hours, update **both**.
- [ ] Fill in `team_members`, `club_tiers` repeater rows with real people
      / real tiers.

## 4. Source and verify photography (do not skip this)

- [ ] Read `assets/images/README.md` in full first.
- [ ] For every image slot in `functions.php`'s `pages.*` / `age_gate`
      keys and every `content-fields.php` `image`-type field (team
      photos, etc.): source a real, license-confirmed photo, **open the
      full-resolution file yourself and actually look at it** — zoom into
      corners, labels, signage, screens, anything with visible text or
      logos — then drop it in `assets/images/` and point the config at
      it.
- [ ] 4 of the first 5 themes in this family shipped real third-party
      wine-brand logos or a readable real storefront sign hidden in
      "safe-looking" stock photos, live on public sites, until this was
      caught by accident. Do not trust a filename, a stock site's own
      tagging, or a thumbnail glance. See `forwineries-theme-core/docs/
      LESSONS.md`, "Content & brand safety."
- [ ] Log what's sourced/verified in `THEME-NOTES.md`'s photography table
      as you go, so a half-finished pass is visible, not silently assumed
      complete.

## 5. Pull in the latest shared core

- [ ] From `forwineries-theme-core`, run:
      `tools/sync-core.sh /path/to/your-winery-theme`
      This populates `core/` and `core-manifest.json` in your new repo.
      Do this again any time `forwineries-theme-core` ships a fix you
      want.
- [ ] Add your new theme's path to `forwineries-theme-core/tools/
      sync-targets.txt` (one path per line) so future `sync-core.sh --all`
      runs include it automatically. (The starter template repo itself is
      deliberately NOT on that list — it's not a live site, so it only
      gets synced explicitly, on demand, the way you just did above.)

## 6. Local smoke test — before touching a live site

- [ ] Symlink your new theme into `/Users/dima/wp-test-forwineries/public/wp-content/themes/`.
- [ ] Activate it via `wp-cli.phar` (or wp-admin) and confirm it activates
      with **no fatals**, even before you've filled anything in — that's
      what proves the scaffold itself is sound, independent of your
      content.
- [ ] Re-check after you've filled in `functions.php`/`content-fields.php`:
      visit `/`, `/collection/wines/`, `/estate`, `/visit`, `/team`,
      `/gift-cards`, `/club`, `/contact`, `/trade-press`, `/events`, a
      single event, the Journal index and a single post, `/reservation`,
      and 404 a nonexistent URL.
- [ ] Run **real computed-style checks**, not just a visual glance — a
      screenshot that "looks fine" is not sufficient on its own. At
      minimum, verify via a headless browser or DevTools:
  - `body`'s computed `font-family` matches your real `font_body` token
    (catches a stale parent-theme/font-cascade issue — see LESSONS.md's
    "Hello Elementor parent-theme CSS silently overriding fonts", though
    this shouldn't be possible in a theme that was always standalone).
  - `body`'s (and `#c7-content`'s, once Commerce7 is connected) computed
    `background-color` matches your real `cream` token — a silent
    white-background regression is invisible in a quick screenshot glance
    but fails a real check (this hit the one dark-palette theme in this
    family for real, live, post-deploy).
  - **`.fw-hero-content`'s computed `opacity`/`is-visible` state** on
    every page that has one — this is the single most severe bug found
    in this entire project: a stale JS selector left hero text
    permanently invisible on 3 live pages, undetected through two rounds
    of AI "looks fine" verification, until the user insisted on a deeper
    look. See `forwineries-theme-core/docs/LESSONS.md`'s "Vespera's
    hero/header/stat-count JS never matched real markup" entry in full —
    do not treat this as paranoia; it happened for real, twice-checked
    and still missed. If you ever rename a class this theme's own
    `assets/js/theme.js` targets, grep that file for the OLD name in the
    same commit — there is no scheduled "verification pass" that reliably
    catches this after the fact.
- [ ] Fix anything that fatals or renders visibly broken before proceeding.

## 7. Connect deploy and go live

- [ ] Connect Hostinger auto-deploy the same way as the existing 5
      product themes (git push to `main`).
- [ ] Push. Then verify the **live** site the same way as step 6 — a
      local pass does not prove the live pass; real font loading, real
      Commerce7 tenant data, and real caching-header effects only show up
      against the actual deploy. See LESSONS.md's "Deploy verification &
      caching" section for the two independent caching layers to be aware
      of (Cloudflare + a server-level page cache) before assuming a fix
      that "looks" undeployed is actually broken.
- [ ] Fill in the rest of `THEME-NOTES.md` (Commerce7 tenant setup, deploy
      URLs, anything fixed for this site specifically) as you go.
