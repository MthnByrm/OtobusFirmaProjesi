# Yapay Zekâ Günlüğü

Bu dosya, proje sürecinde yapay zekadan alınan desteklerin kaydını tutar. Kayıt tarihi, amaç, istem (özet), sonuç, değiştirilen/reddedilen kısım ve nedeni yer alır.

### Kayıt 1 — FR-07 ve FR-08: Ara durak çakışma kuralının kontrolü
- **Tarih:** [11.10.2026]
- **Amaç:** Ara duraklı seferlerde (ör. İstanbul–Ankara–Sivas) koltuk doluluk kontrolünün ve 10 dakikalık kilit süresinin sınır durumlarını doğrulamak.
- **Kullanılan istem (özet):** Yazılan FR-07 ve FR-08 maddelerine bakılarak biniş ve iniş durakları arasındaki çakışma koşulunun matematiksel olarak nasıl ifade edileceği soruldu.
- **Sonuç:** İki etabın çakışması için durak sıralarına göre `a1 < b2` ve `b1 < a2` koşulunun yeterli olduğu, kilit süresinin ise ödeme adımında sunucuda tekrar kontrol edilmesi gerektiği belirtildi.
- **Kabul edilen/reddedilen kısım:** Kabul edildi.
- **Nedeni:** İstanbul–Ankara arası dolu olan koltuğun Ankara–Sivas etabında tekrar satılabilmesi için durak sırası karşılaştırması gerekiyordu.
- **Yapay zekânın yanıldığı / atladığı durum:** Yok.

### Kayıt 2 — FR-09: Karşı cinsiyet kuralı açıklamasının düzenlenmesi
- **Tarih:** [11.10.2026]
- **Amaç:** FR-09'daki yan koltuk cinsiyet kuralını 2+1 ve 2+2 otobüs düzenlerine göre daha açık hale getirmek.
- **Kullanılan istem (özet):** Mevcut FR-09 açıklamasının 2+1 otobüslerdeki tekli koltukları ve kesişen etapları kapsayacak şekilde düzeltilmesi istendi.
- **Sonuç:** Tekli koltukların kuraldan muaf olduğu, kuralın yalnızca aynı ikili koltuk grubunda ve kesişen etaplarda geçerli olduğu bir ifade önerildi.
- **Değiştirilen kısım:** FR-09 açıklaması önerilen ifadeden yararlanılarak yeniden yazıldı.
- **Nedeni:** İlk yazdığımız metinde 2+1 otobüsteki tekli koltukların durumu net değildi.
- **Yapay zekânın yanıldığı / atladığı durum:** Yok.

### Kayıt 3 — FR-14: İade hesaplaması için Strategy deseni kurgusu
- **Tarih:** [12.10.2026]
- **Amaç:** Kalkışa kalan süreye göre (%100, %50, %0) değişen iade oranlarını `if-else` blokları yerine tasarım deseniyle modellemek.
- **Kullanılan istem (özet):** FR-14'teki saat bazlı iade kuralları için Strategy deseninin sınıf yapısının nasıl kurulacağı soruldu.
- **Sonuç:** `IadePolitikasiInterface` arayüzü ve bunu uygulayan `TamIadePolitikasi`, `YarimIadePolitikasi`, `IadeYokPolitikasi` sınıfları önerildi.
- **Değiştirilen kısım:** Sınıf yapısı kabul edildi ancak ücret veri tipi değiştirildi.
- **Nedeni:** İade oranları değiştiğinde servis koduna dokunmadan yeni sınıf ekleyebilmek.
- **Yapay zekânın yanıldığı / atladığı durum:** İlk çıktıda bilet ücretleri `float` olarak verilmişti. Kuruş hesabında yuvarlama hatası olmaması için bu kısım reddedildi ve tüm tutarlar kuruş cinsinden `int` yapıldı.

### Kayıt 4 — Mimari: Katman kuralları ve Repository arayüzleri
- **Tarih:** [13.10.2026]
- **Amaç:** Aşama 2'de JSON, Aşama 3'te MySQL ile çalışacak yapıda katman sınırlarını netleştirmek.
- **Kullanılan istem (özet):** Veritabanı değiştiğinde Service katmanının etkilenmemesi için Repository arayüzlerinin nasıl ayrılması gerektiği soruldu.
- **Sonuç:** `BiletRepositoryInterface`, `SeferRepositoryInterface` ve `KoltukKilidiRepositoryInterface` altında `Json*` ve `MySql*` sınıflarının ayrıldığı yapı oluşturuldu.
- **Kabul edilen/reddedilen kısım:** Kabul edildi.
- **Nedeni:** Aşama 3'te MySQL'e geçerken iş kurallarını tekrar yazmamak.
- **Yapay zekânın yanıldığı / atladığı durum:** Yok.

