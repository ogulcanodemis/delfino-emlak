# Emlak-Delfino Sistem Mimarisi

Bu doküman, Emlak-Delfino uygulamasının sistem mimarisini detaylı olarak açıklamaktadır. Uygulamanın bileşenleri, katmanları ve teknoloji yığını burada tanımlanmıştır.

## 1. Genel Mimari Bakış

Emlak-Delfino, modern bir web uygulaması olarak istemci-sunucu (client-server) mimarisini kullanmaktadır. Frontend ve backend arasında API tabanlı iletişim sağlanmıştır. Sistem genel olarak üç ana katmandan oluşmaktadır:

1. **Frontend (İstemci Katmanı)**: React.js tabanlı kullanıcı arayüzü
2. **Backend (Sunucu Katmanı)**: PHP tabanlı RESTful API
3. **Veritabanı Katmanı**: MySQL veritabanı

Bu mimari, yüksek ölçeklenebilirlik, bakım kolaylığı ve modülerlik sağlamaktadır.

## 2. Frontend Mimarisi

Frontend, modern bir Single Page Application (SPA) yaklaşımıyla React.js kullanılarak geliştirilmiştir.

### 2.1. Bileşenler ve Yapı

- **Bileşen Hiyerarşisi**: Atomik tasarım prensiplerine uygun şekilde (atoms, molecules, organisms, templates, pages) bileşenler oluşturulmuştur.
- **Sayfa Yönlendirme**: React Router kullanılarak SPA içerisinde sayfa geçişleri sağlanmıştır.
- **Durum Yönetimi**: Context API veya Redux kullanılarak global durum yönetimi sağlanmıştır.
- **Form Yönetimi**: Formik ve Yup kütüphaneleri kullanılarak form validasyonu ve yönetimi yapılmıştır.

### 2.2. Klasör Yapısı

```
frontend/
├── public/
│   ├── index.html
│   ├── favicon.ico
│   └── assets/
├── src/
│   ├── components/
│   │   ├── atoms/
│   │   ├── molecules/
│   │   ├── organisms/
│   │   ├── templates/
│   │   └── pages/
│   ├── context/
│   ├── hooks/
│   ├── services/
│   ├── utils/
│   ├── styles/
│   ├── App.js
│   └── index.js
├── package.json
└── README.md
```

### 2.3. Kullanılan Teknolojiler

- **React.js**: UI kütüphanesi
- **React Router**: Sayfa yönlendirme
- **Context API/Redux**: Durum yönetimi
- **Axios**: HTTP istekleri
- **Bootstrap/Material-UI**: UI bileşenleri
- **SCSS/SASS**: Stil yönetimi
- **React-Leaflet**: Harita entegrasyonu
- **React Dropzone**: Dosya yükleme
- **Chart.js**: Grafikler ve istatistikler

## 3. Backend Mimarisi

Backend, PHP ile geliştirilmiş ve RESTful API prensiplerine uygun bir yapıda tasarlanmıştır. MVC (Model-View-Controller) mimarisi kullanılmıştır.

### 3.1. Katmanlar

- **Controllers**: İstek ve yanıtları yöneten, iş mantığını çağıran kontrolörler
- **Models**: Veritabanı etkileşimi ve veri modelleri
- **Services**: İş mantığı ve karmaşık operasyonlar
- **Middleware**: Kimlik doğrulama, yetkilendirme, validasyon gibi ara katman işlemleri
- **Config**: Yapılandırma dosyaları
- **Database**: Veritabanı bağlantısı ve sorgu yönetimi

### 3.2. Klasör Yapısı

```
backend/
├── api/
│   ├── index.php
│   └── .htaccess
├── config/
│   ├── database.php
│   ├── auth.php
│   └── app.php
├── controllers/
│   ├── AuthController.php
│   ├── PropertyController.php
│   ├── UserController.php
│   ├── FavoriteController.php
│   └── AdminController.php
├── models/
│   ├── User.php
│   ├── Property.php
│   ├── PropertyImage.php
│   ├── Favorite.php
│   ├── Role.php
│   └── RoleRequest.php
├── middleware/
│   ├── AuthMiddleware.php
│   ├── RoleMiddleware.php
│   └── ValidationMiddleware.php
├── services/
│   ├── FileUploadService.php
│   ├── NotificationService.php
│   └── EmailService.php
├── database/
│   ├── migrations/
│   └── seeds/
└── utils/
    ├── Response.php
    ├── Validator.php
    └── Helper.php
```

