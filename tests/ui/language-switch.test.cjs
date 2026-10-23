// Browser checks for the VI | EN switch and the unchanged Light/Dark toggle.
// Runs against the local WordPress left by tests/integration/run.mjs (ON, pages published).
//   RBH_WP_PORT=8899 NODE_PATH=$(npm root -g) node tests/ui/language-switch.test.cjs [screenshot-dir]
const { chromium } = require('playwright');
const assert = require('node:assert/strict');
const BASE = `http://127.0.0.1:${process.env.RBH_WP_PORT || '8899'}`;
const SHOTS = process.argv[2];
const PAGES = ['/', '/about-us/', '/blog/', '/?s=rental', '/nope/', '/english-rental-tip-1/',
  '/vi/', '/vi/lien-he/', '/vi/blog/', '/vi/?s=xe', '/vi/khong-co/', '/vi/meo-di-xe-5/', '/vi/chuyen-muc/kinh-nghiem/'];
const WIDTHS = [320, 360, 390, 768, 1024, 1280];
// Header heights measured on the unchanged 1.2.0 theme (docs/vi/QA evidence).
const HEADER = w => (w <= 760 ? 73 : 87);

const luminance = rgb => {
  const [r, g, b] = rgb.match(/[\d.]+/g).slice(0, 3).map(Number).map(v => { v /= 255; return v <= 0.03928 ? v / 12.92 : ((v + 0.055) / 1.055) ** 2.4; });
  return 0.2126 * r + 0.7152 * g + 0.0722 * b;
};
const contrast = (a, b) => { const [x, y] = [luminance(a), luminance(b)].sort((m, n) => n - m); return (x + 0.05) / (y + 0.05); };

let failed = 0, passed = 0;
async function check(name, fn) {
  try { await fn(); passed++; console.log(`  ✔ ${name}`); } catch (e) { failed++; console.log(`  ✖ ${name}\n      ${e.message.split('\n').slice(0, 6).join('\n      ')}`); }
}

