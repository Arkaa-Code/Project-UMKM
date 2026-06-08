<?php
session_start();
require_once '../config/database.php';

$page_title = 'Tambah Equipment';
$current_page = 'equipment';
$is_admin_folder = false;

$errors = [];
$form_data = [];

$db = getDB();

// Get all categories for dropdown
$categories = $db->query("SELECT * FROM categories ORDER BY category_name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $equipment_name = trim($_POST['equipment_name'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $total_stock = (int)($_POST['total_stock'] ?? 0);
    $rental_price = trim($_POST['rental_price'] ?? '');
    $condition_status = $_POST['condition_status'] ?? 'Baik';
    $image_path = null;
    
    // Validation
    if (empty($equipment_name)) {
        $errors[] = 'Nama equipment harus diisi';
    }
    if ($category_id <= 0) {
        $errors[] = 'Kategori harus dipilih';
    }
    if ($total_stock < 0) {
        $errors[] = 'Stok tidak boleh negatif';
    }
    if (empty($rental_price) || $rental_price < 0) {
        $errors[] = 'Harga rental harus diisi dan tidak boleh negatif';
    }
    
    // Handle image upload (optional)
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
        $max_size = 2 * 1024 * 1024; // 2MB
        
        if (!in_array($_FILES['image']['type'], $allowed_types)) {
            $errors[] = 'Format gambar harus JPG atau PNG';
        } elseif ($_FILES['image']['size'] > $max_size) {
            $errors[] = 'Ukuran gambar maksimal 2MB';
        } else {
            // Path absolut untuk upload
            $upload_dir = realpath(__DIR__ . '/../../assets/images/equipment/') . DIRECTORY_SEPARATOR;
            
            // Buat folder jika belum ada
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $new_filename = 'eq_' . time() . '_' . rand(1000, 9999) . '.' . $file_ext;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                // Path relatif untuk disimpan ke database (relatif dari root project)
                $image_path = 'assets/images/equipment/' . $new_filename;
            } else {
                $errors[] = 'Gagal upload gambar';
            }
        }
    }
    
    // If no errors, insert to database
    if (empty($errors)) {
        try {
            $stmt = $db->prepare("
                INSERT INTO equipment 
                (category_id, equipment_name, description, total_stock, rental_price, image_path, condition_status, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");
            $stmt->execute([
                $category_id,
                $equipment_name,
                $description,
                $total_stock,
                $rental_price,
                $image_path,
                $condition_status
            ]);
            
            $_SESSION['success_message'] = 'Equipment berhasil ditambahkan';
            header('Location: equipment-list.php');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Gagal menambahkan equipment: ' . $e->getMessage();
        }
    }
    
    $form_data = $_POST;
}

require_once '../includes/admin-header.php';
?>

<div class="admin-header">
    <div class="admin-header-content">
        <div class="header-title">
            <h2>Tambah Equipment</h2>
            <p>Tambah equipment baru ke inventory</p>
        </div>
        <div class="header-actions">
            <a href="equipment-list.php" class="btn btn-secondary">
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
                <i class="bi bi-plus-circle"></i> Form Tambah Equipment
            </h3>
        </div>
        <div class="card-body">
            <form method="POST" action="" enctype="multipart/form-data">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="equipment_name" class="form-label">
                            <i class="bi bi-box-seam"></i> Nama Equipment *
                        </label>
                        <input 
                            type="text" 
                            id="equipment_name" 
                            name="equipment_name" 
                            class="form-control" 
                            placeholder="Contoh: Speaker Line Array 12 inch"
                            value="<?= htmlspecialchars($form_data['equipment_name'] ?? '') ?>"
                            required
                            autofocus
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="category_id" class="form-label">
                            <i class="bi bi-grid-3x3-gap"></i> Kategori *
                        </label>
                        <select id="category_id" name="category_id" class="form-control" required>
                            <option value="">-- Pilih Kategori --</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['category_id'] ?>" 
                                <?= isset($form_data['category_id']) && $form_data['category_id'] == $cat['category_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['category_name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="total_stock" class="form-label">
                            <i class="bi bi-box"></i> Jumlah Stok *
                        </label>
                        <input 
                            type="number" 
                            id="total_stock" 
                            name="total_stock" 
                            class="form-control" 
                            placeholder="0"
                            value="<?= htmlspecialchars($form_data['total_stock'] ?? '') ?>"
                            min="0"
                            required
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="rental_price" class="form-label">
                            <i class="bi bi-currency-dollar"></i> Harga Rental (per hari) *
                        </label>
                        <input 
                            type="number" 
                            id="rental_price" 
                            name="rental_price" 
                            class="form-control" 
                            placeholder="100000"
                            value="<?= htmlspecialchars($form_data['rental_price'] ?? '') ?>"
                            min="0"
                            step="1000"
                            required
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="condition_status" class="form-label">
                            <i class="bi bi-speedometer"></i> Kondisi
                        </label>
                        <select id="condition_status" name="condition_status" class="form-control">
                            <option value="Baik" <?= isset($form_data['condition_status']) && $form_data['condition_status'] === 'Baik' ? 'selected' : '' ?>>Baik</option>
                            <option value="Maintenance" <?= isset($form_data['condition_status']) && $form_data['condition_status'] === 'Maintenance' ? 'selected' : '' ?>>Maintenance</option>
                            <option value="Rusak" <?= isset($form_data['condition_status']) && $form_data['condition_status'] === 'Rusak' ? 'selected' : '' ?>>Rusak</option>
                        </select>
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
                            placeholder="Deskripsi detail equipment (opsional)"
                        ><?= htmlspecialchars($form_data['description'] ?? '') ?></textarea>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group form-group-full">
                        <label for="image" class="form-label">
                            <i class="bi bi-image"></i> Gambar Equipment (Opsional)
                        </label>
                        <input 
                            type="file" 
                            id="image" 
                            name="image" 
                            class="form-control"
                            accept="image/jpeg,image/png,image/jpg"
                        >
                        <small style="display: block; margin-top: 0.5rem; color: var(--gray-500); font-size: 0.8125rem;">
                            Format: JPG, PNG. Maksimal 2MB
                        </small>
                    </div>
                </div>
                
                <div style="display: flex; gap: 1rem; justify-content: flex-end; padding-top: 1rem; border-top: 1px solid var(--gray-200); margin-top: 1rem;">
                    <a href="equipment-list.php" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Simpan Equipment
                    </button>
                </div>
                
            </form>
        </div>
    </div>
    
</div>

<?php require_once '../includes/admin-footer.php'; ?>
