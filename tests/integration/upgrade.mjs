// Upgrade test that mirrors production: install the deployed ZIPs (theme 1.3.0 + plugin 1.0.0), publish the
// Vietnamese pages and enable /vi/ with the old plugin, then upgrade in place with the new ZIPs and verify that
// only the /vi/ home layout changed. Local site only (127.0.0.1); needs a freshly bootstrapped RBH_WP_ROOT
// served on RBH_WP_PORT.
//   RBH_WP_ROOT=... RBH_WP_PORT=8898 node tests/integration/upgrade.mjs dist/previous/wp-theme-1.3.0.zip \
//     dist/previous/rentbikehanoi-native-vi-1.0.0.zip dist/wp-theme-1.4.0.zip dist/rentbikehanoi-native-vi-1.1.1.zip
import { wp, php, phpJson, get, parse, normalize, check, summary, assert, vi } from './lib.mjs';
import fs from 'node:fs';
import path from 'node:path';

const [oldTheme, oldPlugin, newTheme, newPlugin] = process.argv.slice(2).map(p => path.resolve(p));
const zipVersion = f => path.basename(f).match(/-(\d+\.\d+\.\d+)\.zip$/)[1];
const site = path.join(process.env.RBH_WP_ROOT, 'site/wp-content');
const ENGLISH = ['/', '/about-us/', '/contact-us/', '/faq/', '/terms-of-use/', '/motorbike-rental-prices-in-hanoi/', '/hanoi-scooter-rental/',
  '/blog/', '/blog/tips-resources/motorbike-rental-guides/', '/?s=rental', '/nope/', '/feed/', '/sitemap_index.xml', '/page-sitemap.xml', '/post-sitemap.xml'];
const VI = ['', 'gioi-thieu', 'bang-gia-thue-xe-may-ha-noi', 'thue-xe-ga-ha-noi', 'thu-tuc-thue-xe-may-ha-noi', 'giao-nhan-xe-may-ha-noi',
  'cau-hoi-thuong-gap', 'lien-he', 'blog', 'chinh-sach-bao-mat', 'dieu-khoan-su-dung'];
const snap = async urls => Object.fromEntries(await Promise.all(urls.map(async u => { const r = await get(u); return [u, `${r.status}\n${normalize(r.html)}`]; })));
const head = h => { const x = parse(h); return { title: x.title, canonicals: x.canonicals, descriptions: x.descriptions, robots: x.robots, hreflang: x.hreflang, lang: x.lang, ogLocale: x.ogLocale, h1: x.h1, jsonld: x.jsonld.length }; };
const state = () => phpJson(`global $wpdb; $ids = RBHVI_State::registry(); $o = array('registry' => $ids, 'enabled' => get_option('rbhvi_enabled'), 'settings' => get_option('rbhvi_settings', 'absent'), 'history' => count(RBHVI_State::history()), 'pages' => array());
  foreach ($ids as $k => $id) { $p = get_post($id); $o['pages'][$k] = array($p->post_status, $p->post_name, $p->post_parent, md5($p->post_content), $p->post_modified, get_post_meta($id, '_rbhvi_pair', true), get_post_meta($id, '_yoast_wpseo_title', true)); }
  $o['vi_count'] = count(get_posts(array('post_type' => array('post','page'), 'post_status' => 'any', 'posts_per_page' => -1, 'rbhvi_skip' => true, 'meta_key' => '_rbhvi_managed')));
  $o['english'] = md5(wp_json_encode($wpdb->get_results("SELECT ID, post_title, post_name, post_content, post_status, post_modified FROM {$wpdb->posts} WHERE post_type IN ('post','page','nav_menu_item') AND ID NOT IN (" . implode(',', array_map('intval', $ids)) . ") ORDER BY ID", ARRAY_N)));
  return $o;`);

