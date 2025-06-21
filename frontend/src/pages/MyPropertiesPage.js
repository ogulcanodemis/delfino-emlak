import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { getUserProperties, deleteProperty } from '../services/apiService';
import PropertyCard from '../components/PropertyCard';
import './MyPropertiesPage.css';

const MyPropertiesPage = ({ user }) => {
  const navigate = useNavigate();
  const [properties, setProperties] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [stats, setStats] = useState({
    total: 0,
    active: 0,
    inactive: 0,
    featured: 0
  });

  useEffect(() => {
    if (user) {
      loadUserProperties();
    } else {
      setLoading(false);
    }
  }, [user]);

  const loadUserProperties = async () => {
    try {
      setLoading(true);
      const data = await getUserProperties();
      setProperties(data.properties || []);
      
      // İstatistikleri hesapla
      const total = data.properties?.length || 0;
      const active = data.properties?.filter(p => p.is_active).length || 0;
      const inactive = total - active;
      const featured = data.properties?.filter(p => p.is_featured).length || 0;
      
      setStats({ total, active, inactive, featured });
    } catch (error) {
      setError('İlanlarınız yüklenirken bir hata oluştu: ' + error.message);
    } finally {
      setLoading(false);
    }
  };

  const handleDeleteProperty = async (propertyId) => {
    if (!window.confirm('Bu ilanı silmek istediğinizden emin misiniz?')) {
      return;
    }

    try {
      await deleteProperty(propertyId);
      
      // State'i güncelle ve aynı anda istatistikleri hesapla
      setProperties(prev => {
        const updatedProperties = prev.filter(p => p.id !== propertyId);
        
        // İstatistikleri güncelle
        const total = updatedProperties.length;
        const active = updatedProperties.filter(p => p.is_active).length;
        const inactive = total - active;
        const featured = updatedProperties.filter(p => p.is_featured).length;
        
        setStats({ total, active, inactive, featured });
        
        return updatedProperties;
      });
      
      alert('İlan başarıyla silindi.');
    } catch (error) {
      alert('İlan silinirken bir hata oluştu: ' + error.message);
    }
  };

  const handleEditProperty = (propertyId) => {
    // İlan düzenleme sayfasına yönlendir
    navigate(`/edit-property/${propertyId}`);
  };

  if (!user) {
    return (
      <div className="page-container">
        <div className="auth-required">
          <div className="auth-required-content">
            <h2>◆ Giriş Gerekli</h2>
            <p>İlanlarınızı görüntülemek için giriş yapmalısınız.</p>
            <div className="auth-actions">
              <button 
                onClick={() => navigate('/login')} 
                className="btn btn-primary"
              >
                Giriş Yap
              </button>
              <button 
                onClick={() => navigate('/register')} 
                className="btn btn-secondary"
              >
                Kayıt Ol
              </button>
            </div>
          </div>
        </div>
      </div>
    );
  }

  if (loading) {
    return (
      <div className="page-container">
        <div className="loading">
          <div className="spinner"></div>
          <p>İlanlarınız yükleniyor...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="page-container">
      <div className="my-properties-page">
        <div className="page-header">
          <div className="header-content">
            <h1>◇ İlanlarım</h1>
            <p>Yayınladığınız ilanları yönetin</p>
          </div>
          <div className="header-actions">
            <button 
              onClick={() => navigate('/add-property')}
              className="btn btn-primary"
            >
              ◆ Yeni İlan Ekle
            </button>
          </div>
        </div>

        {error && (
          <div className="error-message">
            <p>{error}</p>
            <button onClick={loadUserProperties} className="btn btn-primary">
              Tekrar Dene
            </button>
          </div>
        )}

        {!error && (
          <>
            {/* İstatistikler */}
            <div className="properties-stats">
              <div className="stat-card">
                <div className="stat-number">{stats.total}</div>
                <div className="stat-label">Toplam İlan</div>
              </div>
              <div className="stat-card active">
                <div className="stat-number">{stats.active}</div>
                <div className="stat-label">Aktif İlan</div>
              </div>
              <div className="stat-card inactive">
                <div className="stat-number">{stats.inactive}</div>
                <div className="stat-label">Pasif İlan</div>
              </div>
              <div className="stat-card featured">
                <div className="stat-number">{stats.featured}</div>
                <div className="stat-label">Öne Çıkan</div>
              </div>
            </div>

            {properties.length === 0 ? (
              <div className="empty-state">
                <div className="empty-state-content">
                  <span className="empty-icon">◇</span>
                  <h3>Henüz ilanınız yok</h3>
                  <p>İlk ilanınızı oluşturun ve potansiyel alıcılarla buluşun.</p>
                  <button 
                    onClick={() => navigate('/add-property')} 
                    className="btn btn-primary"
                  >
                    ◆ İlk İlanımı Oluştur
                  </button>
                </div>
              </div>
            ) : (
              <div className="properties-list">
                {properties.map(property => (
                  <div key={property.id} className="property-item">
                    <div className="property-card-wrapper">
                      <PropertyCard 
                        property={property}
                        user={user}
                        onFavoriteToggle={null} // Kendi ilanlarında favori butonu yok
                        isFavorite={false}
                      />
                      
                      <div className="property-status">
                        {property.is_active ? (
                          <span className="status-badge active">◆ Aktif</span>
                        ) : (
                          <span className="status-badge inactive">◇ Pasif</span>
                        )}
                        {property.is_featured && (
                          <span className="status-badge featured">◆ Öne Çıkan</span>
                        )}
                      </div>
                    </div>
                    
                    <div className="property-actions">
                      <button 
                        onClick={() => navigate(`/property/${property.id}`)}
                        className="btn btn-outline"
                      >
                        ◇ Görüntüle
                      </button>
                      <button 
                        onClick={() => handleEditProperty(property.id)}
                        className="btn btn-secondary"
                      >
                        ◆ Düzenle
                      </button>
                      <button 
                        onClick={() => handleDeleteProperty(property.id)}
                        className="btn btn-danger"
                      >
                        ◇ Sil
                      </button>
                    </div>
                  </div>
                ))}
              </div>
            )}
          </>
        )}
      </div>
    </div>
  );
};

export default MyPropertiesPage; 