# Kopi Nusantara — Inventory & Sales Management System

**UTS Integrated Laravel Project — Web Programming**

Aplikasi web bisnis terintegrasi berbasis **Laravel 12** dan **MySQL** untuk mengelola master data inventaris, pemasok (*supplier*), transaksi pembelian stok (*Purchase / Stock-In*), dan transaksi penjualan kasir (*Sales / Stock-Out*) pada **Toko Kopi Nusantara**. Sistem ini dirancang dengan mengutamakan konsistensi stok otomatis, keamanan *Role-Based Access Control* di sisi server, serta transaksi database atomik (`DB::transaction` & `lockForUpdate`).

---

## 1. Project Overview

Usaha distribusi dan penjualan **Toko Kopi Nusantara** sebelumnya mengalami kendala akibat pencatatan manual:
- Jumlah stok tidak selalu sesuai dengan transaksi yang terjadi.
- Produk yang sudah habis masih dapat terjual (*overselling*).
- Riwayat penjualan dan pembelian sulit ditelusuri kembali.
- Pemilik (*Owner/Admin*) dan staf kasir/gudang (*Staff*) membutuhkan hak akses yang berbeda.
- Pemilik membutuhkan ringkasan penjualan dan peringatan stok rendah secara akurat.

Sistem ini dibangun secara berkesinambungan dari materi perkuliahan:
1. **Pertemuan 3 (Database Relational):** Perancangan skema database MySQL yang ternormalisasi ke dalam 8 tabel utama lengkap dengan *Primary Key*, *Foreign Key*, dan *Unique Constraint*.
2. **Pertemuan 4 (Transaksi Stok & Otorisasi):** Penerapan *Database Transaction* (`DB::transaction`) dengan penguncian baris (`lockForUpdate`), validasi pencegahan stok negatif, serta pembagian hak akses berbasis *role*.
3. **Pertemuan 5 (Laravel MVC & Eloquent ORM):** Penerapan arsitektur *Route -> Middleware -> Controller -> Validation -> Business Logic -> Eloquent Model -> MySQL -> Blade View* dengan antarmuka bertema *Minimalist Elegant Dark*.

---

## 2. Requirements

Spesifikasi perangkat lunak yang dibutuhkan untuk menjalankan sistem ini:
- **PHP** >= 8.2
- **Composer** (Dependency Manager for PHP)
- **MySQL / MariaDB** (Melalui Laragon / XAMPP)
- **Web Browser** Modern (Chrome / Edge / Brave / Firefox)
- *(Opsional)* **Node.js & NPM**

---

## 3. Installation (Tutorial Menjalankan Proyek)

Jalankan perintah berikut secara berurutan di terminal (*PowerShell* / *Git Bash* / *CMD*) untuk memasang proyek pada *environment* bersih (*reproducible setup*):

    git clone <repository-url>
    cd inventory-sales
    composer install
    cp .env.example .env
    php artisan key:generate

---

## 4. Environment Setup

1. Aktifkan layanan **Apache/Nginx** dan **MySQL** pada aplikasi **Laragon** (atau XAMPP).
2. Buka `http://localhost/phpmyadmin` dan buat database kosong baru dengan nama:

    inventory_sales_db

3. Buka file **`.env`** pada direktori utama proyek, lalu sesuaikan konfigurasi koneksi database menjadi seperti berikut:

    APP_NAME="Kopi Nusantara"
    APP_ENV=local
    APP_DEBUG=true
    APP_TIMEZONE=Asia/Jakarta
    APP_URL=http://127.0.0.1:8000

    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=inventory_sales_db
    DB_USERNAME=root
    DB_PASSWORD=

    SESSION_DRIVER=file

---

## 5. Migration & Seeding

Seluruh struktur tabel dan data uji (*dummy data* realistis Toko Kopi Nusantara) dibuat secara otomatis melalui fitur *Migration* dan *Seeder* Laravel. Jalankan perintah berikut di terminal:

    php artisan migrate:fresh --seed
    php artisan serve

Setelah server berjalan, buka browser dan kunjungi alamat:
**http://127.0.0.1:8000**

---

## 6. Demo Account

Berikut adalah akun simulasi (*dummy*) yang disiapkan oleh `DatabaseSeeder` untuk menguji pembatasan hak akses (*Role-Based Access Control*):

| Role | Email | Password | Hak Akses di Dalam Sistem |
| :--- | :--- | :--- | :--- |
| **Admin / Owner** | `admin@kopinusantara.test` | `password` | Mengelola pengguna & role, CRUD penuh pada Kategori, Produk, dan Supplier, melihat seluruh transaksi, membuat transaksi, **membatalkan transaksi (*Cancel & Restore Stock*)**, serta mengakses Dashboard laporan. |
| **Staff** | `staff@kopinusantara.test` | `password` | Melihat daftar & detail produk, melihat daftar stok rendah, membuat transaksi Pembelian (*Stock-In*) dan Penjualan (*Stock-Out*), serta melihat riwayat transaksi. **Dilarang** mengelola pengguna/role atau menghapus master data sensitif. |

