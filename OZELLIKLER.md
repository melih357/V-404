# V-404 kapsam ve uygulama notları

Bu dosya gönderilen KAPSAM.md ile uygulamanın modüllerini eşleştirir.

| İstenen bölüm | Uygulama |
|---|---|
| Ana sayfa ve ziyaretçi araması | Kaybettim, Buldum, Ücretsiz Paylaş, Hayvanlar; anahtar kelime, kategori, şehir, yaklaşık bölge, kurum, tarih ve yayın alanı filtreleri; sayfalama |
| Hesap ve doğrulama | E-posta/parola kaydı, 6 haneli süreli doğrulama, giriş/çıkış, görünen ad, parola ve eşleşme bildirimi tercihi |
| Çocukların kullanımı | Ziyaretçi incelemesi açık; işlem hesabı yetişkin/veli/yetkili beyanı gerektirir. Bağımsız çocuk hesabı ve denetimsiz çocuk sohbeti yok |
| Kayıp ilanı | Kategori, açıklama, tarih, şehir/bölge, çoklu fotoğraf, renk/marka/model, gizli ayrıntı, rota ve sefer metni |
| Bulunan ilanı | Genel muhafaza bilgisi, gizli teslim notu, kurum içi konum; para/hediye istememe kuralı |
| Yayın alanı | Genel ağ, onaylı üyeliği bulunan kurum, her ikisi; tek kayıt ve tek durum |
| Eşleştirme | Kategori, şehir ve yakın tarih ile aday bulma; renk/marka/model/bölge/metin, rota bölge adları, yakın koordinat ve rota noktalarıyla puanlama; olasılık gösterimi |
| Harita | İsteğe bağlı konum ve özel rota seçme; ziyaretçiye yaklaşık konum, yetkiliye ayrıntı; lacivert rota, kırmızı konum, yeşil bulunan eşya noktası |
| Bulunursa haber ver | İlan bazında ve profil genelinde kapatma, tek çift için yinelenmeyen eşleşme bildirimi |
| Sahiplik talebi | Bulunan eşya / hayvan için gizli kanıt, adayları ayrı değerlendirme, yetkili onayı/ret |
| Özel sohbet | Yalnızca kabul edilen ilana bağlı talebin tarafları ve yetkili kurum görevlisi; 6 saniyelik yenileme; kapatma, engelleme ve şikâyet |
| Teslim | Alan ve verenin ayrı onayı; tek onayda açık kalan işlem; yetkili kurum çalışanı için doğrudan teslim kaydı |
| Ücretsiz paylaşım | Eşyanın durumu, fotoğraflar, yaklaşık teslim bölgesi, başvurular arasından alıcı seçme, ayırma ve teslim |
| Bulunan eşyayı paylaşmaya çevirme yasağı | İlan türü sonradan değiştirilemez; sunucu bunu denetler |
| Hayvanlar | Kayıp, bulunan, ücretsiz sahiplendirme; tür, bakım ve bilinen sağlık alanları; ayrı yönetim kuyruğu |
| Kurum yapısı | Kurum → şube/kampüs → bina → kat → bölüm/oda; hiyerarşi sunucuda doğrulanır |
| Kurum üyeliği | Platformca onaylı kurumlar, üyelik başvurusu/onayı, kriptografik tek kullanımlık ve süreli davet kodu, kurumdan ayrılma |
| Kurum yetkileri | Kurum yöneticisi, konum kapsamlı yer yöneticisi, konum kapsamlı çalışan, normal üye |
| Platform yönetimi | Kullanıcı rolü/durumu, kurum onayı, ilan moderasyonu, hayvan kuyruğu, şikâyet, destek, duyuru, kayıt ayarı, işlem kayıtları |
| Güvenlik moderatörü | İlan ve şikâyet incelemesi, şikâyet edilen sohbete kayıtlı erişim, sohbet kapatma, normal hesabı kısıtlama |
| Şikâyet / engelleme | İlan, hesap ve sohbet raporu; belirtilen nedenler; açık/inceleniyor/çözüldü durumları, kullanıcıya yanıt ve bildirim |
| Destek / geri bildirim | Ayrı kayıt ve yönetici yanıtı, görsel ekleme, kullanıcı durum takibi |
| Fotoğraf gizliliği | Çoklu görsel, elle alan kapatma, yayımlanacak içeriği önizleme, GD ile yeniden kodlama/EXIF temizleme |
| İlan süreleri | 90 / 60 / 30 günlük tür bazlı süre, arşivleme, uzatma, erken kapatma/silme, arşivde eşleşmenin durması |
| Saklama | Günlük CLI bakım görevi, süreli özel alan/sohbet/fotoğraf temizleme, doğrulanmış silme talebi için operatör anonimleştirme komutu |
| Tasarım | Verilen lacivert/kırmızı kimlik, nötr yüzeyler, açık varsayılan ve koyu tema, mobil düzen, klavye odağı ve metinli durum etiketleri |

