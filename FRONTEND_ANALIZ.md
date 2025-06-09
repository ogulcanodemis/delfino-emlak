# 🏗️ EMLAK-DELFİNO FRONTEND İŞ ANALİZİ

## 📋 GENEL BAKIŞ

Bu dokümantasyon, Emlak-Delfino projesinin frontend geliştirme sürecinde yapılacak tüm işlerin detaylı analizini içermektedir. Her sayfa, bileşen ve özellik için hangi API endpoint'lerinin kullanılacağı, gerekli state yönetimi ve UI/UX gereksinimleri belirtilmiştir.

## 🎯 PROJE YAPISI

```
frontend/
├── src/
│   ├── components/           # Yeniden kullanılabilir bileşenler
│   │   ├── common/          # Genel bileşenler
│   │   ├── auth/            # Kimlik doğrulama bileşenleri
│   │   ├── property/        # Emlak bileşenleri
│   │   ├── admin/           # Admin panel bileşenleri
│   │   ├── notifications/   # Bildirim bileşenleri
│   │   └── role-requests/   # Rol talep bileşenleri
│   ├── pages/               # Sayfa bileşenleri
│   ├── services/            # API servisleri
│   ├── hooks/               # Custom React hooks
│   ├── context/             # Context providers
│   ├── utils/               # Yardımcı fonksiyonlar
│   ├── guards/              # Route koruma bileşenleri
│   └── styles/              # CSS/SCSS dosyaları
```

---

## 👥 KULLANICI ROLLERİ VE ERİŞİM KONTROLLÜ

### Rol Hiyerarşisi:
1. **Ziyaretçi (Guest)** - Rol ID: 0
2. **Kayıtlı Kullanıcı (Registered User)** - Rol ID: 1  
3. **Emlakçı (Realtor)** - Rol ID: 2
4. **Admin** - Rol ID: 3
5. **Süper Admin** - Rol ID: 4

---

## 🔐 KİMLİK DOĞRULAMA SİSTEMİ

### 1. Giriş Sayfası (Login Page)
**Dosya:** `src/pages/auth/LoginPage.jsx`

#### API Endpoint'leri:
- `POST /auth/login` - Kullanıcı girişi

#### Özellikler:
- Email/şifre formu
- "Beni hatırla" checkbox
- Şifremi unuttum linki
- Sosyal medya giriş seçenekleri (opsiyonel)
- Form validasyonu
- Loading state
- Hata mesajları
- Rol bazlı yönlendirme (giriş sonrası)

#### State Yönetimi:
```javascript
const [formData, setFormData] = useState({
  email: '',
  password: '',
  remember_me: false
});
const [loading, setLoading] = useState(false);
const [errors, setErrors] = useState({});
```

#### Giriş Sonrası Yönlendirme:
```javascript
// Rol bazlı yönlendirme
switch(user.role_id) {
  case 4: // Süper Admin
    navigate('/admin/dashboard');
    break;
  case 3: // Admin  
    navigate('/admin/dashboard');
    break;
  case 2: // Emlakçı
    navigate('/dashboard');
    break;
  case 1: // Kayıtlı Kullanıcı
    navigate('/dashboard');
    break;
  default:
    navigate('/');
}
```

### 2. Kayıt Sayfası (Register Page)
**Dosya:** `src/pages/auth/RegisterPage.jsx`

#### API Endpoint'leri:
- `POST /auth/register` - Kullanıcı kaydı

#### Özellikler:
- Kapsamlı kayıt formu (ad, email, telefon, şifre)
- Şifre güçlülük göstergesi
- Kullanım şartları onayı
- Email doğrulama bildirimi
- Form validasyonu
- Otomatik "Kayıtlı Kullanıcı" rolü atama

### 3. Profil Sayfası (Profile Page)
**Dosya:** `src/pages/auth/ProfilePage.jsx`

#### API Endpoint'leri:
- `GET /auth/me` - Kullanıcı bilgileri
- `PUT /auth/profile` - Profil güncelleme
- `PUT /auth/change-password` - Şifre değiştirme

#### Özellikler:
- Profil bilgileri düzenleme
- Şifre değiştirme formu
- Profil fotoğrafı yükleme
- Hesap silme seçeneği
- Rol durumu gösterimi

