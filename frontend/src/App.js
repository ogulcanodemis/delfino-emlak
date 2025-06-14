import React, { useState, useEffect } from 'react';
import { BrowserRouter as Router, Routes, Route, Link, useNavigate, useLocation } from 'react-router-dom';
import './App.css';

// Sayfalar
import HomePage from './pages/HomePage';
import LoginPage from './pages/LoginPage';
import RegisterPage from './pages/RegisterPage';
import PropertiesPage from './pages/PropertiesPage';
import PropertyDetailPage from './pages/PropertyDetailPage';
import FavoritesPage from './pages/FavoritesPage';
import ProfilePage from './pages/ProfilePage';
import MyPropertiesPage from './pages/MyPropertiesPage';
import AccountSettingsPage from './pages/AccountSettingsPage';
import EditPropertyPage from './pages/EditPropertyPage';
import AddPropertyPage from './pages/AddPropertyPage';
import AdminPanelPage from './pages/AdminPanelPage';
import AdminPropertyDetailPage from './pages/AdminPropertyDetailPage';

// Bileşenler
import NotificationBell from './components/NotificationBell';

// Servisler
import { getCurrentUser, logout, isAuthenticated, canAccessAdminPanel } from './services/apiService';

// Header Component
function Header({ user, onLogout }) {
  const location = useLocation();
  const navigate = useNavigate();

  const isActive = (path) => {
    if (path === '/' && location.pathname === '/') return true;
    if (path !== '/' && location.pathname.startsWith(path)) return true;
    return false;
  };

  return (
    <header className="header">
      <div className="container">
        <div className="header-content">
          <Link to="/" className="logo">
            🏠 Emlak Delfino
          </Link>
          
          <nav className="nav">
            <Link 
              to="/" 
              className={isActive('/') ? 'active' : ''}
            >
              Ana Sayfa
            </Link>
            <Link 
              to="/properties" 
              className={isActive('/properties') ? 'active' : ''}
            >
              İlanlar
            </Link>
            {user && (
              <Link 
                to="/favorites" 
                className={isActive('/favorites') ? 'active' : ''}
              >
                ❤️ Favorilerim
              </Link>
            )}
            {user && canAccessAdminPanel(user) && (
              <Link 
                to="/admin" 
                className={isActive('/admin') ? 'active' : ''}
              >
                🛠️ Admin Paneli
              </Link>
            )}
          </nav>
          
          <div className="auth-section">
            {user ? (
              <div className="user-menu">
                <NotificationBell user={user} />
                <span className="user-name">Merhaba, {user.name}</span>
                <Link to="/profile" className="btn btn-outline">
                  👤 Profil
                </Link>
                <button onClick={onLogout} className="btn btn-secondary">
                  Çıkış Yap
                </button>
              </div>
            ) : (
              <div className="auth-buttons">
                <Link to="/login" className="btn btn-secondary">
                  Giriş Yap
                </Link>
                <Link to="/register" className="btn btn-primary">
                  Kayıt Ol
                </Link>
              </div>
            )}
          </div>
        </div>
      </div>
    </header>
  );
}

// Main App Component
function AppContent() {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);
  const navigate = useNavigate();

  useEffect(() => {
    checkAuthStatus();
  }, []);

  const checkAuthStatus = async () => {
    if (isAuthenticated()) {
      try {
        const userData = await getCurrentUser();
        setUser(userData);
      } catch (error) {
        console.error('Kullanıcı bilgileri alınamadı:', error);
        // Token geçersizse temizle
        localStorage.removeItem('token');
      }
    }
    setLoading(false);
  };

  const handleLogin = (userData) => {
    setUser(userData);
    navigate('/');
  };

  const handleLogout = async () => {
    try {
      await logout();
    } catch (error) {
      console.error('Çıkış hatası:', error);
    } finally {
      setUser(null);
      navigate('/');
    }
  };

  if (loading) {
    return (
      <div className="app">
        <div className="loading">
          <div className="spinner"></div>
          <p>Yükleniyor...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="app">
      <Header user={user} onLogout={handleLogout} />
      
      <main className="main">
        <Routes>
          <Route path="/" element={<HomePage user={user} />} />
          <Route path="/properties" element={<PropertiesPage user={user} />} />
          <Route path="/property/:id" element={<PropertyDetailPage user={user} />} />
          <Route path="/favorites" element={<FavoritesPage user={user} />} />
          <Route path="/profile" element={<ProfilePage user={user} onUserUpdate={setUser} />} />
          <Route path="/my-properties" element={<MyPropertiesPage user={user} />} />
          <Route path="/account-settings" element={<AccountSettingsPage user={user} onLogout={handleLogout} />} />
          <Route path="/add-property" element={<AddPropertyPage user={user} />} />
          <Route path="/edit-property/:id" element={<EditPropertyPage user={user} />} />
          <Route path="/admin" element={<AdminPanelPage user={user} />} />
          <Route path="/admin/property/:id" element={<AdminPropertyDetailPage user={user} />} />
          <Route path="/login" element={<LoginPage onLogin={handleLogin} />} />
          <Route path="/register" element={<RegisterPage onLogin={handleLogin} />} />
        </Routes>
      </main>

      {/* Footer */}
      <footer className="footer">
        <div className="container">
          <div className="footer-content">
            <div className="footer-section">
              <h3>🏠 Emlak Delfino</h3>
              <p>Hayalinizdeki evi bulmanın en kolay yolu</p>
            </div>
            
            <div className="footer-section">
              <h4>Hızlı Linkler</h4>
              <ul>
                <li><Link to="/">Ana Sayfa</Link></li>
                <li><Link to="/properties">İlanlar</Link></li>
                {user && <li><Link to="/my-properties">İlanlarım</Link></li>}
              </ul>
            </div>
            
            <div className="footer-section">
              <h4>İletişim</h4>
              <p>📧 info@emlakdelfino.com</p>
              <p>📞 0212 123 45 67</p>
            </div>
          </div>
          
          <div className="footer-bottom">
            <p>&copy; 2024 Emlak Delfino. Tüm hakları saklıdır.</p>
          </div>
        </div>
      </footer>
    </div>
  );
}

// Root App Component with Router
function App() {
  return (
    <Router>
      <AppContent />
    </Router>
  );
}

export default App;
