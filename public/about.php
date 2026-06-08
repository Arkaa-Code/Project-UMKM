<?php
$page_title   = 'About Us — RZKY Equipment Services';
$current_page = 'about';

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
        <li class="breadcrumb-item active">About Us</li>
      </ol>
    </nav>
    <h1 class="page-hero-title">About RZKY Equipment Services</h1>
    <p class="page-hero-sub">Biak's trusted professional event equipment rental since 2018.</p>
  </div>
</section>

<!-- ════════════════════════════════════════════════════════
     OUR STORY
     ════════════════════════════════════════════════════════ -->
<section class="section-pad" style="background:#fff">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-6">
        <span class="section-tag">Our Story</span>
        <h2 class="section-title">Built by Event Professionals,<br>for Event Professionals</h2>
        <div class="divider-accent"></div>
        <p style="color:var(--text-muted);line-height:1.8;margin-bottom:1.25rem">
          RZKY Equipment Services was founded in 2018 by a team of experienced sound engineers and
          event producers who saw a gap in the Biak market for reliable, high-quality
          equipment rental with real professional support.
        </p>
        <p style="color:var(--text-muted);line-height:1.8;margin-bottom:1.25rem">
          We started with a small inventory of sound equipment and have since grown to
          become one of Biak's most comprehensive event equipment providers, serving
          over 500 events across Indonesia.
        </p>
        <p style="color:var(--text-muted);line-height:1.8">
          From intimate corporate meetings to large-scale outdoor concerts, our mission
          remains the same: deliver flawless equipment, expert technical support, and
          on-time service — every time.
        </p>
      </div>
      <div class="col-lg-6">
        <div class="row g-3">
          <?php
          $stats = [
              ['500+', 'Events Completed', 'bi-trophy-fill', 'si-blue'],
              ['200+', 'Happy Clients',    'bi-people-fill',  'si-green'],
              ['7+',   'Years Experience', 'bi-calendar-fill','si-purple'],
              ['4.9★', 'Average Rating',   'bi-star-fill',    'si-orange'],
          ];
          foreach ($stats as $s): ?>
          <div class="col-6">
            <div class="service-card text-center">
              <div class="service-icon <?= $s[3] ?> mx-auto mb-3">
                <i class="bi <?= $s[1] === 'Average Rating' ? 'bi-star-fill' : $s[2] ?>"></i>
              </div>
              <h3 style="font-size:2rem;font-weight:800;color:var(--primary);margin-bottom:0.25rem"><?= $s[0] ?></h3>
              <p class="service-desc text-center" style="margin:0"><?= $s[1] ?></p>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ════════════════════════════════════════════════════════
     MISSION & VISION
     ════════════════════════════════════════════════════════ -->
<section class="section-pad" style="background:var(--bg)">
  <div class="container">
    <div class="section-header text-center">
      <span class="section-tag">Our Purpose</span>
      <h2 class="section-title">Mission & Vision</h2>
    </div>
    <div class="row g-4 justify-content-center">
      <div class="col-md-6 col-lg-5">
        <div class="service-card h-100">
          <div class="service-icon si-blue" style="width:56px;height:56px">
            <i class="bi bi-bullseye"></i>
          </div>
          <h4 class="service-title">Our Mission</h4>
          <p class="service-desc" style="font-size:0.92rem">
            To provide Biak's event industry with access to professional-grade audio, visual,
            and staging equipment at competitive prices — backed by certified technicians,
            prompt logistics, and a commitment to zero-failure service.
          </p>
        </div>
      </div>
      <div class="col-md-6 col-lg-5">
        <div class="service-card h-100">
          <div class="service-icon si-purple" style="width:56px;height:56px">
            <i class="bi bi-eye-fill"></i>
          </div>
          <h4 class="service-title">Our Vision</h4>
          <p class="service-desc" style="font-size:0.92rem">
            To become Indonesia's leading event equipment rental brand, recognized for
            technical excellence, reliability, and innovation — empowering event creators
            to realize their vision without compromise.
          </p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ════════════════════════════════════════════════════════
     VALUES
     ════════════════════════════════════════════════════════ -->
