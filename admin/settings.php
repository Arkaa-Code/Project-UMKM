<?php
session_start();
require_once '../src/config/database.php';

$page_title      = 'Pengaturan';
$current_page    = 'settings';
$base_path       = '../assets/css/admin.css';
$is_admin_folder = true;

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$db = getDB();

// ── Detect if full_name column exists ─────────────────────
// Run a safe check — wrap in try/catch so older schemas don't break
try {
    $col_check = $db->query("SHOW COLUMNS FROM admins LIKE 'full_name'");
    $has_full_name = ($col_check->rowCount() > 0);
} catch (PDOException $e) {
    $has_full_name = false;
}

// ── Fetch current admin ───────────────────────────────────
try {
    $stmt = $db->prepare("SELECT * FROM admins WHERE admin_id = ?");
    $stmt->execute([$_SESSION['admin_id']]);
    $admin = $stmt->fetch();
} catch (PDOException $e) {
    $admin = [];
}

$errors   = [];
$success  = [];
$active_tab = $_POST['tab'] ?? $_GET['tab'] ?? 'profile';

// ══════════════════════════════════════════════════════════
// POST: Update Profile
// ══════════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['tab'] ?? '') === 'profile') {
    $new_username  = trim($_POST['username']  ?? '');
    $new_full_name = trim($_POST['full_name'] ?? '');

    if (empty($new_username)) {
        $errors[] = 'Username tidak boleh kosong.';
    } elseif (strlen($new_username) < 3 || strlen($new_username) > 50) {
        $errors[] = 'Username harus antara 3–50 karakter.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $new_username)) {
        $errors[] = 'Username hanya boleh berisi huruf, angka, dan underscore.';
    }

    if (empty($errors)) {
        // Check username uniqueness (exclude current admin)
        $chk = $db->prepare("SELECT COUNT(*) FROM admins WHERE username = ? AND admin_id != ?");
        $chk->execute([$new_username, $_SESSION['admin_id']]);
        if ($chk->fetchColumn() > 0) {
            $errors[] = 'Username sudah digunakan oleh admin lain.';
        }
    }

    if (empty($errors)) {
        try {
            if ($has_full_name) {
                $stmt = $db->prepare("UPDATE admins SET username = ?, full_name = ? WHERE admin_id = ?");
                $stmt->execute([$new_username, $new_full_name, $_SESSION['admin_id']]);
                $_SESSION['admin_name']     = $new_full_name;
            } else {
                $stmt = $db->prepare("UPDATE admins SET username = ? WHERE admin_id = ?");
                $stmt->execute([$new_username, $_SESSION['admin_id']]);
            }
            $_SESSION['admin_username'] = $new_username;
            $success[] = 'Profil berhasil diperbarui.';

            // Refresh admin data
            $stmt = $db->prepare("SELECT * FROM admins WHERE admin_id = ?");
            $stmt->execute([$_SESSION['admin_id']]);
            $admin = $stmt->fetch();
        } catch (PDOException $e) {
            $errors[] = 'Gagal memperbarui profil.';
        }
    }
}

// ══════════════════════════════════════════════════════════
// POST: Change Password
// ══════════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['tab'] ?? '') === 'password') {
    $active_tab    = 'password';
    $old_password  = $_POST['old_password']      ?? '';
    $new_password  = $_POST['new_password']      ?? '';
    $confirm_pass  = $_POST['confirm_password']  ?? '';

    if (empty($old_password) || empty($new_password) || empty($confirm_pass)) {
        $errors[] = 'Semua field password harus diisi.';
    } elseif (!password_verify($old_password, $admin['password'])) {
        $errors[] = 'Password lama tidak sesuai.';
    } elseif (strlen($new_password) < 8) {
        $errors[] = 'Password baru minimal 8 karakter.';
    } elseif ($new_password !== $confirm_pass) {
        $errors[] = 'Konfirmasi password tidak cocok.';
    } elseif (password_verify($new_password, $admin['password'])) {
        $errors[] = 'Password baru tidak boleh sama dengan password lama.';
    }

    if (empty($errors)) {
        try {
            $hash = password_hash($new_password, PASSWORD_BCRYPT);
            $stmt = $db->prepare("UPDATE admins SET password = ? WHERE admin_id = ?");
            $stmt->execute([$hash, $_SESSION['admin_id']]);
            $success[] = 'Password berhasil diubah. Silakan login ulang menggunakan password baru.';

            // Refresh
            $stmt = $db->prepare("SELECT * FROM admins WHERE admin_id = ?");
            $stmt->execute([$_SESSION['admin_id']]);
            $admin = $stmt->fetch();
        } catch (PDOException $e) {
            $errors[] = 'Gagal mengubah password.';
        }
    }
}

