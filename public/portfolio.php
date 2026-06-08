<?php
$page_title   = 'Portfolio — RZKY Equipment Services';
$current_page = 'portfolio';

require_once '../src/includes/header.php';
require_once '../src/includes/navbar.php';

// Portfolio data — organized by category
$categories = ['All', 'Concert', 'Cultural & Adat', 'Corporate', 'Wedding', 'Outdoor Festival'];

$portfolio = [
    [
        'title'    => 'HUT Kota Biak 2024',
        'category' => 'Cultural & Adat',
        'location' => 'Lapangan Cenderawasih, Biak',
        'date'     => 'Oktober 2024',
        'desc'     => 'Full outdoor PA system, LED screen 40 sqm, moving head & par can lighting untuk perayaan HUT Kota Biak.',
        'image'    => 'https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?w=600&h=400&fit=crop',
        'tags'     => ['Sound System','LED Screen','Lighting','Generator'],
        'color'    => 'linear-gradient(135deg,#C65D2C,#8B1A0E)',
    ],
    [
        'title'    => 'Konser Musik Papua Night',
        'category' => 'Concert',
        'location' => 'GOR Cenderawasih, Biak',
        'date'     => 'Agustus 2024',
        'desc'     => 'Line array speaker, subwoofer, mixing console digital, full lighting rig dengan moving head beam untuk konser music malam.',
        'image'    => 'https://images.unsplash.com/photo-1501386761578-eac5c94b800a?w=600&h=400&fit=crop',
        'tags'     => ['Line Array','Moving Head','FOH Console','Monitor'],
        'color'    => 'linear-gradient(135deg,#2563EB,#7c3aed)',
    ],
    [
        'title'    => 'Pernikahan Adat Biak',
        'category' => 'Wedding',
        'location' => 'Hotel Cenderawasih, Biak',
        'date'     => 'September 2024',
        'desc'     => 'Dekorasi stage elegan, sistem audio wireless, LED par can warm white, podium, dan layar proyeksi untuk pernikahan adat.',
        'image'    => 'https://images.unsplash.com/photo-1519741497674-611481863552?w=600&h=400&fit=crop',
        'tags'     => ['Stage','Wireless Mic','LED Par','Proyektor'],
        'color'    => 'linear-gradient(135deg,#d97706,#f59e0b)',
    ],
    [
        'title'    => 'Festival Cenderawasih',
        'category' => 'Outdoor Festival',
        'location' => 'Pantai Bosnik, Biak',
        'date'     => 'Juli 2024',
        'desc'     => 'Setup outdoor lengkap: generator 100KVA, line array, truss, LED screen, dan lighting festival untuk ribuan penonton.',
        'image'    => 'https://images.unsplash.com/photo-1429962714451-bb934ecdc4ec?w=600&h=400&fit=crop',
        'tags'     => ['Generator','Line Array','Truss','LED Screen'],
        'color'    => 'linear-gradient(135deg,#059669,#34d399)',
    ],
    [
        'title'    => 'Rapat Dinas Kabupaten Biak',
        'category' => 'Corporate',
        'location' => 'Aula Kantor Bupati, Biak',
        'date'     => 'Juni 2024',
        'desc'     => 'Setup conference room: proyektor, screen, wireless mic, mixing, podium, dan sistem intercom untuk rapat dinas resmi.',
        'image'    => 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=600&h=400&fit=crop',
        'tags'     => ['Proyektor','Wireless Mic','Podium','Intercom'],
        'color'    => 'linear-gradient(135deg,#0891b2,#06b6d4)',
    ],
    [
        'title'    => 'Lomba Musik Tradisional Papua',
        'category' => 'Cultural & Adat',
        'location' => 'Gedung Budaya Biak',
        'date'     => 'Mei 2024',
        'desc'     => 'Sound system PA, stage platform, lighting par, dan sistem monitor untuk lomba musik tradisional Papua.',
        'image'    => 'https://images.unsplash.com/photo-1516450360452-9312f5e86fc7?w=600&h=400&fit=crop',
        'tags'     => ['PA System','Stage Platform','LED Par'],
        'color'    => 'linear-gradient(135deg,#C65D2C,#D4890A)',
    ],
    [
        'title'    => 'Pelantikan Pejabat Kabupaten',
        'category' => 'Corporate',
        'location' => 'Gedung DPRD Biak Numfor',
        'date'     => 'April 2024',
        'desc'     => 'Protokoler sound system, podium resmi, backdrop LED, tata cahaya formil untuk upacara pelantikan pejabat daerah.',
        'image'    => 'https://images.unsplash.com/photo-1573167243872-43c6433b9d40?w=600&h=400&fit=crop',
        'tags'     => ['PA System','Podium','LED Backdrop','Lighting'],
        'color'    => 'linear-gradient(135deg,#334155,#64748B)',
    ],
    [
        'title'    => 'Konser Rohani Papua Bersatu',
        'category' => 'Concert',
        'location' => 'Lapangan Terbuka Sorido, Biak',
        'date'     => 'Maret 2024',
        'desc'     => 'Outdoor sound system 20.000 watt, truss 30 meter, lighting full rig, dan generator power untuk konser rohani massal.',
        'image'    => 'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?w=600&h=400&fit=crop',
        'tags'     => ['Line Array','Truss 30m','Full Lighting','Generator'],
        'color'    => 'linear-gradient(135deg,#7c3aed,#a855f7)',
    ],
    [
        'title'    => 'Pesta Perkawinan Gerejawi',
        'category' => 'Wedding',
        'location' => 'Gereja GKII Biak',
        'date'     => 'Februari 2024',
        'desc'     => 'Sound system gereja, dekorasi lighting hangat, stage backdrop, dan wireless mic untuk pemberkatan pernikahan.',
        'image'    => 'https://images.unsplash.com/photo-1518611012118-696072aa579a?w=600&h=400&fit=crop',
        'tags'     => ['Church Sound','LED Par','Stage Deco','Wireless Mic'],
        'color'    => 'linear-gradient(135deg,#dc2626,#f87171)',
    ],
];
?>

