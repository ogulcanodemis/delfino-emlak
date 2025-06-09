import React from 'react';
import { canViewPrice } from '../services/apiService';

const PropertyCard = ({ property, user, onFavoriteToggle, isFavorite }) => {
  const canSeePrice = canViewPrice(user);
  
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
    return new Date(dateString).toLocaleDateString('tr-TR');
  };

  return (
    <div className="property-card">
      <div className="property-image">
        {property.images && property.images.length > 0 ? (
          <img src={property.images[0].image_url} alt={property.title} />
        ) : (
          <div className="no-image">
            <span>📷</span>
            <p>Fotoğraf Yok</p>
          </div>
        )}
        
        {user && (
          <button 
            className={`favorite-btn ${isFavorite ? 'active' : ''}`}
            onClick={() => onFavoriteToggle && onFavoriteToggle(property.id)}
            title={isFavorite ? 'Favorilerden Çıkar' : 'Favorilere Ekle'}
          >
            {isFavorite ? '❤️' : '🤍'}
          </button>
        )}
        
        <div className="property-badges">
          <span className="status-badge">{property.status_name}</span>
          {property.is_featured && <span className="featured-badge">⭐ Öne Çıkan</span>}
        </div>
      </div>
      
      <div className="property-info">
        <h3 className="property-title">{property.title}</h3>
        
        <div className="property-price">
          {canSeePrice ? (
            <span className="price">{formatPrice(property.price)}</span>
          ) : (
            <span className="price-hidden">
              🔒 Fiyat için üye olun
            </span>
          )}
        </div>
        
        <div className="property-location">
          📍 {property.district_name}, {property.city_name}
        </div>
        
        <div className="property-details">
          <span className="detail-item">
            🏠 {property.property_type_name}
          </span>
          {property.area && (
            <span className="detail-item">
              📐 {property.area} m²
            </span>
          )}
          {property.rooms && (
            <span className="detail-item">
              🛏️ {property.rooms} oda
            </span>
          )}
        </div>
        
        <div className="property-meta">
          <span className="view-count">👁️ {property.view_count} görüntülenme</span>
          <span className="date">📅 {formatDate(property.created_at)}</span>
        </div>
        
        {canSeePrice && property.user_name && (
          <div className="property-agent">
            👤 {property.user_name}
            {property.user_phone && (
              <span className="agent-phone">📞 {property.user_phone}</span>
            )}
          </div>
        )}
      </div>
    </div>
  );
};

export default PropertyCard; 