### 3.3. Kullanılan Teknolojiler

- **PHP 8.x**: Ana programlama dili
- **PDO**: Veritabanı bağlantısı
- **JWT**: JSON Web Token tabanlı kimlik doğrulama
- **Composer**: Paket yönetimi
- **PHPMailer**: E-posta gönderimi

## 4. Veritabanı Mimarisi

Veritabanı katmanı MySQL 8.x kullanılarak tasarlanmıştır. İlişkisel veri modeli ile tablolar arası bağlantılar kurulmuştur.

### 4.1. Temel Tablolar

- **Users**: Kullanıcı bilgileri
- **Roles**: Kullanıcı rolleri
- **Role_Requests**: Rol talepleri
- **Properties**: Emlak ilanları
- **Property_Images**: İlan görselleri
- **Property_Types**: Emlak tipleri
- **Property_Status**: İlan durumları
- **Favorites**: Favori ilanlar
- **Cities**: Şehirler
- **Districts**: İlçeler
- **Neighborhoods**: Mahalleler
- **Notifications**: Bildirimler

### 4.2. Veritabanı İlişkileri

- Users - Roles: Many-to-One (Bir rol birden fazla kullanıcıya atanabilir)
- Users - Properties: One-to-Many (Bir kullanıcı birden fazla ilan ekleyebilir)
- Users - Favorites: One-to-Many (Bir kullanıcı birden fazla ilanı favorilere ekleyebilir)
- Properties - Property_Images: One-to-Many (Bir ilanın birden fazla görseli olabilir)
- Properties - Property_Types: Many-to-One (Bir emlak tipi birden fazla ilanda kullanılabilir)
- Properties - Property_Status: Many-to-One (Bir ilan durumu birden fazla ilanda kullanılabilir)
- Cities - Districts: One-to-Many (Bir şehrin birden fazla ilçesi olabilir)
- Districts - Neighborhoods: One-to-Many (Bir ilçenin birden fazla mahallesi olabilir)

### 4.3. İndeksler ve Optimizasyon

- Birincil anahtarlar (Primary Keys) tüm tablolarda tanımlanmıştır
- Yabancı anahtarlar (Foreign Keys) ilişkili alanlarda tanımlanmıştır
- Performans için gerekli alanlarda indeksler oluşturulmuştur:
  - `properties` tablosunda `user_id`, `property_type_id`, `status_id`, `city`, `district` alanları
  - `property_images` tablosunda `property_id` alanı
  - `favorites` tablosunda `user_id` ve `property_id` alanları
  - Arama ve filtreleme için kullanılan alanlarda indeksler

## 5. API Katmanı

Frontend ve backend arasındaki iletişim RESTful API prensipleri kullanılarak sağlanmıştır.

### 5.1. API Tasarım Prensipleri

- RESTful mimariye uygun endpoint tasarımı
- JWT tabanlı kimlik doğrulama ve yetkilendirme
- JSON formatında veri alışverişi
- HTTP durum kodlarının doğru kullanımı
- Pagination (sayfalama) desteği
- Filtreleme ve sıralama desteği
- Rate limiting (istek sınırlama)
- API versiyonlama

### 5.2. Kimlik Doğrulama ve Güvenlik

- JWT (JSON Web Token) tabanlı kimlik doğrulama
- Rol tabanlı erişim kontrolü (RBAC)
- Şifrelerin bcrypt ile hash'lenmesi
- HTTPS zorunluluğu
- CORS yapılandırması
- Input validasyonu ve sanitizasyonu
- API anahtar yönetimi

## 6. Dosya Depolama

Sistem içerisinde yüklenen görseller ve belgeler için dosya depolama mekanizması oluşturulmuştur.

### 6.1. Dosya Organizasyonu

```
/uploads
├── properties/
│   ├── [property_id]/
│   │   ├── main.jpg
│   │   ├── image1.jpg
│   │   └── image2.jpg
├── users/
│   ├── [user_id]/
│   │   ├── profile.jpg
│   │   └── documents/
└── temp/
```

