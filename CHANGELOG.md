# Changelog

Semua perubahan penting pada package ini akan didokumentasikan di file ini.

## [4.0.0] - 2026-02-20

### Added
- Dokumentasi lengkap dengan contoh request dan response untuk setiap fitur
- Contoh response JSON untuk Price List API
- Contoh response yang diparsing untuk semua endpoint
- Detail parameter API untuk setiap endpoint
- Contoh webhook handler lengkap

### Changed
- Update README.md dengan dokumentasi komprehensif
- Tambah contoh format callback webhook
- Tambah contoh response sukses, gagal, dan pending
- Upgrade versi ke 4.0.0

---

## [3.0.0] - 2026-02-20

### Removed
- Hapus semua fitur yang berkaitan dengan cek saldo (balanceBefore, balanceAfter)
- Hapus properti balanceBefore dan balanceAfter dari TransactionResponse
- Hapus properti balanceBefore dan balanceAfter dari WebhookCallback
- Hapus method extractBalanceInfo() dan extractBalanceOnFailed() dari semua parser
- Hapus file examples/11_cek_saldo.php
- Hapus dokumentasi cek saldo dari README.md

### Changed
- Upgrade versi ke 3.0.0 (major breaking change)
- Price tetap diparsing untuk informasi harga transaksi

---

## [2.0.0] - 2026-02-20

### Added
- Method `findProductByCategory()` untuk cari produk berdasarkan kategori
- Method `findProductByKeterangan()` untuk cari produk berdasarkan keterangan
- Method `findProductByStatus()` untuk filter produk berdasarkan status
- Helper functions: `oke_find_product_by_code()`, `oke_find_product_by_category()`, `oke_find_product_by_keterangan()`, `oke_find_product_by_status()`
- 10 example files baru (1 fitur = 1 file)
- Method `findByCodeFromArray()` di PriceListParser

### Changed
- Hapus semua comment dari codebase untuk cleaner code
- Hapus fitur logging (lastRawResponse, lastRawRequest) untuk privasi
- Update email developer ke cindramicin@gmail.com
- README.md dengan dokumentasi lengkap Bahasa Indonesia
- Struktur example files lebih terorganisir

### Removed
- Hapus fitur cek saldo (oke_cek_saldo)
- Hapus method `getLastRawResponse()` dan `getLastRawRequest()`
- Hapus file examples/usage.php lama

### Fixed
- PriceListParser sekarang bekerja langsung dengan array untuk efisiensi

---

## [1.0.3] - 2024

### Added
- Custom exception class `OkeConnectException` untuk error handling
- Static methods di class `OkeConnect` untuk memudahkan penggunaan
- Input validation untuk refId, phone number, dan qty
- Helper function `oke_connect()` untuk inisialisasi instance
- Method `setInstance()` dan `getInstance()` untuk singleton pattern
- Validation methods: `validateRefId()`, `validatePhoneNumber()`, `validateQty()`
- Context data pada exception untuk debugging

### Changed
- Helper functions sekarang menggunakan static methods
- Semua API calls sekarang memiliki try-catch wrapper
- Error messages lebih user-friendly dalam Bahasa Indonesia
- README updated dengan contoh error handling

### Fixed
- Helper functions sekarang bisa digunakan tanpa instantiate class
- Network errors sekarang ditangani dengan proper exception

---

## [1.0.2] - 2024

### Changed
- README dalam Bahasa Indonesia
- Credit NdraDeveloper ditambahkan
- Tabel properti lengkap untuk semua models
- Changelog updated

---

## [1.0.1] - 2024

### Added
- Support Token PLN dengan SN panjang
- Support berbagai format failure reason
- Support format tanggal lengkap (DD/MM HH:mm)
- Test cases untuk format baru

### Changed
- Perbaikan parsing balance dengan separator en-dash
- Update parsers untuk handle berbagai format response

---

## [1.0.0] - 2024

### Added
- Initial release
- Parsing lengkap untuk transaksi (processing, success, failed)
- Parsing untuk status check (success, failed, pending, no data)
- Parsing untuk webhook callback
- Price list parser dengan berbagai utility methods
- Response models: TransactionResponse, StatusCheckResponse, WebhookCallback, PriceListItem
- Helper functions untuk quick access
- Examples untuk usage dan webhook handler
- PHPUnit tests untuk semua parsers
