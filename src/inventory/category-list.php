<?php
session_start();
require_once '../config/database.php';

$page_title = 'Kategori Equipment';
$current_page = 'category';
$is_admin_folder = false;

// Handle delete
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    try {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM categories WHERE category_id = ?");
        $stmt->execute([$delete_id]);
        $_SESSION['success_message'] = 'Kategori berhasil dihapus';
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Gagal menghapus kategori: ' . $e->getMessage();
    }
    header('Location: category-list.php');
    exit;
}

// Get search query
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Get all categories with optional search
$db = getDB();
$sql = "
    SELECT c.*, COUNT(e.equipment_id) as equipment_count
    FROM categories c
    LEFT JOIN equipment e ON c.category_id = e.category_id
";

$params = [];
if ($search !== '') {
    $sql .= " WHERE c.category_name LIKE ? OR c.description LIKE ?";
    $params = ["%$search%", "%$search%"];
}

$sql .= " GROUP BY c.category_id ORDER BY c.category_name ASC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$categories = $stmt->fetchAll();

require_once '../includes/admin-header.php';
?>

<div class="admin-header">
    <div class="admin-header-content">
        <div class="header-title">
            <h2>Kategori Equipment</h2>
            <p>Kelola kategori peralatan rental</p>
        </div>
        <div class="header-actions">
            <a href="category-create.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Tambah Kategori
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
    
    <!-- Search Bar -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-body" style="padding: 1.25rem 1.5rem;">
            <form method="GET" style="display: flex; gap: 1rem; align-items: center;">
                <div style="position: relative; flex: 1;">
                    <i class="bi bi-search" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--gray-400); font-size: 0.95rem;"></i>
                    <input 
                        type="text" 
                        name="search" 
                        class="form-control" 
                        placeholder="Cari nama kategori atau deskripsi..." 
                        value="<?= htmlspecialchars($search) ?>"
                        style="padding-left: 2.75rem;"
                        autofocus
                    >
                </div>
                <button type="submit" class="btn btn-primary" style="white-space: nowrap;">
                    <i class="bi bi-search"></i> Search
                </button>
            </form>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="bi bi-grid-3x3-gap"></i> Daftar Kategori
            </h3>
            <span style="font-size: 0.875rem; color: var(--gray-500); font-weight: 500;">
                Total: <?= count($categories) ?> kategori
            </span>
        </div>
        <div class="card-body">
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 60px;">No</th>
                            <th>Nama Kategori</th>
                            <th>Deskripsi</th>
                            <th style="width: 120px; text-align: center;">Jumlah Item</th>
                            <th style="width: 200px; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($categories)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 3rem; color: var(--gray-400);">
                                <i class="bi bi-inbox" style="font-size: 3rem; display: block; margin-bottom: 1rem; opacity: 0.5;"></i>
                                Belum ada kategori. Klik "Tambah Kategori" untuk menambah.
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php $no = 1; foreach ($categories as $cat): ?>
                            <tr>
                                <td style="font-weight: 600; color: var(--gray-500);"><?= $no++ ?></td>
                                <td>
                                    <strong style="color: var(--gray-900); font-size: 0.9375rem;">
                                        <?= htmlspecialchars($cat['category_name']) ?>
                                    </strong>
                                </td>
                                <td style="color: var(--gray-600);">
                                    <?= htmlspecialchars($cat['description'] ?? '-') ?>
                                </td>
                                <td style="text-align: center;">
                                    <span class="badge badge-info">
                                        <i class="bi bi-box-seam"></i>
                                        <?= $cat['equipment_count'] ?> item
                                    </span>
                                </td>
                                <td>
                                    <div class="table-actions" style="justify-content: center;">
                                        <a href="category-edit.php?id=<?= $cat['category_id'] ?>" 
                                           class="btn btn-warning btn-sm" 
                                           title="Edit">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        <a href="?delete_id=<?= $cat['category_id'] ?>" 
                                           class="btn btn-danger btn-sm" 
                                           title="Hapus"
                                           onclick="return confirm('Yakin ingin menghapus kategori <?= htmlspecialchars($cat['category_name']) ?>?')">
                                            <i class="bi bi-trash"></i> Hapus
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