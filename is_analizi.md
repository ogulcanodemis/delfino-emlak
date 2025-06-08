# Emlak-Delfino İş Analizi Dokümanı

## 1. Proje Özeti
Emlak-Delfino, emlak ilanlarının listelenmesi ve yönetilmesi için geliştirilen bir web platformudur. Bu platform, ziyaretçilerin ilanları görebileceği, kayıtlı kullanıcıların detaylı bilgilere erişebileceği ve süper admin tarafından yetkilendirilen kullanıcıların ilan ekleyebileceği bir yapıya sahiptir. Proje React (frontend), PHP (backend) ve MySQL (veritabanı) teknolojileri kullanılarak geliştirilecektir.

## 2. Kullanıcı Rolleri ve İzinleri

### 2.1. Ziyaretçi (Kayıtsız Kullanıcı)
- İlanları listeleyebilir ve görebilir
- İlan detaylarını görebilir (fiyat ve emlakçı iletişim bilgileri hariç)
- Arama ve filtreleme yapabilir
- Kayıt olabilir
- Giriş yapabilir

### 2.2. Kayıtlı Kullanıcı
- İlanları listeleyebilir ve görebilir
- İlan detaylarının tamamını görebilir (fiyat ve emlakçı iletişim bilgileri dahil)
- Arama ve filtreleme yapabilir
- Profil bilgilerini düzenleyebilir
- Favorilere ilan ekleyebilir
- Emlakçı olma talebi gönderebilir (Süper Admin onayı gerekir)

### 2.3. Emlakçı (Yetkilendirilmiş Kullanıcı)
- Kayıtlı kullanıcı yetkilerine sahiptir
- İlan ekleyebilir, düzenleyebilir ve silebilir
- Kendi ilanlarını yönetebilir
- İlanlarına gelen ilgi ve istatistikleri görebilir

### 2.4. Süper Admin
- Tüm sistem üzerinde tam yetkiye sahiptir
- Kullanıcıları yönetebilir (ekleme, düzenleme, silme, rol atama)
- Emlakçı olma taleplerini onaylayabilir veya reddedebilir
- Tüm ilanları yönetebilir (onaylama, reddetme, düzenleme, silme)
- Sistem ayarlarını yapılandırabilir

## 3. Kullanıcı Hikayeleri

### 3.1. Ziyaretçi Hikayeleri
- Ziyaretçi olarak, emlak ilanlarını görebilmeliyim.
- Ziyaretçi olarak, ilanları filtreleyebilmeliyim (konum, emlak tipi, vb.).
- Ziyaretçi olarak, ilan detaylarını görebilmeliyim (fiyat ve emlakçı iletişim bilgileri hariç).
- Ziyaretçi olarak, sisteme kayıt olabilmeliyim.
- Ziyaretçi olarak, kayıtlı kullanıcı hesabıyla giriş yapabilmeliyim.

### 3.2. Kayıtlı Kullanıcı Hikayeleri
- Kayıtlı kullanıcı olarak, emlak ilanlarını detaylı görebilmeliyim (fiyat ve emlakçı bilgileri dahil).
- Kayıtlı kullanıcı olarak, profil bilgilerimi düzenleyebilmeliyim.
- Kayıtlı kullanıcı olarak, favori ilanlar listesi oluşturabilmeliyim.
- Kayıtlı kullanıcı olarak, emlakçıyla iletişime geçebilmeliyim.
- Kayıtlı kullanıcı olarak, emlakçı olma talebi gönderebilmeliyim.

### 3.3. Emlakçı Hikayeleri
- Emlakçı olarak, yeni emlak ilanları ekleyebilmeliyim.
- Emlakçı olarak, kendi ilanlarımı düzenleyebilmeliyim.
- Emlakçı olarak, kendi ilanlarımı silebilmeliyim.
- Emlakçı olarak, ilanlarıma gelen ilgiyi ve istatistikleri görebilmeliyim.

