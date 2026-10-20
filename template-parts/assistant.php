<?php defined( 'ABSPATH' ) || exit; ?>
<div id="rbh-assistant" lang="en" hidden>
    <button class="rbha-bubble rbha-chat-toggle" type="button" aria-label="Open website assistant" aria-controls="rbha-chat" aria-expanded="false"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 4h14a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H9l-6 4V6a2 2 0 0 1 2-2Z"/><path d="M7 10h.01M12 10h.01M17 10h.01"/></svg><span class="screen-reader-text">Website assistant</span></button>
    <button class="rbha-bubble rbha-contact-toggle" type="button" aria-label="Open contact options" aria-controls="rbha-contact" aria-expanded="false"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 3h3l2 5-2 2a14 14 0 0 0 5 5l2-2 5 2v3a3 3 0 0 1-3 3C10 20 4 14 3 6a3 3 0 0 1 3-3Z"/></svg><span class="screen-reader-text">Contact options</span></button>
    <section id="rbha-contact" class="rbha-contact-panel" aria-label="Contact Nguyen Ha" hidden>
        <div class="rbha-panel-heading"><strong>Contact Nguyen Ha</strong><button type="button" data-rbha-close aria-label="Close contact options">×</button></div>
        <p>09:00–21:00 daily</p>
        <a href="tel:+84334699969"><span>Call</span><small>+84 334 699 969</small></a>
        <a href="https://zalo.me/0334699969" target="_blank" rel="noopener noreferrer"><span>Zalo</span><small>Open Zalo ↗</small></a>
        <a href="https://wa.me/84334699969" target="_blank" rel="noopener noreferrer"><span>WhatsApp</span><small>Open WhatsApp ↗</small></a>
        <a href="https://share.google/pJ2KSOJcrYDJYM42M" target="_blank" rel="noopener noreferrer"><span>Local map</span><small>View location ↗</small></a>
    </section>
    <dialog id="rbha-chat" class="rbha-chat" aria-labelledby="rbha-title" aria-describedby="rbha-description">
        <header class="rbha-panel-heading"><div><strong id="rbha-title">Nguyen Ha website assistant</strong><p id="rbha-description">Answers from this website · Information only</p></div><button type="button" data-rbha-close aria-label="Close website assistant">×</button></header>
        <div id="rbha-messages" class="rbha-messages" role="log" aria-live="polite" aria-relevant="additions" tabindex="0"><div class="rbha-message">Hello! Ask about reference prices, deposits, our location or the website’s guides. I can find information, but cannot book a bike or confirm availability.</div></div>
        <div class="rbha-suggestions" aria-label="Suggested questions"><button type="button" data-question="Rental prices">Prices</button><button type="button" data-question="Deposit requirements">Deposit</button><button type="button" data-question="Shop address and opening hours">Location</button><button type="button" data-question="Delivery information">Delivery</button><button type="button" data-question="50cc rental prices">50cc</button></div>
        <form class="rbha-form"><label class="screen-reader-text" for="rbha-question">Your question</label><input id="rbha-question" type="text" maxlength="400" placeholder="Ask a question…" autocomplete="off" enterkeyhint="send"><button type="submit" aria-label="Send question"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m3 3 18 9-18 9 4-9-4-9Zm4 9h14"/></svg></button></form>
        <p class="rbha-privacy">Runs locally. Messages are not sent or saved. No bookings.</p>
    </dialog>
</div>
