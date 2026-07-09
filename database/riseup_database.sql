-- =====================================================================
--  RiseUp — Database Lengkap (MySQL)
--  Import langsung: LANGSUNG import file ini — DB 'riseup_db' dibuat otomatis.
--    mysql -u root < riseup_database.sql
--  atau via phpMyAdmin: TIDAK PERLU buat DB dulu, langsung Import dari root -> file ini.
--
--  Akun bawaan:
--    ADMIN  ->  email: admin@riseup.test   password: admin123
--    USER   ->  email: demo@riseup.test    password: user123
--  (password sudah di-hash bcrypt, cocok dgn Hash::check Laravel)
-- =====================================================================

CREATE DATABASE IF NOT EXISTS riseup_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE riseup_db;

SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4;

-- ---------- drop dulu biar bisa re-import (idempotent) ----------
DROP TABLE IF EXISTS wdr_saveup_withdrawal;
DROP TABLE IF EXISTS ubd_user_badge;
DROP TABLE IF EXISTS bdg_badge;
DROP TABLE IF EXISTS jrn_journal;
DROP TABLE IF EXISTS rel_religious_content;
DROP TABLE IF EXISTS lrp_learning_progress;
DROP TABLE IF EXISTS lrn_microlearning_module;
DROP TABLE IF EXISTS qrp_quest_progress;
DROP TABLE IF EXISTS qst_positive_quest;
DROP TABLE IF EXISTS sav_saveup_deposit;
DROP TABLE IF EXISTS sav_saveup_target;
DROP TABLE IF EXISTS gms_gamification_stat;
DROP TABLE IF EXISTS chk_daily_checkin;
DROP TABLE IF EXISTS bas_onboarding_baseline;
DROP TABLE IF EXISTS prf_user_profile;
DROP TABLE IF EXISTS usr_user;
DROP TABLE IF EXISTS adm_admin;

