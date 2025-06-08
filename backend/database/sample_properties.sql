-- Örnek İlan Verileri
-- Emlak-Delfino Projesi

USE emlak_delfino;

-- Örnek ilanlar ekleme (Emlakçı kullanıcısı tarafından)
INSERT INTO properties (
    user_id, title, description, price, property_type_id, status_id,
    address, city_id, district_id, neighborhood_id,
    area, rooms, bathrooms, floor, total_floors, building_age,
    heating_type, furnishing, balcony, elevator, parking,
    is_active, is_featured, view_count
) VALUES
(2, 'Kadıköy Moda\'da Deniz Manzaralı 3+1 Daire', 
 'Kadıköy Moda semtinde, deniz manzaralı, güneş alan, merkezi konumda 3+1 daire. Ulaşım imkanları çok iyi, sosyal tesislere yakın.', 
 2500000, 1, 1, 'Moda Mahallesi, Kadıköy', 1, 1, 1,
 120, 4, 2, 3, 5, 15,
 'Doğalgaz', 'Eşyasız', 1, 1, 0,
 1, 1, 45),

(2, 'Beşiktaş\'ta Modern 2+1 Kiralık Daire',
 'Beşiktaş merkezde, yeni yapılmış binada, modern tasarımlı 2+1 daire. Tüm eşyalar dahil, hemen taşınabilir.',
 8500, 1, 2, 'Beşiktaş Merkez', 1, 2, NULL,
 85, 3, 1, 2, 6, 2,
 'Kombi', 'Eşyalı', 1, 1, 1,
 1, 0, 23),

(2, 'Şişli\'de Satılık Ofis',
 'Şişli iş merkezinde, geniş ofis alanı. Metro ve metrobüs bağlantıları mükemmel. İş yapmak için ideal konum.',
 1800000, 5, 1, 'Şişli İş Merkezi', 1, 3, NULL,
 150, NULL, 2, 4, 8, 10,
 'Merkezi', 'Eşyasız', 0, 1, 1,
 1, 0, 12),

(2, 'Sarıyer\'de Villa',
 'Sarıyer\'de müstakil villa, bahçeli, havuzlu. Doğayla iç içe, şehrin gürültüsünden uzak huzurlu bir yaşam.',
 8500000, 2, 1, 'Sarıyer Bahçeköy', 1, 10, NULL,
 300, 5, 3, 2, 2, 8,
 'Doğalgaz', 'Yarı Eşyalı', 1, 0, 1,
 1, 1, 67),

(2, 'Maltepe\'de Yatırımlık Daire',
 'Maltepe\'de yeni yapılmış sitede, yatırım için ideal 1+1 daire. Denize yakın, ulaşım kolay.',
 950000, 1, 1, 'Maltepe Merkez', 1, 13, NULL,
 55, 2, 1, 1, 5, 1,
 'Kombi', 'Eşyasız', 1, 1, 0,
 1, 0, 8),

-- Ankara ilanları
(2, 'Çankaya\'da Satılık 4+1 Dubleks',
 'Çankaya\'da prestijli sitede, dubleks daire. Geniş terasları, şehir manzarası mevcut.',
 1750000, 3, 1, 'Çankaya Kızılay', 2, 21, NULL,
 180, 5, 3, 5, 6, 12,
 'Doğalgaz', 'Eşyasız', 1, 1, 1,
 1, 0, 34),

(2, 'Keçiören\'de Kiralık Ev',
 'Keçiören\'de müstakil ev, bahçeli. Aile için ideal, sakin mahalle.',
 4500, 2, 2, 'Keçiören Merkez', 2, 22, NULL,
 140, 4, 2, 1, 1, 20,
 'Soba', 'Yarı Eşyalı', 0, 0, 1,
 1, 0, 15),

-- İzmir ilanları  
(2, 'Konak\'ta Deniz Kenarı Daire',
 'İzmir Konak\'ta deniz kenarında, manzaralı 3+1 daire. Kordon\'a yürüme mesafesi.',
 1650000, 1, 1, 'Konak Alsancak', 3, 31, NULL,
 110, 4, 2, 2, 4, 18,
 'Doğalgaz', 'Eşyasız', 1, 0, 0,
 1, 1, 29),

(2, 'Karşıyaka\'da Modern Daire',
 'Karşıyaka\'da yeni yapılmış sitede, modern 2+1 daire. Site içi sosyal tesisler mevcut.',
 1250000, 1, 1, 'Karşıyaka Merkez', 3, 32, NULL,
 95, 3, 2, 3, 5, 3,
 'Kombi', 'Eşyasız', 1, 1, 1,
 1, 0, 18),

(2, 'Bornova\'da Öğrenci Evi',
 'Bornova\'da üniversiteye yakın, öğrenciler için ideal kiralık ev.',
 3200, 1, 2, 'Bornova Üniversite', 3, 33, NULL,
 70, 2, 1, 1, 3, 25,
 'Elektrik', 'Eşyalı', 1, 0, 0,
 1, 0, 42);

-- İlan görsellerini ekle (örnek)
INSERT INTO property_images (property_id, image_path, image_name, is_main, sort_order) VALUES
(1, '/uploads/properties/1/main.jpg', 'Ana Görsel', 1, 1),
(1, '/uploads/properties/1/salon.jpg', 'Salon', 0, 2),
(1, '/uploads/properties/1/mutfak.jpg', 'Mutfak', 0, 3),
(2, '/uploads/properties/2/main.jpg', 'Ana Görsel', 1, 1),
(2, '/uploads/properties/2/yatak_odasi.jpg', 'Yatak Odası', 0, 2),
(3, '/uploads/properties/3/main.jpg', 'Ana Görsel', 1, 1),
(4, '/uploads/properties/4/main.jpg', 'Ana Görsel', 1, 1),
(4, '/uploads/properties/4/bahce.jpg', 'Bahçe', 0, 2),
(4, '/uploads/properties/4/havuz.jpg', 'Havuz', 0, 3),
(5, '/uploads/properties/5/main.jpg', 'Ana Görsel', 1, 1); 