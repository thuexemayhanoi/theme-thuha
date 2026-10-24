# Vietnamese article display — 24 October 2026

Website: https://rentbikehanoi.com. Theme: wp-theme 1.4.0. Native VI plugin: 1.1.1.
Authorized scope: make published Vietnamese articles visible and readable like the English section.

## Applied to production

- Purged LiteSpeed Cache and object cache. An anonymous browser initially received the old
  “no Vietnamese posts” message, while uncached rendered output had nine article cards.
- Preserved the mobile utility-bar CSS and appended fixes/article-listing.css to core
  Additional CSS post 3484 for wp-theme. Every new selector is scoped to the Vietnamese
  body class; blog-specific changes additionally target page 3462.
- Updated only page 3462 post_content with fixes/blog-intro.html: concise reader-oriented
  introduction, six existing Vietnamese parent-category links and rental preparation links.
  The native plugin still appends the actual article query/pagination.
- Theme/plugin runtime files, English menu labels, permalinks, article content and SEO
  settings were not edited. The historical WPVibe draft was not modified or published.

## Verified in the public browser

- /vi/blog/: nine cards, six topic links, two desktop columns, one H1, lang=vi,
  self canonical and index/follow robots. No horizontal overflow at the desktop viewport.
- /vi/blog/page/2/: nine cards, correct self canonical, one H1 and every card link under /vi/.
- /vi/chuyen-muc/huong-dan-thue-xe/: nine cards, Vietnamese category heading,
  correct canonical and all card links under /vi/. Existing child-topic navigation is present.
- /vi/: three latest Vietnamese articles are visible, one H1, lang=vi and index/follow.
- Light/dark toggle: title and card colors use the theme's light/dark variables and stay readable.
- CSS includes a one-column breakpoint at 760px and minimum 44px topic/pagination targets.
  A local multi-viewport rendering attempt could not run because Chromium is absent;
  real-phone rendering has not been claimed or verified.
- Required assistant JavaScript syntax and regression checks passed.

## Cache and future publishing

Article selection is already automatic in the native plugin. Cached custom listing/home
URLs can lag new posts: validate the public /vi/ and /vi/blog/ after publishing, and have
the publishing integration purge the appropriate LiteSpeed URLs when needed. Do not
treat a cached empty page as evidence that WordPress failed to publish.

## Rollback and future maintenance

fixes/editorial-before-2026-10-24.json contains the exact prior CSS/content.
Restore those two fields through WordPress post APIs/commands and purge caches if this
presentation change must be undone. Do not restore the whole historical theme.

Additional CSS is WordPress data and survives normal theme updates for the same stylesheet.
Do not overwrite the mobile correction or add this same CSS twice. This commit records live
data changes; it is not a theme/plugin release and needs no ZIP installation.
