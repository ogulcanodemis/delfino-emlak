-- Property Statuses (Emlak Durumları) Tablosu
-- Satılık, Kiralık, Satıldı, Kiralandı gibi durumları tutar

CREATE TABLE IF NOT EXISTS property_statuses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL COMMENT 'Durum adı (Satılık, Kiralık, vb.)',
    slug VARCHAR(100) NOT NULL UNIQUE COMMENT 'URL dostu slug',
    description TEXT COMMENT 'Durum açıklaması',
    is_active BOOLEAN DEFAULT TRUE COMMENT 'Aktif/Pasif durumu',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_name (name),
    INDEX idx_slug (slug),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Varsayılan emlak durumlarını ekle
INSERT IGNORE INTO property_statuses (name, slug, description, is_active) VALUES
('Satılık', 'satilik', 'Satış için mevcut emlaklar', TRUE),
('Kiralık', 'kiralik', 'Kiralama için mevcut emlaklar', TRUE),
('Satıldı', 'satildi', 'Satışı tamamlanmış emlaklar', TRUE),
('Kiralandı', 'kiralandi', 'Kiralanmış emlaklar', TRUE),
('Rezerve', 'rezerve', 'Rezerve edilmiş emlaklar', TRUE),
('İnşaat Halinde', 'insaat-halinde', 'İnşaatı devam eden emlaklar', TRUE),
('Proje', 'proje', 'Proje aşamasındaki emlaklar', TRUE),
('Değerlendirmede', 'degerlendirmede', 'Değerlendirme aşamasındaki emlaklar', TRUE),
('Pasif', 'pasif', 'Geçici olarak pasif edilmiş emlaklar', FALSE);

-- Properties tablosuna status_id kolonu ekle (eğer yoksa)
-- Önce mevcut foreign key constraint'i kaldır (varsa)
SET FOREIGN_KEY_CHECKS = 0;

-- Status_id kolonu ekle
ALTER TABLE properties 
ADD COLUMN status_id INT DEFAULT 1;

-- Status_id için index ekle
ALTER TABLE properties 
ADD INDEX idx_properties_status_id (status_id);

-- Mevcut properties kayıtlarını güncelle (varsayılan olarak "Satılık" yap)
UPDATE properties SET status_id = 1 WHERE status_id IS NULL OR status_id = 0;

-- Foreign key constraint'i ekle
ALTER TABLE properties 
ADD CONSTRAINT fk_properties_status 
    FOREIGN KEY (status_id) REFERENCES property_statuses(id) 
    ON DELETE SET NULL ON UPDATE CASCADE;

SET FOREIGN_KEY_CHECKS = 1; 