<!-- PAGE HERO -->
<section class="page-hero portfolio-hero">
  <div class="container">
    <nav class="breadcrumb-nav">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
        <li class="breadcrumb-item active">Portfolio</li>
      </ol>
    </nav>
    <h1 class="page-hero-title">Our Portfolio</h1>
    <p class="page-hero-sub">
      Rekam jejak kami melayani berbagai event di Biak Numfor dan Papua.
    </p>

    <!-- Stats strip inside hero -->
    <div class="portfolio-hero-stats">
      <div class="phs-item">
        <span class="phs-val">500+</span>
        <span class="phs-lbl">Event Selesai</span>
      </div>
      <div class="phs-item">
        <span class="phs-val">200+</span>
        <span class="phs-lbl">Klien Puas</span>
      </div>
      <div class="phs-item">
        <span class="phs-val">7+</span>
        <span class="phs-lbl">Tahun Berpengalaman</span>
      </div>
      <div class="phs-item">
        <span class="phs-val">Papua</span>
        <span class="phs-lbl">Area Layanan</span>
      </div>
    </div>
  </div>
</section>

<!-- PAPUAN PATTERN DIVIDER -->
<div class="papuan-divider-strip"></div>

<!-- PORTFOLIO GRID -->
<section class="section-pad">
  <div class="container">

    <!-- Category Filter Tabs -->
    <div class="portfolio-filter-wrap mb-5">
      <?php foreach ($categories as $idx => $cat): ?>
      <button class="pf-tab <?= $idx === 0 ? 'active' : '' ?>"
              data-filter="<?= $cat === 'All' ? 'all' : htmlspecialchars($cat) ?>">
        <?= htmlspecialchars($cat) ?>
      </button>
      <?php endforeach; ?>
    </div>

    <!-- Portfolio Items -->
    <div class="row g-4" id="portfolioGrid">
      <?php foreach ($portfolio as $item): ?>
      <div class="col-md-6 col-lg-4 portfolio-item" data-category="<?= htmlspecialchars($item['category']) ?>">
        <div class="portfolio-card">
          <!-- Image -->
          <div class="pc-img-wrap">
            <img src="<?= htmlspecialchars($item['image']) ?>"
                 class="pc-img"
                 alt="<?= htmlspecialchars($item['title']) ?>"
                 loading="lazy"
                 onerror="this.outerHTML='<div class=\'pc-img-placeholder\' style=\'background:<?= addslashes($item['color']) ?>\'><i class=\'bi bi-camera-video-fill\'></i></div>'">
            <div class="pc-overlay">
              <span class="pc-cat-badge"><?= htmlspecialchars($item['category']) ?></span>
            </div>
          </div>

          <!-- Body -->
          <div class="pc-body">
            <div class="pc-meta">
              <span class="pc-location"><i class="bi bi-geo-alt-fill"></i> <?= htmlspecialchars($item['location']) ?></span>
              <span class="pc-date"><i class="bi bi-calendar3"></i> <?= htmlspecialchars($item['date']) ?></span>
            </div>
            <h5 class="pc-title"><?= htmlspecialchars($item['title']) ?></h5>
            <p class="pc-desc"><?= htmlspecialchars($item['desc']) ?></p>
            <div class="pc-tags">
              <?php foreach ($item['tags'] as $tag): ?>
              <span class="pc-tag"><?= htmlspecialchars($tag) ?></span>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- CTA -->
    <div class="text-center mt-5 pt-4" style="border-top:2px dashed var(--border)">
      <p class="text-muted mb-3">Percayakan event Anda kepada kami</p>
      <a href="reservation.php" class="btn-reserve-cta">
        <i class="bi bi-calendar-check-fill"></i> BUAT RESERVASI SEKARANG
      </a>
    </div>

  </div>
