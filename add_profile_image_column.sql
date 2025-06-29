-- Canlı veritabanında users tablosuna profile_image alanı ekleme
-- Bu scripti canlı veritabanında çalıştırın

USE emlak_delfino; -- Veritabanı adınızı kontrol edin

-- Önce mevcut alan var mı kontrol et
SELECT COLUMN_NAME 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
  AND TABLE_NAME = 'users' 
  AND COLUMN_NAME = 'profile_image';

-- Eğer alan yoksa ekle
ALTER TABLE users 
ADD COLUMN profile_image VARCHAR(255) NULL 
AFTER status;

-- Kontrol et
DESCRIBE users;

-- Test için bir kullanıcının profil resmini güncelle (isteğe bağlı)
-- UPDATE users SET profile_image = 'uploads/profiles/test_profile.jpg' WHERE id = 1;