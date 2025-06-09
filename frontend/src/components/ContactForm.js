import React, { useState } from 'react';
import { sendContactMessage } from '../services/apiService';

const ContactForm = ({ property, user, onClose }) => {
  const [formData, setFormData] = useState({
    name: user?.name || '',
    email: user?.email || '',
    phone: user?.phone || '',
    message: `Merhaba, "${property.title}" ilanınız hakkında bilgi almak istiyorum.`
  });
  const [loading, setLoading] = useState(false);
  const [success, setSuccess] = useState(false);

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
      await sendContactMessage({
        property_id: property.id,
        recipient_id: property.user_id,
        ...formData
      });
      setSuccess(true);
      setTimeout(() => {
        onClose();
      }, 2000);
    } catch (error) {
      alert('Mesaj gönderilirken bir hata oluştu: ' + error.message);
    } finally {
      setLoading(false);
    }
  };

  if (success) {
    return (
      <div className="contact-form-overlay">
        <div className="contact-form-modal">
          <div className="success-message">
            <span className="success-icon">✅</span>
            <h3>Mesajınız Gönderildi!</h3>
            <p>İlan sahibi en kısa sürede size dönüş yapacaktır.</p>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="contact-form-overlay" onClick={onClose}>
      <div className="contact-form-modal" onClick={(e) => e.stopPropagation()}>
        <div className="modal-header">
          <h3>📧 İlan Sahibi ile İletişim</h3>
          <button className="close-btn" onClick={onClose}>×</button>
        </div>

        <div className="property-info-mini">
          <h4>{property.title}</h4>
          <p>📍 {property.district_name}, {property.city_name}</p>
        </div>

        <form onSubmit={handleSubmit} className="contact-form">
          <div className="form-group">
            <label htmlFor="name">Ad Soyad *</label>
            <input
              type="text"
              id="name"
              name="name"
              value={formData.name}
              onChange={handleChange}
              required
              placeholder="Adınız ve soyadınız"
            />
          </div>

          <div className="form-group">
            <label htmlFor="email">E-posta *</label>
            <input
              type="email"
              id="email"
              name="email"
              value={formData.email}
              onChange={handleChange}
              required
              placeholder="ornek@email.com"
            />
          </div>

          <div className="form-group">
            <label htmlFor="phone">Telefon</label>
            <input
              type="tel"
              id="phone"
              name="phone"
              value={formData.phone}
              onChange={handleChange}
              placeholder="0555 123 45 67"
            />
          </div>

          <div className="form-group">
            <label htmlFor="message">Mesajınız *</label>
            <textarea
              id="message"
              name="message"
              value={formData.message}
              onChange={handleChange}
              required
              rows="5"
              placeholder="İlan hakkında sormak istediğiniz sorular..."
            />
          </div>

          <div className="form-actions">
            <button type="button" onClick={onClose} className="btn btn-secondary">
              İptal
            </button>
            <button type="submit" disabled={loading} className="btn btn-primary">
              {loading ? 'Gönderiliyor...' : '📧 Mesaj Gönder'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};

export default ContactForm; 