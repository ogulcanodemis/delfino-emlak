# 📋 VERİTABANI EXPORT/IMPORT REHBERİ

## 🎯 YÖNTEMİN AVANTAJLARI

✅ **Daha Hızlı:** Mevcut verileriniz korunur  
✅ **Daha Güvenli:** Test edilmiş data  
✅ **Daha Kolay:** Tek import işlemi  
✅ **Eksiksiz:** Tüm veriler ve ayarlar dahil  

---

## 📤 ADIM 1: LOCAL VERİTABANINI EXPORT ET

### Yöntem A: phpMyAdmin ile (Önerilen)

1. **XAMPP Control Panel** → **MySQL** → **Admin** (phpMyAdmin açılır)

2. **Sol menüden** `emlak_delfino` veritabanını seç

3. **Üst menüden** `Export` (Dışa Aktar) sekmesine tıkla

4. **Export Ayarları:**
   ```
   Export method: Quick
   Format: SQL
   
   ✅ Structure (Yapı)
   ✅ Data (Veri)
   ✅ Add DROP TABLE / VIEW / PROCEDURE / FUNCTION / EVENT / TRIGGER statement
   ✅ Add CREATE DATABASE statement
   ```

5. **Go** butonuna tıkla → `emlak_delfino.sql` dosyası indirilir

### Yöntem B: Terminal/Command Line ile

```bash
# XAMPP MySQL'e bağlan
cd /Applications/XAMPP/xamppfiles/bin

# Veritabanını export et
./mysqldump -u root -p emlak_delfino > ~/Desktop/emlak_delfino_backup.sql

# Şifre: (Enter - XAMPP'te genelde şifre yok)
```

---

## 📥 ADIM 2: HOSTİNG'E IMPORT ET

### Hosting Bilgileriniz:
- **Host:** localhost
- **Database:** u389707721_bkyatirim  
- **Username:** u389707721_bkdb
- **Password:** $iTxfq%x2B;4GJt

### Import İşlemi:

1. **Hosting Control Panel** → **phpMyAdmin** gir

2. **Sol menüden** `u389707721_bkyatirim` veritabanını seç

3. **Üst menüden** `Import` sekmesine tıkla

4. **Import Ayarları:**
   ```
   File to import: emlak_delfino.sql dosyasını seç
   Format: SQL
   
   ✅ Allow interrupt of import in case of script timeout
   ```

5. **Go** butonuna tıkla

---

## ⚠️ ÖNEMLİ DİKKAT EDİLECEK NOKTALAR

### Import Öncesi Kontrol:

1. **Dosya Boyutu Kontrolü:**
   - Eğer SQL dosyası 50MB'den büyükse, hosting limitini kontrol edin
   - Büyükse **Yöntem C** kullanın

2. **Karakter Seti:**
   - utf8mb4_unicode_ci olduğundan emin olun

### Import Sonrası Kontrol:

1. **Tablo Sayısı:** 23 tablo olmalı
2. **Admin Kullanıcısı:** admin@emlakdelfino.com mevcut olmalı
3. **Test:** https://bkyatirim.com/backend/api/test

---

## 🔧 YÖNTEM C: BÜYÜK VERİTABANI İÇİN

### Eğer SQL dosyası çok büyükse:

1. **Dosyayı Bölün:**
   ```bash
   split -l 5000 emlak_delfino.sql emlak_delfino_part_
   ```

2. **Parça Parça Import Edin:**
   - Önce `emlak_delfino_part_aa` 
   - Sonra `emlak_delfino_part_ab`
   - vs.

3. **Alternatif: SSH/Terminal Erişimi Varsa:**
   ```bash
   mysql -h localhost -u u389707721_bkdb -p u389707721_bkyatirim < emlak_delfino.sql
   ```

---

## 🚨 SORUN GİDERME

### Hata: "Table already exists"
**Çözüm:** Hosting'teki tabloları önce silin veya `DROP TABLE IF EXISTS` ekleyin

### Hata: "Import timeout"
**Çözüm:** Daha küçük parçalar halinde import edin

### Hata: "Character set mismatch"
**Çözüm:** SQL dosyasının başına ekleyin:
```sql
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
```

### Hata: "Database access denied"
**Çözüm:** Hosting control panel'den database user'ının izinlerini kontrol edin

---

## ✅ BAŞARILI IMPORT KONTROLÜ

Import tamamlandıktan sonra kontrol edin:

1. **Tablo Sayısı:**
   ```sql
   SHOW TABLES;
   ```
   Sonuç: 23 tablo görünmeli

2. **Admin Kullanıcısı:**
   ```sql
   SELECT * FROM users WHERE email = 'admin@emlakdelfino.com';
   ```

3. **İlan Sayısı:**
   ```sql
   SELECT COUNT(*) FROM properties;
   ```

4. **API Test:**
   https://bkyatirim.com/backend/api/test

---

## 💡 PRO İPUÇLARI

### Yedekleme Stratejisi:
- Import öncesi hosting'teki mevcut veritabanını da export edin (güvenlik için)
- Local backup'ınızı birden fazla yerde saklayın

### Performans:
- Import sırasında `FOREIGN_KEY_CHECKS = 0` kullanın
- Import sonrası `OPTIMIZE TABLE` çalıştırın

### Güvenlik:
- Import tamamlandıktan sonra admin şifresini değiştirin
- Gereksiz test kullanıcılarını silin

---

## 📞 DESTEK

**Eğer sorun yaşarsanız:**

1. **Hosting Desteği:** Veritabanı limitleri için
2. **SQL Hata Logları:** phpMyAdmin'de hata detayları
3. **Alternative Tools:** 
   - MySQL Workbench
   - HeidiSQL
   - Adminer

---

**✨ Bu yöntemle mevcut tüm verileriniz, kullanıcılarınız ve ilanlarınız hosting'e taşınacak!**

**📅 Güncelleme:** 2025-06-18  
**🔧 Hazırlayan:** Claude Code Assistant