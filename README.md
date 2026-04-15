# Champions League Simulator

Laravel 13 + Vue 3 + Inertia tabanli 4 takimlik bir sampiyonlar ligi simulasyon projesi iskeleti.

## Hazirlanan temel yapi

- Laravel uygulama iskeleti
- Vue 3 ve Inertia giris katmani
- `Team` ve `Fixture` domain modelleri
- 4 takimlik ornek seed verisi
- 6 mac haftalik cift devreli fikstur yapisi
- Basit puan durumu hesaplama servisi
- VS Code icin proje bazli extension onerileri

## Projeyi calistirma

```bash
composer install
npm install
php artisan migrate:fresh --seed
composer run dev
```

Alternatif olarak iki terminal:

```bash
php artisan serve
npm run dev
```
