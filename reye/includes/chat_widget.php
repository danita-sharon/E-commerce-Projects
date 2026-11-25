<?php // Reye AI chat bubble, shown on every customer page (included by footer.php) ?>
<div class="ai" id="reyeAI">
  <button class="ai-launch" id="aiLaunch" aria-label="Chat with Reye AI" aria-expanded="false">
    <svg class="logo"><use href="#reye-logo"/></svg>
    <span>Reye AI</span>
  </button>

  <section class="ai-panel" id="aiPanel" aria-label="Reye AI chat" hidden>
    <header class="ai-head">
      <div class="ai-avatar"><svg class="logo"><use href="#reye-logo"/></svg></div>
      <div><strong>Reye AI</strong><small><span class="ai-dot"></span> Replies instantly</small></div>
      <button class="ai-close" id="aiClose" aria-label="Close chat"><i class="ti ti-x"></i></button>
    </header>
    <div class="ai-log" id="aiLog" aria-live="polite"></div>
    <div class="ai-chips" id="aiChips"></div>
    <form class="ai-form" id="aiForm" autocomplete="off">
      <label class="sr-only" for="aiInput">Message</label>
      <input id="aiInput" maxlength="500" placeholder="Ask about sizes, delivery, anything">
      <button type="submit" aria-label="Send"><i class="ti ti-send"></i></button>
    </form>
    <p class="ai-foot">Reye AI can make mistakes. For orders, the team is on <a href="<?= e(whatsapp_link('Hi Reye, I have a question.')) ?>" target="_blank" rel="noopener">WhatsApp</a>.</p>
  </section>
</div>
