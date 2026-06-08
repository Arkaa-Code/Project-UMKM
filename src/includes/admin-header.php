<?php
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../../admin/login.php');
    exit;
}

$page_title = $page_title ?? 'Dashboard';
$current_page = $current_page ?? 'dashboard';

// Auto detect CSS path based on current directory
$css_path = isset($base_path) ? $base_path : '../../assets/css/admin.css';

// Auto detect assets path based on folder depth
$assets_path = (isset($is_admin_folder) && $is_admin_folder) ? '../assets' : '../../assets';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> — Admin Panel</title>
    
    <!-- Google Fonts: Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="<?= $assets_path ?>/images/equipment/rzlogo-favicon.svg?v=3">
    <link rel="shortcut icon" type="image/png" href="<?= $assets_path ?>/images/equipment/rzlogo-favicon.svg?v=3">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= $assets_path ?>/images/equipment/rzlogo-favicon.svg?v=3">
    
    <!-- Admin Styles -->
    <link href="<?= $css_path ?>" rel="stylesheet">
</head>
<body>
    <div class="admin-wrapper">
        
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="sidebar-brand">
                <div class="sidebar-brand-icon">
                    <img src="<?= $assets_path ?>/images/equipment/rzlogo.png" alt="RZKY Equipment Services logo" class="brand-logo">
                </div>
                <div class="sidebar-brand-text">
                    <h1>RZKY Equipment Services</h1>
                    <p>Admin Panel</p>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <div class="nav-section-title">Main</div>
                <a href="<?= isset($is_admin_folder) && $is_admin_folder ? 'dashboard.php' : '../../admin/dashboard.php' ?>" class="nav-link <?= $current_page === 'dashboard' ? 'active' : '' ?>">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
                
                <div class="nav-section-title">Inventory</div>
                <a href="<?= isset($is_admin_folder) && $is_admin_folder ? '../src/inventory/equipment-list.php' : 'equipment-list.php' ?>" class="nav-link <?= $current_page === 'equipment' ? 'active' : '' ?>">
                    <i class="bi bi-box-seam"></i>
                    <span>Equipment</span>
                </a>
                <a href="<?= isset($is_admin_folder) && $is_admin_folder ? '../src/inventory/category-list.php' : 'category-list.php' ?>" class="nav-link <?= $current_page === 'category' ? 'active' : '' ?>">
                    <i class="bi bi-grid-3x3-gap"></i>
                    <span>Kategori</span>
                </a>
                
                <div class="nav-section-title">Reservasi</div>
                <a href="<?= isset($is_admin_folder) && $is_admin_folder ? 'reservations.php' : '../../admin/reservations.php' ?>" class="nav-link <?= $current_page === 'reservations' ? 'active' : '' ?>">
                    <i class="bi bi-calendar-check"></i>
                    <span>Reservasi</span>
                </a>
                
                <div class="nav-section-title">Laporan</div>
                <a href="<?= isset($is_admin_folder) && $is_admin_folder ? 'reports.php' : '../../admin/reports.php' ?>" class="nav-link <?= $current_page === 'reports' ? 'active' : '' ?>">
                    <i class="bi bi-file-earmark-bar-graph"></i>
                    <span>Laporan</span>
                </a>
                
                <div class="nav-section-title">Pengaturan</div>
                <a href="<?= isset($is_admin_folder) && $is_admin_folder ? 'settings.php' : '../../admin/settings.php' ?>" class="nav-link <?= $current_page === 'settings' ? 'active' : '' ?>">
                    <i class="bi bi-gear"></i>
                    <span>Pengaturan</span>
                </a>
            </nav>
            
            <div class="sidebar-footer">
                <div class="sidebar-user">
                    <div class="sidebar-user-avatar">
                        <?= strtoupper(substr($_SESSION['admin_name'] ?? 'A', 0, 1)) ?>
                    </div>
                    <div class="sidebar-user-info">
                        <h4><?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?></h4>
                        <p><?= htmlspecialchars($_SESSION['admin_username'] ?? 'admin') ?></p>
                    </div>
                </div>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="admin-main">