---

## 🏠 EMLAK YÖNETİM SİSTEMİ (ROL BAZLI ERİŞİM)

### 1. Ana Sayfa (Home Page)
**Dosya:** `src/pages/HomePage.jsx`

#### API Endpoint'leri:
- `GET /properties?featured=true&limit=6` - Öne çıkan ilanlar
- `GET /stats/general` - Genel istatistikler
- `GET /stats/popular-properties?limit=3` - Popüler ilanlar

#### Rol Bazlı İçerik:
```javascript
// Ziyaretçi için
- Öne çıkan ilanlar (fiyat gizli)
- Genel istatistikler
- Kayıt ol çağrısı

// Kayıtlı kullanıcı için
- Öne çıkan ilanlar (fiyat görünür)
- Kişiselleştirilmiş öneriler
- Son baktığınız ilanlar

// Emlakçı için
- Kendi ilanlarının performansı
- Hızlı ilan ekleme butonu
- İlan yönetimi kısayolları
```

### 2. İlan Listesi Sayfası (Properties List)
**Dosya:** `src/pages/properties/PropertiesListPage.jsx`

#### API Endpoint'leri:
- `GET /properties` - İlan listesi (filtreleme ve sayfalama ile)
- `GET /property-types` - Emlak tipleri
- `GET /stats/cities` - Şehir listesi
- `GET /stats/price-ranges` - Fiyat aralıkları

#### Rol Bazlı Görünüm:
```javascript
// Ziyaretçi
const PropertyCard = ({ property, userRole }) => (
  <div className="property-card">
    <img src={property.thumbnail} />
    <h3>{property.title}</h3>
    <p>{property.address}</p>
    {userRole === 0 && (
      <div className="price-hidden">
        <span>Fiyat için üye olun</span>
      </div>
    )}
    {userRole > 0 && (
      <div className="price-visible">
        <span>{property.price} TL</span>
      </div>
    )}
  </div>
);
```

### 3. İlan Detay Sayfası (Property Detail)
**Dosya:** `src/pages/properties/PropertyDetailPage.jsx`

#### API Endpoint'leri:
- `GET /properties/{id}` - İlan detayları
- `GET /properties/{id}/images` - İlan fotoğrafları
- `POST /contact` - İletişim formu
- `POST /properties/{id}/favorite` - Favorilere ekle/çıkar

#### Rol Bazlı İçerik Kontrolü:
```javascript
const PropertyDetail = ({ property, user }) => {
  const canViewPrice = user && user.role_id > 0;
  const canViewContact = user && user.role_id > 0;
  const canAddFavorite = user && user.role_id > 0;
  const canEditProperty = user && (user.role_id >= 3 || user.id === property.user_id);

  return (
    <div>
      {/* Fotoğraf galerisi - Herkese açık */}
      <ImageGallery images={property.images} />
      
      {/* Temel bilgiler - Herkese açık */}
      <PropertyBasicInfo property={property} />
      
      {/* Fiyat bilgisi - Sadece kayıtlı kullanıcılar */}
      {canViewPrice ? (
        <PriceInfo price={property.price} />
      ) : (
        <LoginPrompt message="Fiyat bilgisi için üye olun" />
      )}
      
      {/* İletişim bilgileri - Sadece kayıtlı kullanıcılar */}
      {canViewContact ? (
        <ContactInfo owner={property.user} />
      ) : (
        <LoginPrompt message="İletişim bilgileri için üye olun" />
      )}
      
      {/* Favorilere ekleme - Sadece kayıtlı kullanıcılar */}
      {canAddFavorite && (
        <FavoriteButton propertyId={property.id} />
      )}
      
      {/* Düzenleme butonu - Sadece ilan sahibi ve adminler */}
      {canEditProperty && (
        <EditPropertyButton propertyId={property.id} />
      )}
    </div>
  );
};
```

---

## 🎯 ROL TALEBİ SİSTEMİ

### 1. Emlakçı Olma Talebi Sayfası
**Dosya:** `src/pages/role-requests/RealtorRequestPage.jsx`

#### API Endpoint'leri:
- `POST /role-requests` - Yeni rol talebi oluşturma
- `GET /role-requests` - Kullanıcının taleplerini görüntüleme

