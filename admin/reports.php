<?php
session_start();
require_once '../src/config/database.php';

$page_title      = 'Laporan';
$current_page    = 'reports';
$base_path       = '../assets/css/admin.css';
$is_admin_folder = true;

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$db = getDB();

// ── Date filter ───────────────────────────────────────────
$filter_from = $_GET['date_from'] ?? date('Y-m-01'); // default: first day of current month
$filter_to   = $_GET['date_to']   ?? date('Y-m-d');  // default: today

// ── Revenue Statistics ────────────────────────────────────
$revenue_sql = "
    SELECT
        COUNT(*) AS total_reservations,
        SUM(total_amount) AS total_revenue,
        AVG(total_amount) AS avg_revenue,
        SUM(CASE WHEN status = 'completed' THEN total_amount ELSE 0 END) AS completed_revenue,
        SUM(CASE WHEN status = 'pending'   THEN 1 ELSE 0 END) AS pending_count,
        SUM(CASE WHEN status = 'approved'  THEN 1 ELSE 0 END) AS approved_count,
        SUM(CASE WHEN status = 'rejected'  THEN 1 ELSE 0 END) AS rejected_count,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed_count,
        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled_count
    FROM reservations
    WHERE created_at BETWEEN ? AND ?
";
$stmt = $db->prepare($revenue_sql);
$stmt->execute([$filter_from . ' 00:00:00', $filter_to . ' 23:59:59']);
$stats = $stmt->fetch();

