-- Emlak-Delfino Veritabanı Oluşturma
-- Bu dosya, projenin veritabanını ve tüm tablolarını oluşturur

-- Veritabanını oluştur
CREATE DATABASE IF NOT EXISTS emlak_delfino CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE emlak_delfino;

-- 1. Roller tablosu
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 2. Şehirler tablosu
CREATE TABLE cities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    plate_code VARCHAR(3),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_cities_name (name)
);

-- 3. İlçeler tablosu
CREATE TABLE districts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    city_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (city_id) REFERENCES cities(id) ON DELETE CASCADE,
    INDEX idx_districts_city_id (city_id),
    INDEX idx_districts_name (name)
);

-- 4. Mahalleler tablosu
CREATE TABLE neighborhoods (
    id INT AUTO_INCREMENT PRIMARY KEY,
    district_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    postal_code VARCHAR(10),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (district_id) REFERENCES districts(id) ON DELETE CASCADE,
    INDEX idx_neighborhoods_district_id (district_id),
    INDEX idx_neighborhoods_name (name)
);

-- 5. Kullanıcılar tablosu
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(191) NOT NULL UNIQUE,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    city_id INT,
    district_id INT,
    neighborhood_id INT,
    role_id INT NOT NULL DEFAULT 1,
    status TINYINT NOT NULL DEFAULT 1 COMMENT '0: Pasif, 1: Aktif',
    profile_image VARCHAR(255),
    remember_token VARCHAR(100),
    last_login_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id),
    FOREIGN KEY (city_id) REFERENCES cities(id) ON DELETE SET NULL,
    FOREIGN KEY (district_id) REFERENCES districts(id) ON DELETE SET NULL,
    FOREIGN KEY (neighborhood_id) REFERENCES neighborhoods(id) ON DELETE SET NULL,
    INDEX idx_users_email (email),
    INDEX idx_users_role_id (role_id),
    INDEX idx_users_status (status)
);

-- 6. Emlak tipleri tablosu
CREATE TABLE property_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    icon VARCHAR(100),
    is_active TINYINT NOT NULL DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_property_types_active (is_active)
);

-- 7. Emlak durumları tablosu
CREATE TABLE property_status (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    color VARCHAR(7) DEFAULT '#000000',
    is_active TINYINT NOT NULL DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_property_status_active (is_active)
);

-- 8. Emlak ilanları tablosu
CREATE TABLE properties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(15,2) NOT NULL,
    property_type_id INT NOT NULL,
    status_id INT NOT NULL,
    address TEXT NOT NULL,
    city_id INT NOT NULL,
    district_id INT NOT NULL,
    neighborhood_id INT,
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    area INT COMMENT 'Metrekare',
    rooms INT,
    bathrooms INT,
    floor INT,
    total_floors INT,
    building_age INT,
    heating_type ENUM('Doğalgaz', 'Elektrik', 'Kömür', 'Fuel-oil', 'Güneş Enerjisi', 'Jeotermal', 'Klima', 'Soba', 'Şömine', 'Kombi', 'Merkezi', 'Yok') DEFAULT 'Doğalgaz',
    furnishing ENUM('Eşyalı', 'Eşyasız', 'Yarı Eşyalı') DEFAULT 'Eşyasız',
    balcony TINYINT DEFAULT 0,
    elevator TINYINT DEFAULT 0,
    parking TINYINT DEFAULT 0,
    garden TINYINT DEFAULT 0,
    swimming_pool TINYINT DEFAULT 0,
    security TINYINT DEFAULT 0,
    air_conditioning TINYINT DEFAULT 0,
    internet TINYINT DEFAULT 0,
    credit_suitable TINYINT DEFAULT 0 COMMENT 'Krediye uygun',
    exchange_suitable TINYINT DEFAULT 0 COMMENT 'Takasa uygun',
    is_active TINYINT NOT NULL DEFAULT 1,
    is_featured TINYINT DEFAULT 0 COMMENT 'Öne çıkan ilan',
    view_count INT DEFAULT 0,
    featured_until TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (property_type_id) REFERENCES property_types(id),
    FOREIGN KEY (status_id) REFERENCES property_status(id),
    FOREIGN KEY (city_id) REFERENCES cities(id),
    FOREIGN KEY (district_id) REFERENCES districts(id),
    FOREIGN KEY (neighborhood_id) REFERENCES neighborhoods(id) ON DELETE SET NULL,
    INDEX idx_properties_user_id (user_id),
    INDEX idx_properties_type_id (property_type_id),
    INDEX idx_properties_status_id (status_id),
    INDEX idx_properties_city_id (city_id),
    INDEX idx_properties_district_id (district_id),
    INDEX idx_properties_price (price),
    INDEX idx_properties_active (is_active),
    INDEX idx_properties_featured (is_featured),
    INDEX idx_properties_created_at (created_at),
    INDEX idx_properties_area (area),
    INDEX idx_properties_rooms (rooms),
    FULLTEXT(title, description)
);

-- 9. Emlak görselleri tablosu
CREATE TABLE property_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    image_name VARCHAR(255) NOT NULL,
    image_size INT,
    image_type VARCHAR(50),
    alt_text VARCHAR(255),
    is_main TINYINT DEFAULT 0,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    INDEX idx_property_images_property_id (property_id),
    INDEX idx_property_images_main (is_main)
);

-- 10. Favoriler tablosu
CREATE TABLE favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    property_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_property (user_id, property_id),
    INDEX idx_favorites_user_id (user_id),
    INDEX idx_favorites_property_id (property_id)
);

