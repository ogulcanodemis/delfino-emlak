# 🚀 BK YATIRIM - YAYINA ALMA REHBERİ

## ✅ TAMAMLANAN HAZIRLIKLAR

### 1. Backend Konfigürasyonu
- ✅ `database.php` - Hosting veritabanı bilgileri güncellendi
- ✅ `api/index.php` - URL path yapısı production için hazırlandı
- ✅ Upload klasörleri oluşturuldu (`uploads/properties/`)
- ✅ Güvenlik `.htaccess` dosyaları eklendi

### 2. Frontend Konfigürasyonu  
- ✅ `apiService.js` - API URL `https://bkyatirim.com/backend/api` olarak güncellendi
- ✅ Production build alındı (`frontend/build/` klasörü hazır)

### 3. Veritabanı
- ✅ `VERITABANI_KURULUM.sql` dosyası hazırlandı
- ✅ Temel veriler (şehirler, emlak tipleri, admin kullanıcısı) dahil

---

## 📋 YAYINA ALMA ADIM ADIM KROKISI

### ADIM 1: FTP İLE DOSYA YÜKLEMELERİ

**FTP Bilgileriniz:**
- Host: `ftp://46.202.156.206`
- Kullanıcı: `u389707721.bkyatirim.com`
- Şifre: `$iTxfq%x2B;4GJt`
- Port: 21

**Yüklenecek Dosyalar:**

1. **Frontend Build Dosyaları** (Domain kök dizinine):
   ```
   frontend/build/ klasörünün İÇİNDEKİLERİ → domain kök dizini
   ```
   - `index.html`
   - `static/` klasörü
   - `manifest.json`, `favicon.ico` vb.

2. **Backend Dosyaları**:
   ```
   backend/ klasörü → /backend/
   ```
   - Tüm PHP dosyaları
   - Konfigürasyon dosyaları

3. **Upload Klasörleri**:
   ```
   uploads/ klasörü → /uploads/
   ```

4. **Güvenlik Dosyaları**:
   ```
   .htaccess → domain kök dizini
   backend/.htaccess → /backend/
   uploads/.htaccess → /uploads/
   ```

### ADIM 2: VERİTABANI KURULUMU

**MySQL Bilgileriniz:**
- Host: `localhost`
- Veritabanı: `u389707721_bkyatirim`
- Kullanıcı: `u389707721_bkdb`
- Şifre: `$iTxfq%x2B;4GJt`

**Kurulum Sırası:**
1. cPanel → MySQL Databases → phpMyAdmin
2. `u389707721_bkyatirim` veritabanını seç
3. **ÖNEMLİ:** İlk önce `backend/database/create_database.sql` dosyasını import et
4. Sonra `VERITABANI_KURULUM.sql` dosyasını import et

### ADIM 3: KLASÖR İZİNLERİ

FTP ile aşağıdaki klasörlerin izinlerini ayarlayın:
```
uploads/ → 755 veya 777
uploads/properties/ → 755 veya 777
uploads/temp/ → 755 veya 777
```

### ADIM 4: SSL VE HTTPS AYARLARI

1. Hosting control panel'den SSL sertifikası aktifleştir
2. HTTPS yönlendirmesi kontrol et (`.htaccess`'de mevcut)

---

## 🧪 TEST EDİLECEK ÖZELLIKLER

### Temel Kontroller
- [ ] `https://bkyatirim.com` → Ana sayfa yüklenmesi
- [ ] `https://bkyatirim.com/backend/api` → API durumu kontrolü
- [ ] `https://bkyatirim.com/backend/api/test` → Veritabanı bağlantı testi

### Kullanıcı Girişleri (Test)
- [ ] Admin: `admin@bkyatirim.com` / `password`
- [ ] Emlakçı: `ahmet@bkyatirim.com` / `password`

### İşlevsellik Testleri
- [ ] Kullanıcı kaydı
- [ ] Kullanıcı girişi
- [ ] İlan görüntüleme
- [ ] İlan ekleme (resim yükleme dahil)
- [ ] Admin paneli erişimi
- [ ] İlan onay sistemi

---

## 🔧 OLASI SORUNLAR VE ÇÖZÜMLERİ

### API Çağrısı Sorunları
**Sorun:** CORS hatası
**Çözüm:** `.htaccess` dosyasının doğru yüklendiğini kontrol et

**Sorun:** 404 API hatası  
**Çözüm:** URL rewriting aktif mi kontrol et

### Veritabanı Sorunları
**Sorun:** Bağlantı hatası
**Çözüm:** `database.php` dosyasındaki bilgileri kontrol et

**Sorun:** Tablo bulunamadı
**Çözüm:** SQL dosyalarının doğru sırayla import edildiğini kontrol et

### Dosya Yükleme Sorunları
**Sorun:** Resim yüklenmesi çalışmıyor
**Çözüm:** `uploads/` klasör izinlerini kontrol et

### SSL/HTTPS Sorunları
**Sorun:** Mixed content uyarısı
**Çözüm:** Tüm API çağrılarının HTTPS olduğunu kontrol et

---

## 📧 İLETİŞİM AYARLARI

### Email Gönderimi (Gelecekte)
**SMTP Bilgileri:**
- Host: `smtp.hostinger.com`
- Port: `465`
- Encryption: `SSL`
- Username: `info@bkyatirim.com`
- Password: `A3[31ES&!5a*`

---

## 🎯 İLK AÇILIŞTAN SONRA YAPILACAKLAR

### 1. Admin Paneli Kontrolü
- Admin girişi yap (`admin@bkyatirim.com`)
- Sistem ayarlarını kontrol et
- İlan onay sistemini test et

### 2. SEO ve İçerik
- Google Search Console'a domain ekle
- Google Analytics kod ekle (gerekiyorsa)
- Meta açıklamalarını güncelle

### 3. Güvenlik
- Admin şifresini değiştir
- Gereksiz test kullanıcılarını sil
- Backup sistemi kur

### 4. Performance
- Site hızını test et (GTmetrix, PageSpeed)
- Resim optimizasyonu
- CDN düşün (gelecekte)

---

## 🚨 ÖNEMLİ HATIRLATMALAR

1. **Backup:** Veritabanının düzenli yedeğini al
2. **Güvenlik:** Güçlü şifreler kullan
3. **Updates:** PHP ve sistem güncellemelerini takip et
4. **Monitoring:** Site erişilebilirliğini izle

---

**🎉 BAŞARILAR! Site hazır, yayına alabilirsiniz!**

**📅 Hazırlanma Tarihi:** 2025-06-18  
**🔧 Hazırlayan:** Claude Code Assistant