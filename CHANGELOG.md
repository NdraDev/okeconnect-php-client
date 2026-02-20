# Changelog

Semua perubahan penting pada package ini akan didokumentasikan di file ini.

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
