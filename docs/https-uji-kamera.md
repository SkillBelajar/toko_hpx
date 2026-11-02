# HTTPS Publik untuk Tes Kamera — toko_hpx

Kamera (`navigator.mediaDevices.getUserMedia`) hanya jalan di **secure context**:
`https://` atau `localhost`. Karena itu perlu tunnel HTTPS untuk tes dari HP.

## cloudflared quick tunnel (dipakai)

```bash
cloudflared tunnel --url http://localhost:8091 --no-autoupdate
```

- URL muncul di log stdout, pola: `https://<acak>.trycloudflare.com`
- Sertifikat valid (Google Trust Services), bukan self-signed — HP tidak perlu install CA
- Gratis, tanpa akun Cloudflare
- URL berubah setiap kali dijalankan ulang

### Wajib setelah dapat URL

```bash
cd /home/mfahri/toko_hpx
sed -i 's|^APP_URL=.*|APP_URL=<URL_TUNNEL>|' .env
docker compose exec -T app php artisan config:clear
```

Tanpa ini Laravel generate URL/Laravel asset dengan `http://`, mixed-content diblokir browser.

### Matikan tunnel

Tunnel membuka aplikasi ke **internet publik**. Siapa pun dengan URL bisa membuka
halaman login. Untuk uji coba tidak apa-apa, tapi JANGAN dibiarkan menyala terus
kalau ada data transaksi nyata.

```bash
pkill cloudflared
```

## Tailscale (BELUM terpasang)

Script install butuh `sudo` password; tool memblokir piping password ke `sudo -S`.
Install manual di terminal sendiri:

```bash
curl -fsSL https://tailscale.com/install.sh | sh
sudo tailscale up
```

Setelah itu HP install app Tailscale, login akun sama, akses
`http://100.64.9.86:8091`. Aman antar perangkat, tidak publik — tapi **HTTP biasa**,
karena nginx hanya `listen 80`. Kamera tetap tidak akan jalan via Tailscale kecuali
ditambah HTTPS (magicDNS cert atau mkcert).

## Alternatif lain

- **mkcert** + `listen 443 ssl` di nginx: HTTPS LAN, perlu install CA di HP
- **ngrok**: mirip cloudflared, `ngrok http 8091`
- **localhost di HP lewat adb reverse** (Chrome DevTools): `adb reverse tcp:8091 tcp:8091`
  lalu buka `http://localhost:8091` di Chrome HP — sudah secure context, tanpa internet sama sekali.
  **Ini paling bersih kalau HP bisa konek USB ke PC.**

## Kredensial tes

Dibuat 2026-11-02:

| email | password | role | tenant_id |
|---|---|---|---|
| tes@toko.test | kamera123 | tenant | 1 |

User `perf@toko.test` (dari seeder) password-nya tidak diketahui — jangan pakai untuk tes manual.

## Verifikasi yang sudah lolos

- `GET /` → 200, TLS verify 0 (valid)
- `GET /privasi`, `/manifest.webmanifest` → 200
- `GET /js/imei-scanner.js` → 200 `application/javascript`
- Login `tes@toko.test` → 302 ke `/tenant/dashboard`
- Session cookie punya flag `secure; httponly; samesite=lax`
- `/tenant/pos` → 200 dengan `ImeiScanner` aktif, tanpa `html5-qrcode`
- `/tenant/inventory` → 200 dengan `ImeiScanner` aktif
