-- BK Yatırım - Emlak Delfino Veritabanı Kurulum Scripti
-- Bu dosya hosting ortamında veritabanını kurulum için hazırlanmıştır
-- Veritabanı Adı: u389707721_bkyatirim

-- ==============================================
-- 1. TEMEL VERİLER (ROLLER)
-- ==============================================

INSERT INTO roles (name, description) VALUES
('user', 'Kayıtlı Kullanıcı - İlan görüntüleme ve favori ekleme'),
('realtor', 'Emlakçı - İlan ekleme, düzenleme ve yönetme'),
('admin', 'Admin - Kullanıcı ve ilan yönetimi'),
('super_admin', 'Süper Admin - Tam sistem yönetimi');

-- ==============================================
-- 2. ŞEHİRLER VE İLÇELER 
-- ==============================================

-- İstanbul
INSERT INTO cities (name, plate_code) VALUES ('İstanbul', '34');
SET @istanbul_id = LAST_INSERT_ID();

INSERT INTO districts (city_id, name) VALUES 
(@istanbul_id, 'Kadıköy'),
(@istanbul_id, 'Beşiktaş'),
(@istanbul_id, 'Şişli'),
(@istanbul_id, 'Beyoğlu'),
(@istanbul_id, 'Üsküdar'),
(@istanbul_id, 'Fatih'),
(@istanbul_id, 'Bakırköy'),
(@istanbul_id, 'Maltepe'),
(@istanbul_id, 'Kartal'),
(@istanbul_id, 'Pendik');

-- Ankara
INSERT INTO cities (name, plate_code) VALUES ('Ankara', '06');
SET @ankara_id = LAST_INSERT_ID();

INSERT INTO districts (city_id, name) VALUES 
(@ankara_id, 'Çankaya'),
(@ankara_id, 'Keçiören'),
(@ankara_id, 'Mamak'),
(@ankara_id, 'Sincan'),
(@ankara_id, 'Etimesgut'),
(@ankara_id, 'Yenimahalle'),
(@ankara_id, 'Gölbaşı'),
(@ankara_id, 'Pursaklar');

-- İzmir
INSERT INTO cities (name, plate_code) VALUES ('İzmir', '35');
SET @izmir_id = LAST_INSERT_ID();

INSERT INTO districts (city_id, name) VALUES 
(@izmir_id, 'Konak'),
(@izmir_id, 'Karşıyaka'),
(@izmir_id, 'Bornova'),
(@izmir_id, 'Buca'),
(@izmir_id, 'Çiğli'),
(@izmir_id, 'Gaziemir'),
(@izmir_id, 'Balçova'),
(@izmir_id, 'Narlıdere');

-- Antalya
INSERT INTO cities (name, plate_code) VALUES ('Antalya', '07');
SET @antalya_id = LAST_INSERT_ID();

INSERT INTO districts (city_id, name) VALUES 
(@antalya_id, 'Muratpaşa'),
(@antalya_id, 'Kepez'),
(@antalya_id, 'Konyaaltı'),
(@antalya_id, 'Aksu'),
(@antalya_id, 'Döşemealtı');

-- Bursa
INSERT INTO cities (name, plate_code) VALUES ('Bursa', '16');
SET @bursa_id = LAST_INSERT_ID();

INSERT INTO districts (city_id, name) VALUES 
(@bursa_id, 'Osmangazi'),
(@bursa_id, 'Nilüfer'),
(@bursa_id, 'Yıldırım'),
(@bursa_id, 'Gürsu'),
(@bursa_id, 'Kestel');

-- ==============================================
-- 3. MAHALLELER (Örnek)
-- ==============================================

-- İstanbul Kadıköy Mahalleleri
INSERT INTO neighborhoods (district_id, name) VALUES 
((SELECT id FROM districts WHERE name = 'Kadıköy' AND city_id = @istanbul_id), 'Acıbadem'),
((SELECT id FROM districts WHERE name = 'Kadıköy' AND city_id = @istanbul_id), 'Bostancı'),
((SELECT id FROM districts WHERE name = 'Kadıköy' AND city_id = @istanbul_id), 'Fenerbahçe'),
((SELECT id FROM districts WHERE name = 'Kadıköy' AND city_id = @istanbul_id), 'Göztepe'),
((SELECT id FROM districts WHERE name = 'Kadıköy' AND city_id = @istanbul_id), 'Kozyatağı');