### Kayıt 5 — Sınıf Diyagramı: İlk taslağın oluşturulması ve katman eksikliği
- **Tarih:** [14.10.2026]
- **Amaç:** Tasarım belgesi için Mermaid formatında UML sınıf diyagramı oluşturmak.
- **Kullanılan istem (özet):** Temel sınıflarımızı ve ilişkilerini gösteren Mermaid `classDiagram` taslağı istendi.
- **Sonuç:** Yalnızca temel model ve birkaç servis sınıfını içeren 7 sınıflı küçük bir diyagram üretildi.
- **Kabul edilen/reddedilen kısım:** Reddedildi.
- **Nedeni:** Ödev kriterlerindeki sınıf sayısı şartını sağlamıyordu ve somut Repository sınıfları (`JsonBiletRepository`, `MySqlBiletRepository`) ile Controller katmanı eksikti.
- **Yapay zekânın yanıldığı / atladığı durum:** Yapay zekâ diyagramı kısa tutmak için sadece ana varlıkları koyup mimari katmanları atladı.

### Kayıt 6 — Sınıf Diyagramı: Tüm katmanların diyagrama eklenmesi
- **Tarih:** [14.10.2026]
- **Amaç:** Eksik kalan Controller, Service ve somut Repository sınıflarını ekleyerek diyagramı mimari tabloyla uyumlu hale getirmek.
- **Kullanılan istem (özet):** Controller'ların yalnızca Service katmanını çağırdığı, Service'lerin de Repository arayüzlerine bağlandığı tam sınıf diyagramı istendi.
- **Sonuç:** Arama, koltuk kilitleme, satın alma, bilet sorgulama ve iade akışındaki tüm sınıfları içeren yeni diyagram üretildi.
- **Değiştirilen kısım:** Eski diyagram silinerek yeni yapı `docs/tasarim.md` dosyasına eklendi.
- **Nedeni:** Sunumda katman atlama hatası olmaması ve mimari tablodaki sınıflarla diyagramın birebir örtüşmesi.
- **Yapay zekânın yanıldığı / atladığı durum:** İlk taslaktaki eksik sınıf problemi ikinci istemde açıkça katman isimleri verilerek çözüldü.

### Kayıt 7 — Sıralama Diyagramı: Koltuk kilitleme akışı ve `alt/else` blokları
- **Tarih:** [14.10.2026]
- **Amaç:** Koltuk seçimi ve 10 dakikalık geçici kilit adımlarını hata durumlarıyla birlikte sıralama diyagramında göstermek.
- **Kullanılan istem (özet):** `Tarayıcı -> KoltukController -> KoltukKilitService -> Repository` sırasını izleyen ve çakışma/cinsiyet kuralı hatalarını `alt / else` bloklarıyla gösteren `sequenceDiagram` istendi.
- **Sonuç:** `POST /koltuk/kilitle` isteğiyle başlayan, doğrulama hatası (422), koltuk dolu/cinsiyet hatası (409) ve başarılı kilit (200) durumlarını içeren diyagram üretildi.
- **Değiştirilen kısım:** Aktif kilitler çekilmeden önce süresi dolan kilitlerin silinmesi adımı eklendi.
- **Nedeni:** 10 dakikası dolmuş kilitlerin koltuğu boş yere dolu göstermesini engellemek.
- **Yapay zekânın yanıldığı / atladığı durum:** İlk çıktıda doğrudan `seferdekiAktifler` çağrılıyordu, süresi biten kilitlerin temizlenmesi (`suresiDolanlariSil`) unutulmuştu; tarafımızca eklendi.

### Kayıt 8 — ER Diyagramı: 11 tablonun yerleşimi ve 3NF kontrolü
- **Tarih:** [14.10.2026]
- **Amaç:** Veritabanı tablolarını ve 1:N ilişkilerini Mermaid üzerinde okunaklı şekilde çizmek.
- **Kullanılan istem (özet):** 11 tablomuzun PK/FK alanlarını içeren diyagram kodu ve 3NF uygunluk kontrolü istendi.
- **Sonuç:** Tabloların gruplandırıldığı diyagram oluşturuldu ve `BILETLER.ucret` sütununun neden 3NF ihlali olmadığı açıklandı.
- **Kabul edilen/reddedilen kısım:** Kabul edildi.
- **Nedeni:** Sefer fiyatına ileride zam gelse bile önceden satılmış biletin tutarının sabit kalması gerekiyor.
- **Yapay zekânın yanıldığı / atladığı durum:** Yok.

### Kayıt 9 — Aşama 2 uygulamasının geliştirilmesi

- **Tarih** 10.11.2026
- **Amaç** Gereksinim ve tasarım belgelerini PHP ile çalıştırılabilen, JSON verili bir teslim paketine dönüştürmek.
- **İstem özeti** Sefer arama, etap bazlı koltuk kilidi, demo ödeme, PNR, iptal ve yönetim işlemlerini tamamla. Örnek verileri doldur, MySQL’e geçiş için veri erişimini ayır ve projeyi ZIP’ten çalıştırılabilir biçimde paketle.
- **Uygulanan tasarım kararı** JSON kayıtları ortak veri deposu ve işlem yönetimi arayüzleriyle yönetildi. Bir bilet alımı sırasında sipariş, yolcu, bilet ve kilit güncellemeleri aynı JSON işleminde saklandı.
- **Düzeltilen durum** Ödeme ekranındaki ikinci örnek kimlik numarasının kontrol basamakları doğrulama testinde hatalı çıktı. Geçerli örnekle değiştirildi.
