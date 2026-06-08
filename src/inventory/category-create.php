<?php
session_start();
require_once '../config/database.php';

$page_title = 'Tambah Kategori';
$current_page = 'category';
$is_admin_folder = false;

$errors = [];
$form_data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_name = trim($_POST['category_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    // Validation
    if (empty($category_name)) {
        $errors[] = 'Nama kategori harus diisi';
    }
    
    // If no errors, insert to database
    if (empty($errors)) {
        try {
            $db = getDB();
            $stmt = $db->prepare("
                INSERT INTO categories (category_name, description)
                VALUES (?, ?)
            ");
            $stmt->execute([$category_name, $description]);
            
            $_SESSION['success_message'] = 'Kategori berhasil ditambahkan';
            header('Location: category-list.php');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Gagal menambahkan kategori: ' . $e->getMessage();
        }
    }
    
    // Keep form data for re-display
    $form_data = $_POST;
}

require_once '../includes/admin-header.php';
?>

<div class="admin-header">
    <div class="admin-header-content">
        <div class="header-title">
            <h2>Tambah Kategori</h2>
            <p>Tambah kategori equipment baru</p>
        </div>
        <div class="header-actions">
            <a href="category-list.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
            <a href="../../admin/logout.php" class="btn-logout">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        </div>
    </div>
</div>

<div class="admin-content">
    
    <?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <i class="bi bi-exclamation-circle"></i>
        <div>
            <?php foreach ($errors as $error): ?>
                <div><?= htmlspecialchars($error) ?></div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="bi bi-plus-circle"></i> Form Tambah Kategori
            </h3>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="category_name" class="form-label">
                            <i class="bi bi-tag"></i> Nama Kategori *
                        </label>
                        <input 
                            type="text" 
                            id="category_name" 
                            name="category_name" 
                            class="form-control" 
                            placeholder="Contoh: Sound System"
                            value="<?= htmlspecialchars($form_data['category_name'] ?? '') ?>"
                            required
                            autofocus
                        >
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group form-group-full">
                        <label for="description" class="form-label">
                            <i class="bi bi-text-paragraph"></i> Deskripsi
                        </label>
                        <textarea 
                            id="description" 
                            name="description" 
                            class="form-control" 
                            rows="4"
                            placeholder="Deskripsi kategori (opsional)"
                        ><?= htmlspecialchars($form_data['description'] ?? '') ?></textarea>
                    </div>
                </div>
                
                <div style="display: flex; gap: 1rem; justify-content: flex-end; padding-top: 1rem; border-top: 1px solid var(--gray-200);">
                    <a href="category-list.php" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Simpan Kategori
                    </button>
                </div>
                
            </form>
        </div>
    </div>
    
</div>

<?php require_once '../includes/admin-footer.php'; ?>
