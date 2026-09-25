# V-404 doğrulama kaydı

Tarih: 25 Eylül 2026

## Sunucu kontrolleri

Linux üzerinde PHP 8.3.6, pdo_mysql/mbstring/GD ve MariaDB 10.11.14 kullanıldı. Ayrı yerel test veritabanında kurulum sihirbazı çalıştırılarak tek SQL şemasının ve ilk yönetici hesabının oluşması doğrulandı. Test veritabanı, test hesapları ve test yüklemeleri teslim ZIP'ine dahil değildir.

**32 entegrasyon kontrolü geçti:**

- Üç bağımsız oturumda kayıt ve yerel e-posta kodu doğrulama.
- Ziyaretçinin ilan oluşturmasının reddedilmesi.
- Genel ilan yanıtında gizli doğrulama, rota ve tam muhafaza alanlarının bulunmaması.
- İlan sahibinin kendi özel alanlarını görebilmesi.
- Harita koordinatının ziyaretçiye iki ondalıkla verilmesi; rota noktalarının gizlenmesi.
- İlan sahibinin tam koordinat ve rota noktalarını alabilmesi.
- Benzer kayıp/bulunan ilan eşleştirmesi ve yinelenmeyen bildirim.
- Yetkisiz ilan düzenlemesinin reddedilmesi.
- Bulunan ilanı ücretsiz paylaşım türüne dönüştürmenin reddedilmesi.
- Kabulden önce sohbetin açılmaması.
- Üçüncü hesabın görüşmeye erişememesi ve sahiplik talebini onaylayamaması.
- Kabulden sonra özel mesajlaşma.
- Tek tarafın teslim onayının ilanı kapatmaması.
- Normal ilan sahibinin kurum görevlisi teslim yetkisini kullanamaması.
- İki taraflı teslimden sonra ilanın kapanması ve sohbetin yeni mesajlara kapanması.
- Onaylanmamış kurumun kullanılamaması.
- Kurum konum hiyerarşisinin doğrulanması.
- Kurum özel ilanının ziyaretçiye açılmaması.
- Davet kodunun tek kullanımlı olması.
- Onaysız üyenin kurum özel ilanını görememesi.
- Normal üyenin kurumun tam konum notunu alamaması.
- Çalışanın başka şubedeki ilanı düzenleyememesi veya o konumda kayıt açamaması.
- Atandığı şubenin alt binasında çalışanın yetkisinin geçerli olması.
- Normal hesabın platform yönetimi API'sine erişememesi.
- Moderasyon yanıtının kullanıcı başvurusuna işlenmesi.
- Engellenen hesabın yeni talep gönderememesi.
- Yanlış CSRF belirteciyle değişiklik yapılamaması.

**Ek dosya kontrolleri geçti:**

- GD ile yeniden kodlanan JPEG yükleme ve yetkili fotoğraf sunumu.
- Görsel gibi adlandırılmış PHP/metin içeriğinin reddedilmesi.
- Destek görselinin ziyaretçiye kapalı olması.
- Silinmiş ilanın yeniden açılamaması ve fotoğrafının erişime kapanması.

Bakım komutu hatasız çalıştı. Süreye bağlı 180/365 günlük toplu silme ve kullanıcı anonimleştirme akışının tüm veri kombinasyonları otomatik test edilmedi.

## Sözdizimi

Altı PHP dosyası `php -l` kontrolünden geçti. Uygulama JavaScript'i `node --check` kontrolünden geçti.

## Tarayıcı kontrolleri

Chromium ile 1440 piksel masaüstü ve 390 piksel mobil genişlikte test edildi. Açık/koyu tema, logo, ilan listesi, kayıtlı kullanıcı girişi, ilan formu/önizleme/yayımlama ve yönetim görünümü kontrol edildi. Hayvanlar ekranındaki mobil taşma düzeltilerek yeniden kontrol edildi. Ana sayfa, hayvanlar ve ilan oluşturma ekranlarında yatay taşma bulunmadı. Tarayıcı iş akışında JavaScript çalışma zamanı hatası oluşmadı.

Harita için konum/rota noktası seçimi tarayıcı kontrolüne dahildir. Bu kontrolde dış harita parçası istekleri engellendi; dış sağlayıcının kullanılabilirliği doğrulanmış sayılmaz. Koordinat gizliliği ayrıca gerçek sunucu API'sinde test edildi.

## Bu ortamda doğrulanmayanlar

- Windows/WampServer'ın kendi Apache yapılandırması ve `.htaccess` uygulaması. README'deki 403 kontrollerini kendi kurulumunuzda yapın.
- MySQL 8 üzerinde ayrı çalışma; yürütme testi MariaDB 10.11 üzerinde yapıldı. SQL, MySQL uyumlu sözdizimi kullanır.
- Gerçek SMTP e-posta teslimi; paket yerel kod moduyla gelir.
- OpenStreetMap hizmetinin internet erişimi ve dış servis sürekliliği.
- Yük/stres testi, kapsamlı bağımsız güvenlik denetimi, tüm ekranların bütün cihazlarda erişilebilirlik sertifikasyonu.

Bu kayıt, belirtilen testlerin sonucudur; her koşulda hatasız çalışma garantisi değildir.

## 25 Eylül — Site rehberi güncellemesi

Chromium tarayıcı testi: ziyaretçinin ilk açılışı, hesapla ilk girişte otomatik başlatma, yönetici dahil 29 adımın tüm hedeflerinin bulunması, ileri/geri, Escape, kapatma, tamamlanmayı hatırlama, tekrar açma ve bölüm seçimi geçti. 390 px mobil genişlikte rehber kartının ekran sınırları içinde kalması ve gizli filtrelerin rehber sırasında görünmesi doğrulandı. JavaScript çalışma zamanı hatası görülmedi. Rehber veritabanı değişikliği yapmaz.
