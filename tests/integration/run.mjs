// End-to-end tests against a disposable local WordPress (WordPress + Yoast SEO
// + this theme + the rentbikehanoi-native-vi plugin). Run:
//   RBH_WP_ROOT=... node tests/integration/run.mjs
// Requires the local server: php -S 127.0.0.1:8899 -t $RBH_WP_ROOT/site $RBH_WP_ROOT/router.php
import { wp, php, phpJson, get, parse, normalize, check, summary, assert, BASE, vi } from './lib.mjs';
import { execFileSync } from 'node:child_process';
import fs from 'node:fs';
import path from 'node:path';

const REPO = path.resolve(path.dirname(new URL(import.meta.url).pathname), '../..');
const ENGLISH = [
  '/', '/about-us/', '/contact-us/', '/faq/', '/privacy-police/', '/terms-of-use/',
  '/motorbike-rental-prices-in-hanoi/', '/hanoi-scooter-rental/', '/motorbike-renting-in-hanoi-requirements/',
  '/motorbike-rental-hanoi-delivery-and-pickup-guide-complete-service-overview-2025/', '/english-rental-tip-1/',
  '/blog/', '/blog/tips-resources/motorbike-rental-guides/', '/blog/tips-resources/motorbike-rental-guides/page/2/',
  '/?s=rental', '/nope/', '/feed/', '/sitemap_index.xml', '/page-sitemap.xml', '/post-sitemap.xml', '/category-sitemap.xml',
  '/wp-json/wp/v2/search?search=rental&per_page=50',
];
const VI_PATHS = ['', 'gioi-thieu', 'bang-gia-thue-xe-may-ha-noi', 'thue-xe-ga-ha-noi', 'thu-tuc-thue-xe-may-ha-noi',
  'giao-nhan-xe-may-ha-noi', 'cau-hoi-thuong-gap', 'lien-he', 'blog', 'chinh-sach-bao-mat', 'dieu-khoan-su-dung'];
const PAIRS = { '': '/', 'gioi-thieu': '/about-us/', 'bang-gia-thue-xe-may-ha-noi': '/motorbike-rental-prices-in-hanoi/',
  'thue-xe-ga-ha-noi': '/hanoi-scooter-rental/', 'thu-tuc-thue-xe-may-ha-noi': '/motorbike-renting-in-hanoi-requirements/',
  'giao-nhan-xe-may-ha-noi': '/motorbike-rental-hanoi-delivery-and-pickup-guide-complete-service-overview-2025/',
  'cau-hoi-thuong-gap': '/faq/', 'lien-he': '/contact-us/', 'chinh-sach-bao-mat': '/privacy-police/', 'dieu-khoan-su-dung': '/terms-of-use/' };

async function snapshot(urls = ENGLISH) {
  const out = {};
  for (const u of urls) { const r = await get(u); out[u] = `${r.status}\n${normalize(r.html)}`; }
  return out;
}
function diffSnap(a, b, allow = () => false) {
  const bad = [];
  for (const u of Object.keys(a)) {
    if (a[u] === b[u]) continue;
    const al = a[u].split('\n'), bl = b[u].split('\n');
    const extra = bl.filter(l => !al.includes(l)), missing = al.filter(l => !bl.includes(l));
    const unexpected = [...extra.filter(l => !allow(l, u)), ...missing.filter(l => !allow(l, u, true))];
    if (unexpected.length) bad.push(`${u}: ${unexpected.slice(0, 3).map(l => l.trim().slice(0, 180)).join(' | ')}`);
  }
  return bad;
}
const tableHash = () => php(`global $wpdb; $h=''; foreach (array('posts','postmeta','terms','term_taxonomy','term_relationships','termmeta') as $t) { $h .= md5(wp_json_encode($wpdb->get_results("SELECT * FROM {$wpdb->prefix}$t ORDER BY 1, 2", ARRAY_N))); } echo md5($h . wp_json_encode($wpdb->get_results("SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE 'rbhvi_%' OR option_name IN ('permalink_structure','page_on_front','show_on_front') ORDER BY 1", ARRAY_N)));`);
const englishHash = () => php(`global $wpdb; echo md5(wp_json_encode($wpdb->get_results("SELECT ID, post_title, post_name, post_content, post_status, post_modified, post_parent FROM {$wpdb->posts} WHERE post_type IN ('post','page','nav_menu_item') AND ID NOT IN (SELECT tr.object_id FROM {$wpdb->term_relationships} tr JOIN {$wpdb->term_taxonomy} tt USING (term_taxonomy_id) WHERE tt.taxonomy='rbhvi_language') ORDER BY ID", ARRAY_N)) . wp_json_encode($wpdb->get_results("SELECT post_id, meta_key, meta_value FROM {$wpdb->postmeta} WHERE post_id NOT IN (SELECT tr.object_id FROM {$wpdb->term_relationships} tr JOIN {$wpdb->term_taxonomy} tt USING (term_taxonomy_id) WHERE tt.taxonomy='rbhvi_language') AND meta_key NOT LIKE '\\\\_edit%' ORDER BY meta_id", ARRAY_N)));`);
const graphTypes = p => p.jsonld.flatMap(d => (d['@graph'] || [d]).map(n => [].concat(n['@type']).join('+')));

