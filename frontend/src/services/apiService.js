// Gerçek API Servisi - Emlak Delfino Backend ile entegrasyon

const API_BASE_URL = 'http://localhost/emlak-delfino/backend/api';

// Token yönetimi
export const getToken = () => {
  return localStorage.getItem('token');
};

export const setToken = (token) => {
  localStorage.setItem('token', token);
};

export const removeToken = () => {
  localStorage.removeItem('token');
};

// API çağrısı için basit fetch wrapper
const apiCall = async (endpoint, options = {}) => {
  const token = getToken();
  
  const config = {
    headers: {
      'Content-Type': 'application/json',
      ...(token && { 'Authorization': `Bearer ${token}` })
    },
    ...options
  };

  try {
    const response = await fetch(`${API_BASE_URL}${endpoint}`, config);
    const data = await response.json();
    
    if (!response.ok) {
      throw new Error(data.message || 'Bir hata oluştu');
    }
    
    return data;
  } catch (error) {
    console.error('API Hatası:', error);
    throw error;
  }
};

// ============ AUTH SERVİSLERİ ============

export const login = async (email, password) => {
  const data = await apiCall('/auth/login', {
    method: 'POST',
    body: JSON.stringify({ email, password })
  });
  
  if (data.status === 'success' && data.data.token) {
    setToken(data.data.token);
    return data.data.user;
  }
  
  throw new Error('Giriş başarısız');
};

export const register = async (userData) => {
  const data = await apiCall('/auth/register', {
    method: 'POST',
    body: JSON.stringify(userData)
  });
  
  if (data.status === 'success' && data.data.token) {
    setToken(data.data.token);
    return data.data.user;
  }
  
  throw new Error('Kayıt başarısız');
};

export const getCurrentUser = async () => {
  const token = getToken();
  if (!token) {
    throw new Error('Token bulunamadı');
  }
  
  const data = await apiCall('/auth/me');
  
  if (data.status === 'success') {
    return data.data.user;
  }
  
  throw new Error('Kullanıcı bilgileri alınamadı');
};

export const logout = async () => {
  try {
    await apiCall('/auth/logout', { method: 'POST' });
  } catch (error) {
    console.log('Çıkış hatası:', error);
  } finally {
    removeToken();
  }
};

// ============ PROFILE SERVİSLERİ ============

export const getUserProfile = async () => {
  const data = await apiCall('/auth/profile');
  
  if (data.status === 'success') {
    return data.data.user;
  }
  
  throw new Error('Profil bilgileri getirilemedi');
};

export const updateProfile = async (profileData) => {
  const data = await apiCall('/auth/profile', {
    method: 'PUT',
    body: JSON.stringify(profileData)
  });
  
  if (data.status === 'success') {
    return data.data.user;
  }
  
  throw new Error('Profil güncellenemedi');
};

export const getUserProperties = async (page = 1, limit = 10) => {
  const data = await apiCall(`/user/properties?page=${page}&limit=${limit}`);
  
  if (data.status === 'success') {
    return {
      properties: data.data.properties || [],
      pagination: data.data.pagination || {}
    };
  }
  
  throw new Error('Kullanıcı ilanları getirilemedi');
};

export const deleteProperty = async (propertyId) => {
  const data = await apiCall(`/properties/${propertyId}`, {
    method: 'DELETE'
  });
  
  if (data.status === 'success') {
    return true;
  }
  
  throw new Error('İlan silinemedi');
};

export const changePassword = async (passwordData) => {
  const data = await apiCall('/auth/change-password', {
    method: 'PUT',
    body: JSON.stringify(passwordData)
  });
  
  if (data.status === 'success') {
    return true;
  }
  
  throw new Error('Şifre değiştirilemedi');
};

export const deleteAccount = async () => {
  const data = await apiCall('/auth/delete-account', {
    method: 'DELETE'
  });
  
  if (data.status === 'success') {
    removeToken(); // Token'ı temizle
    return true;
  }
  
  throw new Error('Hesap silinemedi');
};

// ============ PROPERTY SERVİSLERİ ============

export const getProperties = async (filters = {}) => {
  const queryParams = new URLSearchParams();
  
  // Filtreleri query parametrelerine çevir
  Object.keys(filters).forEach(key => {
    if (filters[key] && filters[key] !== '') {
      queryParams.append(key, filters[key]);
    }
  });
  
  const queryString = queryParams.toString();
  const endpoint = queryString ? `/properties?${queryString}` : '/properties';
  
  const data = await apiCall(endpoint);
  
  if (data.status === 'success') {
    return {
      properties: data.data.properties || [],
      pagination: data.data.pagination || {}
    };
  }
  
  throw new Error('İlanlar getirilemedi');
};

export const getProperty = async (id) => {
  const data = await apiCall(`/properties/${id}`);
  
  if (data.status === 'success') {
    return data.data.property;
  }
  
  throw new Error('İlan detayları getirilemedi');
};

export const createProperty = async (propertyData) => {
  const data = await apiCall('/properties', {
    method: 'POST',
    body: JSON.stringify(propertyData)
  });
  
  if (data.status === 'success') {
    return data.data;
  }
  
  throw new Error('İlan eklenemedi');
};

export const updateProperty = async (propertyId, propertyData) => {
  const data = await apiCall(`/properties/${propertyId}`, {
    method: 'PUT',
    body: JSON.stringify(propertyData)
  });
  
  if (data.status === 'success') {
    return data.data;
  }
  
  throw new Error('İlan güncellenemedi');
};

