# Emlak Delfino - Proje Detaylı Analiz

## 📋 Proje Genel Bakış

**Emlak Delfino**, kapsamlı bir emlak platformudur. React frontend ve PHP backend kullanarak geliştirilmiş, modern web teknolojileri ile inşa edilmiştir. Platform, emlak ilanları, kullanıcı yönetimi, admin paneli ve kapsamlı onay sistemi içerir.

### Temel Özellikler
- **Çok Rollü Kullanıcı Sistemi** (Kullanıcı, Emlakçı, Admin, Süper Admin)
- **Emlak İlan Yönetimi** (CRUD işlemleri, çoklu resim yükleme)
- **Admin Onay Sistemi** (İlanlar için onay/red mekanizması)
- **Gelişmiş Arama ve Filtreleme**
- **Favoriler Sistemi**
- **Gerçek Zamanlı Bildirimler**
- **İletişim ve Mesajlaşma Sistemi**
- **Kapsamlı İstatistikler ve Raporlama**

---

## 🏗️ Teknik Mimari

### Backend (PHP)
- **Framework**: Vanilla PHP
- **API Mimarisi**: RESTful API
- **Veritabanı**: MySQL (23 tablo)
- **Kimlik Doğrulama**: JWT Token
- **Dosya Yükleme**: Çoklu resim yükleme sistemi
- **Güvenlik**: Role-based middleware, validation

### Frontend (React)
- **Framework**: React 19.1.0
- **Routing**: React Router DOM 7.6.2
- **State Management**: React Hooks (useState, useEffect)
- **Styling**: CSS Modules + Custom CSS
- **API İletişimi**: Fetch API ile custom service layer

### Veritabanı Tasarımı
23 tablo ile kapsamlı veri modeli:
- Kullanıcı yönetimi (users, roles, role_requests)
- Lokasyon hiyerarşisi (cities, districts, neighborhoods)
- Emlak yönetimi (properties, property_types, property_status, property_images)
- İletişim sistemi (contact_messages, property_inquiries, notifications)
- İstatistik ve analitik (property_views, property_statistics)
- Sistem yönetimi (settings, password_reset_tokens, email_verification_tokens)

---

## 🔐 Kullanıcı Rolleri ve Yetkilendirme

### 1. Kullanıcı (role_id: 1)
- İlan görüntüleme (fiyat gizli)
- Favorilere ekleme
- İletişim formları
- Profil yönetimi
- Rol yükseltme talebi

### 2. Emlakçı (role_id: 2)
- Tüm kullanıcı yetkileri +
- İlan oluşturma/düzenleme/silme
- Fiyat görüntüleme
- Kendi ilanlarını yönetme
- İlan istatistikleri

### 3. Admin (role_id: 3)
- Tüm emlakçı yetkileri +
- İlan onay/red sistemi
- Kullanıcı yönetimi
- Sistem ayarları
- İstatistik raporları
- Rol talepleri değerlendirme

### 4. Süper Admin (role_id: 4)
- Tüm sistem yetkileri
- Admin kullanıcı yönetimi
- Sistem-level konfigürasyonlar

---

## 📡 Backend API Endpoints

### Authentication Endpoints
- `POST /api/auth/register` - Kullanıcı kaydı
- `POST /api/auth/login` - Giriş yapma
- `GET /api/auth/me` - Profil bilgileri
- `PUT /api/auth/profile` - Profil güncelleme
- `POST /api/auth/logout` - Çıkış yapma

### Property Endpoints
- `GET /api/properties` - İlan listesi (filtreleme ve sayfalama)
- `GET /api/properties/{id}` - İlan detayı
- `POST /api/properties` - Yeni ilan (Emlakçı+)
- `PUT /api/properties/{id}` - İlan güncelleme
- `DELETE /api/properties/{id}` - İlan silme

### Location Endpoints
- `GET /api/cities` - Şehir listesi
- `GET /api/districts/{city_id}` - İlçe listesi
- `GET /api/neighborhoods/{district_id}` - Mahalle listesi
- `GET /api/locations/hierarchy` - Konum hiyerarşisi

### Admin Endpoints
- `GET /api/admin/dashboard` - Admin dashboard
- `GET /api/admin/pending-properties` - Onay bekleyen ilanlar
- `PUT /api/admin/approve-property/{id}` - İlan onaylama
- `PUT /api/admin/reject-property/{id}` - İlan reddetme
- `GET /api/admin/users` - Kullanıcı yönetimi

### Image Management
- `POST /api/property-images/upload-multiple` - Çoklu resim yükleme
- `PUT /api/property-images/set-primary/{id}` - Ana resim belirleme
- `PUT /api/property-images/update-order` - Resim sırası güncelleme
- `DELETE /api/property-images/{id}` - Resim silme

### Notification System
- `GET /api/notifications` - Bildirim listesi
- `GET /api/notifications/unread-count` - Okunmamış sayısı
- `PUT /api/notifications/mark-all-read` - Tümünü okundu işaretle

---

## 💻 Frontend Sayfalar ve Bileşenler

### Ana Sayfalar
1. **HomePage** - Hero section, öne çıkan ilanlar, istatistikler
2. **PropertiesPage** - Gelişmiş filtreleme ile ilan listesi
3. **PropertyDetailPage** - Detaylı ilan görünümü, resim galerisi
4. **LoginPage** - Kimlik doğrulama (test kullanıcıları dahil)
5. **RegisterPage** - Kullanıcı kaydı
6. **ProfilePage** - Kullanıcı profil yönetimi

