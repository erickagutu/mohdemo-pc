-- =====================================================================
--  Performance Monitoring Application
--  Database schema (MySQL 5.7+ / 8.0+)
-- =====================================================================

CREATE DATABASE IF NOT EXISTS perfmon
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE perfmon;

-- ---------------------------------------------------------------------
--  Projects
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS projects (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(200) NOT NULL,
  code          VARCHAR(50)  NOT NULL UNIQUE,
  description   TEXT NULL,
  start_date    DATE NULL,
  end_date      DATE NULL,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
--  Performance Contract KPIs
--  Each KPI has 4 quarters (Q1-Q4) with a target & an achievement level
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS kpis (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  project_id    INT NOT NULL,
  kpi_name      VARCHAR(255) NOT NULL,
  sub_kpi_name  VARCHAR(255) NULL,
  weight        DECIMAL(5,2) DEFAULT 0.00,
  unit          VARCHAR(50)  NULL,
  q1_target     DECIMAL(12,2) DEFAULT 0.00,
  q1_achieved   DECIMAL(12,2) DEFAULT 0.00,
  q2_target     DECIMAL(12,2) DEFAULT 0.00,
  q2_achieved   DECIMAL(12,2) DEFAULT 0.00,
  q3_target     DECIMAL(12,2) DEFAULT 0.00,
  q3_achieved   DECIMAL(12,2) DEFAULT 0.00,
  q4_target     DECIMAL(12,2) DEFAULT 0.00,
  q4_achieved   DECIMAL(12,2) DEFAULT 0.00,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_kpis_project
    FOREIGN KEY (project_id) REFERENCES projects(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX idx_kpis_project ON kpis(project_id);

-- ---------------------------------------------------------------------
--  Diary / Meetings
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS meetings (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  title         VARCHAR(255) NOT NULL,
  meeting_date  DATE NOT NULL,
  start_time    TIME NULL,
  end_time      TIME NULL,
  location      VARCHAR(200) NULL,
  attendees     TEXT NULL,
  agenda        TEXT NULL,
  minutes       TEXT NULL,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
--  Meeting <-> Project (many-to-many)
--  Allows attaching more than one project to a single meeting
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS meeting_projects (
  meeting_id    INT NOT NULL,
  project_id    INT NOT NULL,
  PRIMARY KEY (meeting_id, project_id),
  CONSTRAINT fk_mp_meeting
    FOREIGN KEY (meeting_id) REFERENCES meetings(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_mp_project
    FOREIGN KEY (project_id) REFERENCES projects(id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX idx_mp_meeting ON meeting_projects(meeting_id);
CREATE INDEX idx_mp_project ON meeting_projects(project_id);

-- ---------------------------------------------------------------------
--  Seed data
-- ---------------------------------------------------------------------
INSERT INTO projects (name, code, description, start_date, end_date) VALUES
('Road Infrastructure Upgrade', 'RIU-01', 'Rehabilitation of trunk roads and drainage', '2026-01-01', '2026-12-31'),
('Rural Electrification',       'RE-02',  'Last-mile power connections in rural wards',  '2026-02-01', '2026-11-30'),
('Clean Water Initiative',      'CWI-03', 'Boreholes and piped water schemes',           '2026-01-15', '2026-12-15');

INSERT INTO kpis (project_id, kpi_name, sub_kpi_name, weight, unit,
                  q1_target, q1_achieved,
                  q2_target, q2_achieved,
                  q3_target, q3_achieved,
                  q4_target, q4_achieved) VALUES
(1,'Km of road rehabilitated','Trunk A section', 30, 'km',     5, 4.5,  10, 9.2,  15, 14.0, 25, 0),
(1,'Drainage built','Concrete channels',         15, 'm',    200,180,   500,470,  800,720, 1200,0),
(2,'Households connected','New connections',      25, 'no',   120,110,   300,280,  600,520, 1000,0),
(2,'Transformer installed','11kV units',          10, 'no',     2,  2,     5,  4,    8,  7,   14,0),
(3,'Boreholes drilled','Solar boreholes',         12, 'no',     4,  4,     8,  7,   14, 11,   20,0),
(3,'People reached','Beneficiaries',               8, 'no',  2000,1800, 5000,4200,9000,7000,15000,0);

INSERT INTO meetings (title, meeting_date, start_time, end_time, location, attendees, agenda, minutes) VALUES
('Q1 Performance Review', '2026-03-15', '09:00:00', '11:00:00', 'Boardroom A',
 'PC, EO, PMs',
 'Review Q1 deliverables; approve Q2 plan.',
 'Q1 targets largely met; RIU trailing on drainage.'),
('Stakeholder Alignment', '2026-05-20', '14:00:00', '15:30:00', 'Conference Hall',
 'County team, donors',
 'Align donor disbursement with milestones.',
 'Donor agreed to release tranche 2 after Q2 verification.');

-- link seed meeting -> projects (multi-project attachment)
INSERT INTO meeting_projects (meeting_id, project_id) VALUES
(1,1),(1,2),(1,3),
(2,2),(2,3);
