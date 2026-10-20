# wp-theme
https://rentbikehanoi.com

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