// ── Top Equipment (Most Rented) ───────────────────────────
$top_equipment = $db->prepare("
    SELECT
        e.equipment_name,
        e.image_path,
        c.category_name,
        COUNT(ri.reservation_item_id) AS rental_count,
        SUM(ri.quantity) AS total_qty,
        SUM(ri.subtotal) AS total_revenue
    FROM reservation_items ri
    INNER JOIN equipment e  ON e.equipment_id = ri.equipment_id
    INNER JOIN categories c ON c.category_id  = e.category_id
    INNER JOIN reservations r ON r.reservation_id = ri.reservation_id
    WHERE r.created_at BETWEEN ? AND ?
      AND r.status NOT IN ('cancelled', 'rejected')
    GROUP BY e.equipment_id
    ORDER BY rental_count DESC, total_qty DESC
    LIMIT 10
");
$top_equipment->execute([$filter_from . ' 00:00:00', $filter_to . ' 23:59:59']);
$top_equip_rows = $top_equipment->fetchAll();

// ── Revenue by Status ─────────────────────────────────────
$revenue_by_status = $db->prepare("
    SELECT
        status,
        COUNT(*) AS count,
        SUM(total_amount) AS revenue
    FROM reservations
    WHERE created_at BETWEEN ? AND ?
    GROUP BY status
    ORDER BY revenue DESC
");
$revenue_by_status->execute([$filter_from . ' 00:00:00', $filter_to . ' 23:59:59']);
$revenue_status_rows = $revenue_by_status->fetchAll();

// ── Revenue by Category ───────────────────────────────────
$revenue_by_category = $db->prepare("
    SELECT
        c.category_name,
        COUNT(DISTINCT ri.reservation_id) AS reservation_count,
        SUM(ri.quantity) AS total_qty,
        SUM(ri.subtotal) AS total_revenue
    FROM reservation_items ri
    INNER JOIN equipment e  ON e.equipment_id = ri.equipment_id
    INNER JOIN categories c ON c.category_id  = e.category_id
    INNER JOIN reservations r ON r.reservation_id = ri.reservation_id
    WHERE r.created_at BETWEEN ? AND ?
      AND r.status NOT IN ('cancelled', 'rejected')
    GROUP BY c.category_id
    ORDER BY total_revenue DESC
");
$revenue_by_category->execute([$filter_from . ' 00:00:00', $filter_to . ' 23:59:59']);
$category_rows = $revenue_by_category->fetchAll();

// ── Daily Revenue Trend (for chart or table) ──────────────
$daily_revenue = $db->prepare("
    SELECT
        DATE(created_at) AS date,
        COUNT(*) AS reservation_count,
        SUM(total_amount) AS revenue
    FROM reservations
    WHERE created_at BETWEEN ? AND ?
    GROUP BY DATE(created_at)
    ORDER BY date ASC
");
$daily_revenue->execute([$filter_from . ' 00:00:00', $filter_to . ' 23:59:59']);
$daily_rows = $daily_revenue->fetchAll();

// ── Export to CSV ─────────────────────────────────────────
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="laporan_' . date('Y-m-d_His') . '.csv"');

    $out = fopen('php://output', 'w');
    fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM for Excel

    // Section 1: Overview
    fputcsv($out, ['LAPORAN RESERVASI']);
    fputcsv($out, ['Periode', $filter_from . ' s/d ' . $filter_to]);
    fputcsv($out, []);
    fputcsv($out, ['Total Reservasi', $stats['total_reservations']]);
    fputcsv($out, ['Total Pendapatan', 'Rp ' . number_format($stats['total_revenue'] ?? 0, 0, ',', '.')]);
    fputcsv($out, ['Rata-rata/Transaksi', 'Rp ' . number_format($stats['avg_revenue'] ?? 0, 0, ',', '.')]);
    fputcsv($out, ['Pendapatan Selesai', 'Rp ' . number_format($stats['completed_revenue'] ?? 0, 0, ',', '.')]);
    fputcsv($out, []);

    // Section 2: Status Breakdown
    fputcsv($out, ['STATUS', 'JUMLAH', 'PENDAPATAN']);
    foreach ($revenue_status_rows as $row) {
        fputcsv($out, [
            strtoupper($row['status']),
            $row['count'],
            'Rp ' . number_format($row['revenue'], 0, ',', '.')
        ]);
    }
    fputcsv($out, []);

    // Section 3: Top Equipment
    fputcsv($out, ['EQUIPMENT TERLARIS']);
    fputcsv($out, ['Nama Equipment', 'Kategori', 'Jumlah Sewa', 'Total Qty', 'Pendapatan']);
    foreach ($top_equip_rows as $row) {
        fputcsv($out, [
            $row['equipment_name'],
            $row['category_name'],
            $row['rental_count'],
            $row['total_qty'],
            'Rp ' . number_format($row['total_revenue'], 0, ',', '.')
        ]);
    }
    fputcsv($out, []);

    // Section 4: Category Revenue
    fputcsv($out, ['PENDAPATAN PER KATEGORI']);
    fputcsv($out, ['Kategori', 'Jumlah Reservasi', 'Total Qty', 'Pendapatan']);
    foreach ($category_rows as $row) {
        fputcsv($out, [
            $row['category_name'],
            $row['reservation_count'],
            $row['total_qty'],
            'Rp ' . number_format($row['total_revenue'], 0, ',', '.')
        ]);
    }
    fputcsv($out, []);

    // Section 5: Daily Trend
    fputcsv($out, ['TREN HARIAN']);
    fputcsv($out, ['Tanggal', 'Jumlah Reservasi', 'Pendapatan']);
    foreach ($daily_rows as $row) {
        fputcsv($out, [
            date('d M Y', strtotime($row['date'])),
            $row['reservation_count'],
            'Rp ' . number_format($row['revenue'], 0, ',', '.')
        ]);
    }

    fclose($out);
    exit;
}

require_once '../src/includes/admin-header.php';
?>

<div class="admin-header">
    <div class="admin-header-content">
        <div class="header-title">
            <h2><i class="bi bi-file-earmark-bar-graph me-2" style="color:var(--admin-primary)"></i>Laporan & Analitik</h2>
            <p>Pantau performa bisnis dan analisis data reservasi</p>
        </div>
        <div class="header-actions">
            <a href="?date_from=<?= htmlspecialchars($filter_from) ?>&date_to=<?= htmlspecialchars($filter_to) ?>&export=csv"
               class="btn btn-success">
                <i class="bi bi-file-earmark-spreadsheet"></i> Export CSV
            </a>
            <a href="logout.php" class="btn-logout">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        </div>
    </div>
</div>

<div class="admin-content">

<!-- ── Date Filter ──────────────────────────────────────── -->
<div class="card" style="margin-bottom:1.5rem">
    <div class="card-body" style="padding:1rem 1.5rem">
        <form method="GET" style="display:flex;flex-wrap:wrap;gap:1rem;align-items:flex-end">
            <div>
                <label style="font-size:.8125rem;font-weight:600;color:var(--gray-600);display:block;margin-bottom:.35rem">
                    <i class="bi bi-calendar-event"></i> Dari Tanggal
                </label>
                <input type="date" name="date_from" class="form-control"
                       value="<?= htmlspecialchars($filter_from) ?>" required>
            </div>
            <div>
                <label style="font-size:.8125rem;font-weight:600;color:var(--gray-600);display:block;margin-bottom:.35rem">
                    <i class="bi bi-calendar-check"></i> Sampai Tanggal
                </label>
                <input type="date" name="date_to" class="form-control"
                       value="<?= htmlspecialchars($filter_to) ?>" required>
            </div>
            <div style="display:flex;gap:.5rem">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-bar-chart"></i> Tampilkan Laporan
                </button>
                <a href="reports.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-clockwise"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- ── Statistics Overview ──────────────────────────────── -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:1.5rem;margin-bottom:2rem">

    <div class="card" style="border-top:3px solid var(--admin-primary)">
        <div class="card-body">
            <div style="display:flex;align-items:center;gap:1rem">
                <div style="width:56px;height:56px;background:linear-gradient(135deg,#2563EB,#3B82F6);border-radius:12px;display:flex;align-items:center;justify-content:center;color:white;font-size:1.5rem;flex-shrink:0">
                    <i class="bi bi-calendar-check"></i>
                </div>
                <div style="flex:1">
                    <p style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--gray-500);margin-bottom:.25rem">
                        Total Reservasi
                    </p>
                    <h3 style="font-size:1.875rem;font-weight:800;color:var(--gray-900);margin:0">
                        <?= $stats['total_reservations'] ?? 0 ?>
                    </h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card" style="border-top:3px solid var(--success)">
        <div class="card-body">
            <div style="display:flex;align-items:center;gap:1rem">
                <div style="width:56px;height:56px;background:linear-gradient(135deg,#22C55E,#4ADE80);border-radius:12px;display:flex;align-items:center;justify-content:center;color:white;font-size:1.5rem;flex-shrink:0">
                    <i class="bi bi-currency-dollar"></i>
                </div>
                <div style="flex:1">
                    <p style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--gray-500);margin-bottom:.25rem">
                        Total Pendapatan
                    </p>
                    <h3 style="font-size:1.5rem;font-weight:800;color:var(--gray-900);margin:0">
                        Rp <?= number_format($stats['total_revenue'] ?? 0, 0, ',', '.') ?>
                    </h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card" style="border-top:3px solid var(--warning)">
        <div class="card-body">
            <div style="display:flex;align-items:center;gap:1rem">
                <div style="width:56px;height:56px;background:linear-gradient(135deg,#F59E0B,#FBBF24);border-radius:12px;display:flex;align-items:center;justify-content:center;color:white;font-size:1.5rem;flex-shrink:0">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>
                <div style="flex:1">
                    <p style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--gray-500);margin-bottom:.25rem">
                        Rata-rata / Transaksi
                    </p>
                    <h3 style="font-size:1.5rem;font-weight:800;color:var(--gray-900);margin:0">
                        Rp <?= number_format($stats['avg_revenue'] ?? 0, 0, ',', '.') ?>
                    </h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card" style="border-top:3px solid #7C3AED">
        <div class="card-body">
            <div style="display:flex;align-items:center;gap:1rem">
                <div style="width:56px;height:56px;background:linear-gradient(135deg,#7C3AED,#A855F7);border-radius:12px;display:flex;align-items:center;justify-content:center;color:white;font-size:1.5rem;flex-shrink:0">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div style="flex:1">
                    <p style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--gray-500);margin-bottom:.25rem">
                        Pendapatan Selesai
                    </p>
                    <h3 style="font-size:1.5rem;font-weight:800;color:var(--gray-900);margin:0">
                        Rp <?= number_format($stats['completed_revenue'] ?? 0, 0, ',', '.') ?>
                    </h3>
                </div>
            </div>
        </div>
    </div>

