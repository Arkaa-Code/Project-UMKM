<?php
session_start();

if (empty($_SESSION['booking_success'])) {
    header('Location: index.php');
    exit;
}

$booking      = $_SESSION['booking_success'];
$page_title   = 'Booking Confirmed — RZKY Equipment Services';
$current_page = '';

unset($_SESSION['booking_success']);

require_once '../src/includes/header.php';
require_once '../src/includes/navbar.php';
?>

<div class="success-page">
  <div class="success-wrap">
    <div class="success-card">

      <!-- Animated icon -->
      <div class="success-icon-ring">
        <i class="bi bi-check-lg"></i>
      </div>

      <h2 class="success-title">Reservation Submitted!</h2>
      <p class="success-msg">
        Thank you, <strong><?= htmlspecialchars($booking['name']) ?></strong>!<br>
        Our team will review your reservation and <strong>contact you via phone or WhatsApp</strong>
        shortly to confirm availability and next steps.
      </p>

      <!-- Booking details -->
      <div class="success-detail-box">
        <div class="sdb-row">
          <span class="sdb-lbl">Booking ID</span>
          <span class="sdb-val" style="color:var(--primary)">#<?= str_pad($booking['id'], 5, '0', STR_PAD_LEFT) ?></span>
        </div>
        <div class="sdb-row">
          <span class="sdb-lbl">Name</span>
          <span class="sdb-val"><?= htmlspecialchars($booking['name']) ?></span>
        </div>
        <div class="sdb-row">
          <span class="sdb-lbl">Email</span>
          <span class="sdb-val"><?= htmlspecialchars($booking['email']) ?></span>
        </div>
        <div class="sdb-row">
          <span class="sdb-lbl">Phone</span>
          <span class="sdb-val"><?= htmlspecialchars($booking['phone']) ?></span>
        </div>
        <div class="sdb-row">
          <span class="sdb-lbl">Start Date</span>
          <span class="sdb-val"><?= date('d F Y', strtotime($booking['start_date'])) ?></span>
        </div>
        <div class="sdb-row">
          <span class="sdb-lbl">End Date</span>
          <span class="sdb-val"><?= date('d F Y', strtotime($booking['end_date'])) ?></span>
        </div>
        <div class="sdb-row">
          <span class="sdb-lbl">Duration</span>
          <span class="sdb-val"><?= $booking['days'] ?> day<?= $booking['days'] > 1 ? 's' : '' ?></span>
        </div>
        <div class="sdb-row">
          <span class="sdb-lbl">Est. Total</span>
          <span class="sdb-val" style="color:var(--primary);font-size:1rem">
            Rp <?= number_format($booking['total'], 0, ',', '.') ?>
          </span>
        </div>
        <div class="sdb-row">
          <span class="sdb-lbl">Status</span>
          <span class="sdb-val"><span class="badge-status-pending">PENDING</span></span>
        </div>
      </div>

      <!-- What's next -->
      <div class="next-steps-box mb-4">
        <p class="next-steps-title">What Happens Next?</p>
        <?php
        $next_steps = [
            ['bi-telephone-fill',   '#2563EB', 'Confirmation Call',    'Our team will call or WhatsApp you within 1–2 hours.'],
            ['bi-clipboard-check',  '#22C55E', 'Availability Check',   'We confirm equipment and discuss rental terms.'],
            ['bi-truck',            '#F59E0B', 'Delivery Scheduling',  'We arrange on-time delivery and setup at your venue.'],
        ];
        foreach ($next_steps as $idx => $s): ?>
        <div class="next-step-item <?= $idx < count($next_steps) - 1 ? 'mb-2' : '' ?>">
          <div class="ns-icon" style="background:<?= $s[1] ?>18;border:1px solid <?= $s[1] ?>30">
            <i class="bi <?= $s[0] ?>" style="color:<?= $s[1] ?>"></i>
          </div>
          <div>
            <p class="ns-title"><?= $s[2] ?></p>
            <p class="ns-desc"><?= $s[3] ?></p>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Action buttons -->
      <div class="d-flex gap-3 justify-content-center flex-wrap">
        <a href="index.php" class="btn btn-outline-secondary px-4">
          <i class="bi bi-house-fill me-2"></i>Back to Home
        </a>
        <a href="display.php" class="btn btn-primary px-4">
          <i class="bi bi-grid-fill me-2"></i>Explore Catalog
        </a>
      </div>

    </div>
  </div>
</div>

<?php require_once '../src/includes/footer.php'; ?>
