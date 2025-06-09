import React, { useState } from 'react';
import { reportProperty } from '../services/apiService';

const ReportForm = ({ property, user, onClose }) => {
  const [formData, setFormData] = useState({
    reason: '',
    description: '',
    contact_info: user?.email || ''
  });
  const [loading, setLoading] = useState(false);
  const [success, setSuccess] = useState(false);

  const reportReasons = [
    { value: 'fake', label: '🚫 Sahte İlan' },
    { value: 'sold', label: '✅ Satılmış/Kiralanmış' },
    { value: 'wrong_info', label: '❌ Yanlış Bilgiler' },
    { value: 'inappropriate', label: '⚠️ Uygunsuz İçerik' },
    { value: 'duplicate', label: '📋 Tekrar Eden İlan' },
    { value: 'spam', label: '📧 Spam/Reklam' },
    { value: 'other', label: '🔧 Diğer' }
  ];

  const handleChange = (e) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value
    });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);

    try {
      await reportProperty({
        property_id: property.id,
        ...formData
      });
      setSuccess(true);
      setTimeout(() => {
        onClose();
      }, 2000);
    } catch (error) {
      alert('Rapor gönderilirken bir hata oluştu: ' + error.message);
    } finally {
      setLoading(false);
    }
  };

  if (success) {
    return (
      <div className="report-form-overlay">
        <div className="report-form-modal">
          <div className="success-message">
            <span className="success-icon">✅</span>
            <h3>Rapor Gönderildi!</h3>
            <p>Raporunuz incelenmek üzere alındı. Teşekkür ederiz.</p>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="report-form-overlay" onClick={onClose}>
      <div className="report-form-modal" onClick={(e) => e.stopPropagation()}>
        <div className="modal-header">
          <h3>🚨 İlan Rapor Et</h3>
          <button className="close-btn" onClick={onClose}>×</button>
        </div>

        <div className="property-info-mini">
          <h4>{property.title}</h4>
          <p>📍 {property.district_name}, {property.city_name}</p>
        </div>

        <form onSubmit={handleSubmit} className="report-form">
          <div className="form-group">
            <label htmlFor="reason">Rapor Nedeni *</label>
            <select
              id="reason"
              name="reason"
              value={formData.reason}
              onChange={handleChange}
              required
            >
              <option value="">Bir neden seçin</option>
              {reportReasons.map(reason => (
                <option key={reason.value} value={reason.value}>
                  {reason.label}
                </option>
              ))}
            </select>
          </div>

          <div className="form-group">
            <label htmlFor="description">Açıklama *</label>
            <textarea
              id="description"
              name="description"
              value={formData.description}
              onChange={handleChange}
              required
              rows="4"
              placeholder="Lütfen sorunu detaylı olarak açıklayın..."
            />
          </div>

          <div className="form-group">
            <label htmlFor="contact_info">İletişim Bilginiz</label>
            <input
              type="email"
              id="contact_info"
              name="contact_info"
              value={formData.contact_info}
              onChange={handleChange}
              placeholder="ornek@email.com (isteğe bağlı)"
            />
            <small>Gerekirse sizinle iletişime geçebilmemiz için</small>
          </div>

          <div className="report-warning">
            <p>⚠️ <strong>Uyarı:</strong> Yanlış raporlama hesabınızın kısıtlanmasına neden olabilir.</p>
          </div>

          <div className="form-actions">
            <button type="button" onClick={onClose} className="btn btn-secondary">
              İptal
            </button>
            <button type="submit" disabled={loading} className="btn btn-danger">
              {loading ? 'Gönderiliyor...' : '🚨 Rapor Gönder'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};

export default ReportForm; 