#### Özellikler:
- Şirket bilgileri formu
- Belge yükleme alanı
- Talep durumu takibi
- Sadece "Kayıtlı Kullanıcı" rolündekiler erişebilir

#### State Yönetimi:
```javascript
const [requestData, setRequestData] = useState({
  company_name: '',
  company_type: '',
  address: '',
  tax_office: '',
  tax_number: '',
  document: null,
  note: ''
});
const [existingRequest, setExistingRequest] = useState(null);
const [canSubmitRequest, setCanSubmitRequest] = useState(false);

// Kullanıcı zaten emlakçı mı kontrol et
useEffect(() => {
  if (user.role_id >= 2) {
    setCanSubmitRequest(false);
  } else {
    checkExistingRequest();
  }
}, [user]);
```

### 2. Talep Durumu Bileşeni
**Dosya:** `src/components/role-requests/RequestStatus.jsx`

#### Durum Gösterimi:
```javascript
const RequestStatus = ({ status, note, createdAt }) => {
  const getStatusInfo = (status) => {
    switch(status) {
      case 0:
        return { text: 'Beklemede', color: 'warning', icon: 'clock' };
      case 1:
        return { text: 'Onaylandı', color: 'success', icon: 'check' };
      case 2:
        return { text: 'Reddedildi', color: 'danger', icon: 'times' };
      default:
        return { text: 'Bilinmiyor', color: 'secondary', icon: 'question' };
    }
  };

  const statusInfo = getStatusInfo(status);

  return (
    <div className={`request-status status-${statusInfo.color}`}>
      <i className={`fas fa-${statusInfo.icon}`}></i>
      <span>{statusInfo.text}</span>
      {note && <p className="admin-note">{note}</p>}
      <small>Talep Tarihi: {formatDate(createdAt)}</small>
    </div>
  );
};
```

---

## 📊 DASHBOARD SİSTEMİ (ROL BAZLI)

### 1. Kullanıcı Dashboard
**Dosya:** `src/pages/dashboard/UserDashboard.jsx`

#### API Endpoint'leri:
- `GET /stats/my-activity` - Kişisel aktivite
- `GET /my-properties` - Kendi ilanları (sadece emlakçılar)
- `GET /my-favorites` - Favori ilanlar
- `GET /my-contacts` - Mesajlar
- `GET /notifications` - Bildirimler

#### Rol Bazlı Dashboard İçeriği:
```javascript
const UserDashboard = ({ user }) => {
  const renderDashboardContent = () => {
    switch(user.role_id) {
      case 1: // Kayıtlı Kullanıcı
        return (
          <>
            <FavoritesWidget />
            <RecentViewsWidget />
            <RealtorRequestWidget />
            <NotificationsWidget />
          </>
        );
      
      case 2: // Emlakçı
        return (
          <>
            <PropertyStatsWidget />
            <MyPropertiesWidget />
            <MessagesWidget />
            <PerformanceWidget />
            <NotificationsWidget />
          </>
        );
      
      default:
        return <div>Yetkisiz erişim</div>;
    }
  };

  return (
    <div className="dashboard">
      <DashboardHeader user={user} />
      <div className="dashboard-content">
        {renderDashboardContent()}
      </div>
    </div>
  );
};
```

### 2. Admin Dashboard
**Dosya:** `src/pages/admin/AdminDashboard.jsx`

#### API Endpoint'leri:
- `GET /admin/dashboard` - Admin dashboard verileri
- `GET /stats/general` - Genel istatistikler
- `GET /stats/users` - Kullanıcı istatistikleri
- `GET /stats/properties` - Emlak istatistikleri
- `GET /admin/role-requests?status=0` - Bekleyen rol talepleri

#### Özellikler:
- KPI kartları
- Bekleyen rol talepleri bildirimi
- Sistem durumu
- Hızlı eylemler

---

## 👥 KULLANICI YÖNETİMİ (ADMIN)

### 1. Kullanıcı Listesi
**Dosya:** `src/pages/admin/users/UserListPage.jsx`

#### API Endpoint'leri:
- `GET /admin/users` - Kullanıcı listesi
- `PUT /admin/users/{id}/status` - Kullanıcı durumu güncelleme
- `DELETE /admin/users/{id}` - Kullanıcı silme

