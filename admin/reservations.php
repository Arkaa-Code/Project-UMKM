<?php
session_start();
require_once '../src/config/database.php';

$page_title   = 'Manajemen Reservasi';
$current_page = 'reservations';
$base_path    = '../assets/css/admin.css';
$is_admin_folder = true;

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$db = getDB();

// ── Handle status update ──────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $reservation_id = (int)($_POST['reservation_id'] ?? 0);
    $new_status     = $_POST['new_status'] ?? '';
    $allowed = ['approved', 'rejected', 'completed', 'cancelled', 'pending'];

    if ($reservation_id > 0 && in_array($new_status, $allowed)) {
        try {
            $stmt = $db->prepare("UPDATE reservations SET status = ? WHERE reservation_id = ?");
            $stmt->execute([$new_status, $reservation_id]);

            // ── Generate PDF invoice when approving ──────
            if ($new_status === 'approved') {
                try {
                    require_once __DIR__ . '/../src/includes/invoice-generator.php';
                    $inv_path = generateInvoicePDF($reservation_id, 'F');
                    if ($inv_path) {
                        $upd = $db->prepare("UPDATE reservations SET invoice_path = ? WHERE reservation_id = ?");
                        $upd->execute([$inv_path, $reservation_id]);
                        $_SESSION['invoice_path_' . $reservation_id] = $inv_path;
                    }
                } catch (Throwable $e) {
                    // Invoice generation failed — jangan batalkan approval, log saja
                    $_SESSION['invoice_error'] = 'Invoice gagal digenerate: ' . $e->getMessage();
                }
            }

            $labels = [
                'approved'  => 'Reservasi berhasil disetujui. Invoice PDF telah digenerate.',
                'rejected'  => 'Reservasi berhasil ditolak.',
                'completed' => 'Reservasi ditandai selesai.',
                'cancelled' => 'Reservasi berhasil dibatalkan.',
                'pending'   => 'Status dikembalikan ke pending.',
            ];
            $_SESSION['success_message'] = $labels[$new_status] ?? 'Status berhasil diperbarui.';
        } catch (PDOException $e) {
            $_SESSION['error_message'] = 'Gagal memperbarui status reservasi.';
        }
    }
    $qs = http_build_query(array_filter([
        'status'  => $_POST['filter_status'] ?? '',
        'search'  => $_POST['filter_search'] ?? '',
        'date_from'=> $_POST['filter_date_from'] ?? '',
        'date_to'  => $_POST['filter_date_to'] ?? '',
    ]));
    header('Location: reservations.php' . ($qs ? "?$qs" : ''));
    exit;
}

// ── Filters ───────────────────────────────────────────────
$filter_status    = $_GET['status']    ?? '';
$filter_search    = trim($_GET['search']    ?? '');
$filter_date_from = $_GET['date_from'] ?? '';
$filter_date_to   = $_GET['date_to']   ?? '';

// ── View single reservation detail ───────────────────────
$view_id = isset($_GET['view']) ? (int)$_GET['view'] : 0;
$view_reservation = null;
$view_items       = [];

