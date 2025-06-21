# HOSTING GEREKSİNİMLERİ - EMLAK DELFINO PROJESİ

## 📋 PROJE ÖZET RAPORU

### Proje Yapısı
- **Backend**: PHP 7.4+ (PDO, MySQL desteği)
- **Frontend**: React.js (React 19.1.0, React Router v7)
- **Veritabanı**: MySQL 8.0+ (utf8mb4 karakter seti)
- **Dosya Yüklemeleri**: PHP ile çoklu resim yükleme sistemi

### Mevcut Durum
- Proje şu anda XAMPP localhost ortamında çalışıyor
- Backend API: `http://localhost/emlak-delfino/backend/api`
- Frontend: React development server (port 3000)
- Veritabanı: `emlak_delfino` (23 tablo)

---

## 🎯 HOSTİNG ORTAMI İÇİN GEREKLİ BİLGİLER

### 1. HOSTING SERVİS BİLGİLERİ

**Domain ve Hosting Bilgileri:**
- [ ] **Domain adı**: ___________________
- [ ] **Hosting sağlayıcısı**: ___________________
- [ ] **Hosting paketi tipi**: (Shared/VPS/Dedicated)
- [ ] **Control Panel**: (cPanel/Plesk/DirectAdmin)

**FTP/SFTP Bilgileri:**
- [ ] **FTP Host**: ___________________
- [ ] **FTP Kullanıcı Adı**: ___________________
- [ ] **FTP Şifre**: ___________________
- [ ] **FTP Port**: (varsayılan 21)

### 2. VERİTABANI BİLGİLERİ

**MySQL Veritabanı Bilgileri:**
- [ ] **MySQL Host**: (genellikle localhost)
- [ ] **Veritabanı Adı**: ___________________
- [ ] **MySQL Kullanıcı Adı**: ___________________
- [ ] **MySQL Şifre**: ___________________
- [ ] **MySQL Port**: (varsayılan 3306)

**Veritabanı Gereksinimleri:**
- MySQL 8.0+ veya MariaDB 10.3+
- utf8mb4 karakter seti desteği
- InnoDB storage engine
- Minimum 50MB veritabanı alanı

### 3. PHP VE SUNUCU GEREKSİNİMLERİ

**PHP Gereksinimleri:**
- [ ] **PHP Versiyonu**: 7.4 veya üzeri (önerilen 8.0+)
- [ ] **PDO MySQL Extension**: Aktif
- [ ] **JSON Extension**: Aktif
- [ ] **Mbstring Extension**: Aktif
- [ ] **GD Extension**: Aktif (resim işlemleri için)
- [ ] **Fileinfo Extension**: Aktif
- [ ] **OpenSSL Extension**: Aktif

**PHP Konfigürasyonu:**
- [ ] **upload_max_filesize**: minimum 10MB
- [ ] **post_max_size**: minimum 50MB
- [ ] **max_execution_time**: minimum 60 saniye
- [ ] **memory_limit**: minimum 128MB
- [ ] **file_uploads**: On

**Apache/Nginx Ayarları:**
- [ ] **URL Rewriting**: Aktif (.htaccess desteği)
- [ ] **HTTPS Desteği**: SSL sertifikası
- [ ] **CORS Headers**: API çağrıları için

### 4. DOSYA VE KLASÖR İZİNLERİ

**Yazılabilir Klasörler:**
- [ ] `uploads/` - 755 veya 777
- [ ] `uploads/properties/` - 755 veya 777
- [ ] `backend/logs/` (eğer log dosyası kullanılacaksa)

### 5. SMTP/EMAIL SERVİS BİLGİLERİ

**Email Gönderimi İçin:**
- [ ] **SMTP Host**: ___________________
- [ ] **SMTP Port**: ___________________
- [ ] **SMTP Kullanıcı Adı**: ___________________
- [ ] **SMTP Şifre**: ___________________
- [ ] **SMTP Güvenlik**: (TLS/SSL)
- [ ] **Gönderen Email**: ___________________

---

## 🔧 YAPILMASI GEREKEN DEĞİŞİKLİKLER

### 1. Backend Konfigürasyon Dosyaları

