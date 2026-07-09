-- =====================================================================
--  RiseUp — Addon Phase 3B (Google OAuth kolom)
--  Untuk DB yang SUDAH ada. Aman dijalankan sekali.
--    mysql -u root riseup < riseup_addon_phase3b.sql
-- =====================================================================

SET NAMES utf8mb4;

-- 1) Allow NULL password (user Google-only tidak punya password)
ALTER TABLE usr_user
    MODIFY COLUMN usr_password_hash VARCHAR(255) NULL;

-- 2) Kolom Google
ALTER TABLE usr_user
    ADD COLUMN usr_google_id  VARCHAR(64)  NULL AFTER usr_password_hash,
    ADD COLUMN usr_avatar_url VARCHAR(500) NULL AFTER usr_google_id,
    ADD UNIQUE KEY uq_usr_google (usr_google_id);