(async () => {
  console.log('Install the deployed release (theme 1.3.0 + plugin 1.0.0) from ZIP');
  for (const link of [path.join(site, 'themes/wp-theme'), path.join(site, 'plugins/rentbikehanoi-native-vi')]) fs.rmSync(link, { recursive: true, force: true });
  wp('theme', 'install', oldTheme, '--activate');
  wp('plugin', 'install', oldPlugin, '--activate');
  await check('deployed versions installed', () => {
    assert.equal(wp('theme', 'get', 'wp-theme', '--field=version'), '1.3.0');
    assert.equal(wp('plugin', 'get', 'rentbikehanoi-native-vi', '--field=version'), '1.0.0');
  });
  // Same operations as the 23 Oct 2026 deployment: drafts → nine confirmed pairs (terms left unpaired) → publish → enable.
  php(`RBHVI_Seeder::run(); foreach (RBHVI_Language::vi_ids() as $id) { if ('terms' === get_post_meta($id, '_rbhvi_managed', true)) continue; $s = (int) get_post_meta($id, '_rbhvi_pair_suggested', true); if ($s && !RBHVI_Admin::pair_error($id, $s)) update_post_meta($id, '_rbhvi_pair', $s); } foreach (RBHVI_State::registry() as $id) wp_update_post(array('ID' => $id, 'post_status' => 'publish'));`);
  const enabled = phpJson(`return RBHVI_State::enable()['ok'];`);
  wp('eval-file', path.resolve('tests/integration/fixture-logo.php'));
  await check('old release enabled with 11 published pages', () => assert.equal(enabled, true));
  const before = { state: state(), english: await snap(ENGLISH), vi: Object.fromEntries(await Promise.all(VI.map(async p => [p, head((await get(`/vi/${p ? p + '/' : ''}`)).html)]))) };
  const oldHome = (await get('/vi/')).html;

  // [title, twitter:title] of Vietnamese system pages (Yoast prints twitter:title on search results only).
  const viSystem = async () => Object.fromEntries(await Promise.all(['/vi/?s=xe', '/vi/khong-ton-tai/'].map(async u => {
    const h = (await get(u)).html; return [u, [parse(h).title, (h.match(/<meta name="twitter:title" content="([^"]*)"/) || [])[1] || null]];
  })));
  const oldSystem = await viSystem();

  console.log(`Upgrade in place from ZIP (theme ${zipVersion(newTheme)} + plugin ${zipVersion(newPlugin)})`);
  wp('theme', 'install', newTheme, '--force');
  wp('plugin', 'install', newPlugin, '--force');
  await check('new versions installed; theme and plugin still active', () => {
    assert.equal(wp('theme', 'get', 'wp-theme', '--field=version'), zipVersion(newTheme));
    assert.equal(wp('plugin', 'get', 'rentbikehanoi-native-vi', '--field=version'), zipVersion(newPlugin));
    assert.equal(wp('theme', 'list', '--status=active', '--field=name'), 'wp-theme');
    assert.equal(wp('plugin', 'get', 'rentbikehanoi-native-vi', '--field=status'), 'active');
  });
  const afterHome = (await get('/vi/')).html;
  const after = { state: state(), english: await snap(ENGLISH), vi: Object.fromEntries(await Promise.all(VI.map(async p => [p, head((await get(`/vi/${p ? p + '/' : ''}`)).html)]))) };

  await check('database unchanged by the upgrade and the first page views (IDs, status, slugs, content, pairs, Yoast titles, options)', () => {
    assert.deepEqual(after.state, before.state);
  });
  await check('English pages byte-identical before/after the upgrade (incl. homepage, sitemaps, feed, search, 404)', () => {
    for (const u of ENGLISH) assert.equal(after.english[u], before.english[u], u);
  });
  await check('Vietnamese heads unchanged: title, canonical, description, robots, hreflang, lang, og:locale, one H1, one JSON-LD', () => {
    assert.deepEqual(after.vi, before.vi);
  });
  await check('/vi/ switches from the page layout to the English homepage layout; saved content still shown', () => {
    assert.match(oldHome, /class="container page-shell"/); assert.doesNotMatch(oldHome, /rbh-landing/);
    assert.match(afterHome, /<main id="main" class="home-main rbh-landing">/);
    assert.ok(afterHome.includes('Trang này tóm tắt những gì bạn cần biết'), 'saved intro paragraph');
    assert.equal((afterHome.match(/<details class="source-section"/g) || []).length, Number(php(`echo substr_count(get_post_field('post_content', RBHVI_State::page_id('home')), '<h2');`)) + 1);
  });
  await check('logo on Vietnamese pages now links to /vi/ (was the English home in 1.0.0)', () => {
    assert.match(oldHome, /<a href="http:\/\/127\.0\.0\.1:\d+\/" class="custom-logo-link"/);
    assert.ok(afterHome.includes(`<a href="${vi('')}" class="custom-logo-link"`));
  });
  await check('Vietnamese search and 404 titles (incl. twitter:title) switch from Yoast\'s English defaults to Vietnamese', async () => {
    assert.match(oldSystem['/vi/?s=xe'][0], /^You searched for xe/); assert.match(oldSystem['/vi/?s=xe'][1], /^You searched for xe/);
    assert.match(oldSystem['/vi/khong-ton-tai/'][0], /^Page not found/); assert.equal(oldSystem['/vi/khong-ton-tai/'][1], null);
    assert.deepEqual(await viSystem(), {
      '/vi/?s=xe': ['Kết quả tìm kiếm cho “xe” | Thuê xe máy Nguyễn Hà', 'Kết quả tìm kiếm cho “xe” | Thuê xe máy Nguyễn Hà'],
      '/vi/khong-ton-tai/': ['Không tìm thấy trang | Thuê xe máy Nguyễn Hà', null],
    });
  });
  await check('settings without home_layout default to the new layout; "page" switches back', async () => {
    assert.equal(php(`echo RBHVI_State::settings()['home_layout'];`), 'landing');
    php(`RBHVI_State::save_settings(array('sitemap_pairs_only' => true, 'home_layout' => 'page'));`);
    assert.match((await get('/vi/')).html, /class="container page-shell"/);
    php(`RBHVI_State::save_settings(array('sitemap_pairs_only' => true, 'home_layout' => 'landing'));`);
  });
  summary();
})().catch(e => { console.error(e); process.exitCode = 1; });
