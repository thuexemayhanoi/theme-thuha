# wp-theme
https://rentbikehanoi.com

Mobile utility-bar correction applied through WordPress Additional CSS (no reinstall):
[production setting and verification limits](docs/vi/UTILITY-MOBILE-2026-10-23.md).

> **Deployment status (23 Oct 2026, Asia/Ho_Chi_Minh):** the owner installed theme **1.4.0** in `wp-theme` and the custom plugin
> `rentbikehanoi-native-vi` **1.1.1**. Both are active, confirmed through the authenticated WordPress connection.
> The 11 published Vietnamese core pages remain enabled. The old LiteSpeed HTML cache was purged; the new Vietnamese landing,
> images, logo destination, language switch, dark mode and FAQ were checked live.
> See [the current verification record](docs/vi/VERIFICATION-1.4.0-1.1.1-2026-10-23.md) for results and open items.
> The [initial deployment record](docs/vi/DEPLOYMENT-2026-10-23.md) preserves page IDs and the original 1.3.0/1.0.0 installation history.
> Do not reseed or deploy over these pages blindly.
>
> **Installed update:** theme **1.4.0** + plugin **1.1.1** on branch `feature/native-vietnamese-section` give `/vi/`
> the same homepage layout as the English homepage and fix the logo link/alt, sidebar widgets, search/404/category titles and
> breadcrumbs, and the comment form on Vietnamese pages.
> Install guide (Vietnamese): [docs/vi/UPDATE-1.4.0-1.1.1.md](docs/vi/UPDATE-1.4.0-1.1.1.md). Packages in `dist/`.

## Vietnamese home page layout (theme 1.4.0, plugin 1.1.1 — installed)

- Owner indexing preference (23 October): published Vietnamese articles should be eligible for indexing immediately on publication, without waiting for a batch or an English counterpart (`sitemap_pairs_only=false`). Preserve index eligibility for temporarily empty VI categories; do not apply the audit's proposed blanket noindex. The current plugin's empty `/vi/blog/` noindex condition ends when a first public VI post exists. Google decides whether and when to index; publishing alone is not proof of indexing. External writers must assign the native VI language and categories.

- Owner-confirmed extension fee (23 October): **20,000 VND/hour; more than 6 hours is charged as one rental day**.
  English content has been aligned with the Vietnamese config; no reinstall is needed for that content correction.

- `/vi/` (page 3454 in production) renders with the English homepage's design: hero with the existing hero photo, quick links,
  three bike cards reusing the English homepage's bike photos, reference-price table, three steps, short FAQ, and the saved
  page content in the collapsible "details" area (as the English homepage does). Copy is native Vietnamese
  (`plugins/rentbikehanoi-native-vi/config/home-vi.php`); prices/NAP come from `config/business.php`.
- Theme: new presentation-only part `template-parts/landing.php` (same classes as `front-page.php`); `front-page.php`, CSS and JS
  are unchanged. Plugin: `RBHVI_Home` loads it only for the managed `/vi/` home page; setting "Bố cục trang chủ /vi/" switches back.
- Also fixed on all Vietnamese pages: custom logo links to `/vi/` with a Vietnamese alt; a separate Vietnamese widget area replaces
  the English "Article sidebar" widgets; the mobile hours/phone utility bar is shown as on English pages.
