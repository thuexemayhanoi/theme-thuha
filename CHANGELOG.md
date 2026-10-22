# Changelog

All notable changes to this repository. Dates use Asia/Ho_Chi_Minh.

## [Plugin rentbikehanoi-native-vi 1.1.1 + theme 1.4.0] — 2026-10-23 — not deployed

Status: code, tests and packages only. Supersedes plugin 1.1.0 (never installed; its ZIP was removed from `dist/`). Theme 1.4.0 is
unchanged (same files, same ZIP checksum). Install from 1.0.0 directly: docs/vi/UPDATE-1.4.0-1.1.1.md.

### Fixed (Vietnamese pages only)
- Search results, 404 and Vietnamese category archives showed the English defaults Yoast stores in its options ("You searched for …",
  "Page not found", "%%term_title%% Archives") in `<title>`, `og:title`, `twitter:title` (search results) and the WebPage schema
  name, and the visible breadcrumb ended with "You searched for …" or "Page N". They are now Vietnamese:
  "Kết quả tìm kiếm cho “…” | <brand>", "Không tìm thấy trang | <brand>", "<category> – Trang N | <brand>"; crumbs
  "Kết quả tìm kiếm: “…”" and "Trang N" (paged search: "Kết quả tìm kiếm: “…” » Trang N"; "Không tìm thấy trang" should a template
  print breadcrumbs on a 404). The search term is escaped exactly as Yoast escapes it in English titles. A title the owner set for a
  Vietnamese category in Yoast is kept. Filters: `wpseo_title`, `wpseo_opengraph_title`, `wpseo_twitter_title`,
  `wpseo_schema_webpage`, `wpseo_breadcrumb_links`, all scoped to Vietnamese requests.
- WordPress core strings on Vietnamese posts (comment form and list, comment/post navigation labels) fall back to Vietnamese
  (`includes/core-strings-vi.php`, filters `gettext_default` / `gettext_with_context_default`) only when no Vietnamese translation is
  installed; an installed language pack always wins.
- English output unchanged: byte comparison with `main` (1.2.0) and the deployed `e81fbd7` (1.3.0/1.0.0) still passes.

### Tests
- Integration: Vietnamese search (incl. page 2)/404/category/paged titles, og:title, twitter:title, schema name and breadcrumbs; a
  search term with markup is escaped as in English and never printed raw; Vietnamese comment form; an installed translation takes
  precedence; English search/404 titles and comment form unchanged. Upgrade test: after the in-place upgrade from the deployed ZIPs,
  `/vi/?s=…` and a `/vi/` 404 switch from English to Vietnamese titles (incl. twitter:title). Static: every entry of the two
  translation maps keeps its printf placeholders and HTML tags.

## [Theme 1.4.0 + plugin rentbikehanoi-native-vi 1.1.0] — 2026-10-23 — not deployed, superseded by plugin 1.1.1

Status: code, tests and packages only. According to the latest record (docs/vi/DEPLOYMENT-2026-10-23.md) production runs theme 1.3.0 + plugin 1.0.0; this was not re-verified (no WordPress access).
Plugin 1.1.1 contains every change below; install guide: docs/vi/UPDATE-1.4.0-1.1.1.md.

### Added
- Theme `template-parts/landing.php`: data-driven copy of the English homepage layout (same markup/classes as `front-page.php`). The English
  homepage does not use it; `front-page.php`, CSS and JS are unchanged.
- Plugin `RBHVI_Home` + `templates/home-vi.php` + `config/home-vi.php`: the managed `/vi/` home page renders in that layout with native
  Vietnamese copy, reference prices/NAP from `config/business.php`, the English homepage's existing hero and bike photos (Vietnamese alt),
  three steps, four FAQ excerpts (no extra FAQ schema) and the saved page content in collapsible sections. No database writes.
- Admin setting "Bố cục trang chủ /vi/" (landing / regular page) for a no-code rollback.
- Widget area "Thanh bên bài viết tiếng Việt (/vi/)".
- Preflight also blocks enabling `/vi/` while GTranslate or Weglot is active (GTranslate was deactivated in production and must stay off).
- Tests: Vietnamese landing structure/data/saved-content checks, logo/brand links, Vietnamese-only interface text and widgets, English output
  compared with both 1.2.0 (`main`) and the deployed 1.3.0/1.0.0 (`e81fbd7`, plugin ON); browser checks at 320–1280 px incl. dark mode;
  static checks for the homepage copy; repo check that English templates/CSS/JS equal the deployed release.

