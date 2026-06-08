# 🛠️ IMPLEMENTATION PLAN - RZKY Equipment Services

**Tanggal:** 8 Juni 2026  
**Status:** Planning  
**Total Issues:** 15  
**Total Tasks:** 42

---

## 📋 EXECUTIVE SUMMARY

| Kategori | Jumlah | Est. Jam | Prioritas |
|----------|--------|----------|-----------|
| 🔴 Critical | 2 | 1 | P0 (Hari ini) |
| 🟠 High | 3 | 6 | P1 (Minggu ini) |
| 🟡 Medium | 5 | 10 | P2 (Bulan ini) |
| 🟢 Low | 5 | 8 | P3 (Backlog) |
| **TOTAL** | **15** | **25** | - |

---

## 🔴 PHASE 1: CRITICAL ISSUES (1 jam / Hari Ini)

### Issue #1: Git Untracked Files
**Status:** ✅ PREREQUISITE DONE (resolve conflict)  
**Masalah:** File gambar baru belum di-track di git  
**Files:** 
- `assets/images/equipment/rzlogo.png`
- `assets/images/equipment/rzlogo-favicon.svg`

#### Tasks:
| ID | Task | Effort | Status |
|----|------|--------|--------|
| 1.1 | Add untracked image files ke git | 5 min | ⬜ TODO |
| 1.2 | Commit dengan message yang jelas | 5 min | ⬜ TODO |
| 1.3 | Verify files tersimpan di git history | 5 min | ⬜ TODO |

**Commands:**
```bash
git add assets/images/equipment/rzlogo.png
git add assets/images/equipment/rzlogo-favicon.svg
git commit -m "Add equipment logo images - PNG and SVG favicon"
git log --oneline | head -5  # verify
```

---

### Issue #2: Hardcoded Database Credentials
**Status:** 🔴 CRITICAL - Security Issue  
**Masalah:** DB_HOST, DB_USER, DB_PASS hardcoded di source code  
**File:** `src/config/database.php`

#### Root Cause:
Development convenience tanpa environment-based configuration

#### Tasks:
| ID | Task | Effort | Status | Dependencies |
|----|------|--------|--------|--------------|
| 2.1 | Create `.env.example` file dengan template credentials | 15 min | ⬜ TODO | - |
| 2.2 | Modify `src/config/database.php` untuk read dari `.env` | 20 min | ⬜ TODO | 2.1 |
| 2.3 | Add `.env` ke `.gitignore` | 5 min | ⬜ TODO | - |
| 2.4 | Create `.env` (local) dengan actual credentials | 10 min | ⬜ TODO | 2.1 |
| 2.5 | Update ADMIN_README.md dengan setup instructions | 10 min | ⬜ TODO | 2.2 |

**Implementation Details:**

`.env.example`:
```env
# Database Configuration
DB_HOST=localhost
DB_NAME=equipment_rental
DB_USER=root
DB_PASS=
DB_PORT=3306
DB_CHARSET=utf8mb4
```

Update `src/config/database.php`:
```php
// Load environment variables
require_once __DIR__ . '/../../.env.php';

define('DB_HOST',    $_ENV['DB_HOST'] ?? 'localhost');
define('DB_NAME',    $_ENV['DB_NAME'] ?? 'equipment_rental');
define('DB_USER',    $_ENV['DB_USER'] ?? 'root');
define('DB_PASS',    $_ENV['DB_PASS'] ?? '');
```

Create `.env.php` loader:
```php
<?php
// Simple .env parser (alternative to vlucas/phpdotenv)
$env_file = __DIR__ . '/../../.env';
if (file_exists($env_file)) {
    $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}
```

---

## 🟠 PHASE 2: HIGH PRIORITY ISSUES (6 jam / Minggu Ini)

### Issue #3: XSS Vulnerability dalam onclick Handlers
**Status:** 🔴 CRITICAL - Security Risk  
**Masalah:** Nilai PHP tidak di-escape dalam `onclick` attributes  
**Files:**
- `admin/reservations.php` (lines 683, 697)
- `public/reservation.php` (lines 744, 752)

#### Root Cause:
Langsung embed PHP variable ke dalam JavaScript event handler tanpa sanitasi