- Plugin 1.1.1: Vietnamese titles, `og:title`, `twitter:title`, schema name and breadcrumbs for `/vi/` search, 404 and category
  archives (Yoast's stored defaults are English); Vietnamese fallback for the WordPress core comment form and navigation labels when no Vietnamese
  language pack is installed.
- No database writes, no change to saved content, IDs, URLs, pairs, canonical, hreflang, sitemap or schema.

## English + Vietnamese (theme 1.3.0, plugin 1.0.0)

- English keeps every current URL at the root; native Vietnamese lives under `/vi/` (11 core pages, written in Vietnamese, created as drafts).
- `plugins/rentbikehanoi-native-vi/` — the project's own bilingual plugin (no WPML/Polylang/TranslatePress): `/vi/` routing, pairing, hreflang, Yoast integration, sitemap exclusions, VI | EN switch next to Light/Dark, Vietnamese admin screen with preflight, ON/OFF/undo.
- Theme changes are presentation hooks only; Yoast stays the single SEO/schema owner.
- Locked NAP and reference prices: `plugins/rentbikehanoi-native-vi/config/business.php`.

| Topic | Document |
| --- | --- |
| Rules for agents | [AGENTS.md](AGENTS.md) |
| Current production state | [docs/vi/DEPLOYMENT-2026-10-23.md](docs/vi/DEPLOYMENT-2026-10-23.md) |
| Architecture | [docs/vi/ARCHITECTURE.md](docs/vi/ARCHITECTURE.md) |
| Plugin | [docs/vi/PLUGIN.md](docs/vi/PLUGIN.md) |
| Install, staging, production checks | [docs/vi/INSTALL-STAGING.md](docs/vi/INSTALL-STAGING.md) |
| Rollback | [docs/vi/ROLLBACK.md](docs/vi/ROLLBACK.md) |
| SEO, canonical, hreflang, sitemap | [docs/vi/SEO-HREFLANG-SITEMAP.md](docs/vi/SEO-HREFLANG-SITEMAP.md) |
| Pairing English–Vietnamese content | [docs/vi/PAIRING.md](docs/vi/PAIRING.md) |
| Guide for the AI Writer | [docs/vi/AI-WRITER.md](docs/vi/AI-WRITER.md) |
| Locked business data | [docs/vi/BUSINESS-DATA.md](docs/vi/BUSINESS-DATA.md) |
| Unverified items and limits | [docs/vi/LIMITATIONS.md](docs/vi/LIMITATIONS.md) |
| QA report of this release candidate | [dist/QA-REPORT.md](dist/QA-REPORT.md) |
| Update to theme 1.4.0 + plugin 1.1.1 | [docs/vi/UPDATE-1.4.0-1.1.1.md](docs/vi/UPDATE-1.4.0-1.1.1.md) |
| Changes | [CHANGELOG.md](CHANGELOG.md) |

### Test and package

```bash
composer install                       # dev tools only (PHPCS, WPCS, PHPCompatibilityWP)
scripts/test-all.sh                    # static checks
RBH_WP_ROOT=/tmp/rbh-wp RBH_BASELINE_REF=main,e81fbd7 RBH_DEPLOYED_REF=e81fbd7 \
  scripts/test-all.sh --integration --bootstrap   # local WordPress + Yoast + browser; English compared with 1.2.0 and the deployed 1.3.0
python3 scripts/package.py && python3 tests/static/package-test.py                          # dist/ ZIPs, manifest, SHA256SUMS
```

The integration tests build a disposable WordPress on 127.0.0.1; they never contact the live site.

## Live-theme preservation baseline

The complete active **Rentbike Hanoi 1.2.0** theme was imported from `rentbike-hanoi` on 18 October 2026: 40 source files, with no redesign, fixes, feature removal or production writes.

- Source-only baseline commit: `aa0f90cec69b52845de86ccfd5a3e854e7f5f32f`.
- Baseline branch: `baseline/live-2026-10-18`.
- Theme files are at the repository root. The WordPress installation directory must remain **`rentbike-hanoi`**, not `wp-theme`.
- [Migration scope, dependencies and deferred work](docs/baseline/MIGRATION.md).
- [Source file inventory and hashes](docs/baseline/theme-manifest.json).
- [Reference-only WordPress configuration](docs/baseline/site-presentation.json).
- [Additional CSS snapshot](docs/baseline/additional-css.css).
- [Existing SEO Engine 06 snippet snapshot](docs/baseline/seo06-snippet-2962.json).

These snapshots are documentation, not automatic import or activation code. This repository is **not** a database, media, plugin or whole-site backup. Existing WordPress content, images, menus, Yoast metadata and plugin settings remain on the server.

No deploy workflow or webhook was configured by this migration. Do not enable automatic production deployment before selecting the deploy plugin, reviewing its target-folder and failure behavior, and testing rollback.

## SEO update, published 20 October 2026

See [SEO implementation and verification](docs/SEO-2026-10-19.md). The current active stylesheet is `wp-theme`; the earlier migration folder note above is historical. Do not change the active theme folder during this update. Published to the existing wp-theme stylesheet on 20 October 2026 after owner approval and confirmation of weekly hosting backups. Preserve frozen homepage SEO identity. See the implementation report for post-publication checks and remaining follow-up.