export const getFeaturedProperties = async (limit = 6) => {
  const data = await apiCall(`/properties?featured=true&limit=${limit}`);
  
  if (data.status === 'success') {
    return data.data.properties || [];
  }
  
  throw new Error('Öne çıkan ilanlar getirilemedi');
};

export const getSimilarProperties = async (propertyId, cityId, propertyTypeId, limit = 4) => {
  const data = await apiCall(`/properties/${propertyId}/similar?city_id=${cityId}&property_type_id=${propertyTypeId}&limit=${limit}`);
  
  if (data.status === 'success') {
    return data.data.properties || [];
  }
  
  throw new Error('Benzer ilanlar getirilemedi');
};

// ============ PROPERTY TYPES SERVİSLERİ ============

export const getPropertyTypes = async () => {
  const data = await apiCall('/property-types');
  
  if (data.status === 'success') {
    return data.data || [];
  }
  
  throw new Error('Emlak tipleri getirilemedi');
};

// ============ LOCATION SERVİSLERİ ============

export const getCities = async () => {
  const data = await apiCall('/cities');
  
  if (data.status === 'success') {
    return data.data.cities || [];
  }
  
  throw new Error('Şehirler getirilemedi');
};

export const getDistricts = async (cityId) => {
  const data = await apiCall(`/districts/${cityId}`);
  
  if (data.status === 'success') {
    return data.data.districts || [];
  }
  
  throw new Error('İlçeler getirilemedi');
};

// ============ STATS SERVİSLERİ ============

export const getGeneralStats = async () => {
  const data = await apiCall('/stats/general');
  
  if (data.status === 'success') {
    return data.data;
  }
  
  throw new Error('İstatistikler getirilemedi');
};

// ============ FAVORITES SERVİSLERİ ============

export const getFavorites = async () => {
  const data = await apiCall('/favorites');
  
  if (data.status === 'success') {
    return data.data.favorites || [];
  }
  
  throw new Error('Favoriler getirilemedi');
};

export const addToFavorites = async (propertyId) => {
  const data = await apiCall('/favorites', {
    method: 'POST',
    body: JSON.stringify({ property_id: propertyId })
  });
  
  if (data.status === 'success') {
    return data.data;
  }
  
  throw new Error('Favorilere eklenemedi');
};

export const removeFromFavorites = async (propertyId) => {
  const data = await apiCall(`/favorites/${propertyId}`, {
    method: 'DELETE'
  });
  
  if (data.status === 'success') {
    return true;
  }
  
  throw new Error('Favorilerden çıkarılamadı');
};

// ============ CONTACT SERVİSLERİ ============

export const sendContactMessage = async (messageData) => {
  const data = await apiCall('/contact', {
    method: 'POST',
    body: JSON.stringify(messageData)
  });
  
  if (data.status === 'success') {
    return data.data;
  }
  
  throw new Error('Mesaj gönderilemedi');
};

export const reportProperty = async (reportData) => {
  const data = await apiCall('/reports', {
    method: 'POST',
    body: JSON.stringify(reportData)
  });
  
  if (data.status === 'success') {
    return data.data;
  }
  
  throw new Error('Rapor gönderilemedi');
};

// ============ ROLE REQUEST SERVİSLERİ ============

export const createRoleRequest = async (requestData) => {
  const data = await apiCall('/role-requests', {
    method: 'POST',
    body: JSON.stringify(requestData)
  });
  
  if (data.status === 'success') {
    return data.data;
  }
  
  throw new Error('Rol talebi oluşturulamadı');
};

export const getRoleRequests = async () => {
  const data = await apiCall('/role-requests');
  
  if (data.status === 'success') {
    return data.data.requests || [];
  }
  
  throw new Error('Rol talepleri getirilemedi');
};

// ============ NOTIFICATIONS SERVİSLERİ ============

export const getNotifications = async () => {
  const data = await apiCall('/notifications');
  
  if (data.status === 'success') {
    return data.data.notifications || [];
  }
  
  throw new Error('Bildirimler getirilemedi');
};

export const markNotificationAsRead = async (notificationId) => {
  const data = await apiCall(`/notifications/${notificationId}/read`, {
    method: 'PUT'
  });
  
  if (data.status === 'success') {
    return true;
  }
  
  throw new Error('Bildirim okundu olarak işaretlenemedi');
};

// ============ UTILITY FUNCTIONS ============

// Kullanıcı giriş yapmış mı kontrol et
export const isAuthenticated = () => {
  return !!getToken();
};

// Kullanıcı rolü kontrol et
export const getUserRole = (user) => {
  if (!user) return 0; // Ziyaretçi
  return user.role_id || 1; // Varsayılan kayıtlı kullanıcı
};

// Rol bazlı yetki kontrolleri
export const canViewPrice = (user) => {
  return getUserRole(user) >= 1; // Kayıtlı kullanıcı ve üzeri
};

export const canAddProperty = (user) => {
  return getUserRole(user) >= 2; // Emlakçı ve üzeri
};

export const canManageUsers = (user) => {
  return getUserRole(user) >= 3; // Admin ve üzeri
};

export const canApproveRoles = (user) => {
  return getUserRole(user) >= 4; // Süper Admin
};

// Fiyat formatı
export const formatPrice = (price) => {
  if (!price) return 'Fiyat belirtilmemiş';
  return new Intl.NumberFormat('tr-TR', {
    style: 'currency',
    currency: 'TRY',
    minimumFractionDigits: 0
  }).format(price);
};

// Tarih formatı
export const formatDate = (dateString) => {
  if (!dateString) return '';
  return new Date(dateString).toLocaleDateString('tr-TR');
}; 