#### Tasks:
| ID | Task | Effort | Status | Dependencies |
|----|------|--------|--------|--------------|
| 3.1 | Audit semua onclick handlers untuk XSS vulnerability | 15 min | ⬜ TODO | - |
| 3.2 | Create utility function `escapeJs()` untuk safe escaping | 10 min | ⬜ TODO | - |
| 3.3 | Fix onclick di admin/reservations.php (lines 683, 697) | 10 min | ⬜ TODO | 3.2 |
| 3.4 | Fix onclick di public/reservation.php (lines 744, 752) | 10 min | ⬜ TODO | 3.2 |
| 3.5 | Add data-* attributes untuk pass PHP values ke JS | 15 min | ⬜ TODO | 3.3, 3.4 |
| 3.6 | Test semua fixed buttons functionality | 10 min | ⬜ TODO | 3.5 |

**Implementation Details:**

Create utility function (add to `src/config/database.php` atau `src/includes/helpers.php`):
```php
function escapeJs($value) {
    return json_encode($value);
}
```

Before (vulnerable):
```php
onclick="sendNotif(<?= $view_reservation['reservation_id'] ?>, 'wa', this)"
```

After (safe):
```php
data-reservation-id="<?= htmlspecialchars($view_reservation['reservation_id']) ?>"
onclick="sendNotif(this.getAttribute('data-reservation-id'), 'wa', this)"
```

Or dengan proper escaping:
```php
onclick="sendNotif(<?= escapeJs($view_reservation['reservation_id']) ?>, 'wa', this)"
```

---

### Issue #4: Undefined/Inconsistent Session Variables
**Status:** 🟠 HIGH - Runtime Risk  
**Masalah:** Session variables diakses tanpa null-checks  
**Files:** Multiple files dalam admin dan public

#### Root Cause:
Tidak ada fallback untuk session yang belum di-set

#### Tasks:
| ID | Task | Effort | Status | Dependencies |
|----|------|--------|--------|--------------|
| 4.1 | Create session initialization file `src/includes/session-init.php` | 15 min | ⬜ TODO | - |
| 4.2 | Define standardized session key constants | 10 min | ⬜ TODO | - |
| 4.3 | Add null-coalescing checks di booking-success.php | 10 min | ⬜ TODO | 4.1, 4.2 |
| 4.4 | Add null-coalescing checks di admin/dashboard.php | 10 min | ⬜ TODO | 4.1, 4.2 |
| 4.5 | Add null-coalescing checks di admin/reservations.php | 10 min | ⬜ TODO | 4.1, 4.2 |
| 4.6 | Test semua session flows dari login hingga checkout | 15 min | ⬜ TODO | 4.5 |

**Implementation Details:**

`src/includes/session-constants.php`:
```php
<?php
// Session Keys Constants
define('SESSION_ADMIN_LOGGED_IN', 'admin_logged_in');
define('SESSION_ADMIN_ID', 'admin_id');
define('SESSION_ADMIN_USERNAME', 'admin_username');
define('SESSION_ADMIN_NAME', 'admin_name');

// Reservation session keys
define('SESSION_RES_START', 'res_start');
define('SESSION_RES_END', 'res_end');
define('SESSION_RES_EQUIPMENT', 'res_equipment');
define('SESSION_BOOKING_SUCCESS', 'booking_success');
```

Usage (before):
```php
<?= $_SESSION['admin_name'] ?>  // PHP Warning jika tidak set
```

Usage (after):
```php
<?= $_SESSION[SESSION_ADMIN_NAME] ?? 'Guest' ?>  // Safe dengan default
```

---

### Issue #5: Invoice Generator File Dependency
**Status:** 🟠 HIGH - Reliability Issue  
**Masalah:** Fatal error jika `invoice-generator.php` tidak ditemukan  
**File:** `admin/reservations.php#L31`

#### Root Cause:
Hardcoded require tanpa fallback; tidak ada error handling jika file missing

#### Tasks:
| ID | Task | Effort | Status | Dependencies |
|----|------|--------|--------|--------------|
| 5.1 | Verify `src/includes/invoice-generator.php` exists | 5 min | ⬜ TODO | - |
| 5.2 | Create wrapper function `getInvoiceGenerator()` | 10 min | ⬜ TODO | 5.1 |
| 5.3 | Add try-catch di admin/reservations.php untuk invoice generation | 15 min | ⬜ TODO | 5.2 |
| 5.4 | Log invoice generation errors ke database/file | 10 min | ⬜ TODO | 5.3 |
| 5.5 | Add fallback notification jika PDF generation gagal | 10 min | ⬜ TODO | 5.4 |
| 5.6 | Test invoice generation dengan manual approval flow | 10 min | ⬜ TODO | 5.5 |

