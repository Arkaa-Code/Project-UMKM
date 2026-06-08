<?php
$page_title   = 'Contact Us — RZKY Equipment Services';
$current_page = 'contact';

require_once '../src/includes/header.php';
require_once '../src/includes/navbar.php';
?>

<!-- ════════════════════════════════════════════════════════
     PAGE HERO
     ════════════════════════════════════════════════════════ -->
<section class="page-hero">
  <div class="container">
    <nav class="breadcrumb-nav" aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
        <li class="breadcrumb-item active">Contact</li>
      </ol>
    </nav>
    <h1 class="page-hero-title">Get In Touch</h1>
    <p class="page-hero-sub">We're ready to help you plan the perfect event equipment setup.</p>
  </div>
</section>

<!-- ════════════════════════════════════════════════════════
     CONTACT CONTENT
     ════════════════════════════════════════════════════════ -->
<section class="section-pad">
  <div class="container">
    <div class="row g-5">

      <!-- ── Contact Info Column ── -->
      <div class="col-lg-5">
        <span class="section-tag">Contact Information</span>
        <h3 class="section-title mb-4">Let's Talk About Your Event</h3>

        <?php
        $contact_items = [
            ['bi-telephone-fill',  'Phone',         '+62 812-3456-7890',                         'Call us Mon–Sat, 08:00–20:00 WIB'],
            ['bi-whatsapp',        'WhatsApp',       '+62 812-3456-7890',                         'Message us anytime, we reply fast'],
            ['bi-envelope-fill',   'Email',          'info@rzrental.id',                     'We respond within 1 business day'],
            ['bi-geo-alt-fill',    'Office Address', 'Jalan Bibit Unggul No.10, Sorido, Kec. Biak Kota, Kab. Biak Numfor, Papua 98113',  'DKI Biak, Indonesia'],
            ['bi-clock-fill',      'Business Hours', 'Monday – Saturday: 08:00 – 20:00 WIB',     'Closed on public holidays'],
        ];
        foreach ($contact_items as $ci): ?>
        <div class="contact-info-card mb-3">
          <div class="ci-icon"><i class="bi <?= $ci[0] ?>"></i></div>
          <div>
            <p class="ci-title"><?= $ci[1] ?></p>
            <p class="ci-val"><?= htmlspecialchars($ci[2]) ?></p>
            <p class="ci-val" style="font-size:0.78rem;margin-top:0.1rem"><?= $ci[3] ?></p>
          </div>
        </div>
        <?php endforeach; ?>

        <!-- Social Media -->
        <div class="mt-4">
          <p class="fw-bold mb-2" style="font-size:0.88rem">Follow Us</p>
          <div style="display:flex;gap:0.65rem">
            <?php
            $socials = [
                ['bi-instagram','#e1306c','Instagram'],
                ['bi-facebook', '#1877f2','Facebook'],
                ['bi-whatsapp', '#25d366','WhatsApp'],
                ['bi-youtube',  '#ff0000','YouTube'],
                ['bi-tiktok',   '#000000','TikTok'],
            ];
            foreach ($socials as $soc): ?>
            <a href="#" title="<?= $soc[2] ?>"
               style="width:40px;height:40px;border-radius:9px;background:<?= $soc[1] ?>;color:#fff;display:flex;align-items:center;justify-content:center;font-size:1rem;transition:all 0.2s"
               onmouseover="this.style.transform='translateY(-3px)'"
               onmouseout="this.style.transform='translateY(0)'">
              <i class="bi <?= $soc[0] ?>"></i>
            </a>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Quick link to reservation -->
        <div class="mt-4 p-3" style="background:var(--primary-light);border:1px solid rgba(37,99,235,0.2);border-radius:var(--radius)">
          <p style="font-size:0.85rem;font-weight:700;color:var(--primary);margin-bottom:0.5rem">
            <i class="bi bi-lightning-fill me-1"></i> Ready to Book?
          </p>
          <p style="font-size:0.82rem;color:var(--text-muted);margin-bottom:1rem;line-height:1.5">
            Skip the form and go straight to our online reservation system.
          </p>
          <a href="reservation.php" class="btn btn-primary btn-sm w-100">
            <i class="bi bi-calendar-check me-1"></i>Start Reservation
          </a>
        </div>
      </div>

      <!-- ── Contact Form Column ── -->
      <div class="col-lg-7">
        <div class="res-box">
          <h4 class="res-box-title">
            <i class="bi bi-send-fill me-2" style="color:var(--primary)"></i>
            Send Us a Message
          </h4>
          <p class="res-box-sub">Fill in the form below and we'll get back to you within 1 business day.</p>

          <form id="contactForm" novalidate>
            <div class="row g-3">

              <div class="col-sm-6">
                <label class="form-label" for="c_name">Full Name <span style="color:var(--danger)">*</span></label>
                <input type="text" class="form-control" id="c_name" placeholder="Your full name" required>
              </div>

              <div class="col-sm-6">
                <label class="form-label" for="c_email">Email Address <span style="color:var(--danger)">*</span></label>
                <input type="email" class="form-control" id="c_email" placeholder="your@email.com" required>
              </div>

              <div class="col-sm-6">
                <label class="form-label" for="c_phone">Phone / WhatsApp</label>
                <input type="tel" class="form-control" id="c_phone" placeholder="08xxxxxxxxxx">
              </div>

              <div class="col-sm-6">
                <label class="form-label" for="c_subject">Subject <span style="color:var(--danger)">*</span></label>
                <select class="form-select" id="c_subject" required>
                  <option value="" disabled selected>Select a subject…</option>
                  <option>Equipment Inquiry</option>
                  <option>Quotation Request</option>
                  <option>Technical Support</option>
                  <option>Delivery & Logistics</option>
                  <option>Partnership</option>
                  <option>Other</option>
                </select>
              </div>

              <div class="col-12">
                <label class="form-label" for="c_event">Event Type & Date</label>
                <input type="text" class="form-control" id="c_event"
                       placeholder="e.g. Corporate Conference, 15 August 2026">
              </div>

              <div class="col-12">
                <label class="form-label" for="c_message">Message <span style="color:var(--danger)">*</span></label>
                <textarea class="form-control" id="c_message" rows="5"
                          placeholder="Tell us about your event, equipment needs, and any questions…"
                          required></textarea>
              </div>

              <div class="col-12">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="c_consent" required>
                  <label class="form-check-label" for="c_consent" style="font-size:0.82rem;color:var(--text-muted)">
                    I agree to be contacted by the RZKY Equipment Services team via the information I've provided.
                  </label>
                </div>
              </div>

              <div class="col-12">
                <button type="submit" class="btn btn-primary btn-lg w-100" style="padding:0.9rem">
                  <i class="bi bi-send me-2"></i>Send Message
                </button>
              </div>

            </div>
          </form>
        </div>
      </div>

    </div>
  </div>
