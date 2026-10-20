# Preservation import — 18 October 2026

## Scope and source

Target site: https://rentbikehanoi.com only. This is not the separate thuha.rentbikehanoi.com project.

The active theme reported by WordPress was Rentbike Hanoi, stylesheet/template `rentbike-hanoi`, version 1.2.0. Its complete file listing contained 40 files. All 40 were read from the active theme, without creating or publishing a draft. Numbered UTF-8 export wrappers were removed; application source was not edited.

Included: all templates and template parts; shared styling, color tokens and scripts; Customizer/settings code; light/dark/auto; contextual links, related articles and CTA; contact dock and contact assistant; local deterministic website assistant; existing heading/Yoast/breadcrumb/schema integration behavior. Existing bugs and hard-coded values are preserved.

The earlier assistant ZIP was a partial source/evidence bundle, not the complete theme. It was used for context, not as a substitute for the live-theme export.

Source-only baseline: `aa0f90cec69b52845de86ccfd5a3e854e7f5f32f`.
Baseline branch: `baseline/live-2026-10-18`.

## Data and dependencies outside the theme

| Component | Preserved location / repository record |
| --- | --- |
| Additional CSS | WordPress custom_css post 1805; full reference in additional-css.css; not enqueued again |
| Logo, menu assignments and theme settings | Existing database; selected snapshot in site-presentation.json |
| Primary menu 23 and footer menu 22 | Existing database; public item snapshots in site-presentation.json |
| Sidebar block 29 | Existing widget option; public markup snapshot in site-presentation.json |
| Homepage content, prices, FAQ, post text and attachments | Existing WordPress database/uploads; theme reads them at runtime; not exported as theme code |
| Yoast canonical, metadata, schema graph and per-post SEO fields | Existing Yoast plugins/database; not duplicated or migrated |
| SEO Engine 06 custom code | Existing WPCode post 2962; full reference snapshot in seo06-snippet-2962.json |
| Contact forms, translation, TOC, cache and other plugins | Remain installed in WordPress; observed inventory in site-presentation.json |

WPCode post status `publish` is recorded but is not a separately verified execution/enablement state. The SEO snippet snapshot is inert JSON and is not required or executed by the theme. Do not move it into functions.php or enable it blindly: duplicate filters or schema may result.

No standalone source component named **SEO Hulk** was identified in the 40 theme files, active-plugin inventory or WPCode titles examined. Existing theme SEO behavior and the identified SEO Engine 06 snippet were preserved; equivalence to a separately named “SEO Hulk” feature remains unverified. Obtain its exact name/source before claiming that distinct component has been migrated.

Plugin source, premium-plugin licenses, secrets, admin credentials, wp-config.php, database dumps, uploads and all-site options were not copied into this public repository. The curated snapshots contain presentation configuration, not a full-site restore package.

## Verification

- Complete active-theme inventory: 40/40 files exported.
- The Git blob hashes of all 40 uploaded source files matched the locally exported source.
- JavaScript syntax checks passed for main.js, upgrade.js and assistant.js.
- theme.json parsed as JSON.
- All declared inc modules and referenced template parts exist.
- A targeted secret-pattern review found no credential literals in the exported theme.
- PHP lint was not run: this local environment has no PHP executable.
- No WordPress activation, production deployment, browser visual/interaction QA, Lighthouse run or independent server-side byte-checksum export was performed.
- No 98/100 score is asserted by this migration.
- Temporary HTTP 429 responses during export were handled by backing off and using slower reads; no reconnection or account-switch workaround was used.

The manifest verifies the exported source stored in Git, not a complete database/media backup. The historical ZIP contains some trailing-newline differences and is not the authority for the live theme.

## Deferred work — no fixes in this import

1. Identify what the owner calls “SEO Hulk” if it denotes a separate component.
2. Review which plugin-owned features can safely become theme-native, individually; no plugin was disabled or removed.
3. Keep Yoast as the SEO owner while reviewing any schema integration changes.
4. Perform actual mobile/tablet/desktop, keyboard, menu, assistant and contact-link QA before production changes; earlier simulation/raw-HTML checks are not visual QA.
5. Audit existing bugs separately and commit each fix after this preservation baseline.
6. Re-measure performance when deployment is tested; do not promise a fixed score.
7. Design CI, releases, authenticated webhook, rollback and deploy-plugin integration only after that plugin is chosen.

## Deployment boundary for the next step

The GitHub repository name is wp-theme. The WordPress theme slug must stay **rentbike-hanoi** so `theme_mods_rentbike-hanoi`, its Additional CSS, menu locations and logo continue to use the same database identity.

Deploy only into `wp-content/themes/rentbike-hanoi/`. Do not deploy the repository into the WordPress root, overwrite wp-content, rename the active stylesheet, modify uploads, replace databases or change posts/URLs/Yoast metadata. Do not activate a wpvibe-draft folder.

The next deploy setup needs a current whole-site/host backup, a tested theme-folder backup, a review of whether the chosen plugin deletes/recreates that folder, failure-safe deployment and rollback, and exclusion of documentation from a distribution ZIP if appropriate. Restoring a theme commit does not restore a database.

No deployment plugin, webhook, scheduled task or GitHub Actions workflow was configured, enabled or invoked by this import.
