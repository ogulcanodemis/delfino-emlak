-- Basit migration - cPanel için
USE u389707721_bkyatirim;

-- Alan var mı kontrol etmek için önce DESCRIBE çalıştırın
DESCRIBE users;

-- Eğer profile_image alanı yoksa, bu komutu çalıştırın:
-- ALTER TABLE users ADD COLUMN profile_image VARCHAR(255) NULL;

-- Alan eklendikten sonra tekrar kontrol edin:
-- DESCRIBE users;