<section class="section-pad" style="background:#fff">
  <div class="container">
    <div class="section-header text-center">
      <span class="section-tag">What We Stand For</span>
      <h2 class="section-title">Our Core Values</h2>
    </div>
    <div class="row g-4">
      <?php
      $values = [
          ['bi-shield-fill-check','si-blue',  'Reliability',   'We show up on time, every time. Our logistics and setup process is engineered for zero delays.'],
          ['bi-patch-check-fill', 'si-green', 'Quality',       'All equipment is regularly serviced and certified. We only rent gear we\'d use ourselves.'],
          ['bi-person-fill',      'si-orange','Client First',  'Your event\'s success is our success. We go beyond rental to provide full technical partnership.'],
          ['bi-lightbulb-fill',   'si-purple','Innovation',    'We continuously invest in the latest equipment and technologies to keep your events ahead.'],
          ['bi-hand-thumbs-up-fill','si-cyan','Transparency',  'Clear pricing, clear communication, no hidden fees. What you book is what you get.'],
          ['bi-people-fill',      'si-red',   'Teamwork',      'Our crews are trained professionals who work as an integrated team during your event.'],
      ];
      foreach ($values as $v): ?>
      <div class="col-md-4 col-sm-6">
        <div class="service-card">
          <div class="service-icon <?= $v[1] ?>"><i class="bi <?= $v[0] ?>"></i></div>
          <h5 class="service-title"><?= $v[2] ?></h5>
          <p class="service-desc"><?= $v[3] ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ════════════════════════════════════════════════════════
     TEAM
     ════════════════════════════════════════════════════════ -->
<section class="section-pad" style="background:var(--bg)">
  <div class="container">
    <div class="section-header text-center">
      <span class="section-tag">The People Behind It</span>
      <h2 class="section-title">Meet Our Team</h2>
      <p class="section-subtitle mx-auto">
        Experienced professionals dedicated to making every event a success.
      </p>
    </div>
    <div class="row g-4 justify-content-center">
      <?php
      $team = [
          ['R','Rizky Pratama',    'Founder & CEO',            'linear-gradient(135deg,#2563EB,#7c3aed)'],
          ['A','Ayu Wandira',      'Head of Operations',       'linear-gradient(135deg,#059669,#34d399)'],
          ['D','Dimas Fauzan',     'Chief Sound Engineer',     'linear-gradient(135deg,#d97706,#f59e0b)'],
          ['S','Sinta Rahayu',     'Lighting & Visual Director','linear-gradient(135deg,#dc2626,#f87171)'],
          ['H','Hendra Wijaya',    'Logistics Manager',        'linear-gradient(135deg,#0891b2,#06b6d4)'],
          ['F','Farida Nusantara', 'Client Relations',         'linear-gradient(135deg,#7c3aed,#a855f7)'],
      ];
      foreach ($team as $member): ?>
      <div class="col-6 col-md-4 col-lg-2">
        <div class="team-card">
          <div class="team-avatar" style="background:<?= $member[3] ?>">
            <?= $member[0] ?>
          </div>
          <p class="team-name"><?= $member[1] ?></p>
          <p class="team-role"><?= $member[2] ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ════════════════════════════════════════════════════════
     GOOGLE MAPS EMBED
     ════════════════════════════════════════════════════════ -->
<section style="padding-bottom:5rem">
  <div class="container">

    <!-- Address bar above map -->
    <div class="map-address-bar">
      <div class="mab-left">
        <div class="mab-icon"><i class="bi bi-geo-alt-fill"></i></div>
        <div>
          <p class="mab-title">Lokasi Kami</p>
          <p class="mab-addr">Jalan Bibit Unggul No.10, Sorido, Kec. Biak Kota, Kab. Biak Numfor, Papua 98113</p>
        </div>
      </div>
      <a href="https://maps.app.goo.gl/C7cxuCTSwcNfBxvD6"
         target="_blank" rel="noopener noreferrer"
         class="btn btn-primary btn-sm px-4">
        <i class="bi bi-box-arrow-up-right me-1"></i>Buka di Google Maps
      </a>
    </div>

    <!-- Map embed -->
    <div class="map-embed-wrap">
      <iframe
        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3988.123456789!2d136.0!3d-1.1763!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x68038f3e826e9451%3A0xe3724760dbcee754!2sRZKY%20Event%20Equipment%20Rental%20Services!5e0!3m2!1sid!2sid!4v1700000000000!5m2!1sid!2sid"
        width="100%"
        height="420"
        style="border:0; display:block;"
        allowfullscreen=""
        loading="lazy"
        referrerpolicy="no-referrer-when-downgrade"
        title="RZKY Event Equipment Rental Services — Lokasi">
      </iframe>
    </div>

  </div>
</section>


<!-- ════════════════════════════════════════════════════════
     CTA
     ════════════════════════════════════════════════════════ -->
<section class="cta-banner">
  <div class="container">
    <h2 class="cta-banner-title">Let's Work Together</h2>
    <p class="cta-banner-sub">
      Ready to power your next event? Browse our catalog or get in touch with our team.
    </p>
    <div class="d-flex gap-3 justify-content-center flex-wrap">
      <a href="reservation.php" class="btn-cta-white">
        <i class="bi bi-calendar-check-fill"></i> Start Reservation
      </a>
      <a href="contact.php" class="btn btn-outline-light btn-lg px-4">
        <i class="bi bi-telephone me-2"></i>Contact Us
      </a>
    </div>
  </div>
</section>

<?php require_once '../src/includes/footer.php'; ?>