#### Özellikler:
- Rol bazlı filtreleme
- Kullanıcı durumu toggle
- Toplu işlemler
- Rol değiştirme

### 2. Rol Talepleri Yönetimi
**Dosya:** `src/pages/admin/role-requests/RoleRequestsPage.jsx`

#### API Endpoint'leri:
- `GET /admin/role-requests` - Tüm rol talepleri
- `PUT /admin/role-requests/{id}` - Talep onaylama/reddetme
- `POST /notifications/bulk-send` - Toplu bildirim gönderme

#### Özellikler:
```javascript
const RoleRequestsPage = () => {
  const [requests, setRequests] = useState([]);
  const [filter, setFilter] = useState('pending'); // pending, approved, rejected, all

  const handleApproveRequest = async (requestId) => {
    try {
      await apiService.put(`/admin/role-requests/${requestId}`, {
        status: 1, // Onaylandı
        note: 'Talep onaylandı'
      });
      
      // Kullanıcıya bildirim gönder
      await apiService.post('/notifications/bulk-send', {
        user_ids: [request.user_id],
        type: 'role_approved',
        title: 'Emlakçı Talebiniz Onaylandı',
        message: 'Emlakçı olma talebiniz onaylanmıştır. Artık ilan ekleyebilirsiniz.'
      });
      
      refreshRequests();
    } catch (error) {
      console.error('Talep onaylanırken hata:', error);
    }
  };

  const handleRejectRequest = async (requestId, note) => {
    try {
      await apiService.put(`/admin/role-requests/${requestId}`, {
        status: 2, // Reddedildi
        note: note
      });
      
      // Kullanıcıya bildirim gönder
      await apiService.post('/notifications/bulk-send', {
        user_ids: [request.user_id],
        type: 'role_rejected',
        title: 'Emlakçı Talebiniz Reddedildi',
        message: `Emlakçı olma talebiniz reddedilmiştir. Sebep: ${note}`
      });
      
      refreshRequests();
    } catch (error) {
      console.error('Talep reddedilirken hata:', error);
    }
  };

  return (
    <div className="role-requests-page">
      <RequestFilters filter={filter} setFilter={setFilter} />
      <RequestsList 
        requests={requests}
        onApprove={handleApproveRequest}
        onReject={handleRejectRequest}
      />
    </div>
  );
};
```

---

## 🔔 BİLDİRİM SİSTEMİ

### 1. Bildirim Merkezi
**Dosya:** `src/components/notifications/NotificationCenter.jsx`

#### API Endpoint'leri:
- `GET /notifications` - Bildirim listesi
- `GET /notifications/unread-count` - Okunmamış sayısı
- `PUT /notifications/{id}/read` - Bildirimi okundu işaretle
- `PUT /notifications/mark-all-read` - Tümünü okundu işaretle

#### Bildirim Tipleri:
```javascript
const NotificationTypes = {
  ROLE_REQUEST: 'role_request',
  ROLE_APPROVED: 'role_approved', 
  ROLE_REJECTED: 'role_rejected',
  PROPERTY_APPROVED: 'property_approved',
  PROPERTY_REJECTED: 'property_rejected',
  NEW_MESSAGE: 'new_message',
  SYSTEM: 'system'
};

const getNotificationIcon = (type) => {
  switch(type) {
    case NotificationTypes.ROLE_REQUEST:
      return 'user-plus';
    case NotificationTypes.ROLE_APPROVED:
      return 'check-circle';
    case NotificationTypes.ROLE_REJECTED:
      return 'times-circle';
    case NotificationTypes.PROPERTY_APPROVED:
      return 'home';
    case NotificationTypes.NEW_MESSAGE:
      return 'envelope';
    default:
      return 'bell';
  }
};
```

---

## 🛡️ ROUTE KORUMA SİSTEMİ

### 1. Route Guards
**Dosya:** `src/guards/RouteGuards.jsx`

