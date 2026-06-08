/* ============================================================
   SoundStage Pro — Application JavaScript
   ============================================================ */

document.addEventListener('DOMContentLoaded', function () {

  /* ── Navbar scroll effect ──────────────────────────────── */
  const navbar = document.getElementById('mainNavbar');
  if (navbar) {
    const onScroll = () => {
      navbar.classList.toggle('scrolled', window.scrollY > 40);
    };
    window.addEventListener('scroll', onScroll, { passive: true });
  }

  /* ── Date input: enforce min = today ─────────────────── */
  const today = new Date().toISOString().split('T')[0];
  const startInput = document.getElementById('start_date');
  const endInput   = document.getElementById('end_date');

  if (startInput) {
    startInput.min = today;
    if (!startInput.value) startInput.value = today;

    startInput.addEventListener('change', function () {
      if (endInput) {
        endInput.min = this.value;
        if (endInput.value && endInput.value <= this.value) {
          // Set end date to start + 1 day
          const next = new Date(this.value);
          next.setDate(next.getDate() + 1);
          endInput.value = next.toISOString().split('T')[0];
        }
      }
    });
  }

  if (endInput) {
    if (startInput) {
      endInput.min = startInput.value || today;
    }
  }

  /* ── Equipment selection cards ────────────────────────── */
  initEquipmentCards();

  /* ── Quantity controls ────────────────────────────────── */
  // Delegated so it works for dynamically added content
  document.addEventListener('click', function (e) {
    const btn = e.target.closest('.qty-btn');
    if (!btn) return;
    const delta = btn.dataset.delta ? parseInt(btn.dataset.delta) : 0;
    const input = btn.closest('.qty-wrap')?.querySelector('.qty-val');
    if (!input) return;
    const max = parseInt(input.dataset.max || 99);
    let val = parseInt(input.value) || 1;
    val = Math.min(max, Math.max(1, val + delta));
    input.value = val;
  });

  /* ── Step 2 form validation ───────────────────────────── */
  const step2Form = document.getElementById('step2Form');
  if (step2Form) {
    step2Form.addEventListener('submit', function (e) {
      const checked = step2Form.querySelectorAll('.eq-checkbox:checked');
      if (checked.length === 0) {
        e.preventDefault();
        showAlert('step2Alert', 'Please select at least one equipment item to continue.');
        return;
      }
      // Make sure all selected items have quantity ≥ 1
      let qtyOk = true;
      checked.forEach(cb => {
        const eqId = cb.value;
        const qtyInput = document.getElementById('qty_' + eqId);
        if (qtyInput && (parseInt(qtyInput.value) || 0) < 1) qtyOk = false;
      });
      if (!qtyOk) {
        e.preventDefault();
        showAlert('step2Alert', 'Please ensure all selected items have a quantity of at least 1.');
      }
    });
  }

  /* ── Contact form feedback ────────────────────────────── */
  const contactForm = document.getElementById('contactForm');
  if (contactForm) {
    contactForm.addEventListener('submit', function (e) {
      e.preventDefault();
      const btn = contactForm.querySelector('[type="submit"]');
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending...';
      setTimeout(() => {
        contactForm.innerHTML = `
          <div class="text-center py-4">
            <div class="success-icon-ring mx-auto mb-3" style="width:70px;height:70px">
              <i class="bi bi-check-lg" style="font-size:2rem;color:var(--success)"></i>
            </div>
            <h5 class="fw-bold">Message Sent!</h5>
            <p class="text-muted mb-0">Thank you for contacting us. We'll get back to you within 1 business day.</p>
          </div>`;
      }, 1200);
    });
  }

  /* ── Smooth anchor scroll ─────────────────────────────── */
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      const target = document.querySelector(this.getAttribute('href'));
      if (target) {
        e.preventDefault();
        const navH = navbar ? navbar.offsetHeight : 70;
        const top  = target.getBoundingClientRect().top + window.scrollY - navH - 12;
        window.scrollTo({ top, behavior: 'smooth' });
      }
    });
  });

  /* ── Fade-in animation on scroll ─────────────────────── */
  if ('IntersectionObserver' in window) {
    const fadeEls = document.querySelectorAll('.fade-up');
    const observer = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.1 });
    fadeEls.forEach(el => observer.observe(el));
  }

});

/* ── Equipment card init (called on load and re-init) ──── */
function initEquipmentCards() {
  document.querySelectorAll('.eq-sel-card').forEach(card => {
    const checkbox = card.querySelector('.eq-checkbox');
    if (!checkbox) return;

    // Sync visual state on load
    syncCardState(card, checkbox.checked);

    card.addEventListener('click', function (e) {
      // Ignore clicks on qty controls
      if (e.target.closest('.qty-wrap') || e.target.tagName === 'INPUT') return;
      checkbox.checked = !checkbox.checked;
      syncCardState(this, checkbox.checked);
    });

    checkbox.addEventListener('change', function () {
      syncCardState(card, this.checked);
    });
  });
}

function syncCardState(card, selected) {
  card.classList.toggle('selected', selected);
  const checkEl = card.querySelector('.eq-sel-check');
  if (checkEl) checkEl.innerHTML = selected ? '<i class="bi bi-check-lg"></i>' : '';
}

/* ── Quantity helpers (global) ─────────────────────────── */
function changeQty(equipmentId, delta) {
  const input = document.getElementById('qty_' + equipmentId);
  if (!input) return;
  const max = parseInt(input.dataset.max || 99);
  let val = parseInt(input.value) || 1;
  val = Math.min(max, Math.max(1, val + delta));
  input.value = val;
}

/* ── Alert helper ─────────────────────────────────────── */
function showAlert(containerId, message) {
  const el = document.getElementById(containerId);
  if (!el) return;
  el.innerHTML = `<div class="alert-error d-flex align-items-center gap-2">
    <i class="bi bi-exclamation-circle-fill"></i>${message}
  </div>`;
  el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

/* ── Price formatter ───────────────────────────────────── */
function formatRupiah(amount) {
  return 'Rp ' + parseInt(amount).toLocaleString('id-ID');
}