**Değiştirilecek Dosyalar:**
- `backend/config/database.php` - Veritabanı bağlantı bilgileri
- `backend/api/index.php` - URL path düzeltmeleri
- `frontend/src/services/apiService.js` - API base URL

### 2. Frontend Build ve URL Yapılandırması

**React Build İşlemi:**
- `npm run build` komutu ile production build
- Build dosyalarının hosting sunucusuna yüklenmesi
- API URL'lerinin production ortamına göre güncellenmesi

### 3. Veritabanı Migration

**Gerekli SQL Dosyaları:**
- `backend/database/create_database.sql` - Ana veritabanı yapısı
- `backend/database/property_statuses.sql` - Emlak durumları
- `backend/database/sample_properties.sql` - Örnek veriler
- `backend/database/seed_data.sql` - Temel veriler

### 4. Dosya Upload Klasör Yapısı

**Oluşturulacak Klasörler:**
```
uploads/
├── properties/
│   ├── 2024/
│   ├── 2025/
│   └── temp/
```

---

## ⚡ HIZLI KURULUM REHBERİ

### Adım 1: Hosting Bilgilerini Topla
Yukarıdaki tüm bilgileri hosting sağlayıcınızdan alın.

### Adım 2: Veritabanını Oluştur
1. Hosting control panel'den MySQL veritabanı oluşturun
2. Veritabanı kullanıcısı oluşturun ve yetkilendirin
3. `create_database.sql` dosyasını import edin

### Adım 3: Backend Dosyalarını Yükle
1. `backend/` klasörünü FTP ile yükleyin
2. `database.php` dosyasını hosting bilgilerinizle güncelleyin
3. `uploads/` klasörünü oluşturun ve izinlerini ayarlayın

### Adım 4: Frontend Build Al ve Yükle
1. Local'de `npm run build` çalıştırın
2. `build/` klasörü içeriğini domain kök dizinine yükleyin
3. API URL'lerini production ortamına göre güncelleyin

### Adım 5: SSL ve Domain Ayarları
1. SSL sertifikası yükleyin
2. HTTPS yönlendirmesi yapın
3. Domain ayarlarını kontrol edin

---

## 🔍 TEST EDİLECEK ÖZELLIKLER

### Temel İşlevsellik
- [ ] Ana sayfa yüklenmesi
- [ ] Kullanıcı kaydı ve girişi
- [ ] İlan listeleme ve detay sayfaları
- [ ] İlan ekleme (resim yükleme dahil)
- [ ] Favori ekleme/çıkarma
- [ ] İletişim formu

### Admin Panel
- [ ] Admin paneli erişimi
- [ ] İlan onay sistemi
- [ ] Kullanıcı yönetimi
- [ ] İstatistikler

### API Endpoints
- [ ] `/api/test` - Veritabanı bağlantı testi
- [ ] `/api/auth/login` - Kullanıcı girişi
- [ ] `/api/properties` - İlan listesi
- [ ] `/api/property-images/upload-multiple` - Resim yükleme

---

## 🚨 DİKKAT EDİLECEK NOKTALAR

### Güvenlik
- Veritabanı şifrelerini güçlü tutun
- Admin hesaplarını güçlü şifrelerle koruyun
- File upload güvenliği için dosya tipi kontrolü
- SQL injection koruması (hazır PDO kullanımı)

### Performans
- Resim boyutlarını optimize edin
- CDN kullanımını düşünün
- Veritabanı indexlerini kontrol edin
- Caching stratejisi belirleyin

### Backup
- Düzenli veritabanı yedeği
- Dosya yedeği (uploads klasörü)
- Kod yedeği (Git repository)

---

## 📞 DESTEK

Kurulum sırasında karşılaştığınız sorunlar için:
1. Hosting sağlayıcınızın teknik desteğine başvurun
2. PHP error loglarını kontrol edin
3. Browser developer tools'u kullanarak JavaScript hatalarını kontrol edin
4. API endpoint'lerini Postman gibi araçlarla test edin

---

**📅 Son Güncelleme:** 2025-06-18
**🔧 Hazırlayan:** Claude Code Assistant