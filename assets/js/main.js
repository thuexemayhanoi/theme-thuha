(() => {
  'use strict';
  const root = document.documentElement;
  const colorMedia = window.matchMedia('(prefers-color-scheme: dark)');
  const darkSwitch = document.getElementById('rbh-dark-mode');
  const listenMedia = (media, listener) => media.addEventListener ? media.addEventListener('change', listener) : media.addListener(listener);
  function renderAppearance() {
    const preference = root.dataset.appearance || 'auto';
    const dark = preference === 'auto' ? colorMedia.matches : preference === 'dark';
    root.dataset.theme = dark ? 'dark' : 'light';
    if (darkSwitch) darkSwitch.checked = dark;
  }
  darkSwitch?.addEventListener('change', () => {
    root.dataset.appearance = darkSwitch.checked ? 'dark' : 'light';
    try { localStorage.setItem('rbh-appearance', root.dataset.appearance); } catch (_) { /* Persistence is optional. */ }
    renderAppearance();
  });
  listenMedia(colorMedia, renderAppearance);
  window.addEventListener('storage', event => {
    if (event.key === 'rbh-appearance') {
      root.dataset.appearance = ['light', 'dark', 'auto'].includes(event.newValue) ? event.newValue : 'auto';
      renderAppearance();
    }
  });
  renderAppearance();

  const menu = document.getElementById('site-menu');
  const triggers = document.querySelectorAll('.menu-trigger');
  let opened = false;
  let returnFocus;
  let previousInert = [];
  if (menu) {
    const nativePopover = typeof menu.showPopover === 'function';
    function syncMenu(open) {
      if (opened === open) return;
      opened = open;
      if (open) {
        previousInert = Array.from(document.body.children)
          .filter(el => el !== menu && !['SCRIPT', 'STYLE'].includes(el.tagName))
          .map(el => ({ el, inert: el.inert }));
        previousInert.forEach(({ el }) => { el.inert = true; });
        menu.setAttribute('aria-modal', 'true');
        requestAnimationFrame(() => menu.querySelector('.menu-close')?.focus({ preventScroll: true }));
      } else {
        previousInert.forEach(({ el, inert }) => { el.inert = inert; });
        previousInert = [];
        menu.removeAttribute('aria-modal');
        returnFocus?.focus({ preventScroll: true });
      }
      document.body.classList.toggle('menu-open', open);
      triggers.forEach(trigger => trigger.setAttribute('aria-expanded', String(open)));
    }
    function closeMenu() {
      if (nativePopover) { if (menu.matches(':popover-open')) menu.hidePopover(); }
      else { menu.classList.remove('is-open'); syncMenu(false); }
    }
    triggers.forEach(trigger => trigger.addEventListener('click', () => {
      returnFocus = trigger;
      if (!nativePopover) { menu.classList.add('is-open'); syncMenu(true); }
    }));
    if (nativePopover) menu.addEventListener('beforetoggle', event => syncMenu(event.newState === 'open'));
    else {
      root.classList.add('rbh-popover-fallback');
      menu.querySelector('.menu-close')?.addEventListener('click', closeMenu);
    }
    document.addEventListener('keydown', event => {
      if (!opened) return;
      if (event.key === 'Escape') { closeMenu(); return; }
      if (event.key === 'Tab') {
        const items = Array.from(menu.querySelectorAll('a[href],button,input,select,textarea,[tabindex="0"]'))
          .filter(el => !el.disabled && el.getClientRects().length);
        const first = items[0], last = items[items.length - 1];
        if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last?.focus(); }
        if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first?.focus(); }
      }
    });
    menu.addEventListener('click', event => {
      if (event.target.closest('a[href]')) closeMenu();
    });
    window.addEventListener('pagehide', closeMenu);
  }

  const smallFooter = window.matchMedia('(max-width: 760px)');
  function syncFooter() { document.querySelectorAll('.footer-group').forEach(group => { group.open = !smallFooter.matches; }); }
  listenMedia(smallFooter, syncFooter);
  syncFooter();
  document.querySelectorAll('.entry-content table').forEach(table => {
    if (table.closest('.wp-block-table,.rate-table,.table-scroll')) return;
    const wrapper = document.createElement('div');
    wrapper.className = 'table-scroll';
    table.parentNode.insertBefore(wrapper, table);
    wrapper.appendChild(table);
  });
  function revealAnchor() {
    let id;
    try { id = decodeURIComponent(location.hash.slice(1)); } catch (_) { return; }
    const target = id && document.getElementById(id);
    const section = target && (target.matches('.source-section') ? target : target.closest('.source-section'));
    if (section) { section.open = true; target.scrollIntoView(); }
  }
  window.addEventListener('hashchange', revealAnchor);
  revealAnchor();
})();
