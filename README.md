# V-404 — WampServer yerel sürüm

Kaybolduysa, burada bul.

Bu paket PHP 8.2+ ve MySQL 8.0+ / MariaDB 10.4+ ile çalışır. Frontend HTML, CSS ve JavaScript; backend PHP/PDO'dur. Node, Composer veya internetten indirilen frontend bağımlılığı gerekmez. Açık tema varsayılandır. Verilen orijinal logo değiştirilmeden pakete eklenmiştir; başlıkta görselin açık zeminli logo bölümü kullanılır.

## 1. WampServer'a kurulum

1. ZIP'i çıkarın. `V-404` klasörünü `C:\wamp64\www\V-404` konumuna koyun.
2. WampServer'ı başlatın; simgenin yeşil olmasını bekleyin. Apache ve MySQL/MariaDB çalışmalı.
3. Wamp menüsünde PHP sürümünü **8.2 veya üzeri** seçin. `pdo_mysql`, `mbstring`, `gd` ve `fileinfo` eklentilerini etkinleştirin.
4. `V-404\.env` dosyasını Not Defteri ile açın. `DB_HOST`, `DB_PORT`, `DB_USER`, `DB_PASS` değerlerini kendi Wamp MySQL bilgilerinize göre düzenleyin. Varsayılan kullanıcı `root`, parola boş, port `3306`dır; sizin kurulumunuz farklı olabilir. MariaDB portunuz 3307 ise onu yazın. Dosyanın adı `.env` olmalı; `.env.txt` veya `.end` değil.
5. Chrome'da **http://localhost/V-404/setup.php** açın.
6. Kendi yönetici adınızı, e-postanızı ve en az 12 karakterli parolanızı belirleyin. “Veritabanını kur” düğmesi şemayı kurar ve ilk yönetici hesabını oluşturur. Kurulum daha sonra kendini kilitler. Hazır ortak yönetici parolası yoktur.
7. Site: **http://localhost/V-404/**
8. Yönetim paneli: **http://localhost/V-404/admin.php**. Kurulumda oluşturduğunuz hesapla giriş yapın; ayrı yönetim görünümü açılır.

**PHP dosyasını çift tıklayarak / file:// adresiyle açmayın.** Backend için Chrome'da localhost adresi kullanılmalıdır. ZIP açmak tek başına MySQL'i başlatmaz.

## 2. Veritabanı dosyası

Tek şema dosyası: `database/schema.sql`. 18 uygulama tablosunu oluşturur; şema birden fazla sıralı migration dosyasına bölünmemiştir. Kurulum sihirbazı bu dosyayı çalıştırır. **Normal kurulumda ayrıca phpMyAdmin içe aktarımı gerekmez.**

İsterseniz boş veritabanına phpMyAdmin'den schema.sql aktarın, ardından setup.php ile ilk yöneticiyi oluşturun. SQL varsayılan `v404` adını kullanır. Farklı DB_NAME için sihirbaz adını otomatik uyarlar; manuel aktarımda SQL'in CREATE DATABASE ve USE satırlarını aynı ada değiştirin. `IF NOT EXISTS` mevcut veriyi silmez, fakat eski/farklı sürüm şemasını dönüştüren migration değildir; bu ilk sürüm yeni veritabanı içindir.

## 3. Klasörler

- `frontend/`: sayfalar için JS, renk sistemi, responsive CSS ve orijinal logo.
- `backend/`: PDO bağlantısı, oturum/yetki kontrolleri, eşleştirme ve bakım komutu.
- `api.php`: frontend'in çağırdığı JSON API ve yetki kontrollü görsel sunumu.
- `index.php`: kullanıcı uygulaması; `admin.php`: yönetim girişi.
- `setup.php`: yalnızca localhost'a açık, ilk yönetici sonrası kilitlenen kurulum.
- `database/schema.sql`: tek veritabanı şeması.
- `uploads/`: yeniden kodlanmış ilan/destek fotoğrafları; doğrudan web erişimi engellenir.
- `storage/`: yerel çalışma alanı; web erişimi engellenir.
- `.env`, `.env.example`: sunucu ve e-posta modu ayarları.
- `KAPSAM.md`: gönderdiğiniz ürün tanımının orijinali.
- `OZELLIKLER.md`: modüller, uygulama kararları ve dış hizmet sınırları.
- `tests/`: yerel entegrasyon kontrol betiği; sadece test veritabanında çalıştırılır.

## 4. İlk kullanım

Ana sayfa gerçek veritabanını gösterir. Sahte ilan veya önceden tanımlı kullanıcı yoktur; başlangıçta boş olması normaldir.