</section>

<!-- TESTIMONIALS SECTION -->
<section class="section-pad" style="background:var(--surface-1)">
  <div class="container">
    <div class="section-header text-center">
      <span class="section-tag">Kata Klien Kami</span>
      <h2 class="section-title">Testimoni</h2>
      <p class="section-subtitle mx-auto">
        Kepuasan klien adalah prioritas utama kami di setiap event.
      </p>
    </div>
    <div class="row g-4">
      <?php
      $testimonials = [
          ['Y','Yusuf Kambu',    'Panitia HUT Kota Biak',           'RZKY Equipment Services luar biasa! Sound system dan lighting untuk HUT Kota Biak berjalan sempurna. Tim sangat profesional dan tepat waktu.'],
          ['M','Maria Rumbiak',  'Panitia Festival Cenderawasih',   'Generator dan PA system yang disewa untuk Festival Cenderawasih sangat handal. Tidak ada kendala selama 3 hari festival berlangsung.'],
          ['J','Johannes Wapai', 'Kabag Humas Kab. Biak Numfor',    'Pelayanan RZ Equipment sangat memuaskan. Peralatan lengkap, kondisi prima, dan teknisi siap standby selama acara pelantikan.'],
          ['S','Sara Mansim',    'Wedding Organizer Biak',           'Dekorasi lighting dan stage untuk pernikahan adat yang kami tangani selalu menggunakan RZ Rental. Hasilnya selalu memukau!'],
      ];
      foreach ($testimonials as $t): ?>
      <div class="col-md-6">
        <div class="testi-card">
          <div class="testi-stars">
            <?php for ($i=0;$i<5;$i++): ?><i class="bi bi-star-fill"></i><?php endfor; ?>
          </div>
          <p class="testi-text">"<?= htmlspecialchars($t[3]) ?>"</p>
          <div class="testi-author">
            <div class="testi-avatar"><?= $t[0] ?></div>
            <div>
              <p class="testi-name"><?= htmlspecialchars($t[1]) ?></p>
              <p class="testi-role"><?= htmlspecialchars($t[2]) ?></p>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- PAPUAN MOTIF SECTION -->
