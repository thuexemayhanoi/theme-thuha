# wp-theme
https://rentbikehanoi.com

> **Deployment status (23 Oct 2026, Asia/Ho_Chi_Minh):** production runs theme **1.3.0** in `wp-theme` and the custom plugin
> `rentbikehanoi-native-vi` **1.0.0**. The owner installed both ZIPs; an authenticated administrator then published
> the 11 Vietnamese core pages and enabled `/vi/`. See [the live deployment record](docs/vi/DEPLOYMENT-2026-10-23.md)
> for page IDs, measured checks, remaining limitations and rollback. Do not reseed or deploy over these pages blindly.

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
| Changes | [CHANGELOG.md](CHANGELOG.md) |

### Test and package

```bash
composer install                       # dev tools only (PHPCS, WPCS, PHPCompatibilityWP)
scripts/test-all.sh                    # static checks
RBH_WP_ROOT=/tmp/rbh-wp RBH_BASELINE_REF=main scripts/test-all.sh --integration --bootstrap   # local WordPress + Yoast + browser
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
