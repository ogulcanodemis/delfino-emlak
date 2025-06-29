import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { getProperty, addToFavorites, removeFromFavorites, canViewPrice } from '../services/apiService';
import ContactForm from '../components/ContactForm';
import SimilarProperties from '../components/SimilarProperties';
import ReportForm from '../components/ReportForm';
import MapSelector from '../components/MapSelector';
import './PropertyDetailPage.css';

const PropertyDetailPage = ({ user }) => {
  const { id: propertyId } = useParams();
  const navigate = useNavigate();
  const [property, setProperty] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [isFavorite, setIsFavorite] = useState(false);
  const [currentImageIndex, setCurrentImageIndex] = useState(0);
  const [showContactForm, setShowContactForm] = useState(false);
  const [showReportForm, setShowReportForm] = useState(false);
  const [activeTab, setActiveTab] = useState('description'); // 'description' or 'location'

  useEffect(() => {
    loadProperty();
  }, [propertyId]);

  const loadProperty = async () => {
    try {
      setLoading(true);
      const data = await getProperty(propertyId);
      console.log('🔍 FULL Property data:', data); // Debug için
      console.log('👤 User ID:', data.user_id);
      console.log('👤 User Name:', data.user_name);
      console.log('🖼️ User profile image:', data.user_profile_image); // Debug için
      console.log('🖼️ Profile image type:', typeof data.user_profile_image);
      console.log('🖼️ Profile image length:', data.user_profile_image?.length);
      setProperty(data);
    } catch (error) {
      setError('İlan detayları yüklenirken bir hata oluştu: ' + error.message);
    } finally {
      setLoading(false);
    }
  };

  const handleFavoriteToggle = async () => {
    if (!user) {
      alert('Favorilere eklemek için giriş yapmalısınız');
      return;
    }

    try {
      if (isFavorite) {
        await removeFromFavorites(propertyId);
        setIsFavorite(false);
      } else {
        await addToFavorites(propertyId);
        setIsFavorite(true);
      }
    } catch (error) {
      alert('Favori işlemi sırasında bir hata oluştu: ' + error.message);
    }
  };

  const formatPrice = (price) => {
    if (!price) return 'Fiyat Belirtilmemiş';
    return new Intl.NumberFormat('tr-TR', {
      style: 'currency',
      currency: 'TRY',
      minimumFractionDigits: 0,
      maximumFractionDigits: 0
    }).format(price);
  };

  const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('tr-TR', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });
  };

  if (loading) {
    return (
      <div className="page-container">
        <div className="loading">
          <div className="spinner"></div>
          <p>İlan detayları yükleniyor...</p>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="page-container">
        <div className="error-message">
          <h3>Hata</h3>
          <p>{error}</p>
          <button onClick={() => navigate(-1)} className="btn btn-primary">
            Geri Dön
          </button>
        </div>
      </div>
    );
  }

  if (!property) {
    return (
      <div className="page-container">
        <div className="error-message">
          <h3>İlan Bulunamadı</h3>
          <p>Aradığınız ilan bulunamadı veya kaldırılmış olabilir.</p>
          <button onClick={() => navigate(-1)} className="btn btn-primary">
            Geri Dön
          </button>
        </div>
      </div>
    );
  }

  const canSeePrice = canViewPrice(user);
  const images = property.images || [];
  const currentImage = images[currentImageIndex];

  return (
    <div className="page-container">
      <div className="property-detail">
        {/* Header */}
        <div className="property-header">
          <button onClick={() => navigate(-1)} className="back-btn">
            ← Geri Dön
          </button>
          
          <div className="property-actions">
            {user && (
              <button 
                onClick={handleFavoriteToggle}
                className={`favorite-btn ${isFavorite ? 'active' : ''}`}
              >
                {isFavorite ? '♥ Favorilerden Çıkar' : '♡ Favorilere Ekle'}
              </button>
            )}
            
            <button 
              onClick={() => setShowReportForm(true)}
              className="report-btn"
              title="İlanı Rapor Et"
            >
              ⚠ Rapor Et
            </button>
          </div>
        </div>

        {/* Main Content Layout */}
        <div className="property-content-layout">
          {/* Left Side - Image Gallery + Description */}
          <div className="property-gallery-section">
            <div className="property-gallery">
              {images.length > 0 ? (
                <>
                  <div className="main-image">
                    <img 
                      src={currentImage?.image_url || `https://bkyatirim.com/${currentImage?.image_path}`} 
                      alt={property.title}
                    />
                    {images.length > 1 && (
                      <>
                        <button 
                          className="gallery-nav prev"
                          onClick={() => setCurrentImageIndex(
                            currentImageIndex === 0 ? images.length - 1 : currentImageIndex - 1
                          )}
                        >
                          ‹
                        </button>
                        <button 
                          className="gallery-nav next"
                          onClick={() => setCurrentImageIndex(
                            currentImageIndex === images.length - 1 ? 0 : currentImageIndex + 1
                          )}
                        >
                          ›
                        </button>
                      </>
                    )}
                  </div>
                  
                  {images.length > 1 && (
                    <div className="image-thumbnails">
                      {images.map((image, index) => (
                        <img
                          key={image.id}
                          src={image.image_url || `https://bkyatirim.com/${image.image_path}`}
                          alt={`${property.title} - ${index + 1}`}
                          className={index === currentImageIndex ? 'active' : ''}
                          onClick={() => setCurrentImageIndex(index)}
                        />
                      ))}
                    </div>
                  )}
                </>
              ) : (
                <div className="no-images">
                  <span>○</span>
                  <p>Bu ilan için fotoğraf bulunmuyor</p>
                </div>
              )}
            </div>
            
            {/* Tab Navigation & Content */}
            <div className="property-tabs-section">
              <div className="tab-navigation">
                <button
                  type="button"
                  className={`tab-button ${activeTab === 'description' ? 'active' : ''}`}
                  onClick={() => setActiveTab('description')}
                >
                  📝 Açıklama
                </button>
                <button
                  type="button"
                  className={`tab-button ${activeTab === 'location' ? 'active' : ''}`}
                  onClick={() => setActiveTab('location')}
                >
                  🗺️ Konum
                </button>
              </div>

              <div className="tab-content">
                {activeTab === 'description' && (
                  <div className="tab-pane active">
                    <div className="property-description">
                      <h3>◆ Açıklama</h3>
                      <p>{property.description || 'Bu ilan için açıklama bulunmuyor.'}</p>
                    </div>
                  </div>
                )}

                {activeTab === 'location' && (
                  <div className="tab-pane active">
                    <div className="property-location-tab">
                      <h3>🗺️ Konum Bilgileri</h3>
                      <div className="location-details">
                        <p><strong>Adres:</strong> {property.address}</p>
                        <p><strong>Bölge:</strong> {property.neighborhood_name && `${property.neighborhood_name}, `}
                           {property.district_name}, {property.city_name}</p>
                      </div>
                      
                      {/* Harita - Eğer koordinatlar varsa göster */}
                      {property.latitude && property.longitude ? (
                        <div className="property-map-container">
                          <MapSelector
                            latitude={parseFloat(property.latitude)}
                            longitude={parseFloat(property.longitude)}
                            cityName={property.city_name}
                            districtName={property.district_name}
                            address={property.address}
                            readonly={true}
                          />
                        </div>
                      ) : (
                        <div className="no-location">
                          <p>Bu ilan için harita konumu belirtilmemiş.</p>
                        </div>
                      )}
                    </div>
                  </div>
                )}
              </div>
            </div>
          </div>

          {/* Right Side - Property Info */}
          <div className="property-info-section">
            <div className="property-main">
            <div className="property-title-section">
              <h1>{property.title}</h1>
              <div className="property-badges">
                <span className="status-badge">{property.status_name}</span>
                {property.is_featured && <span className="featured-badge">⭐ Öne Çıkan</span>}
              </div>
            </div>

            <div className="property-price-section">
              {canSeePrice ? (
                <div className="price">{formatPrice(property.price)}</div>
              ) : (
                <div className="price-hidden">
                  <span>🔒 Fiyat ve iletişim bilgileri için üye olun</span>
                  <p>Üye olmak ücretsizdir ve sadece birkaç dakika sürer.</p>
                </div>
              )}
            </div>

            <div className="property-location-section">
              <h3>◉ Konum</h3>
              <p>{property.address}</p>
              <p>{property.neighborhood_name && `${property.neighborhood_name}, `}
                 {property.district_name}, {property.city_name}</p>
            </div>

            <div className="property-features">
              <h3>◆ Özellikler</h3>
              <div className="features-grid">
                <div className="feature-item">
                  <span className="feature-label">Emlak Tipi:</span>
                  <span className="feature-value">{property.property_type_name}</span>
                </div>
                
                {property.area && (
                  <div className="feature-item">
                    <span className="feature-label">Alan:</span>
                    <span className="feature-value">{property.area} m²</span>
                  </div>
                )}
                
                {property.rooms && (
                  <div className="feature-item">
                    <span className="feature-label">Oda Sayısı:</span>
                    <span className="feature-value">{property.rooms}</span>
                  </div>
                )}
                
                {property.bathrooms && (
                  <div className="feature-item">
                    <span className="feature-label">Banyo Sayısı:</span>
                    <span className="feature-value">{property.bathrooms}</span>
                  </div>
                )}
                
                {property.floor && (
                  <div className="feature-item">
                    <span className="feature-label">Kat:</span>
                    <span className="feature-value">{property.floor}</span>
                  </div>
                )}
                
                {property.total_floors && (
                  <div className="feature-item">
                    <span className="feature-label">Toplam Kat:</span>
                    <span className="feature-value">{property.total_floors}</span>
                  </div>
                )}
                
                {property.building_age && (
                  <div className="feature-item">
                    <span className="feature-label">Bina Yaşı:</span>
                    <span className="feature-value">{property.building_age} yıl</span>
                  </div>
                )}
                
                {property.heating_type && (
                  <div className="feature-item">
                    <span className="feature-label">Isıtma:</span>
                    <span className="feature-value">{property.heating_type}</span>
                  </div>
                )}
                
                {property.furnishing && (
                  <div className="feature-item">
                    <span className="feature-label">Eşya Durumu:</span>
                    <span className="feature-value">{property.furnishing}</span>
                  </div>
                )}
              </div>
            </div>

            <div className="property-amenities">
              <h3>◇ Özellikler</h3>
              <div className="amenities-grid">
                
                {Boolean(property.balcony) && <span className="amenity">🏡 Balkon</span>}
                {Boolean(property.elevator) && <span className="amenity">🛗 Asansör</span>}
                {Boolean(property.parking) && <span className="amenity">🚗 Otopark</span>}
                {Boolean(property.garden) && <span className="amenity">🌳 Bahçe</span>}
                {Boolean(property.swimming_pool) && <span className="amenity">🏊 Havuz</span>}
                {Boolean(property.security) && <span className="amenity">🔒 Güvenlik</span>}
                {Boolean(property.air_conditioning) && <span className="amenity">❄️ Klima</span>}
                {Boolean(property.internet) && <span className="amenity">🌐 İnternet</span>}
                {Boolean(property.credit_suitable) && <span className="amenity">◇ Krediye Uygun</span>}
                {Boolean(property.exchange_suitable) && <span className="amenity">◇ Takasa Uygun</span>}
                
                {/* Show message if no features available */}
                {!Boolean(property.balcony) && 
                 !Boolean(property.elevator) && 
                 !Boolean(property.parking) && 
                 !Boolean(property.garden) && 
                 !Boolean(property.swimming_pool) && 
                 !Boolean(property.security) && 
                 !Boolean(property.air_conditioning) && 
                 !Boolean(property.internet) && 
                 !Boolean(property.credit_suitable) && 
                 !Boolean(property.exchange_suitable) && (
                  <span className="no-amenities">Bu ilan için özellik bilgisi bulunmuyor.</span>
                )}
              </div>
            </div>

            {/* Agent Card - Only show to logged in users */}
            {canSeePrice && property.user_name && (
              <div className="agent-card">
                <h3>◉ İlan Sahibi</h3>
                <div className="agent-info">
                  <div className="agent-profile">
                    <div className="agent-avatar">
                      {(() => {
                        console.log('Profile image check:', {
                          user_profile_image: property.user_profile_image,
                          type: typeof property.user_profile_image,
                          length: property.user_profile_image?.length,
                          trimmed: property.user_profile_image?.trim()
                        });
                        
                        const hasValidImage = property.user_profile_image && 
                                            property.user_profile_image.trim() !== '' && 
                                            property.user_profile_image !== 'null' && 
                                            property.user_profile_image !== 'undefined';
                                            
                        if (hasValidImage) {
                          const imageUrl = property.user_profile_image.startsWith('http') 
                            ? property.user_profile_image 
                            : `https://bkyatirim.com/${property.user_profile_image}`;
                          
                          console.log('Final image URL:', imageUrl);
                          
                          return (
                            <img 
                              src={imageUrl}
                              alt={property.user_name}
                              className="profile-image"
                              onError={(e) => {
                                console.log('Image load error:', e.target.src);
                                e.target.style.display = 'none';
                                e.target.parentNode.innerHTML = `
                                  <div class="profile-placeholder">
                                    <span class="placeholder-icon">👤</span>
                                  </div>
                                `;
                              }}
                              onLoad={() => console.log('Image loaded successfully:', imageUrl)}
                            />
                          );
                        } else {
                          console.log('Using placeholder - no valid image');
                          return (
                            <div className="profile-placeholder">
                              <span className="placeholder-icon">👤</span>
                            </div>
                          );
                        }
                      })()}
                    </div>
                    <div className="agent-details">
                      <p className="agent-name">{property.user_name}</p>
                      {property.user_phone && (
                        <p className="agent-phone">
                          ☏ <a href={`tel:${property.user_phone}`}>{property.user_phone}</a>
                        </p>
                      )}
                      {property.user_email && (
                        <p className="agent-email">
                          ✉ <a href={`mailto:${property.user_email}`}>{property.user_email}</a>
                        </p>
                      )}
                    </div>
                  </div>
                </div>
                
                <div className="contact-actions">
                  <button 
                    onClick={() => setShowContactForm(true)} 
                    className="btn btn-primary"
                  >
                    ✉ Mesaj Gönder
                  </button>
                  {property.user_phone && (
                    <a href={`tel:${property.user_phone}`} className="btn btn-secondary">
                      ☏ Ara
                    </a>
                  )}
                  {property.user_email && (
                    <a href={`mailto:${property.user_email}`} className="btn btn-outline">
                      ✉ E-posta
                    </a>
                  )}
                </div>
              </div>
            )}

            <div className="property-stats">
              <h3>◆ İstatistikler</h3>
              <div className="stat-item">
                <span className="stat-label">Görüntülenme:</span>
                <span className="stat-value">{property.view_count}</span>
              </div>
              <div className="stat-item">
                <span className="stat-label">Yayın Tarihi:</span>
                <span className="stat-value">{formatDate(property.created_at)}</span>
              </div>
              {property.updated_at !== property.created_at && (
                <div className="stat-item">
                  <span className="stat-label">Güncelleme:</span>
                  <span className="stat-value">{formatDate(property.updated_at)}</span>
                </div>
              )}
            </div>

            <div className="social-share">
              <h3>◇ Paylaş</h3>
              <div className="share-buttons">
                <a 
                  href={`https://wa.me/?text=${encodeURIComponent(`${property.title} - ${window.location.href}`)}`}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="share-btn whatsapp"
                  title="WhatsApp'ta Paylaş"
                >
                  ◇ WhatsApp
                </a>
                
                <a 
                  href={`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(window.location.href)}`}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="share-btn facebook"
                  title="Facebook'ta Paylaş"
                >
                  ◇ Facebook
                </a>
                
                <a 
                  href={`https://twitter.com/intent/tweet?text=${encodeURIComponent(property.title)}&url=${encodeURIComponent(window.location.href)}`}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="share-btn twitter"
                  title="Twitter'da Paylaş"
                >
                  ◇ Twitter
                </a>
                
                <button 
                  onClick={() => {
                    if (navigator.share) {
                      navigator.share({
                        title: property.title,
                        text: `${property.title} - BK Yatırım`,
                        url: window.location.href
                      });
                    } else {
                      navigator.clipboard.writeText(window.location.href);
                      alert('Link kopyalandı!');
                    }
                  }}
                  className="share-btn copy"
                  title="Linki Kopyala"
                >
                  ◇ Linki Kopyala
                </button>
              </div>
            </div>
            </div>
          </div>
        </div>


        {/* Similar Properties */}
        <SimilarProperties 
          property={property}
          user={user}
          onPropertyClick={(newPropertyId) => {
            // PropertyDetailPage'i yeni ilan ID'si ile yeniden yükle
            window.location.hash = `#property-detail-${newPropertyId}`;
            window.location.reload();
          }}
          onFavoriteToggle={handleFavoriteToggle}
          favoriteIds={[]} // Bu kısmı daha sonra kullanıcının favori listesi ile doldurabiliriz
        />

        {/* Contact Form Modal */}
        {showContactForm && (
          <ContactForm 
            property={property}
            user={user}
            onClose={() => setShowContactForm(false)}
          />
        )}

        {/* Report Form Modal */}
        {showReportForm && (
          <ReportForm 
            property={property}
            user={user}
            onClose={() => setShowReportForm(false)}
          />
        )}
      </div>
    </div>
  );
};

export default PropertyDetailPage; 