<section class="papuan-heritage-section section-pad">
  <div class="papuan-geo-bg"></div>
  <div class="container" style="position:relative;z-index:2">
    <div class="row align-items-center g-5">
      <div class="col-lg-6">
        <span class="section-tag" style="background:rgba(212,137,10,0.2);color:#D4890A;border-color:rgba(212,137,10,0.3)">
          <i class="bi bi-heart-fill"></i> Papua Bangga
        </span>
        <h2 class="section-title" style="color:#fff">Melayani dengan Semangat Tanah Papua</h2>
        <div class="divider-accent" style="background:linear-gradient(90deg,#C65D2C,#D4890A)"></div>
        <p style="color:rgba(255,255,255,0.72);line-height:1.85;margin-bottom:1.5rem">
          Kami bangga menjadi bagian dari pertumbuhan industri event di Biak Numfor.
          Dengan semangat Cenderawasih — terbang tinggi membawa keindahan —
          kami berkomitmen menghadirkan layanan terbaik untuk setiap momen berharga
          di tanah Papua yang kami cintai.
        </p>
        <div class="papuan-feature-list">
          <?php
          $pf = [
              ['bi-geo-alt-fill','#D4890A','Berbasis di Biak, Papua','Melayani seluruh wilayah Biak Numfor dan sekitarnya'],
              ['bi-people-fill', '#C65D2C','Tim Putra Papua','SDM lokal berpengalaman dan berdedikasi tinggi'],
              ['bi-heart-fill',  '#2B5E20','Cinta Budaya Lokal','Mendukung setiap event adat, budaya, dan tradisi Papua'],
          ];
          foreach ($pf as $f): ?>
          <div class="pf-item">
            <div class="pf-icon" style="background:<?= $f[1] ?>22;border:1px solid <?= $f[1] ?>44">
              <i class="bi <?= $f[0] ?>" style="color:<?= $f[1] ?>"></i>
            </div>
            <div>
              <p class="pf-title"><?= $f[2] ?></p>
              <p class="pf-sub"><?= $f[3] ?></p>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="papuan-card-grid">
          <div class="pcg-item pcg-big" style="background:linear-gradient(135deg,#C65D2C,#8B1A0E)">
            <i class="bi bi-music-note-beamed"></i>
            <p>Event Musik</p>
          </div>
          <div class="pcg-item" style="background:linear-gradient(135deg,#D4890A,#7A3B1E)">
            <i class="bi bi-people-fill"></i>
            <p>Acara Adat</p>
          </div>
          <div class="pcg-item" style="background:linear-gradient(135deg,#2B5E20,#34d399)">
            <i class="bi bi-building"></i>
            <p>Corporate</p>
          </div>
          <div class="pcg-item" style="background:linear-gradient(135deg,#2563EB,#7c3aed)">
            <i class="bi bi-camera-video-fill"></i>
            <p>Festival</p>
          </div>
          <div class="pcg-item" style="background:linear-gradient(135deg,#7c3aed,#a855f7)">
            <i class="bi bi-heart-fill"></i>
            <p>Wedding</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
// Portfolio filter
document.addEventListener('DOMContentLoaded', function () {
  const tabs  = document.querySelectorAll('.pf-tab');
  const items = document.querySelectorAll('.portfolio-item');

  tabs.forEach(tab => {
    tab.addEventListener('click', function () {
      tabs.forEach(t => t.classList.remove('active'));
      this.classList.add('active');

      const filter = this.dataset.filter;
      items.forEach(item => {
        const match = filter === 'all' || item.dataset.category === filter;
        item.style.display = match ? 'block' : 'none';
        if (match) {
          item.style.animation = 'fadeUp .35s ease forwards';
        }
      });
    });
  });
});
</script>

<?php require_once '../src/includes/footer.php'; ?>
