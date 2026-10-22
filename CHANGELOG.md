# Changelog

All notable changes to this repository. Dates use Asia/Ho_Chi_Minh.

## [Theme 1.3.0 + plugin rentbikehanoi-native-vi 1.0.0] — 2026-10-22 — not deployed

Status: code, tests and release candidates only. **Not installed, activated or published on https://rentbikehanoi.com.**

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
