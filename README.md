# RideOn Taxi backend

RideOn Taxi v1.3 Laravel administration panel and API, with the supplied landing page in `landing/` and the isolated production deployment files in `deploy/`.

Production domains:

- `https://taxi.mili.ge` — landing page
- `https://taxi-admin.mili.ge` — administration panel
- `https://taxi-dispatch.mili.ge` — operations/dispatch access to the RideOn panel
- `https://taxi-api.mili.ge` — mobile API host

Runtime secrets (`.env`, Firebase service-account JSON, database passwords) are intentionally excluded from Git.

The supplied activation and purchase-validation files are preserved without modification and are explicitly excluded from Git line-ending normalization in `.gitattributes`.
