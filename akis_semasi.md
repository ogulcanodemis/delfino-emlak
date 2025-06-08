# Emlak-Delfino Sistem Akış Şeması

Bu doküman, Emlak-Delfino uygulamasındaki temel kullanıcı akış senaryolarını detaylı olarak açıklamaktadır. Farklı kullanıcı rollerine göre sistemdeki olası akışlar belirtilmiştir.

## 1. Ziyaretçi (Kayıtsız Kullanıcı) Akışı

### 1.1. İlan Görüntüleme Akışı

1. Ziyaretçi ana sayfaya giriş yapar
2. Ana sayfada öne çıkan ilanları görüntüler
3. İlan listesi sayfasına geçiş yapar
4. Filtreleme seçeneklerini kullanarak (konum, fiyat aralığı, emlak tipi vb.) ilanları filtreler
5. İlgilendiği bir ilanın detay sayfasına girer
6. İlan detaylarını (konum, özellikler, açıklama, görseller) görüntüler
   - Fiyat ve emlakçı iletişim bilgileri gizlidir
   - "Üye olarak fiyat ve iletişim bilgilerini görebilirsiniz" mesajını görür
7. Benzer ilanları görüntüler

### 1.2. Kayıt ve Giriş Akışı

1. Ziyaretçi, "Kayıt Ol" butonuna tıklar
2. Kayıt formunu doldurur (ad-soyad, e-posta, şifre, telefon)
3. Kayıt formunu gönderir
4. Sistem kayıt bilgilerini doğrular
5. Kayıt işlemi başarılı olduğunda, e-posta doğrulama bağlantısı gönderilir
6. Ziyaretçi e-posta doğrulama bağlantısına tıklar
7. E-posta doğrulandıktan sonra, kullanıcı giriş sayfasına yönlendirilir
8. Kullanıcı, e-posta ve şifre bilgilerini girerek giriş yapar
9. Sistem kimlik bilgilerini doğrular ve kullanıcıyı ana sayfaya yönlendirir

## 2. Kayıtlı Kullanıcı Akışı

### 2.1. İlan Görüntüleme Akışı

1. Kullanıcı ana sayfaya giriş yapar
2. Ana sayfada öne çıkan ilanları ve kişiselleştirilmiş önerileri görüntüler
3. İlan listesi sayfasına geçiş yapar
4. Filtreleme seçeneklerini kullanarak ilanları filtreler
5. İlgilendiği bir ilanın detay sayfasına girer
6. İlan detaylarını tam olarak görüntüler (fiyat ve emlakçı iletişim bilgileri dahil)
7. İlanı favorilere ekleyebilir
8. Emlakçıyla iletişime geçebilir

### 2.2. Profil Yönetimi Akışı

1. Kullanıcı profil sayfasına giriş yapar
2. Profil bilgilerini görüntüler
3. Profil bilgilerini düzenleyebilir (ad-soyad, telefon, adres)
4. Şifre değişikliği yapabilir
5. Bildirim ayarlarını yapılandırabilir

### 2.3. Favoriler Akışı

1. Kullanıcı favoriler sayfasına giriş yapar
2. Favori olarak işaretlediği ilanları listeler
3. Favorilerden ilan çıkarabilir
4. Favori ilanın detaylarına gidebilir

### 2.4. Emlakçı Olma Talebi Akışı

1. Kullanıcı emlakçı olma talebi sayfasına giriş yapar
2. Talep formunu doldurur (şirket adı, kurum tipi, adres, vergi bilgileri, belge)
3. Talebi gönderir
4. Süper admin talebini değerlendirir
5. Talep sonucunu görüntüler (beklemede, onaylandı, reddedildi)
6. Onaylanması durumunda emlakçı rolüne yükseltilir

## 3. Emlakçı Akışı

### 3.1. İlan Yönetimi Akışı