-- İstanbul Beşiktaş Mahalleleri
INSERT INTO neighborhoods (district_id, name) VALUES 
((SELECT id FROM districts WHERE name = 'Beşiktaş' AND city_id = @istanbul_id), 'Etiler'),
((SELECT id FROM districts WHERE name = 'Beşiktaş' AND city_id = @istanbul_id), 'Levent'),
((SELECT id FROM districts WHERE name = 'Beşiktaş' AND city_id = @istanbul_id), 'Bebek'),
((SELECT id FROM districts WHERE name = 'Beşiktaş' AND city_id = @istanbul_id), 'Ortaköy'),
((SELECT id FROM districts WHERE name = 'Beşiktaş' AND city_id = @istanbul_id), 'Arnavutköy');

-- ==============================================
-- 4. EMLAK TİPLERİ
-- ==============================================

INSERT INTO property_types (name, description, icon, sort_order) VALUES
('Daire', 'Apartman daireleri', 'fa-building', 1),
('Villa', 'Müstakil villalar', 'fa-home', 2),
('Dubleks', 'İki katlı daireler', 'fa-layer-group', 3),
('Penthouse', 'Çatı katı lüks daireler', 'fa-crown', 4),
('Studio', 'Tek oda stüdyo daireler', 'fa-bed', 5),
('Ofis', 'Ticari ofis alanları', 'fa-briefcase', 6),
('Dükkan', 'Ticari dükkan alanları', 'fa-store', 7),
('Arsa', 'İnşaat arsaları', 'fa-map', 8);

-- ==============================================
-- 5. EMLAK DURUMLARI
-- ==============================================

INSERT INTO property_status (name, description, color, sort_order) VALUES
('Satılık', 'Satılık emlak ilanları', '#28a745', 1),
('Kiralık', 'Kiralık emlak ilanları', '#007bff', 2),
('Günlük Kiralık', 'Günlük kiralık emlak ilanları', '#ffc107', 3),
('Satıldı', 'Satılmış emlak ilanları', '#6c757d', 4),
('Kiralandı', 'Kiralanmış emlak ilanları', '#6c757d', 5),
('Rezerve', 'Rezerve edilmiş emlak ilanları', '#fd7e14', 6);

-- ==============================================
-- 6. SÜPER ADMİN KULLANICISI
-- ==============================================

INSERT INTO users (
    name, 
    email, 
    password, 
    phone, 
    role_id, 
    status, 
    email_verified_at,
    city_id
) VALUES (
    'BK Yatırım Admin',
    'admin@bkyatirim.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: password
    '+90 555 123 4567',
    4, -- super_admin
    1, -- aktif
    NOW(),
    @istanbul_id
);

-- ==============================================
-- 7. SİSTEM AYARLARI
-- ==============================================

INSERT INTO settings (key_name, value, description, type, is_public) VALUES
('site_name', 'BK Yatırım', 'Site adı', 'string', 1),
('site_description', 'Sahibinden satılık daire, satılık lüks ev ve gayrimenkul ilanları', 'Site açıklaması', 'string', 1),
('contact_email', 'info@bkyatirim.com', 'İletişim email adresi', 'string', 1),
('contact_phone', '+90 555 123 4567', 'İletişim telefon numarası', 'string', 1),
('properties_per_page', '12', 'Sayfa başına ilan sayısı', 'number', 1),
('max_images_per_property', '10', 'İlan başına maksimum resim sayısı', 'number', 0),
('require_admin_approval', '1', 'İlanların admin onayı gerektirip gerektirmediği', 'boolean', 0),
('max_file_size', '10485760', 'Maksimum dosya boyutu (bytes)', 'number', 0),
('allowed_file_types', 'jpg,jpeg,png,gif', 'İzin verilen dosya tipleri', 'string', 0),
('google_analytics_id', '', 'Google Analytics ID', 'string', 1),
('facebook_url', 'https://facebook.com/bkyatirim', 'Facebook sayfa linki', 'string', 1),
('instagram_url', 'https://instagram.com/bkyatirim', 'Instagram sayfa linki', 'string', 1),
('twitter_url', '', 'Twitter sayfa linki', 'string', 1),
('whatsapp_number', '+905551234567', 'WhatsApp iletişim numarası', 'string', 1);