// ══════════════════════════════════════════════════════════
// POST: Add full_name column if missing (DB Migration)
// ══════════════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['tab'] ?? '') === 'migrate') {
    $active_tab = 'system';
    if (!$has_full_name) {
        try {
            $db->exec("ALTER TABLE `admins`
                ADD COLUMN `full_name` VARCHAR(100) NOT NULL DEFAULT 'Administrator' AFTER `password`,
                ADD COLUMN `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `full_name`,
                ADD COLUMN `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`");
            $db->exec("UPDATE `admins` SET `full_name` = 'Super Admin' WHERE `username` = 'admin'");

            // Also add to categories if needed
            $col_chk2 = $db->query("SHOW COLUMNS FROM categories LIKE 'created_at'");
            if ($col_chk2->rowCount() === 0) {
                $db->exec("ALTER TABLE `categories`
                    ADD COLUMN `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `description`,
                    ADD COLUMN `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`");
            }

            $has_full_name = true;
            $success[] = 'Migrasi database berhasil. Kolom full_name dan timestamps telah ditambahkan.';

            // Refresh admin
            $stmt = $db->prepare("SELECT * FROM admins WHERE admin_id = ?");
            $stmt->execute([$_SESSION['admin_id']]);
            $admin = $stmt->fetch();
        } catch (PDOException $e) {
            $errors[] = 'Gagal migrasi: ' . $e->getMessage();
        }
    } else {
        $success[] = 'Kolom full_name sudah ada. Tidak perlu migrasi.';
    }
}

// ══════════════════════════════════════════════════════════
// Fetch / POST: Invoice Template Settings
// ══════════════════════════════════════════════════════════

// Ensure invoice_settings table exists (safe guard)
try {
    $db->query("SELECT 1 FROM invoice_settings LIMIT 1");
} catch (PDOException $e) {
    // Table not yet migrated — set empty defaults
}

