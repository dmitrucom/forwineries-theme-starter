# Theme notes — [Your Winery Name]

Working notes for this specific theme install. Every sibling product
theme in this family (Larkhaven, Fault Line, Vespera, Albariza,
Heronrest) keeps one of these — Commerce7 tenant setup, per-site
quirks, and anything fixed by hand for THIS site specifically belong
here, not in `forwineries-theme-core` (which only ever holds things
true for every theme built on the shared core).

Delete this template content and replace it with your own notes as you
go. Suggested sections below — keep, drop, or add to them as needed.

## Commerce7 setup

- Tenant ID: `TODO`
- App ID / Secret Key: `TODO` (only needed for the REST-backed widgets —
  the filterable Wines Catalog, the homepage Featured Wines teaser, the
  1Pixel integration; the basic `[c7_*]` shortcode widgets only need the
  Tenant ID)
- Reservation type slug (if using the live availability calendar
  widget): `TODO`
- Default club slug: `TODO`

## Manual Commerce7 admin steps

Some things live in Commerce7 tenant data, which this theme has no
admin-side write access to — track anything that needs a one-time manual
step in the Commerce7 admin here (e.g. product photography, club tier
copy, checkout branding) so it doesn't get lost between sessions.

- TODO

## Fixed for this site specifically

Anything patched by hand for this one theme install, beyond what
`forwineries-theme-core` already provides — a real per-site quirk, not
a bug worth reporting upstream. If the same issue turns out to affect
every theme built on this core, it belongs in
`forwineries-theme-core/docs/LESSONS.md` instead, so the fix gets baked
into the shared core rather than re-discovered per theme.

- TODO

## Photography sourcing log

Track what's been sourced/verified vs. still a placeholder — see
`assets/images/README.md` for why this step can't be skipped or
rubber-stamped.

| Slot | Status | Source | Verified? |
|---|---|---|---|
| Homepage hero | placeholder | — | — |
| Homepage reservation band | placeholder | — | — |
| Estate hero / history / winemaking | placeholder | — | — |
| Visit hero | placeholder | — | — |
| Gift cards hero | placeholder | — | — |
| Wines page hero | placeholder | — | — |
| Age gate background | placeholder | — | — |
| Team member photos | placeholder | — | — |

## Deploy / hosting

- Repo: `TODO` (e.g. `https://github.com/dmitrucom/TODO-theme`)
- Live URL: `TODO`
- Hostinger auto-deploy connected: `TODO` (yes/no — see
  `NEW-THEME-CHECKLIST.md`)
- `core-manifest.json` last synced from forwineries-theme-core commit:
  `TODO`
