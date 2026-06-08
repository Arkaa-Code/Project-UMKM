-- ============================================================
-- Migration: Invoice Settings Table
-- File: database/migration_invoice_settings.sql
-- Run ONCE on your existing equipment_rental database
-- ============================================================

USE `equipment_rental`;

-- ── Create invoice_settings table ────────────────────────
CREATE TABLE IF NOT EXISTS `invoice_settings` (
  `id`                   int(11)      NOT NULL AUTO_INCREMENT,
  `company_name`         varchar(150) NOT NULL DEFAULT 'RZ Equipment Rental',
  `company_address`      text         DEFAULT NULL,
  `company_phone`        varchar(50)  DEFAULT NULL,
  `company_email`        varchar(100) DEFAULT NULL,
  `payment_instructions` text         DEFAULT NULL,
  `invoice_notes`        text         DEFAULT NULL,
  `logo_path`            varchar(255) DEFAULT NULL,
  `updated_at`           timestamp    NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ── Add invoice_path column to reservations ──────────────
-- Stores the generated PDF path once a reservation is approved
ALTER TABLE `reservations`
  ADD COLUMN IF NOT EXISTS `invoice_path` varchar(255) DEFAULT NULL AFTER `notes`;

-- ── Seed default settings row ────────────────────────────
INSERT INTO `invoice_settings`
  (`id`, `company_name`, `company_address`, `company_phone`, `company_email`,
   `payment_instructions`, `invoice_notes`, `logo_path`)
VALUES
  (1,
   'RZ Equipment Rental',
   'Jl. Contoh No. 1, Surakarta, Jawa Tengah 57100',
   '(0271) 000-0000',
   'info@rzrental.com',
   'Transfer ke rekening BCA 1234567890 a.n. RZ Equipment Rental.\nPembayaran harus dilunasi sebelum tanggal sewa.',
   'Terima kasih telah menggunakan layanan kami. Barang harap dikembalikan dalam kondisi baik.',
   NULL)
ON DUPLICATE KEY UPDATE `id` = `id`; -- no-op if already seeded
