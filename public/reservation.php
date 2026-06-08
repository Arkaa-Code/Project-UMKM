<?php
session_start();
require_once '../src/config/database.php';

$page_title   = 'Make a Reservation — RZKY Equipment Services';
$current_page = '';

$db     = getDB();
$errors = [];
$step   = 1;
$action = '';

// ── Active tab: 'book' | 'check' ─────────────────────────
$active_tab   = $_GET['tab'] ?? 'book';
$check_errors = [];
$check_result = null;   // reservation row
$check_items  = [];     // reservation_items rows

// ── Back navigation ──────────────────────────────────────
if (isset($_GET['back'])) {
    $back = (int)$_GET['back'];
    if ($back <= 1) {
        unset($_SESSION['res_start'], $_SESSION['res_end'], $_SESSION['res_equipment']);
        header('Location: reservation.php');
        exit;
    }
    if ($back === 2) {
        unset($_SESSION['res_equipment']);
        // Fall through to step 2 below
    }
}

// ── POST Handling ────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ── STEP 1 → Check Availability ─────────────────────
    if ($action === 'check_availability') {
        $start_date = trim($_POST['start_date'] ?? '');
        $end_date   = trim($_POST['end_date']   ?? '');
        $today      = date('Y-m-d');

        if (!$start_date || !$end_date) {
            $errors[] = 'Please select both start and end dates.';
        } elseif ($start_date < $today) {
            $errors[] = 'Start date cannot be in the past.';
        } elseif ($end_date <= $start_date) {
            $errors[] = 'End date must be after the start date.';
        } else {
            $_SESSION['res_start'] = $start_date;
            $_SESSION['res_end']   = $end_date;
            $step = 2;
        }

        if (!empty($errors)) $step = 1;

    // ── STEP 2 → Select Equipment ────────────────────────
    } elseif ($action === 'select_equipment') {
        if (empty($_SESSION['res_start'])) {
            header('Location: reservation.php');
            exit;
        }

        $selected_ids = $_POST['equipment_ids'] ?? [];
        $quantities   = $_POST['qty']           ?? [];

        if (empty($selected_ids)) {
            $errors[] = 'Please select at least one equipment item.';
            $step = 2;
        } else {
            $items = [];
            foreach ($selected_ids as $eq_id) {
                $eq_id = (int)$eq_id;
                $qty   = max(1, (int)($quantities[$eq_id] ?? 1));
                $items[] = ['equipment_id' => $eq_id, 'quantity' => $qty];
            }
            $_SESSION['res_equipment'] = $items;
            $step = 3;
        }

    // ── STEP 3 → Complete Booking ────────────────────────
    } elseif ($action === 'complete_booking') {
        if (empty($_SESSION['res_start']) || empty($_SESSION['res_equipment'])) {
            header('Location: reservation.php');
            exit;
        }

        $customer_name = trim($_POST['customer_name'] ?? '');
        $email         = trim($_POST['email']         ?? '');
        $phone         = trim($_POST['phone']         ?? '');
        $notes         = trim($_POST['notes']         ?? '');

        if (!$customer_name)                                          $errors[] = 'Full name is required.';
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL))   $errors[] = 'A valid email address is required.';
        if (!$phone)                                                  $errors[] = 'Phone number is required.';

        if (empty($errors)) {
            try {
                // Calculate rental days
                $d1          = new DateTime($_SESSION['res_start']);
                $d2          = new DateTime($_SESSION['res_end']);
                $rental_days = max(1, (int)$d1->diff($d2)->days);

                // Fetch equipment prices
                $eq_ids   = array_column($_SESSION['res_equipment'], 'equipment_id');
                $in_ph    = implode(',', array_fill(0, count($eq_ids), '?'));
                $pstmt    = $db->prepare("SELECT equipment_id, rental_price FROM equipment WHERE equipment_id IN ($in_ph)");
                $pstmt->execute($eq_ids);
                $price_map = [];
                foreach ($pstmt->fetchAll() as $row) {
                    $price_map[$row['equipment_id']] = (float)$row['rental_price'];
                }

                // Calculate total
                $total_amount = 0.00;
                foreach ($_SESSION['res_equipment'] as $item) {
                    $unit  = $price_map[$item['equipment_id']] ?? 0;
                    $total_amount += $unit * $item['quantity'] * $rental_days;
                }

                $db->beginTransaction();

                // Insert reservation
                $stmt = $db->prepare("
                    INSERT INTO reservations
                        (customer_name, phone, email, notes, start_date, end_date, status, total_amount)
                    VALUES (?, ?, ?, ?, ?, ?, 'pending', ?)
                ");
                $stmt->execute([
                    $customer_name,
                    $phone,
                    $email,
                    $notes ?: null,
                    $_SESSION['res_start'],
                    $_SESSION['res_end'],
                    $total_amount,
                ]);
                $reservation_id = (int)$db->lastInsertId();

                // Insert reservation items
                $si = $db->prepare("
                    INSERT INTO reservation_items
                        (reservation_id, equipment_id, quantity, rental_price, subtotal)
                    VALUES (?, ?, ?, ?, ?)
                ");
                foreach ($_SESSION['res_equipment'] as $item) {
                    $unit     = $price_map[$item['equipment_id']] ?? 0;
                    $subtotal = $unit * $item['quantity'] * $rental_days;
                    $si->execute([
                        $reservation_id,
                        $item['equipment_id'],
                        $item['quantity'],
                        $unit,
                        $subtotal,
                    ]);
                }

                $db->commit();

                // Store confirmation data and clear booking session
                $_SESSION['booking_success'] = [
                    'id'         => $reservation_id,
                    'name'       => $customer_name,
                    'email'      => $email,
                    'phone'      => $phone,
                    'start_date' => $_SESSION['res_start'],
                    'end_date'   => $_SESSION['res_end'],
                    'total'      => $total_amount,
                    'days'       => $rental_days,
                ];
                unset($_SESSION['res_start'], $_SESSION['res_end'], $_SESSION['res_equipment']);

                header('Location: booking-success.php');
                exit;

            } catch (PDOException $e) {
                $db->rollBack();
                $errors[] = 'Booking failed due to a system error. Please try again.';
                $step = 3;
            }
        } else {
            $step = 3;
        }
    }
}

