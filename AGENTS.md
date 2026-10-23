# Theme maintenance — rentbikehanoi.com

## Purpose of this repository
Source of the active WordPress theme of https://rentbikehanoi.com (theme "Rentbike Hanoi", active stylesheet folder `wp-theme`) and, since 1.3.0, of the project's own bilingual plugin `plugins/rentbikehanoi-native-vi/`. English stays at the site root; native Vietnamese lives under `/vi/`. Production status is recorded in dated publication records in `docs/`; see `docs/vi/DEPLOYMENT-2026-10-23.md` for the live theme 1.3.0, plugin 1.0.0, published page IDs and enabled Vietnamese section.

## Current live bilingual state — 23 October 2026
- Mobile utility-bar wrapping was corrected using core Additional CSS for `wp-theme` (post 3484); preserve this setting. Source and verification limits: `docs/vi/UTILITY-MOBILE-2026-10-23.md`. This is not included in the theme ZIP and has not been tested on physical iPhone Safari.
- Latest authenticated verification: theme `wp-theme` **1.4.0**, native VI plugin **1.1.1**, both active after the owner's ZIP installation.
  See `docs/vi/VERIFICATION-1.4.0-1.1.1-2026-10-23.md`. The earlier deployment record is historical, not the current installed version.
  Old guest HTML survived the upgrade in LiteSpeed; a native cache purge restored the landing, images and VI logo destination.
  Eleven VI pages, two sampled category archives, search, 404 and the VI/EN, dark-mode and FAQ interactions were checked live.
  Sitemap retrieval and live mobile verification are not claimed. The owner subsequently confirmed 20,000 VND/hour;
  more than 6 hours is charged as one rental day. Six EN page/post bodies were corrected and read back; the VI rate already matched.
- The 11 Vietnamese core pages already exist and are published (IDs 3454–3464); `/vi/` is enabled. Do not recreate, overwrite or republish seed content over owner edits.
- Nine English–Vietnamese pairs were confirmed. The Vietnamese terms page is deliberately unpaired because the English terms cover a broader rental-service scope. The empty Vietnamese blog is noindex and excluded from the sitemap.
- Legacy GTranslate was deactivated; use only the custom bilingual plugin for the language section. Yoast and LiteSpeed remain in place.
- The owner's authenticated admin-profile language was changed to Vietnamese; the public English site's default language was not changed.
- Claude must still be assumed to have GitHub-only access to the two project repos, not WordPress or a reliable crawler. The live publishing work was done separately by an authorized administrator. `/thuha-writer` was not modified in this deployment.
- Follow `docs/vi/ROLLBACK.md` before deactivation. Never substitute a raw `rbhvi_enabled` option write for the plugin's preflight/enable action.

## Installed release — theme 1.4.0 + plugin 1.1.1
- Purpose: `/vi/` uses the English homepage's layout (hero, quick links, bike cards, price table, steps, FAQ, collapsible saved content). Details and install steps: docs/vi/UPDATE-1.4.0-1.1.1.md. Plugin 1.1.0 was never installed and is superseded by 1.1.1.
- Theme part `template-parts/landing.php` is presentation only and is used **only** by the plugin for the managed Vietnamese home page (registry key `home`, production ID 3454). `front-page.php`, `inc/content.php`, CSS and JS must stay identical to the deployed release unless the owner asks for an English change; `tests/static/repo-check.mjs` enforces this against `e81fbd7`.
- Vietnamese homepage copy lives in `plugins/rentbikehanoi-native-vi/config/home-vi.php` (placeholders only for prices/NAP/fees). Photos are reused read-only from the English homepage's existing media (same file-name tokens as `rbh_home_data()`); never upload or invent images.
- The landing never writes to the database and never replaces `post_content`; the saved page content is rendered in the details area. Setting "Bố cục trang chủ /vi/ → Trang thường" restores the 1.0.0 page layout without code.
- On Vietnamese pages the custom logo links to `/vi/` with a Vietnamese alt, and the widget area "Thanh bên bài viết tiếng Việt (/vi/)" replaces the English "Article sidebar" (empty until an admin adds Vietnamese widgets).
- The plugin's preflight refuses to enable `/vi/` while Polylang, WPML, TranslatePress, GTranslate or Weglot is active. Never re-enable GTranslate or add translation APIs.
- Vietnamese search, 404 and Vietnamese category titles/`og:title`/`twitter:title`/WebPage name/breadcrumbs are set by `RBHVI_SEO` through Yoast's public filters, only on Vietnamese requests (a Yoast title saved for a Vietnamese category wins). Never change the English ones or the frozen Site Name.
- WordPress core strings shown on Vietnamese pages (comment form/list, post and comment navigation) fall back to `includes/core-strings-vi.php` only when no Vietnamese translation is installed. Keep printf placeholders and HTML tags identical (static test); add strings there rather than editing core output.
- Install order does not matter (each package degrades safely without the other), but both are needed for the new layout.