```javascript
// Giriş yapmış kullanıcılar için
export const PrivateRoute = ({ children }) => {
  const { user, loading } = useAuth();
  
  if (loading) return <LoadingSpinner />;
  
  return user ? children : <Navigate to="/login" />;
};

// Rol bazlı erişim kontrolü
export const RoleBasedRoute = ({ children, requiredRole, exact = false }) => {
  const { user, loading } = useAuth();
  
  if (loading) return <LoadingSpinner />;
  
  if (!user) return <Navigate to="/login" />;
  
  const hasPermission = exact 
    ? user.role_id === requiredRole 
    : user.role_id >= requiredRole;
  
  return hasPermission ? children : <Navigate to="/unauthorized" />;
};

// Admin rotaları için
export const AdminRoute = ({ children }) => (
  <RoleBasedRoute requiredRole={3}>
    {children}
  </RoleBasedRoute>
);

// Emlakçı rotaları için
export const RealtorRoute = ({ children }) => (
  <RoleBasedRoute requiredRole={2}>
    {children}
  </RoleBasedRoute>
);

// Kayıtlı kullanıcı rotaları için
export const UserRoute = ({ children }) => (
  <RoleBasedRoute requiredRole={1}>
    {children}
  </RoleBasedRoute>
);
```

### 2. Router Yapılandırması
**Dosya:** `src/App.jsx`

```javascript
const App = () => {
  return (
    <AuthProvider>
      <NotificationProvider>
        <Router>
          <Routes>
            {/* Herkese açık rotalar */}
            <Route path="/" element={<HomePage />} />
            <Route path="/properties" element={<PropertiesListPage />} />
            <Route path="/properties/:id" element={<PropertyDetailPage />} />
            <Route path="/login" element={<LoginPage />} />
            <Route path="/register" element={<RegisterPage />} />
            
            {/* Kayıtlı kullanıcı rotaları */}
            <Route path="/dashboard" element={
              <UserRoute>
                <UserDashboard />
              </UserRoute>
            } />
            <Route path="/profile" element={
              <UserRoute>
                <ProfilePage />
              </UserRoute>
            } />
            <Route path="/favorites" element={
              <UserRoute>
                <FavoritesPage />
              </UserRoute>
            } />
            <Route path="/role-request" element={
              <UserRoute>
                <RealtorRequestPage />
              </UserRoute>
            } />
            
            {/* Emlakçı rotaları */}
            <Route path="/properties/add" element={
              <RealtorRoute>
                <PropertyFormPage />
              </RealtorRoute>
            } />
            <Route path="/properties/:id/edit" element={
              <RealtorRoute>
                <PropertyFormPage />
              </RealtorRoute>
            } />
            <Route path="/my-properties" element={
              <RealtorRoute>
                <MyPropertiesPage />
              </RealtorRoute>
            } />
            
            {/* Admin rotaları */}
            <Route path="/admin/*" element={
              <AdminRoute>
                <AdminLayout />
              </AdminRoute>
            }>
              <Route path="dashboard" element={<AdminDashboard />} />
              <Route path="users" element={<UserListPage />} />
              <Route path="role-requests" element={<RoleRequestsPage />} />
              <Route path="properties" element={<PropertyManagementPage />} />
              <Route path="statistics" element={<StatisticsPage />} />
            </Route>
            
            {/* Hata sayfaları */}
            <Route path="/unauthorized" element={<UnauthorizedPage />} />
            <Route path="*" element={<NotFoundPage />} />
          </Routes>
        </Router>
      </NotificationProvider>
    </AuthProvider>
  );
};
```

---

## 🔧 SERVİS KATMANI

### 1. API Service
**Dosya:** `src/services/apiService.js`