// ── Check Booking Status handler ─────────────────────────
if ($action === 'check_booking_status') {
    $active_tab   = 'check';
    $booking_id   = (int)trim($_POST['booking_id'] ?? 0);
    $identifier   = trim($_POST['identifier'] ?? '');   // email or phone

    if (!$booking_id || $booking_id <= 0) {
        $check_errors[] = 'Masukkan kode booking yang valid (angka).';
    }
    if (!$identifier) {
        $check_errors[] = 'Masukkan email atau nomor HP yang digunakan saat booking.';
    }

    if (empty($check_errors)) {
        $stmt = $db->prepare("
            SELECT * FROM reservations
            WHERE reservation_id = ?
              AND (LOWER(email) = LOWER(?) OR phone = ?)
            LIMIT 1
        ");
        $stmt->execute([$booking_id, $identifier, $identifier]);
        $check_result = $stmt->fetch();

        if (!$check_result) {
            $check_errors[] = 'Booking tidak ditemukan. Periksa kembali kode booking dan email/nomor HP Anda.';
        } else {
            // Fetch reserved items
            $iStmt = $db->prepare("
                SELECT ri.quantity, ri.rental_price, ri.subtotal,
                       e.equipment_name, e.image_path, c.category_name
                FROM reservation_items ri
                JOIN equipment e  ON e.equipment_id   = ri.equipment_id
                JOIN categories c ON c.category_id    = e.category_id
                WHERE ri.reservation_id = ?
                ORDER BY c.category_name, e.equipment_name
            ");
            $iStmt->execute([$check_result['reservation_id']]);
            $check_items = $iStmt->fetchAll();
        }
    }
}

// ── Restore step from session ────────────────────────────
if ($step === 1 && !empty($_SESSION['res_start'])) {
    $step = !empty($_SESSION['res_equipment']) ? 3 : 2;
}
// Handle GET back=2
if (isset($_GET['back']) && (int)$_GET['back'] === 2 && !empty($_SESSION['res_start'])) {
    $step = 2;
}

$start_date = $_SESSION['res_start'] ?? '';
$end_date   = $_SESSION['res_end']   ?? '';

// Rental days
$rental_days = 1;
if ($start_date && $end_date) {
    $d1 = new DateTime($start_date);
    $d2 = new DateTime($end_date);
    $rental_days = max(1, (int)$d1->diff($d2)->days);
}

// ── Available equipment for Step 2 ───────────────────────
$available_equipment = [];
$equipment_by_cat    = [];

if ($step === 2) {
    $stmt = $db->prepare("
        SELECT
            e.equipment_id,
            e.equipment_name,
            e.description,
            e.rental_price,
            e.image_path,
            e.total_stock,
            e.category_id,
            c.category_name AS cat_name,
            (e.total_stock - COALESCE(b.booked, 0)) AS available_stock
        FROM equipment e
        JOIN categories c ON c.category_id = e.category_id
        LEFT JOIN (
            SELECT ri.equipment_id, SUM(ri.quantity) AS booked
            FROM reservation_items ri
            INNER JOIN reservations r ON r.reservation_id = ri.reservation_id
            WHERE r.status NOT IN ('cancelled','rejected')
              AND r.start_date <= :end_date
              AND r.end_date   >= :start_date
            GROUP BY ri.equipment_id
        ) b ON b.equipment_id = e.equipment_id
        WHERE e.total_stock > 0
          AND e.condition_status = 'Baik'
        HAVING available_stock > 0
        ORDER BY c.category_name, e.equipment_name
    ");
    $stmt->execute([':start_date' => $start_date, ':end_date' => $end_date]);
    $available_equipment = $stmt->fetchAll();

    foreach ($available_equipment as $eq) {
        $cid = $eq['category_id'];
        if (!isset($equipment_by_cat[$cid])) {
            $equipment_by_cat[$cid] = ['cat_name' => $eq['cat_name'], 'items' => []];
        }
        $equipment_by_cat[$cid]['items'][] = $eq;
    }
}

// ── Equipment details for Step 3 summary ────────────────
$selected_eq_data = [];
if ($step === 3 && !empty($_SESSION['res_equipment'])) {
    $ids    = array_column($_SESSION['res_equipment'], 'equipment_id');
    $qtyMap = array_column($_SESSION['res_equipment'], 'quantity', 'equipment_id');
    $in     = implode(',', array_fill(0, count($ids), '?'));
    $res    = $db->prepare("
        SELECT e.*, c.category_name AS cat_name
        FROM equipment e
        JOIN categories c ON c.category_id = e.category_id
        WHERE e.equipment_id IN ($in)
    ");
    $res->execute($ids);
    while ($row = $res->fetch()) {
        $row['qty']      = $qtyMap[$row['equipment_id']] ?? 1;
        $row['subtotal'] = $row['rental_price'] * $row['qty'] * $rental_days;
        $selected_eq_data[] = $row;
    }
    $grand_total = array_sum(array_column($selected_eq_data, 'subtotal'));
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
    <nav class="breadcrumb-nav">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
        <li class="breadcrumb-item"><a href="display.php">Catalog</a></li>
        <li class="breadcrumb-item active">Reservation</li>
      </ol>
    </nav>
    <h1 class="page-hero-title">
      <?= $active_tab === 'check' ? 'Cek Status Booking' : 'Buat Reservasi' ?>
    </h1>
    <p class="page-hero-sub">
      <?= $active_tab === 'check'
          ? 'Masukkan kode booking Anda untuk melihat status reservasi.'
          : 'Ikuti langkah di bawah untuk menyewa peralatan.' ?>
    </p>
  </div>
</section>

<!-- RESERVATION FLOW -->
<section class="section-pad reservation-page">
  <div class="container">

    <!-- ── Tab Switcher ── -->
    <div class="res-tab-wrap">
      <a href="reservation.php"
         class="res-tab <?= $active_tab === 'book' ? 'active' : '' ?>">
        <i class="bi bi-calendar-plus-fill"></i>
        Buat Reservasi
      </a>
      <a href="reservation.php?tab=check"
         class="res-tab <?= $active_tab === 'check' ? 'active' : '' ?>">
        <i class="bi bi-search"></i>
        Cek Status Booking
      </a>
    </div>

    <?php if ($active_tab === 'check'): ?>
    <!-- ══════════════════════════════════════════════════
         CHECK BOOKING TAB
         ══════════════════════════════════════════════════ -->
    <div class="row justify-content-center">
      <div class="col-lg-7">

        <!-- Search Form -->
        <div class="res-box mb-4">
          <h3 class="res-box-title">
            <i class="bi bi-search me-2" style="color:var(--primary)"></i>
            Cek Status Reservasi
          </h3>
          <p class="res-box-sub">
            Masukkan kode booking dan email atau nomor HP yang Anda gunakan saat booking.
          </p>

          <?php if (!empty($check_errors)): ?>
          <div class="alert-error mb-3 d-flex align-items-start gap-2">
            <i class="bi bi-exclamation-circle-fill mt-1 flex-shrink-0"></i>
            <ul class="mb-0 ps-2">
              <?php foreach ($check_errors as $e): ?>
              <li><?= htmlspecialchars($e) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
          <?php endif; ?>

          <form method="POST" action="reservation.php?tab=check">
            <input type="hidden" name="action" value="check_booking_status">
            <div class="row g-3">
              <div class="col-sm-5">
                <label class="form-label" for="booking_id">
                  Kode Booking <span style="color:var(--danger)">*</span>
                </label>
                <div class="input-group">
                  <span class="input-group-text" style="border:1.5px solid var(--line-1);border-right:none;background:var(--surface-1);font-weight:700;color:var(--primary);font-size:.85rem">
                    #
                  </span>
                  <input type="number" class="form-control" id="booking_id" name="booking_id"
                         placeholder="00001"
                         value="<?= htmlspecialchars($_POST['booking_id'] ?? '') ?>"
                         min="1" style="border-left:none !important" required>
                </div>
                <div class="form-text">Nomor booking dari email konfirmasi</div>
              </div>

              <div class="col-sm-7">
                <label class="form-label" for="identifier">
                  Email / No. HP <span style="color:var(--danger)">*</span>
                </label>
                <input type="text" class="form-control" id="identifier" name="identifier"
                       placeholder="email@domain.com atau 08xxxxxxxxxx"
                       value="<?= htmlspecialchars($_POST['identifier'] ?? '') ?>"
                       required>
                <div class="form-text">Digunakan sebagai verifikasi identitas</div>
              </div>

              <div class="col-12">
                <button type="submit" class="btn btn-primary w-100" style="padding:.82rem">
                  <i class="bi bi-search me-2"></i>CEK STATUS BOOKING
                </button>
              </div>
            </div>
          </form>
        </div>

        <?php if ($check_result): ?>
        <!-- ── Result Card ── -->
        <?php
        // Status config
        $status_cfg = [
            'pending'   => ['label'=>'PENDING',    'color'=>'#F59E0B','bg'=>'#FEF3C7','icon'=>'bi-clock-fill',        'msg'=>'Reservasi Anda sedang menunggu konfirmasi dari tim kami.'],
            'approved'  => ['label'=>'APPROVED',   'color'=>'#22C55E','bg'=>'#DCFCE7','icon'=>'bi-check-circle-fill', 'msg'=>'Reservasi Anda telah disetujui. Tim kami akan segera menghubungi Anda.'],
            'rejected'  => ['label'=>'REJECTED',   'color'=>'#EF4444','bg'=>'#FEE2E2','icon'=>'bi-x-circle-fill',     'msg'=>'Maaf, reservasi Anda tidak dapat diproses. Hubungi kami untuk informasi lebih lanjut.'],
            'completed' => ['label'=>'COMPLETED',  'color'=>'#2563EB','bg'=>'#DBEAFE','icon'=>'bi-bag-check-fill',    'msg'=>'Reservasi telah selesai. Terima kasih telah menggunakan layanan kami!'],
            'cancelled' => ['label'=>'CANCELLED',  'color'=>'#64748B','bg'=>'#F1F5F9','icon'=>'bi-slash-circle-fill', 'msg'=>'Reservasi ini telah dibatalkan.'],
        ];
        $s     = $check_result['status'] ?? 'pending';
        $scfg  = $status_cfg[$s] ?? $status_cfg['pending'];
        $d1    = new DateTime($check_result['start_date']);
        $d2    = new DateTime($check_result['end_date']);
        $days  = max(1, (int)$d1->diff($d2)->days);
        ?>

        <div class="check-result-card">

          <!-- Status banner -->
          <div class="crb-status-banner" style="background:<?= $scfg['bg'] ?>;border-color:<?= $scfg['color'] ?>33">
            <div class="crb-status-icon" style="background:<?= $scfg['color'] ?>22;border:1.5px solid <?= $scfg['color'] ?>44">
              <i class="bi <?= $scfg['icon'] ?>" style="color:<?= $scfg['color'] ?>"></i>
            </div>
            <div class="flex-grow-1">
              <div class="d-flex align-items-center gap-2 mb-1">
                <span class="crb-status-badge" style="background:<?= $scfg['color'] ?>;color:#fff">
                  <?= $scfg['label'] ?>
                </span>
                <span class="crb-booking-id">
                  Booking #<?= str_pad($check_result['reservation_id'], 5, '0', STR_PAD_LEFT) ?>
                </span>
              </div>
              <p class="crb-status-msg" style="color:<?= $scfg['color'] ?>">
                <?= $scfg['msg'] ?>
              </p>
            </div>
          </div>

          <!-- Details grid -->
          <div class="crb-body">
            <h6 class="crb-section-title">Detail Reservasi</h6>
            <div class="crb-detail-grid">
              <div class="crb-detail-item">
                <span class="crb-detail-lbl"><i class="bi bi-person-fill"></i> Nama</span>
                <span class="crb-detail-val"><?= htmlspecialchars($check_result['customer_name']) ?></span>
              </div>
              <div class="crb-detail-item">
                <span class="crb-detail-lbl"><i class="bi bi-telephone-fill"></i> No. HP</span>
                <span class="crb-detail-val"><?= htmlspecialchars($check_result['phone']) ?></span>
              </div>
              <div class="crb-detail-item">
                <span class="crb-detail-lbl"><i class="bi bi-envelope-fill"></i> Email</span>
                <span class="crb-detail-val"><?= htmlspecialchars($check_result['email']) ?></span>
              </div>
              <div class="crb-detail-item">
                <span class="crb-detail-lbl"><i class="bi bi-calendar-event-fill"></i> Tanggal Mulai</span>
                <span class="crb-detail-val"><?= date('d M Y', strtotime($check_result['start_date'])) ?></span>
              </div>
              <div class="crb-detail-item">
                <span class="crb-detail-lbl"><i class="bi bi-calendar-check-fill"></i> Tanggal Selesai</span>
                <span class="crb-detail-val"><?= date('d M Y', strtotime($check_result['end_date'])) ?></span>
              </div>
              <div class="crb-detail-item">
                <span class="crb-detail-lbl"><i class="bi bi-clock-fill"></i> Durasi</span>
                <span class="crb-detail-val"><?= $days ?> hari</span>
              </div>
              <div class="crb-detail-item">
                <span class="crb-detail-lbl"><i class="bi bi-calendar3"></i> Dibuat</span>
                <span class="crb-detail-val"><?= date('d M Y, H:i', strtotime($check_result['created_at'])) ?> WIT</span>
              </div>
              <?php if (!empty($check_result['notes'])): ?>
              <div class="crb-detail-item crb-detail-full">
                <span class="crb-detail-lbl"><i class="bi bi-chat-left-text-fill"></i> Catatan</span>
                <span class="crb-detail-val"><?= htmlspecialchars($check_result['notes']) ?></span>
              </div>
              <?php endif; ?>
            </div>

            <!-- Equipment list -->
            <?php if (!empty($check_items)): ?>
            <h6 class="crb-section-title mt-4">Peralatan yang Dipesan</h6>
            <div class="crb-items-list">
              <?php foreach ($check_items as $item): ?>
              <div class="crb-item-row">
                <div class="crb-item-info">
                  <span class="crb-item-cat"><?= htmlspecialchars($item['category_name']) ?></span>
                  <span class="crb-item-name"><?= htmlspecialchars($item['equipment_name']) ?></span>
                </div>
                <div class="crb-item-right">
                  <span class="crb-item-qty">× <?= $item['quantity'] ?></span>
                  <span class="crb-item-sub">
                    Rp <?= number_format($item['subtotal'], 0, ',', '.') ?>
                  </span>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Total -->
            <?php if (!empty($check_result['total_amount']) && $check_result['total_amount'] > 0): ?>
            <div class="crb-total-row">
              <span>Estimasi Total</span>
              <span class="crb-total-val">
                Rp <?= number_format($check_result['total_amount'], 0, ',', '.') ?>
              </span>
            </div>
            <?php endif; ?>

            <!-- Info note -->
            <div class="alert-info-custom mt-3">
              <i class="bi bi-info-circle-fill me-2"></i>
              Untuk pertanyaan lebih lanjut, hubungi kami via WhatsApp di
              <strong>+62 812-3456-7890</strong> dengan menyebutkan kode booking Anda.
            </div>
          </div>
        </div>
        <?php endif; // $check_result ?>

      </div>
    </div>

    <?php else: // $active_tab === 'book' ?>

    <!-- Step Indicator -->
    <div class="steps-wrap">
      <div class="step-node <?= $step >= 1 ? ($step > 1 ? 'done' : 'active') : '' ?>">
        <div class="step-circle"><?= $step > 1 ? '<i class="bi bi-check-lg"></i>' : '1' ?></div>
        <span class="step-lbl">Select Dates</span>
      </div>
      <div class="step-line <?= $step > 1 ? 'done' : '' ?>"></div>
      <div class="step-node <?= $step >= 2 ? ($step > 2 ? 'done' : 'active') : '' ?>">
        <div class="step-circle"><?= $step > 2 ? '<i class="bi bi-check-lg"></i>' : '2' ?></div>
        <span class="step-lbl">Choose Equipment</span>
      </div>
      <div class="step-line <?= $step > 2 ? 'done' : '' ?>"></div>
      <div class="step-node <?= $step >= 3 ? 'active' : '' ?>">
        <div class="step-circle">3</div>
        <span class="step-lbl">Booking Details</span>
      </div>
    </div>

    <!-- Errors -->
    <?php if (!empty($errors)): ?>
    <div class="alert-error mb-4 d-flex align-items-start gap-2">
      <i class="bi bi-exclamation-circle-fill mt-1 flex-shrink-0"></i>
      <ul class="mb-0 ps-2">
        <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
      </ul>
    </div>
    <?php endif; ?>

    <!-- ══════════ STEP 1: Date Selection ══════════ -->
    <?php if ($step === 1): ?>
    <div class="row justify-content-center">
      <div class="col-lg-6">
        <div class="res-box">
          <h3 class="res-box-title">
            <i class="bi bi-calendar3 me-2" style="color:var(--primary)"></i>Select Rental Dates
          </h3>
          <p class="res-box-sub">Choose your dates and we'll show available equipment.</p>

          <form method="POST" action="reservation.php">
            <input type="hidden" name="action" value="check_availability">
            <div class="row g-3 mb-4">
              <div class="col-sm-6">
                <div class="date-field">
                  <label for="start_date">
                    <i class="bi bi-calendar-event me-1" style="color:var(--primary)"></i>
                    Start Date <span style="color:var(--danger)">*</span>
                  </label>
                  <input type="date" id="start_date" name="start_date"
                         value="<?= htmlspecialchars($start_date ?: date('Y-m-d')) ?>"
                         min="<?= date('Y-m-d') ?>" required>
                </div>
              </div>
              <div class="col-sm-6">
                <div class="date-field">
                  <label for="end_date">
                    <i class="bi bi-calendar-check me-1" style="color:var(--success)"></i>
                    End Date <span style="color:var(--danger)">*</span>
                  </label>
                  <input type="date" id="end_date" name="end_date"
                         value="<?= htmlspecialchars($end_date) ?>"
                         min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                </div>
              </div>
            </div>
            <div class="alert-info-custom mb-4">
              <i class="bi bi-info-circle-fill me-2"></i>
              Only equipment available for your selected dates will be shown.
            </div>
            <div class="d-grid">
              <button type="submit" class="btn btn-primary btn-lg" style="padding:0.9rem">
                <i class="bi bi-search me-2"></i>CHECK AVAILABILITY
              </button>
            </div>
          </form>
        </div>

        <!-- Mini guide -->
        <div class="row g-3 mt-3">
          <?php foreach ([['1','Pick dates','Check availability'],['2','Select items','Set quantities'],['3','Fill details','Complete booking']] as $sg): ?>
          <div class="col-4">
            <div class="step-guide-card">
              <div class="step-guide-num"><?= $sg[0] ?></div>
              <p class="step-guide-title"><?= $sg[1] ?></p>
              <p class="step-guide-sub"><?= $sg[2] ?></p>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <!-- ══════════ STEP 2: Equipment Selection ══════════ -->
    <?php if ($step === 2): ?>
    <form method="POST" action="reservation.php" id="step2Form">
      <input type="hidden" name="action" value="select_equipment">

      <!-- Date bar -->
      <div class="date-summary-bar mb-4">
        <div class="d-flex align-items-center flex-wrap gap-3">
          <div class="dsb-item">
            <i class="bi bi-calendar-event me-1" style="color:var(--primary)"></i>
            <strong>Start:</strong> <?= date('d M Y', strtotime($start_date)) ?>
          </div>
          <i class="bi bi-arrow-right text-muted"></i>
          <div class="dsb-item">
            <i class="bi bi-calendar-check me-1" style="color:var(--success)"></i>
            <strong>End:</strong> <?= date('d M Y', strtotime($end_date)) ?>
          </div>
          <span class="dsb-badge"><?= $rental_days ?> day<?= $rental_days > 1 ? 's' : '' ?></span>
        </div>
        <a href="reservation.php?back=1" class="btn btn-outline-secondary btn-sm flex-shrink-0">
          <i class="bi bi-pencil me-1"></i>Change Dates
        </a>
      </div>

      <div id="step2Alert"></div>

      <?php if (empty($available_equipment)): ?>
      <div class="alert-empty">
        <i class="bi bi-calendar-x"></i>
        <h5>No Equipment Available</h5>
        <p>Sorry, no equipment is available for your selected dates. Please try different dates.</p>
        <a href="reservation.php?back=1" class="btn btn-primary mt-3">
          <i class="bi bi-arrow-left me-1"></i>Change Dates
        </a>
      </div>
      <?php else: ?>

      <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="fw-bold mb-0">
          <i class="bi bi-check2-circle me-2" style="color:var(--success)"></i>
          <?= count($available_equipment) ?> item<?= count($available_equipment) != 1 ? 's' : '' ?> available
        </h5>
        <small class="text-muted">Click a card to select · set quantity below</small>
      </div>

      <?php foreach ($equipment_by_cat as $cat_id => $cat_group):
        $cm  = $cat_meta[$cat_id] ?? ['icon'=>'bi-box-fill','gradient'=>'linear-gradient(135deg,#1e3a5f,#2563EB)'];
      ?>
      <div class="mb-4">
        <div class="cat-group-header">
          <div class="cat-group-icon" style="background:<?= $cm['gradient'] ?>">
            <i class="bi <?= $cm['icon'] ?>"></i>
          </div>
          <h6 class="cat-group-title"><?= htmlspecialchars($cat_group['cat_name']) ?></h6>
        </div>
        <div class="row g-3">
          <?php foreach ($cat_group['items'] as $eq): ?>
          <div class="col-sm-6 col-lg-4">
            <div class="eq-sel-card" id="card_<?= $eq['equipment_id'] ?>">
              <input type="checkbox" class="eq-checkbox d-none"
                     name="equipment_ids[]"
                     value="<?= $eq['equipment_id'] ?>"
                     id="chk_<?= $eq['equipment_id'] ?>">

              <div class="eq-sel-img-wrap">
                <?php if (!empty($eq['image_path'])): ?>
                <img src="../<?= htmlspecialchars($eq['image_path']) ?>"
                     class="eq-sel-img"
                     alt="<?= htmlspecialchars($eq['equipment_name']) ?>"
                     loading="lazy"
                     onerror="this.outerHTML='<div class=\'eq-sel-placeholder\' style=\'background:<?= addslashes($cm['gradient']) ?>\'><i class=\'bi <?= $cm['icon'] ?>\'></i></div>'">
                <?php else: ?>
                <div class="eq-sel-placeholder" style="background:<?= $cm['gradient'] ?>">
                  <i class="bi <?= $cm['icon'] ?>"></i>
                </div>
                <?php endif; ?>
                <div class="eq-sel-check" id="check_<?= $eq['equipment_id'] ?>"></div>
              </div>

              <div class="eq-sel-body">
                <h6 class="eq-sel-name"><?= htmlspecialchars($eq['equipment_name']) ?></h6>
                <p class="eq-sel-cat"><?= htmlspecialchars($eq['cat_name']) ?></p>
                <span class="eq-avail">
                  <i class="bi bi-check-circle-fill"></i>
                  <?= $eq['available_stock'] ?> available
                </span>
                <div class="eq-price-tag">
                  Rp <?= number_format($eq['rental_price'], 0, ',', '.') ?><small>/day</small>
                </div>
                <div>
                  <label style="font-size:0.72rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.8px;display:block;margin-bottom:0.35rem">Quantity</label>
                  <div class="qty-wrap">
                    <button type="button" class="qty-btn" onclick="changeQty(<?= $eq['equipment_id'] ?>, -1)">−</button>
                    <input type="number" class="qty-val"
                           id="qty_<?= $eq['equipment_id'] ?>"
                           name="qty[<?= $eq['equipment_id'] ?>]"
                           value="1" min="1"
                           max="<?= $eq['available_stock'] ?>"
                           data-max="<?= $eq['available_stock'] ?>"
                           readonly>
                    <button type="button" class="qty-btn" onclick="changeQty(<?= $eq['equipment_id'] ?>, 1)">+</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endforeach; ?>

      <div class="d-flex justify-content-between align-items-center mt-4 pt-3" style="border-top:1px solid var(--border)">
        <a href="reservation.php?back=1" class="btn btn-outline-secondary">
          <i class="bi bi-arrow-left me-1"></i>Back
        </a>
        <button type="submit" class="btn btn-primary btn-lg px-5">
          CONTINUE BOOKING <i class="bi bi-arrow-right ms-2"></i>
        </button>
      </div>
      <?php endif; ?>
    </form>
    <?php endif; ?>

    <!-- ══════════ STEP 3: Booking Form ══════════ -->
    <?php if ($step === 3): ?>
    <form method="POST" action="reservation.php">
      <input type="hidden" name="action" value="complete_booking">

      <div class="row g-4">
        <!-- Form -->
        <div class="col-lg-7">
          <div class="res-box">
            <h3 class="res-box-title">
              <i class="bi bi-person-fill me-2" style="color:var(--primary)"></i>Your Contact Details
            </h3>
            <p class="res-box-sub">We'll use this information to confirm your reservation.</p>

            <div class="row g-3">
              <div class="col-12">
                <label class="form-label" for="customer_name">
                  Full Name <span style="color:var(--danger)">*</span>
                </label>
                <input type="text" class="form-control" id="customer_name" name="customer_name"
                       placeholder="e.g. Budi Santoso"
                       value="<?= htmlspecialchars($_POST['customer_name'] ?? '') ?>" required>
              </div>

              <div class="col-sm-6">
                <label class="form-label" for="email">
                  Email Address <span style="color:var(--danger)">*</span>
                </label>
                <input type="email" class="form-control" id="email" name="email"
                       placeholder="budi@email.com"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
              </div>

              <div class="col-sm-6">
                <label class="form-label" for="phone">
                  Phone Number <span style="color:var(--danger)">*</span>
                </label>
                <div class="input-group">
                  <span class="input-group-text" style="border:1.5px solid var(--border);border-right:none;background:var(--bg)">
                    <i class="bi bi-telephone-fill" style="color:var(--primary)"></i>
                  </span>
                  <input type="tel" class="form-control" id="phone" name="phone"
                         placeholder="08xxxxxxxxxx"
                         value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
                         style="border-left:none !important" required>
                </div>
                <div class="form-text">We'll contact you here to confirm the reservation.</div>
              </div>

              <div class="col-12">
                <label class="form-label" for="notes">
                  Notes / Additional Information
                  <span style="color:var(--text-faint);font-weight:400;font-size:0.78rem">(optional)</span>
                </label>
                <textarea class="form-control" id="notes" name="notes" rows="4"
                          placeholder="Delivery address, event venue, special requirements, setup instructions..."><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>
                <div class="form-text">Include your event venue or delivery address here.</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Summary -->
        <div class="col-lg-5">
          <div class="res-box">
            <h5 class="fw-bold mb-3">
              <i class="bi bi-receipt me-2" style="color:var(--primary)"></i>Booking Summary
            </h5>

            <div class="booking-summary mb-3">
              <p class="bs-title">Rental Period</p>
              <div class="bs-row">
                <span class="bs-label">Start Date</span>
                <span class="bs-value"><?= date('d M Y', strtotime($start_date)) ?></span>
              </div>
              <div class="bs-row">
                <span class="bs-label">End Date</span>
                <span class="bs-value"><?= date('d M Y', strtotime($end_date)) ?></span>
              </div>
              <div class="bs-row">
                <span class="bs-label">Duration</span>
                <span class="bs-value"><?= $rental_days ?> day<?= $rental_days > 1 ? 's' : '' ?></span>
              </div>
            </div>

            <div class="booking-summary mb-3">
              <p class="bs-title">Selected Equipment</p>
              <?php foreach ($selected_eq_data as $eq): ?>
              <div class="bs-row">
                <span class="bs-label" style="max-width:55%">
                  <?= htmlspecialchars($eq['equipment_name']) ?>
                </span>
                <span class="bs-value">
                  × <?= $eq['qty'] ?>
                </span>
              </div>
              <?php endforeach; ?>
              <?php if (isset($grand_total)): ?>
              <div class="bs-row" style="margin-top:0.5rem;padding-top:0.75rem;border-top:2px solid var(--border);border-bottom:none">
                <span class="bs-label fw-bold" style="color:var(--text)">Estimated Total</span>
                <span class="bs-value" style="color:var(--primary);font-size:1rem">
                  Rp <?= number_format($grand_total, 0, ',', '.') ?>
                </span>
              </div>
              <?php endif; ?>
            </div>

            <div class="pending-notice mb-3">
              <div class="d-flex align-items-center gap-2 mb-1">
                <i class="bi bi-clock-fill" style="color:var(--warning)"></i>
                <strong style="font-size:0.85rem">Status: PENDING Review</strong>
              </div>
              <p style="font-size:0.78rem;color:var(--text-muted);margin:0;line-height:1.6">
                No payment required now. Our team will review and contact you to confirm.
              </p>
            </div>

            <div class="d-grid">
              <button type="submit" class="btn btn-success btn-lg" style="padding:0.9rem">
                <i class="bi bi-check-circle-fill me-2"></i>COMPLETE BOOKING
              </button>
            </div>
          </div>

          <div class="text-center mt-3">
            <a href="reservation.php?back=2" class="btn btn-link text-muted" style="font-size:0.85rem">
              <i class="bi bi-arrow-left me-1"></i>Back to Equipment Selection
            </a>
          </div>
        </div>
      </div>
    </form>
    <?php endif; // step 3 ?>

    <?php endif; // tab === 'book' ?>

  </div>
</section>

<?php require_once '../src/includes/footer.php'; ?>