### 6.2. Dosya İşleme

- Görsel boyutlandırma ve optimizasyon
- Güvenli dosya adlandırma
- Dosya tipi kontrolü
- Dosya boyutu sınırlaması
- Watermark ekleme (opsiyonel)

## 7. Önbellek (Cache) Mekanizması

Performansı artırmak için önbellek mekanizmaları kullanılmıştır.

### 7.1. Önbellek Stratejileri

- Veritabanı sorgularının önbelleğe alınması
- API yanıtlarının önbelleğe alınması
- Statik içeriklerin tarayıcı önbelleğinde tutulması
- Redis veya Memcached kullanımı

### 7.2. Önbellek Temizleme

- Veri değişikliklerinde ilgili önbelleğin otomatik temizlenmesi
- Zamana bağlı önbellek süresi tanımlaması
- Manuel önbellek temizleme mekanizması

## 8. Ölçeklenebilirlik

Sistem, artan kullanıcı ve trafik yüküne uyum sağlayacak şekilde ölçeklenebilir olarak tasarlanmıştır.

### 8.1. Yatay Ölçeklendirme

- Load balancer arkasında çoklu uygulama sunucuları
- Veritabanı replikasyonu ve sharding
- Stateless API tasarımı
- Dağıtık önbellek kullanımı

### 8.2. Dikey Ölçeklendirme

- Sunucu kaynaklarının (CPU, RAM) artırılması
- Veritabanı performans optimizasyonu
- Sorgu optimizasyonu
- İndeks stratejileri

## 9. İzleme ve Günlükleme

Sistemin sağlıklı çalışması ve sorunların hızlı tespiti için izleme ve günlükleme mekanizmaları kurulmuştur.

### 9.1. Günlükleme (Logging)

- Uygulama hataları
- Kullanıcı işlemleri
- API istekleri
- Veritabanı sorguları
- Güvenlik olayları

### 9.2. İzleme (Monitoring)

- Sunucu durumu ve kaynakları
- Uygulama performansı
- Veritabanı performansı
- API yanıt süreleri
- Kullanıcı oturumları

## 10. Dağıtım ve DevOps

Uygulama geliştirme, test ve dağıtım süreçleri için DevOps pratikleri uygulanmıştır.

### 10.1. Ortamlar

- Geliştirme (Development)
- Test (Staging)
- Üretim (Production)

### 10.2. CI/CD Pipeline

- Kod değişikliklerinin otomatik test edilmesi
- Statik kod analizi
- Dağıtım otomasyonu
- Sürüm kontrolü
- Geri alma (rollback) mekanizması

### 10.3. Konteynerizasyon

- Docker ile konteynerizasyon
- Docker Compose ile çoklu servis yönetimi
- Konteyner orkestrasyon (opsiyonel)

## 11. Güvenlik Önlemleri

Sistem güvenliği için çeşitli katmanlarda önlemler alınmıştır.

### 11.1. Uygulama Güvenliği

- Input validasyonu
- SQL enjeksiyon koruması
- XSS (Cross-Site Scripting) koruması
- CSRF (Cross-Site Request Forgery) koruması
- Session hijacking koruması
- Güçlü şifre politikaları

### 11.2. Altyapı Güvenliği

- Firewall yapılandırması
- Rate limiting
- DDoS koruması
- Güncel güvenlik yamalarının uygulanması
- Düzenli güvenlik taramaları

## 12. Felaket Kurtarma

Sistem kesintileri ve veri kayıplarına karşı felaket kurtarma planı oluşturulmuştur.

### 12.1. Yedekleme Stratejisi

- Veritabanı için düzenli tam ve artımlı yedekleme
- Dosya sistemi yedeklemesi
- Yedeklerin dış konumda saklanması
- Yedekleme doğrulama ve test etme prosedürleri

### 12.2. Kurtarma Planı

- Maksimum kabul edilebilir kesinti süresi (RTO - Recovery Time Objective)
- Maksimum kabul edilebilir veri kaybı (RPO - Recovery Point Objective)
- Kurtarma prosedürleri
- Fail-over mekanizmaları 