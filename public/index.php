<?php
require_once '../src/config/database.php';

$page_title   = 'RZKY Equipment Services — Professional Event Equipment Rental';
$current_page = 'home';

$db = getDB();

// Categories with equipment count
$categories = $db->query("
    SELECT c.*, COUNT(e.equipment_id) AS eq_count
    FROM categories c
    LEFT JOIN equipment e ON e.category_id = c.category_id AND e.condition_status = 'Baik'
    GROUP BY c.category_id
    ORDER BY c.category_id
")->fetchAll();

// Featured equipment — latest 6 in good condition
$featured = $db->query("
    SELECT e.*, c.category_name AS cat_name
    FROM equipment e
    JOIN categories c ON c.category_id = e.category_id
    WHERE e.condition_status = 'Baik'
    ORDER BY e.created_at DESC
    LIMIT 6
")->fetchAll();

// Stats
$total_eq  = $db->query("SELECT COUNT(*) FROM equipment WHERE condition_status = 'Baik'")->fetchColumn();
$total_cat = $db->query("SELECT COUNT(*) FROM categories")->fetchColumn();

// Per-category icon/gradient map
$cat_meta = [
    1 => ['icon' => 'bi-speaker-fill',        'gradient' => 'linear-gradient(135deg,#2563EB,#3b82f6)'],
    2 => ['icon' => 'bi-lightbulb-fill',      'gradient' => 'linear-gradient(135deg,#d97706,#f59e0b)'],
    3 => ['icon' => 'bi-grid-3x3-gap-fill',   'gradient' => 'linear-gradient(135deg,#059669,#34d399)'],
    4 => ['icon' => 'bi-display-fill',        'gradient' => 'linear-gradient(135deg,#7c3aed,#a855f7)'],
    5 => ['icon' => 'bi-lightning-charge-fill','gradient' => 'linear-gradient(135deg,#dc2626,#ef4444)'],
    6 => ['icon' => 'bi-headset',             'gradient' => 'linear-gradient(135deg,#0891b2,#06b6d4)'],
];

require_once '../src/includes/header.php';
require_once '../src/includes/navbar.php';
?>

<!-- ════════════════════════════════════════════════════════
     HERO
     ════════════════════════════════════════════════════════ -->
<section class="hero">
  <div class="hero-bg"></div>
  <div class="hero-gradient"></div>
  <div class="hero-glow hero-glow-blue"></div>
  <div class="hero-glow hero-glow-purple"></div>

  <div class="container hero-content">
    <div class="row align-items-center gy-5">

      <div class="col-lg-6">
        <div class="hero-badge">
          <i class="bi bi-stars"></i> One Stop Equipment Rental · Biak Papua
        </div>
        <h1 class="hero-title">
          Power Every<br>
          <span class="hl">Stage, Concert</span><br>
          &amp; Event
        </h1>
        <p class="hero-subtitle">
          From intimate conferences to massive concerts — we deliver professional
          sound, lighting, stage, and multimedia equipment with guaranteed quality
          and on-time service.
        </p>
        <div class="hero-actions">
          <a href="display.php" class="btn-hero-primary">
            <i class="bi bi-grid-fill"></i> Explore Catalog
          </a>
          <a href="contact.php" class="btn-hero-ghost">
            <i class="bi bi-telephone-fill"></i> Contact Us
          </a>
        </div>
        <div class="hero-stats">
          <div class="hero-stat">
            <span class="hero-stat-val"><?= $total_eq ?><span>+</span></span>
            <span class="hero-stat-lbl">Equipment</span>
          </div>
          <div class="hero-stat">
            <span class="hero-stat-val"><?= $total_cat ?></span>
            <span class="hero-stat-lbl">Categories</span>
          </div>
          <div class="hero-stat">
            <span class="hero-stat-val">500<span>+</span></span>
            <span class="hero-stat-lbl">Events Served</span>
          </div>
          <div class="hero-stat">
            <span class="hero-stat-val">7<span>yr</span></span>
            <span class="hero-stat-lbl">Experience</span>
          </div>
        </div>
      </div>

      <div class="col-lg-6 d-none d-lg-block">
        <div class="hero-visual-card ms-auto" style="max-width:360px">
          <?php
          $hvc_items = [
              ['icon'=>'bi-speaker-fill',         'color'=>'hvc-icon-blue',  'name'=>'Sound System',     'count'=>'Professional PA & monitoring'],
              ['icon'=>'bi-lightbulb-fill',        'color'=>'hvc-icon-yellow','name'=>'Lighting Rigs',   'count'=>'Moving heads, LED, follow spots'],
              ['icon'=>'bi-grid-3x3-gap-fill',     'color'=>'hvc-icon-green', 'name'=>'Stage & Truss',   'count'=>'Modular platforms & structures'],
              ['icon'=>'bi-display-fill',          'color'=>'hvc-icon-purple','name'=>'LED & Projection', 'count'=>'Screens, projectors, displays'],
              ['icon'=>'bi-lightning-charge-fill', 'color'=>'hvc-icon-orange','name'=>'Power Supply',    'count'=>'Generators & distribution'],
              ['icon'=>'bi-headset',               'color'=>'hvc-icon-cyan',  'name'=>'Communication',   'count'=>'IEM, walkie talkie, intercom'],
          ];
          foreach ($hvc_items as $item): ?>
          <div class="hvc-item">
            <div class="hvc-icon <?= $item['color'] ?>">
              <i class="bi <?= $item['icon'] ?>"></i>
            </div>
            <div>
              <p class="hvc-name"><?= $item['name'] ?></p>
              <p class="hvc-count"><?= $item['count'] ?></p>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ════════════════════════════════════════════════════════
     ABOUT / OVERVIEW
     ════════════════════════════════════════════════════════ -->
<section class="section-pad" style="background:#fff">
  <div class="container">
    <div class="row align-items-center g-5">

      <div class="col-lg-5">
        <span class="section-tag">Who We Are</span>
        <h2 class="section-title">One Stop Equipment Rental di Biak, Papua</h2>
        <div class="divider-accent"></div>
        <p style="color:var(--text-muted);line-height:1.85;margin-bottom:1.25rem;font-size:0.97rem">
          <strong>RZKY Equipment Services</strong> hadir sebagai solusi lengkap penyewaan peralatan event
          profesional di Kabupaten Biak Numfor, Papua. Kami menyediakan sound system, lighting,
          stage, multimedia, hingga power supply dengan kualitas terjamin.
        </p>
        <p style="color:var(--text-muted);line-height:1.85;margin-bottom:2rem;font-size:0.97rem">
          Tim teknisi bersertifikat kami siap memastikan event Anda berjalan lancar —
          dari acara corporate, pesta adat, hingga konser outdoor berskala besar.
        </p>
        <div class="row g-3">
          <div class="col-6">
            <div class="stat-mini">
              <span class="stat-mini-val">500+</span>
              <span class="stat-mini-lbl">Events Completed</span>
            </div>
          </div>
          <div class="col-6">
            <div class="stat-mini">
              <span class="stat-mini-val">200+</span>
              <span class="stat-mini-lbl">Happy Clients</span>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-7">
        <div class="row g-3">
          <?php
          $highlights = [
              ['bi-music-note-beamed','si-blue',  'Concert & Live Music', 'Full line-array PA systems, stage monitoring, and FOH mixing for any scale event.'],
              ['bi-camera-video-fill','si-purple','Corporate Events',      'LED screens, projection, and communication for conferences and product launches.'],
              ['bi-stars',           'si-orange', 'Special Occasions',     'Mood lighting, portable sound, and staging for weddings and private celebrations.'],
              ['bi-broadcast',       'si-green',  'Festivals & Outdoor',   'Weatherproof PA, truss structures, and generator power for outdoor productions.'],
          ];
          foreach ($highlights as $h): ?>
          <div class="col-sm-6">
            <div class="service-card">
              <div class="service-icon <?= $h[1] ?>">
                <i class="bi <?= $h[0] ?>"></i>
              </div>
              <h5 class="service-title"><?= $h[2] ?></h5>
              <p class="service-desc"><?= $h[3] ?></p>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ════════════════════════════════════════════════════════
     EQUIPMENT CATEGORIES
     ════════════════════════════════════════════════════════ -->
<section class="section-pad" style="background:var(--bg)">
  <div class="container">
    <div class="section-header text-center">
      <span class="section-tag">Our Inventory</span>
      <h2 class="section-title">Browse by Category</h2>
      <p class="section-subtitle mx-auto">
        Explore our full range of professional event equipment organized by category.
      </p>
    </div>
    <div class="row g-3 justify-content-center">
      <?php foreach ($categories as $cat):
        $cm = $cat_meta[$cat['category_id']] ?? ['icon'=>'bi-box-fill','gradient'=>'linear-gradient(135deg,#64748b,#94a3b8)'];
      ?>
      <div class="col-6 col-md-4 col-lg-2">
        <a href="display.php?cat=<?= $cat['category_id'] ?>" class="cat-card">
          <div class="cat-icon" style="background:<?= $cm['gradient'] ?>">
            <i class="bi <?= $cm['icon'] ?>" style="color:#fff"></i>
          </div>
          <p class="cat-name"><?= htmlspecialchars($cat['category_name']) ?></p>
          <p class="cat-count"><?= $cat['eq_count'] ?> item<?= $cat['eq_count'] != 1 ? 's' : '' ?></p>
        </a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ════════════════════════════════════════════════════════
     FEATURED EQUIPMENT
     ════════════════════════════════════════════════════════ -->
<?php if (!empty($featured)): ?>
<section class="section-pad" style="background:#fff">
  <div class="container">
    <div class="section-header">
      <span class="section-tag">Popular Picks</span>
      <h2 class="section-title">Featured Equipment</h2>
      <p class="section-subtitle">Handpicked professional-grade items most requested by our clients.</p>
    </div>
    <div class="row g-4">
      <?php foreach ($featured as $eq):
        $cat_id  = (int)$eq['category_id'];
        $cm      = $cat_meta[$cat_id] ?? ['icon'=>'bi-box-fill','gradient'=>'linear-gradient(135deg,#1e3a5f,#2563EB)'];
      ?>
      <div class="col-sm-6 col-lg-4">
        <div class="eq-card">
          <div class="eq-img-wrap">
            <?php if (!empty($eq['image_path'])): ?>
            <img src="../<?= htmlspecialchars($eq['image_path']) ?>"
                 class="eq-img"
                 alt="<?= htmlspecialchars($eq['equipment_name']) ?>"
                 loading="lazy"
                 onerror="this.outerHTML='<div class=\'eq-img-placeholder\' style=\'background:<?= addslashes($cm['gradient']) ?>\'><i class=\'bi <?= $cm['icon'] ?>\'></i></div>'">
            <?php else: ?>
            <div class="eq-img-placeholder" style="background:<?= $cm['gradient'] ?>">
              <i class="bi <?= $cm['icon'] ?>"></i>
            </div>
            <?php endif; ?>
            <span class="eq-cat-badge"><?= htmlspecialchars($eq['cat_name']) ?></span>
          </div>
          <div class="eq-body">
            <h5 class="eq-name"><?= htmlspecialchars($eq['equipment_name']) ?></h5>
            <p class="eq-desc"><?= htmlspecialchars($eq['description'] ?? '') ?></p>
            <div class="eq-footer">
              <div class="eq-price">
                Rp <?= number_format($eq['rental_price'], 0, ',', '.') ?>
                <small>/day</small>
              </div>
              <span class="eq-stock-badge">
                <i class="bi bi-check-circle-fill"></i> <?= $eq['total_stock'] ?> avail
              </span>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="text-center mt-5">
      <a href="display.php" class="btn btn-outline-primary btn-lg px-5">
        <i class="bi bi-grid me-2"></i>View Full Catalog
      </a>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ════════════════════════════════════════════════════════
     WHY CHOOSE US
     ════════════════════════════════════════════════════════ -->
<section class="section-pad why-section">
  <div class="container">
    <div class="row align-items-center g-5">

      <div class="col-lg-6">
        <span class="section-tag" style="background:rgba(37,99,235,0.18);color:#93c5fd;border-color:rgba(96,165,250,0.2)">
          Why RZKY Equipment Services
        </span>
        <h2 class="section-title" style="color:#fff">Mengapa Memilih RZKY Equipment Services?</h2>
        <div class="divider-accent"></div>

        <?php
        $whys = [
            ['bi-patch-check-fill', 'Peralatan Bersertifikat',   'Semua peralatan rutin diperiksa dan dirawat sesuai standar industri profesional.'],
            ['bi-clock-history',    'Dukungan Teknis 24 Jam',    'Teknisi kami siap standby selama acara berlangsung untuk menangani masalah teknis.'],
            ['bi-truck',            'Pengiriman Tepat Waktu',     'Kami menjamin pengiriman dan pemasangan selesai sebelum acara dimulai — tanpa terkecuali.'],
            ['bi-map',              'Layanan Seluruh Papua',      'Kami melayani pengiriman dan setup ke seluruh wilayah Biak Numfor dan sekitarnya.'],
        ];
        foreach ($whys as $w): ?>
        <div class="why-feature">
          <div class="why-feature-icon"><i class="bi <?= $w[0] ?>"></i></div>
          <div>
            <p class="why-feature-title"><?= $w[1] ?></p>
            <p class="why-feature-desc"><?= $w[2] ?></p>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <div class="col-lg-6">
        <div class="why-image-card">
          <div class="row g-3 mb-3">
            <?php
            $counters = [['500+','Events'],['200+','Clients'],['7yr','Experience'],['4.9★','Rating']];
            foreach ($counters as $c): ?>
            <div class="col-6">
              <div class="why-counter">
                <span class="why-counter-val"><?= $c[0] ?></span>
                <span class="why-counter-lbl"><?= $c[1] ?></span>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <div class="testimonial-card">
            <div class="tc-stars">
              <?php for ($i=0;$i<5;$i++): ?><i class="bi bi-star-fill"></i><?php endfor; ?>
            </div>
            <p class="tc-text">
              "RZKY Equipment Services memberikan setup sound dan lighting yang luar biasa untuk
              acara HUT Kota Biak kami. Semua berjalan sempurna dan tim sangat profesional."
            </p>
            <div class="tc-author">
              <div class="tc-avatar">Y</div>
              <div>
                <p class="tc-name">Yusuf Kambu</p>
                <p class="tc-role">Panitia HUT Kota Biak, Biak Numfor</p>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ════════════════════════════════════════════════════════
     CTA BANNER
     ════════════════════════════════════════════════════════ -->
<section class="cta-banner">
  <div class="container">
    <h2 class="cta-banner-title">Ready to Power Your Next Event?</h2>
    <p class="cta-banner-sub">Browse our full equipment catalog and start your reservation in minutes.</p>
    <a href="reservation.php" class="btn-cta-white">
      <i class="bi bi-calendar-check-fill"></i> Start Reservation Now
    </a>
  </div>
</section>

<?php require_once '../src/includes/footer.php'; ?>