-- 11. Rol talepleri tablosu
CREATE TABLE role_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    requested_role_id INT NOT NULL,
    company_name VARCHAR(255),
    company_type ENUM('Şahıs', 'Şirket') DEFAULT 'Şahıs',
    company_address TEXT,
    tax_office VARCHAR(100),
    tax_number VARCHAR(50),
    document_path VARCHAR(255),
    status TINYINT NOT NULL DEFAULT 0 COMMENT '0: Beklemede, 1: Onaylandı, 2: Reddedildi',
    admin_note TEXT,
    user_note TEXT,
    reviewed_by INT,
    reviewed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (requested_role_id) REFERENCES roles(id),
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_role_requests_user_id (user_id),
    INDEX idx_role_requests_status (status),
    INDEX idx_role_requests_role_id (requested_role_id)
);

-- 12. Bildirimler tablosu
CREATE TABLE notifications (
    id VARCHAR(36) PRIMARY KEY,
    type VARCHAR(255) NOT NULL,
    notifiable_type VARCHAR(255) NOT NULL,
    notifiable_id INT NOT NULL,
    data JSON NOT NULL,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_notifications_notifiable (notifiable_type, notifiable_id),
    INDEX idx_notifications_read_at (read_at)
);

-- 13. İletişim mesajları tablosu
CREATE TABLE contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(191) NOT NULL,
    phone VARCHAR(20),
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    status TINYINT NOT NULL DEFAULT 0 COMMENT '0: Okunmadı, 1: Okundu, 2: Yanıtlandı',
    ip_address VARCHAR(45),
    user_agent TEXT,
    replied_at TIMESTAMP NULL,
    replied_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (replied_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_contact_messages_status (status),
    INDEX idx_contact_messages_email (email)
);

-- 14. Sistem ayarları tablosu
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    key_name VARCHAR(255) NOT NULL UNIQUE,
    value TEXT,
    description TEXT,
    type ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    is_public TINYINT DEFAULT 0 COMMENT '1: Frontend tarafından erişilebilir',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_settings_key (key_name),
    INDEX idx_settings_public (is_public)
);

-- 15. Password reset tokens tablosu
CREATE TABLE password_reset_tokens (
    email VARCHAR(191) NOT NULL,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_password_reset_tokens_email (email)
);

-- 16. İlan görüntüleme istatistikleri tablosu
CREATE TABLE property_views (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    user_id INT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    referer VARCHAR(255),
    viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_property_views_property_id (property_id),
    INDEX idx_property_views_user_id (user_id),
    INDEX idx_property_views_date (viewed_at)
);

-- 17. Emlakçı ile iletişim tablosu
CREATE TABLE property_inquiries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    user_id INT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(191) NOT NULL,
    phone VARCHAR(20),
    message TEXT NOT NULL,
    status TINYINT NOT NULL DEFAULT 0 COMMENT '0: Yeni, 1: Okundu, 2: Yanıtlandı',
    ip_address VARCHAR(45),
    replied_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_property_inquiries_property_id (property_id),
    INDEX idx_property_inquiries_status (status),
    INDEX idx_property_inquiries_email (email)
);

-- 18. Emlak özellikleri tablosu (ek özellikler için)
CREATE TABLE property_features (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    feature_name VARCHAR(100) NOT NULL,
    feature_value VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    INDEX idx_property_features_property_id (property_id),
    INDEX idx_property_features_name (feature_name)
);

-- 19. Kayıtlı aramalar tablosu
CREATE TABLE saved_searches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    search_criteria JSON NOT NULL,
    is_active TINYINT DEFAULT 1,
    email_notifications TINYINT DEFAULT 0,
    last_notification_sent TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_saved_searches_user_id (user_id),
    INDEX idx_saved_searches_active (is_active)
);

-- 20. Emlakçı şirket bilgileri tablosu
CREATE TABLE realtor_companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    company_name VARCHAR(255) NOT NULL,
    company_type ENUM('Şahıs', 'Şirket', 'Franchise') DEFAULT 'Şahıs',
    license_number VARCHAR(100),
    tax_office VARCHAR(100),
    tax_number VARCHAR(50),
    company_address TEXT,
    company_phone VARCHAR(20),
    company_email VARCHAR(191),
    website VARCHAR(255),
    logo_path VARCHAR(255),
    description TEXT,
    established_year YEAR,
    employee_count INT,
    is_verified TINYINT DEFAULT 0,
    verification_documents JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_company (user_id),
    INDEX idx_realtor_companies_verified (is_verified)
);

-- 21. İlan istatistikleri tablosu (günlük/aylık özet için)
CREATE TABLE property_statistics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    date DATE NOT NULL,
    views_count INT DEFAULT 0,
    favorites_count INT DEFAULT 0,
    inquiries_count INT DEFAULT 0,
    phone_reveals_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    UNIQUE KEY unique_property_date (property_id, date),
    INDEX idx_property_statistics_property_id (property_id),
    INDEX idx_property_statistics_date (date)
);

-- 22. Email doğrulama token'ları tablosu
CREATE TABLE email_verification_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_email_verification_user_id (user_id),
    INDEX idx_email_verification_token (token),
    INDEX idx_email_verification_expires (expires_at)
);

-- 23. API rate limiting tablosu
CREATE TABLE api_rate_limits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    endpoint VARCHAR(255) NOT NULL,
    requests_count INT DEFAULT 1,
    window_start TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_rate_limits_ip (ip_address),
    INDEX idx_rate_limits_endpoint (endpoint),
    INDEX idx_rate_limits_window (window_start)
); 