-- =====================================================================
--  ADMIN
-- =====================================================================
CREATE TABLE adm_admin (
    adm_id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    adm_email         VARCHAR(120) NOT NULL,
    adm_password_hash VARCHAR(255) NOT NULL,
    adm_name          VARCHAR(100) NULL,
    PRIMARY KEY (adm_id),
    UNIQUE KEY uq_adm_email (adm_email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
--  USER
-- =====================================================================
CREATE TABLE usr_user (
    usr_id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    usr_email         VARCHAR(120) NOT NULL,
    usr_password_hash VARCHAR(255) NULL,             -- boleh NULL untuk akun Google-only
    usr_google_id     VARCHAR(64) NULL,              -- 'sub' dari Google (unique)
    usr_avatar_url    VARCHAR(500) NULL,             -- avatar dari Google (opsional)
    usr_status        ENUM('active','inactive') NOT NULL DEFAULT 'active',
    PRIMARY KEY (usr_id),
    UNIQUE KEY uq_usr_email (usr_email),
    UNIQUE KEY uq_usr_google (usr_google_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
--  PROFILE (nama, umur, preferensi agama utk konten religius)
-- =====================================================================
CREATE TABLE prf_user_profile (
    prf_id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    prf_usr_id        INT UNSIGNED NOT NULL,
    prf_full_name     VARCHAR(100) NULL,
    prf_age_years     TINYINT UNSIGNED NULL,
    prf_religion_pref VARCHAR(20) NULL,          -- umum/islam/kristen/katolik/hindu/buddha
    prf_updated_at    DATETIME NULL,
    PRIMARY KEY (prf_id),
    UNIQUE KEY uq_prf_usr (prf_usr_id),
    CONSTRAINT fk_prf_usr FOREIGN KEY (prf_usr_id) REFERENCES usr_user(usr_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
--  ONBOARDING BASELINE
-- =====================================================================
CREATE TABLE bas_onboarding_baseline (
    bas_id                 INT UNSIGNED NOT NULL AUTO_INCREMENT,
    bas_usr_id             INT UNSIGNED NOT NULL,
    bas_exposure_duration  VARCHAR(10) NULL,     -- <6m / 6-12m / >12m
    bas_main_reason        VARCHAR(20) NULL,     -- stres/bosan/teman/uang/lainnya
    bas_target_goal        VARCHAR(30) NULL,     -- stop/reduce_frequency/reduce_duration
    bas_daily_duration     VARCHAR(10) NULL,     -- <30m / 30-60m / 1-2h / >2h
    bas_est_loss_monthly   DECIMAL(12,2) NULL,
    bas_est_income_monthly DECIMAL(12,2) NULL,
    bas_risk_hour_start    TIME NULL,            -- jam rawan mulai (utk reminder Buddy)
    bas_risk_hour_end      TIME NULL,            -- jam rawan selesai
    bas_created_at         DATETIME NULL,
    PRIMARY KEY (bas_id),
    UNIQUE KEY uq_bas_usr (bas_usr_id),
    CONSTRAINT fk_bas_usr FOREIGN KEY (bas_usr_id) REFERENCES usr_user(usr_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
--  DAILY CHECK-IN
-- =====================================================================
CREATE TABLE chk_daily_checkin (
    chk_id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    chk_usr_id         INT UNSIGNED NOT NULL,
    chk_date           DATE NOT NULL,
    chk_mood           VARCHAR(10) NULL,          -- baik/netral/sedih/cemas/stres
    chk_urge_level     TINYINT UNSIGNED NULL,     -- 0..5
    chk_trigger        VARCHAR(20) NULL,
    chk_status_color   ENUM('green','red') NULL,
    chk_relapse_reason VARCHAR(20) NULL,
    chk_note_text      VARCHAR(255) NULL,
    chk_created_at     DATETIME NULL,
    PRIMARY KEY (chk_id),
    UNIQUE KEY uq_chk_usr_date (chk_usr_id, chk_date),
    CONSTRAINT fk_chk_usr FOREIGN KEY (chk_usr_id) REFERENCES usr_user(usr_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
--  GAMIFICATION STAT
-- =====================================================================
CREATE TABLE gms_gamification_stat (
    gms_id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    gms_usr_id     INT UNSIGNED NOT NULL,
    gms_total_xp   INT NOT NULL DEFAULT 0,
    gms_level_num  INT NOT NULL DEFAULT 1,
    gms_weekly_xp  INT NOT NULL DEFAULT 0,
    gms_updated_at DATETIME NULL,
    PRIMARY KEY (gms_id),
    UNIQUE KEY uq_gms_usr (gms_usr_id),
    CONSTRAINT fk_gms_usr FOREIGN KEY (gms_usr_id) REFERENCES usr_user(usr_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
--  U SAVE UP — TARGET & DEPOSIT
-- =====================================================================
CREATE TABLE sav_saveup_target (
    sav_id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    sav_usr_id        INT UNSIGNED NOT NULL,
    sav_target_name   VARCHAR(100) NULL,
    sav_target_amount DECIMAL(12,2) NULL,
    sav_created_at    DATETIME NULL,
    PRIMARY KEY (sav_id),
    UNIQUE KEY uq_sav_usr (sav_usr_id),
    CONSTRAINT fk_sav_usr FOREIGN KEY (sav_usr_id) REFERENCES usr_user(usr_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE sav_saveup_deposit (
    dep_id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    dep_usr_id       INT UNSIGNED NOT NULL,
    dep_amount       DECIMAL(12,2) NOT NULL,
    dep_note         VARCHAR(255) NULL,
    dep_date         DATE NOT NULL,
    dep_status       ENUM('paid','pending','failed','manual') NOT NULL DEFAULT 'manual',
    dep_source       ENUM('manual','midtrans') NOT NULL DEFAULT 'manual',
    dep_order_id     VARCHAR(64) NULL,
    dep_payment_type VARCHAR(40) NULL,
    dep_paid_at      DATETIME NULL,
    dep_created_at   DATETIME NULL,
    PRIMARY KEY (dep_id),
    KEY idx_dep_usr (dep_usr_id),
    UNIQUE KEY uq_dep_order (dep_order_id),
    CONSTRAINT fk_dep_usr FOREIGN KEY (dep_usr_id) REFERENCES usr_user(usr_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Withdrawal (vault): user hanya bisa mengajukan tarik dana
-- kalau progres tabungan sudah >= 100% dari target.
CREATE TABLE wdr_saveup_withdrawal (
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

-- =====================================================================
--  POSITIVE QUEST
-- =====================================================================
CREATE TABLE qst_positive_quest (
    qst_id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    qst_title        VARCHAR(120) NOT NULL,
    qst_category     VARCHAR(40) NULL,
    qst_description  VARCHAR(255) NULL,
    qst_duration_min INT NULL,
    qst_xp_reward    INT NOT NULL DEFAULT 0,
    qst_is_active    TINYINT(1) NOT NULL DEFAULT 1,
    qst_created_at   DATETIME NULL,
    PRIMARY KEY (qst_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE qrp_quest_progress (
    qrp_id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    qrp_usr_id       INT UNSIGNED NOT NULL,
    qrp_qst_id       INT UNSIGNED NOT NULL,
    qrp_date         DATE NOT NULL,
    qrp_status       VARCHAR(20) NULL,
    qrp_completed_at DATETIME NULL,
    PRIMARY KEY (qrp_id),
    KEY idx_qrp_usr (qrp_usr_id),
    CONSTRAINT fk_qrp_usr FOREIGN KEY (qrp_usr_id) REFERENCES usr_user(usr_id) ON DELETE CASCADE,
    CONSTRAINT fk_qrp_qst FOREIGN KEY (qrp_qst_id) REFERENCES qst_positive_quest(qst_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
--  MICROLEARNING
-- =====================================================================
CREATE TABLE lrn_microlearning_module (
    lrn_id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    lrn_day_number INT NULL,
    lrn_title      VARCHAR(120) NOT NULL,
    lrn_category   VARCHAR(40) NULL,
    lrn_content    TEXT NULL,
    lrn_xp_reward  INT NOT NULL DEFAULT 0,
    lrn_is_active  TINYINT(1) NOT NULL DEFAULT 1,
    lrn_created_at DATETIME NULL,
    PRIMARY KEY (lrn_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE lrp_learning_progress (
    lrp_id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    lrp_usr_id       INT UNSIGNED NOT NULL,
    lrp_lrn_id       INT UNSIGNED NOT NULL,
    lrp_status       VARCHAR(20) NULL,
    lrp_completed_at DATETIME NULL,
    PRIMARY KEY (lrp_id),
    KEY idx_lrp_usr (lrp_usr_id),
    CONSTRAINT fk_lrp_usr FOREIGN KEY (lrp_usr_id) REFERENCES usr_user(usr_id) ON DELETE CASCADE,
    CONSTRAINT fk_lrp_lrn FOREIGN KEY (lrp_lrn_id) REFERENCES lrn_microlearning_module(lrn_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
--  [BARU] KONTEN RELIGIUS (multi-agama)
-- =====================================================================
CREATE TABLE rel_religious_content (
    rel_id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    rel_religion_pref VARCHAR(20) NOT NULL DEFAULT 'umum',  -- umum/islam/kristen/katolik/hindu/buddha
    rel_category     VARCHAR(40) NULL,                      -- motivasi/pengingat/syukur
    rel_text         VARCHAR(500) NOT NULL,
    rel_source       VARCHAR(120) NULL,
    rel_is_active    TINYINT(1) NOT NULL DEFAULT 1,
    rel_created_at   DATETIME NULL,
    PRIMARY KEY (rel_id),
    KEY idx_rel_religion (rel_religion_pref)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
--  [BARU] JOURNALING HARIAN
-- =====================================================================
CREATE TABLE jrn_journal (
    jrn_id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    jrn_usr_id      INT UNSIGNED NOT NULL,
    jrn_date        DATE NOT NULL,
    jrn_prompt      VARCHAR(255) NULL,
    jrn_answer_text TEXT NULL,
    jrn_mood_ref    VARCHAR(10) NULL,
    jrn_created_at  DATETIME NULL,
    PRIMARY KEY (jrn_id),
    UNIQUE KEY uq_jrn_usr_date (jrn_usr_id, jrn_date),
    CONSTRAINT fk_jrn_usr FOREIGN KEY (jrn_usr_id) REFERENCES usr_user(usr_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================================
--  [BARU] BADGE (definisi) & USER_BADGE (perolehan)
-- =====================================================================
CREATE TABLE bdg_badge (
    bdg_id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    bdg_code           VARCHAR(40) NOT NULL,
    bdg_name           VARCHAR(80) NOT NULL,
    bdg_description    VARCHAR(200) NULL,
    bdg_icon           VARCHAR(16) NULL,          -- emoji
    bdg_condition_type VARCHAR(30) NOT NULL,      -- total_xp/green_streak/checkin_count/learning_done/quest_done/journal_count
    bdg_condition_value INT NOT NULL DEFAULT 0,
    bdg_is_active      TINYINT(1) NOT NULL DEFAULT 1,
    bdg_created_at     DATETIME NULL,
    PRIMARY KEY (bdg_id),
    UNIQUE KEY uq_bdg_code (bdg_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ubd_user_badge (
    ubd_id        INT UNSIGNED NOT NULL AUTO_INCREMENT,
    ubd_usr_id    INT UNSIGNED NOT NULL,
    ubd_bdg_id    INT UNSIGNED NOT NULL,
    ubd_earned_at DATETIME NULL,
    PRIMARY KEY (ubd_id),
    UNIQUE KEY uq_ubd_usr_bdg (ubd_usr_id, ubd_bdg_id),
    CONSTRAINT fk_ubd_usr FOREIGN KEY (ubd_usr_id) REFERENCES usr_user(usr_id) ON DELETE CASCADE,
    CONSTRAINT fk_ubd_bdg FOREIGN KEY (ubd_bdg_id) REFERENCES bdg_badge(bdg_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================================
--  SEED DATA
-- =====================================================================

-- Admin (password: admin123)
INSERT INTO adm_admin (adm_email, adm_password_hash, adm_name) VALUES
('admin@riseup.test', '$2y$10$SXvEW2eN43cgWR3yyRYEmuLCnXAyaBtIdzqitUpGom4g9ae5kRXTS', 'Admin RiseUp');

-- Demo user (password: user123)
INSERT INTO usr_user (usr_id, usr_email, usr_password_hash, usr_status) VALUES
(1, 'demo@riseup.test', '$2y$10$.q6NnFe9i7KCl0.XU4GvZegxtj4PB29RzK5Yjv9/cExwG2zLH5lQK', 'active');

INSERT INTO prf_user_profile (prf_usr_id, prf_full_name, prf_age_years, prf_religion_pref, prf_updated_at) VALUES
(1, 'Demo User', 20, 'islam', NOW());

INSERT INTO bas_onboarding_baseline
(bas_usr_id, bas_exposure_duration, bas_main_reason, bas_target_goal, bas_daily_duration, bas_est_loss_monthly, bas_est_income_monthly, bas_risk_hour_start, bas_risk_hour_end, bas_created_at) VALUES
(1, '6-12m', 'stres', 'stop', '1-2h', 1500000.00, 3000000.00, '20:00:00', '23:00:00', NOW());

INSERT INTO gms_gamification_stat (gms_usr_id, gms_total_xp, gms_level_num, gms_weekly_xp, gms_updated_at) VALUES
(1, 120, 2, 60, NOW());

-- contoh check-in: hari ini hijau, kemarin merah (biar dashboard & admin ada isinya)
INSERT INTO chk_daily_checkin
(chk_usr_id, chk_date, chk_mood, chk_urge_level, chk_trigger, chk_status_color, chk_relapse_reason, chk_note_text, chk_created_at) VALUES
(1, CURDATE(),                    'baik',  1, 'iklan',    'green', NULL,    'Hari ini kuat menahan diri.', NOW()),
(1, DATE_SUB(CURDATE(),INTERVAL 1 DAY), 'stres', 4, 'sepibosan','red',  'bosan', 'Sempat tergoda saat bosan.',  NOW());

-- Microlearning path
INSERT INTO lrn_microlearning_module (lrn_day_number, lrn_title, lrn_category, lrn_content, lrn_xp_reward, lrn_is_active, lrn_created_at) VALUES
(1, 'Mengenali Pola Dorongan', 'awareness', 'Dorongan berjudi biasanya muncul pada waktu dan situasi tertentu. Kenali kapan dorongan itu paling sering datang agar kamu bisa bersiap.', 15, 1, NOW()),
(2, 'Jebakan "Hampir Menang"', 'awareness', 'Rasa "hampir menang" dirancang untuk membuatmu terus bermain. Menyadari ini membantumu berhenti lebih awal.', 15, 1, NOW()),
(3, 'Teknik Menunda 10 Menit', 'coping', 'Saat dorongan muncul, tunda keputusan selama 10 menit dan lakukan aktivitas lain. Dorongan sering mereda dengan sendirinya.', 20, 1, NOW()),
(4, 'Menghitung Biaya Nyata', 'finansial', 'Hitung total uang dan waktu yang hilang selama sebulan terakhir. Angka nyata sering menjadi motivasi kuat untuk berhenti.', 20, 1, NOW());

-- Positive Quest
INSERT INTO qst_positive_quest (qst_title, qst_category, qst_description, qst_duration_min, qst_xp_reward, qst_is_active, qst_created_at) VALUES
('Jalan kaki 10 menit', 'fisik', 'Alihkan dorongan dengan gerak ringan selama 10 menit.', 10, 10, 1, NOW()),
('Tarik napas 4-7-8', 'relaksasi', 'Latihan pernapasan untuk menenangkan diri saat urge tinggi.', 5, 10, 1, NOW()),
('Hubungi 1 teman', 'sosial', 'Kirim pesan ke orang yang kamu percaya, ceritakan kondisimu.', 10, 15, 1, NOW()),
('Tulis 3 hal disyukuri', 'refleksi', 'Catat tiga hal baik hari ini untuk menggeser fokus.', 5, 10, 1, NOW());

-- Konten Religius (multi-agama, nilai moral universal — teks orisinal)
INSERT INTO rel_religious_content (rel_religion_pref, rel_category, rel_text, rel_source, rel_is_active, rel_created_at) VALUES
('umum',    'motivasi', 'Setiap langkah kecil menjauh dari judi adalah kemenangan yang layak kamu syukuri.', 'Refleksi Universal', 1, NOW()),
('umum',    'motivasi', 'Hari ini kamu punya kesempatan baru untuk memilih yang lebih baik bagi dirimu.', 'Refleksi Universal', 1, NOW()),
('umum',    'pengingat','Kekuatan sejati bukan tidak pernah tergoda, tapi memilih berhenti saat tergoda.', 'Refleksi Universal', 1, NOW()),
('umum',    'syukur',   'Menjaga diri dari kerugian adalah bentuk menghargai masa depanmu sendiri.', 'Refleksi Universal', 1, NOW()),
('islam',   'motivasi', 'Menahan diri dari yang merugikan adalah bagian dari menjaga amanah atas dirimu.', 'Nilai Kebaikan', 1, NOW()),
('islam',   'pengingat','Rezeki yang berkah datang dari usaha yang halal dan hati yang tenang.', 'Nilai Kebaikan', 1, NOW()),
('islam',   'syukur',   'Bersabar hari ini menanam ketenangan untuk hari esok.', 'Nilai Kebaikan', 1, NOW()),
('kristen', 'motivasi', 'Setiap hari adalah kesempatan untuk memulai kembali dengan hati yang baru.', 'Nilai Pengharapan', 1, NOW()),
('kristen', 'pengingat','Ketekunan menumbuhkan karakter, dan karakter menumbuhkan pengharapan.', 'Nilai Pengharapan', 1, NOW()),
('katolik', 'motivasi', 'Pengharapan memberi kekuatan untuk bangkit setiap kali terjatuh.', 'Nilai Pengharapan', 1, NOW()),
('hindu',   'motivasi', 'Perbuatan baik hari ini menanam benih ketenangan bagi langkahmu berikutnya.', 'Nilai Kebajikan', 1, NOW()),
('buddha',  'motivasi', 'Melepaskan keinginan yang merugikan adalah jalan menuju ketenangan batin.', 'Nilai Kebajikan', 1, NOW());

-- Badge (definisi pencapaian)
INSERT INTO bdg_badge (bdg_code, bdg_name, bdg_description, bdg_icon, bdg_condition_type, bdg_condition_value, bdg_is_active, bdg_created_at) VALUES
('first_step',   'Langkah Pertama',   'Menyelesaikan check-in pertamamu.',            '🌱', 'checkin_count',  1,   1, NOW()),
('streak_3',     'Konsisten 3 Hari',  'Mencapai 3 hari hijau beruntun.',              '🔥', 'green_streak',   3,   1, NOW()),
('streak_7',     'Seminggu Bersih',   'Mencapai 7 hari hijau beruntun.',              '🏆', 'green_streak',   7,   1, NOW()),
('learner',      'Pembelajar',        'Menyelesaikan 3 materi microlearning.',        '📚', 'learning_done',  3,   1, NOW()),
('activist',     'Aktivis Positif',   'Menyelesaikan 5 positive quest.',              '⚡', 'quest_done',     5,   1, NOW()),
('reflective',   'Reflektif',         'Menulis 3 jurnal harian.',                     '✍️', 'journal_count',  3,   1, NOW()),
('xp_100',       'Pengumpul XP',      'Mengumpulkan total 100 XP.',                   '⭐', 'total_xp',       100, 1, NOW()),
('xp_300',       'Juara XP',          'Mengumpulkan total 300 XP.',                   '🌟', 'total_xp',       300, 1, NOW());

-- =====================================================================
--  Selesai. DB siap dipakai.
-- =====================================================================