1. Emlakçı kontrol paneline giriş yapar
2. İlan yönetimi sayfasına geçiş yapar
3. Mevcut ilanlarını listeler
4. Yeni ilan eklemek için ilan ekleme formuna geçiş yapar
5. İlan bilgilerini doldurur (başlık, açıklama, fiyat, konum, özellikler)
6. İlan görsellerini yükler
7. İlanı kaydeder ve yayınlar
8. İlan, sistemde aktif olarak listelenir

### 3.2. İlan Düzenleme/Silme Akışı

1. Emlakçı, ilan yönetimi sayfasından düzenlemek istediği ilanı seçer
2. İlan düzenleme formunda bilgileri günceller
3. Değişiklikleri kaydeder
4. Güncellenmiş ilan sistemde güncellenir
5. Silmek istediği ilanı seçer ve silme işlemi yapar
6. İlan sistemden kaldırılır

### 3.3. İlan İstatistikleri Akışı

1. Emlakçı, ilan istatistikleri sayfasına giriş yapar
2. İlanlarının görüntülenme sayılarını görüntüler
3. İlanlarının favori eklenme sayılarını görüntüler
4. İlanlarının arama sonuçlarında görünme sayılarını görüntüler
5. Belirli tarih aralıklarına göre istatistikleri filtreler

## 4. Süper Admin Akışı

### 4.1. Kullanıcı Yönetimi Akışı

1. Süper admin, admin kontrol paneline giriş yapar
2. Kullanıcı yönetimi sayfasına geçiş yapar
3. Tüm kullanıcıları listeler
4. Kullanıcı filtreleme ve arama seçeneklerini kullanır
5. Kullanıcı detaylarını görüntüler
6. Kullanıcı bilgilerini düzenleyebilir
7. Kullanıcıya rol atayabilir/değiştirebilir
8. Kullanıcıyı pasif duruma alabilir veya silebilir

### 4.2. Rol Talepleri Yönetimi Akışı

1. Süper admin, rol talepleri sayfasına giriş yapar
2. Bekleyen rol taleplerini listeler
3. Talep detaylarını görüntüler
4. Talebi onaylayabilir veya reddedebilir
5. Talep onaylandığında, kullanıcı rolü otomatik olarak güncellenir
6. Talebe not ekleyebilir

### 4.3. İlan Yönetimi Akışı

1. Süper admin, ilan yönetimi sayfasına giriş yapar
2. Tüm ilanları listeler
3. İlan filtreleme ve arama seçeneklerini kullanır
4. İlan detaylarını görüntüler
5. İlanı düzenleyebilir
6. İlanı pasif duruma alabilir veya silebilir
7. İlanı öne çıkarabilir

### 4.4. Sistem Ayarları Akışı

1. Süper admin, sistem ayarları sayfasına giriş yapar
2. Genel site ayarlarını yapılandırır (başlık, açıklama, logo)
3. İletişim bilgilerini düzenler
4. E-posta şablonlarını yapılandırır
5. Bildirim ayarlarını yapılandırır
6. SEO ayarlarını yapılandırır

## 5. Ortak Akışlar

### 5.1. Arama Akışı

1. Kullanıcı (herhangi bir rol), arama kutusuna arama terimini girer
2. Arama sonuçları sayfasına yönlendirilir
3. Arama sonuçlarını görüntüler
4. Sonuçları filtreleyebilir ve sıralayabilir
5. İlgili ilan detayına gidebilir

### 5.2. Bildirimler Akışı

1. Kullanıcı (kayıtlı kullanıcı, emlakçı, süper admin), bildirimlerini görüntüler
2. Okunmamış bildirimleri görebilir
3. Bildirime tıklayarak ilgili sayfaya yönlendirilir
4. Bildirimleri okundu olarak işaretleyebilir

### 5.3. Şifre Sıfırlama Akışı

1. Kullanıcı, giriş sayfasında "Şifremi Unuttum" seçeneğine tıklar
2. E-posta adresini girer
3. Şifre sıfırlama bağlantısı e-posta adresine gönderilir
4. Kullanıcı, e-posta içindeki bağlantıya tıklar
5. Yeni şifre belirleme formunu doldurur
6. Yeni şifre kaydedilir
7. Kullanıcı yeni şifre ile giriş yapar 