```javascript
class ApiService {
  constructor() {
    this.baseURL = process.env.REACT_APP_API_URL;
    this.token = localStorage.getItem('token');
  }

  // Auth endpoints
  async login(credentials) { /* POST /auth/login */ }
  async register(userData) { /* POST /auth/register */ }
  async getProfile() { /* GET /auth/me */ }
  async updateProfile(data) { /* PUT /auth/profile */ }

  // Role request endpoints
  async createRoleRequest(data) { /* POST /role-requests */ }
  async getRoleRequests() { /* GET /role-requests */ }
  async updateRoleRequest(id, data) { /* PUT /admin/role-requests/{id} */ }

  // Property endpoints (rol bazlı)
  async getProperties(filters) { /* GET /properties */ }
  async getProperty(id) { 
    const response = await this.get(`/properties/${id}`);
    // Rol bazlı veri filtreleme
    return this.filterPropertyData(response.data, this.getCurrentUserRole());
  }

  // Rol bazlı veri filtreleme
  filterPropertyData(property, userRole) {
    if (userRole === 0) { // Ziyaretçi
      return {
        ...property,
        price: null,
        user: {
          ...property.user,
          email: null,
          phone: null
        }
      };
    }
    return property; // Kayıtlı kullanıcılar için tam veri
  }

  // Notification endpoints
  async getNotifications() { /* GET /notifications */ }
  async markAsRead(id) { /* PUT /notifications/{id}/read */ }
  async getUnreadCount() { /* GET /notifications/unread-count */ }

  // Statistics endpoints
  async getGeneralStats() { /* GET /stats/general */ }
  async getUserStats() { /* GET /stats/users */ }
  async getPropertyStats() { /* GET /stats/properties */ }

  // Contact endpoints
  async submitContact(data) { /* POST /contact */ }
  async getMyContacts() { /* GET /my-contacts */ }
  async getContactTypes() { /* GET /contact/types */ }
}
```

---

## 🎨 CONTEXT PROVIDERS

### 1. Auth Context
**Dosya:** `src/context/AuthContext.jsx`

```javascript
const AuthContext = createContext();

export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);
  const [isAuthenticated, setIsAuthenticated] = useState(false);

  // Rol kontrol fonksiyonları
  const isGuest = () => !user || user.role_id === 0;
  const isRegisteredUser = () => user && user.role_id >= 1;
  const isRealtor = () => user && user.role_id >= 2;
  const isAdmin = () => user && user.role_id >= 3;
  const isSuperAdmin = () => user && user.role_id === 4;

  // Rol bazlı yetki kontrolleri
  const canViewPrice = () => isRegisteredUser();
  const canViewContact = () => isRegisteredUser();
  const canAddProperty = () => isRealtor();
  const canManageUsers = () => isAdmin();
  const canApproveRoleRequests = () => isSuperAdmin();

  const value = {
    user,
    loading,
    isAuthenticated,
    isGuest,
    isRegisteredUser,
    isRealtor,
    isAdmin,
    isSuperAdmin,
    canViewPrice,
    canViewContact,
    canAddProperty,
    canManageUsers,
    canApproveRoleRequests,
    login,
    logout,
    register
  };

  return (
    <AuthContext.Provider value={value}>
      {children}
    </AuthContext.Provider>
  );
};
```

### 2. Notification Context
**Dosya:** `src/context/NotificationContext.jsx`

```javascript
const NotificationContext = createContext();

export const NotificationProvider = ({ children }) => {
  const [notifications, setNotifications] = useState([]);
  const [unreadCount, setUnreadCount] = useState(0);

  // Rol talebi bildirimi
  const notifyRoleRequestUpdate = (status, note) => {
    const message = status === 1 
      ? 'Emlakçı talebiniz onaylandı!' 
      : `Emlakçı talebiniz reddedildi: ${note}`;
    
    showToast(message, status === 1 ? 'success' : 'error');
  };

  // Süper admin için rol talebi bildirimi
  const notifyNewRoleRequest = (userName) => {
    showToast(`${userName} emlakçı olmak için talepte bulundu`, 'info');
  };

  const value = {
    notifications,
    unreadCount,
    notifyRoleRequestUpdate,
    notifyNewRoleRequest,
    markAsRead,
    markAllAsRead
  };

  return (
    <NotificationContext.Provider value={value}>
      {children}
    </NotificationContext.Provider>
  );
};
```

---

## 🎯 SAYFA BAZLI ENDPOINT EŞLEŞTİRMESİ

### Ziyaretçi Sayfaları
- **Ana Sayfa**: `GET /properties?featured=true&limit=6`, `GET /stats/general`
- **İlan Listesi**: `GET /properties` (fiyat bilgisi filtrelenir)
- **İlan Detay**: `GET /properties/{id}` (fiyat ve iletişim bilgisi filtrelenir)

### Kayıtlı Kullanıcı Sayfaları
- **Dashboard**: `GET /stats/my-activity`, `GET /my-favorites`, `GET /notifications`
- **İlan Detay**: `GET /properties/{id}` (tam veri)
- **Favoriler**: `GET /my-favorites`, `POST /favorites`, `DELETE /favorites/{id}`
- **Rol Talebi**: `POST /role-requests`, `GET /role-requests`

