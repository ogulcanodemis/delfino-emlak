import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { getFavorites, removeFromFavorites } from '../services/apiService';
import PropertyCard from '../components/PropertyCard';

const FavoritesPage = ({ user }) => {
  const navigate = useNavigate();
  const [favorites, setFavorites] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    if (user) {
      loadFavorites();
    } else {
      setLoading(false);
    }
  }, [user]);

  const loadFavorites = async () => {
    try {
      setLoading(true);
      const data = await getFavorites();
      setFavorites(data);
    } catch (error) {
      setError('Favoriler yüklenirken bir hata oluştu: ' + error.message);
    } finally {
      setLoading(false);
    }
  };

  const handleRemoveFromFavorites = async (propertyId) => {
    try {
      await removeFromFavorites(propertyId);
      setFavorites(prev => prev.filter(fav => fav.property_id !== propertyId));
    } catch (error) {
      alert('Favorilerden çıkarma işlemi sırasında bir hata oluştu: ' + error.message);
    }
  };

  if (!user) {
    return (
      <div className="page-container">
        <div className="auth-required">
          <div className="auth-required-content">
            <h2>🔒 Giriş Gerekli</h2>
            <p>Favorilerinizi görüntülemek için giriş yapmalısınız.</p>
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
          <p>Favorileriniz yükleniyor...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="page-container">
      <div className="favorites-page">
        <div className="page-header">
          <h1>❤️ Favori İlanlarım</h1>
          <p>Beğendiğiniz ilanları buradan takip edebilirsiniz.</p>
        </div>

        {error && (
          <div className="error-message">
            <p>{error}</p>
            <button onClick={loadFavorites} className="btn btn-primary">
              Tekrar Dene
            </button>
          </div>
        )}

        {!error && (
          <>
            {favorites.length === 0 ? (
              <div className="empty-state">
                <div className="empty-state-content">
                  <span className="empty-icon">💔</span>
                  <h3>Henüz favori ilanınız yok</h3>
                  <p>İlanları beğenmeye başlayın, burada görüntüleyin.</p>
                  <button 
                    onClick={() => navigate('/properties')} 
                    className="btn btn-primary"
                  >
                    İlanları Keşfet
                  </button>
                </div>
              </div>
            ) : (
              <>
                <div className="favorites-stats">
                  <p>{favorites.length} favori ilanınız var</p>
                </div>

                <div className="properties-grid">
                  {favorites.map(favorite => {
                    // Favorite verisini PropertyCard'ın beklediği formata dönüştür
                    const propertyData = {
                      id: favorite.property_id,
                      title: favorite.title,
                      price: favorite.price,
                      address: favorite.address,
                      city_name: favorite.city,
                      district_name: favorite.district,
                      area: favorite.area,
                      rooms: favorite.rooms,
                      bathrooms: favorite.bathrooms,
                      property_type_name: favorite.property_type,
                      status_name: favorite.status,
                      main_image: favorite.thumbnail,
                      created_at: favorite.created_at,
                      view_count: 0 // Favoriler API'sinde view_count yok
                    };

                    return (
                      <div key={favorite.id} onClick={() => navigate(`/property/${favorite.property_id}`)}>
                        <PropertyCard 
                          property={propertyData}
                          user={user}
                          onFavoriteToggle={() => handleRemoveFromFavorites(favorite.property_id)}
                          isFavorite={true}
                        />
                      </div>
                    );
                  })}
                </div>
              </>
            )}
          </>
        )}
      </div>
    </div>
  );
};

export default FavoritesPage; 