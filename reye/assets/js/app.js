/* =========================================================
   REYE - Site interactions
   Search, product cards, bag, wishlist, filters, reviews
   ========================================================= */

const BASE = document.body.dataset.base;
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
const $ = (sel, root = document) => root.querySelector(sel);
const $$ = (sel, root = document) => [...root.querySelectorAll(sel)];

/* ---------- Helpers ---------- */

// Send form data to one of our api/ files and get JSON back
async function post(path, data) {
  const res = await fetch(`${BASE}/${path}`, {
    method: 'POST',
    headers: { 'X-CSRF-Token': CSRF },
    body: new URLSearchParams({ ...data, csrf_token: CSRF }),
  });
  const json = await res.json().catch(() => ({ ok: false, message: 'Something went wrong. Try again.' }));
  json.status = res.status;
  return json;
}

// Small message at the bottom of the screen
let toastTimer;
function toast(message) {
  const el = $('#toast');
  el.textContent = message;
  el.classList.add('show');
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => el.classList.remove('show'), 2200);
}

// Escape text before putting it into HTML
function esc(str) {
  return String(str).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
}

/* ---------- Announcement bar ---------- */
const announcements = [
  'Free delivery in Accra on orders over GH₵ 800 · Worldwide shipping',
  'Every pair is handmade in Ghana',
  'Bespoke orders ready in 7 to 10 days',
];
const announceEl = $('#announce');
if (announceEl) {
  let a = 0;
  setInterval(() => {
    announceEl.style.opacity = 0;
    setTimeout(() => {
      a = (a + 1) % announcements.length;
      announceEl.textContent = announcements[a];
      announceEl.style.opacity = 1;
    }, 300);
  }, 4000);
}

/* ---------- Mobile menu ---------- */
$('#menuBtn')?.addEventListener('click', () => $('#mobileMenu').classList.toggle('open'));

/* ---------- Live search ---------- */
const searchPanel = $('#searchPanel');
const searchInput = $('#searchInput');
const searchResults = $('#searchResults');
let searchTimer;

$('#searchBtn')?.addEventListener('click', () => {
  searchPanel.classList.toggle('open');
  if (searchPanel.classList.contains('open')) searchInput.focus();
});

searchInput?.addEventListener('input', () => {
  clearTimeout(searchTimer);
  const q = searchInput.value.trim();
  if (q.length < 2) { searchResults.innerHTML = ''; return; }
  searchTimer = setTimeout(async () => {
    const res = await fetch(`${BASE}/api/search.php?q=${encodeURIComponent(q)}`);
    const items = await res.json();
    searchResults.innerHTML = items.length
      ? items.map(i => `<a href="${esc(i.url)}"><span>${esc(i.name)}</span><span>${esc(i.price)}</span></a>`).join('')
      : '<p class="muted">No matches. Try "crimson" or "pouch".</p>';
  }, 250);
});

/* ---------- Product cards ---------- */

// Same drawing as product_art() in PHP, used when switching colours
function productArt(type, main, accent) {
  if (type === 'pouches') {
    return `<svg class="art" viewBox="0 0 120 100" aria-hidden="true"><rect x="15" y="25" width="90" height="62" rx="6" fill="${accent}"/><path d="M15 31Q60 70 105 31V28Q60 58 15 28Z" fill="${main}"/><circle cx="60" cy="52" r="4" fill="#c9a24b"/></svg>`;
  }
  return `<svg class="art" viewBox="0 0 120 120" aria-hidden="true"><path d="M60 8C38 8 32 40 33 62C34 95 42 112 60 112C78 112 86 95 87 62C88 40 82 8 60 8Z" fill="#3a332d"/><path d="M28 42L92 70V86L28 58Z" fill="${main}"/><path d="M92 42L28 70V86L92 58Z" fill="${accent}"/></svg>`;
}

