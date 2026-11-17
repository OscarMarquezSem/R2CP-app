-- ARIADNA · Estructura de la base de datos (MySQL 5.7+ / MariaDB 10.3+)

CREATE TABLE IF NOT EXISTS settings (
  k VARCHAR(64) NOT NULL PRIMARY KEY,
  v TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS offices (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  city VARCHAR(120) NULL,
  timezone VARCHAR(64) NOT NULL DEFAULT 'America/Cancun',
  workdays VARCHAR(20) NOT NULL DEFAULT '1,2,3,4,5',
  pin_enabled TINYINT(1) NOT NULL DEFAULT 0,
  training_weekday TINYINT NULL,
  oh_mode VARCHAR(20) NOT NULL DEFAULT 'open_house',
  remind_morning TINYINT(1) NOT NULL DEFAULT 1,
  remind_morning_hour TINYINT NOT NULL DEFAULT 9,
  remind_night TINYINT(1) NOT NULL DEFAULT 1,
  remind_night_hour TINYINT NOT NULL DEFAULT 20,
  last_report_date DATE NULL,
  active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS office_training_dates (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  office_id INT UNSIGNED NOT NULL,
  tdate DATE NOT NULL,
  UNIQUE KEY uq_office_date (office_id, tdate),
  CONSTRAINT fk_otd_office FOREIGN KEY (office_id) REFERENCES offices(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sesiones en la oficina: la oficina pone fecha y hora a cada tema del plan.
CREATE TABLE IF NOT EXISTS office_sessions (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  office_id INT UNSIGNED NOT NULL,
  activity_id INT UNSIGNED NOT NULL,
  sdate DATE NOT NULL,
  stime TIME NULL,
  who VARCHAR(150) NULL,
  place VARCHAR(150) NULL,
  repeat_weekly TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_office_act (office_id, activity_id),
  CONSTRAINT fk_os_office FOREIGN KEY (office_id) REFERENCES offices(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  office_id INT UNSIGNED NULL,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(190) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','director','gerente','team_leader') NOT NULL,
  active TINYINT(1) NOT NULL DEFAULT 1,
  last_login_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_email (email),
  CONSTRAINT fk_users_office FOREIGN KEY (office_id) REFERENCES offices(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS login_attempts (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  k VARCHAR(190) NOT NULL,
  at DATETIME NOT NULL,
  KEY idx_k_at (k, at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS advisors (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  office_id INT UNSIGNED NOT NULL,
  name VARCHAR(150) NOT NULL,
  phone VARCHAR(30) NOT NULL,
  email VARCHAR(190) NULL,
  start_date DATE NOT NULL,
  token CHAR(64) NOT NULL,
  pin_hash VARCHAR(255) NULL,
  workdays_override VARCHAR(20) NULL,
  training_date DATE NULL,
  training_manual TINYINT(1) NOT NULL DEFAULT 0,
  consent_at DATETIME NULL,
  consent_ip VARCHAR(45) NULL,
  oh_done TINYINT(1) NOT NULL DEFAULT 0,
  oh_date DATE NULL,
  oh_attendees INT NOT NULL DEFAULT 0,
  cap_precio TINYINT(1) NOT NULL DEFAULT 0,
  cap_docs TINYINT(1) NOT NULL DEFAULT 0,
  cap_disp TINYINT(1) NOT NULL DEFAULT 0,
  renta_closed TINYINT(1) NOT NULL DEFAULT 0,
  renta_date DATE NULL,
  status_color ENUM('verde','amarillo','rojo') NOT NULL DEFAULT 'verde',
  status_reason VARCHAR(255) NULL,
  closed_weeks TINYINT NOT NULL DEFAULT 0,
  last_morning_reminder DATE NULL,
  last_night_reminder DATE NULL,
  decision ENUM('pendiente','continua','no_continua') NULL,
  decision_note VARCHAR(500) NULL,
  decision_by INT UNSIGNED NULL,
  decision_at DATETIME NULL,
  active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_token (token),
  KEY idx_office (office_id),
  CONSTRAINT fk_adv_office FOREIGN KEY (office_id) REFERENCES offices(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Metas propias del asesor por semana (contactos al día; lo demás por semana).
CREATE TABLE IF NOT EXISTS advisor_goals (
  advisor_id INT UNSIGNED NOT NULL,
  week_no TINYINT NOT NULL,
  contactos INT NOT NULL DEFAULT 0,
  tratos INT NOT NULL DEFAULT 0,
  analizadas INT NOT NULL DEFAULT 0,
  promovidas INT NOT NULL DEFAULT 0,
  citas INT NOT NULL DEFAULT 0,
  captaciones INT NOT NULL DEFAULT 0,
  low_accepted TINYINT(1) NOT NULL DEFAULT 0,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (advisor_id, week_no),
  CONSTRAINT fk_goal_adv FOREIGN KEY (advisor_id) REFERENCES advisors(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS push_subscriptions (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  advisor_id INT UNSIGNED NOT NULL,
  endpoint VARCHAR(600) NOT NULL,
  endpoint_hash CHAR(64) NOT NULL,
  p256dh VARCHAR(200) NOT NULL,
  auth VARCHAR(100) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_endpoint (endpoint_hash),
  CONSTRAINT fk_push_adv FOREIGN KEY (advisor_id) REFERENCES advisors(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS plan_weeks (
  week_no TINYINT NOT NULL PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  objective TEXT NULL,
  material TEXT NULL,
  remember TEXT NULL,
  review_questions TEXT NULL,
  routine_start_day TINYINT NOT NULL DEFAULT 1,
  meta_contactos INT NOT NULL DEFAULT 0,
  meta_tratos INT NOT NULL DEFAULT 0,
  meta_analizadas INT NOT NULL DEFAULT 0,
  meta_promovidas INT NOT NULL DEFAULT 0,
  meta_citas INT NOT NULL DEFAULT 0,
  meta_captaciones INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS plan_activities (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  week_no TINYINT NOT NULL,
  day_no TINYINT NOT NULL,
  sort INT NOT NULL DEFAULT 0,
  title VARCHAR(255) NOT NULL,
  guide TEXT NULL,
  minutes INT NULL,
  kind VARCHAR(12) NOT NULL DEFAULT 'check',
  video_url VARCHAR(500) NULL,
  help_links TEXT NULL,
  active TINYINT(1) NOT NULL DEFAULT 1,
  KEY idx_wd (week_no, day_no, sort)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS daily_logs (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  advisor_id INT UNSIGNED NOT NULL,
  log_date DATE NOT NULL,
  week_no TINYINT NOT NULL DEFAULT 0,
  day_type VARCHAR(12) NOT NULL DEFAULT 'plan',
  day_label VARCHAR(60) NULL,
  contactos INT NOT NULL DEFAULT 0,
  tratos INT NOT NULL DEFAULT 0,
  analizadas INT NOT NULL DEFAULT 0,
  promovidas INT NOT NULL DEFAULT 0,
  citas INT NOT NULL DEFAULT 0,
  captaciones INT NOT NULL DEFAULT 0,
  analisis TEXT NULL,
  note TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_adv_date (advisor_id, log_date),
  CONSTRAINT fk_log_adv FOREIGN KEY (advisor_id) REFERENCES advisors(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS log_items (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  log_id INT UNSIGNED NOT NULL,
  item_key VARCHAR(20) NOT NULL,
  done TINYINT(1) NOT NULL DEFAULT 0,
  result_text TEXT NULL,
  photo VARCHAR(120) NULL,
  UNIQUE KEY uq_log_item (log_id, item_key),
  CONSTRAINT fk_item_log FOREIGN KEY (log_id) REFERENCES daily_logs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS week_reviews (
  advisor_id INT UNSIGNED NOT NULL,
  week_no TINYINT NOT NULL,
  answers TEXT NULL,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (advisor_id, week_no),
  CONSTRAINT fk_rev_adv FOREIGN KEY (advisor_id) REFERENCES advisors(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Avisos que solo se envían una vez (metas bajas, temas sin fecha…).
CREATE TABLE IF NOT EXISTS notices (
  k VARCHAR(190) NOT NULL PRIMARY KEY,
  at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS email_log (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  kind VARCHAR(30) NOT NULL,
  office_id INT UNSIGNED NULL,
  advisor_id INT UNSIGNED NULL,
  recipients TEXT NULL,
  subject VARCHAR(255) NULL,
  status VARCHAR(20) NOT NULL,
  error TEXT NULL,
  created_at DATETIME NOT NULL,
  KEY idx_kind (kind, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
