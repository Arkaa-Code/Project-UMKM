<?php
session_start();
require_once '../config/database.php';

$page_title = 'Daftar Equipment';
$current_page = 'equipment';
$is_admin_folder = false; // File ini di src/inventory/

// Handle delete
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    try {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM equipment WHERE equipment_id = ?");
        $stmt->execute([$delete_id]);
        $_SESSION['success_message'] = 'Equipment berhasil dihapus';
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Gagal menghapus equipment: ' . $e->getMessage();
    }
    header('Location: equipment-list.php');
    exit;
}

// Get search & filter params
$search     = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_cat = isset($_GET['cat'])    ? (int)$_GET['cat']    : 0;

// Get all categories for filter dropdown
$db = getDB();
$categories = $db->query("SELECT * FROM categories ORDER BY category_name")->fetchAll();

// Build query
$sql    = "SELECT e.*, c.category_name FROM equipment e JOIN categories c ON e.category_id = c.category_id";
$where  = [];
$params = [];

if ($search !== '') {
    $where[]  = "(e.equipment_name LIKE ? OR c.category_name LIKE ? OR e.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($filter_cat > 0) {
    $where[]  = "e.category_id = ?";
    $params[] = $filter_cat;
}
if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY e.created_at DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$equipments = $stmt->fetchAll();

require_once '../includes/admin-header.php';
?>

<div class="admin-header">
    <div class="admin-header-content">
        <div class="header-title">
            <h2>Daftar Equipment</h2>
            <p>Kelola peralatan rental</p>
        </div>
        <div class="header-actions">
            <a href="equipment-create.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Tambah Equipment
            </a>
            <a href="../../admin/logout.php" class="btn-logout">
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
    
    <!-- Search & Filter Bar -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-body" style="padding: 1.25rem 1.5rem;">
            <form method="GET" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                <!-- Search Input -->
                <div style="position: relative; flex: 1; min-width: 220px;">
                    <i class="bi bi-search" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--gray-400); font-size: 0.95rem;"></i>
                    <input 
                        type="text" 
                        name="search" 
                        class="form-control" 
                        placeholder="Cari nama equipment atau deskripsi..." 
                        value="<?= htmlspecialchars($search) ?>"
                        style="padding-left: 2.75rem;"
                    >
                </div>
                <!-- Filter Kategori -->
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <i class="bi bi-funnel" style="color: var(--gray-500); font-size: 0.95rem;"></i>
                    <select name="cat" class="form-control" style="width: auto; min-width: 180px;">
                        <option value="0">Semua Kategori</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['category_id'] ?>" <?= $filter_cat == $cat['category_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['category_name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <!-- Tombol -->
                <button type="submit" class="btn btn-primary" style="white-space: nowrap;">
                    <i class="bi bi-search"></i> Search
                </button>
                <?php if ($search !== '' || $filter_cat > 0): ?>
                <a href="equipment-list.php" class="btn btn-secondary" style="white-space: nowrap;">
                    <i class="bi bi-x-circle"></i> Reset
                </a>
                <?php endif; ?>
            </form>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="bi bi-box-seam"></i> Daftar Equipment
            </h3>
            <span style="font-size: 0.875rem; color: var(--gray-500); font-weight: 500;">
                Total: <?= count($equipments) ?> item
            </span>
        </div>
        <div class="card-body">
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 60px;">No</th>
                            <th style="width: 80px;">Gambar</th>
                            <th>Nama Equipment</th>
                            <th>Kategori</th>
                            <th style="width: 100px; text-align: right;">Harga/Hari</th>
                            <th style="width: 80px; text-align: center;">Stok</th>
                            <th style="width: 100px; text-align: center;">Kondisi</th>
                            <th style="width: 200px; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($equipments)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 3rem; color: var(--gray-400);">
                                <i class="bi bi-inbox" style="font-size: 3rem; display: block; margin-bottom: 1rem; opacity: 0.5;"></i>
                                Belum ada equipment. Klik "Tambah Equipment" untuk menambah.
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php $no = 1; foreach ($equipments as $eq): ?>
                            <tr>
                                <td style="font-weight: 600; color: var(--gray-500);"><?= $no++ ?></td>
                                <td>
                                    <?php if (!empty($eq['image_path'])): ?>
                                        <img src="../../<?= htmlspecialchars($eq['image_path']) ?>" 
                                             alt="<?= htmlspecialchars($eq['equipment_name']) ?>"
                                             style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px; border: 1px solid var(--gray-300);"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div style="display: none; width: 60px; height: 60px; background: var(--gray-200); border-radius: 8px; align-items: center; justify-content: center; color: var(--gray-400);">
                                            <i class="bi bi-image" style="font-size: 1.5rem;"></i>
                                        </div>
                                    <?php else: ?>
                                        <div style="width: 60px; height: 60px; background: var(--gray-200); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: var(--gray-400);">
                                            <i class="bi bi-image" style="font-size: 1.5rem;"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong style="color: var(--gray-900); font-size: 0.9375rem;">
                                        <?= htmlspecialchars($eq['equipment_name']) ?>
                                    </strong>
                                    <?php if ($eq['description']): ?>
                                    <br><small style="color: var(--gray-500); font-size: 0.8125rem;">
                                        <?= htmlspecialchars(substr($eq['description'], 0, 50)) ?><?= strlen($eq['description']) > 50 ? '...' : '' ?>
                                    </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-info">
                                        <?= htmlspecialchars($eq['category_name']) ?>
                                    </span>
                                </td>
                                <td style="text-align: right; font-weight: 600; color: var(--admin-primary);">
                                    Rp <?= number_format($eq['rental_price'], 0, ',', '.') ?>
                                </td>
                                <td style="text-align: center; font-weight: 600;">
                                    <?= $eq['total_stock'] ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php
                                    $badge_class = 'badge-success';
                                    if ($eq['condition_status'] === 'Maintenance') $badge_class = 'badge-warning';
                                    if ($eq['condition_status'] === 'Rusak') $badge_class = 'badge-danger';
                                    ?>
                                    <span class="badge <?= $badge_class ?>">
                                        <?= htmlspecialchars($eq['condition_status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="table-actions" style="justify-content: center;">
                                        <a href="equipment-edit.php?id=<?= $eq['equipment_id'] ?>" 
                                           class="btn btn-warning btn-sm" 
                                           title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="?delete_id=<?= $eq['equipment_id'] ?>" 
                                           class="btn btn-danger btn-sm" 
                                           title="Hapus"
                                           onclick="return confirm('Yakin ingin menghapus <?= htmlspecialchars($eq['equipment_name']) ?>?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
</div>

<?php require_once '../includes/admin-footer.php'; ?>