-- ============================================================
-- Migration: Notification Settings & Log
-- Jalankan sekali di phpMyAdmin → SQL tab
-- ============================================================

-- Tabel konfigurasi notifikasi
CREATE TABLE IF NOT EXISTS `notification_settings` (
  `id`                 int(11)      NOT NULL AUTO_INCREMENT,
  `fonnte_token`       varchar(255) DEFAULT NULL   COMMENT 'Token API Fonnte untuk WhatsApp',
  `smtp_host`          varchar(150) DEFAULT 'smtp.gmail.com',
  `smtp_port`          smallint     DEFAULT 587,
  `smtp_user`          varchar(150) DEFAULT NULL   COMMENT 'Email pengirim (akun Gmail)',
  `smtp_pass`          varchar(255) DEFAULT NULL   COMMENT 'App Password Gmail (bukan password biasa)',
  `smtp_from_name`     varchar(100) DEFAULT 'RZ Equipment Rental',
  `company_name_notif` varchar(150) DEFAULT 'RZ Equipment Rental' COMMENT 'Nama usaha pada pesan notifikasi',
  `wa_enabled`         tinyint(1)   DEFAULT 1,
  `email_enabled`      tinyint(1)   DEFAULT 1,
  `updated_at`         timestamp    NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert baris default jika belum ada
INSERT INTO `notification_settings` (`id`) VALUES (1)
ON DUPLICATE KEY UPDATE `id` = 1;

-- Tabel log pengiriman notifikasi
CREATE TABLE IF NOT EXISTS `notification_log` (
  `id`             int(11)     NOT NULL AUTO_INCREMENT,
  `reservation_id` int(11)     NOT NULL,
  `type`           enum('wa','email') NOT NULL,
  `status`         enum('success','failed') NOT NULL,
  `message`        text        DEFAULT NULL,
  `created_at`     timestamp   NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_reservation` (`reservation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
