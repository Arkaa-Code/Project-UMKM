-- ============================================================
-- Equipment Rental Management System — Seed Data
-- Compatible with equipment_rental.sql schema
-- ============================================================

USE `equipment_rental`;

-- ── Categories ───────────────────────────────────────────
INSERT INTO `categories` (`category_id`, `category_name`, `description`) VALUES
(1, 'Sound System',       'Professional audio equipment for concerts, conferences, and events'),
(2, 'Lighting',           'Stage and architectural lighting for events and productions'),
(3, 'Stage',              'Stage platforms, trusses, and furniture for event productions'),
(4, 'Multimedia',         'LED screens, projectors, and display solutions'),
(5, 'Power Distribution', 'Generators and power management equipment'),
(6, 'Communication',      'Walkie talkies, IEM systems, and intercom solutions');

-- ── Equipment — Sound System ─────────────────────────────
INSERT INTO `equipment` (`category_id`, `equipment_name`, `description`, `total_stock`, `rental_price`, `image_path`, `condition_status`) VALUES
(1, 'Line Array Speaker (per unit)',
   'Professional line array speaker with high SPL output. Frequency response: 65Hz–18kHz. Ideal for large concerts and outdoor events.',
   12, 500000.00,
   'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=400&h=250&fit=crop', 'Baik'),

(1, 'Subwoofer 18"',
   'High-powered 18-inch subwoofer delivering deep, punchy bass. Perfect for clubs, concerts, and events.',
   8, 300000.00,
   'https://images.unsplash.com/photo-1545127398-14699f92334b?w=400&h=250&fit=crop', 'Baik'),

(1, 'Digital Mixing Console 48ch',
   'Professional 48-channel digital mixing console with onboard effects, EQ, and dynamics for FOH and monitor mixing.',
   3, 750000.00,
   'https://images.unsplash.com/photo-1598488035139-bdbb2231ce04?w=400&h=250&fit=crop', 'Baik'),

(1, 'Stage Monitor Speaker',
   'Floor monitor speaker for on-stage performers with clear mid-range and feedback rejection.',
   10, 200000.00,
   'https://images.unsplash.com/photo-1520170350707-b2da59970118?w=400&h=250&fit=crop', 'Baik'),

(1, 'Wireless Microphone System (2-ch)',
   '2-channel UHF wireless microphone system with handheld transmitters. Frequency-selectable for multi-system use.',
   6, 250000.00,
   'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?w=400&h=250&fit=crop', 'Baik'),

(1, 'DI Box (Active)',
   'Professional active DI box for converting unbalanced signals to balanced XLR. For keyboards and acoustic instruments.',
   20, 75000.00,
   'https://images.unsplash.com/photo-1619983081563-430f63602796?w=400&h=250&fit=crop', 'Baik');

-- ── Equipment — Lighting ─────────────────────────────────
INSERT INTO `equipment` (`category_id`, `equipment_name`, `description`, `total_stock`, `rental_price`, `image_path`, `condition_status`) VALUES
(2, 'Moving Head Beam 230W',
   'Professional 230W moving head beam with sharp parallel beam, gobo rotation, and full color mixing.',
   8, 450000.00,
   'https://images.unsplash.com/photo-1501386761578-eac5c94b800a?w=400&h=250&fit=crop', 'Baik'),

(2, 'LED Par Can RGBWA',
   'Full-color RGBWA LED par can with smooth color mixing. Perfect for wash lighting and stage color.',
   24, 150000.00,
   'https://images.unsplash.com/photo-1516450360452-9312f5e86fc7?w=400&h=250&fit=crop', 'Baik'),

(2, 'Follow Spot 2000W',
   '2000-watt follow spot for tracking performers on stage. Variable zoom, iris, and color frame. Includes stand.',
   2, 600000.00,
   'https://images.unsplash.com/photo-1429962714451-bb934ecdc4ec?w=400&h=250&fit=crop', 'Baik'),

(2, 'Haze Machine',
   'Professional haze machine for atmospheric haze to enhance beam visibility. Low-residue fluid.',
   4, 200000.00,
   'https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?w=400&h=250&fit=crop', 'Baik'),

(2, 'Strobe Light 3200W',
   'High-intensity strobe with variable speed and burst control for concerts and events.',
   6, 175000.00,
   'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?w=400&h=250&fit=crop', 'Baik'),

(2, 'DMX Lighting Controller',
   '512-channel DMX console for programming and controlling stage lighting rigs.',
   3, 350000.00,
   'https://images.unsplash.com/photo-1598488035139-bdbb2231ce04?w=400&h=250&fit=crop', 'Baik');

