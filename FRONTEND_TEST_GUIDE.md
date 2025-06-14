# 🧪 Frontend İlan Onay Sistemi Test Rehberi

## 📋 Test Hesapları

### 🔑 Admin Hesabı
- **Email**: admin@emlakdelfino.com
- **Şifre**: admin123
- **Yetki**: Admin (role_id: 3)
- **Erişim**: Admin paneli, tüm özellikler

### 👤 Test Kullanıcıları
1. **Ahmet Yılmaz (Emlakçı)**
   - Email: ahmet@test.com
   - Şifre: test123
   - Yetki: Emlakçı (role_id: 2)

2. **Fatma Kaya (Emlakçı)**
   - Email: fatma@test.com
   - Şifre: test123
   - Yetki: Emlakçı (role_id: 2)

3. **Mehmet Demir (Kullanıcı)**
   - Email: mehmet@test.com
   - Şifre: test123
   - Yetki: Normal Kullanıcı (role_id: 1)

4. **Ayşe Özkan (Kullanıcı)**
   - Email: ayse@test.com
   - Şifre: test123
   - Yetki: Normal Kullanıcı (role_id: 1)

## 🏠 Test İlanları Durumu

### ✅ Onaylı İlanlar (1 adet)
- **Kadıköy'de Deniz Manzaralı 3+1 Daire** (Ahmet Yılmaz)
  - Fiyat: 2.500.000 TL
  - Durum: Onaylı ve aktif
  - Ana sayfada görünür

### ⏳ Bekleyen İlanlar (3 adet)
1. **Beşiktaş'ta Modern 2+1 Daire** (Fatma Kaya) - 1.800.000 TL
2. **Çankaya'da Geniş 4+1 Villa** (Ahmet Yılmaz) - 3.200.000 TL
3. **İzmir Konak'ta İş Yeri** (Fatma Kaya) - 850.000 TL

## 🎯 Test Senaryoları

### 1. Admin Paneli Testi

#### Adım 1: Admin Girişi
1. Frontend'i başlatın: `cd frontend && npm start`
2. http://localhost:3000 adresine gidin
3. Admin hesabıyla giriş yapın
4. **Kontrol**: Bildirim zilinde yeni bildirimler var mı?
5. **Kontrol**: Navigasyonda "🛠️ Admin Paneli" linki görünüyor mu?

#### Adım 2: Admin Paneli Erişimi
1. "Admin Paneli" linkine tıklayın
2. **Kontrol**: Admin paneli açıldı mı?
3. **Kontrol**: İstatistik kartları doğru mu?
   - Bekleyen İlan: 3
   - Bugün Onaylanan: 1
   - Toplam Onaylı: 1

#### Adım 3: Bekleyen İlanları İnceleme
1. "Bekleyen İlanlar" sekmesinde olduğunuzdan emin olun
2. **Kontrol**: 3 bekleyen ilan görünüyor mu?
3. **Kontrol**: İlan detayları doğru mu?
4. **Kontrol**: Onay/Red butonları çalışıyor mu?

#### Adım 4: İlan Onaylama
1. Bir ilanı onaylayın
2. **Kontrol**: Başarı mesajı geldi mi?
3. **Kontrol**: İlan listeden kayboldu mu?
4. **Kontrol**: İstatistikler güncellendi mi?

#### Adım 5: İlan Reddetme
1. Bir ilanı reddetmeye çalışın
2. Red sebebi modalı açıldı mı?
3. Sebep yazıp reddedin
4. **Kontrol**: İlan reddedildi mi?

### 2. Bildirim Sistemi Testi

#### Adım 1: Admin Bildirimleri
1. Admin hesabıyla giriş yapın
2. **Kontrol**: Bildirim zilinde sayı var mı?
3. Bildirim ziline tıklayın
4. **Kontrol**: "İlan Onayı Gerekli" bildirimleri var mı?
5. **Kontrol**: Bildirimler okundu işaretlenebiliyor mu?

#### Adım 2: Kullanıcı Bildirimleri
1. Ahmet Yılmaz hesabıyla giriş yapın (ahmet@test.com)
2. **Kontrol**: "İlan Onaylandı" bildirimi var mı?
3. **Kontrol**: Bildirim detayları doğru mu?

### 3. Ana Sayfa İlan Görünürlüğü Testi

#### Adım 1: Onaylı İlanlar
1. Ana sayfaya gidin
2. **Kontrol**: Onaylanan ilan görünüyor mu?
3. **Kontrol**: İlan detayları doğru mu?

#### Adım 2: Bekleyen İlanlar
1. **Kontrol**: Bekleyen ilanlar ana sayfada görünmüyor mu?
2. **Kontrol**: Sadece onaylı ilanlar listeleniyor mu?

### 4. Yetki Kontrolü Testi

#### Adım 1: Normal Kullanıcı
1. Mehmet Demir hesabıyla giriş yapın (mehmet@test.com)
2. **Kontrol**: Admin paneli linki görünmüyor mu?
3. `/admin` adresine manuel gidin
4. **Kontrol**: "Erişim Reddedildi" mesajı geliyor mu?

#### Adım 2: Emlakçı
1. Fatma Kaya hesabıyla giriş yapın (fatma@test.com)
2. **Kontrol**: Admin paneli linki görünmüyor mu?
3. **Kontrol**: Bildirim sistemi çalışıyor mu?

### 5. Yeni İlan Oluşturma Testi

#### Adım 1: İlan Oluşturma
1. Emlakçı hesabıyla giriş yapın
2. "İlan Ekle" sayfasına gidin
3. Yeni bir ilan oluşturun
4. **Kontrol**: İlan "beklemede" durumunda oluşturuldu mu?
5. **Kontrol**: Admin'e bildirim gitti mi?

#### Adım 2: Admin Onayı
1. Admin hesabına geçin
2. **Kontrol**: Yeni ilan admin panelinde görünüyor mu?
3. İlanı onaylayın
4. **Kontrol**: İlan ana sayfada görünmeye başladı mı?

## ✅ Başarı Kriterleri

### Zorunlu Özellikler
- [ ] Admin paneli sadece admin kullanıcılarına açık
- [ ] Bekleyen ilanlar admin panelinde listeleniyor
- [ ] İlan onay/red işlemleri çalışıyor
- [ ] Bildirim sistemi çalışıyor
- [ ] Onaylı ilanlar ana sayfada görünüyor
- [ ] Bekleyen/reddedilen ilanlar ana sayfada görünmüyor

### İsteğe Bağlı Özellikler
- [ ] Responsive tasarım çalışıyor
- [ ] Bildirim sayısı doğru gösteriliyor
- [ ] İstatistikler doğru hesaplanıyor
- [ ] Pagination çalışıyor
- [ ] Modal'lar düzgün açılıyor/kapanıyor

## 🐛 Bilinen Sorunlar

1. **İlan Reddetme**: Backend'de rejectProperty fonksiyonunda sorun olabilir
2. **Konum Eşleştirme**: Test verilerinde şehir/ilçe eşleştirmesi rastgele

## 📞 Destek

Herhangi bir sorunla karşılaştığınızda:
1. Browser console'u kontrol edin
2. Network sekmesinde API çağrılarını kontrol edin
3. Backend log'larını kontrol edin

---

**🎉 İyi testler!** 