## Açıkça belirtilen uygulama kararları

- **Yerel doğrulama:** Kod yerel geliştirme ekranında görünür. Gerçek e-posta teslimi için PHP mail/SMTP kurulumu gerekir. Telefon/SMS doğrulaması eklenmemiştir; tanımdaki “e-posta veya telefon” seçeneği e-posta ile uygulanır.
- **Eşleştirme:** Açıklanabilir kural puanı kullanılır. Fotoğrafı analiz eden yapay zekâ veya kusursuz eşleşme garantisi yoktur. Tarih adayı ±30 gün, kategori ve şehir eşitliğiyle daraltılır; en az 60 puan alan aday gösterilir.
- **Harita ve konum:** İsteğe bağlı Leaflet/OpenStreetMap haritasında ilgili nokta ve en fazla 30 rota noktası seçilir. Rota lacivert, ilgili nokta kırmızı, bulunan eşya noktası koyu yeşildir. Ham koordinat ve rota yalnızca ilan sahibi/konum yetkisi olan görevliye gösterilir. Ziyaretçinin koordinatı iki ondalığa yuvarlanır ve yaklaşık bölge halkası çizilir. Harita, kullanıcı açmayı seçince dış sağlayıcıdan harita parçalarını yükler; internet gerektirir. İnternet olmadan metin konumu ve diğer temel işlevler kullanılabilir. Canlı GPS, otomatik takip veya yol tarifi hizmeti yoktur.
- **Fotoğraf:** Gizleme kullanıcı tarafından dikdörtgen alan kapatma yoluyla yapılır. Otomatik yüz/plaka/kart numarası algılama yoktur. Son kontrol kullanıcıya aittir.
- **Çocuk güvenliği:** Veli/yetkili beyanı ile yetişkin hesabı kullanılır. Resmî yaş/kimlik doğrulama servisi bağlı değildir.
- **Gizli ayrıntı:** Kullanıcı sahiplik talebinde kanıtını kendisi yazar. Karşı tarafın gizli yanıtı adaylara gösterilmez. Başvuru kanıtını ilan sahibi ve ilgili kurum görevlisi görebilir.
- **Moderasyon:** Yasaklı paylaşım için temel sözcük/iletişim alanı kontrolleri ve insan inceleme kuyruğu vardır; otomatik denetim her ihlali tespit edemez.
- **Kurum doğrulama:** Başvuruyu platform yöneticisi manuel inceler. Harici kurum siciline bağlantı yoktur.
- **Sohbet:** WebSocket yerine 6 saniyelik yenileme kullanılır; Wamp'ta ek süreç gerektirmez.
- **Yönetim listeleri:** Platform yönetim listelerinde son 500 kayıt görüntülenir. Kullanıcı ilan araması 12'li sayfalıdır.
- **Çevrimdışı çalışma:** Kayıt, ilan, arama, kurum, moderasyon ve yerel bildirimler dış servis olmadan çalışacak şekilde hazırlanmıştır. Gerçek e-posta ayrı sunucu yapılandırması, isteğe bağlı harita ise internet bağlantısı ister.

## Sunucu yetki sınırları

Kurum üyeliği, kurum yöneticiliğiyle aynı değildir. Çalışan ve yer yöneticisi yalnızca atandığı konumun alt ağacında işlem yapabilir. Dış kullanıcı kurum içi konumu alamaz. İlan sahibi kendi kayıtlarını yönetebilir. Platform moderatörü rastgele özel görüşme listesini alamaz; şikâyet kaydı üzerinden incelediğinde erişim audit kaydına yazılır. Özel fotoğrafların adresini bilmek erişim sağlamaz; fotoğraf API'si ilan/talep yetkisini denetler.

## Etkileşimli tanıtım rehberi

İlk ziyaret/girişte otomatik açılır. Hedef alan vurgusu, adım sayacı, ileri/geri, bölüm seçimi, atlama, Escape ile çıkış ve üst menüden yeniden başlatma sunar. Ziyaretçi ile giriş yapmış kullanıcı için farklı adımlar; yönetici/moderatör için yetkisine uygun ek açıklamalar vardır. Mobilde filtre alanını görünür kılar. Tercih hesap bazında bu tarayıcıda tutulur; veritabanı migration'ı gerekmez.
