# TokoHPX — SaaS POS Toko HP

SaaS Point of Sales multi-tenant untuk toko HP: kasir dengan scan IMEI via kamera,
anti-kecurangan kasir (shift + modal laci), aging alert stok mengendap, dan pengingat upsell.

Versi **1.0 (Final)**.

## Stack
Laravel 13 · Octane (FrankenPHP) · MySQL 8 · Redis 7 · Tailwind CSS 3.4 · Alpine.js · Nginx · Docker Compose

## Menjalankan
```sh
docker compose up -d --build
docker exec tokohpx_app php artisan migrate
docker exec tokohpx_app php artisan db:seed   # paket Free/Pro + superadmin
# buka http://localhost:8091
```

Override environment (default production):
```sh
APP_ENV=local APP_DEBUG=true docker compose up -d app
APP_PORT=9000 docker compose up -d
```

## Akun awal
- Superadmin: `admin@saas.com` / `password`
- Daftar sendiri untuk akun tenant.

## Catatan kamera
Browser hanya memberi akses kamera pada `https://` atau `localhost`. Untuk uji dari HP lewat jaringan lokal:

```sh
cloudflared tunnel --url http://localhost:8091
```

Panduan lengkap per role: menu **Panduan** di dalam aplikasi.

## Struktur
- `app/Http/Controllers/{Superadmin,Tenant}` — modul per role
- `resources/views/{superadmin,tenant,layouts,auth}` — UI Blade + Alpine
- `docker/entrypoint.sh` — cache warm-up & tunggu DB saat start
- `.ai-project-memory.md` — catatan teknis, jebakan, dan status per slice
