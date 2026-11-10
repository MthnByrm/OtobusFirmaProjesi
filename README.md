# Otobüs Firması Bilet Satış Sitesi

**PHP Programlama · Aşama 2 · Gümüşhane Üniversitesi**

| Ekip üyesi | Öğrenci numarası |
| --- | --- |
| Metehan Bayram | 2407231006 |
| Mustafa Efe Kaya | 2407231029 |
| Osman Nuri Gönültaş | 2407231079 |

Uygulama, şehirlerarası sefer aramayı, koltuk ayırmayı, demo ödeme ile bilet almayı, PNR sorgusunu ve bilet iptalini destekler. Yönetim panelinden güzergâh, otobüs ve sefer eklenip düzenlenebilir. Doluluk ve gelir raporu da aynı panelde yer alır.

## Yerelde çalıştırma

1. ZIP’i tamamen çıkarın. Terminalde `README.md` ile `public` klasörünün bulunduğu `OtobusFirmaProjesi` klasörüne girin.
2. PHP 8.2 veya üzeri kuruluysa aşağıdaki komutu çalıştırın.

   ```sh
   php -S 127.0.0.1:8000 -t public
   ```

   Windows’ta PHP komutu bulunamıyorsa ve XAMPP varsayılan konuma kuruluysa PowerShell’de aşağıdaki komutu kullanın.

   ```powershell
   & 'C:\xampp\php\php.exe' -S 127.0.0.1:8000 -t public
   ```

3. Tarayıcıda [http://127.0.0.1:8000](http://127.0.0.1:8000) adresini açın. Sunucuyu durdurmak için terminalde **Ctrl+C** tuşlarına basın.

Uygulama için Composer ve MySQL kurulumu gerekmez. `public/` web köküdür. `storage/` klasörü yazılabilir olmalıdır. Örnek seferler ilk çalıştırmanın ertesi gününden başlayarak 10 güne yayılır. İlk çalıştırmada 8 güzergâh, 8 otobüs, 90 sefer ve 12 bilet oluşturulur. Kayıtlar, koltuk seçimleri, satışlar ve iptaller `storage/veri.json` dosyasına yazılır.

### Hazır hesaplar

| Rol | E-posta | Parola |
| --- | --- | --- |
| Yolcu | `yolcu@otobusbilet.test` | `Yolcu2026!` |
| Yönetici | `admin@otobusbilet.test` | `Yonetim2026!` |

### Hızlı demo

1. Ana sayfadaki **İstanbul → Sivas** kartına tıklayın. İlk seferi açın ve yolcu hesabıyla giriş yapın. **Kadın** seçip 10 numaralı koltuğu ayırın.
2. Yolcu adı için `Demo Yolcu`, demo kimlik için `12345678950` girin. Demo ödeme onaylandığında PNR kodunu ve yazdırılabilir bileti görebilirsiniz.
3. Bileti sorgulamak için üretilen PNR’yi ve aynı kimlik numarasını kullanın. Hazır bir örnek görmek için `DEMO01` ve `12345678950` bilgilerini girin. Biniş durağındaki kalkış saatine göre iptal ekranında demo iade tutarı görünür.
4. İlk seferde 1 numaralı koltuk İstanbul–Ankara ve Ankara–Sivas aralıklarına ayrı ayrı satılmıştır. Bu örnek, aynı koltuğun çakışmayan etaplarda kullanılabildiğini gösterir. İstanbul–Ankara aramasında 2 numaralı koltuğu **Erkek** seçerek yan koltuk kuralını da deneyebilirsiniz.
5. Yönetici hesabıyla giriş yapın. Güzergâh, otobüs ve sefer sekmelerinde kayıtları yönetin. **Doluluk ve gelir** ekranında satış ve iadeleri takip edin. Doluluk için seferin en yoğun durak aralığı esas alınır.

Ödeme simülasyonu hazır demo kartla çalışır. Ödeme reddi senaryosunda kilit süresi içinde yeniden denenebilir. Örnek ortamı sıfırlamak için sunucuyu durdurup `storage/veri.json` ve `storage/oturumlar/` öğelerini silin. Bu işlem sonradan eklenen demo kayıtlarını siler ve bir sonraki açılışta örnek seferleri yeni tarihlerle oluşturur.

## Uygulama yapısı

| Bölüm | Görev |
| --- | --- |
| `public/index.php` ve `src/Support/Router.php` | İstek yönlendirme, form doğrulama ve erişim denetimi |
| `src/Controller/` ve `views/` | Yolcu ve yönetici ekranları |
| `src/Service/` | Sefer arama, koltuk kilidi, satın alma, bilet iptali ve raporlama |
| `src/Repository/` | Veri erişimi ve işlem sınırı arayüzleri, JSON uygulaması |
| `storage/ornek-veriler.json` | İlk çalıştırmada kullanılan örnek kayıtlar |
| `tests/` | Çekirdek iş kuralları ve JSON işlemleri için PHPUnit testleri |

Koltuk doluluğu durak sırasına göre hesaplanır. Seçim, en fazla 10 dakika boyunca kilitlenir. Ödeme sırasında kilitler tekrar doğrulanır. JSON değişiklikleri dosya kilidi altında ve tek işlemde yazılır. İade kuralları, kalkışa 24 saatten fazla kaldığında %100, 6 ile 24 saat arasında %50 ve 6 saatten az kaldığında %0 olarak uygulanır.

Servisler `IslemYoneticisiInterface` ve `KayitDeposuInterface` üzerinden veriye erişir. Aşama 2’de her varlık için ayrı dosya yerine ortak bir kayıt deposu arayüzü kullanıldı. Aşama 3’te bu arayüzler PDO/MySQL ile uygulanıp `bootstrap.php` içindeki bağlantı değiştirilebilir. MySQL, projenin sonraki aşamasıdır.

### Geliştirici kontrolleri

Composer ve PHPUnit için gereken PHP eklentileri kuruluysa proje kökünde aşağıdaki komutları çalıştırın.

```sh
composer install
vendor/bin/phpunit
vendor/bin/phpstan analyse src --level=3 --memory-limit=512M
```

Aşama 1 belgeleri `docs/` altında yer alır.
