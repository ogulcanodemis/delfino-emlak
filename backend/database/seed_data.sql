-- Emlak-Delfino Temel Veriler
-- Bu dosya, sistemin çalışması için gerekli temel verileri ekler

USE emlak_delfino;

-- 1. Roller ekleme
INSERT INTO roles (name, description) VALUES
('Kayıtlı Kullanıcı', 'Sisteme kayıt olmuş standart kullanıcı'),
('Emlakçı', 'İlan ekleyebilen, düzenleyebilen kullanıcı'),
('Süper Admin', 'Sistem yöneticisi, tüm yetkilere sahip');

-- 2. Emlak tipleri ekleme
INSERT INTO property_types (name, description, icon, sort_order) VALUES
('Daire', 'Apartman dairesi', 'fa-building', 1),
('Villa', 'Müstakil villa', 'fa-home', 2),
('Dubleks', 'İki katlı daire', 'fa-layer-group', 3),
('Penthouse', 'Çatı katı daire', 'fa-crown', 4),
('Ofis', 'İşyeri', 'fa-briefcase', 5),
('Dükkan', 'Ticari alan', 'fa-store', 6),
('Depo', 'Depo alanı', 'fa-warehouse', 7),
('Arsa', 'İmar parseli', 'fa-map', 8),
('Bahçe', 'Bahçe alanı', 'fa-seedling', 9),
('Tarla', 'Tarım arazisi', 'fa-tractor', 10);

-- 3. Emlak durumları ekleme
INSERT INTO property_status (name, description, color, sort_order) VALUES
('Satılık', 'Satış için', '#28a745', 1),
('Kiralık', 'Kira için', '#007bff', 2),
('Satıldı', 'Satış tamamlandı', '#dc3545', 3),
('Kiralandı', 'Kira verildi', '#fd7e14', 4);

