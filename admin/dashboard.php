<?php
session_start();
require_once '../src/config/database.php';

$page_title = 'Dashboard';
$current_page = 'dashboard';
$base_path = '../assets/css/admin.css'; // Path CSS untuk file di folder admin/
$is_admin_folder = true; // Menandakan file ini di folder admin/

// Check authentication
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$db = getDB();

// Get statistics
$total_equipment = $db->query("SELECT COUNT(*) FROM equipment")->fetchColumn();
$total_categories = $db->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$total_reservations = $db->query("SELECT COUNT(*) FROM reservations")->fetchColumn();
$pending_reservations = $db->query("SELECT COUNT(*) FROM reservations WHERE status = 'pending'")->fetchColumn();

// Get equipment by condition
$equipment_good = $db->query("SELECT COUNT(*) FROM equipment WHERE condition_status = 'Baik'")->fetchColumn();
$equipment_maintenance = $db->query("SELECT COUNT(*) FROM equipment WHERE condition_status = 'Maintenance'")->fetchColumn();
$equipment_broken = $db->query("SELECT COUNT(*) FROM equipment WHERE condition_status = 'Rusak'")->fetchColumn();

// Get recent reservations
$recent_reservations = $db->query("
    SELECT r.*, 
           COUNT(ri.reservation_item_id) as item_count
    FROM reservations r
    LEFT JOIN reservation_items ri ON r.reservation_id = ri.reservation_id
    GROUP BY r.reservation_id
    ORDER BY r.created_at DESC
    LIMIT 5
")->fetchAll();

require_once '../src/includes/admin-header.php';
?>

<div class="admin-header">
    <div class="admin-header-content">
        <div class="header-title">
            <h2>Dashboard</h2>
            <p>Selamat datang, <?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?>!</p>
        </div>
        <div class="header-actions">
            <a href="logout.php" class="btn-logout">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        </div>
    </div>
</div>

<div class="admin-content">
    
    <!-- Statistics Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        
        <!-- Total Equipment -->
        <div class="card" style="border-top: 3px solid var(--admin-primary);">
            <div class="card-body">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <div style="width: 56px; height: 56px; background: linear-gradient(135deg, #2563EB, #3B82F6); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem;">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <div style="flex: 1;">
                        <p style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--gray-500); margin-bottom: 0.25rem;">Total Equipment</p>
                        <h3 style="font-size: 1.875rem; font-weight: 800; color: var(--gray-900); margin: 0;"><?= $total_equipment ?></h3>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Total Categories -->
        <div class="card" style="border-top: 3px solid var(--success);">
            <div class="card-body">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <div style="width: 56px; height: 56px; background: linear-gradient(135deg, #22C55E, #4ADE80); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem;">
                        <i class="bi bi-grid-3x3-gap"></i>
                    </div>
                    <div style="flex: 1;">
                        <p style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--gray-500); margin-bottom: 0.25rem;">Kategori</p>
                        <h3 style="font-size: 1.875rem; font-weight: 800; color: var(--gray-900); margin: 0;"><?= $total_categories ?></h3>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Total Reservations -->
        <div class="card" style="border-top: 3px solid var(--warning);">
            <div class="card-body">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <div style="width: 56px; height: 56px; background: linear-gradient(135deg, #F59E0B, #FBBF24); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem;">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <div style="flex: 1;">
                        <p style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--gray-500); margin-bottom: 0.25rem;">Reservasi</p>
                        <h3 style="font-size: 1.875rem; font-weight: 800; color: var(--gray-900); margin: 0;"><?= $total_reservations ?></h3>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Pending Reservations -->
        <div class="card" style="border-top: 3px solid var(--danger);">
            <div class="card-body">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <div style="width: 56px; height: 56px; background: linear-gradient(135deg, #EF4444, #F87171); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem;">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div style="flex: 1;">
                        <p style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--gray-500); margin-bottom: 0.25rem;">Pending</p>
                        <h3 style="font-size: 1.875rem; font-weight: 800; color: var(--gray-900); margin: 0;"><?= $pending_reservations ?></h3>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
        
        <!-- Equipment by Condition -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="bi bi-speedometer"></i> Status Kondisi Equipment
                </h3>
            </div>
            <div class="card-body">
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div style="flex: 1;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                <span style="font-size: 0.875rem; font-weight: 600; color: var(--gray-700);">
                                    <i class="bi bi-check-circle-fill" style="color: var(--success);"></i> Baik
                                </span>
                                <span style="font-size: 0.875rem; font-weight: 700; color: var(--success);">
                                    <?= $equipment_good ?>
                                </span>
                            </div>
                            <div style="background: var(--gray-200); height: 10px; border-radius: 999px; overflow: hidden;">
                                <div style="background: var(--success); height: 100%; width: <?= $total_equipment > 0 ? ($equipment_good / $total_equipment * 100) : 0 ?>%; border-radius: 999px;"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div style="flex: 1;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                <span style="font-size: 0.875rem; font-weight: 600; color: var(--gray-700);">
                                    <i class="bi bi-exclamation-triangle-fill" style="color: var(--warning);"></i> Maintenance
                                </span>
                                <span style="font-size: 0.875rem; font-weight: 700; color: var(--warning);">
                                    <?= $equipment_maintenance ?>
                                </span>
                            </div>
                            <div style="background: var(--gray-200); height: 10px; border-radius: 999px; overflow: hidden;">
                                <div style="background: var(--warning); height: 100%; width: <?= $total_equipment > 0 ? ($equipment_maintenance / $total_equipment * 100) : 0 ?>%; border-radius: 999px;"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div style="flex: 1;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                <span style="font-size: 0.875rem; font-weight: 600; color: var(--gray-700);">
                                    <i class="bi bi-x-circle-fill" style="color: var(--danger);"></i> Rusak
                                </span>
                                <span style="font-size: 0.875rem; font-weight: 700; color: var(--danger);">
                                    <?= $equipment_broken ?>
                                </span>
                            </div>
                            <div style="background: var(--gray-200); height: 10px; border-radius: 999px; overflow: hidden;">
                                <div style="background: var(--danger); height: 100%; width: <?= $total_equipment > 0 ? ($equipment_broken / $total_equipment * 100) : 0 ?>%; border-radius: 999px;"></div>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="bi bi-lightning-charge"></i> Quick Actions
                </h3>
            </div>
            <div class="card-body">
                <div style="display: grid; gap: 0.75rem;">
                    <a href="../src/inventory/equipment-create.php" class="btn btn-primary" style="width: 100%; justify-content: flex-start;">
                        <i class="bi bi-plus-circle"></i> Tambah Equipment Baru
                    </a>
                    <a href="../src/inventory/category-create.php" class="btn btn-success" style="width: 100%; justify-content: flex-start;">
                        <i class="bi bi-grid-3x3-gap"></i> Tambah Kategori Baru
                    </a>
                    <a href="reservations.php?status=pending" class="btn btn-warning" style="width: 100%; justify-content: flex-start;">
                        <i class="bi bi-clock-history"></i> Reservasi Pending
                    </a>
                    <a href="reports.php" class="btn btn-secondary" style="width: 100%; justify-content: flex-start;">
                        <i class="bi bi-file-earmark-bar-graph"></i> Lihat Laporan
                    </a>
                </div>
            </div>
        </div>
        
    </div>
    
    <!-- Recent Reservations -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="bi bi-clock-history"></i> Reservasi Terbaru
            </h3>
            <a href="reservations.php" class="btn btn-secondary btn-sm">
                <i class="bi bi-eye"></i> Lihat Semua
            </a>
        </div>
        <div class="card-body">
            <?php if (empty($recent_reservations)): ?>
            <div style="text-align: center; padding: 2rem; color: var(--gray-400);">
                <i class="bi bi-inbox" style="font-size: 3rem; display: block; margin-bottom: 1rem; opacity: 0.5;"></i>
                Belum ada reservasi
            </div>
            <?php else: ?>
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama Customer</th>
                            <th>Telepon</th>
                            <th>Tanggal</th>
                            <th>Items</th>
                            <th>Status</th>
                            <th>Total</th>
                            <th style="text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_reservations as $res): ?>
                        <tr>
                            <td style="font-weight: 600;">RES-<?= str_pad($res['reservation_id'], 4, '0', STR_PAD_LEFT) ?></td>
                            <td><?= htmlspecialchars($res['customer_name']) ?></td>
                            <td><?= htmlspecialchars($res['phone']) ?></td>
                            <td style="font-size: 0.8125rem;">
                                <?= date('d/m/Y', strtotime($res['start_date'])) ?> - <?= date('d/m/Y', strtotime($res['end_date'])) ?>
                            </td>
                            <td style="text-align: center;">
                                <span class="badge badge-info"><?= $res['item_count'] ?> item</span>
                            </td>
                            <td>
                                <?php
                                $status_map = [
                                    'pending' => ['badge-warning', 'Pending'],
                                    'approved' => ['badge-success', 'Approved'],
                                    'rejected' => ['badge-danger', 'Rejected'],
                                    'completed' => ['badge-info', 'Completed'],
                                    'cancelled' => ['badge-danger', 'Cancelled']
                                ];
                                $status_info = $status_map[$res['status']] ?? ['badge-info', $res['status']];
                                ?>
                                <span class="badge <?= $status_info[0] ?>"><?= $status_info[1] ?></span>
                            </td>
                            <td style="font-weight: 700; color: var(--admin-primary);">
                                Rp <?= number_format($res['total_amount'], 0, ',', '.') ?>
                            </td>
                            <td>
                                <a href="reservations.php?view=<?= $res['reservation_id'] ?>" class="btn btn-secondary btn-sm" title="Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
</div>

<?php require_once '../src/includes/admin-footer.php'; ?>