---

## 7. Entity Relationship Diagram (ERD) & Penjelasan Relasi

### A. Diagram Skema Database (8 Tabel Utama)

    +-------------------+          +------------------------+          +-------------------+
    |    categories     |          |        products        |          |     suppliers     |
    +-------------------+          +------------------------+          +-------------------+
    | PK id             | 1      * | PK id                  |          | PK id             |
    |    name (unique)  |----------| FK category_id         |          |    name           |
    |    timestamps     |          |    sku (unique)        |          |    phone          |
    +-------------------+          |    name                |          |    email          |
                                   |    purchase_price      |          |    timestamps     |
                                   |    selling_price       |          +-------------------+
                                   |    stock               |                    | 1
                                   |    minimum_stock       |                    |
                                   |    timestamps          |                    |
                                   +------------------------+                    |
                                      | 1                | 1                     |
                                      |                  |                       |
                    +-----------------+                  +-------+               |
                    | *                                          | *             | *
         +---------------------+                      +---------------------+    |
         |      sale_items     |                      |   purchase_items    |    |
         +---------------------+                      +---------------------+    |
         | PK id               |                      | PK id               |    |
         | FK sale_id          |                      | FK purchase_id      |    |
         | FK product_id       |                      | FK product_id       |    |
         |    quantity         |                      |    quantity         |    |
         |    unit_price       |                      |    unit_cost        |    |
         |    subtotal         |                      |    subtotal         |    |
         |    timestamps       |                      |    timestamps       |    |
         +---------------------+                      +---------------------+    |
                    | *                                          | *             |
                    | 1                                          | 1             |
         +---------------------+                      +---------------------+    |
         |        sales        |                      |      purchases      |----+
         +---------------------+                      +---------------------+
         | PK id               |                      | PK id               |
         | FK user_id          |                      | FK supplier_id      |
         |    sale_number (UQ) |                      | FK user_id          |
         |    total            |                      |    purchase_no (UQ) |
         |    status           |                      |    total            |
         |    sold_at          |                      |    status           |
         |    timestamps       |                      |    purchased_at     |
         +---------------------+                      |    timestamps       |
                    | *                               +---------------------+
                    |                                            | *
                    |              +-------------------+         |
                    |              |       users       |         |
                    |              +-------------------+         |
                    +--------------| PK id             |---------+
                                 1 |    name           | 1
                                   |    email (unique) |
                                   |    password       |
                                   |    role           |
                                   |    timestamps     |
                                   +-------------------+

### B. Penjelasan Relasi Eloquent ORM
1. **`Category` hasMany `Product` / `Product` belongsTo `Category`:**
   Satu kategori dapat memiliki banyak produk kopi/alat seduh. *Foreign Key* `category_id` diletakkan pada tabel `products`.
2. **`Supplier` hasMany `Purchase` / `Purchase` belongsTo `Supplier`:**
   Satu pemasok dapat menyuplai banyak transaksi pembelian (*Purchase Order*). *Foreign Key* `supplier_id` berada pada tabel `purchases`.
3. **`Purchase` hasMany `PurchaseItem` / `PurchaseItem` belongsTo `Purchase`:**
   Satu nota pembelian (header) memuat satu atau banyak baris barang yang dibeli (detail). *Foreign Key* `purchase_id` berada pada tabel detail `purchase_items`.
4. **`Sale` hasMany `SaleItem` / `SaleItem` belongsTo `Sale`:**
   Satu nota penjualan (header) memuat satu atau banyak baris barang yang dibeli pelanggan (detail). *Foreign Key* `sale_id` berada pada tabel detail `sale_items`.
5. **`Product` hasMany `PurchaseItem` & `SaleItem`:**
   Satu produk dapat tercatat di banyak rincian transaksi pembelian maupun penjualan. Dilindungi dengan `restrictOnDelete()` agar produk yang sudah memiliki riwayat transaksi tidak dapat dihapus sembarangan.
6. **`User` hasMany `Purchase` & `Sale`:**
   Setiap transaksi pembelian dan penjualan mencatat `user_id` petugas (Admin/Staff) yang memproses transaksi tersebut sebagai jejak audit (*audit trail*).

---

## 8. Features & Business Rules yang Diterapkan