-- 4. Şehirler ekleme (Türkiye'nin büyük şehirleri)
INSERT INTO cities (name, plate_code) VALUES
('İstanbul', '34'),
('Ankara', '06'),
('İzmir', '35'),
('Bursa', '16'),
('Antalya', '07'),
('Adana', '01'),
('Konya', '42'),
('Şanlıurfa', '63'),
('Gaziantep', '27'),
('Kayseri', '38'),
('Mersin', '33'),
('Eskişehir', '26'),
('Diyarbakır', '21'),
('Samsun', '55'),
('Denizli', '20'),
('Sakarya', '54'),
('Van', '65'),
('Hatay', '31'),
('Manisa', '45'),
('Kocaeli', '41'),
('Balıkesir', '10'),
('Malatya', '44'),
('Erzurum', '25'),
('Tekirdağ', '59'),
('Elazığ', '23'),
('Trabzon', '61'),
('Ordu', '52'),
('Rize', '53'),
('Artvin', '08'),
('Giresun', '28');

-- 5. İstanbul ilçeleri ekleme (örnek)
INSERT INTO districts (city_id, name) VALUES
(1, 'Kadıköy'),
(1, 'Beşiktaş'),
(1, 'Şişli'),
(1, 'Fatih'),
(1, 'Beyoğlu'),
(1, 'Üsküdar'),
(1, 'Bakırköy'),
(1, 'Zeytinburnu'),
(1, 'Kağıthane'),
(1, 'Sarıyer'),
(1, 'Başakşehir'),
(1, 'Beylikdüzü'),
(1, 'Maltepe'),
(1, 'Pendik'),
(1, 'Ümraniye'),
(1, 'Kartal'),
(1, 'Ataşehir'),
(1, 'Esenler'),
(1, 'Güngören'),
(1, 'Bahçelievler');

-- 6. Ankara ilçeleri ekleme (örnek)
INSERT INTO districts (city_id, name) VALUES
(2, 'Çankaya'),
(2, 'Keçiören'),
(2, 'Yenimahalle'),
(2, 'Mamak'),
(2, 'Sincan'),
(2, 'Etimesgut'),
(2, 'Gölbaşı'),
(2, 'Pursaklar'),
(2, 'Altındağ'),
(2, 'Polatlı');

-- 7. İzmir ilçeleri ekleme (örnek)
INSERT INTO districts (city_id, name) VALUES
(3, 'Konak'),
(3, 'Karşıyaka'),
(3, 'Bornova'),
(3, 'Buca'),
(3, 'Alsancak'),
(3, 'Gaziemir'),
(3, 'Balçova'),
(3, 'Narlıdere'),
(3, 'Bayraklı'),
(3, 'Çiğli');

-- 8. Mahalleler ekleme (Kadıköy örneği)
INSERT INTO neighborhoods (district_id, name, postal_code) VALUES
(1, 'Moda', '34710'),
(1, 'Fenerbahçe', '34726'),
(1, 'Caddebostan', '34728'),
(1, 'Bağdat Caddesi', '34734'),
(1, 'Göztepe', '34730'),
(1, 'Erenköy', '34738'),
(1, 'Suadiye', '34740'),
(1, 'Sahrayıcedid', '34734'),
(1, 'Fikirtepe', '34722'),
(1, 'Kozyatağı', '34742');

-- 9. Süper admin kullanıcısı ekleme
INSERT INTO users (name, email, password, phone, role_id, status, email_verified_at) VALUES
('Süper Admin', 'admin@emlakdelfino.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+90 555 123 4567', 3, 1, NOW());

-- 10. Sistem ayarları ekleme
INSERT INTO settings (key_name, value, description, type, is_public) VALUES
('site_name', 'Emlak Delfino', 'Site adı', 'string', 1),
('site_description', 'En güvenilir emlak platformu', 'Site açıklaması', 'string', 1),
('site_logo', '/assets/logo.png', 'Site logosu', 'string', 1),
('contact_email', 'info@emlakdelfino.com', 'İletişim e-posta adresi', 'string', 1),
('contact_phone', '+90 555 123 4567', 'İletişim telefonu', 'string', 1),
('contact_address', 'İstanbul, Türkiye', 'İletişim adresi', 'string', 1),
('properties_per_page', '12', 'Sayfa başına ilan sayısı', 'number', 0),
('featured_properties_count', '6', 'Ana sayfada öne çıkan ilan sayısı', 'number', 0),
('max_images_per_property', '10', 'İlan başına maksimum görsel sayısı', 'number', 0),
('max_image_size', '5242880', 'Maksimum görsel boyutu (byte)', 'number', 0),
('allowed_image_types', 'jpg,jpeg,png,gif', 'İzin verilen görsel formatları', 'string', 0),
('email_verification_required', 'true', 'E-posta doğrulama zorunlu mu', 'boolean', 0),
('auto_approve_properties', 'false', 'İlanları otomatik onayla', 'boolean', 0),
('facebook_url', 'https://facebook.com/emlakdelfino', 'Facebook sayfası', 'string', 1),
('twitter_url', 'https://twitter.com/emlakdelfino', 'Twitter hesabı', 'string', 1),
('instagram_url', 'https://instagram.com/emlakdelfino', 'Instagram hesabı', 'string', 1),
('linkedin_url', 'https://linkedin.com/company/emlakdelfino', 'LinkedIn sayfası', 'string', 1),
('google_maps_api_key', '', 'Google Maps API anahtarı', 'string', 0),
('recaptcha_site_key', '', 'reCAPTCHA site anahtarı', 'string', 1),
('recaptcha_secret_key', '', 'reCAPTCHA gizli anahtarı', 'string', 0),
('smtp_host', '', 'SMTP sunucu adresi', 'string', 0),
('smtp_port', '587', 'SMTP port numarası', 'string', 0),
('smtp_username', '', 'SMTP kullanıcı adı', 'string', 0),
('smtp_password', '', 'SMTP şifresi', 'string', 0),
('smtp_encryption', 'tls', 'SMTP şifreleme türü', 'string', 0),
('mail_from_address', 'noreply@emlakdelfino.com', 'Gönderen e-posta adresi', 'string', 0),
('mail_from_name', 'Emlak Delfino', 'Gönderen adı', 'string', 0);

-- 11. Örnek emlakçı kullanıcısı ekleme
INSERT INTO users (name, email, password, phone, role_id, status, email_verified_at, city_id, district_id) VALUES
('Ahmet Emlakçı', 'emlakci@emlakdelfino.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+90 555 987 6543', 2, 1, NOW(), 1, 1);

-- 12. Örnek kayıtlı kullanıcı ekleme
INSERT INTO users (name, email, password, phone, role_id, status, email_verified_at, city_id, district_id) VALUES
('Mehmet Kullanıcı', 'kullanici@emlakdelfino.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+90 555 111 2233', 1, 1, NOW(), 1, 2);

-- 13. Emlakçı şirket bilgileri ekleme
INSERT INTO realtor_companies (user_id, company_name, company_type, license_number, tax_office, tax_number, company_address, company_phone, company_email, description, established_year, employee_count, is_verified) VALUES
(2, 'Ahmet Emlak Danışmanlık', 'Şahıs', 'EML-2024-001', 'Kadıköy Vergi Dairesi', '1234567890', 'Kadıköy, İstanbul', '+90 555 987 6543', 'info@ahmetemlak.com', 'Kadıköy bölgesinde 10 yıllık tecrübe ile hizmet veren emlak danışmanlık firması.', 2014, 3, 1);

-- 14. Ek sistem ayarları
INSERT INTO settings (key_name, value, description, type, is_public) VALUES
('currency_symbol', '₺', 'Para birimi sembolü', 'string', 1),
('currency_code', 'TRY', 'Para birimi kodu', 'string', 1),
('default_property_image', '/assets/default-property.jpg', 'Varsayılan emlak görseli', 'string', 1),
('maintenance_mode', 'false', 'Bakım modu', 'boolean', 0),
('user_registration_enabled', 'true', 'Kullanıcı kaydı açık mı', 'boolean', 0),
('property_approval_required', 'false', 'İlan onayı gerekli mi', 'boolean', 0),
('max_properties_per_user', '50', 'Kullanıcı başına maksimum ilan sayısı', 'number', 0),
('featured_property_price', '100', 'Öne çıkan ilan ücreti (TL)', 'number', 0),
('property_listing_duration', '90', 'İlan yayın süresi (gün)', 'number', 0),
('search_results_per_page', '20', 'Arama sonuçlarında sayfa başına ilan sayısı', 'number', 0);

-- 15. Email ve API ayarları
INSERT INTO settings (key_name, value, description, type, is_public) VALUES
('email_verification_token_expiry', '24', 'Email doğrulama token süresi (saat)', 'number', 0),
('password_reset_token_expiry', '1', 'Şifre sıfırlama token süresi (saat)', 'number', 0),
('api_rate_limit_per_minute', '60', 'Dakika başına API istek limiti', 'number', 0),
('api_rate_limit_per_hour', '1000', 'Saat başına API istek limiti', 'number', 0),
('jwt_secret_key', 'your-secret-key-here', 'JWT gizli anahtarı', 'string', 0),
('jwt_token_expiry', '7', 'JWT token süresi (gün)', 'number', 0),
('file_upload_max_size', '10485760', 'Maksimum dosya yükleme boyutu (byte)', 'number', 0),
('backup_frequency', 'daily', 'Yedekleme sıklığı', 'string', 0),
('log_retention_days', '30', 'Log dosyalarını saklama süresi (gün)', 'number', 0),
('enable_debug_mode', 'false', 'Debug modu aktif mi', 'boolean', 0);

-- Not: Tüm kullanıcı şifreleri: "password" (bcrypt hash ile şifrelenmiş)
-- Gerçek kullanımda bu şifreler değiştirilmelidir. 