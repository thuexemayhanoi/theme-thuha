(() => {
  'use strict';
  const config = window.rbhUpgrade || {};
  const dialog = document.getElementById('rbh-quick-search');
  let searchReturn;
  if (dialog && typeof dialog.showModal === 'function') {
    document.querySelectorAll('[data-rbh-search]').forEach(trigger => {
      trigger.addEventListener('click', event => {
        event.preventDefault();
        searchReturn = trigger;
        if (!dialog.open) dialog.showModal();
        dialog.querySelector('input[type="search"]')?.focus();
      });
    });
    dialog.querySelector('[data-rbh-search-close]')?.addEventListener('click', () => dialog.close());
    dialog.addEventListener('close', () => searchReturn?.focus());
    dialog.addEventListener('click', event => {
      if (event.target !== dialog) return;
      const r = dialog.getBoundingClientRect();
      if (event.clientX < r.left || event.clientX > r.right || event.clientY < r.top || event.clientY > r.bottom) dialog.close();
    });
  }
  document.querySelectorAll('.rbh-share').forEach(panel => {
    const status = panel.querySelector('.rbh-copy-status');
    const share = panel.querySelector('[data-rbh-share]');
    if (navigator.share) {
      share.hidden = false;
      share.addEventListener('click', async () => {
        try { await navigator.share({ title: panel.dataset.shareTitle, url: panel.dataset.shareUrl }); }
        catch (e) { if (e.name !== 'AbortError') status.textContent = config.copyFailed || 'Copy this link manually:'; }
      });
    }
    const copy = panel.querySelector('[data-rbh-copy]');
    copy.hidden = false;
    copy.addEventListener('click', async () => {
      try {
        if (!navigator.clipboard?.writeText) throw new Error('Clipboard unavailable');
        await navigator.clipboard.writeText(panel.dataset.shareUrl);
        status.textContent = config.copied || 'Link copied';
      } catch (_) {
        const fallback = panel.querySelector('.rbh-copy-fallback');
        fallback.hidden = false;
        fallback.focus(); fallback.select();
        status.textContent = config.copyFailed || 'Copy this link manually:';
      }
    });
  });
  const article = document.querySelector('body.single .entry-content');
  const progress = document.querySelector('.rbh-reading-progress span');
  const dock = document.querySelector('.contact-dock');
  let pending = false;
  let widgets = [];
  const widgetObserver = new MutationObserver(() => schedule());
  const findWidgets = () => {
    widgets = Array.from(document.querySelectorAll('#motoq4-wrap, #chatbase-bubble-button, #chatbase-bubble-window, #tawkchat-container, .crisp-client, iframe[title*="chat" i], [data-rbh-floating]'));
    widgetObserver.disconnect();
    widgets.forEach(widget => widgetObserver.observe(widget, { attributes: true, subtree: true, attributeFilter: ['style', 'class', 'hidden'] }));
  };
  findWidgets();
  const overlaps = (a, b) => a.left < b.right && a.right > b.left && a.top < b.bottom && a.bottom > b.top;
  function update() {
    pending = false;
    if (article && progress) {
      const rect = article.getBoundingClientRect();
      const travel = rect.height - window.innerHeight;
      const value = travel > 0 ? Math.max(0, Math.min(1, -rect.top / travel)) : (rect.bottom <= window.innerHeight ? 1 : 0);
      progress.style.transform = `scaleX(${value})`;
    }
    if (dock && config.floating) {
      const rect = dock.getBoundingClientRect();
      const viewport = window.visualViewport;
      const keyboard = viewport && viewport.height < window.innerHeight * .75 && /INPUT|TEXTAREA|SELECT/.test(document.activeElement?.tagName || '');
      const blocked = widgets.some(widget => {
        const style = getComputedStyle(widget);
        if (style.display === 'none' || style.visibility === 'hidden' || parseFloat(style.opacity) === 0) return false;
        const r = widget.getBoundingClientRect();
        return r.width > 0 && r.height > 0 && overlaps(rect, r);
      });
      dock.classList.toggle('rbh-dock-paused', Boolean(keyboard || blocked || document.body.classList.contains('menu-open') || dialog?.open));
    }
  }
  function schedule() { if (!pending) { pending = true; requestAnimationFrame(update); } }
  window.addEventListener('scroll', schedule, { passive: true });
  window.addEventListener('resize', schedule, { passive: true });
  window.visualViewport?.addEventListener('resize', schedule, { passive: true });
  document.addEventListener('focusin', schedule);
  document.addEventListener('focusout', schedule);
  if (dialog) { dialog.addEventListener('close', schedule); dialog.addEventListener('toggle', schedule); }
  new MutationObserver(() => { findWidgets(); schedule(); }).observe(document.body, { childList: true, subtree: true });
  new MutationObserver(schedule).observe(document.body, { attributes: true, attributeFilter: ['class'] });
  if (window.ResizeObserver && article) new ResizeObserver(schedule).observe(article);
  window.addEventListener('load', schedule);
  schedule();
})();