### A. Kelengkapan Modul & Halaman Antarmuka
- **Halaman Login (`/login`):** Form autentikasi bersih dengan validasi kredensial dan proteksi sesi.
- **Halaman Dashboard (`/dashboard`):** Menampilkan ringkasan metrik real-time dari database (Total Produk, Penjualan Hari Ini, Penjualan Bulan Ini, Total Transaksi, Peringatan Stok Rendah, dan 5 Penjualan Terakhir).
- **Halaman Master Produk (`/products`):** Daftar produk dengan fitur pencarian (*Search SKU/Nama*), filter kategori, filter status stok, *pagination* (`withQueryString()`), halaman tambah/edit produk (khusus Admin), dan halaman detail riwayat keluar-masuk per produk.
- **Halaman Khusus Stok Rendah (`/products/low-stock`):** Menampilkan seluruh produk yang stoknya berada pada atau di bawah batas minimum (`stock <= minimum_stock`).
- **Halaman Master Kategori (`/categories`) & Supplier (`/suppliers`):** Modul CRUD khusus Admin dengan validasi integritas relasi sebelum penghapusan.
- **Halaman Manajemen Pengguna & Role (`/users`):** Modul khusus Admin untuk menambah staf baru dan mengubah *role* pengguna (`admin` / `staff`).
- **Halaman Pembelian / Stock-In (`/purchases`):** Daftar PO dengan filter tanggal/status, form input pembelian multi-item dinamis, detail nota PO, dan tombol pembatalan PO.
- **Halaman Penjualan / Stock-Out (`/sales`):** Daftar invoice dengan filter tanggal/status, form kasir multi-item dinamis, detail invoice penjualan, dan tombol pembatalan penjualan.

### B. Jaminan Business Rules & Konsistensi Stok
1. **Unik & Valid:** Kode `sku` produk, `name` kategori, `email` pengguna, `purchase_number`, dan `sale_number` dijamin unik baik di level validasi Laravel maupun di level *unique index* database MySQL. Kuantitas transaksi wajib bernilai bulat positif (`>= 1`) dan harga tidak boleh negatif (`>= 0`).
2. **Pencegahan Stok Negatif & Overselling:** Pada transaksi penjualan (`SaleController@store`), sistem mengunci baris produk menggunakan `Product::lockForUpdate()` dan memeriksa `if ($product->stock <$qty)`. Jika stok tidak mencukupi, sistem melempar `ValidationException` dengan pesan jelas dan membatalkan transaksi.
3. **Perhitungan Total di Server (*Server-Side Calculation*):** Subtotal dan total harga penjualan dihitung langsung di server menggunakan harga jual resmi dari tabel `products` (`$product->selling_price * $qty`), sehingga aman dari manipulasi input di sisi browser.
4. **Atomicity dengan `DB::transaction()`:** Penyimpanan header transaksi, detail item, dan pembaruan stok dibungkus dalam satu blok `DB::transaction()`. Jika gagal pada salah satu item, seluruh perubahan di-*rollback* secara utuh.
5. **Aturan Pembatalan Transaksi (*Cancel & Stock Restoration*):** Transaksi yang sudah selesai tidak diedit sembarangan, melainkan hanya dapat dibatalkan oleh Admin melalui mekanisme *Cancel* yang mengembalikan stok produk secara konsisten di dalam `DB::transaction()`.
6. **Larangan Hard Delete Produk:** Produk yang sudah pernah digunakan dalam transaksi pembelian atau penjualan ditolak untuk dihapus permanen.

---

## 9. Test Cases & Bukti Hasil Pengujian

| No | Skenario Uji | Langkah Pengujian | Hasil yang Diharapkan | Status |
| :- | :--- | :--- | :--- | :---: |
| 1 | **Guest & Auth Protection** | Akses `/dashboard` tanpa login, lalu coba login dengan password salah. | Guest diarahkan ke `/login`; password salah ditolak dengan pesan error. | **PASS** |
| 2 | **Validasi SKU Duplikat & Harga Negatif** | Tambah produk baru dengan SKU `KOPI-ARB-001` atau harga `-5000`. | Ditolak oleh validasi server; input form sebelumnya tetap dipertahankan (`old()`). | **PASS** |
| 3 | **Purchase Transaction (Stock-In)** | Buat pembelian baru untuk 2 produk (masing-masing Qty: 10). | Nota PO tersimpan, total terhitung benar, dan stok kedua produk bertambah +10. | **PASS** |
| 4 | **Sales Transaction (Stock-Out)** | Buat penjualan untuk produk dengan stok tersedia 25 sebanyak Qty: 3. | Invoice tersimpan, total sesuai harga database, dan stok produk berkurang menjadi 22. | **PASS** |
| 5 | **Insufficient Stock & Atomic Rollback** | Buat penjualan 2 item sekaligus: Item 1 (Qty: 1, stok cukup) & Item 2 (Qty: 999, stok kurang). | Transaksi ditolak dengan pesan stok tidak cukup; stok Item 1 tetap utuh (di-*rollback*). | **PASS** |
| 6 | **Server-Side Role Authorization** | Login sebagai `staff@kopinusantara.test` lalu akses URL `/categories`, `/suppliers`, atau `/users`. | Ditolak oleh server dengan status **403 Forbidden (This action is unauthorized)**. | **PASS** |
| 7 | **Proteksi Hard Delete Produk** | Login sebagai Admin, lalu klik tombol Hapus pada produk yang sudah memiliki riwayat transaksi. | Penghapusan ditolak dengan pesan peringatan *Business Rule*. | **PASS** |
| 8 | **Search, Filter & Pagination** | Cari produk berdasarkan SKU dan filter transaksi berdasarkan tanggal & status. | Menampilkan data yang tepat dan mempertahankan filter saat berpindah halaman. | **PASS** |