document.addEventListener('click', async (e) => {
  const card = e.target.closest('.card');

  // Pick a colour
  const swatch = e.target.closest('.swatch');
  if (swatch && card) {
    $$('.swatch', card).forEach(s => s.classList.remove('on'));
    swatch.classList.add('on');
    $('.swatch-name', card).textContent = swatch.dataset.name;
    const photo = $('.card-photo', card);
    const name = $('.card-title a', card).textContent;
    photo.innerHTML = swatch.dataset.image
      ? `<img src="${esc(swatch.dataset.image)}" alt="${esc(name + ' in ' + swatch.dataset.name)}">`
      : productArt(card.dataset.type, swatch.dataset.main, swatch.dataset.accent);
    return;
  }

  // Pick a size
  const size = e.target.closest('.size');
  if (size && card) {
    $$('.size', card).forEach(s => s.classList.remove('on'));
    size.classList.add('on');
    $('.error-text', card).classList.remove('show');
    return;
  }

  // Add to bag
  const addBtn = e.target.closest('.add-btn');
  if (addBtn && card) {
    const chosenSize = $('.size.on', card);
    if (!chosenSize) { $('.error-text', card).classList.add('show'); return; }
    const chosenColour = $('.swatch.on', card);
    addBtn.disabled = true;
    const res = await post('api/cart.php', {
      action: 'add',
      product_id: card.dataset.id,
      colour_id: chosenColour ? chosenColour.dataset.colour : 0,
      size: chosenSize.dataset.size,
      qty: 1,
    });
    addBtn.disabled = false;
    if (res.ok) {
      $('#bagCount').textContent = res.count;
      addBtn.textContent = 'Added';
      setTimeout(() => (addBtn.textContent = 'Add to bag'), 1400);
      toast(`${$('.card-title a', card).textContent} added to your bag`);
    } else {
      toast(res.message || "Couldn't add to bag. Try again.");
    }
    return;
  }

  // Save to wishlist
  const wishBtn = e.target.closest('.wish-btn');
  if (wishBtn) {
    if (document.body.dataset.loggedIn !== '1') {
      toast('Log in to save your favourites');
      setTimeout(() => (window.location = `${BASE}/login.php`), 1200);
      return;
    }
    const res = await post('api/wishlist.php', { product_id: wishBtn.dataset.wish });
    if (res.ok) {
      wishBtn.classList.toggle('on', res.saved);
      $('#wishCount').textContent = res.count || '';
      toast(res.saved ? 'Saved to your wishlist' : 'Removed from your wishlist');
    } else {
      toast(res.message || 'Something went wrong. Try again.');
    }
  }
});

/* ---------- Filters and sorting ---------- */
const grid = $('#productGrid');
let currentFilter = new URLSearchParams(location.search).get('cat') || 'all';

function applyFilter() {
  let shown = 0;
  $$('.card', grid).forEach(card => {
    const show = currentFilter === 'all' || card.dataset.type === currentFilter;
    card.hidden = !show;
    if (show) shown++;
  });
  const empty = $('#emptyNote');
  if (empty) empty.hidden = shown > 0;
}

$('#filters')?.addEventListener('click', (e) => {
  const btn = e.target.closest('button');
  if (!btn) return;
  $$('#filters button').forEach(b => b.classList.toggle('active', b === btn));
  currentFilter = btn.dataset.filter;
  applyFilter();
});

$('#sortSelect')?.addEventListener('change', (e) => {
  const cards = $$('.card', grid);
  const key = e.target.value;
  cards.sort((a, b) => {
    if (key === 'low') return a.dataset.price - b.dataset.price;
    if (key === 'high') return b.dataset.price - a.dataset.price;
    return a.dataset.order - b.dataset.order;
  });
  cards.forEach(c => grid.insertBefore(c, $('#emptyNote')));
});

if (grid && currentFilter !== 'all') {
  $$('#filters button').forEach(b => b.classList.toggle('active', b.dataset.filter === currentFilter));
  applyFilter();
}

/* ---------- Size guide ---------- */
$('#sizeGuideBtn')?.addEventListener('click', () => $('#sizeGuide').classList.toggle('open'));
if (location.hash === '#size-guide') $('#sizeGuide')?.classList.add('open');

/* ---------- Reviews slider ---------- */
if (window.REVIEWS && window.REVIEWS.length > 1) {
  let r = 0;
  const show = (step) => {
    r = (r + step + REVIEWS.length) % REVIEWS.length;
    const text = $('#reviewText');
    text.style.opacity = 0;
    setTimeout(() => {
      text.textContent = `"${REVIEWS[r].text}"`;
      $('#reviewBy').textContent = REVIEWS[r].by;
      $('#revCount').textContent = `${r + 1} / ${REVIEWS.length}`;
      text.style.opacity = 1;
    }, 250);
  };
  $('#revPrev').addEventListener('click', () => show(-1));
  $('#revNext').addEventListener('click', () => show(1));
}

/* ---------- Newsletter ---------- */
$('#newsletterForm')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  const input = $('#nlEmail');
  const msg = $('#nlMsg');
  if (!/^\S+@\S+\.\S+$/.test(input.value.trim())) {
    msg.style.color = 'var(--danger)';
    msg.textContent = 'Enter a valid email address.';
    return;
  }
  const res = await post('api/newsletter.php', { email: input.value.trim() });
  msg.style.color = res.ok ? 'var(--success)' : 'var(--danger)';
  msg.textContent = res.message;
  if (res.ok) input.value = '';
});
$('#nlEmail')?.addEventListener('input', () => ($('#nlMsg').textContent = ''));

/* ---------- Fade sections in as you scroll ---------- */
if ('IntersectionObserver' in window) {
  const io = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
        io.unobserve(entry.target);
      }
    });
  }, { threshold: 0.12 });
  $$('.reveal').forEach(el => io.observe(el));
} else {
  $$('.reveal').forEach(el => el.classList.add('visible'));
}
