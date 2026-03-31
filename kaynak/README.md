## Tahsilat Paneli

Laravel 11, PHP 8.3+, PostgreSQL 16, Blade, Alpine.js ve Tailwind CSS ile geliştirilen standalone tahsilat uygulaması.

### Kapsam

- Protokol yönetimi
- Günlük tahsilat yönetimi
- Tüm tahsilatlar listesi
- Dashboard / izlence analizi
- Yetki ve prim ayarları
- Prime esas tahsilat pivot ekranı
- Toplu protokol importu
- Sadece dashboard analizi için toplu tahsilat geçmiş importu

### Teknik Notlar

- Timezone: `Europe/Istanbul`
- Locale: `tr_TR`
- Para birimi: `TRY`
- Tüm parasal alanlar: `numeric(15,2)`
- Tahsilat dekontları ve protokol PDF dosyaları Laravel storage üzerinden `public` diskte tutulur
- `storage:link` akışı hazırdır
- Dashboard geçmiş import kayıtları ana tahsilat listesine girmez
- Prim pivotu yalnızca gerçek ve onaylı tahsilatlardan beslenir

### Varsayılan Giriş Bilgileri

Seed sırasında aşağıdaki env değişkenleri kullanılır:

- `DEFAULT_ADMIN_NAME`
- `DEFAULT_ADMIN_EMAIL`
- `DEFAULT_ADMIN_PASSWORD`

Varsayılan değerler:

- E-posta: `admin@example.com`
- Şifre: `password`

### Kurulum

1. `docker compose up -d`
2. `php artisan migrate --seed`
3. `php artisan storage:link`
4. `php artisan serve`

Uygulama `http://127.0.0.1:8000` altında açılır.

### Çevre Değerleri

`.env` ve `.env.example` PostgreSQL için hazır gelir:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5433
DB_DATABASE=tahsilat_paneli
DB_USERNAME=postgres
DB_PASSWORD=postgres
```

### Import / Export

- Toplu protokol şablonu: `/tahsilat/protokol-toplu/sablon`
- Toplu protokol import: `/tahsilat/protokol-toplu/import`
- İzlence geçmiş şablonu: `/tahsilat/izlence-gecmis/sablon`
- İzlence geçmiş import: `/tahsilat/izlence-gecmis/import`
- Excel export: `/tahsilat/export/excel`
- Mail order PDF export: `/tahsilat/export/mail-order-pdf`

### Test

Feature testler kritik kuralları kapsar:

- Protokol numarası üretimi
- 3 hacizcide pay oranı zorunluluğu
- Dekont zorunluluğu ve beklemede başlangıcı
- Onay / iptal ile over-collection engeli
- Red nedeni zorunluluğu
- Dashboard geçmiş importunun ana listeye sızmaması
- Prim pivotunun yalnızca gerçek onaylı tahsilatlardan beslenmesi

Çalıştırmak için:

```bash
php artisan test
```

### Not

Frontend assetleri derlenmiş olarak `public/build` altında mevcuttur. Tasarım veya JS geliştirmesi yapacaksanız:

```bash
npm install
npm run build
```
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Jump24](https://jump24.co.uk)**
- **[Redberry](https://redberry.international/laravel/)**
- **[Active Logic](https://activelogic.com)**
- **[byte5](https://byte5.de)**
- **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