</section>


<!-- FAQ Strip -->
<section class="section-pad-sm" style="background:var(--bg)">
  <div class="container">
    <div class="section-header text-center">
      <h4 class="fw-bold">Frequently Asked Questions</h4>
    </div>
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <?php
        $faqs = [
            ['How far in advance should I book?', 'We recommend booking at least 3–7 days before your event. For large events or peak seasons, book 2–4 weeks in advance.'],
            ['Do you offer delivery and setup?', 'Yes. We deliver, set up, and do a full system check before your event. Pickup is handled at the end.'],
            ['Is a deposit required?', 'Yes, a deposit (typically 30–50%) is required upon booking confirmation. The remaining balance is due before equipment delivery.'],
            ['What if equipment is damaged during my event?', 'All equipment is covered by our rental insurance. Please report any issues immediately to your assigned technician.'],
        ];
        foreach ($faqs as $idx => $faq): ?>
        <div class="accordion-item" style="border:1px solid var(--border);border-radius:6px;margin-bottom:0.75rem;overflow:visible">
          <h5 style="margin:0">
            <button class="accordion-button <?= $idx > 0 ? 'collapsed' : '' ?>"
                    style="font-weight:600;font-size:0.95rem;background:#fff;padding:1rem 1.25rem;"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#faq<?= $idx ?>"
                    style="font-family:var(--font);font-weight:600;font-size:0.92rem;background:#fff;color:var(--text)">
              <?= htmlspecialchars($faq[0]) ?>
            </button>
          </h5>
          <div id="faq<?= $idx ?>" class="accordion-collapse collapse <?= $idx === 0 ? 'show' : '' ?>">
            <div class="accordion-body" style="font-size:0.88rem;color:var(--text-muted);line-height:1.7;background:#fff;padding:0.25rem 1.25rem 1rem 1.25rem">
              <?= htmlspecialchars($faq[1]) ?>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<?php require_once '../src/includes/footer.php'; ?>
