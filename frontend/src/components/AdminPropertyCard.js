import React from 'react';

const AdminPropertyCard = ({ property, onApprove, onReject, onView }) => {
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

  const getStatusBadgeClass = (status) => {
    switch(status) {
      case 'pending': return 'pending';
      case 'approved': return 'approved';
      case 'rejected': return 'rejected';
      default: return '';
    }
  };

  return (
    <div className="admin-property-card">
      <div className="admin-property-image">
        {property.images && property.images.length > 0 ? (
          <img 
            src={property.images[0]?.image_url || '/assets/images/no-image.jpg'} 
            alt={property.title}
          />
        ) : (
          <div className="admin-no-image">
            <span>○</span>
            <p>Fotoğraf Yok</p>
          </div>
        )}
      </div>
      
      <div className="admin-property-content">
        <div className="admin-property-header">
          <h3>{property.title}</h3>
          <span className={`admin-status-badge ${getStatusBadgeClass(property.approval_status)}`}>
            {property.status_name}
          </span>
        </div>
        
        <div className="admin-property-price">
          {formatPrice(property.price)}
        </div>
        
        <div className="admin-property-details">
          <p><strong>Konum:</strong> {property.district_name}, {property.city_name}</p>
          <p><strong>Tür:</strong> {property.property_type_name}</p>
          {property.area && <p><strong>Alan:</strong> {property.area} m²</p>}
          {property.rooms && <p><strong>Oda:</strong> {property.rooms}</p>}
          <p><strong>Tarih:</strong> {formatDate(property.created_at)}</p>
          {property.user_name && <p><strong>Kullanıcı:</strong> {property.user_name}</p>}
        </div>
        
        {property.description && (
          <div className="admin-property-description">
            <p>{property.description.length > 150 
               ? `${property.description.substring(0, 150)}...` 
               : property.description}
            </p>
          </div>
        )}
        
        <div className="admin-property-actions">
          {property.approval_status === 'pending' && (
            <>
              <button 
                className="admin-approve-btn"
                onClick={() => onApprove && onApprove(property.id)}
              >
                Onayla
              </button>
              <button 
                className="admin-reject-btn"
                onClick={() => onReject && onReject(property.id)}
              >
                Reddet
              </button>
            </>
          )}
          <button 
            className="admin-view-btn"
            onClick={() => onView && onView(property.id)}
          >
            Görüntüle
          </button>
        </div>
      </div>
    </div>
  );
};

export default AdminPropertyCard;