(async () => {
  const browser = await chromium.launch();
  for (const width of WIDTHS) {
    await check(`layout at ${width}px: switch beside Light/Dark, ≥44px, no overflow, header height unchanged, no layout shift`, async () => {
      const page = await browser.newPage({ viewport: { width, height: 800 } });
      for (const path of PAGES) {
        await page.goto(BASE + path, { waitUntil: 'load' });
        const m = await page.evaluate(() => {
          const box = s => { const e = document.querySelector(s); if (!e) return null; const r = e.getBoundingClientRect(); return { l: r.left, r: r.right, t: r.top, b: r.bottom, w: r.width, h: r.height, visible: getComputedStyle(e).display !== 'none' && r.width > 0 }; };
          return { overflow: document.documentElement.scrollWidth - document.documentElement.clientWidth, header: box('.site-header'), toggle: box('.appearance-toggle'), sw: box('.rbh-lang-switch'), actions: box('.header-actions'), vw: innerWidth };
        });
        const where = `${path}@${width}`;
        assert.ok(m.overflow <= 0, `${where} horizontal overflow ${m.overflow}px`);
        assert.ok(m.sw && m.sw.visible, `${where} switch visible`);
        assert.ok(m.sw.w >= 44 && m.sw.h >= 44, `${where} switch ${m.sw.w}x${m.sw.h}`);
        const gap = m.sw.l - m.toggle.r;
        assert.ok(gap >= 0 && gap <= 16, `${where} gap toggle→switch ${gap}`);
        assert.ok(Math.abs((m.sw.t + m.sw.b) / 2 - (m.toggle.t + m.toggle.b) / 2) <= 1, `${where} vertical alignment`);
        assert.ok(m.actions.r <= m.vw, `${where} actions inside viewport`);
        if (!process.env.RBH_LOGO) assert.equal(Math.round(m.header.h), HEADER(width), `${where} header height`);
        const without = await page.evaluate(() => { document.querySelector('.rbh-lang-switch').remove(); return document.querySelector('.site-header').getBoundingClientRect().height; });
        assert.equal(Math.round(m.header.h), Math.round(without), `${where} header height with switch = without switch`);
        await page.goto(BASE + path, { waitUntil: 'load' });
        if (SHOTS && (width === 390 || width === 1280) && ['/', '/vi/', '/vi/lien-he/', '/vi/blog/'].includes(path)) {
          await page.screenshot({ path: `${SHOTS}/${process.env.RBH_LOGO ? 'logo' : 'text'}${path.replace(/[/?=]/g, '_')}-${width}.png` });
        }
      }
      const cls = await page.evaluate(() => new Promise(resolve => {
        let total = 0; new PerformanceObserver(l => { for (const e of l.getEntries()) if (!e.hadRecentInput) total += e.value; }).observe({ type: 'layout-shift', buffered: true });
        setTimeout(() => resolve(total), 300);
      }));
      assert.ok(cls < 0.01, `CLS ${cls}`);
      await page.close();
    });
  }

  await check('keyboard: Tab goes Light/Dark → VI | EN; Enter opens the paired English page', async () => {
    const page = await browser.newPage({ viewport: { width: 1280, height: 800 } });
    await page.goto(BASE + '/vi/lien-he/');
    let guard = 0;
    while (!(await page.evaluate(() => document.activeElement && document.activeElement.id === 'rbh-dark-mode')) && guard++ < 40) await page.keyboard.press('Tab');
    await page.keyboard.press('Tab');
    const focused = await page.evaluate(() => ({ cls: document.activeElement.className, outline: getComputedStyle(document.activeElement).outlineStyle, name: document.activeElement.innerText }));
    assert.equal(focused.cls, 'rbh-lang-switch'); assert.notEqual(focused.outline, 'none', 'visible focus');
    await Promise.all([page.waitForNavigation(), page.keyboard.press('Enter')]);
    assert.equal(page.url(), BASE + '/contact-us/');
    await page.close();
  });

  await check('screen reader name and language attributes', async () => {
    const page = await browser.newPage();
    await page.goto(BASE + '/vi/');
    const vi = await page.locator('.rbh-lang-switch').evaluate(e => ({ lang: e.lang, hreflang: e.hreflang, sr: e.querySelector('.screen-reader-text').textContent, hidden: [...e.querySelectorAll('.rbh-lang-code')].every(c => c.getAttribute('aria-hidden') === 'true') }));
    assert.deepEqual(vi, { lang: 'en', hreflang: 'en', sr: 'View in English', hidden: true });
    assert.equal(await page.getByRole('link', { name: 'View in English' }).count(), 1);
    await page.goto(BASE + '/');
    assert.equal(await page.getByRole('link', { name: 'Xem bằng tiếng Việt' }).count(), 1);
    await page.close();
  });

  await check('Light/Dark keeps working on English and Vietnamese pages and is remembered', async () => {
    const ctx = await browser.newContext({ colorScheme: 'light' });
    const page = await ctx.newPage();
    for (const path of ['/', '/vi/']) {
      await page.goto(BASE + path);
      await page.evaluate(() => localStorage.removeItem('rbh-appearance')); await page.reload();
      assert.equal(await page.evaluate(() => document.documentElement.dataset.theme), 'light', path);
      const before = await page.evaluate(() => getComputedStyle(document.body).backgroundColor);
      await page.click('.appearance-toggle');
      assert.equal(await page.evaluate(() => document.documentElement.dataset.theme), 'dark', path);
      assert.notEqual(await page.evaluate(() => getComputedStyle(document.body).backgroundColor), before, path);
      await page.reload();
      assert.equal(await page.evaluate(() => document.documentElement.dataset.theme), 'dark', `${path} remembered`);
    }
    await page.goto(BASE + '/vi/lien-he/');
    assert.equal(await page.evaluate(() => document.documentElement.dataset.theme), 'dark', 'preference shared across languages');
    await ctx.close();
  });

  await check('contrast ≥ 4.5:1 for switch text in light and dark', async () => {
    for (const scheme of ['light', 'dark']) {
      const ctx = await browser.newContext({ colorScheme: scheme });
      const page = await ctx.newPage();
      for (const path of ['/', '/vi/']) {
        await page.goto(BASE + path);
        const c = await page.evaluate(() => { const s = document.querySelector('.rbh-lang-switch'); return { bg: getComputedStyle(s).backgroundColor, codes: [...s.querySelectorAll('.rbh-lang-code')].map(e => getComputedStyle(e).color) }; });
        for (const color of c.codes) assert.ok(contrast(color, c.bg) >= 4.5, `${scheme} ${path} ${color} on ${c.bg}: ${contrast(color, c.bg).toFixed(2)}`);
      }
      await ctx.close();
    }
  });

  await check('mobile menu on Vietnamese pages opens and lists only /vi/ links; Menu keeps its accessible name', async () => {
    const page = await browser.newPage({ viewport: { width: 360, height: 780 } });
    await page.goto(BASE + '/vi/');
    const trigger = page.locator('.header-actions > .menu-trigger');
    assert.equal(await trigger.evaluate(e => e.textContent.trim()), 'Menu');
    await trigger.click();
    await page.waitForSelector('#site-menu:popover-open');
    const links = await page.$$eval('#site-menu nav a', as => as.map(a => a.href));
    assert.ok(links.length >= 8); for (const l of links) assert.ok(l.startsWith(BASE + '/vi/'), l);
    await page.close();
  });

  for (const width of [320, 360, 390, 768, 1280]) {
    await check(`Vietnamese home at ${width}px: landing layout, one H1, photos loaded, phone number unbroken, no overflow`, async () => {
      const page = await browser.newPage({ viewport: { width, height: 900 } });
      await page.goto(BASE + '/vi/', { waitUntil: 'load' });
      for (const img of await page.$$('.bike-card-image img, .hero-photo')) await img.scrollIntoViewIfNeeded();
      await page.waitForFunction(() => [...document.querySelectorAll('.bike-card-image img, .hero-photo')].every(i => i.complete));
      const m = await page.evaluate(() => {
        const phone = document.querySelector('.hero .button-row a[href^="tel:"]');
        const node = [...phone.childNodes].find(n => n.nodeType === 3);
        const start = node.textContent.indexOf('0334');
        const range = document.createRange(); range.setStart(node, start); range.setEnd(node, start + '0334 699 969'.length);
        const lines = new Set([...range.getClientRects()].map(r => Math.round(r.top))).size;
        const vw = document.documentElement.clientWidth;
        const outside = [...document.querySelectorAll('main *')].filter(e => { const r = e.getBoundingClientRect(); return r.width && (r.right > vw + 1 || r.left < -1) && !e.closest('.rate-table'); }).map(e => e.className || e.tagName).slice(0, 3);
        const table = document.querySelector('.rate-table').getBoundingClientRect();
        return {
          landing: !!document.querySelector('main.rbh-landing'), h1: document.querySelectorAll('h1').length,
          images: [...document.querySelectorAll('.bike-card-image img, .hero-photo')].map(i => i.naturalWidth > 0),
          lines, overflow: document.documentElement.scrollWidth - vw, outside, tableInside: table.left >= 0 && table.right <= vw + 1,
          buttonsVisible: [...document.querySelectorAll('.hero .button-row a')].every(a => a.getBoundingClientRect().height >= 44),
        };
      });
      assert.ok(m.landing, 'landing layout'); assert.equal(m.h1, 1);
      assert.deepEqual(m.images, [true, true, true, true], 'hero + 3 bike photos loaded');
      assert.equal(m.lines, 1, 'phone number on one line'); assert.ok(m.overflow <= 0, `overflow ${m.overflow}`);
      assert.deepEqual(m.outside, [], 'elements outside the viewport'); assert.ok(m.tableInside, 'price table container inside the viewport');
      assert.ok(m.buttonsVisible, 'hero buttons ≥ 44px tall');
      await page.close();
    });
  }

  await check('mobile utility bar (hours + phone) shows on Vietnamese pages exactly like on English pages', async () => {
    for (const width of [320, 360, 390, 768, 1280]) {
      const page = await browser.newPage({ viewport: { width, height: 800 } });
      const bar = async path => { await page.goto(BASE + path); return page.evaluate(() => { const b = document.querySelector('.utility-bar'); const tel = b.querySelector('a[href^="tel:"]'); return { display: getComputedStyle(b).display, tel: !!tel && tel.getBoundingClientRect().height >= (innerWidth <= 760 ? 44 : 1) }; }); };
      const en = await bar('/'), viHome = await bar('/vi/'), viPage = await bar('/vi/lien-he/');
      assert.deepEqual(viHome, en, `${width}px /vi/`); assert.deepEqual(viPage, en, `${width}px /vi/lien-he/`);
      assert.notEqual(en.display, 'none', `${width}px utility bar visible`);
      await page.close();
    }
  });

  await check('Vietnamese home: logo/brand goes to /vi/, switch goes to the English home and back', async () => {
    const page = await browser.newPage({ viewport: { width: 390, height: 800 } });
    await page.goto(BASE + '/vi/lien-he/');
    await Promise.all([page.waitForNavigation(), page.click('.site-branding a')]);
    assert.equal(page.url(), BASE + '/vi/');
    await Promise.all([page.waitForNavigation(), page.click('.rbh-lang-switch')]);
    assert.equal(page.url(), BASE + '/');
    await Promise.all([page.waitForNavigation(), page.click('.rbh-lang-switch')]);
    assert.equal(page.url(), BASE + '/vi/');
    await page.close();
  });

  await check('Vietnamese home in dark mode: theme switches, hero text and price table stay readable (≥ 4.5:1)', async () => {
    const ctx = await browser.newContext({ colorScheme: 'light', viewport: { width: 390, height: 900 } });
    const page = await ctx.newPage();
    await page.goto(BASE + '/vi/');
    await page.click('.appearance-toggle');
    assert.equal(await page.evaluate(() => document.documentElement.dataset.theme), 'dark');
    const c = await page.evaluate(() => {
      const bg = getComputedStyle(document.body).backgroundColor;
      const pick = s => getComputedStyle(document.querySelector(s)).color;
      const table = document.querySelector('.rate-table');
      return { bg, h1: pick('.hero h1'), lede: pick('.hero-lede'), td: pick('.rate-table td'), tableBg: getComputedStyle(table).backgroundColor, step: pick('.steps-grid p') };
    });
    for (const [name, color, bg] of [['h1', c.h1, c.bg], ['lede', c.lede, c.bg], ['step', c.step, c.bg], ['table', c.td, c.tableBg]])
      assert.ok(contrast(color, bg) >= 4.5, `${name} ${color} on ${bg}: ${contrast(color, bg).toFixed(2)}`);
    await ctx.close();
  });

  await browser.close();
  console.log(`\n${passed} passed, ${failed} failed`);
  if (failed) process.exitCode = 1;
})().catch(e => { console.error(e); process.exitCode = 1; });
