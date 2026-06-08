-- Seed data untuk categories
-- Jalankan ini setelah update_admin_table.sql

INSERT INTO `categories` (`category_name`, `description`, `created_at`, `updated_at`) VALUES
('Sound System', 'Peralatan audio profesional untuk event dan konser', NOW(), NOW()),
('Lighting', 'Lampu panggung, LED, moving head, dan efek lighting', NOW(), NOW()),
('Stage & Truss', 'Panggung modular dan struktur truss untuk event', NOW(), NOW()),
('Multimedia', 'Proyektor, layar LED, dan display untuk presentasi', NOW(), NOW()),
('Power Supply', 'Generator dan sistem distribusi listrik', NOW(), NOW()),
('Communication', 'IEM, walkie talkie, dan sistem komunikasi', NOW(), NOW());
