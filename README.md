# Mobile Repair Shop (DBMS mini project)

PHP + MySQL + Docker. Register repairs, track status, search/export the report.

## Run
```bash
docker compose down -v      # only needed once if you had the old database
docker compose up --build -d
```

- App deployed : https://mobilerepair-production-09dc0ad6.up.railway.app/repair.php
-                https://mobilerepair-production-09dc0ad6.up.railway.app/report.php
- App: http://localhost:8080/repair.php
- Report: http://localhost:8080/report.php
- phpMyAdmin: http://localhost:8081 (root / root123)

## Structure
```
src/
  repair.php   registration form
  report.php   report table (search, sort, paginate, view, delete, export)
  edit.php     edit a repair / change status
  db.php       connection + helpers (CSRF, flash messages)
  includes/    header.php, footer.php
  assets/      style.css, app.js
```