</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:2rem">

    <!-- ── Revenue by Status ────────────────────────────── -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="bi bi-pie-chart"></i> Reservasi per Status
            </h3>
        </div>
        <div class="card-body">
            <?php if (empty($revenue_status_rows)): ?>
            <div style="text-align:center;padding:2rem;color:var(--gray-400)">
                <i class="bi bi-inbox" style="font-size:2.5rem;display:block;margin-bottom:.75rem;opacity:.5"></i>
                <small>Tidak ada data untuk periode ini</small>
            </div>
            <?php else: ?>
            <div style="display:flex;flex-direction:column;gap:1rem">
                <?php
                $status_colors = [
                    'pending'   => ['#F59E0B', 'Pending',   'bi-clock-fill'],
                    'approved'  => ['#22C55E', 'Approved',  'bi-check-circle-fill'],
                    'rejected'  => ['#EF4444', 'Rejected',  'bi-x-circle-fill'],
                    'completed' => ['#2563EB', 'Completed', 'bi-bag-check-fill'],
                    'cancelled' => ['#64748B', 'Cancelled', 'bi-slash-circle-fill'],
                ];
                $max_revenue = max(array_column($revenue_status_rows, 'revenue'));
                foreach ($revenue_status_rows as $row):
                    $sc     = $status_colors[$row['status']] ?? ['#64748B', ucfirst($row['status']), 'bi-circle'];
                    $pct    = $max_revenue > 0 ? ($row['revenue'] / $max_revenue * 100) : 0;
                ?>
                <div>
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.5rem">
                        <span style="font-size:.875rem;font-weight:600;color:var(--gray-700)">
                            <i class="bi <?= $sc[2] ?>" style="color:<?= $sc[0] ?>"></i> <?= $sc[1] ?>
                        </span>
                        <div style="text-align:right">
                            <span style="font-weight:700;color:<?= $sc[0] ?>;font-size:.9375rem">
                                <?= $row['count'] ?>x
                            </span>
                            <span style="color:var(--gray-500);font-size:.8125rem;margin-left:.5rem">
                                Rp <?= number_format($row['revenue'], 0, ',', '.') ?>
                            </span>
                        </div>
                    </div>
                    <div style="background:var(--gray-200);height:10px;border-radius:999px;overflow:hidden">
                        <div style="background:<?= $sc[0] ?>;height:100%;width:<?= $pct ?>%;border-radius:999px"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ── Top Equipment ────────────────────────────────── -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="bi bi-trophy"></i> Equipment Terlaris
            </h3>
        </div>
        <div class="card-body">
            <?php if (empty($top_equip_rows)): ?>
            <div style="text-align:center;padding:2rem;color:var(--gray-400)">
                <i class="bi bi-inbox" style="font-size:2.5rem;display:block;margin-bottom:.75rem;opacity:.5"></i>
                <small>Tidak ada data rental</small>
            </div>
            <?php else: ?>
            <div style="display:flex;flex-direction:column;gap:.75rem">
                <?php foreach (array_slice($top_equip_rows, 0, 5) as $idx => $eq): ?>
                <div style="display:flex;align-items:center;gap:.75rem;padding:.75rem;border-radius:8px;background:var(--gray-50)">
                    <div style="width:38px;height:38px;background:linear-gradient(135deg,#2563EB,#3B82F6);border-radius:8px;display:flex;align-items:center;justify-content:center;color:white;font-size:.875rem;font-weight:800;flex-shrink:0">
                        <?= $idx + 1 ?>
                    </div>
                    <div style="flex:1;min-width:0">
                        <strong style="font-size:.875rem;display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                            <?= htmlspecialchars($eq['equipment_name']) ?>
                        </strong>
                        <small style="color:var(--gray-500);font-size:.75rem">
                            <?= htmlspecialchars($eq['category_name']) ?>
                        </small>
                    </div>
                    <div style="text-align:right;flex-shrink:0">
                        <div style="font-weight:700;color:var(--admin-primary);font-size:.875rem">
                            <?= $eq['rental_count'] ?>x
                        </div>
                        <small style="color:var(--gray-500);font-size:.7rem">
                            Qty: <?= $eq['total_qty'] ?>
                        </small>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- ── Revenue by Category ───────────────────────────────── -->
