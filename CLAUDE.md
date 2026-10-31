# CLAUDE.md

Bu dosya, bu depoda çalışan Claude Code (ve ekipteki diğer yapay zekâ araçları) için proje bağlamıdır.
Ayrıntılı tasarım: `docs/tasarim.md`. Yapay zekâ kullanımı `docs/yapay-zeka-gunlugu.md` dosyasına kaydedilir.

## Proje özeti

**Otobüs Firması Bilet Satış Sitesi.** PHP Programlama dersi dönem projesi, 3 kişilik ekip.
Yolcu nereden/nereye/tarih ile sefer arar, koltuk haritasından koltuk seçer, yolcu bilgisini girer, simüle ödeme yapar ve PNR kodlu bilet alır; bileti sorgular, iptal eder, iade alır. Yönetici güzergâh, otobüs ve sefer yönetir, doluluk ve gelir raporu görür.

## Çekirdek problem (hepsi sunucuda denetlenir)

1. **Koltuk çakışması ve geçici kilit:** seçilen koltuk 10 dk kilitlenir, iki kişi aynı koltuğu alamaz, süre dolunca kilit kalkar.
2. **Duraklara göre (etap bazlı) doluluk:** bir koltuk İstanbul–Ankara arasında doluyken Ankara–Sivas arasında satılabilir.
3. **Yan yana koltukta karşı cinsiyet kuralı:** komşu koltukta (aynı 2'li grup, kesişen etap) karşı cinsiyet varsa seçilemez. 2+1 planındaki tekli koltukta kural yoktur.
4. **İptal ve iade:** kalkışa kalan süre > 24 saat %100, 6–24 saat %50 (sınırlar dâhil), < 6 saat iade yok, kalkış geçmişse iptal yok. Referans: yolcunun biniş durağından kalkış zamanı.

Kapsam dışı: gerçek ödeme, SMS/e-posta, dinamik fiyatlandırma. Bunlar için kod yazma.

## Mimari (zorunlu)

Her istek: `public/index.php → Router → Controller → Service → Repository (arayüz) → veri`. Bir katman yalnızca **altındaki** katmanı çağırır.

| Katman | Görevi | Asla yapmaz |
| --- | --- | --- |
| Router | Adrese göre controller metodunu seçer | İş kuralı içermez |
| Controller | `$_POST`/`$_GET` alır, servisi çağırır, view seçer veya JSON döner | SQL yazmaz, hesap yapmaz, **Repository'yi çağırmaz** |
| Service | İş kuralları ve çekirdek algoritma | `$_POST` okumaz, HTML üretmez, SQL yazmaz |
| Repository | Veri okur/yazar (arayüz + `Json*` ve `MySql*` uygulamaları) | İş kuralı içermez |
| Model | Veri taşıyan sade sınıf, enum'lar | Veritabanına erişmez |
| View | HTML gösterir, çıktıyı `htmlspecialchars` ile kaçışlar | Hesap yapmaz, veri çekmez |

Servisler somut sınıfa değil `*RepositoryInterface`, `IslemYoneticisiInterface` ve `SaatInterface` arayüzlerine bağımlıdır (yapıcıdan enjeksiyon). Nesneler yalnızca `bootstrap.php` içinde bağlanır.

Tasarım desenleri: **Repository** (zorunlu) ve **Strategy** (iade politikaları: `IadePolitikasiInterface`, `TamIade`, `YarimIade`, `IadeYok`, `IadeHesaplayici`).

## Klasör yapısı

```text
public/        index.php (tek giriş), assets/
src/Controller, Service (Service/Iade), Repository (Json/, MySql/), Model, Support, Exception
views/         layout.php, auth/, seferler/, seats/, booking/, biletler/, admin/
database/      schema.sql, seed.sql
storage/       Aşama 2 JSON dosyaları (git'e girmez)
tests/         PHPUnit
docs/          tasarim.md, backlog.md, wireframes/, yapay-zeka-gunlugu.md
```

## Teknik kurallar

- PHP 8.2+, Composer PSR-4 (`App\` → `src/`), namespace klasör yoluyla birebir aynı, PSR-12 kod stili, `declare(strict_types=1)`.
- **Framework yasak** (Laravel, Symfony, Slim vb.). Router, controller, DI kendi kodumuzdur. Framework'e özgü kod önerme.
- **Para:** veritabanında `DECIMAL(10,2)`, PHP'de **kuruş (int)**. Float ile hesap yapma.
- **Saat:** `Europe/Istanbul`. Zamanı doğrudan `new DateTime()` ile alma; `SaatInterface` kullan (testte `SahteSaat`).
- **Kilit temizliği:** arka plan betiği yok; süresi dolan kilitler ilgili sefere her okuma/yazmada silinir.
- **Eşzamanlılık:** kilit ve satış `IslemYoneticisiInterface::calistir()` içinde, sefer kilitlenerek yapılır (Aşama 2: `flock`, Aşama 3: `SELECT … FOR UPDATE`).
- **Güvenlik:** PDO hazırlıklı ifadeler, `password_hash`/`password_verify`, çıktı kaçışlama, CSRF token, girişte `session_regenerate_id`, rol yetkilendirme, sunucu tarafı doğrulama. TC kimlik no sayfalarda maskelenir ve loglanmaz.
- Test: `phpunit/phpunit`, statik analiz: `phpstan/phpstan` (hatasız). Çekirdek için en az 10 PHPUnit testi, sınır değerleri dâhil (24:00, 24:01, 6:00, 5:59).

## Çalışma kuralları (ekip + yapay zekâ)

- Açıklanamayan kod teslim edilmez; sunumda rastgele satır sorulabilir. Her sınıf için "hangi FR için var?" cevaplanabilmeli.
- Büyük işi küçük böl; önce test, sonra kod. Değişiklikten sonra: çalıştır → PHPUnit → PHPStan → commit.
- Yapay zekânın önerdiği sınıf/tablo gereksinimle bağlanmıyorsa çıkarılır. Katman kuralını bozan öneri kabul edilmez.
- Önemli yapay zekâ kullanımı `docs/yapay-zeka-gunlugu.md` dosyasına yazılır (tarih, amaç, istem, sonuç, değiştirilen kısım, neden, yapay zekânın yanıldığı durum).

## Git

- `main` korumalıdır; her iş ayrı dalda (`ozellik/...`), commit mesajında issue numarası (`#12`), PR + 1 onay ile birleşir.
- Anlamlı commit mesajı yaz ("Etap çakışma testi eklendi #14"); "güncelleme" kabul edilmez.
- `.env`, `vendor/`, `storage/*.json` depoya girmez.

## Komutlar

```bash
composer install
composer dump-autoload
vendor/bin/phpunit
vendor/bin/phpstan analyse src
php -S localhost:8000 -t public
```