### Fixed (Vietnamese pages only)
- The custom logo linked to the English home page and used the English site name as alt text; it now links to `/vi/` with the Vietnamese brand.
- The English "Article sidebar" widgets (production: a "Latest Posts" heading block) appeared on Vietnamese pages; they are replaced by the
  Vietnamese widget area.
- On phones (≤ 760 px) the dark hours/phone utility bar was missing on Vietnamese pages because 1.0.0 dequeued the theme's
  `assistant.css`, which also holds the mobile utility-bar rules. Only the assistant's script is removed now; the English assistant
  markup is still not rendered on Vietnamese pages.

### Changed
- Theme version 1.3.0 → 1.4.0 (style.css, `RBH_VERSION` asset query strings). Plugin 1.0.0 → 1.1.0.

## [Production deployment] — 2026-10-23

- Owner-installed theme 1.3.0 (`wp-theme`) and custom plugin 1.0.0 verified through authenticated WordPress reads.
- Published 11 existing native Vietnamese drafts, confirmed nine valid English pairs, and enabled `/vi/` through the plugin's guarded admin action.
- Changed the signed-in owner's admin-profile language to Vietnamese without changing the public English locale.
- Deactivated legacy GTranslate; kept Yoast, LiteSpeed and all English content/URLs unchanged.
- Verified public HTTP responses, language metadata, homepage SEO identity preservation, reciprocal switching, sitemap and FAQ schema JSON. See `docs/vi/DEPLOYMENT-2026-10-23.md`; this is not a claim of Google indexing or complete mobile/performance/legal validation.

## [Theme 1.3.0 + plugin rentbikehanoi-native-vi 1.0.0] — 2026-10-22 — not deployed

Historical status on 22 October: code, tests and release candidates only, not deployed at that time. Superseded by the production deployment record above.

### Added
- Plugin `plugins/rentbikehanoi-native-vi/` (custom, no WPML/Polylang/TranslatePress):
  `/vi/` routing via the core `request` filter (no rewrite flush or .htaccess), ON/OFF/undo with preflight and automatic rollback,
  private language taxonomy, English–Vietnamese pairs stored on the Vietnamese side only, language-separated lists
  (archives, search, feeds, widgets, related, previous/next, public REST search, categories), hreflang en/vi/x-default for
  confirmed pairs, Yoast integration through public filters (og:locale, breadcrumbs, paginated listing canonical/title,
  empty-listing noindex, sitemap exclusions), fallback title/description without an SEO plugin, VI | EN switch,
  Vietnamese admin screen, editor meta box, list column, Vietnamese category flag.
- Native Vietnamese draft content for 11 pages (`/vi/` … `/vi/dieu-khoan-su-dung/`), FAQ as a Yoast FAQ block.
- Single business-data config `config/business.php` (NAP, hours, reference prices).
- Tests: local WordPress 7.1.3 + Yoast 28.7 integration suite (52 checks, incl. byte comparison of English HTML with the
  1.2.0 theme), Playwright UI suite (11 checks × text-brand and logo headers), static content/business-data tests (276),
  repository guardrails, package verification; WPCS (plugin) and PHPCompatibilityWP 7.4+ rulesets; CI workflow without deploy.
- Reproducible packaging `scripts/package.py` → `dist/` ZIPs, `manifest.json`, `SHA256SUMS`, QA report.
- Documentation in `docs/vi/`.

### Changed (theme 1.2.0 → 1.3.0, presentation hooks only)
- `header.php`: `do_action( 'rbh_header_actions' )` right after the Light/Dark toggle; search trigger uses `rbh_home_url()`.
- `inc/navigation.php`: `rbh_home_url()`, filters `rbh_menu_items` and `rbh_menu_group` (no label is renamed or saved).
- `functions.php`: version 1.3.0; loads `assets/css/language-switch.css` only while `rbh_language_switch_enabled` is true.
- `inc/seo.php`: `rbh_assistant_enabled` filter around the assistant template.
- `footer.php`: "Call" label translatable; `searchform.php`, `404.php`, `index.php`: home links via `rbh_home_url()`.
- With no language plugin active, English HTML is identical to 1.2.0 except asset `?ver=` strings (tested).

## [Theme 1.2.0] — 2026-10-20
SEO update published to the `wp-theme` stylesheet after owner approval — see docs/SEO-2026-10-19.md and docs/publish-qa-2026-10-20.json.

## [Baseline] — 2026-10-18
Preservation import of the active Rentbike Hanoi 1.2.0 theme — see docs/baseline/MIGRATION.md.