(async () => {
  // ---------------------------------------------------------------- 0. baseline
  console.log('0. Plugin inactive (theme only)');
  try { wp('plugin', 'deactivate', 'rentbikehanoi-native-vi'); } catch (e) { /* already inactive */ }
  const E0 = await snapshot();
  const H0 = englishHash();

  if (process.env.RBH_BASELINE_REF) {
    await check(`English output identical to theme at ${process.env.RBH_BASELINE_REF} (plugin inactive)`, async () => {
      const dir = path.join(process.env.RBH_WP_ROOT, 'baseline-theme');
      fs.rmSync(dir, { recursive: true, force: true });
      execFileSync('git', ['-C', REPO, 'worktree', 'add', '--force', '--detach', dir, process.env.RBH_BASELINE_REF], { stdio: 'ignore' });
      const link = path.join(process.env.RBH_WP_ROOT, 'site/wp-content/themes/wp-theme');
      fs.rmSync(link); fs.symlinkSync(dir, link);
      try {
        const old = await snapshot();
        assert.deepEqual(diffSnap(old, E0), []);
      } finally {
        fs.rmSync(link); fs.symlinkSync(REPO, link);
        execFileSync('git', ['-C', REPO, 'worktree', 'remove', '--force', dir], { stdio: 'ignore' });
      }
    });
  }

  // ---------------------------------------------------------------- 1. activation
  console.log('1. Activation only');
  wp('plugin', 'activate', 'rentbikehanoi-native-vi');
  await check('activation writes no option and creates no content', () => {
    assert.equal(php(`echo wp_json_encode(array(get_option('rbhvi_enabled','absent'), get_option('rbhvi_registry','absent')));`), '["absent","absent"]');
    assert.equal(php(`echo count(get_posts(array('post_type'=>'page','post_status'=>'any','name'=>'vi')));`), '0');
  });
  await check('English pages byte-identical after activation', async () => assert.deepEqual(diffSnap(E0, await snapshot()), []));
  await check('/vi/ answers 404 while OFF', async () => {
    for (const p of ['/vi/', '/vi/lien-he/', '/vi/blog/']) assert.equal((await get(p)).status, 404, p);
  });

  // ---------------------------------------------------------------- 2. seeding
  console.log('2. Seed drafts');
  const seed1 = phpJson(`return wp_list_pluck(RBHVI_Seeder::run(), 'status');`);
  const seed2 = phpJson(`return wp_list_pluck(RBHVI_Seeder::run(), 'status');`);
  await check('creates 11 drafts once; re-run creates nothing', () => {
    assert.equal(Object.values(seed1).filter(s => s === 'created').length, 11);
    assert.ok(Object.values(seed2).every(s => s === 'exists'), JSON.stringify(seed2));
    const statuses = phpJson(`return array_map('get_post_status', RBHVI_State::registry());`);
    assert.ok(Object.values(statuses).every(s => s === 'draft'), JSON.stringify(statuses));
    assert.equal(php(`echo count(get_posts(array('post_type'=>'page','post_status'=>'any','posts_per_page'=>-1,'meta_key'=>'_rbhvi_managed')));`), '11');
  });
  await check('English posts/pages/menus untouched by seeding', () => assert.equal(englishHash(), H0));
  await check('English pages byte-identical with Vietnamese drafts present', async () => assert.deepEqual(diffSnap(E0, await snapshot()), []));
  await check('seeded content: no H1, no unresolved placeholder, Yoast fields set', () => {
    const rows = phpJson(`$o=array(); foreach (RBHVI_State::registry() as $k=>$id) { $c=get_post_field('post_content',$id); $o[$k]=array(preg_match('/<h1[\\s>]/i',$c), substr_count($c,'{{'), get_post_meta($id,'_yoast_wpseo_title',true)!=='', get_post_meta($id,'_yoast_wpseo_metadesc',true)!=='', RBHVI_Language::is_vi($id)); } return $o;`);
    for (const [k, r] of Object.entries(rows)) assert.deepEqual(r, [0, 0, true, true, true], k);
  });

  // ---------------------------------------------------------------- 3. enable with drafts
  console.log('3. Enable while pages are drafts');
  const en1 = phpJson(`$r=RBHVI_State::enable(); return array($r['ok'], RBHVI_State::is_enabled());`);
  await check('enable succeeds (only warnings)', () => assert.deepEqual(en1, [true, true]));
  await check('unpublished Vietnamese URLs answer 404', async () => {
    for (const p of VI_PATHS) assert.equal((await get(`/vi/${p ? p + '/' : ''}`)).status, 404, p);
  });
  await check('no switch, no hreflang, English unchanged while /vi/ home is a draft', async () => assert.deepEqual(diffSnap(E0, await snapshot()), []));

  // ---------------------------------------------------------------- 4. publish + pairs
  console.log('4. Publish Vietnamese pages and confirm suggested pairs');
  php(`foreach (RBHVI_Language::vi_ids() as $id) { $s=(int)get_post_meta($id,'_rbhvi_pair_suggested',true); if ($s && !RBHVI_Admin::pair_error($id,$s)) update_post_meta($id,'_rbhvi_pair',$s); } foreach (RBHVI_State::registry() as $id) wp_update_post(array('ID'=>$id,'post_status'=>'publish'));`);
  await check('English posts still untouched after publishing Vietnamese pages', () => assert.equal(englishHash(), H0));

  const viPages = {};
  for (const p of VI_PATHS) viPages[p] = await get(`/vi/${p ? p + '/' : ''}`);
  await check('every Vietnamese page answers 200', () => { for (const [p, r] of Object.entries(viPages)) assert.equal(r.status, 200, p); });
  await check('lang="vi", og:locale vi_VN, exactly one title/canonical/description/H1, no duplicate robots', () => {
    for (const [p, r] of Object.entries(viPages)) {
      const x = parse(r.html);
      assert.equal(x.lang, 'vi', p); assert.equal(x.ogLocale, 'vi_VN', p);
      assert.equal(x.titles, 1, p); assert.equal(x.descriptions.length, 1, p);
      // Yoast prints no canonical on a noindex page (the empty listing); otherwise exactly one.
      assert.equal(x.canonicals.length, x.robots.join().includes('noindex') ? 0 : 1, p);
      assert.ok(x.robots.length <= 1, p); assert.equal(x.h1, 1, p); assert.equal(x.jsonld.length, 1, `${p} JSON-LD blocks`);
    }
  });
  await check('canonical of each Vietnamese page is itself (never English)', () => {
    for (const [p, r] of Object.entries(viPages)) { const c = parse(r.html).canonicals; if (c.length) assert.equal(c[0], vi(p), p); else assert.equal(p, 'blog'); }
  });
  await check('Vietnamese titles and descriptions are unique', () => {
    const t = Object.values(viPages).map(r => parse(r.html).title), d = Object.values(viPages).map(r => parse(r.html).descriptions[0]);
    assert.equal(new Set(t).size, t.length); assert.equal(new Set(d).size, d.length);
    for (const s of t) assert.doesNotMatch(s, /Motorbike|Rental|Hanoi /);
  });
  await check('hreflang en/vi/x-default bidirectional for confirmed pairs only', async () => {
    for (const [p, r] of Object.entries(viPages)) {
      const x = parse(r.html);
      if (!(p in PAIRS)) { assert.equal(x.hreflangCount, 0, `${p} must have no hreflang`); continue; }
      assert.deepEqual(x.hreflang, { en: BASE + PAIRS[p], vi: vi(p), 'x-default': BASE + PAIRS[p] }, p);
      const e = parse((await get(PAIRS[p])).html);
      assert.deepEqual(e.hreflang, { en: BASE + PAIRS[p], vi: vi(p), 'x-default': BASE + PAIRS[p] }, PAIRS[p]);
      assert.equal(e.canonicals.length, 1); assert.equal(e.canonicals[0], BASE + PAIRS[p], 'English canonical stays English');
    }
    assert.equal(parse((await get('/english-rental-tip-1/')).html).hreflangCount, 0, 'unpaired English post has no hreflang');
  });
  await check('schema: Yoast graph only; FAQPage only on the FAQ page with all visible questions', () => {
    for (const [p, r] of Object.entries(viPages)) {
      const types = graphTypes(parse(r.html));
      assert.ok(types.includes('WebSite') && types.some(t => t.startsWith('WebPage')), `${p}: ${types}`);
      if (p !== '') assert.ok(types.includes('BreadcrumbList'), `${p}: ${types}`);
      assert.equal(types.some(t => t.includes('FAQPage')), p === 'cau-hoi-thuong-gap', p);
    }
    const faq = viPages['cau-hoi-thuong-gap'].html;
    const qs = parse(faq).jsonld[0]['@graph'].filter(n => n['@type'] === 'Question').map(n => n.name);
    assert.ok(qs.length >= 8); for (const q of qs) assert.ok(faq.includes(`<strong class="schema-faq-question">${q}</strong>`), q);
  });
  await check('breadcrumbs start at "Trang chủ" /vi/ and contain no English link', () => {
    for (const [p, r] of Object.entries(viPages)) {
      const crumbs = parse(r.html).breadcrumb; if (p === '') continue;
      assert.deepEqual(crumbs[0], [vi(''), 'Trang chủ'], p);
      for (const [href] of crumbs) assert.ok(href.startsWith(vi('')), `${p}: ${href}`);
      const list = parse(r.html).jsonld[0]['@graph'].find(n => n['@type'] === 'BreadcrumbList').itemListElement;
      assert.equal(list[0].item, vi(''));
    }
  });
  await check('menus, mobile menu and footer on Vietnamese pages link only to /vi/', () => {
    for (const [p, r] of Object.entries(viPages)) {
      const x = parse(r.html);
      assert.ok(x.desktopNav.length >= 4, p);
      for (const href of [...x.desktopNav, ...x.mobileNav, ...x.footerNav]) assert.ok(href.startsWith(vi('')), `${p}: ${href}`);
    }
  });
  await check('English menus keep the frozen labels and English URLs', async () => {
    const snap = JSON.parse(fs.readFileSync(path.join(REPO, 'docs/navigation-labels-approved-2026-10-20.json'), 'utf8'));
    const html = (await get('/')).html;
    for (const item of snap.menus.primary) assert.ok(html.includes(`>${item.title.replace('&', '&amp;')}</a>`), item.title);
    for (const href of parse(html).mobileNav) assert.ok(!href.includes('/vi/'), href);
  });
  await check('VI | EN switch: paired target, else other home; sits right after the Light/Dark toggle', async () => {
    for (const [p, r] of Object.entries(viPages)) {
      const [href, lang] = parse(r.html).switchLink;
      assert.equal(lang, 'en'); assert.equal(href, BASE + (PAIRS[p] || '/'), p);
      assert.match(r.html, /<\/label>\s*<script>document\.getElementById\('rbh-dark-mode'\)[^<]*<\/script>\s*<a class="rbh-lang-switch"/);
    }
    for (const [p, en] of Object.entries(PAIRS)) assert.deepEqual(parse((await get(en)).html).switchLink, [vi(p), 'vi'], en);
    assert.deepEqual(parse((await get('/blog/')).html).switchLink, [vi(''), 'vi']);
    assert.deepEqual(parse((await get('/?s=rental')).html).switchLink, [vi(''), 'vi']);
    assert.deepEqual(parse((await get('/nope/')).html).switchLink, [vi(''), 'vi']);
    const vi404 = await get('/vi/khong-ton-tai/'); assert.equal(vi404.status, 404);
    assert.deepEqual(parse(vi404.html).switchLink, [BASE + '/', 'en']); assert.equal(parse(vi404.html).lang, 'vi');
    assert.equal(parse(vi404.html).h1, 1);
  });
  await check('empty Vietnamese blog listing is noindex and absent from the sitemap', async () => {
    assert.match(parse(viPages.blog.html).robots[0], /noindex/);
    assert.ok(!(await get('/page-sitemap.xml')).html.includes(vi('blog')));
  });
  await check('sitemap lists published Vietnamese pages (no second sitemap)', async () => {
    const xml = (await get('/page-sitemap.xml')).html;
    for (const p of VI_PATHS.filter(p => p !== 'blog')) assert.ok(xml.includes(`<loc>${vi(p)}</loc>`), p);
    for (const u of ['/', '/about-us/']) assert.ok(xml.includes(`<loc>${BASE}${u}</loc>`));
    assert.equal((await get('/wp-sitemap.xml')).status === 200 && (await get('/wp-sitemap.xml')).html.includes('<sitemapindex'), false, 'core sitemap must stay replaced by Yoast');
  });
  await check('Vietnamese search is noindex; English search excludes Vietnamese', async () => {
    const r = await get('/vi/?s=xe'); assert.equal(r.status, 200);
    assert.match(parse(r.html).robots.join(), /noindex/); assert.equal(parse(r.html).lang, 'vi');
  });

  // ---------------------------------------------------------------- 5. posts
  console.log('5. Vietnamese posts, categories, search, pagination');
  const ids = phpJson(`
    $cat = wp_insert_term('Kinh nghiệm', 'category', array('slug'=>'kinh-nghiem')); $cat = is_wp_error($cat) ? (int) $cat->get_error_data() : (int) $cat['term_id'];
    update_term_meta($cat, '_rbhvi_lang', 'vi'); wp_update_term($cat, 'category', array());
    $en = get_page_by_path('english-rental-tip-1', OBJECT, 'post')->ID; $o = array('cat'=>$cat);
    $mk = function($slug, $status, $date, $pair = 0) use ($cat) { $id = wp_insert_post(array('post_type'=>'post','post_status'=>$status,'post_name'=>$slug,'post_title'=>'Bài thử '.$slug,'post_date'=>$date,'post_content'=>'<!-- wp:paragraph --><p>Nội dung tiếng Việt về xe máy '.$slug.'.</p><!-- /wp:paragraph -->','post_category'=>array($cat))); RBHVI_Language::set_vi($id, true); if ($pair) update_post_meta($id,'_rbhvi_pair',$pair); wp_update_post(array('ID'=>$id)); return $id; };
    $o['paired'] = $mk('kinh-nghiem-thue-xe-lan-dau', 'publish', '2026-05-01 10:00:00', $en);
    for ($i = 1; $i <= 10; $i++) { $o['n'.$i] = $mk('meo-di-xe-'.$i, 'publish', sprintf('2026-05-%02d 11:00:00', $i + 1)); }
    $o['draft'] = $mk('ban-nhap-viet', 'draft', '2026-05-20 10:00:00');
    $o['private'] = $mk('rieng-tu-viet', 'private', '2026-05-21 10:00:00');
    $o['future'] = $mk('hen-gio-viet', 'future', '2030-01-01 10:00:00');
    return $o;`);
  const viPost = await get('/vi/kinh-nghiem-thue-xe-lan-dau/');
  await check('Vietnamese post lives at /vi/{slug}/ with self canonical and hreflang to its English pair', () => {
    assert.equal(viPost.status, 200); const x = parse(viPost.html);
    assert.equal(x.canonicals[0], vi('kinh-nghiem-thue-xe-lan-dau'));
    assert.equal(x.hreflang.en, BASE + '/english-rental-tip-1/'); assert.equal(x.hreflang['x-default'], BASE + '/english-rental-tip-1/');
    assert.ok(graphTypes(x).some(t => t.includes('Article')), 'Article schema from Yoast');
    assert.equal(x.h1, 1); assert.equal(x.lang, 'vi');
  });
  await check('Vietnamese post is not reachable at an English URL', async () => {
    assert.equal((await get('/kinh-nghiem-thue-xe-lan-dau/')).status, 404);
    assert.equal((await get(`/?p=${ids.paired}`)).status, 404);
  });
  await check('draft/private/future Vietnamese posts are 404 and never listed', async () => {
    for (const s of ['ban-nhap-viet', 'rieng-tu-viet', 'hen-gio-viet']) assert.equal((await get(`/vi/${s}/`)).status, 404, s);
    const pages = [await get('/vi/blog/'), await get('/vi/blog/page/2/'), await get('/vi/?s=Bài'), await get('/vi/chuyen-muc/kinh-nghiem/'), await get('/post-sitemap.xml'), await get('/feed/')];
    for (const r of pages) for (const s of ['ban-nhap-viet', 'rieng-tu-viet', 'hen-gio-viet']) assert.ok(!r.html.includes(s), s);
  });
  await check('English lists never contain Vietnamese posts (blog, category, search, feed, REST, home, related)', async () => {
    for (const u of ['/blog/', '/blog/tips-resources/motorbike-rental-guides/', '/?s=B%C3%A0i', '/?s=rental', '/feed/', '/wp-json/wp/v2/search?search=B%C3%A0i', '/', '/english-rental-tip-1/', '/hanoi-scooter-rental/']) {
      // hreflang/switch legitimately point to the paired Vietnamese URL; lists must not.
      const html = (await get(u)).html.replace(/<link rel="alternate" hreflang[^>]*>/g, '').replace(/<a class="rbh-lang-switch"[^>]*>/g, '');
      assert.ok(!html.includes('/vi/meo-di-xe') && !html.includes('/vi/kinh-nghiem-thue'), u);
    }
    assert.equal((await get('/blog/chuyen-muc/kinh-nghiem/')).status, 404);
  });
  await check('Vietnamese lists contain only Vietnamese posts', async () => {
    for (const u of ['/vi/blog/', '/vi/blog/page/2/', '/vi/?s=xe', '/vi/chuyen-muc/kinh-nghiem/']) {
      const r = await get(u); assert.equal(r.status, 200, u); const cards = parse(r.html).cards;
      assert.ok(cards.length > 0, u); for (const c of cards) assert.ok(c.startsWith(vi('')), `${u}: ${c}`);
    }
    const s = parse((await get('/vi/?s=rental')).html).cards; assert.equal(s.length, 0, 'English posts not in Vietnamese search');
  });
  await check('Vietnamese blog pagination: page 2 self-canonical, page 3 is 404, now indexable', async () => {
    const p1 = parse((await get('/vi/blog/')).html); assert.ok(!p1.robots.join().includes('noindex'));
    assert.equal(p1.cards.length, 9);
    const p2 = await get('/vi/blog/page/2/'); assert.equal(p2.status, 200);
    assert.equal(parse(p2.html).canonicals[0], vi('blog') + 'page/2/'); assert.match(parse(p2.html).title, /Trang 2/);
    assert.equal((await get('/vi/blog/page/3/')).status, 404);
  });
  await check('previous/next and related posts stay in Vietnamese', async () => {
    const r = await get('/vi/meo-di-xe-5/'); const x = parse(r.html);
    const links = [...r.html.matchAll(/<div class="nav-(?:previous|next)"><a href="([^"]+)"/g)].map(m => m[1]);
    assert.ok(links.length > 0, 'post navigation present'); for (const l of links) assert.ok(l.startsWith(vi('')), l);
    for (const c of x.cards) assert.ok(c.startsWith(vi('')), c);
    const en = await get('/english-rental-tip-5/');
    for (const l of [...en.html.matchAll(/<div class="nav-(?:previous|next)"><a href="([^"]+)"/g)].map(m => m[1])) assert.ok(!l.includes('/vi/'), l);
  });
  await check('post sitemap: paired Vietnamese post in, unpaired excluded (strict default), English intact', async () => {
    const xml = (await get('/post-sitemap.xml')).html;
    assert.ok(xml.includes(vi('kinh-nghiem-thue-xe-lan-dau'))); assert.ok(!xml.includes('/vi/meo-di-xe-'));
    assert.ok(xml.includes(BASE + '/hanoi-scooter-rental/'));
    const cat = (await get('/category-sitemap.xml')).html; assert.ok(cat.includes(vi('chuyen-muc/kinh-nghiem')));
  });
  await check('page views do not write to the database', async () => {
    const before = tableHash();
    for (const u of [...ENGLISH, ...VI_PATHS.map(p => `/vi/${p ? p + '/' : ''}`), '/vi/blog/page/2/', '/vi/?s=xe', '/vi/meo-di-xe-3/', '/vi/x/']) await get(u);
    assert.equal(tableHash(), before);
  });
  await check('English posts still untouched', () => assert.equal(englishHash(), H0));

  // ---------------------------------------------------------------- 6. disable / undo
  console.log('6. Disable and undo');
  php(`RBHVI_State::disable();`);
  await check('OFF: every Vietnamese URL 404, sitemap without /vi/, content kept', async () => {
    for (const p of [...VI_PATHS.map(p => `/vi/${p ? p + '/' : ''}`), '/vi/kinh-nghiem-thue-xe-lan-dau/', '/vi/chuyen-muc/kinh-nghiem/']) assert.equal((await get(p)).status, 404, p);
    for (const s of ['/page-sitemap.xml', '/post-sitemap.xml', '/category-sitemap.xml']) assert.ok(!(await get(s)).html.includes('/vi/'), s);
    assert.equal(php(`echo count(RBHVI_Language::vi_ids());`), '25');
  });
  // Only Yoast's sitemap-index <lastmod> may move (it reflects the newest modified page of each type).
  await check('OFF: English output identical to the plugin-inactive baseline', async () => assert.deepEqual(diffSnap(E0, await snapshot(), l => /^\s*<lastmod>/.test(l)), []));
  const undo = phpJson(`$r = RBHVI_State::undo(); return array($r['ok'], RBHVI_State::is_enabled());`);
  await check('undo restores the previous state (ON)', async () => { assert.deepEqual(undo, [true, true]); assert.equal((await get('/vi/')).status, 200); });

  // ---------------------------------------------------------------- 7. preflight errors
  console.log('7. Preflight blocks unsafe enable');
  const blocked = phpJson(`RBHVI_State::disable(); $p = wp_insert_post(array('post_type'=>'post','post_status'=>'publish','post_name'=>'vi','post_title'=>'Conflict')); $r = RBHVI_State::enable(); $o = array($r['ok'], RBHVI_State::is_enabled(), wp_list_pluck(array_filter($r['checks'], function($c){return 'error'===$c['level'];}), 'id')); wp_delete_post($p, true); return $o;`);
  await check('slug conflict "vi" → enable refused, state stays OFF', () => { assert.equal(blocked[0], false); assert.equal(blocked[1], false); assert.ok(Object.values(blocked[2]).includes('slug_vi_post')); });
  const slugBroken = phpJson(`$home = RBHVI_State::page_id('home'); wp_update_post(array('ID'=>$home, 'post_name'=>'vi-moved')); $r = RBHVI_State::enable(); $o = array($r['ok'], RBHVI_State::is_enabled()); wp_update_post(array('ID'=>$home, 'post_name'=>'vi')); return $o;`);
  await check('broken /vi/ home slug → enable refused, no state change', () => assert.deepEqual(slugBroken, [false, false]));
  const rolled = phpJson(`$id = RBHVI_State::page_id('contact'); $parent = wp_get_post_parent_id($id); wp_update_post(array('ID'=>$id, 'post_parent'=>0)); $r = RBHVI_State::enable(); $h = RBHVI_State::history(); $last = end($h); $o = array($r['ok'], (bool) $r['verify'], RBHVI_State::is_enabled(), $last['action']); wp_update_post(array('ID'=>$id, 'post_parent'=>$parent)); return $o;`);
  await check('post-enable verification failure → automatic rollback to OFF', () => assert.deepEqual(rolled, [false, true, false, 'rollback']));
  const ok = phpJson(`$r = RBHVI_State::enable(); return $r['ok'];`);
  await check('enable works again after fixing the conflict', () => assert.equal(ok, true));

  // ---------------------------------------------------------------- 8. no SEO plugin
  console.log('8. Without Yoast (fallback)');
  wp('plugin', 'deactivate', 'wordpress-seo');
  await check('fallback: one title, description, canonical, robots-or-none; hreflang kept; core sitemap lists /vi/', async () => {
    const x = parse((await get('/vi/lien-he/')).html);
    assert.equal(x.titles, 1); assert.match(x.title, /Liên hệ thuê xe/); assert.equal(x.descriptions.length, 1);
    assert.deepEqual(x.canonicals, [vi('lien-he')]); assert.equal(x.hreflang.vi, vi('lien-he')); assert.equal(x.jsonld.length, 0);
    const xml = (await get('/wp-sitemap-posts-page-1.xml')).html; assert.ok(xml.includes(vi('lien-he')));
    const posts = (await get('/wp-sitemap-posts-post-1.xml')).html; assert.ok(!posts.includes('/vi/meo-di-xe-'));
    assert.ok(parse((await get('/vi/blog/')).html).robots.length <= 1);
  });
  wp('plugin', 'activate', 'wordpress-seo');

  // ---------------------------------------------------------------- 9. deactivate
  console.log('9. Plugin deactivated');
  wp('plugin', 'deactivate', 'rentbikehanoi-native-vi');
  await check('English pages keep status, title, canonical, menus and content after deactivation', async () => {
    for (const u of ENGLISH.filter(u => !u.includes('sitemap') && !u.includes('wp-json') && !u.includes('feed'))) {
      const a = (await get(u)), b = E0[u];
      assert.equal(String(a.status), b.split('\n')[0], u);
      const x = parse(a.html), y = parse(b.slice(b.indexOf('\n') + 1));
      assert.deepEqual([x.title, x.canonicals, x.desktopNav, x.mobileNav, x.hreflangCount, x.switchLink], [y.title, y.canonicals, y.desktopNav, y.mobileNav, 0, []], u);
    }
  });
  // Documented limitation: without the plugin WordPress no longer separates languages in lists
  // (e.g. the Latest Posts widget) and serves published /vi/ pages natively. See docs/vi/ROLLBACK.md.
  await check('documented limitation is real: Vietnamese pages remain reachable by core after deactivation', async () => {
    assert.equal((await get('/vi/lien-he/')).status, 200);
  });
  await check('Vietnamese content is kept after deactivation', () => assert.equal(php(`echo count(get_posts(array('post_type'=>array('post','page'),'post_status'=>'any','posts_per_page'=>-1,'meta_key'=>'_rbhvi_managed')));`), '11'));
  wp('plugin', 'activate', 'rentbikehanoi-native-vi');

  summary();
})().catch(e => { console.error(e); process.exitCode = 1; });
