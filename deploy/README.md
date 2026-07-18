# Isolated production stack

This deployment keeps DropTaxi separate from the existing MILI/6amMart application on the shared VPS.

## Runtime layout

- `/opt/droptaxi/repo` - clean release export of `Ertoba/taxi-backend`;
- `/opt/droptaxi/runtime/app` - writable runtime copy used by official activation;
- `/opt/droptaxi/secrets` - root-only database/Redis secret files;
- `127.0.0.1:9180` - Apache/PHP container, reachable only through the host Nginx;
- named Docker volumes - dedicated MySQL 8 and Redis 7.2 data.

The stack does not publish MySQL or Redis ports and does not reuse MILI's MariaDB, PHP-FPM, Supervisor, application files, or database.

The `dispatch` service is kept behind the `activated` Compose profile until official domain activation and admin configuration are complete. It runs the vendor's `public/cron.php` continuously with Docker restart supervision, matching the official one-minute cron watchdog's intended behavior.

Real secrets and activated runtime files are never committed. The purchased activation and license source files remain unchanged in Git; official activation operates only on the writable runtime copy.