### Emlakçı Sayfaları
- **İlan Yönetimi**: `GET /my-properties`, `POST /properties`, `PUT /properties/{id}`
- **İlan İstatistikleri**: `GET /stats/my-activity`, `GET /properties/{id}/stats`

### Admin Sayfaları
- **Kullanıcı Yönetimi**: `GET /admin/users`, `PUT /admin/users/{id}`, `DELETE /admin/users/{id}`
- **Rol Talepleri**: `GET /admin/role-requests`, `PUT /admin/role-requests/{id}`
- **İlan Yönetimi**: `GET /admin/properties`, `PUT /admin/properties/{id}/status`

---

## 📱 RESPONSIVE TASARIM

### Breakpoint'ler
- **Mobile:** 320px - 768px
- **Tablet:** 768px - 1024px  
- **Desktop:** 1024px+

### Rol Bazlı Mobile Optimizasyon
- Ziyaretçiler için basitleştirilmiş arayüz
- Emlakçılar için hızlı ilan ekleme widget'ı
- Admin için kompakt yönetim paneli

---

## 🔒 GÜVENLİK ÖNLEMLERİ

### Frontend Güvenlik
- Rol bazlı component rendering
- Route seviyesinde yetki kontrolü
- Hassas verilerin client-side filtrelenmesi
- JWT token güvenli saklama
- XSS koruması (input sanitization)

### API Güvenlik
- Her istekte Authorization header kontrolü
- Rol bazlı endpoint erişim kontrolü
- Rate limiting uyarıları
- Error handling

---

## 📅 GELİŞTİRME PLANI

### Faz 1: Temel Altyapı (1-2 hafta)
1. Proje kurulumu ve konfigürasyon
2. Routing yapısı ve route guards
3. Auth sistemi ve rol yönetimi
4. Layout bileşenleri
5. API service katmanı

### Faz 2: Kullanıcı Arayüzü (2-3 hafta)
1. Ana sayfa (rol bazlı içerik)
2. İlan listesi ve detay sayfaları (rol bazlı görünüm)
3. Kullanıcı dashboard
4. Profil yönetimi
5. Rol talebi sistemi

### Faz 3: Emlakçı ve Admin Panel (2-3 hafta)
1. Emlakçı dashboard ve ilan yönetimi
2. Admin dashboard
3. Kullanıcı yönetimi
4. Rol talepleri yönetimi
5. İstatistik sayfaları

### Faz 4: Optimizasyon ve Test (1-2 hafta)
1. Performance optimizasyonu
2. Rol bazlı test senaryoları
3. Security testing
4. SEO optimizasyonu
5. Deployment hazırlığı

---

## 🎨 UI/UX TASARIM REHBERİ

### Rol Bazlı Renk Kodlaması
```css
:root {
  --guest-color: #6c757d;        /* Gri - Ziyaretçi */
  --user-color: #2563eb;         /* Mavi - Kayıtlı Kullanıcı */
  --realtor-color: #10b981;      /* Yeşil - Emlakçı */
  --admin-color: #f59e0b;        /* Turuncu - Admin */
  --super-admin-color: #ef4444;  /* Kırmızı - Süper Admin */
}
```

### Rol Gösterge Bileşeni
```javascript
const RoleBadge = ({ roleId, roleName }) => {
  const getRoleColor = (roleId) => {
    switch(roleId) {
      case 0: return 'guest';
      case 1: return 'user';
      case 2: return 'realtor';
      case 3: return 'admin';
      case 4: return 'super-admin';
      default: return 'guest';
    }
  };

  return (
    <span className={`role-badge role-${getRoleColor(roleId)}`}>
      {roleName}
    </span>
  );
};
```

---

Bu detaylı analiz, Emlak-Delfino frontend geliştirme sürecinin tüm aşamalarını rol bazlı erişim kontrolü ile birlikte kapsamaktadır. Her bileşen, sayfa ve özellik için gerekli API endpoint'leri, state yönetimi ve UI gereksinimleri belirtilmiştir.

Geliştirme sürecinde bu dokümantasyon referans alınarak, güvenli ve kullanıcı dostu bir frontend uygulaması geliştirilecektir. 