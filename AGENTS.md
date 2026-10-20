# Theme maintenance — rentbikehanoi.com

- Preserve existing posts, menu objects, taxonomy, permalinks, canonicals and redirects. Do not write business content from theme activation hooks.
- Homepage SEO title, description, Open Graph identity and Site Name are frozen. Changing them requires the owner's specific approval. The SEO audit module must not filter them.
- Owner-confirmed extension fee: 20,000 VND/hour; more than 6 hours is charged as one rental day. Do not reinterpret the threshold. Workflow source: lucynguyen777/wp-writer/config/business-facts.json → extension_policy.
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