// Fetch invoice settings (row id=1 is the singleton)
try {
    $inv_cfg = $db->query("SELECT * FROM invoice_settings WHERE id = 1")->fetch();
} catch (PDOException $e) {
    $inv_cfg = false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['tab'] ?? '') === 'invoice') {
    $active_tab = 'invoice';

    $inv_company  = trim($_POST['company_name']         ?? '');
    $inv_address  = trim($_POST['company_address']      ?? '');
    $inv_phone    = trim($_POST['company_phone']        ?? '');
    $inv_email    = trim($_POST['company_email']        ?? '');
    $inv_payment  = trim($_POST['payment_instructions'] ?? '');
    $inv_notes    = trim($_POST['invoice_notes']        ?? '');
    $inv_logo     = $inv_cfg['logo_path'] ?? null;

    if (empty($inv_company)) {
        $errors[] = 'Nama perusahaan tidak boleh kosong.';
    }

    // Handle logo upload
    if (!empty($_FILES['logo_file']['name'])) {
        $allowedTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/gif'];
        $ftype = mime_content_type($_FILES['logo_file']['tmp_name']);
        if (!in_array($ftype, $allowedTypes)) {
            $errors[] = 'Format logo tidak didukung. Gunakan PNG, JPG, atau GIF.';
        } elseif ($_FILES['logo_file']['size'] > 2 * 1024 * 1024) {
            $errors[] = 'Ukuran logo maksimal 2MB.';
        } else {
            $ext      = pathinfo($_FILES['logo_file']['name'], PATHINFO_EXTENSION);
            $logoName = 'company_logo_' . time() . '.' . $ext;
            $logoDir  = '../assets/images/';
            if (!is_dir($logoDir)) mkdir($logoDir, 0775, true);
            if (move_uploaded_file($_FILES['logo_file']['tmp_name'], $logoDir . $logoName)) {
                $inv_logo = 'assets/images/' . $logoName;
            } else {
                $errors[] = 'Gagal menyimpan file logo.';
            }
        }
    }

    if (empty($errors)) {
        try {
            if ($inv_cfg) {
                $stmt = $db->prepare("UPDATE invoice_settings
                    SET company_name=?, company_address=?, company_phone=?, company_email=?,
                        payment_instructions=?, invoice_notes=?, logo_path=?
                    WHERE id=1");
                $stmt->execute([$inv_company, $inv_address, $inv_phone, $inv_email, $inv_payment, $inv_notes, $inv_logo]);
            } else {
                $stmt = $db->prepare("INSERT INTO invoice_settings
                    (id, company_name, company_address, company_phone, company_email,
                     payment_instructions, invoice_notes, logo_path)
                    VALUES (1,?,?,?,?,?,?,?)");
                $stmt->execute([$inv_company, $inv_address, $inv_phone, $inv_email, $inv_payment, $inv_notes, $inv_logo]);
            }
            $success[] = 'Pengaturan invoice berhasil disimpan.';
            // Refresh
            $inv_cfg = $db->query("SELECT * FROM invoice_settings WHERE id = 1")->fetch();
        } catch (PDOException $e) {
            $errors[] = 'Gagal menyimpan pengaturan invoice: ' . $e->getMessage();
        }
    }
}

// ══════════════════════════════════════════════════════════
// Fetch / POST: Notification Settings
// ══════════════════════════════════════════════════════════

// Ensure notification_settings table exists
try {
    $db->query("SELECT 1 FROM notification_settings LIMIT 1");
    $notif_table_exists = true;
} catch (PDOException $e) {
    $notif_table_exists = false;
}

// Fetch notification config
$notif_cfg = false;
if ($notif_table_exists) {
    try {
        $notif_cfg = $db->query("SELECT * FROM notification_settings WHERE id = 1")->fetch();
    } catch (PDOException $e) { $notif_cfg = false; }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['tab'] ?? '') === 'notification') {
    $active_tab = 'notification';

    if (!$notif_table_exists) {
        $errors[] = 'Tabel notification_settings belum ada. Jalankan migration_notification_settings.sql terlebih dahulu.';
    } else {
        $n_fonnte    = trim($_POST['fonnte_token']       ?? '');
        $n_host      = trim($_POST['smtp_host']          ?? 'smtp.gmail.com');
        $n_port      = (int)($_POST['smtp_port']         ?? 587);
        $n_user      = trim($_POST['smtp_user']          ?? '');
        $n_pass      = trim($_POST['smtp_pass']          ?? '');
        $n_fromname  = trim($_POST['smtp_from_name']     ?? 'RZ Equipment Rental');
        $n_company   = trim($_POST['company_name_notif'] ?? 'RZ Equipment Rental');
        $n_wa_en     = isset($_POST['wa_enabled'])    ? 1 : 0;
        $n_email_en  = isset($_POST['email_enabled']) ? 1 : 0;

        // Jika smtp_pass dikosongkan, pertahankan nilai lama
        if (empty($n_pass) && !empty($notif_cfg['smtp_pass'])) {
            $n_pass = $notif_cfg['smtp_pass'];
        }

        try {
            $db->prepare(
                "INSERT INTO notification_settings
                    (id, fonnte_token, smtp_host, smtp_port, smtp_user, smtp_pass, smtp_from_name, company_name_notif, wa_enabled, email_enabled)
                 VALUES (1, :ft, :sh, :sp, :su, :spw, :sfn, :cn, :we, :ee)
                 ON DUPLICATE KEY UPDATE
                    fonnte_token=VALUES(fonnte_token), smtp_host=VALUES(smtp_host),
                    smtp_port=VALUES(smtp_port), smtp_user=VALUES(smtp_user),
                    smtp_pass=VALUES(smtp_pass), smtp_from_name=VALUES(smtp_from_name),
                    company_name_notif=VALUES(company_name_notif),
                    wa_enabled=VALUES(wa_enabled), email_enabled=VALUES(email_enabled)"
            )->execute([
                ':ft'  => $n_fonnte ?: null,
                ':sh'  => $n_host,
                ':sp'  => $n_port,
                ':su'  => $n_user ?: null,
                ':spw' => $n_pass ?: null,
                ':sfn' => $n_fromname,
                ':cn'  => $n_company,
                ':we'  => $n_wa_en,
                ':ee'  => $n_email_en,
            ]);
            $success[] = 'Pengaturan notifikasi berhasil disimpan.';
            $notif_cfg = $db->query("SELECT * FROM notification_settings WHERE id = 1")->fetch();
        } catch (PDOException $e) {
            $errors[] = 'Gagal menyimpan: ' . $e->getMessage();
        }
    }
}

require_once '../src/includes/admin-header.php';
?>

<div class="admin-header">
    <div class="admin-header-content">
        <div class="header-title">
            <h2><i class="bi bi-gear me-2" style="color:var(--admin-primary)"></i>Pengaturan Admin</h2>
            <p>Kelola akun dan konfigurasi sistem admin</p>
        </div>
        <div class="header-actions">
            <a href="logout.php" class="btn-logout">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        </div>
    </div>
</div>

<div class="admin-content">

<!-- ── Alerts ──────────────────────────────────────────── -->
<?php if (!empty($success)): ?>
<div class="alert alert-success">
    <i class="bi bi-check-circle"></i>
    <span><?= htmlspecialchars($success[0]) ?></span>
</div>
<?php endif; ?>
<?php if (!empty($errors)): ?>
<div class="alert alert-error">
    <i class="bi bi-exclamation-circle"></i>
    <div>
        <?php foreach ($errors as $e): ?>
        <div><?= htmlspecialchars($e) ?></div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:240px 1fr;gap:1.5rem;align-items:start">

    <!-- ── Sidebar Tabs ─────────────────────────────────── -->
    <div class="card">
        <div class="card-body" style="padding:.75rem">
            <?php
            $tabs = [
                ['profile',      'bi-person-circle',     'Profil Admin'],
                ['password',     'bi-shield-lock',        'Ubah Password'],
                ['system',       'bi-database',           'Sistem'],
                ['invoice',      'bi-file-earmark-text',  'Template Invoice'],
                ['notification', 'bi-bell-fill',          'Notifikasi'],
            ];
            foreach ($tabs as [$slug, $icon, $label]):
                $is_active = $active_tab === $slug;
            ?>
            <a href="settings.php?tab=<?= $slug ?>"
               style="display:flex;align-items:center;gap:.75rem;padding:.75rem 1rem;border-radius:8px;margin-bottom:.25rem;font-size:.875rem;font-weight:600;text-decoration:none;
                      background:<?= $is_active ? 'var(--admin-primary)' : 'transparent' ?>;
                      color:<?= $is_active ? 'white' : 'var(--gray-700)' ?>">
                <i class="bi <?= $icon ?>" style="font-size:1.1rem;width:20px;text-align:center"></i>
                <?= $label ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ── Main Panel ───────────────────────────────────── -->
    <div>

        <!-- ════ TAB: PROFIL ════ -->
        <?php if ($active_tab === 'profile'): ?>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="bi bi-person-circle"></i> Profil Admin
                </h3>
            </div>
            <div class="card-body">

                <!-- Avatar preview -->
                <div style="display:flex;align-items:center;gap:1.5rem;padding:1.5rem;background:var(--gray-50);border-radius:12px;margin-bottom:1.5rem">
                    <div style="width:72px;height:72px;background:linear-gradient(135deg,#2563EB,#3B82F6);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.75rem;font-weight:800;color:white;flex-shrink:0">
                        <?= strtoupper(substr($admin['full_name'] ?? $admin['username'] ?? 'A', 0, 1)) ?>
                    </div>
                    <div>
                        <h4 style="font-size:1.125rem;font-weight:700;margin:0 0 .25rem">
                            <?= htmlspecialchars($admin['full_name'] ?? $admin['username'] ?? 'Admin') ?>
                        </h4>
                        <p style="color:var(--gray-500);margin:0;font-size:.875rem">
                            @<?= htmlspecialchars($admin['username'] ?? '') ?>
                        </p>
                        <?php if ($has_full_name && !empty($admin['created_at'])): ?>
                        <p style="color:var(--gray-400);margin:.25rem 0 0;font-size:.8125rem">
                            <i class="bi bi-calendar3"></i>
                            Bergabung <?= date('d M Y', strtotime($admin['created_at'])) ?>
                        </p>
                        <?php endif; ?>
                    </div>
                </div>

                <form method="POST" action="settings.php">
                    <input type="hidden" name="tab" value="profile">

                    <div class="form-row">
                        <div class="form-group">
                            <label for="username" class="form-label">
                                <i class="bi bi-at"></i> Username *
                            </label>
                            <input type="text" id="username" name="username" class="form-control"
                                   value="<?= htmlspecialchars($admin['username'] ?? '') ?>"
                                   required minlength="3" maxlength="50"
                                   placeholder="Username admin">
                            <small style="font-size:.8125rem;color:var(--gray-500);margin-top:.35rem;display:block">
                                Hanya huruf, angka, dan underscore. Min. 3 karakter.
                            </small>
                        </div>

                        <?php if ($has_full_name): ?>
                        <div class="form-group">
                            <label for="full_name" class="form-label">
                                <i class="bi bi-person"></i> Nama Lengkap
                            </label>
                            <input type="text" id="full_name" name="full_name" class="form-control"
                                   value="<?= htmlspecialchars($admin['full_name'] ?? '') ?>"
                                   placeholder="Nama lengkap admin">
                        </div>
                        <?php else: ?>
                        <div class="form-group">
                            <label class="form-label">
                                <i class="bi bi-person"></i> Nama Lengkap
                            </label>
                            <div style="padding:.75rem 1rem;background:var(--gray-100);border-radius:8px;border:1.5px solid var(--gray-200);font-size:.875rem;color:var(--gray-500)">
                                Belum tersedia. Jalankan migrasi di tab Sistem terlebih dahulu.
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div style="display:flex;gap:1rem;justify-content:flex-end;padding-top:1rem;border-top:1px solid var(--gray-200)">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <!-- ════ TAB: PASSWORD ════ -->
        <?php if ($active_tab === 'password'): ?>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="bi bi-shield-lock"></i> Ubah Password
                </h3>
            </div>
            <div class="card-body">

                <div style="display:flex;align-items:flex-start;gap:1rem;padding:1rem;background:#DBEAFE;border:1px solid #93C5FD;border-radius:8px;margin-bottom:1.5rem">
                    <i class="bi bi-info-circle-fill" style="color:#1D4ED8;font-size:1.25rem;flex-shrink:0;margin-top:.1rem"></i>
                    <div>
                        <strong style="font-size:.875rem;color:#1E40AF">Tips keamanan password:</strong>
                        <ul style="font-size:.8125rem;color:#1E40AF;margin:.35rem 0 0;padding-left:1.25rem;line-height:1.8">
                            <li>Minimal 8 karakter</li>
                            <li>Kombinasi huruf besar, kecil, angka, dan simbol</li>
                            <li>Jangan gunakan informasi pribadi</li>
                        </ul>
                    </div>
                </div>

                <form method="POST" action="settings.php">
                    <input type="hidden" name="tab" value="password">

                    <div class="form-row">
                        <div class="form-group form-group-full">
                            <label for="old_password" class="form-label">
                                <i class="bi bi-lock"></i> Password Lama *
                            </label>
                            <input type="password" id="old_password" name="old_password" class="form-control"
                                   placeholder="Masukkan password saat ini" required
                                   autocomplete="current-password">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="new_password" class="form-label">
                                <i class="bi bi-lock-fill"></i> Password Baru *
                            </label>
                            <input type="password" id="new_password" name="new_password" class="form-control"
                                   placeholder="Password baru (min. 8 karakter)" required
                                   minlength="8" autocomplete="new-password"
                                   oninput="checkPasswordStrength(this.value)">
                            <!-- Strength indicator -->
                            <div style="margin-top:.5rem">
                                <div id="strengthBar" style="height:6px;border-radius:999px;background:var(--gray-200);overflow:hidden">
                                    <div id="strengthFill" style="height:100%;width:0;transition:width .3s,background .3s;border-radius:999px"></div>
                                </div>
                                <small id="strengthText" style="font-size:.75rem;color:var(--gray-500);margin-top:.25rem;display:block"></small>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password" class="form-label">
                                <i class="bi bi-lock-fill"></i> Konfirmasi Password Baru *
                            </label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control"
                                   placeholder="Ulangi password baru" required
                                   minlength="8" autocomplete="new-password"
                                   oninput="checkMatch()">
                            <small id="matchText" style="font-size:.75rem;display:block;margin-top:.25rem"></small>
                        </div>
                    </div>

                    <div style="display:flex;gap:1rem;justify-content:flex-end;padding-top:1rem;border-top:1px solid var(--gray-200)">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-shield-check"></i> Ubah Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <!-- ════ TAB: SYSTEM ════ -->
        <?php if ($active_tab === 'system'): ?>

        <!-- DB Info -->
        <div class="card" style="margin-bottom:1.5rem">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="bi bi-database"></i> Informasi Database
                </h3>
            </div>
            <div class="card-body">
                <?php
                try {
                    $tables    = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
                    $db_version = $db->query("SELECT VERSION()")->fetchColumn();
                    $table_info = [];
                    foreach ($tables as $tbl) {
                        $row_count = $db->query("SELECT COUNT(*) FROM `$tbl`")->fetchColumn();
                        $table_info[] = ['table' => $tbl, 'rows' => $row_count];
                    }
                } catch (PDOException $e) {
                    $table_info = [];
                    $db_version = 'N/A';
                }
                ?>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.5rem">
                    <div style="background:var(--gray-50);border-radius:8px;padding:1rem">
                        <small style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:var(--gray-400)">
                            <i class="bi bi-server"></i> Server DB
                        </small>
                        <div style="font-size:.9375rem;font-weight:700;color:var(--gray-900);margin-top:.3rem">
                            <?= htmlspecialchars(DB_HOST) ?>
                        </div>
                    </div>
                    <div style="background:var(--gray-50);border-radius:8px;padding:1rem">
                        <small style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:var(--gray-400)">
                            <i class="bi bi-hdd"></i> Nama Database
                        </small>
                        <div style="font-size:.9375rem;font-weight:700;color:var(--gray-900);margin-top:.3rem">
                            <?= htmlspecialchars(DB_NAME) ?>
                        </div>
                    </div>
                    <div style="background:var(--gray-50);border-radius:8px;padding:1rem">
                        <small style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:var(--gray-400)">
                            <i class="bi bi-gear"></i> Versi MariaDB/MySQL
                        </small>
                        <div style="font-size:.9375rem;font-weight:700;color:var(--gray-900);margin-top:.3rem">
                            <?= htmlspecialchars($db_version) ?>
                        </div>
                    </div>
                    <div style="background:var(--gray-50);border-radius:8px;padding:1rem">
                        <small style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:var(--gray-400)">
                            <i class="bi bi-php"></i> Versi PHP
                        </small>
                        <div style="font-size:.9375rem;font-weight:700;color:var(--gray-900);margin-top:.3rem">
                            <?= PHP_VERSION ?>
                        </div>
                    </div>
                </div>

                <h6 style="font-size:.875rem;font-weight:700;color:var(--gray-700);margin-bottom:.75rem">
                    <i class="bi bi-table"></i> Tabel Database
                </h6>
                <div class="table-wrapper">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Nama Tabel</th>
                                <th style="text-align:center">Jumlah Baris</th>
                                <th style="text-align:center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($table_info as $tbl): ?>
                        <tr>
                            <td>
                                <code style="background:var(--gray-100);padding:.2rem .5rem;border-radius:4px;font-size:.875rem">
                                    <?= htmlspecialchars($tbl['table']) ?>
                                </code>
                            </td>
                            <td style="text-align:center;font-weight:600">
                                <?= number_format($tbl['rows']) ?>
                            </td>
                            <td style="text-align:center">
                                <span class="badge badge-success">
                                    <i class="bi bi-check-circle"></i> OK
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- DB Migration -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="bi bi-arrow-up-circle"></i> Migrasi Database
                </h3>
            </div>
            <div class="card-body">

                <?php if (!$has_full_name): ?>
                <div style="display:flex;align-items:flex-start;gap:1rem;padding:1rem;background:#FEF3C7;border:1px solid #FCD34D;border-radius:8px;margin-bottom:1.5rem">
                    <i class="bi bi-exclamation-triangle-fill" style="color:#D97706;font-size:1.25rem;flex-shrink:0;margin-top:.1rem"></i>
                    <div>
                        <strong style="font-size:.875rem;color:#92400E">Kolom full_name belum ada</strong>
                        <p style="font-size:.8125rem;color:#78350F;margin:.35rem 0 0">
                            Klik tombol di bawah untuk menambahkan kolom <code>full_name</code>,
                            <code>created_at</code>, dan <code>updated_at</code> ke tabel <code>admins</code> dan <code>categories</code>.
                            Ini diperlukan agar fitur profil admin berfungsi penuh.
                        </p>
                    </div>
                </div>
                <form method="POST" action="settings.php">
                    <input type="hidden" name="tab" value="migrate">
                    <button type="submit" class="btn btn-warning"
                            onclick="return confirm('Jalankan migrasi database? Operasi ini tidak dapat dibalik.')">
                        <i class="bi bi-arrow-up-circle"></i> Jalankan Migrasi
                    </button>
                </form>
                <?php else: ?>
                <div style="display:flex;align-items:flex-start;gap:1rem;padding:1rem;background:#DCFCE7;border:1px solid #86EFAC;border-radius:8px">
                    <i class="bi bi-check-circle-fill" style="color:#15803D;font-size:1.25rem;flex-shrink:0;margin-top:.1rem"></i>
                    <div>
                        <strong style="font-size:.875rem;color:#14532D">Database sudah up-to-date</strong>
                        <p style="font-size:.8125rem;color:#166534;margin:.25rem 0 0">
                            Semua kolom yang diperlukan sudah tersedia.
                        </p>
                    </div>
                </div>
                <?php endif; ?>

            </div>
        </div>

        <?php endif; // system tab ?>

        <!-- ════ TAB: INVOICE TEMPLATE ════ -->
        <?php if ($active_tab === 'invoice'): ?>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="bi bi-file-earmark-text"></i> Template Invoice
                </h3>
                <small style="color:var(--gray-500)">Konfigurasi tampilan PDF invoice yang dikirim ke pelanggan</small>
            </div>
            <div class="card-body">

                <?php if ($inv_cfg === false): ?>
                <div class="alert alert-error">
                    <i class="bi bi-exclamation-triangle"></i>
                    <span>Tabel <code>invoice_settings</code> belum ada. Jalankan file
                    <strong>database/migration_invoice_settings.sql</strong> terlebih dahulu.</span>
                </div>
                <?php else: ?>

                <!-- Preview hint -->
                <div style="background:linear-gradient(135deg,#EFF6FF,#DBEAFE);border:1px solid #BFDBFE;border-radius:10px;padding:1rem 1.25rem;margin-bottom:1.5rem;display:flex;align-items:center;gap:.875rem">
                    <i class="bi bi-info-circle-fill" style="color:#2563EB;font-size:1.25rem;flex-shrink:0"></i>
                    <p style="margin:0;font-size:.875rem;color:#1E3A8A">
                        Data di bawah akan muncul pada setiap invoice PDF yang digenerate saat reservasi disetujui.
                    </p>
                </div>

                <form method="POST" action="settings.php" enctype="multipart/form-data">
                    <input type="hidden" name="tab" value="invoice">

                    <!-- Logo section -->
                    <div style="display:flex;align-items:flex-start;gap:1.5rem;padding:1.25rem;background:var(--gray-50);border-radius:10px;margin-bottom:1.5rem">
                        <div style="width:72px;height:72px;background:white;border:2px dashed var(--gray-300);border-radius:10px;display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0" id="logoPreviewBox">
                            <?php if (!empty($inv_cfg['logo_path']) && file_exists('../' . $inv_cfg['logo_path'])): ?>
                            <img src="../<?= htmlspecialchars($inv_cfg['logo_path']) ?>" style="max-width:68px;max-height:68px;object-fit:contain" id="logoPreviewImg">
                            <?php else: ?>
                            <i class="bi bi-building" style="font-size:2rem;color:var(--gray-400)" id="logoPlaceholder"></i>
                            <img id="logoPreviewImg" style="max-width:68px;max-height:68px;object-fit:contain;display:none">
                            <?php endif; ?>
                        </div>
                        <div style="flex:1">
                            <label class="form-label" style="font-size:.875rem;font-weight:700">Logo Perusahaan</label>
                            <input type="file" name="logo_file" id="logoFileInput" accept="image/png,image/jpeg,image/gif"
                                   class="form-control" style="margin-top:.35rem">
                            <small style="color:var(--gray-500);font-size:.8rem;display:block;margin-top:.35rem">
                                Format: PNG / JPG / GIF. Maks. 2MB. Ukuran optimal: 200×200px.
                                <?php if (!empty($inv_cfg['logo_path'])): ?>
                                <span style="color:var(--success)"><i class="bi bi-check-circle"></i> Logo terpasang</span>
                                <?php endif; ?>
                            </small>
                        </div>
                    </div>

                    <!-- Company info grid -->
                    <div class="form-row" style="grid-template-columns:1fr 1fr;gap:1rem">
                        <div class="form-group">
                            <label class="form-label"><i class="bi bi-building"></i> Nama Perusahaan *</label>
                            <input type="text" name="company_name" class="form-control" required maxlength="150"
                                   placeholder="Contoh: RZ Equipment Rental"
                                   value="<?= htmlspecialchars($inv_cfg['company_name'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label"><i class="bi bi-telephone"></i> Nomor Telepon</label>
                            <input type="text" name="company_phone" class="form-control" maxlength="50"
                                   placeholder="(0271) 000-0000"
                                   value="<?= htmlspecialchars($inv_cfg['company_phone'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label"><i class="bi bi-envelope"></i> Email Perusahaan</label>
                        <input type="email" name="company_email" class="form-control" maxlength="100"
                               placeholder="info@rzrental.com"
                               value="<?= htmlspecialchars($inv_cfg['company_email'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label"><i class="bi bi-geo-alt"></i> Alamat Perusahaan</label>
                        <textarea name="company_address" class="form-control" rows="2"
                                  placeholder="Jl. Contoh No. 1, Surakarta, Jawa Tengah 57100"><?= htmlspecialchars($inv_cfg['company_address'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label"><i class="bi bi-credit-card"></i> Instruksi Pembayaran</label>
                        <textarea name="payment_instructions" class="form-control" rows="3"
                                  placeholder="Contoh: Transfer ke BCA 1234567890 a.n. RZ Equipment Rental..."><?= htmlspecialchars($inv_cfg['payment_instructions'] ?? '') ?></textarea>
                        <small style="color:var(--gray-400);font-size:.8rem">Muncul di bawah tabel item pada invoice.</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label"><i class="bi bi-chat-quote"></i> Catatan Invoice</label>
                        <textarea name="invoice_notes" class="form-control" rows="2"
                                  placeholder="Terima kasih telah menggunakan layanan kami..."><?= htmlspecialchars($inv_cfg['invoice_notes'] ?? '') ?></textarea>
                        <small style="color:var(--gray-400);font-size:.8rem">Muncul di bagian footer invoice (italic).</small>
                    </div>

                    <div style="display:flex;justify-content:flex-end;gap:.75rem;padding-top:1rem;border-top:1px solid var(--gray-200)">
                        <a href="settings.php?tab=invoice" class="btn btn-secondary">
                            <i class="bi bi-arrow-counterclockwise"></i> Reset
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-floppy"></i> Simpan Pengaturan
                        </button>
                    </div>

                </form>
                <?php endif; ?>

            </div>
        </div>
        <?php endif; // invoice tab ?>

        <?php if ($active_tab === 'notification'): ?>
        <div class="card">
            <div class="card-body">
                <h5 style="margin:0 0 .25rem;color:var(--gray-900)">
                    <i class="bi bi-bell-fill"></i> Pengaturan Notifikasi Otomatis
                </h5>
                <small style="color:var(--gray-500)">Konfigurasi pengiriman WhatsApp (Fonnte) dan Email (SMTP) saat reservasi disetujui</small>

                <?php if (!$notif_table_exists): ?>
                <div style="margin-top:1rem;padding:.875rem 1rem;background:#FEF3C7;border:1px solid #FCD34D;border-radius:8px;font-size:.875rem;color:#92400E">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <span>Tabel <code>notification_settings</code> belum ada. Jalankan file
                    <strong>database/migration_notification_settings.sql</strong> di phpMyAdmin terlebih dahulu.</span>
                </div>
                <?php else: ?>

                <?php if (isset($_GET['saved'])): ?>
                <div style="margin-bottom:1rem;padding:.75rem 1rem;background:#ECFDF5;border:1px solid #6EE7B7;border-radius:8px;color:#065F46;font-size:.875rem;font-weight:600">
                    <i class="bi bi-check-circle-fill me-2"></i>Pengaturan notifikasi berhasil disimpan.
                </div>
                <?php elseif (isset($_GET['error'])): ?>
                <div style="margin-bottom:1rem;padding:.75rem 1rem;background:#FEF2F2;border:1px solid #FCA5A5;border-radius:8px;color:#991B1B;font-size:.875rem">
                    <i class="bi bi-exclamation-circle-fill me-2"></i>Gagal menyimpan: <?= htmlspecialchars($_GET['error']) ?>
                </div>
                <?php endif; ?>
                <form method="POST" action="save-notification-settings.php" style="margin-top:1.5rem">
                    <input type="hidden" name="tab" value="notification">

                    <!-- ── WhatsApp / Fonnte ── -->
                    <div style="background:#F0FDF4;border:1px solid #BBF7D0;border-radius:10px;padding:1.25rem;margin-bottom:1.25rem">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem">
                            <h6 style="margin:0;color:#166534;font-size:.9rem"><i class="bi bi-whatsapp me-2"></i>WhatsApp via Fonnte</h6>
                            <label style="display:flex;align-items:center;gap:.5rem;font-size:.85rem;cursor:pointer">
                                <input type="checkbox" name="wa_enabled" value="1"
                                    <?= !empty($notif_cfg['wa_enabled']) ? 'checked' : '' ?>>
                                Aktifkan
                            </label>
                        </div>
                        <div style="margin-bottom:.75rem">
                            <label class="form-label" style="font-size:.85rem"><i class="bi bi-key"></i> Fonnte Token</label>
                            <input type="text" name="fonnte_token" class="form-control"
                                   value="<?= htmlspecialchars($notif_cfg['fonnte_token'] ?? '') ?>"
                                   placeholder="Masukkan token dari dashboard.fonnte.com">
                            <small style="color:var(--gray-400);font-size:.8rem">
                                Daftar gratis di <a href="https://fonnte.com" target="_blank">fonnte.com</a> → Devices → Token API.
                                Pastikan nomor WA sudah terhubung di Fonnte.
                            </small>
                        </div>
                    </div>

                    <!-- ── Email / SMTP ── -->
                    <div style="background:#EFF6FF;border:1px solid #BFDBFE;border-radius:10px;padding:1.25rem;margin-bottom:1.25rem">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem">
                            <h6 style="margin:0;color:#1E3A8A;font-size:.9rem"><i class="bi bi-envelope-fill me-2"></i>Email via SMTP (Gmail)</h6>
                            <label style="display:flex;align-items:center;gap:.5rem;font-size:.85rem;cursor:pointer">
                                <input type="checkbox" name="email_enabled" value="1"
                                    <?= !empty($notif_cfg['email_enabled']) ? 'checked' : '' ?>>
                                Aktifkan
                            </label>
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 120px;gap:.75rem;margin-bottom:.75rem">
                            <div>
                                <label class="form-label" style="font-size:.85rem">SMTP Host</label>
                                <input type="text" name="smtp_host" class="form-control"
                                       value="<?= htmlspecialchars($notif_cfg['smtp_host'] ?? 'smtp.gmail.com') ?>">
                            </div>
                            <div>
                                <label class="form-label" style="font-size:.85rem">Port</label>
                                <input type="number" name="smtp_port" class="form-control"
                                       value="<?= (int)($notif_cfg['smtp_port'] ?? 587) ?>">
                            </div>
                        </div>
                        <div style="margin-bottom:.75rem">
                            <label class="form-label" style="font-size:.85rem">Akun Gmail Pengirim</label>
                            <input type="email" name="smtp_user" class="form-control"
                                   value="<?= htmlspecialchars($notif_cfg['smtp_user'] ?? '') ?>"
                                   placeholder="nama@gmail.com">
                        </div>
                        <div style="margin-bottom:.75rem">
                            <label class="form-label" style="font-size:.85rem">App Password Gmail</label>
                            <input type="password" name="smtp_pass" class="form-control"
                                   placeholder="<?= !empty($notif_cfg['smtp_pass']) ? '••••••••••••••••' : 'Masukkan App Password' ?>">
                            <small style="color:var(--gray-400);font-size:.8rem">
                                Bukan password Gmail biasa. Buat di
                                <a href="https://myaccount.google.com/apppasswords" target="_blank">myaccount.google.com/apppasswords</a>
                                (aktifkan 2FA dulu). Kosongkan jika tidak ingin mengubah password lama.
                            </small>
                        </div>
                        <div>
                            <label class="form-label" style="font-size:.85rem">Nama Pengirim Email</label>
                            <input type="text" name="smtp_from_name" class="form-control"
                                   value="<?= htmlspecialchars($notif_cfg['smtp_from_name'] ?? 'RZ Equipment Rental') ?>">
                        </div>
                    </div>

                    <!-- ── Nama Usaha pada Pesan ── -->
                    <div style="margin-bottom:1.25rem">
                        <label class="form-label"><i class="bi bi-building"></i> Nama Usaha pada Pesan Notifikasi</label>
                        <input type="text" name="company_name_notif" class="form-control"
                               value="<?= htmlspecialchars($notif_cfg['company_name_notif'] ?? 'RZ Equipment Rental') ?>"
                               placeholder="RZ Equipment Rental">
                        <small style="color:var(--gray-400);font-size:.8rem">Muncul pada isi pesan WA dan email konfirmasi.</small>
                    </div>

                    <div style="display:flex;gap:.75rem;flex-wrap:wrap">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Simpan Pengaturan Notifikasi
                        </button>
                        <a href="settings.php?tab=notification" class="btn btn-secondary">Reset</a>
                    </div>
                </form>

                <!-- ── Panduan Instalasi PHPMailer ── -->
                <div style="margin-top:1.5rem;padding:1rem;background:#F8FAFC;border:1px solid #E2E8F0;border-radius:8px;font-size:.82rem;color:#475569">
                    <strong><i class="bi bi-info-circle"></i> Cara pasang PHPMailer:</strong><br>
                    Download dari <a href="https://github.com/PHPMailer/PHPMailer/releases" target="_blank">github.com/PHPMailer/PHPMailer/releases</a>,
                    lalu salin file <code>src/Exception.php</code>, <code>src/PHPMailer.php</code>, <code>src/SMTP.php</code>
                    ke folder <code>src/includes/phpmailer/</code> di proyek ini.
                </div>

                <?php endif; // notif_table_exists ?>
            </div>
        </div>
        <?php endif; // notification tab ?>
</div><!-- /grid -->

</div><!-- /admin-content -->

<script>
// Logo preview on file select
const logoInput = document.getElementById('logoFileInput');
if (logoInput) {
    logoInput.addEventListener('change', function() {
        const file = this.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = e => {
            const img = document.getElementById('logoPreviewImg');
            const placeholder = document.getElementById('logoPlaceholder');
            img.src = e.target.result;
            img.style.display = 'block';
            if (placeholder) placeholder.style.display = 'none';
        };
        reader.readAsDataURL(file);
    });
}

// Password strength checker
function checkPasswordStrength(val) {
    const fill = document.getElementById('strengthFill');
    const text = document.getElementById('strengthText');
    if (!fill || !text) return;

    let score = 0;
    if (val.length >= 8)  score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;

    const levels = [
        { pct: '25%',  color: '#EF4444', label: 'Sangat Lemah' },
        { pct: '50%',  color: '#F59E0B', label: 'Lemah' },
        { pct: '75%',  color: '#3B82F6', label: 'Sedang' },
        { pct: '100%', color: '#22C55E', label: 'Kuat' },
    ];
    const lvl = levels[Math.max(0, score - 1)] || levels[0];
    fill.style.width     = val.length ? lvl.pct   : '0';
    fill.style.background = val.length ? lvl.color : '';
    text.textContent     = val.length ? lvl.label  : '';
    text.style.color     = val.length ? lvl.color  : '';
}

// Confirm password match checker
function checkMatch() {
    const np = document.getElementById('new_password');
    const cp = document.getElementById('confirm_password');
    const mt = document.getElementById('matchText');
    if (!np || !cp || !mt) return;
    if (!cp.value) { mt.textContent = ''; return; }
    if (np.value === cp.value) {
        mt.textContent = '✓ Password cocok';
        mt.style.color = '#15803D';
    } else {
        mt.textContent = '✗ Password tidak cocok';
        mt.style.color = '#DC2626';
    }
}
</script>

<?php require_once '../src/includes/admin-footer.php'; ?>
