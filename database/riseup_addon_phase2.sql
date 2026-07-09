-- =====================================================================
--  RiseUp — Addon Phase 2 (Midtrans + Vault Withdrawal)
--  Untuk DB yang SUDAH ada dari versi sebelumnya. Aman dijalankan sekali.
--    mysql -u root riseup < riseup_addon_phase2.sql
-- =====================================================================

SET NAMES utf8mb4;

-- 1) Kolom Midtrans di setoran
ALTER TABLE sav_saveup_deposit
    ADD COLUMN dep_status       ENUM('paid','pending','failed','manual') NOT NULL DEFAULT 'manual' AFTER dep_date,
    ADD COLUMN dep_source       ENUM('manual','midtrans') NOT NULL DEFAULT 'manual' AFTER dep_status,
    ADD COLUMN dep_order_id     VARCHAR(64) NULL AFTER dep_source,
    ADD COLUMN dep_payment_type VARCHAR(40) NULL AFTER dep_order_id,
    ADD COLUMN dep_paid_at      DATETIME NULL AFTER dep_payment_type,
    ADD UNIQUE KEY uq_dep_order (dep_order_id);

-- 2) Withdrawal (vault)
CREATE TABLE IF NOT EXISTS wdr_saveup_withdrawal (
    wdr_id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    wdr_usr_id        INT UNSIGNED NOT NULL,
    wdr_amount        DECIMAL(12,2) NOT NULL,
    wdr_reason        VARCHAR(255) NULL,
    wdr_status        ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    wdr_admin_note    VARCHAR(255) NULL,
    wdr_processed_at  DATETIME NULL,
    wdr_created_at    DATETIME NULL,
    PRIMARY KEY (wdr_id),
    KEY idx_wdr_usr (wdr_usr_id),
    CONSTRAINT fk_wdr_usr FOREIGN KEY (wdr_usr_id) REFERENCES usr_user(usr_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