## Existing rules (unchanged)
- Preserve existing posts, menu objects, taxonomy, permalinks, canonicals and redirects. Do not write business content from theme activation hooks.
- Homepage SEO title, description, Open Graph identity and Site Name are frozen. Changing them requires the owner's specific approval. The SEO audit module must not filter them.
- Owner reconfirmed on 23 October 2026: extension fee 20,000 VND/hour; more than 6 hours is charged as one rental day. Do not reinterpret `>6` as `>=6`. Source: the owner's explicit confirmation; native VI config already matches. The corresponding facts are recorded in the same website's `thuha-writer/config/business-facts.json`.
- Yoast remains the sole source of metadata/schema. Extend it with scoped public filters; do not emit a second schema graph.
- Keep assistant searches public, same-origin, bounded and on demand. No whole-site JSON index in initial HTML; no external AI service, booking or payment actions.
- Use the WPVibe draft/preview/publish workflow. Inspect an existing draft before changing it; never activate a -wpvibe-draft theme directly. Publication follows the owner preview/approval and recent-backup check required by the tool workflow.
- Current active stylesheet is wp-theme. Historical migration folder notes do not authorize switching directories.
- Run node --check assets/js/assistant-seo-20261019.js and node tests/assistant.cjs. The WPVibe PHP syntax check and rendered-preview smoke tests are also required.
- Read docs/SEO-2026-10-19.md for measured evidence, remaining checks and rollback.

## Frozen menu and footer labels — owner approval required
- The owner froze the current English navigation labels on 20 October 2026. Preserve their wording, capitalization and punctuation across desktop/mobile menus and the shared footer.
- **Ask the website owner and wait for explicit approval before any label change.** Present the exact old label, proposed new label and unchanged destination URL. General audit, optimization, redesign, article publication or publish permission does not authorize changing these frozen labels.
- This applies to WordPress menu-item titles and to theme/filter/fallback code that would change their rendered text. Keep the approved 2–3-word labels synchronized between menu and footer.
- Preserve destination URLs, slugs, ordering and parent/child structure. Do not automatically rename labels during imports, theme updates, activation, sync or content publishing.
- See docs/NAVIGATION-LABEL-FREEZE.md and docs/navigation-labels-approved-2026-10-20.json. The snapshot is a read-only reference, not a restore/migration script. Report drift; do not overwrite it without the owner's specific approval.
- The `rbh_menu_items` / `rbh_menu_group` filters exist only so the Vietnamese plugin can show a *separate* Vietnamese menu on `/vi/` pages. Never use them to rename, reorder or hide English items.

## Bilingual architecture (English root, Vietnamese /vi/)
- Responsibilities — **theme**: presentation only (header, footer, menus, Light/Dark, cards, CSS, the data-driven `template-parts/landing.php`) plus the hooks `rbh_header_actions`, `rbh_home_url()`, `rbh_menu_items`, `rbh_menu_group`, `rbh_assistant_enabled`, `rbh_language_switch_enabled`. **Plugin** `rentbikehanoi-native-vi`: language detection, `/vi/` routing (core `request` filter, no rewrite flush), pairing, list separation, hreflang, Yoast filters, sitemap exclusions, VI | EN switch, Vietnamese admin screen, draft seeding, ON/OFF/undo. **Yoast SEO**: title, description, canonical, robots, Open Graph, JSON-LD, sitemap.
- No WPML/Polylang/TranslatePress, translation API, external AI, third-party front-end assets, cookies or JavaScript language switching, and no IP/country/browser-language redirects.
- Details: docs/vi/ARCHITECTURE.md, docs/vi/PLUGIN.md, docs/vi/SEO-HREFLANG-SITEMAP.md, docs/vi/PAIRING.md.

## Protected URLs (never change, redirect or canonicalise elsewhere)
`/`, `/about-us/`, `/contact-us/`, `/faq/`, `/privacy-police/`, `/terms-of-use/`, `/motorbike-rental-prices-in-hanoi/`, `/hanoi-scooter-rental/`, `/motorbike-renting-in-hanoi-requirements/`, `/motorbike-rental-hanoi-delivery-and-pickup-guide-complete-service-overview-2025/`, `/blog/` and every category path under it (see docs/navigation-labels-approved-2026-10-20.json), every existing post permalink, sitemaps `sitemap_index.xml`, `*-sitemap.xml`.
Vietnamese URLs managed by the plugin: `/vi/`, `/vi/gioi-thieu/`, `/vi/bang-gia-thue-xe-may-ha-noi/`, `/vi/thue-xe-ga-ha-noi/`, `/vi/thu-tuc-thue-xe-may-ha-noi/`, `/vi/giao-nhan-xe-may-ha-noi/`, `/vi/cau-hoi-thuong-gap/`, `/vi/lien-he/`, `/vi/blog/`, `/vi/chinh-sach-bao-mat/`, `/vi/dieu-khoan-su-dung/`, plus `/vi/{post-slug}/` and `/vi/chuyen-muc/{slug}/`.

