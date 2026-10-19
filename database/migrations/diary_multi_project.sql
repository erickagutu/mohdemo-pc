-- Diary multi-project link table.
-- Allows a diary_events row to be attached to many projects, and a project to
-- many diary entries. The legacy diary_events.project_id column is kept for
-- backwards compatibility (it mirrors the first linked project) but the link
-- table is now the source of truth.
--
-- Idempotent: safe to run more than once.

CREATE TABLE IF NOT EXISTS `diary_event_projects` (
  `diary_event_id` INT NOT NULL,
  `project_id`     INT NOT NULL,
  PRIMARY KEY (`diary_event_id`, `project_id`),
  KEY `idx_dep_project` (`project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Backfill the link table from the legacy single project_id column, once.
INSERT IGNORE INTO `diary_event_projects` (`diary_event_id`, `project_id`)
SELECT `id`, `project_id` FROM `diary_events`
WHERE `project_id` IS NOT NULL AND `project_id` > 0;