---

## 10. Known Limitations

Sesuai dengan batasan ruang lingkup pada *Project Brief* UTS, sistem ini berfokus pada keakuratan *business logic* inventaris dan transaksi, sehingga:
- Belum terintegrasi dengan *Payment Gateway* pembayaran digital eksternal.
- Mengelola stok pada satu lokasi gudang utama (belum mencakup *multi-warehouse* kompleks).
- Tidak mencakup pembukuan akuntansi penuh (buku besar/neraca), *multi-currency*, atau peramalan berbasis AI.
- Perubahan angka stok secara langsung pada form Edit Produk sengaja dinonaktifkan agar setiap perubahan stok wajib tercatat melalui transaksi *Purchase (Stock-In)* atau *Sales (Stock-Out)*.


# PANDUAN MENJALANKAN DAN LOGIN APLIKASI
## Kopi Nusantara — Inventory & Sales Management System

Berikut adalah panduan lengkap langkah demi langkah untuk menjalankan aplikasi dan melakukan pengujian login ke dalam sistem:

---

### Langkah 1: Aktifkan Layanan di Laragon
1. Buka aplikasi **Laragon** di komputer Anda.
2. Klik tombol **Start All** untuk memastikan server web (**Apache/Nginx**) dan database server (**MySQL**) aktif berjalan.

---

### Langkah 2: Buka Folder Proyek di Terminal VS Code
1. Buka aplikasi **VS Code**.
2. Pastikan Anda telah membuka folder proyek:
   D:\Program\Laragon\www\inventory-sales
3. Buka terminal terintegrasi di VS Code dengan menekan tombol kombinasi **Ctrl + ~** (atau melalui menu atas: **Terminal -> New Terminal**).
4. Pastikan direktori terminal berada pada path:
   PS D:\Program\Laragon\www\inventory-sales>

---

### Langkah 3: Menjalankan Server Lokal Laravel
Ketik dan jalankan perintah berikut pada terminal:

    php artisan serve

Setelah dijalankan, terminal akan menampilkan pesan:
INFO  Server running on [http://127.0.0.1:8000].

*(Catatan: Jangan menutup jendela terminal ini selama Anda menguji atau menggunakan website).*

---

### Langkah 4: Membuka Aplikasi di Web Browser
1. Buka browser (Google Chrome, Microsoft Edge, Brave, dll.).
2. Masukkan alamat URL berikut pada bilah alamat (address bar), lalu tekan **Enter**:
   http://127.0.0.1:8000
3. Sistem secara otomatis akan menampilkan halaman **Login** dengan tema gelap minimalis.

---

### Langkah 5: Kredensial Login Sistem

Silakan gunakan akun demo sesuai peran yang ingin diuji:

#### 1. Login sebagai Admin / Owner (Akses Penuh)
- **Email:** admin@kopinusantara.test
- **Password:** password
- **Hak Akses:** Mengelola seluruh data master (Produk, Kategori, Supplier), mengelola Users & Role, memproses transaksi Purchase & Sales, membatalkan transaksi (Cancel & Restore Stock), serta memantau Dashboard ringkasan.

#### 2. Login sebagai Staff (Akses Terbatas Kasir & Gudang)
- **Email:** staff@kopinusantara.test
- **Password:** password
- **Hak Akses:** Melihat katalog produk dan daftar stok rendah, membuat transaksi pembelian (Stock-In) dan penjualan (Stock-Out). Menu data master dan pembatalan transaksi otomatis dibatasi oleh sistem.

---

### Langkah 6: Selesai
Setelah tombol **Login** ditekan, Anda akan diarahkan langsung ke halaman **Dashboard** Toko Kopi Nusantara dan dapat mulai menguji seluruh alur transaksi.