**Implementation Details:**

Create in `src/includes/helpers.php`:
```php
function getInvoiceGenerator() {
    $path = __DIR__ . '/invoice-generator.php';
    if (!file_exists($path)) {
        throw new Exception('Invoice generator file not found: ' . $path);
    }
    return $path;
}
```

In `admin/reservations.php`:
```php
try {
    require_once getInvoiceGenerator();
    // invoice generation code
} catch (Exception $e) {
    error_log('Invoice generation failed: ' . $e->getMessage());
    $_SESSION['warning'][] = 'Reservasi diapprove tapi PDF gagal di-generate. Bisa di-generate manual nanti.';
}
```

---

## 🟡 PHASE 3: MEDIUM PRIORITY ISSUES (10 jam / Bulan Ini)

### Issue #6: Inconsistent File Path Resolution
**Status:** 🟡 MEDIUM - Maintenance Risk  
**Masalah:** Mixing relative dan absolute paths dengan realpath()  
**Files:** `src/inventory/equipment-edit.php#L75-89`, `equipment-create.php#L51`

#### Tasks:
| ID | Task | Effort | Status | Dependencies |
|----|------|--------|--------|--------------|
| 6.1 | Create `path-helper.php` untuk centralized path management | 15 min | ⬜ TODO | - |
| 6.2 | Define constants untuk equipment upload directory | 10 min | ⬜ TODO | 6.1 |
| 6.3 | Refactor `equipment-create.php` pakai path helper | 15 min | ⬜ TODO | 6.1, 6.2 |
| 6.4 | Refactor `equipment-edit.php` pakai path helper | 15 min | ⬜ TODO | 6.1, 6.2 |
| 6.5 | Test file upload dan old image deletion flow | 10 min | ⬜ TODO | 6.4 |

**Implementation Details:**

`src/includes/path-helper.php`:
```php
<?php
class PathHelper {
    const UPLOAD_BASE = __DIR__ . '/../../assets/images/equipment/';
    
    public static function getEquipmentDir() {
        $dir = self::UPLOAD_BASE;
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        return $dir;
    }
    
    public static function getEquipmentPath($filename) {
        $realpath = realpath(self::getEquipmentDir() . $filename);
        if ($realpath === false) {
            throw new Exception('Invalid equipment file path');
        }
        return $realpath;
    }
    
    public static function saveEquipmentImage($uploaded_file, $prefix = 'eq_') {
        $filename = $prefix . time() . '_' . random_int(1000, 9999) . '.jpg';
        $target = self::getEquipmentDir() . $filename;
        
        if (!move_uploaded_file($uploaded_file, $target)) {
            throw new Exception('Failed to save equipment image');
        }
        
        return 'eq_' . $filename;  // relative path untuk database
    }
    
    public static function deleteEquipmentImage($filename) {
        $path = self::getEquipmentPath($filename);
        if (file_exists($path)) {
            unlink($path);
        }
    }
}
```

---

### Issue #7: Missing Favicon Path & Untracked Image File
**Status:** 🟡 MEDIUM - User Experience  
**Masalah:** Favicon reference tapi file untracked  
**File:** `src/includes/header.php#L25-27`

