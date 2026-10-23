# Production deployment — 23 October 2026

Timezone: Asia/Ho_Chi_Minh. Website: https://rentbikehanoi.com.

## Authority and installed release

The owner explicitly requested publishing the Vietnamese section and delegated the WordPress operations. The owner installed the two supplied ZIPs. An authorized administrator then used the configured WordPress connector and a securely authenticated browser to complete the plugin's native administration flow. No credentials are stored in this repository.

- Active theme: `wp-theme`, version **1.3.0**.
- Custom bilingual plugin: `rentbikehanoi-native-vi`, version **1.0.0**, active.
- Runtime source inspected: `e81fbd75ee29d2c1eeefeed424ddcdc1d4165179`.
- The runtime ZIPs were reproduced from that source and their checksums matched the original release candidates: theme `786b781dc99184cd34b259a5cc697f9c84fd58d551fb6a09c02bd52e2a6bc545`; plugin `a0d472f2b2d3f58f68747a2c6f4ffaa9b3da951ddc2bae4bcbba947cf7da258e`.
- Production WordPress **7.1.3**, PHP **8.2.34** observed through the authenticated connection.
- No hosting files, rewrite configuration, English content, English navigation labels, writer workflow or public default locale were edited in this deployment.

## Content and state

The existing 11 Vietnamese drafts were inspected before publication. They were nonempty, had no unresolved template placeholders or extra content H1s, and had the correct parent-page structure. The WordPress update returned success for all 11 IDs. `/vi/` was then enabled through the plugin's native admin button, including its preflight and route verification; the option was not toggled directly.

| Key | WordPress ID | Public path |
| --- | --- | --- |
| home | 3454 | `/vi/` |
| about | 3455 | `/vi/gioi-thieu/` |
| prices | 3456 | `/vi/bang-gia-thue-xe-may-ha-noi/` |
| scooter | 3457 | `/vi/thue-xe-ga-ha-noi/` |
| procedure | 3458 | `/vi/thu-tuc-thue-xe-may-ha-noi/` |
| delivery | 3459 | `/vi/giao-nhan-xe-may-ha-noi/` |
| faq | 3460 | `/vi/cau-hoi-thuong-gap/` |
| contact | 3461 | `/vi/lien-he/` |
| blog | 3462 | `/vi/blog/` |
| privacy | 3463 | `/vi/chinh-sach-bao-mat/` |
| terms | 3464 | `/vi/dieu-khoan-su-dung/` |

Nine English pairs are confirmed. The Vietnamese terms page remains deliberately unpaired: the English terms include broader rental-service/deposit provisions, while the Vietnamese page describes website use. The empty Vietnamese blog has no English pair and remains noindex, excluded from the sitemap.

The owner's signed-in admin-profile language was changed to `vi`. This does not change the public English website's locale. Legacy `gtranslate` was deactivated successfully so the native section does not depend on an outside translation plugin. LiteSpeed cache was purged after enabling; deactivating GTranslate also triggered the connector's automatic cache purge.

## Observed live checks

- All 11 Vietnamese paths returned HTTP **200**, `lang="vi"`, `og:locale="vi_VN"`, one title, one description and one H1.
- The 10 indexable Vietnamese pages had a single self-canonical. The empty `/vi/blog/` had `noindex, follow` and no canonical, as designed by the existing plugin/Yoast integration.
- Confirmed pairs emitted `en`, `vi` and `x-default` alternates. The unpaired terms and blog emitted no hreflang.
- All nine inspected non-home English core paths returned 200 with self-canonicals; their eight confirmed counterparts had reciprocal language alternates. The English terms intentionally had no hreflang. `/vi/?s=xe` returned 200 with `noindex, follow` and no canonical/hreflang.
- The English homepage returned 200, `lang="en-US"`. Its title, description, canonical, `og:title` and `og:site_name` were byte-equal to the anonymous pre-enable snapshot.
- Yoast `page-sitemap.xml` returned 200 with the 10 indexable Vietnamese URLs and without the empty Vietnamese blog.
- FAQ JSON-LD parsed successfully and included FAQPage with ten Question nodes. This is a JSON/visible-content check, not a Google Rich Results Test certification.
- A nonexistent Vietnamese path returned **404**.
- In the authenticated Chrome viewport, the native VI/EN switch was visible next to Light/Dark and switched `/vi/` to `/` and back. Dark mode was toggled and restored to light successfully. The Vietnamese header/footer and contact buttons were visible.
- Browser-flow QA: **4/5 for the tested desktop flow**. This is intentionally limited: no real-phone or Safari evaluation was performed.

## Remaining limitations — do not overstate completion

- No staging clone or fresh hosting backup was independently created/verified during this publishing session. The existing theme backup folder is not a full-site backup. Future releases still follow the backup/staging procedure.
- The legal pages were published under the owner's delegated publication request, not certified as legally complete. The owner should review business/data-handling policies; inherited airport-delivery and map details still warrant reconfirmation.
- The Vietnamese home currently uses the theme's regular page layout; it is not a translated reproduction of the English hero/cards homepage. A same-layout homepage enhancement is separate work, not silently completed here.
- No Search Console submission, Google indexing/ranking result, Rich Results Test, PageSpeed measurement, mobile/Safari/Firefox pass or exhaustive English-URL regression is claimed.
- The test workspace did not have PHP; local PHP lint/integration suites were not rerun by this publishing administrator. Packaging/checksums and JavaScript guards were checked; the original local WordPress test report remains in `dist/QA-REPORT.md`.
- Sitewide Yoast/WPCode configuration adds Article and Organization/LocalBusiness types to some pages. JSON was valid; the correctness of every inherited schema property was not audited here.
- Claude must still treat WordPress/crawler access as unavailable. Future assistants should read this record instead of assuming the original "not deployed" handoff is current.

## Recovery

Use the plugin's **Tắt /vi/** action first to hide the section without deleting content; this also removes switch/hreflang/sitemap entries. Keep the plugin active unless Vietnamese pages/posts have first been returned to draft/private. See `ROLLBACK.md`. No English content restoration is necessary for the publication-only actions described above.
