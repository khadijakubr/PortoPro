-- ============================================================================
-- [RENDER-FIX 8] Migrasi skema untuk Cloudinary — file BARU
-- ----------------------------------------------------------------------------
-- SEBELUM  : projects.thumbnail varchar(255) hanya muat nama file lokal.
-- SESUDAH : + kolom thumbnail_public_id (untuk hapus file di Cloudinary) +
--           thumbnail diperlebar ke 500 (URL CDN lebih panjang dari nama file).
-- CARA PAKAI: jalankan SETELAH import PortoPro.sql ke DB cloud (TiDB /
--           PlanetScale / Clever Cloud). Bila kolom sudah ada, abaikan error
--           "Duplicate column name" — aman dijalankan ulang sebagian.
-- CATATAN TiDB/PlanetScale: foreign key fk_category_id tetap dipertahankan.
-- ============================================================================

ALTER TABLE `projects`
  MODIFY COLUMN `thumbnail` varchar(500) NOT NULL;

ALTER TABLE `projects`
  ADD COLUMN `thumbnail_public_id` varchar(255) NULL DEFAULT NULL
  AFTER `thumbnail`;