-- ── Equipment — Stage ────────────────────────────────────
INSERT INTO `equipment` (`category_id`, `equipment_name`, `description`, `total_stock`, `rental_price`, `image_path`, `condition_status`) VALUES
(3, 'Stage Platform 2x1m (per unit)',
   'Heavy-duty aluminum stage platform, 2x1m, adjustable height 60–100cm. Load rating: 750 kg/m².',
   40, 150000.00,
   'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=400&h=250&fit=crop', 'Baik'),

(3, 'Portable Podium / Lectern',
   'Elegant portable podium with reading light and microphone holder. For conferences and ceremonies.',
   5, 200000.00,
   'https://images.unsplash.com/photo-1573167243872-43c6433b9d40?w=400&h=250&fit=crop', 'Baik'),

(3, 'Stage Stairs (per set)',
   'Safety stage access stairs with handrail. Compatible with 60–100cm platforms. Non-slip tread.',
   10, 100000.00,
   'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=400&h=250&fit=crop', 'Baik'),

(3, 'Box Truss 2m Section',
   '2-meter aluminum box truss for rigging lights, audio, and visual equipment. Load: 500 kg/m.',
   30, 125000.00,
   'https://images.unsplash.com/photo-1518611012118-696072aa579a?w=400&h=250&fit=crop', 'Baik');

-- ── Equipment — Multimedia ───────────────────────────────
INSERT INTO `equipment` (`category_id`, `equipment_name`, `description`, `total_stock`, `rental_price`, `image_path`, `condition_status`) VALUES
(4, 'LED Screen P3 Indoor (per sqm)',
   'P3 indoor LED screen panel. High brightness and contrast for vivid presentations. Modular assembly.',
   20, 600000.00,
   'https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?w=400&h=250&fit=crop', 'Baik'),

(4, 'Projector 10000 Lumen',
   'Laser projector, 10,000 ANSI lumens. For large venues, outdoor events, and ballrooms.',
   3, 800000.00,
   'https://images.unsplash.com/photo-1558618047-3c8c76ca7d13?w=400&h=250&fit=crop', 'Baik'),

(4, 'Video Switcher / Mixer',
   'Multi-input video switcher with HDMI, SDI, and VGA inputs. Real-time video mixing and recording.',
   4, 400000.00,
   'https://images.unsplash.com/photo-1581092921461-eab62e97a780?w=400&h=250&fit=crop', 'Baik'),

(4, 'LED Monitor 55" Display',
   '55-inch Full HD LED commercial display for side stage IMAG, info displays, and sponsor screens.',
   6, 350000.00,
   'https://images.unsplash.com/photo-1593640408182-31c228e2e7d5?w=400&h=250&fit=crop', 'Baik');

-- ── Equipment — Power Distribution ──────────────────────
INSERT INTO `equipment` (`category_id`, `equipment_name`, `description`, `total_stock`, `rental_price`, `image_path`, `condition_status`) VALUES
(5, 'Generator 100 KVA',
   'Silent diesel generator, 100 KVA with automatic voltage regulation. For large-scale events.',
   2, 2000000.00,
   'https://images.unsplash.com/photo-1621905251918-48416bd8575a?w=400&h=250&fit=crop', 'Baik'),

(5, 'Generator 50 KVA',
   'Silent diesel generator, 50 KVA output. Compact power source for medium-scale events.',
   3, 1000000.00,
   'https://images.unsplash.com/photo-1558981285-6f0c987eba91?w=400&h=250&fit=crop', 'Baik'),

(5, 'Power Distribution Box (32A)',
   '32A distribution unit with circuit breakers and multiple outlets for organized power management.',
   8, 200000.00,
   'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=400&h=250&fit=crop', 'Baik');

-- ── Equipment — Communication ────────────────────────────
INSERT INTO `equipment` (`category_id`, `equipment_name`, `description`, `total_stock`, `rental_price`, `image_path`, `condition_status`) VALUES
(6, 'Walkie Talkie (per unit)',
   'Professional UHF walkie talkie, 16 channels, noise-cancelling mic. Range up to 5km open area.',
   20, 75000.00,
   'https://images.unsplash.com/photo-1587825140708-dfaf72ae4b04?w=400&h=250&fit=crop', 'Baik'),

(6, 'IEM System (In-Ear Monitor)',
   '4-channel wireless IEM system for on-stage performers. Personal mix via in-ear receivers.',
   4, 500000.00,
   'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=400&h=250&fit=crop', 'Baik'),

(6, 'Wired Intercom System (4-station)',
   '4-station wired intercom for crew communication. Headset-based beltpack units, full-duplex audio.',
   3, 300000.00,
   'https://images.unsplash.com/photo-1560707854-fb9a41690fca?w=400&h=250&fit=crop', 'Baik');
