# DropTaxi backend

Licensed DropTaxi 2.3.0 backend source for the private `Ertoba/taxi-backend` repository.

The source is mapped exactly from the purchased package's `server` directory. The official vendor PDF is preserved at `docs/vendor/droptaxi-documentation230.pdf`.

## Safety boundary

This service is independent from the existing MILI/6amMart delivery platform. Production deployment must use its own application directory, database, Redis instance/namespace, PHP/Apache runtime, storage volumes, cron service, and Nginx virtual hosts. It must not reuse or mutate MILI's database, PHP-FPM pool, Supervisor configuration, application directory, or Nginx site.

## Runtime secrets

Do not commit any of the following:

- Firebase service-account credentials;
- Google OAuth credentials;
- database passwords or database dumps;
- purchase codes or any separate secret supplied during activation;
- API, payment, SMS, email, or signing secrets.

The purchased `drop-files/lib/license.php`, activation files, empty credential files, and default settings files are tracked exactly as supplied. They are not modified or bypassed. The `*.example.*` files are non-secret deployment references; all real credentials and activated payment/Maps/Firebase/SMTP values exist only in the separate writable production runtime. Vendor activation is performed only through the official activation flow.

## Current status

The purchased source has been imported and audited for repository safety. Production activation, service credentials, Georgian configuration, and final mobile builds are intentionally performed in later stages.
