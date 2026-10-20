Rentbike Hanoi 1.2.0
===================
Independent classic WordPress theme. No parent theme, page builder, remote font,
CDN script, framework, analytics or custom post type is required.

Setup
-----
The original WordPress front page, published content, taxonomies, permalinks,
menus, media and Yoast metadata are never replaced by the theme.
Activation copies valid menu assignments and a custom logo from the previous
theme into this theme only. Known menu slugs are used only on rentbikehanoi.com.
Repeated activation preserves edited settings and does not insert duplicate data.

Homepage
--------
The rental layout reads the existing front-page content, identifies its image,
rental model images, rate table and FAQ table, and presents them in a new layout.
All original homepage sections remain accessible in expandable sections.
The original database content is unchanged and continues to be the source.
If the DOM extension is unavailable, the original page content is shown.
Appearance > Customize > Rentbike Theme > Homepage can switch back to the
original page-content layout without a database migration.

Editing
-------
Appearance > Rentbike Theme links to contact, homepage and appearance settings.
Appearance > Menus controls navigation. Footer links include every primary
menu link with its original label and URL, followed by non-duplicate footer links.
Appearance > Widgets can add optional article-sidebar content.
The mobile contact dock has Call, WhatsApp and Menu. Zalo is in the menu/footer.

Compatibility
-------------
Yoast owns metadata and structured data. This theme does not output canonical,
Open Graph, Twitter or JSON-LD markup. Breadcrumbs use Yoast when available.
WordPress remains responsible for search/404 responses and core robots behavior.
LuckyWP/other detected TOCs are respected; no second TOC is added.
The observed Moto QuickCall wrapper is hidden while the new contact bar is enabled,
so its existing plugin/configuration can be restored simply by changing themes.
Contact Form 7, normal WordPress content filters, wp_head, wp_footer and
wp_body_open are preserved. Theme assets are small and use versioned URLs.
No cache settings are modified. Purge the host/LiteSpeed cache after switching.

Rollback
--------
Activate the previous theme and purge the cache. Original homepage content and
the previous theme's own settings are preserved. Keep a current whole-site backup
before switching themes. rbh_activation_snapshot records prior theme/front-page/
permalink references for diagnostics; it is not a complete database backup.

Scope and verification
----------------------
This theme is prepared from the inspected rentbikehanoi.com content/menu snapshot.
The source includes preconfigured contact facts for that domain only.
Local fixtures are for verification, and must never be uploaded to production.
Deploy through the supported WPVibe draft/preview/publish workflow. Do not activate
a folder ending in -wpvibe-draft directly. Local tests do not replace a live smoke test.

License: GPL-2.0-or-later.

Interaction update
------------------
The shared header/dock menu uses native HTML popover controls in supporting browsers.
The sun/moon control is a real checkbox switch: CSS can change its appearance without
JavaScript. JavaScript adds saved preferences, keyboard focus management and a menu
fallback for older browsers. Test the actual exported preview as well as WordPress.

Shared architecture (1.2)
-------------------------
Appearance > Rentbike Theme > Customize theme is the single place for business
contacts, optional default card photo, CTA labels/destination/context copy,
announcement and feature flags. Banner starts OFF and blank; no business claim is
invented. Existing text typed inside articles remains untouched; settings update
the theme's shared components, not historical prose.

WordPress template hierarchy remains intact: front-page/page/single/index/404;
home/archive/category/search reuse index.php. Header/footer/sidebar/searchform
are conventional shared templates. template-parts contains menu, contact-dock,
cta, banner, context-links, related and card. No build step or extra dependency.

assets/css/tokens.css owns light/dark colors, type, spacing, button and radius
tokens and is loaded by the frontend and block editor. main.css consumes them.

inc/settings.php owns defaults and sanitizers. inc/components.php owns contexts,
related selection and shared renderers. Context mapping uses existing category
IDs resolved from existing slugs; the Customizer can override these mappings.
Specific rental destinations in the existing menu select rental CTAs. A filter
rbh_content_context(context, post_id) supports further presentation-only rules.

Slots (WordPress actions; no output unless a callback is attached):
- rbh_hero(post_id): after the hero/title area.
- rbh_before_content(post_id), rbh_after_content(post_id): content boundaries.
- rbh_article_end(post_id): contextual CTA at priority 10, then up to 3 links.
- rbh_related(post_id): related component at priority 10, outside article/sidebar.
- rbh_footer_cta: shared footer CTA at priority 10.
- rbh_banner: optional banner after navigation at priority 10.
Use add_action/remove_action in a site customization plugin for reusable slots.
Do not add metadata/schema in these slots: Yoast remains responsible for SEO.

Related posts use tag matches first, then specific categories, excluding self,
duplicates, non-published and password-protected posts. Default 3, hard maximum 6.
Context links come from WordPress menu objects, maximum 3, excluding self and
destinations already linked in saved content. Nothing is injected into saved text.
The TOC master switch controls LuckyWP through its public lwptoc_active filter;
when on, LuckyWP settings apply. Its stored configuration is never changed.