### 3.4. Süper Admin Hikayeleri
- Süper admin olarak, tüm kullanıcıları yönetebilmeliyim.
- Süper admin olarak, kullanıcılara rol atayabilmeliyim.
- Süper admin olarak, emlakçı olma taleplerini yönetebilmeliyim.
- Süper admin olarak, tüm ilanları yönetebilmeliyim.
- Süper admin olarak, sistem ayarlarını yapılandırabilmeliyim.

## 4. Veritabanı Modeli

### 4.1. Users (Kullanıcılar) Tablosu
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- name (VARCHAR) - Kullanıcının adı soyadı
- email (VARCHAR, UNIQUE) - E-posta adresi
- password (VARCHAR) - Şifre (hash'lenmiş)
- phone (VARCHAR) - Telefon numarası
- address (TEXT) - Adres
- role_id (INT, FOREIGN KEY) - Kullanıcı rolü (1: Kayıtlı Kullanıcı, 2: Emlakçı, 3: Süper Admin)
- status (TINYINT) - Hesap durumu (0: Pasif, 1: Aktif)
- email_verified_at (TIMESTAMP) - E-posta doğrulama tarihi
- created_at (TIMESTAMP) - Oluşturma tarihi
- updated_at (TIMESTAMP) - Güncelleme tarihi

### 4.2. Roles (Roller) Tablosu
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- name (VARCHAR) - Rol adı
- description (TEXT) - Rol açıklaması
- created_at (TIMESTAMP) - Oluşturma tarihi
- updated_at (TIMESTAMP) - Güncelleme tarihi

### 4.3. Role_Requests (Rol Talepleri) Tablosu
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- user_id (INT, FOREIGN KEY) - Talepte bulunan kullanıcı
- requested_role_id (INT, FOREIGN KEY) - Talep edilen rol
- status (TINYINT) - Talep durumu (0: Beklemede, 1: Onaylandı, 2: Reddedildi)
- note (TEXT) - Talep notu
- created_at (TIMESTAMP) - Oluşturma tarihi
- updated_at (TIMESTAMP) - Güncelleme tarihi

### 4.4. Properties (Emlak İlanları) Tablosu
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- user_id (INT, FOREIGN KEY) - İlanı ekleyen kullanıcı
- title (VARCHAR) - İlan başlığı
- description (TEXT) - İlan açıklaması
- price (DECIMAL) - Fiyat
- property_type_id (INT, FOREIGN KEY) - Emlak tipi (1: Konut, 2: İşyeri, 3: Arsa, vb.)
- status_id (INT, FOREIGN KEY) - İlan durumu (1: Satılık, 2: Kiralık)
- address (TEXT) - Adres
- city (VARCHAR) - Şehir
- district (VARCHAR) - İlçe
- neighborhood (VARCHAR) - Mahalle
- latitude (DECIMAL) - Enlem
- longitude (DECIMAL) - Boylam
- area (INT) - Metrekare
- rooms (INT) - Oda sayısı
- bathrooms (INT) - Banyo sayısı
- floor (INT) - Bulunduğu kat
- building_age (INT) - Bina yaşı
- heating_type (VARCHAR) - Isıtma tipi
- is_active (TINYINT) - İlan aktif mi (0: Pasif, 1: Aktif)
- view_count (INT) - Görüntülenme sayısı
- created_at (TIMESTAMP) - Oluşturma tarihi
- updated_at (TIMESTAMP) - Güncelleme tarihi

### 4.5. Property_Types (Emlak Tipleri) Tablosu
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- name (VARCHAR) - Tip adı (Daire, Villa, Arsa, vb.)
- created_at (TIMESTAMP) - Oluşturma tarihi
- updated_at (TIMESTAMP) - Güncelleme tarihi

### 4.6. Property_Status (Emlak Durumları) Tablosu
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- name (VARCHAR) - Durum adı (Satılık, Kiralık)
- created_at (TIMESTAMP) - Oluşturma tarihi
- updated_at (TIMESTAMP) - Güncelleme tarihi

### 4.7. Property_Images (Emlak Görselleri) Tablosu
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- property_id (INT, FOREIGN KEY) - İlgili emlak ilanı
- image_path (VARCHAR) - Görsel dosya yolu
- is_main (TINYINT) - Ana görsel mi (0: Hayır, 1: Evet)
- created_at (TIMESTAMP) - Oluşturma tarihi
- updated_at (TIMESTAMP) - Güncelleme tarihi

### 4.8. Favorites (Favoriler) Tablosu
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- user_id (INT, FOREIGN KEY) - Kullanıcı
- property_id (INT, FOREIGN KEY) - Emlak ilanı
- created_at (TIMESTAMP) - Oluşturma tarihi
- updated_at (TIMESTAMP) - Güncelleme tarihi

## 5. API Endpoint'leri

### 5.1. Kullanıcı İşlemleri
- POST /api/auth/register - Yeni kullanıcı kaydı
- POST /api/auth/login - Kullanıcı girişi
- GET /api/auth/me - Mevcut kullanıcı bilgilerini getir
- PUT /api/auth/profile - Kullanıcı profil bilgilerini güncelle
- POST /api/auth/logout - Kullanıcı çıkışı
- POST /api/role-requests - Rol talebi oluştur

### 5.2. Emlak İlanları
- GET /api/properties - Tüm ilanları listele (filtreleme seçenekleriyle)
- GET /api/properties/{id} - Belirli bir ilanın detaylarını getir
- POST /api/properties - Yeni ilan ekle (sadece emlakçı ve süper admin)
- PUT /api/properties/{id} - İlan güncelle (sadece ilan sahibi ve süper admin)
- DELETE /api/properties/{id} - İlan sil (sadece ilan sahibi ve süper admin)
- POST /api/properties/{id}/images - İlana görsel ekle
- DELETE /api/properties/{id}/images/{image_id} - İlandan görsel sil

### 5.3. Favoriler
- GET /api/favorites - Kullanıcının favori ilanlarını getir
- POST /api/favorites - Favorilere ilan ekle
- DELETE /api/favorites/{property_id} - Favorilerden ilan çıkar

### 5.4. Admin İşlemleri
- GET /api/admin/users - Tüm kullanıcıları listele
- GET /api/admin/users/{id} - Belirli bir kullanıcının detaylarını getir
- PUT /api/admin/users/{id} - Kullanıcı bilgilerini güncelle
- DELETE /api/admin/users/{id} - Kullanıcı sil
- GET /api/admin/role-requests - Rol taleplerini listele
- PUT /api/admin/role-requests/{id} - Rol talebini güncelle (onay/red)

## 6. Kullanıcı Arayüzü Tasarımı

### 6.1. Genel Sayfalar
- Ana Sayfa - Öne çıkan ilanlar, son eklenen ilanlar, filtreleme seçenekleri
- İlan Listeleme - Tüm ilanlar, filtreleme ve sıralama özellikleriyle
- İlan Detay - İlan bilgileri, görseller, harita, emlakçı bilgileri (rol bazlı)
- Hakkımızda - Site hakkında bilgiler
- İletişim - İletişim bilgileri ve iletişim formu

### 6.2. Kullanıcı Sayfaları
- Giriş - Kullanıcı girişi
- Kayıt - Yeni kullanıcı kaydı
- Profil - Kullanıcı bilgileri düzenleme
- Favoriler - Favori ilanlar listesi
- Rol Talebi - Emlakçı olma talebi oluşturma

### 6.3. Emlakçı Sayfaları
- İlan Yönetimi - İlanları listeleme, ekleme, düzenleme, silme
- İlan Ekleme/Düzenleme - İlan bilgileri formu, görsel yükleme
- İlan İstatistikleri - İlan görüntüleme ve etkileşim istatistikleri

### 6.4. Admin Sayfaları
- Kontrol Paneli - Genel istatistikler ve özet bilgiler
- Kullanıcı Yönetimi - Kullanıcıları listeleme, düzenleme, silme
- Rol Talepleri - Rol taleplerini görüntüleme, onaylama, reddetme
- İlan Yönetimi - Tüm ilanları yönetme
- Sistem Ayarları - Genel site ayarlarını yapılandırma

## 7. Güvenlik Gereksinimleri

### 7.1. Kimlik Doğrulama ve Yetkilendirme
- JWT (JSON Web Token) tabanlı kimlik doğrulama
- Rol tabanlı erişim kontrolü
- Şifre hashleme (Bcrypt)
- E-posta doğrulama
- Şifremi unuttum işlevi

### 7.2. Veri Güvenliği
- Input validasyonu ve sanitizasyonu
- SQL enjeksiyon koruması
- XSS (Cross-Site Scripting) koruması
- CSRF (Cross-Site Request Forgery) koruması
- Hassas verilerin şifrelenmesi

### 7.3. Altyapı Güvenliği
- HTTPS kullanımı
- Rate limiting
- Firewall koruması
- Düzenli güvenlik güncellemeleri
- Veritabanı yedekleme ve kurtarma planı

## 8. Performans Gereksinimleri
- Sayfa yüklenme süresi < 2 saniye
- API yanıt süresi < 500ms
- CDN kullanımı (görseller için)
- Veritabanı optimizasyonu
- Lazy loading ve code splitting (React)

## 9. Proje Aşamaları ve Zaman Çizelgesi

### 9.1. Planlama ve Analiz (1 Hafta)
- İş gereksinimlerinin belirlenmesi
- Veritabanı tasarımı
- API endpoint'lerinin planlanması
- Teknoloji stack'inin kesinleştirilmesi

### 9.2. Frontend Geliştirme (4 Hafta)
- Sayfa şablonlarının oluşturulması
- Bileşenlerin geliştirilmesi
- API entegrasyonu
- Responsive tasarım implementasyonu

### 9.3. Backend Geliştirme (4 Hafta)
- Veritabanı şemasının oluşturulması
- API endpoint'lerinin geliştirilmesi
- Kimlik doğrulama ve yetkilendirme mekanizmalarının kurulması
- Veri işleme ve validasyon

### 9.4. Test ve Hata Düzeltme (2 Hafta)
- Birim testleri
- Entegrasyon testleri
- Kullanıcı arayüzü testleri
- Performans testleri
- Güvenlik testleri

### 9.5. Canlıya Alma ve Bakım (1 Hafta)
- Sunucu yapılandırması
- Veritabanı kurulumu
- Uygulama dağıtımı
- Kullanıcı eğitimi
- Düzenli bakım planı

## 10. Teknik Altyapı

### 10.1. Frontend
- React.js
- React Router (sayfa yönlendirme)
- Redux veya Context API (durum yönetimi)
- Axios (API istekleri)
- Bootstrap veya Material-UI (UI bileşenleri)
- SCSS/SASS (stil)
- React-Leaflet (harita entegrasyonu)
- React Dropzone (dosya yükleme)

### 10.2. Backend
- PHP 8.x
- MySQL 8.x
- PDO (veritabanı bağlantısı)
- JWT (kimlik doğrulama)
- Composer (paket yönetimi)

### 10.3. Sunucu Gereksinimleri
- Linux tabanlı sunucu
- Apache veya Nginx web sunucusu
- PHP 8.x
- MySQL 8.x
- SSL sertifikası
- Yeterli depolama alanı (özellikle görseller için)

## 11. Gelecek Geliştirmeler
- Mobil uygulama
- İleri düzey arama özellikleri (harita üzerinde arama)
- Emlak değerleme aracı
- Sanal tur entegrasyonu
- Çoklu dil desteği
- Çevrimiçi ödeme entegrasyonu (premium ilanlar için)
- Bildirim sistemi
- Sohbet özelliği (emlakçı-kullanıcı iletişimi için)

## 12. Ek Bilgiler

### 12.1. Hedef Kitle
- Ev/işyeri arayanlar
- Emlak satmak/kiralamak isteyenler
- Profesyonel emlakçılar
- Gayrimenkul yatırımcıları

### 12.2. Rakip Analizi
- Emlak siteleri ve uygulamalarının güçlü ve zayıf yönleri
- Pazardaki boşluklar ve fırsatlar
- Rekabet avantajları

### 12.3. Pazarlama Stratejisi
- SEO optimizasyonu
- Sosyal medya kampanyaları
- E-posta pazarlaması
- İçerik pazarlaması (blog yazıları, emlak rehberleri)
- Kullanıcı memnuniyeti ve sadakat programları 