-- =====================================================================
--  RiseUp — Addon Phase 1B (Badge + Jam Rawan)
--  Untuk DB yang SUDAH pernah di-import riseup_database.sql versi lama
--  dan tidak ingin data hilang. Jalankan SEKALI.
--    mysql -u root riseup < riseup_addon_phase1b.sql
--  (Kalau import ulang riseup_database.sql penuh, addon ini TIDAK perlu.)
-- =====================================================================

SET NAMES utf8mb4;

-- 1) Kolom jam rawan pada onboarding baseline
ALTER TABLE bas_onboarding_baseline
    ADD COLUMN bas_risk_hour_start TIME NULL AFTER bas_est_income_monthly,
    ADD COLUMN bas_risk_hour_end   TIME NULL AFTER bas_risk_hour_start;

-- 2) Tabel badge
CREATE TABLE IF NOT EXISTS bdg_badge (
    bdg_id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    bdg_code           VARCHAR(40) NOT NULL,
    bdg_name           VARCHAR(80) NOT NULL,
    bdg_description    VARCHAR(200) NULL,
    bdg_icon           VARCHAR(16) NULL,
    bdg_condition_type VARCHAR(30) NOT NULL,
    bdg_condition_value INT NOT NULL DEFAULT 0,
    bdg_is_active      TINYINT(1) NOT NULL DEFAULT 1,
    bdg_created_at     DATETIME NULL,
    PRIMARY KEY (bdg_id),
    UNIQUE KEY uq_bdg_code (bdg_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ubd_user_badge (
    ubd_id        INT UNSIGNED NOT NULL AUTO_INCREMENT,
    ubd_usr_id    INT UNSIGNED NOT NULL,
    ubd_bdg_id    INT UNSIGNED NOT NULL,
    ubd_earned_at DATETIME NULL,
    PRIMARY KEY (ubd_id),
    UNIQUE KEY uq_ubd_usr_bdg (ubd_usr_id, ubd_bdg_id),
    CONSTRAINT fk_ubd_usr FOREIGN KEY (ubd_usr_id) REFERENCES usr_user(usr_id) ON DELETE CASCADE,
    CONSTRAINT fk_ubd_bdg FOREIGN KEY (ubd_bdg_id) REFERENCES bdg_badge(bdg_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3) Seed badge (INSERT IGNORE supaya aman kalau dijalankan ulang)
INSERT IGNORE INTO bdg_badge (bdg_code, bdg_name, bdg_description, bdg_icon, bdg_condition_type, bdg_condition_value, bdg_is_active, bdg_created_at) VALUES
('first_step',   'Langkah Pertama',   'Menyelesaikan check-in pertamamu.',            '🌱', 'checkin_count',  1,   1, NOW()),
('streak_3',     'Konsisten 3 Hari',  'Mencapai 3 hari hijau beruntun.',              '🔥', 'green_streak',   3,   1, NOW()),
('streak_7',     'Seminggu Bersih',   'Mencapai 7 hari hijau beruntun.',              '🏆', 'green_streak',   7,   1, NOW()),
('learner',      'Pembelajar',        'Menyelesaikan 3 materi microlearning.',        '📚', 'learning_done',  3,   1, NOW()),
('activist',     'Aktivis Positif',   'Menyelesaikan 5 positive quest.',              '⚡', 'quest_done',     5,   1, NOW()),
('reflective',   'Reflektif',         'Menulis 3 jurnal harian.',                     '✍️', 'journal_count',  3,   1, NOW()),
('xp_100',       'Pengumpul XP',      'Mengumpulkan total 100 XP.',                   '⭐', 'total_xp',       100, 1, NOW()),
('xp_300',       'Juara XP',          'Mengumpulkan total 300 XP.',                   '🌟', 'total_xp',       300, 1, NOW());

-- 4) (opsional) set jam rawan demo user
UPDATE bas_onboarding_baseline SET bas_risk_hour_start='20:00:00', bas_risk_hour_end='23:00:00' WHERE bas_usr_id=1;
