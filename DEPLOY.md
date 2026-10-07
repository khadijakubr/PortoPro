# Deploy PortoPro ke Render (Docker + MySQL Cloud + Cloudinary)

Semua perubahan kode sudah siap. Ikuti langkah ini berurutan.

## 1. Siapkan database MySQL gratis (pilih satu)

| Pilihan | Gratis | Catatan koneksi |
|---|---|---|
| **TiDB Cloud Serverless** (disarankan) | ±5GB, tanpa kartu kredit | Port `4000`, **wajib SSL** |
| PlanetScale | free tier kecil | Port `3306`, wajib SSL |
| Clever Cloud MySQL | kecil | Port `3306`, wajib SSL |

1. Buat database bernama `PortoPro`.
2. Import file `PortoPro.sql` (struktur + data awal) via SQL editor provider.
3. Jalankan `db/migrate_cloudinary.sql` (tambah kolom `thumbnail_public_id`).
4. Catat: **Host, Port, User, Password, Database**.

> Password admin di `PortoPro.sql` adalah hash lama yang tidak diketahui
> plaintext-nya. Reset setelah import:
> ```bash
> php -r "echo password_hash('PASSWORD-BARU-KAMU', PASSWORD_DEFAULT), PHP_EOL;"
> ```
> lalu di SQL editor:
> ```sql
> UPDATE admin SET password = '<hasil-hash-di-atas>' WHERE username = 'admin';
> ```

## 2. Siapkan Cloudinary gratis (dashboard.cloudinary.com)

1. Daftar (tanpa kartu kredit) → dapat **Cloud Name, API Key, API Secret**.
2. Buat folder `portopro/uploads` (otomatis dibuat saat upload pertama juga).
3. Foto lama di `media/image/uploads/` ikut ter-copy ke Docker image jadi
   tetap tampil; upload **baru** otomatis ke Cloudinary.

## 3. Deploy di Render

1. Push repo ini ke GitHub (pastikan `.env` **tidak** ikut — sudah di-ignore).
2. Render → **New → Blueprint** → pilih repo → Render membaca `render.yaml`.
3. Isi Environment Variables (`sync: false`):
   `DB_HOST`, `DB_PORT` (`4000` TiDB / `3306` lainnya), `DB_USER`,
   `DB_PASS`, `DB_NAME=PortoPro`, `DB_SSL=true`,
   `CLOUDINARY_CLOUD_NAME`, `CLOUDINARY_API_KEY`, `CLOUDINARY_API_SECRET`.
4. Deploy → tunggu status **Live**. Cek `https://<app>.onrender.com/health.php`
   harus `OK`.

## 4. Tes production

- [ ] Home, Projects, detail project (gambar lama tampil).
- [ ] Contact form terkirim & tersimpan.
- [ ] Login admin → Add Project (gambar masuk Cloudinary) → Update → Delete.
- [ ] Logout jalan, session tidak error.

## 5. Jalan lokal (XAMPP / Docker)

**XAMPP/MAMP:** tanpa setting apa pun tetap jalan (fallback
`localhost/root/root/PortoPro`, upload ke `media/image/uploads/`).

**Docker (mirip Render):**
```bash
cp .env.example .env   # isi sesuai lokal/cloud
docker build -t portopro .
docker run -p 8000:10000 --env-file .env portopro
# buka http://localhost:8000
```

## File yang berubah & kenapa (ringkas)

| File | Sebelum → Sesudah | Alasan |
|---|---|---|
| `connection.php` | hardcode → `getenv()` + SSL + cek error | kredensial beda per env; DB cloud wajib SSL |
| `bootstrap.php` (baru) | — | 1 pintu session aman + `e()`/`e_text()` |
| `navigation.php`, `admin_logout.php` | `session_start()` ganda → via bootstrap | cegah warning + cookie secure di HTTPS |
| `lib/cloudinary.php` (baru) | — | upload CDN, fallback lokal dev |
| `add/update/delete_project.php` | lokal + SQLi → Cloudinary + prepared | disk Render ephemeral; anti-SQLi |
| `project/detail/contact/login/category/clients` | query interpolasi + echo mentah → prepared + escape | anti-SQLi/XSS |
| `Dockerfile`, `docker/*`, `render.yaml`, `health.php`, `.htaccess` | — | syarat deploy Render (`$PORT`, healthcheck) |
| `media/Image/` → `media/image/` | huruf besar → kecil | Linux case-sensitive (404 di Docker) |
| `db/migrate_cloudinary.sql` | — | kolom `thumbnail_public_id` |
| `admin_login.php` | tanpa `exit`, footer dobel → `exit` + tanpa footer | redirect bocor; footer 2x di `index.php` |

## Struktur folder (Opsi A)

```
config/    connection.php, bootstrap.php
includes/  navigation.php, footer.php
pages/     home, project, contact, detail_project, list_clients
admin/     login/logout, add/update/delete project & category
lib/ db/ docker/ media/  (tidak berubah)
```

Semua include memakai path `__DIR__`-eksplisit, jadi aman dipindah.
`Dockerfile`/`render.yaml`/`health.php` tetap di root.