<div class="card" style="margin-bottom:2rem">
    <div class="card-header">
        <h3 class="card-title">
            <i class="bi bi-grid-3x3-gap"></i> Pendapatan per Kategori
        </h3>
    </div>
    <div class="card-body">
        <?php if (empty($category_rows)): ?>
        <div style="text-align:center;padding:2rem;color:var(--gray-400)">
            <i class="bi bi-inbox" style="font-size:2.5rem;display:block;margin-bottom:.75rem;opacity:.5"></i>
            <small>Tidak ada data kategori</small>
        </div>
        <?php else: ?>
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kategori</th>
                        <th style="text-align:center">Jumlah Reservasi</th>
                        <th style="text-align:center">Total Qty</th>
                        <th style="text-align:right">Pendapatan</th>
                        <th style="text-align:right">Porsi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $total_cat_revenue = array_sum(array_column($category_rows, 'total_revenue'));
                    $no = 1;
                    foreach ($category_rows as $cat):
                        $pct = $total_cat_revenue > 0 ? ($cat['total_revenue'] / $total_cat_revenue * 100) : 0;
                    ?>
                    <tr>
                        <td style="font-weight:600;color:var(--gray-500)"><?= $no++ ?></td>
                        <td>
                            <strong style="font-size:.9375rem"><?= htmlspecialchars($cat['category_name']) ?></strong>
                        </td>
                        <td style="text-align:center">
                            <span class="badge badge-info"><?= $cat['reservation_count'] ?>x</span>
                        </td>
                        <td style="text-align:center;font-weight:600">
                            <?= $cat['total_qty'] ?>
                        </td>
                        <td style="text-align:right;font-weight:700;color:var(--admin-primary)">
                            Rp <?= number_format($cat['total_revenue'], 0, ',', '.') ?>
                        </td>
                        <td style="text-align:right">
                            <div style="display:flex;align-items:center;gap:.5rem;justify-content:flex-end">
                                <div style="flex:1;max-width:80px;background:var(--gray-200);height:8px;border-radius:999px;overflow:hidden">
                                    <div style="background:var(--success);height:100%;width:<?= $pct ?>%;border-radius:999px"></div>
                                </div>
                                <span style="font-size:.8125rem;font-weight:700;color:var(--gray-700);min-width:45px">
                                    <?= number_format($pct, 1) ?>%
                                </span>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot style="background:var(--gray-50);border-top:2px solid var(--gray-200)">
                    <tr>
                        <td colspan="4" style="font-weight:700;font-size:.9375rem">Total</td>
                        <td style="text-align:right;font-weight:800;font-size:1rem;color:var(--admin-primary)">
                            Rp <?= number_format($total_cat_revenue, 0, ',', '.') ?>
                        </td>
                        <td style="text-align:right;font-weight:700">100%</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- ── Daily Revenue Trend ───────────────────────────────── -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="bi bi-graph-up"></i> Tren Harian
        </h3>
    </div>
    <div class="card-body">
        <?php if (empty($daily_rows)): ?>
        <div style="text-align:center;padding:2rem;color:var(--gray-400)">
            <i class="bi bi-inbox" style="font-size:2.5rem;display:block;margin-bottom:.75rem;opacity:.5"></i>
            <small>Tidak ada data harian untuk periode ini</small>
        </div>
        <?php else: ?>
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th style="text-align:center">Jumlah Reservasi</th>
                        <th style="text-align:right">Pendapatan</th>
                        <th style="text-align:right">Visualisasi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $max_daily = max(array_column($daily_rows, 'revenue'));
                    foreach ($daily_rows as $day):
                        $pct = $max_daily > 0 ? ($day['revenue'] / $max_daily * 100) : 0;
                    ?>
                    <tr>
                        <td style="font-weight:600;font-size:.875rem">
                            <?= date('d M Y', strtotime($day['date'])) ?>
                        </td>
                        <td style="text-align:center">
                            <span class="badge badge-info"><?= $day['reservation_count'] ?>x</span>
                        </td>
                        <td style="text-align:right;font-weight:700;color:var(--admin-primary)">
                            Rp <?= number_format($day['revenue'], 0, ',', '.') ?>
                        </td>
                        <td style="text-align:right">
                            <div style="display:flex;align-items:center;justify-content:flex-end;gap:.5rem">
                                <div style="flex:1;max-width:150px;background:var(--gray-200);height:8px;border-radius:999px;overflow:hidden">
                                    <div style="background:linear-gradient(90deg,#2563EB,#3B82F6);height:100%;width:<?= $pct ?>%;border-radius:999px"></div>
                                </div>
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

</div><!-- /admin-content -->

<?php require_once '../src/includes/admin-footer.php'; ?>
