# 🚀 pemad-intl/main-api-service

Layanan API untuk berinteraksi dengan **Main API Service** Pemad International.

---

## 🛠️ Instalasi

### 1. Tambahkan ke Proyek

Gunakan Composer untuk menginstal paket:

```bash
composer require pemad-intl/main-api-service
````

### 2\. Publikasikan Konfigurasi

Jalankan perintah Artisan ini untuk mempublikasikan file konfigurasi `config/mainapi.php`:

```bash
php artisan vendor:publish --tag=mainapi-config
```

-----

## 🔑 Konfigurasi Variabel Lingkungan

Tambahkan variabel-variabel berikut ke file `.env` di root proyek Anda. Nilai-nilai ini akan digunakan oleh paket untuk mengautentikasi dan mengarahkan permintaan API.

```ini
MAIN_API_URL=[https://example.test](https://example.test)
MAIN_API_CODE=appcode
MAIN_API_SECRET=xxx
MAIN_API_KEY=vAWG...
```

-----

## 💡 Penggunaan (Usage)

Layanan dapat diakses dengan me-resolve dari container Laravel.

### Mengakses Service

```php
// Resolve via container:
$api = app(\Pemad\MainApi\MainApiService::class);
```

### Contoh Permintaan GET dan POST

Anda dapat menggunakan metode `$api->get()` atau `$api->post()` untuk berinteraksi dengan endpoint.

**Contoh dengan Opsi (Query Parameters atau Body):**

```php
// Permintaan GET dengan query parameter 'limit'
$response = $api->get('/api/user', ['limit' => 100]);

// Permintaan POST dengan data body 'empl'
$response = $api->post('/api/sync', ['empl' => 69]);
```

**Contoh dengan Custom Headers:**

Jika Anda perlu mengirimkan header tambahan (misalnya untuk format respon spesifik), kirimkan sebagai parameter ketiga.

```php
$response = $api->get('/api/jabatan', [], [
    'Accept' => 'application/json', // Mengirim header 'Accept'
]);
```

> **Catatan:** Header khusus yang sering digunakan seperti `Authorization` atau `Content-Type` biasanya sudah ditangani secara otomatis oleh *service*.

-----

## 🧪 Uji Coba (Testing)

Anda dapat menguji koneksi ke Main API Service menggunakan perintah Artisan yang telah disediakan.

```bash
php artisan mainapi:test /api/health
```

Perintah ini akan melakukan permintaan **GET** ke endpoint yang ditentukan menggunakan konfigurasi dari `.env` Anda.

```
---