1. Normal kullanıcı hesabı açın. 18 yaş / veli beyanını onaylayın.
2. Yerel geliştirme posta kutusunda görünen 6 haneli kodu doğrulama alanına yazın. Kod yalnızca kayıt yapan oturumun yanıtında gösterilir; genel bir posta kutusu dosyası yoktur.
3. Kaybettim, Buldum, Ücretsiz Paylaş veya Hayvanlar bölümünden ilan açın. Fotoğraf üzerinde sürükleyerek hassas alanları kapatabilirsiniz. İsterseniz haritayı açıp konum seçebilir, güzergâh noktaları ekleyebilirsiniz; ham rota gizli kalır. “Önizle” ile açık bilgileri inceleyip onaylayın.
4. İkinci kullanıcı için Chrome gizli pencere veya başka tarayıcı kullanın. Aynı tarayıcı sekmeleri aynı oturumu paylaşır.
5. İkinci kullanıcı bulunan eşya ilanında gizli ayrıntıyla sahiplik talebi gönderir. İlan sahibi talebi doğrular veya reddeder.
6. Kabulden sonra özel görüşme açılır. Teslimin iki tarafça onaylanması ilanı kapatır. Aynı kayıt kurum ve genel ağda birlikte kapanır.

### Eşleşme denemesi

Bir kullanıcıyla kayıp, başka kullanıcıyla bulunan ilanı açın. Aynı kategori / şehir, yakın tarih ve aynı renk / marka / bölge kullanın. Eşleşmeler ekranında olasılık görünür. Tek eşya çifti için bildirim tekrar gönderilmez. Eşleştirme bir kural puanıdır; yapay zekâ, görüntü tanıma veya kesin sahiplik kararı değildir.

### Kurum denemesi

Kurumlar → başvuru oluştur → platform yöneticisinden onay → kurum paneli. Kurum yöneticisi şube, bina, kat ve oda ekler. Üye başvurularını onaylar veya tek kullanımlık 7 günlük davet oluşturur. Çalışan / yer yöneticisine konum atamak zorunludur. Yetki o konum ve alt konumlarla sınırlıdır. Kurum yöneticisi tüm kurumda yetkilidir. Üyeler kendi ilanlarını yönetir.

## 5. E-posta ve yerel mod

Paket `.env` içinde `APP_ENV=local` ve `MAIL_MODE=local` ile gelir. Bu modda **gerçek e-posta veya SMS gönderilmez**; kod giriş yapan kullanıcıya yerel geliştirme ekranında gösterilir. Yerel kayıt akışı test edilebilir, e-posta hesabının gerçekten kontrol edildiğini kanıtlamaz.

Gerçek e-posta için `APP_ENV=production`, `MAIL_MODE=mail`, `MAIL_FROM` ayarlayın; PHP `mail()` için SMTP/MTA yapılandırmasını sunucunuzda tamamlayın. Wamp'ta varsayılan mail() çoğu kurulumda yapılandırılmamıştır. Kod gönderilemezse kullanıcıya hata gösterilir; ayar düzeldikten sonra yeni kod istenebilir. Harici SMS servisi bu pakete dahil değildir. E-posta veya telefon seçeneklerinden e-posta uygulanmıştır.

### Harita

Leaflet 1.9.4 dosyaları pakete dahildir (`frontend/vendor`, BSD lisansı yanındadır). Harita yalnızca “Haritayı aç” seçildiğinde OpenStreetMap harita parçalarını yükler; bunun için internet gerekir ve sağlayıcı IP adresini/görüntülenen alanı görebilir. Harita kapalıyken dış harita isteği yapılmaz. Tam nokta ve rota yetkiliye özeldir; ziyaretçi koordinatı iki ondalığa yuvarlanır. Harita yüklenmezse şehir/bölge metniyle tüm temel işlemleri sürdürebilirsiniz.

## 6. Süreler ve otomatik bakım

- Kayıp / bulunan eşya: 90 gün.
- Ücretsiz eşya: 30 gün.
- Kayıp / bulunan hayvan: 60 gün.
- Sahiplendirme: 30 gün.

API kullanılırken süresi dolan ilanlar otomatik arşivlenir. Sistem kullanılmadığında da arşivleme ve saklama temizliği çalışsın diye Windows Görev Zamanlayıcı'da günlük görev oluşturun:

Program: `C:\wamp64\bin\php\php8.x.x\php.exe` (kendi PHP klasörünüzü yazın)
Argüman: `C:\wamp64\www\V-404\backend\maintenance.php`
Başlangıç: `C:\wamp64\www\V-404`

Kapanma/arşivden 180 gün sonra özel ilan alanları, talep kanıtları, mesajlar ve ilan fotoğrafları temizlenir. Yönetim kayıtları ve sonuçlandırılmış şikâyetler 365 gün sonra temizlenir. Veritabanı/yükleme yedeklerinizi ayrıca kendi saklama politikanızla yönetin.

Doğrulanmış kişisel veri silme talebi için operatör komutu:

