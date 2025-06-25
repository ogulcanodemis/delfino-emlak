import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { getPropertyForAdmin, canAccessAdminPanel } from '../services/apiService';
import './AdminPropertyDetailPage.css';

const AdminPropertyDetailPage = ({ user }) => {
  const { id } = useParams();
  const navigate = useNavigate();
  const [property, setProperty] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [selectedImageIndex, setSelectedImageIndex] = useState(null);

  // Yetki kontrolü
  const hasAdminAccess = user && canAccessAdminPanel(user);

  useEffect(() => {
    if (!hasAdminAccess) {
      navigate('/');
      return;
    }

    loadProperty();
  }, [id, hasAdminAccess, navigate]);

  const loadProperty = async () => {
    try {
      setLoading(true);
      setError(null);
      const propertyData = await getPropertyForAdmin(id);
      setProperty(propertyData);
    } catch (error) {
      console.error('İlan yüklenirken hata:', error);
      setError('İlan yüklenirken hata oluştu: ' + error.message);
    } finally {
      setLoading(false);
    }
  };

  // Fiyat formatı
  const formatPrice = (price) => {
    return new Intl.NumberFormat('tr-TR', {
      style: 'currency',
      currency: 'TRY',
      minimumFractionDigits: 0
    }).format(price);
  };

  // Tarih formatı
  const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('tr-TR', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  };

  // Fotoğraf modal fonksiyonları
  const openImageModal = (index) => {
    setSelectedImageIndex(index);
  };

  const closeImageModal = () => {
    setSelectedImageIndex(null);
  };

  const nextImage = () => {
    if (property.images && selectedImageIndex < property.images.length - 1) {
      setSelectedImageIndex(selectedImageIndex + 1);
    }
  };

  const prevImage = () => {
    if (selectedImageIndex > 0) {
      setSelectedImageIndex(selectedImageIndex - 1);
    }
  };

  // Klavye kontrolü
  useEffect(() => {
    const handleKeyPress = (e) => {
      if (selectedImageIndex !== null) {
        if (e.key === 'Escape') {
          closeImageModal();
        } else if (e.key === 'ArrowRight') {
          nextImage();
        } else if (e.key === 'ArrowLeft') {
          prevImage();
        }
      }
    };

    document.addEventListener('keydown', handleKeyPress);
    return () => document.removeEventListener('keydown', handleKeyPress);
  }, [selectedImageIndex, property]);

  if (!hasAdminAccess) {
    return null; // Navigate zaten çalışacak
  }

  if (loading) {
    return (
      <div className="admin-property-detail-page">
        <div className="loading-container">
          <div className="spinner"></div>
          <p>İlan yükleniyor...</p>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="admin-property-detail-page">
        <div className="error-container">
          <h2>◆ Hata</h2>
          <p>{error}</p>
          <button onClick={() => navigate('/admin')} className="back-btn">
            ← Admin Paneline Dön
          </button>
        </div>
      </div>
    );
  }

  if (!property) {
    return (
      <div className="admin-property-detail-page">
        <div className="error-container">
          <h2>◆ İlan Bulunamadı</h2>
          <p>Aradığınız ilan bulunamadı.</p>
          <button onClick={() => navigate('/admin')} className="back-btn">
            ← Admin Paneline Dön
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="admin-property-detail-page">
      <div className="container">
        {/* Header */}
        <div className="page-header">
          <button onClick={() => navigate('/admin')} className="back-btn">
            ← Admin Paneline Dön
          </button>
          <h1>İlan Detayı - Admin Görünümü</h1>
        </div>

        {/* Property Header */}
        <div className="property-header">
          <div className="title-section">
            <h2>{property.title}</h2>
            <span className={`status-badge ${property.approval_status}`}>
              {property.approval_status === 'pending' ? 'Beklemede' :
               property.approval_status === 'approved' ? 'Onaylandı' : 'Reddedildi'}
            </span>
          </div>
          <div className="price-section">
            <div className="price">{formatPrice(property.price)}</div>
          </div>
        </div>

        {/* Property Details */}
        <div className="property-content">
          <div className="details-grid">
            <div className="detail-section">
              <h3>◆ Emlak Bilgileri</h3>
              <div className="detail-group">
                <div className="detail-item">
                  <span className="label">📍 Konum:</span>
                  <span className="value">
                    {property.address}<br/>
                    {property.city_name}, {property.district_name}
                  </span>
                </div>
                
                <div className="detail-item">
                  <span className="label">🏠 Emlak Tipi:</span>
                  <span className="value">{property.property_type_name}</span>
                </div>
                
                <div className="detail-item">
                  <span className="label">📊 Durum:</span>
                  <span className="value">{property.status_name}</span>
                </div>
                
                <div className="detail-item">
                  <span className="label">📐 Alan:</span>
                  <span className="value">{property.area} m²</span>
                </div>
                
                <div className="detail-item">
                  <span className="label">🚪 Oda Sayısı:</span>
                  <span className="value">{property.rooms} oda</span>
                </div>
                
                {property.bathrooms && (
                  <div className="detail-item">
                    <span className="label">🚿 Banyo:</span>
                    <span className="value">{property.bathrooms} banyo</span>
                  </div>
                )}
                
                <div className="detail-item">
                  <span className="label">🏢 Kat:</span>
                  <span className="value">
                    {property.floor}. kat
                    {property.total_floors && ` / ${property.total_floors}`}
                  </span>
                </div>
                
                <div className="detail-item">
                  <span className="label">🏗️ Bina Yaşı:</span>
                  <span className="value">{property.building_age} yıl</span>
                </div>
                
                <div className="detail-item">
                  <span className="label">🔥 Isıtma:</span>
                  <span className="value">{property.heating_type}</span>
                </div>
                
                <div className="detail-item">
                  <span className="label">🪑 Eşya Durumu:</span>
                  <span className="value">{property.furnishing}</span>
                </div>
              </div>
            </div>

            <div className="detail-section">
              <h3>◆ İlan Sahibi Bilgileri</h3>
              <div className="detail-group">
                <div className="detail-item">
                  <span className="label">👤 Ad Soyad:</span>
                  <span className="value">{property.user_name}</span>
                </div>
                
                <div className="detail-item">
                  <span className="label">📧 E-posta:</span>
                  <span className="value">{property.user_email}</span>
                </div>
                
                <div className="detail-item">
                  <span className="label">📞 Telefon:</span>
                  <span className="value">{property.user_phone}</span>
                </div>
                
                {property.user_company && (
                  <div className="detail-item">
                    <span className="label">🏢 Şirket:</span>
                    <span className="value">{property.user_company}</span>
                  </div>
                )}
              </div>

              <h3>◆ İlan Bilgileri</h3>
              <div className="detail-group">
                <div className="detail-item">
                  <span className="label">📅 Oluşturulma:</span>
                  <span className="value">{formatDate(property.created_at)}</span>
                </div>
                
                <div className="detail-item">
                  <span className="label">🔄 Güncellenme:</span>
                  <span className="value">{formatDate(property.updated_at)}</span>
                </div>
                
                <div className="detail-item">
                  <span className="label">👁️ Görüntülenme:</span>
                  <span className="value">{property.view_count} kez</span>
                </div>
                
                <div className="detail-item">
                  <span className="label">🔄 Onay Durumu:</span>
                  <span className="value">
                    <span className={`status-badge ${property.approval_status}`}>
                      {property.approval_status === 'pending' ? 'Beklemede' :
                       property.approval_status === 'approved' ? 'Onaylandı' : 'Reddedildi'}
                    </span>
                  </span>
                </div>
                
                {property.approved_at && (
                  <div className="detail-item">
                    <span className="label">✅ Onay Tarihi:</span>
                    <span className="value">{formatDate(property.approved_at)}</span>
                  </div>
                )}
                
                {property.rejection_reason && (
                  <div className="detail-item">
                    <span className="label">❌ Red Sebebi:</span>
                    <span className="value rejection-reason">{property.rejection_reason}</span>
                  </div>
                )}
              </div>
            </div>
          </div>

          {/* Description */}
          <div className="description-section">
            <h3>◆ Açıklama</h3>
            <div className="description-content">
              {property.description}
            </div>
          </div>

          {/* Features */}
          <div className="features-section">
            <h3>◆ Özellikler</h3>
            <div className="features-grid">
              {property.balcony && <span className="feature">◇ Balkon</span>}
              {property.elevator && <span className="feature">◇ Asansör</span>}
              {property.parking && <span className="feature">◇ Otopark</span>}
              {property.garden && <span className="feature">◇ Bahçe</span>}
              {property.swimming_pool && <span className="feature">◇ Havuz</span>}
              {property.security && <span className="feature">◇ Güvenlik</span>}
              {property.air_conditioning && <span className="feature">◇ Klima</span>}
              {property.internet && <span className="feature">◇ İnternet</span>}
              {property.credit_suitable && <span className="feature">◇ Krediye Uygun</span>}
              {property.exchange_suitable && <span className="feature">◇ Takasa Uygun</span>}
            </div>
          </div>

          {/* Images */}
          {property.images && property.images.length > 0 && (
            <div className="images-section">
              <h3>◆ Resimler ({property.images.length} adet)</h3>
              <div className="images-grid">
                {property.images.map((image, index) => (
                  <div key={index} className="image-item" onClick={() => openImageModal(index)}>
                    <img 
                      src={image.image_url || `https://bkyatirim.com/${image.image_path}`}
                      alt={`${property.title} - Resim ${index + 1}`}
                      onError={(e) => {
                        e.target.src = '/placeholder-property.jpg';
                      }}
                    />
                    <div className="image-overlay">
                      <span className="image-number">{index + 1}</span>
                      {image.is_primary && <span className="primary-badge">◆ Ana Resim</span>}
                    </div>
                  </div>
                ))}
              </div>
            </div>
          )}

          {/* Image Modal */}
          {selectedImageIndex !== null && property.images && (
            <div className="image-modal" onClick={closeImageModal}>
              <div className="modal-content" onClick={(e) => e.stopPropagation()}>
                <button className="modal-close" onClick={closeImageModal}>×</button>
                
                <div className="modal-image-container">
                  <img 
                    src={property.images[selectedImageIndex].image_url || `https://bkyatirim.com/${property.images[selectedImageIndex].image_path}`}
                    alt={`${property.title} - Resim ${selectedImageIndex + 1}`}
                    onError={(e) => {
                      e.target.src = '/placeholder-property.jpg';
                    }}
                  />
                </div>

                <div className="modal-controls">
                  <button 
                    className="modal-nav prev" 
                    onClick={prevImage}
                    disabled={selectedImageIndex === 0}
                  >
                    ‹
                  </button>
                  
                  <div className="modal-info">
                    <span className="image-counter">
                      {selectedImageIndex + 1} / {property.images.length}
                    </span>
                    {property.images[selectedImageIndex].is_primary && (
                      <span className="primary-indicator">◆ Ana Resim</span>
                    )}
                  </div>
                  
                  <button 
                    className="modal-nav next" 
                    onClick={nextImage}
                    disabled={selectedImageIndex === property.images.length - 1}
                  >
                    ›
                  </button>
                </div>
              </div>
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

export default AdminPropertyDetailPage; 