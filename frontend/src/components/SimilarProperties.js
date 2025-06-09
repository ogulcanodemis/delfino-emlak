import React, { useState, useEffect } from 'react';
import { getSimilarProperties } from '../services/apiService';
import PropertyCard from './PropertyCard';

const SimilarProperties = ({ property, user, onPropertyClick, onFavoriteToggle, favoriteIds = [] }) => {
  const [similarProperties, setSimilarProperties] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    if (property?.id) {
      loadSimilarProperties();
    }
  }, [property]);

  const loadSimilarProperties = async () => {
    try {
      setLoading(true);
      const data = await getSimilarProperties(
        property.id,
        property.city_id,
        property.property_type_id
      );
      setSimilarProperties(data);
    } catch (error) {
      setError('Benzer ilanlar yüklenirken bir hata oluştu');
      console.error('Similar properties error:', error);
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
    return (
      <div className="similar-properties">
        <h3>🔍 Benzer İlanlar</h3>
        <div className="loading-similar">
          <div className="spinner"></div>
          <p>Benzer ilanlar yükleniyor...</p>
        </div>
      </div>
    );
  }

  if (error || similarProperties.length === 0) {
    return (
      <div className="similar-properties">
        <h3>🔍 Benzer İlanlar</h3>
        <div className="no-similar">
          <p>Bu ilana benzer başka ilan bulunamadı.</p>
        </div>
      </div>
    );
  }

  return (
    <div className="similar-properties">
      <h3>🔍 Benzer İlanlar</h3>
      <p className="similar-description">
        {property.city_name} bölgesinde {property.property_type_name} kategorisindeki benzer ilanlar
      </p>
      
      <div className="similar-grid">
        {similarProperties.map(similarProperty => (
          <div 
            key={similarProperty.id} 
            onClick={() => onPropertyClick && onPropertyClick(similarProperty.id)}
            className="similar-property-item"
          >
            <PropertyCard 
              property={similarProperty}
              user={user}
              onFavoriteToggle={onFavoriteToggle}
              isFavorite={favoriteIds.includes(similarProperty.id)}
            />
          </div>
        ))}
      </div>
      
      {similarProperties.length >= 4 && (
        <div className="similar-footer">
          <p>Daha fazla benzer ilan için arama yapabilirsiniz.</p>
        </div>
      )}
    </div>
  );
};

export default SimilarProperties; 