-- ==============================================
-- 8. ÖRNEK BİR EMLAKÇI KULLANICISI
-- ==============================================

INSERT INTO users (
    name, 
    email, 
    password, 
    phone, 
    role_id, 
    status, 
    email_verified_at,
    city_id,
    district_id
) VALUES (
    'Ahmet Emlakçı',
    'ahmet@bkyatirim.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: password
    '+90 555 987 6543',
    2, -- realtor
    1, -- aktif
    NOW(),
    @istanbul_id,
    (SELECT id FROM districts WHERE name = 'Kadıköy' AND city_id = @istanbul_id)
);

-- ==============================================
-- 9. ÖRNEK EMLAK İLANLARI
-- ==============================================

SET @realtor_id = LAST_INSERT_ID();

-- Örnek Satılık Daire
INSERT INTO properties (
    user_id,
    title,
    description,
    price,
    property_type_id,
    status_id,
    address,
    city_id,
    district_id,
    neighborhood_id,
    area,
    rooms,
    bathrooms,
    floor,
    total_floors,
    building_age,
    heating_type,
    furnishing,
    balcony,
    elevator,
    parking,
    is_active,
    is_featured
) VALUES (
    @realtor_id,
    'Kadıköy Acıbadem\'de Satılık 3+1 Lüks Daire',
    'Şehir manzaralı, güvenlikli sitede, asansörlü, merkezi konumda satılık daire. Tüm detaylar için iletişime geçiniz.',
    2850000.00,
    (SELECT id FROM property_types WHERE name = 'Daire'),
    (SELECT id FROM property_status WHERE name = 'Satılık'),
    'Acıbadem Mahallesi, Üsküdar Caddesi No:123',
    @istanbul_id,
    (SELECT id FROM districts WHERE name = 'Kadıköy' AND city_id = @istanbul_id),
    (SELECT id FROM neighborhoods WHERE name = 'Acıbadem'),
    140,
    3,
    2,
    5,
    8,
    5,
    'Kombi',
    'Eşyasız',
    1,
    1,
    1,
    1,
    1
);

-- Örnek Kiralık Villa
INSERT INTO properties (
    user_id,
    title,
    description,
    price,
    property_type_id,
    status_id,
    address,
    city_id,
    district_id,
    area,
    rooms,
    bathrooms,
    building_age,
    heating_type,
    furnishing,
    balcony,
    garden,
    swimming_pool,
    parking,
    is_active
) VALUES (
    @realtor_id,
    'Beşiktaş Etiler\'de Kiralık Lüks Villa',
    'Bahçeli, havuzlu, tam eşyalı lüks villa. Aile ve iş adamları için ideal.',
    45000.00,
    (SELECT id FROM property_types WHERE name = 'Villa'),
    (SELECT id FROM property_status WHERE name = 'Kiralık'),
    'Etiler Mahallesi, Nispetiye Caddesi No:456',
    @istanbul_id,
    (SELECT id FROM districts WHERE name = 'Beşiktaş' AND city_id = @istanbul_id),
    320,
    5,
    3,
    2,
    'Merkezi',
    'Eşyalı',
    1,
    1,
    1,
    1,
    1
);

-- ==============================================
-- KURULUM TAMAMLANDI
-- ==============================================

-- Bu script ile temel veriler yüklenmiştir:
-- ✓ 5 Ana şehir ve ilçeleri
-- ✓ Örnek mahalleler  
-- ✓ 8 Emlak tipi
-- ✓ 6 Emlak durumu
-- ✓ 1 Süper Admin kullanıcısı (admin@bkyatirim.com / password: password)
-- ✓ 1 Emlakçı kullanıcısı (ahmet@bkyatirim.com / password: password)
-- ✓ 2 Örnek emlak ilanı
-- ✓ Temel sistem ayarları