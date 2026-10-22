// Shared helpers for the local WordPress integration tests. Local only:
// every request goes to 127.0.0.1; nothing here can reach production.
import { execFileSync } from 'node:child_process';
import assert from 'node:assert/strict';

export const ROOT = process.env.RBH_WP_ROOT;
export const PORT = process.env.RBH_WP_PORT || '8899';
export const BASE = `http://127.0.0.1:${PORT}`;
if (!ROOT) throw new Error('Set RBH_WP_ROOT (see tests/integration/bootstrap-wp.sh).');

export function wp(...args) {
  return execFileSync(`${ROOT}/wp`, args, { encoding: 'utf8', stdio: ['ignore', 'pipe', 'pipe'] }).trim();
}
export function php(code) {
  return wp('eval', `wp_set_current_user(1); ${code}`);
}
export function phpJson(code) {
  return JSON.parse(php(`echo wp_json_encode((function(){ ${code} })());`));
}

export async function get(path, opts = {}) {
  const res = await fetch(BASE + path, { redirect: 'manual', ...opts });
  const html = await res.text();
  return { status: res.status, html, location: res.headers.get('location') };
}

const all = (re, s) => [...s.matchAll(re)];
export const parse = html => ({
  lang: (html.match(/<html[^>]*\blang="([^"]*)"/) || [])[1],
  title: (html.match(/<title>([^<]*)<\/title>/) || [])[1],
  titles: all(/<title>/g, html).length,
  canonicals: all(/<link rel="canonical" href="([^"]*)"/g, html).map(m => m[1]),
  robots: all(/<meta name=['"]robots['"] content=['"]([^'"]*)['"]/g, html).map(m => m[1]),
  descriptions: all(/<meta name="description" content="([^"]*)"/g, html).map(m => m[1]),
  ogLocale: (html.match(/<meta property="og:locale" content="([^"]*)"/) || [])[1],
  hreflang: Object.fromEntries(all(/<link rel="alternate" hreflang="([^"]+)" href="([^"]+)"/g, html).map(m => [m[1], m[2]])),
  hreflangCount: all(/hreflang="[^"]+" href=/g, html.split('</head>')[0]).length,
  jsonld: all(/<script type="application\/ld\+json"[^>]*>([\s\S]*?)<\/script>/g, html).map(m => JSON.parse(m[1])),
  h1: all(/<h1[\s>]/g, html).length,
  switchLink: (html.match(/<a class="rbh-lang-switch" href="([^"]+)" hreflang="([^"]+)"/) || []).slice(1),
  desktopNav: all(/href="([^"]+)"/g, (html.match(/<nav class="desktop-nav"[\s\S]*?<\/nav>/) || [''])[0]).map(m => m[1]),
  mobileNav: all(/href="([^"]+)"/g, (html.match(/<nav aria-label="[^"]*"><ul>[\s\S]*?<\/nav>/) || [''])[0]).map(m => m[1]),
  footerNav: all(/<details class="footer-group"[\s\S]*?<\/details>/g, html).flatMap(m => all(/href="([^"]+)"/g, m[0]).map(x => x[1])),
  cards: all(/<h2 class="card-title"><a href="([^"]+)"/g, html).map(m => m[1]),
  breadcrumb: all(/<a href="([^"]+)">([^<]+)<\/a>/g, (html.match(/<nav class="breadcrumbs"[\s\S]*?<\/nav>/) || [''])[0]).map(m => [m[1], m[2]]),
  postNav: all(/href="([^"]+)" rel="(?:prev|next)"/g, html).map(m => m[1]),
});

// Remove values that legitimately differ between two otherwise identical renders.
export const normalize = html => html
  .replace(/ver=[0-9a-z.]+/g, 'ver=X')
  .replace(/rbh-search-\d+/g, 'rbh-search-N')
  .replace(/"(datePublished|dateModified)":"[^"]*"/g, '"$1":"D"')
  .replace(/<meta property="article:modified_time" content="[^"]*"/g, '<meta property="article:modified_time" content="D"');

export function vi(path = '') { return `${BASE}/vi/${path ? path + '/' : ''}`; }
export function abs(path) { return BASE + path; }

let failures = 0, passes = 0;
export async function check(name, fn) {
  try { await fn(); passes++; console.log(`  ✔ ${name}`); }
  catch (e) { failures++; console.log(`  ✖ ${name}\n      ${String(e.message).split('\n').join('\n      ')}`); }
}
export function summary() {
  console.log(`\n${passes} passed, ${failures} failed`);
  if (failures) process.exitCode = 1;
  return failures;
}
export { assert };
