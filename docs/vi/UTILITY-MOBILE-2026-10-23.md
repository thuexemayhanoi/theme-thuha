# Mobile utility bar — 23 October 2026

The owner reported different line wrapping in the black hours/phone strip after switching VI/EN on an iPhone. The existing mobile flex-wrap rules allowed the longer Vietnamese text to increase the strip's height.

An authorized administrator applied `fixes/utility-mobile.css` through WordPress core Additional CSS for the active stylesheet `wp-theme`: published `custom_css` post **3484**, slug `wp-theme`, and theme mod `custom_css_post_id=3484`. There was no previous Additional CSS for this stylesheet. CSS posts for older themes were preserved. LiteSpeed/object cache was purged.

At widths up to 760px the brand occupies the first row; hours and the telephone occupy two columns on the next row. The telephone retains a 44px minimum touch target. Desktop rules are untouched. Extremely narrow screens may still wrap the hours to keep all information visible.

## Verification and limits

- Public browser inspection confirmed the exact grid override in the loaded CSSOM on both `/vi/` and `/` (LiteSpeed aggregates it; a missing `#wp-custom-css` element is not proof it is absent).
- Clicking the actual VI → EN switch reached `/` with `lang=en-US`; its return link points to `/vi/`.
- At the observed desktop viewport (1363 CSS pixels), `.utility-inner` still computes to `display:flex`.
- Live mobile dimensions and physical iPhone Safari appearance have **not** been verified. Browser zoom did not change the remote viewport. Local Playwright had no installed Chromium binary; no browser tooling was installed. No full theme/integration test run is claimed for this database CSS setting.

No theme/plugin reinstall, version change, main merge, hosting edit or content/SEO field change was needed. The runtime source files and existing English-preservation tests remain unchanged. This repository copy records the production setting; installing the ZIP alone on a fresh WordPress site does not install Additional CSS. Future maintainers must preserve this setting or deliberately migrate it into the stylesheet with responsive verification.

## Rollback

Remove only the dated utility-bar block from Additional CSS for `wp-theme`, then purge the page cache. Keep any CSS added later. Do not delete the unrelated older themes' CSS or deactivate the bilingual plugin. Post 3484 and its theme mod record the setting used here; preserve a copy before changing it.