#### Tasks:
| ID | Task | Effort | Status | Dependencies |
|----|------|--------|--------|--------------|
| 7.1 | Add favicon files ke git (already done in Issue #1) | 5 min | ✅ INCLUDED-IN-1 | 1.2 |
| 7.2 | Verify favicon path di header.php correct | 5 min | ⬜ TODO | - |
| 7.3 | Test favicon loading di browser | 5 min | ⬜ TODO | 7.2 |

---

### Issue #8: Admin Path Navigation Complexity
**Status:** 🟡 MEDIUM - Code Maintainability  
**Masalah:** Complex conditional path di `admin-header.php`  
**File:** `src/includes/admin-header.php#L35-50`

#### Tasks:
| ID | Task | Effort | Status | Dependencies |
|----|------|--------|--------|--------------|
| 8.1 | Create global `$BASE_PATH` constant | 10 min | ⬜ TODO | - |
| 8.2 | Refactor conditional paths ke standardized format | 20 min | ⬜ TODO | 8.1 |
| 8.3 | Test navigation links dari admin dan inventory pages | 15 min | ⬜ TODO | 8.2 |

**Implementation Details:**

Di `src/config/database.php` (atau di bagian atas admin-header.php):
```php
// Determine base path based on current file location
if (!defined('BASE_URL')) {
    $script_path = dirname($_SERVER['SCRIPT_NAME']);
    if (strpos($script_path, '/src/inventory') !== false) {
        define('BASE_URL', '../../');
        define('IS_INVENTORY_PAGE', true);
    } elseif (strpos($script_path, '/admin') !== false) {
        define('BASE_URL', '../');
        define('IS_INVENTORY_PAGE', false);
    } else {
        define('BASE_URL', './');
        define('IS_INVENTORY_PAGE', false);
    }
}
```

Replace in `admin-header.php`:
```php
// Before
<?= isset($is_admin_folder) && $is_admin_folder ? 'dashboard.php' : '../../admin/dashboard.php' ?>

// After
<?= BASE_URL . 'admin/dashboard.php' ?>
```

---

### Issue #9: CSS Path Inconsistency
**Status:** 🟡 MEDIUM - Code Quality  
**Masalah:** CSS path dideklarasikan berbeda di berbagai files  
**Files:** `admin/dashboard.php`, `src/inventory/equipment-list.php`

#### Tasks:
| ID | Task | Effort | Status | Dependencies |
|----|------|--------|--------|--------------|
| 9.1 | Create css-loader.php helper | 10 min | ⬜ TODO | - |
| 9.2 | Add function `loadCSS()` untuk include CSS dengan base path aware | 10 min | ⬜ TODO | 9.1 |
| 9.3 | Refactor semua files untuk use loadCSS() | 15 min | ⬜ TODO | 9.2 |
| 9.4 | Test CSS loading di admin dan inventory pages | 10 min | ⬜ TODO | 9.3 |

**Implementation Details:**

`src/includes/css-loader.php`:
```php
<?php
function loadCSS($stylesheet) {
    $base = IS_INVENTORY_PAGE ? '../../' : '../';
    echo "<link rel='stylesheet' href='{$base}assets/css/{$stylesheet}.css'>";
}

function loadJS($script) {
    $base = IS_INVENTORY_PAGE ? '../../' : '../';
    echo "<script src='{$base}assets/js/{$script}.js'></script>";
}
```

---

### Issue #10: No Consistent Error Logging
**Status:** 🟡 MEDIUM - Maintainability & Security  
**Masalah:** Error messages di-expose ke user; tidak ada logging  
**Files:** Multiple catch blocks

#### Tasks:
| ID | Task | Effort | Status | Dependencies |
|----|------|--------|--------|--------------|
| 10.1 | Create error-logger.php dengan logging functionality | 20 min | ⬜ TODO | - |
| 10.2 | Create logs/ directory dengan .gitignore | 5 min | ⬜ TODO | - |
| 10.3 | Update catch blocks di inventory files pakai logger | 20 min | ⬜ TODO | 10.1, 10.2 |
| 10.4 | Update catch blocks di admin files pakai logger | 15 min | ⬜ TODO | 10.1, 10.2 |
| 10.5 | Create admin page untuk view error logs | 20 min | ⬜ TODO | 10.4 |
| 10.6 | Test error logging dengan intentional errors | 10 min | ⬜ TODO | 10.5 |

**Implementation Details:**

`src/includes/error-logger.php`:
```php
<?php
class ErrorLogger {
    const LOG_DIR = __DIR__ . '/../../logs/';
    const LOG_FILE = 'error.log';
    
    public static function log($level, $message, $context = []) {
        if (!is_dir(self::LOG_DIR)) {
            mkdir(self::LOG_DIR, 0755, true);
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $log_entry = "[$timestamp] [$level] $message";
        
        if (!empty($context)) {
            $log_entry .= ' | ' . json_encode($context);
        }
        
        error_log($log_entry . PHP_EOL, 3, self::LOG_DIR . self::LOG_FILE);
    }
    
    public static function error($message, $context = []) {
        self::log('ERROR', $message, $context);
    }
    
    public static function warning($message, $context = []) {
        self::log('WARNING', $message, $context);
    }
}
```

Usage:
```php
catch (PDOException $e) {
    ErrorLogger::error('Equipment creation failed', ['error' => $e->getMessage()]);
    $_SESSION['error'][] = 'Gagal menambahkan equipment. Tim teknis sudah diberitahu.';
}
```

---

## 🟢 PHASE 4: LOW PRIORITY ISSUES (8 jam / Backlog)

### Issue #11: Confirmation Dialog Inline JavaScript
**Status:** 🟢 LOW - UX Improvement  
**Masalah:** Inline confirmation dialogs tidak konsisten dengan design  
**Files:** Multiple (admin/settings.php, category-list.php, equipment-list.php, reservations.php)

#### Tasks:
| ID | Task | Effort | Status | Dependencies |
|----|------|--------|--------|--------------|
| 11.1 | Create modal-confirm.html template component | 15 min | ⬜ TODO | - |
| 11.2 | Create confirmDelete() JS function dengan modal | 15 min | ⬜ TODO | 11.1 |
| 11.3 | Replace inline confirm() di admin/settings.php | 10 min | ⬜ TODO | 11.2 |
| 11.4 | Replace inline confirm() di category & equipment list | 15 min | ⬜ TODO | 11.2 |
| 11.5 | Add CSS styling untuk match design system | 10 min | ⬜ TODO | 11.2 |
| 11.6 | Test modal confirm flow | 10 min | ⬜ TODO | 11.5 |

---

### Issue #12: Database Query Optimization
**Status:** 🟢 LOW - Performance  
**Masalah:** Multiple separate queries untuk statistics di dashboard  
**File:** `admin/dashboard.php#L23-26`

#### Tasks:
| ID | Task | Effort | Status | Dependencies |
|----|------|--------|--------|--------------|
| 12.1 | Combine 3 COUNT queries menjadi 1 query | 10 min | ⬜ TODO | - |
| 12.2 | Benchmark performance improvement | 10 min | ⬜ TODO | 12.1 |
| 12.3 | Add index di database untuk optimization | 5 min | ⬜ TODO | - |

**Query Optimization:**

Before (3 queries):
```php
$total_equipment = $db->query("SELECT COUNT(*) FROM equipment")->fetchColumn();
$total_categories = $db->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$total_reservations = $db->query("SELECT COUNT(*) FROM reservations")->fetchColumn();
```

After (1 query):
```php
$stats = $db->query("
    SELECT 
        (SELECT COUNT(*) FROM equipment) as total_equipment,
        (SELECT COUNT(*) FROM categories) as total_categories,
        (SELECT COUNT(*) FROM reservations) as total_reservations
")->fetch();

$total_equipment = $stats['total_equipment'];
$total_categories = $stats['total_categories'];
$total_reservations = $stats['total_reservations'];
```

---

### Issue #13: Unused Form Data State
**Status:** 🟢 LOW - Code Quality  
**Masalah:** Form data disimpan tapi tidak digunakan untuk pre-fill setelah error  
**File:** `src/inventory/equipment-create.php#L70`

#### Tasks:
| ID | Task | Effort | Status | Dependencies |
|----|------|--------|--------|--------------|
| 13.1 | Add form pre-fill logic di equipment-create.php | 15 min | ⬜ TODO | - |
| 13.2 | Pre-fill form fields dengan $form_data values | 15 min | ⬜ TODO | 13.1 |
| 13.3 | Do same untuk equipment-edit.php | 10 min | ⬜ TODO | 13.1 |
| 13.4 | Test form validation error dan pre-fill | 10 min | ⬜ TODO | 13.3 |

---

### Issue #14: Favicon Cache Busting Parameter
**Status:** 🟢 LOW - Best Practice  
**Masalah:** Manual version numbering pada favicon  
**File:** `src/includes/header.php#L25-27`

#### Tasks:
| ID | Task | Effort | Status | Dependencies |
|----|------|--------|--------|--------------|
| 14.1 | Create auto-versioning helper function | 10 min | ⬜ TODO | - |
| 14.2 | Update favicon link pakai auto-version | 5 min | ⬜ TODO | 14.1 |
| 14.3 | Apply same versioning ke CSS dan JS includes | 10 min | ⬜ TODO | 14.1 |

**Implementation:**

`src/includes/version-helper.php`:
```php
<?php
function assetVersion($filename) {
    $filepath = __DIR__ . '/../../' . $filename;
    if (file_exists($filepath)) {
        return filemtime($filepath);
    }
    return 1;  // fallback
}
```

Usage:
```php
<?php $v = assetVersion('assets/images/equipment/rzlogo.png'); ?>
<link rel="icon" href="../assets/images/equipment/rzlogo.png?v=<?= $v ?>">
```

---

### Issue #15: Database Schema Migration in Settings
**Status:** 🟢 LOW - Architecture  
**Masalah:** Manual migration logic di settings.php  
**File:** `admin/settings.php`

#### Tasks:
| ID | Task | Effort | Status | Dependencies |
|----|------|--------|--------|--------------|
| 15.1 | Create migration system di database/ folder | 20 min | ⬜ TODO | - |
| 15.2 | Create run-migrations.php CLI script | 15 min | ⬜ TODO | 15.1 |
| 15.3 | Move migration logic dari settings.php | 10 min | ⬜ TODO | 15.2 |
| 15.4 | Update ADMIN_README.md dengan migration instructions | 10 min | ⬜ TODO | 15.3 |

---

## 🎯 EXECUTION ROADMAP

### Week 1 (Days 1-7)
```
Day 1 (Today):
  ✅ CRITICAL Issue #2 - Hardcoded credentials → .env
  ✅ CRITICAL Issue #3 - XSS in onclick → Escaped

Day 2-3:
  □ HIGH Issue #4 - Session variables → Null-coalescing
  □ HIGH Issue #5 - Invoice generator → Error handling

Day 4-5:
  □ MEDIUM Issue #6 - Path resolution → Path helper
  □ MEDIUM Issue #8 - Admin navigation → BASE_PATH constant

Day 6-7:
  □ MEDIUM Issue #9 - CSS paths → CSS loader
  □ MEDIUM Issue #10 - Error logging → Error logger
```

### Week 2-3 (Days 8-21)
```
  □ LOW Issue #11 - Confirm dialogs → Modal component
  □ LOW Issue #12 - Query optimization → Combined queries
  □ LOW Issue #13 - Form pre-fill → Re-populate form
  □ LOW Issue #14 - Cache busting → Auto-versioning
  □ LOW Issue #15 - Schema migration → Migration system
```

### Testing & QA (Days 22-28)
```
  □ Integration testing
  □ Security audit
  □ Performance testing
  □ User acceptance testing
  □ Production deployment prep
```

---

## 📊 DEPENDENCY GRAPH

```
Issue #2 (Credentials)
  ↓
Issue #3 (XSS) ← Independent
Issue #4 (Sessions) ← Independent
  ↓
Issue #5 (Invoice)
  ↓
Issue #6 (Paths)
  ├→ Issue #8 (Navigation)
  └→ Issue #9 (CSS)
      ↓
      Issue #10 (Logging)
        ↓
        Issue #11 (Dialogs)

Issue #12-15 (Independent from others)
```

---

## 🔧 TOOLS & TECHNOLOGIES NEEDED

| Tool | Purpose | Already Have? |
|------|---------|---------------|
| Git | Version control | ✅ Yes |
| PHP 8.0+ | Backend | ✅ Yes |
| MySQL 5.7+ | Database | ✅ Yes |
| phpMyAdmin | Database management | ✅ Yes |
| VS Code | Editor | ✅ Yes |
| Postman/Insomnia | API testing | ⚠️ Optional |

---

## ✅ COMPLETION CRITERIA

Each issue must satisfy:

- [ ] Code reviewed and tested
- [ ] No PHP warnings/errors
- [ ] Database queries optimized
- [ ] Security vulnerabilities resolved
- [ ] Documentation updated
- [ ] Changes committed to git with clear message
- [ ] No regression in existing functionality

---

## 📝 NOTES

- **Backward Compatibility:** Semua changes harus backward-compatible dengan existing data
- **Testing:** Setiap change harus di-test di XAMPP local server
- **Documentation:** Update README files seiring dengan changes
- **Commits:** Frequent, atomic commits dengan meaningful messages
- **Contingency:** Keep 1-2 days buffer untuk unexpected issues

---

**Prepared by:** GitHub Copilot  
**Last Updated:** June 8, 2026  
**Next Review:** After Phase 1 completion