`php backend/maintenance.php anonymize KULLANICI_ID CONFIRM`

Bu komut geri alınamaz: önce kullanıcı isteğini ve kimliğini doğrulayın, ilgili kurumun yönetimini devredin. Kullanıcı kapanır, adı/e-postası anonimleşir; ilanları ve ekleri kaldırılır; kendi mesajları, üyelikleri ve talep kanıtları temizlenir. Başkalarının mesajlarında yazdığı kişisel bilgileri ayrıca inceleyin. Platform yönetici hesabı bu komutla silinmez.

## 7. Güvenlik uygulaması

PDO hazırlanmış sorgular, password_hash/password_verify, CSRF belirteci, HttpOnly/SameSite oturum çerezi, oturum kimliği yenileme, hız sınırları ve sunucuda rol/konum denetimi kullanılır. Fotoğraflar boyut/MIME denetiminden sonra GD ile yeniden JPEG'e kodlanır; kaynak dosyanın EXIF metaverisi ve yürütülebilir içeriği taşınmaz. Fotoğraflar API yetkisiyle sunulur. `.env`, SQL ve özel klasörlerin Apache erişimi `.htaccess` ile engellenir.

Wamp Apache'de bu klasör için `AllowOverride All` etkin olmalıdır. `http://localhost/V-404/.env`, `/database/schema.sql` ve `/uploads/` adreslerinin **403** verdiğini kontrol edin. `.htaccess` desteği olmadan uygulamayı internete açmayın. Bu paket localhost içindir; internet yayını için HTTPS, gerçek posta servisi, yedekleme, operasyonel moderasyon ve saklama görevlerini yapılandırın.

## 8. Sık sorunlar

- **Tablo bulunamadı:** setup.php çalıştırın; `.env` DB_NAME değerinin oluşturulan veritabanıyla aynı olduğunu doğrulayın. 18 tablo tek şemadadır.
- **500 / bağlantı hatası:** Apache/PHP hata günlüğüne bakın; MySQL portu ve parolayı kontrol edin. Teknik SQL ayrıntıları kullanıcı ekranına yazılmaz.
- **could not find driver:** Wamp PHP `pdo_mysql` eklentisini etkinleştirip servisleri yeniden başlatın.
- **Fotoğraf yüklenmiyor:** `gd` açık olmalı, `uploads` Apache tarafından yazılabilir olmalı. php.ini içinde `upload_max_filesize=6M`, `post_max_size=8M` veya üzeri seçin. Uygulama tek dosyada 5 MB sınırlar.
- **Doğrulama kodu yok:** Profilde yeni kod isteyin; `.env` yerel modunu kontrol edin.
- **Kurum görünmüyor:** platform onayı ve üyelik onayı ayrı adımlardır.
- **Görüşme açılmıyor:** talep önce ilan sorumlusu tarafından kabul edilmelidir.
- **PHP kodu ekranda görünüyor:** dosyayı file:// ile değil Wamp'ın localhost adresiyle açın.

## 9. Kontrol

Bu paketin hazırlanırken yapılabilen kontrolleri ve sonuçlarını `TEST_SONUCLARI.md` dosyasında okuyun. Windows/WampServer ortamınızda kurulum sonrası smoke-test adımlarını uygulayın. Sunucu test betiği Node 18+ ile çalışır, üretim verisinde çalıştırılmaz; siteyi kullanmak için Node gerekmez.

## 10. Etkileşimli site rehberi

İlk ziyarette ve hesabın ilk doğrulanmış girişinde alanları vurgulayan bir rehber açılır. İleri / Geri ile gezinilir; “Bölüme git” ile istenen konu seçilebilir. Rehber kapatılabilir veya Escape ile atlanabilir. Üst menüdeki **? Site rehberi** düğmesi tekrar açar (telefonda **?**).

Tamamlanma/atlama tercihi tarayıcıda ziyaretçi ve kullanıcı hesabı için ayrı tutulur. Başka cihazda veya tarayıcı verileri silinince yeniden gösterilir. Rehber örnek kayıt oluşturmaz, ilan yayımlamaz ve başvuruları değiştirmez. Kayıtlı kullanıcının kişisel alanlarını, yönetici/moderatörün yetkisine göre yönetim ekranlarını açıklar. Kaydedilmemiş bir formdan manuel başlatılırsa sayfalar arası geçiş öncesinde uyarır.

### Önceki sürümden güncelleme

Mevcut kurulumda `index.php`, `frontend/app.js`, `frontend/style.css` ve yeni `frontend/tour.js` dosyalarını bu pakettekilerle değiştirin. **Kendi `.env` dosyanızı ve `uploads` klasörünüzü koruyun.** Bu rehber güncellemesi veritabanı değişikliği gerektirmez; SQL'i yeniden içe aktarmayın. Tarayıcıda Ctrl+F5 ile yenileyin.
