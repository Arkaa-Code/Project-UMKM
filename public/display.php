<?php
require_once '../src/config/database.php';

$page_title   = 'Equipment Catalog — RZKY Equipment Services';
$current_page = 'catalog';

$db = getDB();

$active_cat = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;

// All categories with count
$categories = $db->query("
    SELECT c.*, COUNT(e.equipment_id) AS eq_count
    FROM categories c
    LEFT JOIN equipment e ON e.category_id = c.category_id AND e.condition_status = 'Baik'
    GROUP BY c.category_id
    ORDER BY c.category_id
")->fetchAll();

// Equipment list — filtered or all
if ($active_cat > 0) {
    $stmt = $db->prepare("
        SELECT e.*, c.category_name AS cat_name
        FROM equipment e
        JOIN categories c ON c.category_id = e.category_id
        WHERE e.category_id = ? AND e.condition_status = 'Baik'
        ORDER BY e.equipment_name
    ");
    $stmt->execute([$active_cat]);
} else {
    $stmt = $db->query("
        SELECT e.*, c.category_name AS cat_name
        FROM equipment e
        JOIN categories c ON c.category_id = e.category_id
        WHERE e.condition_status = 'Baik'
        ORDER BY c.category_id, e.equipment_name
    ");
}
$equipment_list = $stmt->fetchAll();

// Active category label
$active_cat_name = 'All Equipment';
foreach ($categories as $c) {
    if ((int)$c['category_id'] === $active_cat) {
        $active_cat_name = $c['category_name'];
        break;
    }
}

// Icon/gradient map
$cat_meta = [
    1 => ['icon'=>'bi-speaker-fill',        'gradient'=>'linear-gradient(135deg,#2563EB,#3b82f6)'],
    2 => ['icon'=>'bi-lightbulb-fill',      'gradient'=>'linear-gradient(135deg,#d97706,#f59e0b)'],
    3 => ['icon'=>'bi-grid-3x3-gap-fill',   'gradient'=>'linear-gradient(135deg,#059669,#34d399)'],
    4 => ['icon'=>'bi-display-fill',        'gradient'=>'linear-gradient(135deg,#7c3aed,#a855f7)'],
    5 => ['icon'=>'bi-lightning-charge-fill','gradient'=>'linear-gradient(135deg,#dc2626,#ef4444)'],
    6 => ['icon'=>'bi-headset',             'gradient'=>'linear-gradient(135deg,#0891b2,#06b6d4)'],
];

require_once '../src/includes/header.php';
require_once '../src/includes/navbar.php';
?>

<!-- PAGE HERO -->
<section class="page-hero">
  <div class="container">
    <nav class="breadcrumb-nav" aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
        <li class="breadcrumb-item active">Catalog</li>
      </ol>
    </nav>
    <h1 class="page-hero-title">Equipment Catalog</h1>
    <p class="page-hero-sub">Browse our full range of professional event equipment for rent.</p>
  </div>
</section>

<!-- CATALOG -->
<section class="section-pad">
  <div class="container">
    <div class="row g-4">

      <!-- Sidebar -->
      <div class="col-lg-3">
        <div class="sidebar">
          <div class="sidebar-card mb-3">
            <div class="sidebar-hd">
              <i class="bi bi-funnel-fill"></i> Filter by Category
            </div>
            <div class="sidebar-body">
              <a href="display.php" class="cat-filter-link <?= $active_cat === 0 ? 'active' : '' ?>">
                <i class="bi bi-grid-fill"></i>
                All Categories
                <span class="cf-count"><?= array_sum(array_column($categories, 'eq_count')) ?></span>
              </a>
              <?php foreach ($categories as $cat):
                $cm = $cat_meta[$cat['category_id']] ?? ['icon'=>'bi-box-fill'];
              ?>
              <a href="display.php?cat=<?= $cat['category_id'] ?>"
                 class="cat-filter-link <?= $active_cat === (int)$cat['category_id'] ? 'active' : '' ?>">
                <i class="bi <?= $cm['icon'] ?>"></i>
                <?= htmlspecialchars($cat['category_name']) ?>
                <span class="cf-count"><?= $cat['eq_count'] ?></span>
              </a>
              <?php endforeach; ?>
            </div>
          </div>

          <div class="sidebar-card">
            <div class="sidebar-hd"><i class="bi bi-question-circle-fill"></i> Need Help?</div>
            <div class="sidebar-body">
              <p style="font-size:0.82rem;color:var(--text-muted);margin-bottom:1rem;line-height:1.65">
                Not sure what you need? Our team can help you plan the perfect setup for your event.
              </p>
              <a href="contact.php" class="btn btn-outline-primary btn-sm w-100">
                <i class="bi bi-telephone me-1"></i>Contact Us
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- Equipment Grid -->
      <div class="col-lg-9">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
          <div>
            <h4 class="fw-bold mb-0"><?= htmlspecialchars($active_cat_name) ?></h4>
            <small class="text-muted">
              <?= count($equipment_list) ?> item<?= count($equipment_list) != 1 ? 's' : '' ?> found
            </small>
          </div>
          <?php if ($active_cat > 0): ?>
          <a href="display.php" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-x-circle me-1"></i>Clear Filter
          </a>
          <?php endif; ?>
        </div>

        <?php if (empty($equipment_list)): ?>
        <div class="alert-empty">
          <i class="bi bi-search"></i>
          <h5>No Equipment Found</h5>
          <p>No available items in this category. Please try another filter.</p>
        </div>
        <?php else: ?>
        <div class="row g-4">
          <?php foreach ($equipment_list as $eq):
            $cat_id = (int)$eq['category_id'];
            $cm     = $cat_meta[$cat_id] ?? ['icon'=>'bi-box-fill','gradient'=>'linear-gradient(135deg,#1e3a5f,#2563EB)'];
          ?>
          <div class="col-sm-6 col-xl-4">
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
                    <i class="bi bi-check-circle-fill"></i> <?= $eq['total_stock'] ?> unit<?= $eq['total_stock'] != 1 ? 's' : '' ?>
                  </span>
                </div>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Large CTA -->
        <div class="text-center mt-5 pt-4" style="border-top:2px dashed var(--border)">
          <p class="text-muted mb-3">Found what you need? Reserve your equipment now.</p>
          <a href="reservation.php" class="btn-reserve-cta">
            <i class="bi bi-calendar-check-fill"></i>
            START RESERVATION
          </a>
          <p class="text-muted mt-3" style="font-size:0.8rem">
            <i class="bi bi-shield-check me-1"></i>No payment required. We'll confirm via phone or WhatsApp.
          </p>
        </div>
      </div>

    </div>
  </div>
</section>

<?php require_once '../src/includes/footer.php'; ?>
