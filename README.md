# Performance Monitoring App (PHP / MySQL)

A small self-contained PHP/MySQL application for tracking performance contracts
(KPIs and quarterly achievement) and stakeholder meetings.

## Stack
- PHP 7.4+ (8.x recommended) with PDO
- MySQL 5.7+ / 8.0+

## Project layout
```
.
├── index.php              # Dashboard
├── config/db.php          # PDO connection + helpers (edit credentials here)
├── includes/layout.php    # Shared header/footer
├── assets/styles.css      # Stylesheet
├── modules/
│   ├── pc.php             # Performance Contract: KPI table + toggleable Gantt chart
│   ├── diary.php          # Meeting diary with multi-project attachment + edit/delete
│   └── projects.php       # Manage projects referenced by contracts and meetings
└── database/schema.sql    # Schema + seed data
```

## Deployment
1. Copy the whole folder to your PHP server (e.g. under `htdocs/` or `/var/www/html/`).
2. Create the database and load the schema:
   ```bash
   mysql -u root -p < database/schema.sql
   ```
   This creates the `perfmon` database, all tables, and inserts sample data.
3. Edit `config/db.php` and set your credentials:
   ```php
   define('DB_HOST', '127.0.0.1');
   define('DB_NAME', 'perfmon');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```
4. Open the app in a browser, e.g. `http://localhost/`.

## Features

### Performance Contract (`modules/pc.php`)
- **KPI table at the top** (`Performance Contract — KPIs and Sub-KPIs`) with per-quarter
  target/achievement, progress bars, achievement-level badges and an overall percentage.
- **Gantt chart at the bottom** (`Performance Contract Gantt Chart — Quarterly Progression &
  Achievement Levels`) with striped per-quarter bands and achievement dots.
- The Gantt section is **hidden by default** and toggled by a **"VIEW GANTT CHART"** button
  (the label switches to "HIDE GANTT CHART" when expanded).
- Add / edit / delete KPIs per project from the same page.

### Diary (`modules/diary.php`)
- Each meeting can be **attached to one or more projects** (many-to-many via the
  `meeting_projects` table). Selection is made via chip-style checkboxes.
- **Edit** and **Delete** buttons on every meeting row.
- Records title, date, times, location, attendees, agenda and minutes.

### Projects (`modules/projects.php`)
- Full CRUD for the projects used by the Performance Contract and Diary pages.
