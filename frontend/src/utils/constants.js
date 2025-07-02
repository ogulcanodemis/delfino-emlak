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
  { value: '1+2', label: '1+2' },
  { value: '2+1', label: '2+1' },
  { value: '2+2', label: '2+2' },
  { value: '3+1', label: '3+1' },
  { value: '3+2', label: '3+2' },
  { value: '3+3', label: '3+3' },
  { value: '4+1', label: '4+1' },
  { value: '4+2', label: '4+2' },
  { value: '4+3', label: '4+3' },
  { value: '5+1', label: '5+1' },
  { value: '5+2', label: '5+2' },
  { value: '5+3', label: '5+3' },
  { value: '5+4', label: '5+4' },
  { value: '6+1', label: '6+1' },
  { value: '6+2', label: '6+2' },
  { value: '6+3', label: '6+3' },
  { value: '7+1', label: '7+1' },
  { value: '7+2', label: '7+2' },
  { value: '8+1', label: '8+1' },
  { value: '8+2', label: '8+2' },
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

// Araç yakıt tipleri
export const FUEL_TYPE_OPTIONS = [
  { value: '', label: 'Yakıt Tipi Seçiniz' },
  { value: 'Benzin', label: 'Benzin' },
  { value: 'Dizel', label: 'Dizel' },
  { value: 'LPG', label: 'LPG' },
  { value: 'Elektrik', label: 'Elektrik' },
  { value: 'Hibrit', label: 'Hibrit' },
  { value: 'CNG', label: 'CNG' }
];

// Araç vites tipleri
export const TRANSMISSION_OPTIONS = [
  { value: '', label: 'Vites Tipi Seçiniz' },
  { value: 'Manuel', label: 'Manuel' },
  { value: 'Otomatik', label: 'Otomatik' },
  { value: 'Yarı Otomatik', label: 'Yarı Otomatik' },
  { value: 'CVT', label: 'CVT' }
];

// Araç durumu
export const VEHICLE_CONDITION_OPTIONS = [
  { value: '', label: 'Araç Durumu Seçiniz' },
  { value: 'Sıfır', label: 'Sıfır Araç' },
  { value: 'İkinci El', label: 'İkinci El' },
  { value: 'Hasarlı', label: 'Hasarlı/Kazalı' }
];

// Emlak tipi kategorileri (koşullu alan gösterimi için)
export const PROPERTY_TYPE_CATEGORIES = {
  REAL_ESTATE: [1, 2, 3, 4, 5, 6, 7], // Daire, Villa, Dubleks, Penthouse, Ofis, Dükkan, Depo
  LAND: [8, 9, 10], // Arsa, Bahçe, Tarla
  VEHICLE: [11, 12, 13, 14, 15] // Motosiklet, Otomobil, Kamyonet, Kamyon, İş Makinesi
};