if ($view_id > 0) {
    $stmt = $db->prepare("SELECT * FROM reservations WHERE reservation_id = ?");
    $stmt->execute([$view_id]);
    $view_reservation = $stmt->fetch();

    // Also check session for freshly generated invoice
    if ($view_reservation && !empty($_SESSION['invoice_path_' . $view_id])) {
        $view_reservation['invoice_path'] = $_SESSION['invoice_path_' . $view_id];
    }

    if ($view_reservation) {
        $iStmt = $db->prepare("
            SELECT ri.*, e.equipment_name, e.image_path, c.category_name
            FROM reservation_items ri
            JOIN equipment e  ON e.equipment_id = ri.equipment_id
            JOIN categories c ON c.category_id  = e.category_id
            WHERE ri.reservation_id = ?
            ORDER BY c.category_name, e.equipment_name
        ");
        $iStmt->execute([$view_id]);
        $view_items = $iStmt->fetchAll();
    }
}

// ── Build main query ─────────────────────────────────────
$where  = [];
$params = [];

if ($filter_status !== '') {
    $where[]  = 'r.status = ?';
    $params[] = $filter_status;
}
if ($filter_search !== '') {
    $where[]  = '(r.customer_name LIKE ? OR r.email LIKE ? OR r.phone LIKE ?)';
    $like     = "%$filter_search%";
    $params   = array_merge($params, [$like, $like, $like]);
}
if ($filter_date_from !== '') {
    $where[]  = 'r.start_date >= ?';
    $params[] = $filter_date_from;
}
if ($filter_date_to !== '') {
    $where[]  = 'r.end_date <= ?';
    $params[] = $filter_date_to;
}

$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$stmt = $db->prepare("
    SELECT r.*,
           COUNT(ri.reservation_item_id) AS item_count
    FROM reservations r
    LEFT JOIN reservation_items ri ON r.reservation_id = ri.reservation_id
    $where_sql
    GROUP BY r.reservation_id
    ORDER BY r.created_at DESC
");
$stmt->execute($params);
$reservations = $stmt->fetchAll();

// Status counts for filter pills
$status_counts = $db->query("
    SELECT status, COUNT(*) AS total FROM reservations GROUP BY status
")->fetchAll(PDO::FETCH_KEY_PAIR);

$status_map = [
    'pending'   => ['badge-warning', 'Pending',   'bi-clock-fill',         '#F59E0B'],
    'approved'  => ['badge-success', 'Approved',  'bi-check-circle-fill',  '#22C55E'],
    'rejected'  => ['badge-danger',  'Rejected',  'bi-x-circle-fill',      '#EF4444'],
    'completed' => ['badge-info',    'Completed', 'bi-bag-check-fill',     '#2563EB'],
    'cancelled' => ['badge-danger',  'Cancelled', 'bi-slash-circle-fill',  '#64748B'],
];

require_once '../src/includes/admin-header.php';
?>

<!-- PAGE HEADER -->
<div class="admin-header">
    <div class="admin-header-content">
        <div class="header-title">
            <h2><i class="bi bi-calendar-check me-2" style="color:var(--admin-primary)"></i>Manajemen Reservasi</h2>
            <p>Kelola dan pantau semua reservasi pelanggan</p>
        </div>
        <div class="header-actions">
            <a href="logout.php" class="btn-logout">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        </div>
    </div>
</div>

<div class="admin-content">

<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success">
    <i class="bi bi-check-circle"></i>
    <span><?= htmlspecialchars($_SESSION['success_message']) ?></span>
</div>
<?php unset($_SESSION['success_message']); endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-error">
    <i class="bi bi-exclamation-circle"></i>
    <span><?= htmlspecialchars($_SESSION['error_message']) ?></span>
</div>
<?php unset($_SESSION['error_message']); endif; ?>

<?php if (isset($_SESSION['invoice_error'])): ?>
<div class="alert alert-error">
    <i class="bi bi-file-earmark-x"></i>
    <span><?= htmlspecialchars($_SESSION['invoice_error']) ?></span>
</div>
<?php unset($_SESSION['invoice_error']); endif; ?>

<!-- ── Status Summary Pills ───────────────────────────── -->
<div style="display:flex;flex-wrap:wrap;gap:.75rem;margin-bottom:1.5rem">
    <?php
    $all_count = array_sum($status_counts);
    $is_all    = $filter_status === '';
    ?>
    <a href="reservations.php"
       style="display:inline-flex;align-items:center;gap:.5rem;padding:.5rem 1rem;border-radius:9999px;font-size:.8125rem;font-weight:600;text-decoration:none;
              background:<?= $is_all ? 'var(--admin-primary)' : 'var(--gray-200)' ?>;
              color:<?= $is_all ? 'white' : 'var(--gray-700)' ?>">
        <i class="bi bi-list-ul"></i> Semua <span>(<?= $all_count ?>)</span>
    </a>
    <?php foreach ($status_map as $key => [$badge, $label, $icon, $color]): ?>
    <?php $cnt = $status_counts[$key] ?? 0; $is_active = $filter_status === $key; ?>
    <a href="reservations.php?status=<?= $key ?>"
       style="display:inline-flex;align-items:center;gap:.5rem;padding:.5rem 1rem;border-radius:9999px;font-size:.8125rem;font-weight:600;text-decoration:none;
              background:<?= $is_active ? $color : 'var(--gray-200)' ?>;
              color:<?= $is_active ? 'white' : 'var(--gray-700)' ?>">
        <i class="bi <?= $icon ?>"></i> <?= $label ?> <span>(<?= $cnt ?>)</span>
    </a>
    <?php endforeach; ?>
</div>

<!-- ── Filters ────────────────────────────────────────── -->
<div class="card" style="margin-bottom:1.5rem">
    <div class="card-body" style="padding:1rem 1.5rem">
        <form method="GET" style="display:flex;flex-wrap:wrap;gap:1rem;align-items:flex-end">
            <?php if ($filter_status): ?>
            <input type="hidden" name="status" value="<?= htmlspecialchars($filter_status) ?>">
            <?php endif; ?>

            <div style="flex:1;min-width:200px">
                <label style="font-size:.8125rem;font-weight:600;color:var(--gray-600);display:block;margin-bottom:.35rem">
                    <i class="bi bi-search"></i> Cari
                </label>
                <input type="text" name="search" class="form-control"
                       placeholder="Nama, email, atau no. HP..."
                       value="<?= htmlspecialchars($filter_search) ?>">
            </div>

            <div>
                <label style="font-size:.8125rem;font-weight:600;color:var(--gray-600);display:block;margin-bottom:.35rem">
                    <i class="bi bi-calendar-event"></i> Dari Tanggal
                </label>
                <input type="date" name="date_from" class="form-control"
                       value="<?= htmlspecialchars($filter_date_from) ?>">
            </div>

            <div>
                <label style="font-size:.8125rem;font-weight:600;color:var(--gray-600);display:block;margin-bottom:.35rem">
                    <i class="bi bi-calendar-check"></i> Sampai Tanggal
                </label>
                <input type="date" name="date_to" class="form-control"
                       value="<?= htmlspecialchars($filter_date_to) ?>">
            </div>

            <div style="display:flex;gap:.5rem">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i> Filter
                </button>
                <a href="reservations.php<?= $filter_status ? '?status='.$filter_status : '' ?>" class="btn btn-secondary">
                    <i class="bi bi-x"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- ── Reservation Table ──────────────────────────────── -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="bi bi-calendar-check"></i> Daftar Reservasi
        </h3>
        <span style="font-size:.875rem;color:var(--gray-500);font-weight:500">
            Menampilkan <?= count($reservations) ?> reservasi
        </span>
    </div>
    <div class="card-body" style="padding:0">

        <?php if (empty($reservations)): ?>
        <div style="text-align:center;padding:3rem;color:var(--gray-400)">
            <i class="bi bi-calendar-x" style="font-size:3rem;display:block;margin-bottom:1rem;opacity:.5"></i>
            <p style="font-weight:600;margin-bottom:.5rem">Tidak ada reservasi ditemukan</p>
            <small>Coba ubah filter pencarian Anda</small>
        </div>
        <?php else: ?>

        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Pelanggan</th>
                        <th>Kontak</th>
                        <th>Tanggal Sewa</th>
                        <th style="text-align:center">Items</th>
                        <th style="text-align:right">Total</th>
                        <th style="text-align:center">Status</th>
                        <th style="text-align:center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($reservations as $res):
                    $si    = $status_map[$res['status']] ?? ['badge-info', $res['status'], 'bi-circle', '#64748B'];
                    $d1    = new DateTime($res['start_date']);
                    $d2    = new DateTime($res['end_date']);
                    $days  = max(1, (int)$d1->diff($d2)->days);
                ?>
                <tr>
                    <td>
                        <strong style="color:var(--admin-primary)">
                            #<?= str_pad($res['reservation_id'], 5, '0', STR_PAD_LEFT) ?>
                        </strong>
                        <br>
                        <small style="color:var(--gray-400);font-size:.75rem">
                            <?= date('d M Y', strtotime($res['created_at'])) ?>
                        </small>
                    </td>
                    <td>
                        <strong><?= htmlspecialchars($res['customer_name']) ?></strong>
                        <?php if (!empty($res['notes'])): ?>
                        <br><small style="color:var(--gray-400);font-size:.75rem" title="<?= htmlspecialchars($res['notes']) ?>">
                            <i class="bi bi-chat-left-text"></i>
                            <?= htmlspecialchars(mb_substr($res['notes'], 0, 35)) ?><?= mb_strlen($res['notes']) > 35 ? '…' : '' ?>
                        </small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div><?= htmlspecialchars($res['phone']) ?></div>
                        <?php if (!empty($res['email'])): ?>
                        <small style="color:var(--gray-500);font-size:.75rem"><?= htmlspecialchars($res['email']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:.8125rem">
                        <div><i class="bi bi-calendar-event" style="color:var(--admin-primary)"></i>
                            <?= date('d M Y', strtotime($res['start_date'])) ?>
                        </div>
                        <div><i class="bi bi-calendar-check" style="color:var(--success)"></i>
                            <?= date('d M Y', strtotime($res['end_date'])) ?>
                        </div>
                        <small style="color:var(--gray-500)"><?= $days ?> hari</small>
                    </td>
                    <td style="text-align:center">
                        <span class="badge badge-info"><?= $res['item_count'] ?> item</span>
                    </td>
                    <td style="text-align:right;font-weight:700;color:var(--admin-primary)">
                        Rp <?= number_format($res['total_amount'], 0, ',', '.') ?>
                    </td>
                    <td style="text-align:center">
                        <span class="badge <?= $si[0] ?>">
                            <i class="bi <?= $si[2] ?>"></i> <?= $si[1] ?>
                        </span>
                    </td>
                    <td>
                        <div class="table-actions" style="justify-content:center;flex-wrap:wrap;gap:.4rem">
                            <!-- Detail -->
                            <a href="reservations.php?view=<?= $res['reservation_id'] ?><?= $filter_status ? '&status='.$filter_status : '' ?><?= $filter_search ? '&search='.urlencode($filter_search) : '' ?>"
                               class="btn btn-secondary btn-sm" title="Lihat Detail">
                                <i class="bi bi-eye"></i>
                            </a>

                            <!-- Approve -->
                            <?php if (in_array($res['status'], ['pending', 'rejected'])): ?>
                            <form method="POST" style="display:inline">
                                <input type="hidden" name="action"          value="update_status">
                                <input type="hidden" name="reservation_id" value="<?= $res['reservation_id'] ?>">
                                <input type="hidden" name="new_status"     value="approved">
                                <input type="hidden" name="filter_status"  value="<?= htmlspecialchars($filter_status) ?>">
                                <input type="hidden" name="filter_search"  value="<?= htmlspecialchars($filter_search) ?>">
                                <button type="submit" class="btn btn-success btn-sm" title="Setujui"
                                        onclick="return confirm('Setujui reservasi ini?')">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                            </form>
                            <?php endif; ?>

                            <!-- Complete -->
                            <?php if ($res['status'] === 'approved'): ?>
                            <form method="POST" style="display:inline">
                                <input type="hidden" name="action"          value="update_status">
                                <input type="hidden" name="reservation_id" value="<?= $res['reservation_id'] ?>">
                                <input type="hidden" name="new_status"     value="completed">
                                <input type="hidden" name="filter_status"  value="<?= htmlspecialchars($filter_status) ?>">
                                <button type="submit" class="btn btn-primary btn-sm" title="Tandai Selesai"
                                        onclick="return confirm('Tandai reservasi ini sebagai selesai?')">
                                    <i class="bi bi-bag-check"></i>
                                </button>
                            </form>
                            <?php endif; ?>

                            <!-- Reject -->
                            <?php if (in_array($res['status'], ['pending', 'approved'])): ?>
                            <form method="POST" style="display:inline">
                                <input type="hidden" name="action"          value="update_status">
                                <input type="hidden" name="reservation_id" value="<?= $res['reservation_id'] ?>">
                                <input type="hidden" name="new_status"     value="rejected">
                                <input type="hidden" name="filter_status"  value="<?= htmlspecialchars($filter_status) ?>">
                                <button type="submit" class="btn btn-danger btn-sm" title="Tolak"
                                        onclick="return confirm('Tolak reservasi ini?')">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </form>
                            <?php endif; ?>

                            <!-- Cancel -->
                            <?php if (in_array($res['status'], ['pending', 'approved'])): ?>
                            <form method="POST" style="display:inline">
                                <input type="hidden" name="action"          value="update_status">
                                <input type="hidden" name="reservation_id" value="<?= $res['reservation_id'] ?>">
                                <input type="hidden" name="new_status"     value="cancelled">
                                <input type="hidden" name="filter_status"  value="<?= htmlspecialchars($filter_status) ?>">
                                <button type="submit" class="btn btn-secondary btn-sm" title="Batalkan"
                                        onclick="return confirm('Batalkan reservasi ini?')">
                                    <i class="bi bi-slash-circle"></i>
                                </button>
                            </form>
                            <?php endif; ?>

                            <!-- Download Invoice (approved only) -->
                            <?php
                            $inv_p = $res['invoice_path'] ?? null;
                            if ($res['status'] === 'approved' && !empty($inv_p) && file_exists(dirname(__DIR__) . '/' . $inv_p)):
                            ?>
                            <a href="../<?= htmlspecialchars($inv_p) ?>" target="_blank"
                               class="btn btn-primary btn-sm" title="Download Invoice PDF"
                               style="background:#2563EB">
                                <i class="bi bi-file-earmark-pdf"></i>
                            </a>
                            <?php endif; ?>

                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

    </div>
</div>

<!-- ══ DETAIL MODAL ══════════════════════════════════════ -->
<?php if ($view_reservation):
    $vsi   = $status_map[$view_reservation['status']] ?? ['badge-info', $view_reservation['status'], 'bi-circle', '#64748B'];
    $d1    = new DateTime($view_reservation['start_date']);
    $d2    = new DateTime($view_reservation['end_date']);
    $days  = max(1, (int)$d1->diff($d2)->days);
?>
<div id="detailModal"
     style="position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:2000;display:flex;align-items:flex-start;justify-content:center;padding:2rem 1rem;overflow-y:auto">
    <div style="background:white;border-radius:16px;max-width:680px;width:100%;box-shadow:0 20px 60px rgba(0,0,0,.3);animation:slideUp .3s ease">

        <!-- Modal Header -->
        <div style="display:flex;align-items:center;justify-content:space-between;padding:1.25rem 1.5rem;border-bottom:1px solid var(--gray-200)">
            <div>
                <h3 style="font-size:1.125rem;font-weight:700;margin:0">
                    Detail Reservasi
                    <span style="color:var(--admin-primary)">
                        #<?= str_pad($view_reservation['reservation_id'], 5, '0', STR_PAD_LEFT) ?>
                    </span>
                </h3>
                <small style="color:var(--gray-500)">
                    Dibuat <?= date('d M Y, H:i', strtotime($view_reservation['created_at'])) ?>
                </small>
            </div>
            <a href="reservations.php?<?= http_build_query(array_filter(['status'=>$filter_status,'search'=>$filter_search,'date_from'=>$filter_date_from,'date_to'=>$filter_date_to])) ?>"
               style="width:36px;height:36px;border-radius:50%;background:var(--gray-100);display:flex;align-items:center;justify-content:center;color:var(--gray-600);font-size:1.25rem;flex-shrink:0">
                &times;
            </a>
        </div>

        <!-- Status Banner -->
        <div style="padding:1.25rem 1.5rem;background:<?= $vsi[3] ?>0d;border-bottom:1px solid <?= $vsi[3] ?>22">
            <div style="display:flex;align-items:center;gap:.75rem">
                <div style="width:44px;height:44px;border-radius:50%;background:<?= $vsi[3] ?>1a;border:1.5px solid <?= $vsi[3] ?>44;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <i class="bi <?= $vsi[2] ?>" style="color:<?= $vsi[3] ?>;font-size:1.25rem"></i>
                </div>
                <div>
                    <span style="display:inline-block;padding:.2rem .8rem;border-radius:999px;background:<?= $vsi[3] ?>;color:white;font-size:.75rem;font-weight:700;text-transform:uppercase">
                        <?= $vsi[1] ?>
                    </span>
                    <p style="margin:.25rem 0 0;font-size:.875rem;color:var(--gray-600)">
                        Status reservasi saat ini
                    </p>
                </div>

                <!-- Update status from modal -->
                <?php if (in_array($view_reservation['status'], ['pending', 'approved'])): ?>
                <div style="margin-left:auto;display:flex;gap:.5rem">
                    <?php if ($view_reservation['status'] === 'pending'): ?>
                    <form method="POST">
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" name="reservation_id" value="<?= $view_reservation['reservation_id'] ?>">
                        <input type="hidden" name="new_status" value="approved">
                        <button type="submit" class="btn btn-success btn-sm"
                                onclick="return confirm('Setujui reservasi ini?')">
                            <i class="bi bi-check-lg"></i> Setujui
                        </button>
                    </form>
                    <form method="POST">
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" name="reservation_id" value="<?= $view_reservation['reservation_id'] ?>">
                        <input type="hidden" name="new_status" value="rejected">
                        <button type="submit" class="btn btn-danger btn-sm"
                                onclick="return confirm('Tolak reservasi ini?')">
                            <i class="bi bi-x-lg"></i> Tolak
                        </button>
                    </form>
                    <?php elseif ($view_reservation['status'] === 'approved'): ?>
                    <form method="POST">
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" name="reservation_id" value="<?= $view_reservation['reservation_id'] ?>">
                        <input type="hidden" name="new_status" value="completed">
                        <button type="submit" class="btn btn-primary btn-sm"
                                onclick="return confirm('Tandai sebagai selesai?')">
                            <i class="bi bi-bag-check"></i> Selesai
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div style="padding:1.5rem">

            <!-- Customer Info -->
            <h5 style="font-size:.875rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--gray-500);margin-bottom:1rem">
                <i class="bi bi-person-fill me-2" style="color:var(--admin-primary)"></i>Data Pelanggan
            </h5>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:1.5rem">
                <?php
                $details = [
                    ['bi-person',       'Nama Pelanggan',  $view_reservation['customer_name']],
                    ['bi-telephone',    'No. Telepon',     $view_reservation['phone']],
                    ['bi-envelope',     'Email',           $view_reservation['email'] ?: '—'],
                ];
                foreach ($details as [$ic, $lbl, $val]):
                ?>
                <div style="background:var(--gray-50);border-radius:8px;padding:.875rem">
                    <small style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:var(--gray-400)">
                        <i class="bi <?= $ic ?> me-1"></i><?= $lbl ?>
                    </small>
                    <div style="font-size:.9375rem;font-weight:600;color:var(--gray-900);margin-top:.3rem">
                        <?= htmlspecialchars($val) ?>
                    </div>
                </div>
                <?php endforeach; ?>
                <div style="background:var(--gray-50);border-radius:8px;padding:.875rem">
                    <small style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:var(--gray-400)">
                        <i class="bi bi-calendar-range me-1"></i>Periode Sewa
                    </small>
                    <div style="font-size:.875rem;font-weight:600;color:var(--gray-900);margin-top:.3rem">
                        <?= date('d M Y', strtotime($view_reservation['start_date'])) ?>
                        <span style="color:var(--gray-400);margin:0 .4rem">→</span>
                        <?= date('d M Y', strtotime($view_reservation['end_date'])) ?>
                        <span style="color:var(--gray-500);font-size:.8rem;font-weight:400"> (<?= $days ?> hari)</span>
                    </div>
                </div>
            </div>

            <?php if (!empty($view_reservation['notes'])): ?>
            <div style="background:#FEF3C7;border:1px solid #FCD34D;border-radius:8px;padding:.875rem;margin-bottom:1.5rem">
                <small style="font-size:.75rem;font-weight:700;text-transform:uppercase;color:#92400E">
                    <i class="bi bi-chat-left-text me-1"></i>Catatan Pelanggan
                </small>
                <p style="margin:.35rem 0 0;font-size:.875rem;color:#78350F">
                    <?= htmlspecialchars($view_reservation['notes']) ?>
                </p>
            </div>
            <?php endif; ?>

            <!-- Equipment Items -->
            <h5 style="font-size:.875rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--gray-500);margin-bottom:1rem">
                <i class="bi bi-box-seam me-2" style="color:var(--admin-primary)"></i>Peralatan yang Dipesan
            </h5>

            <?php if (empty($view_items)): ?>
            <div style="text-align:center;padding:1.5rem;color:var(--gray-400)">
                <i class="bi bi-inbox" style="font-size:2rem;display:block;margin-bottom:.5rem;opacity:.5"></i>
                <small>Tidak ada item tercatat</small>
            </div>
            <?php else: ?>
            <div style="border:1px solid var(--gray-200);border-radius:8px;overflow:hidden;margin-bottom:1.5rem">
                <table style="width:100%;border-collapse:collapse">
                    <thead style="background:var(--gray-50)">
                        <tr>
                            <th style="padding:.75rem 1rem;text-align:left;font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:var(--gray-500)">Equipment</th>
                            <th style="padding:.75rem 1rem;text-align:center;font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:var(--gray-500)">Qty</th>
                            <th style="padding:.75rem 1rem;text-align:right;font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:var(--gray-500)">Harga/Hari</th>
                            <th style="padding:.75rem 1rem;text-align:right;font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:var(--gray-500)">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($view_items as $item): ?>
                    <tr style="border-top:1px solid var(--gray-200)">
                        <td style="padding:.75rem 1rem">
                            <strong style="font-size:.875rem"><?= htmlspecialchars($item['equipment_name']) ?></strong>
                            <br><small style="color:var(--gray-500)"><?= htmlspecialchars($item['category_name']) ?></small>
                        </td>
                        <td style="padding:.75rem 1rem;text-align:center;font-weight:600">× <?= $item['quantity'] ?></td>
                        <td style="padding:.75rem 1rem;text-align:right;font-size:.875rem">
                            Rp <?= number_format($item['rental_price'], 0, ',', '.') ?>
                        </td>
                        <td style="padding:.75rem 1rem;text-align:right;font-weight:700;color:var(--admin-primary)">
                            Rp <?= number_format($item['subtotal'], 0, ',', '.') ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot style="background:var(--gray-50);border-top:2px solid var(--gray-200)">
                        <tr>
                            <td colspan="3" style="padding:.875rem 1rem;font-weight:700;font-size:.9375rem">Total Estimasi</td>
                            <td style="padding:.875rem 1rem;text-align:right;font-weight:800;font-size:1rem;color:var(--admin-primary)">
                                Rp <?= number_format($view_reservation['total_amount'], 0, ',', '.') ?>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <?php endif; ?>

            <div style="text-align:right">
                <a href="reservations.php?<?= http_build_query(array_filter(['status'=>$filter_status,'search'=>$filter_search,'date_from'=>$filter_date_from,'date_to'=>$filter_date_to])) ?>"
                   class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali ke Daftar
                </a>
            </div>

            <?php if ($view_reservation['status'] === 'approved'): ?>
            <!-- ── Invoice Action Panel ── -->
            <?php
            $inv_path   = $view_reservation['invoice_path'] ?? null;
            $inv_exists = !empty($inv_path) && file_exists(dirname(__DIR__) . '/' . $inv_path);
            $wa_msg = rawurlencode(
                'Halo ' . $view_reservation['customer_name'] . ', reservasi Anda di ' .
                ($view_reservation['company_name'] ?? 'RZ Equipment Rental') .
                ' telah disetujui. Periode: ' .
                date('d M Y', strtotime($view_reservation['start_date'])) . ' s/d ' .
                date('d M Y', strtotime($view_reservation['end_date'])) .
                '. Total: Rp ' . number_format($view_reservation['total_amount'], 0, ',', '.') .
                '. Terima kasih!'
            );
            $phone_clean = preg_replace('/[^0-9]/', '', $view_reservation['phone']);
            if (str_starts_with($phone_clean, '0')) $phone_clean = '62' . substr($phone_clean, 1);
            $wa_url = 'https://wa.me/' . $phone_clean . '?text=' . $wa_msg;
            $email_subject = rawurlencode('Konfirmasi Reservasi #' . str_pad($view_reservation['reservation_id'], 5, '0', STR_PAD_LEFT));
            $email_body = rawurlencode(
                'Halo ' . $view_reservation['customer_name'] . ",\n\n" .
                "Reservasi Anda telah DISETUJUI.\n" .
                'Periode: ' . date('d M Y', strtotime($view_reservation['start_date'])) . ' s/d ' . date('d M Y', strtotime($view_reservation['end_date'])) . "\n" .
                'Total: Rp ' . number_format($view_reservation['total_amount'], 0, ',', '.') . "\n\n" .
                "Terima kasih telah menggunakan layanan kami.\n"
            );
            $email_url = 'mailto:' . ($view_reservation['email'] ?? '') . '?subject=' . $email_subject . '&body=' . $email_body;
            ?>

            <div style="margin-top:1.25rem;padding:1.25rem;background:#EFF6FF;border:1px solid #BFDBFE;border-radius:10px">
                <p style="font-size:.875rem;font-weight:700;color:#1E3A8A;margin:0 0 .875rem">
                    <i class="bi bi-send-check-fill me-2"></i>Aksi Notifikasi & Invoice
                </p>
                <div style="display:flex;flex-direction:column;gap:.75rem">

                    <!-- Baris 1: Invoice -->
                    <div style="display:flex;flex-wrap:wrap;gap:.75rem">
                    <?php if ($inv_exists): ?>
                    <a href="../<?= htmlspecialchars($inv_path) ?>" target="_blank"
                       class="btn btn-primary" style="background:#2563EB">
                        <i class="bi bi-file-earmark-pdf-fill"></i> Download Invoice PDF
                    </a>
                    <a href="invoice-stream.php?id=<?= $view_reservation['reservation_id'] ?>" target="_blank"
                       class="btn btn-secondary">
                        <i class="bi bi-eye"></i> Preview Invoice
                    </a>
                    <?php else: ?>
                    <a href="invoice-stream.php?id=<?= $view_reservation['reservation_id'] ?>&regen=1" target="_blank"
                       class="btn btn-primary" style="background:#2563EB"
                       onclick="alert('Invoice sedang digenerate. Tab baru akan terbuka.')">
                        <i class="bi bi-arrow-repeat"></i> Generate Invoice PDF
                    </a>
                    <span style="font-size:.8rem;color:#64748B;align-self:center">
                        <i class="bi bi-info-circle"></i>
                        File PDF belum tersimpan. Klik tombol di atas untuk generate ulang.
                    </span>
                    <?php endif; ?>
                    </div>

                    <!-- Baris 2: WhatsApp -->
                    <div style="display:flex;flex-wrap:wrap;gap:.75rem;align-items:center">
                    <button type="button" class="btn btn-success"
                            onclick="sendNotif(<?= $view_reservation['reservation_id'] ?>, 'wa', this)">
                        <i class="bi bi-whatsapp"></i> Kirim WhatsApp Otomatis
                    </button>
                    <a href="<?= $wa_url ?>" target="_blank"
                       class="btn btn-outline-success btn-sm" style="font-size:.78rem;padding:.3rem .75rem"
                       title="Buka WhatsApp Web (manual)">
                        <i class="bi bi-whatsapp"></i> Manual
                    </a>
                    </div>

                    <!-- Baris 3: Email -->
                    <?php if (!empty($view_reservation['email'])): ?>
                    <div style="display:flex;flex-wrap:wrap;gap:.75rem;align-items:center">
                    <button type="button" class="btn btn-secondary"
                            onclick="sendNotif(<?= $view_reservation['reservation_id'] ?>, 'email', this)">
                        <i class="bi bi-envelope-fill"></i> Kirim Email Otomatis
                    </button>
                    <a href="<?= $email_url ?>"
                       class="btn btn-outline-secondary btn-sm" style="font-size:.78rem;padding:.3rem .75rem"
                       title="Buka email client (manual)" onclick="showEmailSentAlert()">
                        <i class="bi bi-envelope"></i> Manual
                    </a>
                    </div>
                    <?php endif; // if email ?>

                </div><!-- /flex-column -->
            </div><!-- /panel biru -->

            <script>
            function showEmailSentAlert() {
                setTimeout(() => {
                    const a = document.createElement('div');
                    a.innerHTML = `
                        <div id="emailAlert" style="position:fixed;bottom:1.5rem;right:1.5rem;z-index:9999;
                            background:#22C55E;color:white;padding:1rem 1.5rem;border-radius:10px;
                            box-shadow:0 8px 24px rgba(0,0,0,.18);display:flex;align-items:center;gap:.75rem;font-weight:600">
                            <i class="bi bi-envelope-check-fill" style="font-size:1.25rem"></i>
                            Email client telah dibuka. Periksa draft email Anda.
                            <span onclick="document.getElementById('emailAlert').remove()" style="cursor:pointer;margin-left:.5rem;opacity:.8">&times;</span>
                        </div>`;
                    document.body.appendChild(a);
                    setTimeout(() => a.remove(), 5000);
                }, 800);
                return true;
            }

            // Kirim notifikasi server-side (WA via Fonnte / Email via SMTP)
            async function sendNotif(reservationId, type, btn) {
                const label = type === 'wa' ? 'WhatsApp' : 'Email';
                if (!confirm('Kirim ' + label + ' otomatis ke pelanggan?')) return;

                btn.disabled = true;
                const original = btn.innerHTML;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Mengirim...';

                try {
                    const fd = new FormData();
                    fd.append('reservation_id', reservationId);
                    fd.append('type', type);

                    const res  = await fetch('send-notification.php', { method: 'POST', body: fd });
                    const data = await res.json();

                    const color = data.success ? '#22C55E' : '#EF4444';
                    const icon  = data.success ? 'bi-check-circle-fill' : 'bi-x-circle-fill';
                    const msg   = data.success
                        ? label + ' berhasil dikirim ke pelanggan!'
                        : label + ' gagal dikirim: ' + (data.error ?? 'Unknown error');

                    const toast = document.createElement('div');
                    toast.innerHTML = `
                        <div style="position:fixed;bottom:1.5rem;right:1.5rem;z-index:9999;
                            background:${color};color:white;padding:1rem 1.5rem;border-radius:10px;
                            box-shadow:0 8px 24px rgba(0,0,0,.18);display:flex;align-items:center;
                            gap:.75rem;font-weight:600;max-width:360px">
                            <i class="bi ${icon}" style="font-size:1.25rem;flex-shrink:0"></i>
                            <span>${msg}</span>
                        </div>`;
                    document.body.appendChild(toast);
                    setTimeout(() => toast.remove(), 6000);
                } catch (err) {
                    alert('Error: ' + err.message);
                } finally {
                    btn.disabled = false;
                    btn.innerHTML = original;
                }
            }
            </script>
            <?php endif; // approved ?>
    </div><!-- /modal card -->
</div>
<?php endif; // view_reservation ?>

</div><!-- /admin-content -->

<?php require_once '../src/includes/admin-footer.php'; ?>