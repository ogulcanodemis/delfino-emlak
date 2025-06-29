/**
 * Uygulama sabitleri
 * Emlak-Delfino Projesi
 */

// Oda sayısı seçenekleri (Emlak sektörü standardı)
export const ROOM_OPTIONS = [
  { value: '', label: 'Oda Sayısı Seçiniz' },
  { value: '0+1', label: 'Stüdyo (0+1)' },
  { value: '1+0', label: '1+0' },
  { value: '1+1', label: '1+1' },
  { value: '2+1', label: '2+1' },
  { value: '3+1', label: '3+1' },
  { value: '4+1', label: '4+1' },
  { value: '5+1', label: '5+1' },
  { value: '6+1', label: '6+1' },
  { value: '7+1', label: '7+1' },
  { value: '8+1', label: '8+1' },
  { value: '9+1', label: '9+1' },
  { value: '10+', label: '10+ Oda' }
];

// Banyo sayısı seçenekleri
export const BATHROOM_OPTIONS = [
  { value: '', label: 'Banyo Sayısı Seçiniz' },
  { value: 1, label: '1 Banyo' },
  { value: 2, label: '2 Banyo' },
  { value: 3, label: '3 Banyo' },
  { value: 4, label: '4 Banyo' },
  { value: 5, label: '5+ Banyo' }
];

// Filtreleme için oda seçenekleri (arama sayfası)
export const ROOM_FILTER_OPTIONS = [
  { value: '', label: 'Tümü' },
  { value: '0+1', label: 'Stüdyo' },
  { value: '1+0', label: '1+0' },
  { value: '1+1', label: '1+1' },
  { value: '2+1', label: '2+1' },
  { value: '3+1', label: '3+1' },
  { value: '4+1', label: '4+1' },
  { value: '5+1', label: '5+1' },
  { value: '6+', label: '6+ Oda' }
];

// API URL konfigürasyonu
export const API_BASE_URL = 'https://bkyatirim.com/backend/api';

// Dosya yükleme limitleri
export const FILE_UPLOAD_LIMITS = {
  MAX_IMAGES_PER_PROPERTY: 30,
  MAX_FILE_SIZE: 5 * 1024 * 1024, // 5MB
  ALLOWED_IMAGE_TYPES: ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp']
};

// Kullanıcı rolleri
export const USER_ROLES = {
  GUEST: 0,
  USER: 1,
  REALTOR: 2,
  ADMIN: 3,
  SUPER_ADMIN: 4
};

// İlan durumları
export const PROPERTY_STATUS = {
  PENDING: 0,     // Onay bekliyor
  APPROVED: 1,    // Onaylandı
  REJECTED: 2,    // Reddedildi
  INACTIVE: 3     // Pasif
};