### Kullanıcı Panelleri
7. **MyPropertiesPage** - Kullanıcının ilanları
8. **FavoritesPage** - Favori ilanlar
9. **AddPropertyPage** - Yeni ilan ekleme
10. **EditPropertyPage** - İlan düzenleme
11. **AccountSettingsPage** - Hesap ayarları

### Admin Paneli
12. **AdminPanelPage** - Admin dashboard ve ilan onay sistemi
13. **AdminPropertyDetailPage** - Admin için detaylı ilan görünümü

### Yeniden Kullanılabilir Bileşenler
- **PropertyCard** - İlan kartı bileşeni
- **ImageUploader** - Drag-drop resim yükleme
- **NotificationBell** - Gerçek zamanlı bildirimler
- **ContactForm** - İletişim modal'ı
- **ReportForm** - İlan şikayet formu
- **SimilarProperties** - Benzer ilanlar

---

## 🗂️ Veritabanı Şeması

### Temel Tablolar
- **users** - Kullanıcı bilgileri ve yetkilendirme
- **properties** - Emlak ilanları (30+ alan)
- **property_images** - İlan resimleri ve sıralama
- **roles** - Kullanıcı rolleri tanımları
- **role_requests** - Rol yükseltme talepleri

### Lokasyon Hiyerarşisi
- **cities** - Şehirler
- **districts** - İlçeler
- **neighborhoods** - Mahalleler

### İletişim ve Bildirimler
- **notifications** - Sistem bildirimleri (JSON data)
- **contact_messages** - Genel iletişim formları
- **property_inquiries** - İlana özel sorular

### İstatistik ve Analitik
- **property_views** - İlan görüntüleme takibi
- **property_statistics** - Günlük/aylık istatistikler
- **favorites** - Kullanıcı favorileri

---

## 🔧 Özel Özellikler

### 1. İlan Onay Sistemi
- Yeni ilanlar admin onayı bekler
- Onay/red işlemi bildirim sistemi ile
- Admin dashboard'da toplu onay işlemleri
- Onay ayarlarını açma/kapama

### 2. Çoklu Resim Yönetimi
- Drag-drop ile çoklu yükleme
- Resim sıralama ve ana resim belirleme
- Otomatik dosya organizasyonu (yıl/ay)
- Resim optimizasyonu ve validasyon

### 3. Gelişmiş Filtreleme
- Fiyat aralığı, oda sayısı, metrekare
- Lokasyon bazlı filtreleme (şehir/ilçe/mahalle)
- Özellik bazlı filtreleme (balkon, asansör, vb.)
- Gerçek zamanlı arama sonuçları

### 4. Rol Yükseltme Sistemi
- Kullanıcıdan emlakçıya geçiş talebi
- Doküman yükleme ve admin onayı
- Şirket bilgileri ve lisans takibi

### 5. Bildirim Sistemi
- Gerçek zamanlı bildirimler
- Okundu/okunmadı durumu
- Bildirim tipleri ve önceliklendirme
- Toplu bildirim gönderimi (admin)

---

## 📊 İstatistik ve Raporlama

### Kullanıcı İstatistikleri
- İlan görüntüleme sayıları
- Favori ekleme oranları
- Kullanıcı aktivite geçmişi

### Admin İstatistikleri
- Sistem geneli özet veriler
- En popüler şehir/bölgeler
- En aktif emlakçılar
- Aylık trend analizleri
- Özel tarih aralığı raporları

### Performans Metrikleri
- API rate limiting tablosu
- Veritabanı indeksleme optimizasyonu
- Resim yükleme ve organizasyon
- Arama performansı (FULLTEXT index)

---

## 🔒 Güvenlik Özellikleri

### Authentication & Authorization
- JWT token bazlı kimlik doğrulama
- Role-based access control (RBAC)
- Password hashing (PHP password_hash)
- Email doğrulama sistemi

### API Güvenliği
- CORS yapılandırması
- Request validation middleware
- Rate limiting (IP bazlı)
- SQL injection koruması (PDO prepared statements)

### Dosya Güvenliği
- Upload dosya tipi validasyonu
- Dosya boyutu kısıtlamaları
- Güvenli dosya adlandırma
- Public erişim kontrolleri

---

## 🚀 Deployment ve Konfigürasyon

### Backend Konfigürasyon
- **Veritabanı**: `backend/config/database.php`
- **API Endpoint**: `/backend/api/index.php`
- **Dosya Yükleme**: `uploads/properties/YYYY/MM/`
- **JWT Secret**: Güvenli token imzalama

### Frontend Konfigürasyon
- **Development**: `npm start` (Port 3000)
- **Production Build**: `npm run build`
- **API Base URL**: Backend endpoint konfigürasyonu
- **Route Protection**: JWT token validation

### Production Gereksinimleri
- **PHP 7.4+** PDO MySQL desteği ile
- **MySQL 5.7+** JSON field desteği ile
- **Node.js 14+** React build için
- **Web Server**: Apache/Nginx (CORS desteği)

---

Bu analiz, Emlak Delfino projesinin kapsamlı bir genel bakışını sunmaktadır. Proje, modern web geliştirme standartları ile inşa edilmiş, ölçeklenebilir ve güvenli bir emlak platformudur.