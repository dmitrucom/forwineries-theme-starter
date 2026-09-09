# Photography goes here — and it MUST be visually verified

This directory ships empty on purpose. No stock photo in this starter
theme has been individually checked, because **no stock photo ships with
this starter theme at all.**

## Why this matters (read this before adding any image)

While building this theme family, 4 of the 5 original product themes
shipped stock photos with real, identifiable third-party wine-brand
bottle labels — one an actual "19 Crimes" lineup — and one with a
readable real "Whole Foods" storefront sign, hiding behind innocuous
filenames (`story-wine-pour.jpg`, `flagship-cellar-rack.jpg`,
`tasting-room-lineup.jpg`, a blog-fallback image). These were live, in
prominent homepage placements, on real public demo sites, until an
unrelated task happened to prompt actually looking at the photos
directly — not assuming a filename or stock-source label meant a photo
was safe. See `forwineries-theme-core/docs/LESSONS.md`, "Content & brand
safety", for the full account.

**The filename, the stock site's own tagging, and even a quick thumbnail
glance are not reliable signals.** A bottle label, a storefront sign, or
a piece of readable text can be small, blurred, or off in a corner and
still be a real trademark violation and a real problem for a client
site.

## The rule for this theme (and every theme built from it)

Before pointing any `functions.php` config key, `content-fields.php`
default, or template `background-image:url()` at a real photo file:

1. Source it from a license you've actually confirmed allows commercial
   use (a stock site's own license page, not just "free-looking").
2. **Open the full-resolution image yourself and actually look at it** —
   zoom into corners, labels, signage, screens, and anything with visible
   text or logos. Do this for every photo individually; a sibling theme
   (or a previous photo from the same shoot) having already used a
   similar-looking photo is not a reason to skip this for a new one.
3. Only then drop it in this directory and point a config key at it.

Until a real photo is verified and added, every image slot in this
theme's templates renders as an obvious, labeled CSS placeholder (the
`.ph-img`/`.ph-tag` system in `style.css`, section 3) — a plain gradient
box with a corner label describing what to source (e.g.
"vineyard-at-golden-hour — 1920x1200"). That's deliberate: it's a much
safer default than a real photo nobody has actually looked at, and it
makes every remaining TODO trivially visible on every page.

See `NEW-THEME-CHECKLIST.md` for the full "sourcing photography" step —
it's a required, explicit checklist item, not an afterthought.
