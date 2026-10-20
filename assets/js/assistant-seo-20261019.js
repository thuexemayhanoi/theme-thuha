/* Original, dependency-free website lookup and floating UI. Same-origin search on demand; no chat storage. */
(() => {
  'use strict';
  const root = document.getElementById('rbh-assistant');
  if (!root) return;
  root.querySelector('.rbha-privacy').textContent = 'Guide searches use this website. No external AI service or booking actions.';
  const chat = root.querySelector('#rbha-chat'), contact = root.querySelector('#rbha-contact');
  const chatButton = root.querySelector('.rbha-chat-toggle'), contactButton = root.querySelector('.rbha-contact-toggle');
  const input = root.querySelector('#rbha-question'), log = root.querySelector('#rbha-messages');
  const dock = document.querySelector('.contact-dock');
  let scheduled = false, lastFocus, asking = false;
  const searchCache = new Map();
  const normalize = s => String(s).normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().replace(/đ/g, 'd');
  const stop = new Set('a an the i me my we you your can could do does is are am it this that on in at to for of and or with what which how where when about please tell want need hanoi motorbike rental bike'.split(' '));
  const tokens = s => normalize(s).match(/[a-z0-9]+/g)?.filter(w => w.length > 2 && !stop.has(w)) || [];
  const bookingReply = { text: 'I cannot take bookings, reserve a bike, accept payment or confirm live availability. Please use the contact button to speak directly with Nguyen Ha. Do not enter payment or identity details here.' };
  async function answer(question) {
    const q = normalize(question);
    if (/\b(extension|extend|overtime|late fee)\b|gia han/.test(q)) return {text: 'Hourly extensions cost VND 20,000 per hour. An extension of more than 6 hours is charged as one rental day. Contact Nguyen Ha to agree on the revised return time.'};
    if (/\b(book(?:ing)?|reserv(?:e|ation)|confirm|available|availability|pay(?:ment)?|credit card|passport number|order)\b|dat xe|giu xe|giu cho|thanh toan|con xe/.test(q)) return bookingReply;
    if (/\S+@\S+\.\S+|(?:\+?\d[\s().-]*){9,}/.test(q)) return {text: 'Please do not enter personal details. This assistant only answers general questions and cannot process bookings.'};
    if (/\b(price|prices|rate|rates|cost|weekly|monthly|daily|vision|air blade|50cc|electric|scooter|semi.automatic)\b|bang gia|gia thue/.test(q)) return {text: 'Reference prices in VND — confirm the model and quote before renting.\n\nSemi-automatic: 150,000–200,000/day · 700,000–800,000/week · 1–1.2 million/month.\nScooter: 150,000–200,000/day · 600,000–1 million/week · 1.2–2 million/month.\nElectric: daily price depends on the model · 600,000–800,000/week · 1.5–1.8 million/month.\n50cc or Vision: 200,000/day · 1 million/week · 2 million/month.\nAir Blade: 200,000/day · 900,000/week · 1.5 million/month.\n\nThese are reference prices, not an availability or booking confirmation.'};
    if (/\b(address|location|map|hours|open|phone|contact|zalo|whatsapp)\b|dia chi|gio mo/.test(q)) return {text: 'Thuê xe máy Nguyễn Hà\nNgõ 5 Nguyễn Văn Cừ, Bồ Đề, Long Biên, Hà Nội (near Gia Lam Bus Station).\nOpen 09:00–21:00 daily.\nPhone / Zalo / WhatsApp: +84 334 699 969.\n\nUse the contact button for Call, Zalo, WhatsApp and the shop’s local map.'};
    if (/\b(deposit|refund|passport|document|license|licence|age|insurance|damage|fuel|late|early|delivery|deliver|airport|pickup|pick.up|handover|policy|policies|return)\b|tien coc|giao xe|giay to/.test(q)) return {text: 'Please confirm the requirements and handover details directly with Nguyen Ha for your situation. A deposit may be required; no deposit-free offer is assumed. The shop does not deliver to Noi Bai Airport. I cannot verify individual eligibility, promise refunds or confirm a delivery. Use the contact button for an accurate answer.'};
    if (/^(hi|hello|hey|thanks|thank you)[!.\s]*$/.test(q)) return {text: 'Hello! I can help with reference prices, our location and information in the website’s guides. What would you like to know?'};
    const terms = [...new Set(tokens(q))].slice(0,18);
    if (!terms.length) return {text:'Ask about prices, the shop location or a topic from our website. I cannot make bookings.'};
    const search = terms.slice(0,6).join(' ');
    let index = searchCache.get(search);
    if (!index) {
      try {
        const apiRoot = document.querySelector('link[rel="https://api.w.org/"]')?.href || location.origin+'/wp-json/';
        const url = new URL('wp/v2/search',apiRoot.endsWith('/')?apiRoot:apiRoot+'/');
        if (url.origin !== location.origin) throw new Error('Invalid search endpoint');
        url.searchParams.set('search',search);
        url.searchParams.set('type','post');
        url.searchParams.set('subtype','post');
        url.searchParams.set('per_page','12');
        const response = await fetch(url,{credentials:'omit',signal:AbortSignal.timeout(8000)});
        if (!response.ok) throw new Error('Search unavailable');
        index = await response.json();
        if (!Array.isArray(index)) throw new Error('Invalid search response');
        index = index.map(row => ({...row,heading:'',text:'Open this website guide for the full information.',link_only:true})).filter(row => {
          try { const u = new URL(row.url); return u.origin === location.origin && u.protocol === 'https:' && typeof row.text === 'string' && typeof row.title === 'string'; } catch (_) { return false; }
        }).map(row => ({...row, words: new Set(tokens(row.title+' '+row.heading+' '+row.text)), titleWords: new Set(tokens(row.title+' '+row.heading))}));
        if (searchCache.size >= 12) searchCache.delete(searchCache.keys().next().value);
        searchCache.set(search,index);
      } catch (_) { return {text:'Website search is temporarily unavailable. Use the site search or contact Nguyen Ha for help.'}; }
    }
    const ranked = index.map(row => {
      const hits = terms.filter(w => row.words.has(w));
      return {row, hits:hits.length, score:hits.reduce((n,w)=>n+(row.titleWords.has(w)?4:1),0)};
    }).sort((a,b)=>b.score-a.score);
    if (!ranked.length) return {text:'I could not find a clear answer in the website’s public information. Try a specific place or topic, or use the contact button. I will not guess.'};
    const top = ranked[0].row;
    if (top.link_only) return {text:'I found a related guide. Open the source for the full information.', sources:[{title:'Read the source guide',url:top.url}]};
    return {text:'From “'+top.title+'”:\n\n'+top.text, sources:[{title:'Read the source guide',url:top.url}]};
  }
  function append(text, user = false, sources = []) {
    const bubble = document.createElement('div'); bubble.className = 'rbha-message'+(user?' rbha-message-user':'');
    const p = document.createElement('p'); p.textContent = text; bubble.append(p);
    sources.forEach(source => { const a = document.createElement('a'); a.href = source.url; a.textContent = source.title; bubble.append(a); });
    log.append(bubble);
    while (log.children.length > 40) log.firstElementChild.remove();
    log.scrollTop = log.scrollHeight;
  }
  async function ask(question) {
    if (asking) return;
    const q = question.trim().slice(0,400); if (!q) return;
    // Do not echo identity/payment details into the conversation.
    if (/\S+@\S+\.\S+|(?:\+?\d[\s().-]*){9,}/.test(q)) { append('Please do not enter personal or payment details. Ask a general question instead.'); input.value = ''; return; }
    asking = true; input.value = ''; append(q,true); log.setAttribute('aria-busy','true');
    try { const result = await answer(q); append(result.text,false,result.sources || []); }
    finally { asking = false; log.removeAttribute('aria-busy'); }
  }
  root.querySelector('.rbha-form').addEventListener('submit', event => {event.preventDefault();ask(input.value);});
  root.querySelectorAll('[data-question]').forEach(button => button.addEventListener('click',()=>ask(button.dataset.question)));
  function closeContact(restore = false) {contact.hidden = true;contactButton.setAttribute('aria-expanded','false');if(restore)contactButton.focus({preventScroll:true});}
  function closeChat() { if(chat.open)chat.close(); }
  chatButton.addEventListener('click',()=>{
    closeContact(); lastFocus = chatButton; layout();
    if (typeof chat.showModal !== 'function') {location.href = '/?s='+encodeURIComponent('rental information'); return;}
    chat.showModal();root.classList.add('rbha-chat-open');chatButton.setAttribute('aria-expanded','true');schedule();
    // Focus the heading close button first; do not open the mobile keyboard automatically.
    chat.querySelector('[data-rbha-close]').focus({preventScroll:true});
  });
  contactButton.addEventListener('click',()=>{if(!contact.hidden){closeContact(true);return;}closeChat();contact.hidden=false;contactButton.setAttribute('aria-expanded','true');schedule();});
  chat.querySelector('[data-rbha-close]').addEventListener('click',closeChat);
  contact.querySelector('[data-rbha-close]').addEventListener('click',()=>closeContact(true));
  chat.addEventListener('close',()=>{root.classList.remove('rbha-chat-open');chatButton.setAttribute('aria-expanded','false');if(!document.body.classList.contains('menu-open'))lastFocus?.focus({preventScroll:true});schedule();});
  document.addEventListener('keydown',event=>{if(event.key==='Escape'&&!contact.hidden){event.preventDefault();closeContact(true);}});
  document.addEventListener('pointerdown',event=>{if(!contact.hidden&&!contact.contains(event.target)&&!contactButton.contains(event.target))closeContact();});
  document.addEventListener('focusin',event=>{if(!contact.hidden&&!contact.contains(event.target)&&event.target!==contactButton)closeContact();schedule();});
  const visible = el => {const s=getComputedStyle(el),r=el.getBoundingClientRect();return s.display!=='none'&&s.visibility!=='hidden'&&Number(s.opacity)!==0&&r.width>0&&r.height>0;};
  const intersects = (a,b) => a.left < b.right+10 && a.right > b.left-10 && a.top < b.bottom+10 && a.bottom > b.top-10;
  function layout() {
    scheduled = false;
    const viewport = window.visualViewport;
    const top = viewport?.offsetTop || 0, height = viewport?.height || window.innerHeight;
    const bottom = top+height, edge = window.innerWidth <= 380?12:16;
    const header = document.querySelector('.site-header')?.getBoundingClientRect();
    const keyboard = chat.contains(document.activeElement) && height < window.innerHeight * .78;
    const ceiling = keyboard ? top+8 : Math.max(top+12, header && header.bottom>top && header.top<bottom ? header.bottom+12 : top+12);
    let floor = bottom-12;
    if(dock&&visible(dock)){const r=dock.getBoundingClientRect();if(r.top>top && r.top<bottom)floor=Math.min(floor,r.top-12);}
    // Known floating controls plus bottom fixed overlays; callers may mark custom obstacles.
    const candidates = new Set([...document.querySelectorAll('[data-rbh-obstacle],.cookie-notice,.cookie-banner,#cookie-notice,.cmplz-cookiebanner,#motoq4-wrap,body > aside,body > div,body > iframe')]);
    const obstacles=[];
    candidates.forEach(el=>{if(el===root||el.contains(root)||el===dock||root.contains(el)||!visible(el))return;const s=getComputedStyle(el),r=el.getBoundingClientRect();if(s.position==='fixed'&&r.bottom>top+height*.5&&r.top<bottom)obstacles.push(r);});
    const covered = document.body.classList.contains('menu-open') || Array.from(document.querySelectorAll('dialog[open]')).some(el=>el!==chat);
    root.classList.toggle('rbha-tools-covered',covered);
    if(covered){closeContact();closeChat();}
    [chatButton,contactButton].forEach((button,i)=>{
      const left=i?window.innerWidth-edge-52:edge;let y=floor-52;
      for(let pass=0;pass<obstacles.length+1;pass++){
        const hit=obstacles.find(r=>intersects({left,right:left+52,top:y,bottom:y+52},r));if(!hit)break;y=hit.top-64;
      }
      button.style.bottom=Math.max(12,window.innerHeight-y-52)+'px';
      button.style.display=y<ceiling?'none':'';
    });
    const chatFloor=obstacles.reduce((f,r)=>r.left<Math.min(440,window.innerWidth-10)&&r.right>10?Math.min(f,r.top-12):f,floor);
    const room=Math.max(0,chatFloor-ceiling);
    chat.style.setProperty('--rbha-chat-bottom',Math.max(0,window.innerHeight-chatFloor)+'px');
    chat.style.setProperty('--rbha-chat-height',Math.min(620,room)+'px');
    // Small keyboard viewports retain composer space; messages scroll inside the remaining area.
    chat.classList.toggle('rbha-compact',room<350);
    const cr=contactButton.getBoundingClientRect();
    contact.style.bottom=Math.max(12,window.innerHeight-cr.top+12)+'px';
    contact.style.maxHeight=Math.max(80,cr.top-ceiling-12)+'px';
  }
  function schedule(){if(!scheduled){scheduled=true;requestAnimationFrame(layout);}}
  window.addEventListener('resize',schedule,{passive:true});window.addEventListener('scroll',schedule,{passive:true});
  window.visualViewport?.addEventListener('resize',schedule,{passive:true});window.visualViewport?.addEventListener('scroll',schedule,{passive:true});
  if(window.ResizeObserver&&dock)new ResizeObserver(schedule).observe(dock);
  new MutationObserver(schedule).observe(document.body,{attributes:true,attributeFilter:['class']});
  document.querySelector('#site-menu')?.addEventListener('toggle',schedule);
  document.querySelector('#rbh-quick-search')?.addEventListener('toggle',schedule);
  const observer=new MutationObserver(records=>{if(records.some(r=>!root.contains(r.target)))schedule();});
  observer.observe(document.body,{childList:true,subtree:true,attributes:true,attributeFilter:['style','class','hidden','open']});
  window.addEventListener('load',schedule);root.hidden=false;schedule();
})();
