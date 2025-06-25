import React, { useState } from 'react';
import { canViewPrice } from '../services/apiService';
import './UserPropertyCard.css';

const PropertyCard = ({ property, user, onFavoriteToggle, isFavorite }) => {
  const canSeePrice = canViewPrice(user);
  const [currentImageIndex, setCurrentImageIndex] = useState(0);
  const [touchStart, setTouchStart] = useState(null);
  const [touchEnd, setTouchEnd] = useState(null);
  
  const images = property.images || [];
  const hasMultipleImages = images.length > 1;
  
  // Swipe detection
  const minSwipeDistance = 50;
  
  const onTouchStart = (e) => {
    setTouchEnd(null);
    setTouchStart(e.targetTouches[0].clientX);
  };
  
  const onTouchMove = (e) => {
    setTouchEnd(e.targetTouches[0].clientX);
  };
  
  const onTouchEnd = () => {
    if (!touchStart || !touchEnd) return;
    
    const distance = touchStart - touchEnd;
    const isLeftSwipe = distance > minSwipeDistance;
    const isRightSwipe = distance < -minSwipeDistance;
    
    if (isLeftSwipe && currentImageIndex < images.length - 1) {
      setCurrentImageIndex(currentImageIndex + 1);
    }
    if (isRightSwipe && currentImageIndex > 0) {
      setCurrentImageIndex(currentImageIndex - 1);
    }
  };
  
  const goToPrevious = (e) => {
    e.stopPropagation();
    setCurrentImageIndex(
      currentImageIndex === 0 ? images.length - 1 : currentImageIndex - 1
    );
  };
  
  const goToNext = (e) => {
    e.stopPropagation();
    setCurrentImageIndex(
      currentImageIndex === images.length - 1 ? 0 : currentImageIndex + 1
    );
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
    return new Date(dateString).toLocaleDateString('tr-TR');
  };

  return (
    <div className="property-card">
      <div className="property-image">
        {images.length > 0 ? (
          <div 
            className="image-gallery"
            onTouchStart={hasMultipleImages ? onTouchStart : undefined}
            onTouchMove={hasMultipleImages ? onTouchMove : undefined}
            onTouchEnd={hasMultipleImages ? onTouchEnd : undefined}
          >
            <img 
              src={images[currentImageIndex]?.image_url || '/assets/images/no-image.jpg'} 
              alt={property.title}
              className="gallery-image"
              draggable={false}
            />
            
            {hasMultipleImages && (
              <>
                {/* Navigation Arrows */}
                <button 
                  className="gallery-nav prev"
                  onClick={goToPrevious}
                  style={{ display: currentImageIndex === 0 ? 'none' : 'flex' }}
                  aria-label="Önceki fotoğraf"
                >
                  ‹
                </button>
                
                <button 
                  className="gallery-nav next"
                  onClick={goToNext}
                  style={{ display: currentImageIndex === images.length - 1 ? 'none' : 'flex' }}
                  aria-label="Sonraki fotoğraf"
                >
                  ›
                </button>
                
                {/* Image Counter */}
                <div className="image-counter">
                  {currentImageIndex + 1}/{images.length}
                </div>
                
                {/* Dots Indicator */}
                <div className="gallery-dots">
                  {images.slice(0, 5).map((_, index) => (
                    <button
                      key={index}
                      className={`dot ${index === currentImageIndex ? 'active' : ''}`}
                      onClick={(e) => {
                        e.stopPropagation();
                        setCurrentImageIndex(index);
                      }}
                      aria-label={`Fotoğraf ${index + 1}`}
                    />
                  ))}
                  {images.length > 5 && (
                    <span className="more-indicator">+{images.length - 5}</span>
                  )}
                </div>
                
                {/* Swipe Hint for Mobile */}
                <div className="swipe-hint">
                  ◇ Kaydırın
                </div>
              </>
            )}
          </div>
        ) : (
          <div className="no-image">
            <span>○</span>
            <p>Fotoğraf Yok</p>
          </div>
        )}
        
        {user && (
          <button 
            className={`favorite-btn ${isFavorite ? 'active' : ''}`}
            onClick={(e) => {
              e.stopPropagation();
              onFavoriteToggle && onFavoriteToggle(property.id, isFavorite);
            }}
            title={isFavorite ? 'Favorilerden Çıkar' : 'Favorilere Ekle'}
          >
            {isFavorite ? '♥' : '♡'}
          </button>
        )}
        
        <div className="property-badges">
          <span className="status-badge">{property.status_name}</span>
          {Boolean(property.is_featured) && <span className="featured-badge">◆ Öne Çıkan</span>}
        </div>
      </div>
      
      <div className="property-info">
        <h3 className="property-title">{property.title}</h3>
        
        <div className="property-price">
          {canSeePrice ? (
            <span className="price">{formatPrice(property.price)}</span>
          ) : (
            <span className="price-hidden">
              ◉ Fiyat için üye olun
            </span>
          )}
        </div>
        
        <div className="property-location">
          ◉ {property.district_name}, {property.city_name}
        </div>
        
        <div className="property-details">
          <span className="user-detail-item">
            ◆ {property.property_type_name}
          </span>
          {Boolean(property.area) && (
            <span className="user-detail-item">
              ◇ {property.area} m²
            </span>
          )}
          {Boolean(property.rooms) && (
            <span className="user-detail-item">
              ◇ {property.rooms} oda
            </span>
          )}
        </div>
        
        <div className="property-meta">
          <span className="view-count">◇ {property.view_count} görüntülenme</span>
          <span className="date">◇ {formatDate(property.created_at)}</span>
        </div>
        
        {canSeePrice && Boolean(property.user_name) && (
          <div className="property-agent">
            ◉ {property.user_name}
            {Boolean(property.user_phone) && (
              <span className="agent-phone">☏ {property.user_phone}</span>
            )}
          </div>
        )}
      </div>
    </div>
  );
};

export default PropertyCard; 