## Locked business data (NAP and reference prices)
Single source: `plugins/rentbikehanoi-native-vi/config/business.php` (mirrored for humans in docs/vi/BUSINESS-DATA.md and asserted by tests/static/content-test.php). Name: Thuê xe máy Nguyễn Hà. Address: Ngõ 5 Nguyễn Văn Cừ, Bồ Đề, Long Biên, Hà Nội (gần Bến xe Gia Lâm). Phone/Zalo/WhatsApp: 0334 699 969 / +84 334 699 969. Hours: 09:00–21:00 hằng ngày. Prices per day/week/month: Xe số 150.000–200.000đ / 700.000–800.000đ / 1.000.000–1.200.000đ; Xe ga 150.000–200.000đ / 600.000–1.000.000đ / 1.200.000–2.000.000đ; Xe điện daily price depends on the model (never invent one) / 600.000–800.000đ / 1.500.000–1.800.000đ; Xe 50cc 200.000đ / 1.000.000đ / 2.000.000đ; Honda Vision 200.000đ / 1.000.000đ / 2.000.000đ; Honda Air Blade 200.000đ / 900.000đ / 1.500.000đ.
- Prices are reference prices to be confirmed before booking. Never advertise "no deposit" by default, never guess procedures or deposit-refund policy, never invent reviews, experience, years in business, fleet size, offers, insurance or service promises. Do not change NAP or prices without the owner's approval; do not duplicate them in templates (use the config / placeholders).

## Content rules
- No machine translation. Vietnamese pages are written natively for Vietnamese search intent; English pages stay as they are. Do not edit English content as a side effect of Vietnamese work.
- One URL = one language = one primary search intent. Check `config/pages.php → keyword` and existing posts before adding a page; no doorway pages, thin pages, near-duplicates or repeated AI text; no two URLs targeting the same keyword.
- Exactly one H1 per page (the theme prints the title); FAQ schema only via a Yoast FAQ block whose questions are all visible.
- Vietnamese posts: mark language "Tiếng Việt", use Vietnamese categories, pair only with a truly equivalent English post. See docs/vi/AI-WRITER.md.
- Never publish automatically. Plugin activation and seeding create no published content; publication needs owner preview/approval.

## Build, lint, test, package
- `composer install` (dev tools only), then `scripts/test-all.sh` — runs the AGENTS checks above, repository guardrails, content/business-data tests, WPCS (plugin) and PHPCompatibilityWP 7.4+ (whole repo).
- Full run with a disposable local WordPress + Yoast (127.0.0.1 only): `RBH_WP_ROOT=/tmp/rbh-wp RBH_BASELINE_REF=main,e81fbd7 RBH_DEPLOYED_REF=e81fbd7 scripts/test-all.sh --integration --bootstrap` (needs PHP 8 with pdo_sqlite/gd, Node 22, Playwright Chromium). `RBH_BASELINE_REF` compares English HTML (plugin inactive) with the theme at each ref; `RBH_DEPLOYED_REF` compares English HTML with the plugin ON and Vietnamese content published against the deployed theme+plugin. Update `e81fbd7` when a newer release is recorded as deployed.
- Package: commit first, then `python3 scripts/package.py && python3 tests/static/package-test.py` → `dist/wp-theme-<ver>.zip`, `dist/rentbikehanoi-native-vi-<ver>.zip`, `dist/manifest.json`, `dist/SHA256SUMS`. ZIPs contain only git-tracked runtime files and are reproducible.
- Never weaken or delete a test to make CI green. CI (.github/workflows/ci.yml) only checks and builds; it must never deploy.

## Branches, commits, pull requests
- Work on a feature branch (e.g. `feature/native-vietnamese-section`); small, logical commits; no force-push; never merge into `main` yourself; open Draft PRs that state clearly whether anything was deployed.
- Update README.md, CHANGELOG.md, this file and docs/vi/* whenever architecture, hooks, URLs, business data or the release process changes.

## QA before any release
Follow docs/vi/INSTALL-STAGING.md: recent full backup, staging first, WPVibe preview, owner approval, then production; verify English URLs/titles/canonicals/frozen homepage identity/frozen menu labels unchanged, Vietnamese pages (lang, single title/canonical/description/H1, hreflang pairs, sitemap, FAQ rich result, noindex search), switch next to Light/Dark on mobile and desktop, cache purge.

## Rollback
docs/vi/ROLLBACK.md: (A) "Tắt /vi/" in the plugin screen, (B) undo last transition, (C) deactivate the plugin only after drafting Vietnamese content if it must be hidden, (D) uninstall keeps content, (E) restore theme 1.2.0 (commit 057df19 / WPVibe backup), (F) full hosting backup with owner approval.

## What cannot be verified without the real WordPress
See docs/vi/LIMITATIONS.md (real content/plugins/cache/Yoast configuration, Search Console, real devices, performance on production, legal-page approval, facts taken from the repo that the owner should reconfirm).
