import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { createProperty, getPropertyTypes, getCities, getDistricts, uploadPropertyImages } from '../services/apiService';
import ImageUploader from '../components/ImageUploader';
import './AddPropertyPage.css';

const AddPropertyPage = ({ user }) => {
  const navigate = useNavigate();
  const [property, setProperty] = useState({
    title: '',
    description: '',
    price: '',
    property_type_id: '',
    status_id: 1,
    address: '',
    city_id: '',
    district_id: '',
    area: '',
    rooms: '',
    bathrooms: '',
    floor: '',
    total_floors: '',
    building_age: '',
    heating_type: 'Doğalgaz',
    furnishing: 'Eşyasız',
    balcony: 0,
    elevator: 0,
    parking: 0,
    garden: 0,
    swimming_pool: 0,
    security: 0,
    air_conditioning: 0,
    internet: 0,
    credit_suitable: 0,
    exchange_suitable: 0,
    is_active: 1
  });

  const [propertyTypes, setPropertyTypes] = useState([]);
  const [cities, setCities] = useState([]);
  const [districts, setDistricts] = useState([]);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  const [images, setImages] = useState([]);

  useEffect(() => {
    loadInitialData();
  }, []);

  useEffect(() => {
    if (property.city_id) {
      loadDistricts(property.city_id);
    }
  }, [property.city_id]);

  const loadInitialData = async () => {
    try {
      setLoading(true);
      
      // Paralel olarak verileri yükle
      const [propertyTypesData, citiesData] = await Promise.all([
        getPropertyTypes(),
        getCities()
      ]);

      setPropertyTypes(propertyTypesData);
      setCities(citiesData);

    } catch (error) {
      setError('Veriler yüklenirken bir hata oluştu: ' + error.message);
    } finally {
      setLoading(false);
    }
  };

  const loadDistricts = async (cityId) => {
    try {
      const districtsData = await getDistricts(cityId);
      setDistricts(districtsData);
    } catch (error) {
      console.error('İlçeler yüklenirken hata:', error);
    }
  };

  const handleChange = (e) => {
    const { name, value, type, checked } = e.target;
    
    setProperty(prev => ({
      ...prev,
      [name]: type === 'checkbox' ? (checked ? 1 : 0) : value
    }));

    // Şehir değiştiğinde ilçeleri sıfırla ve yeni ilçeleri yükle
    if (name === 'city_id') {
      setProperty(prev => ({ ...prev, district_id: '' }));
      setDistricts([]);
      if (value) {
        loadDistricts(value);
      }
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setSaving(true);
    setError('');
    setSuccess('');

    try {
      // Boş string değerleri temizle
      const cleanedProperty = { ...property };
      
      // Eğer furnishing boş ise default değer ata
      if (!cleanedProperty.furnishing || cleanedProperty.furnishing === '') {
        cleanedProperty.furnishing = 'Eşyasız';
      }
      
      // Eğer heating_type boş ise default değer ata
      if (!cleanedProperty.heating_type || cleanedProperty.heating_type === '') {
        cleanedProperty.heating_type = 'Doğalgaz';
      }

      // Önce ilanı oluştur
      const result = await createProperty(cleanedProperty);
      const propertyId = result.property_id;
      
      // Eğer fotoğraflar varsa yükle
      if (images.length > 0) {
        try {
          const imageFiles = images.map(img => img.file);
          await uploadPropertyImages(propertyId, imageFiles);
          setSuccess('İlan ve fotoğraflar başarıyla eklendi!');
        } catch (imageError) {
          console.error('Fotoğraf yükleme hatası:', imageError);
          setSuccess('İlan eklendi ancak bazı fotoğraflar yüklenemedi. Daha sonra düzenleyebilirsiniz.');
        }
      } else {
        setSuccess('İlan başarıyla eklendi!');
      }
      
      setTimeout(() => {
        navigate('/my-properties');
      }, 2000);
      
    } catch (error) {
      setError('İlan eklenirken bir hata oluştu: ' + error.message);
    } finally {
      setSaving(false);
    }
  };

  if (!user) {
    return (
      <div className="page-container">
        <div className="auth-required">
          <div className="auth-required-content">
            <h2>◆ Giriş Gerekli</h2>
            <p>İlan eklemek için giriş yapmalısınız.</p>
            <div className="auth-actions">
              <button 
                onClick={() => navigate('/login')} 
                className="btn btn-primary"
              >
                Giriş Yap
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
          <p>Veriler yükleniyor...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="page-container">
      <div className="add-property-page">
        <div className="page-header">
          <button onClick={() => navigate('/my-properties')} className="btn btn-secondary">
            ← Geri Dön
          </button>
          <h1>◆ Yeni İlan Ekle</h1>
          <p>İlan bilgilerinizi girin ve yayınlayın</p>
        </div>

        {error && (
          <div className="error-message">
            <p>{error}</p>
          </div>
        )}

        {success && (
          <div className="success-message">
            <p>{success}</p>
          </div>
        )}

        <form onSubmit={handleSubmit} className="add-property-form">
          <div className="form-section">
            <h3>◆ Temel Bilgiler</h3>
            
            <div className="form-row">
              <div className="form-group">
                <label htmlFor="title">İlan Başlığı *</label>
                <input
                  type="text"
                  id="title"
                  name="title"
                  value={property.title}
                  onChange={handleChange}
                  required
                  placeholder="İlan başlığını girin"
                />
              </div>

              <div className="form-group">
                <label htmlFor="property_type_id">Emlak Tipi *</label>
                <select
                  id="property_type_id"
                  name="property_type_id"
                  value={property.property_type_id}
                  onChange={handleChange}
                  required
                >
                  <option value="">Emlak tipi seçin</option>
                  {propertyTypes.map(type => (
                    <option key={type.id} value={type.id}>
                      {type.name}
                    </option>
                  ))}
                </select>
              </div>
            </div>

            <div className="form-group">
              <label htmlFor="description">Açıklama *</label>
              <textarea
                id="description"
                name="description"
                value={property.description}
                onChange={handleChange}
                required
                rows="4"
                placeholder="İlan açıklamasını girin"
              />
            </div>

            <div className="form-row">
              <div className="form-group">
                <label htmlFor="price">Fiyat (TL) *</label>
                <input
                  type="number"
                  id="price"
                  name="price"
                  value={property.price}
                  onChange={handleChange}
                  required
                  min="0"
                  step="1000"
                  placeholder="0"
                />
              </div>

              <div className="form-group">
                <label htmlFor="area">Alan (m²) *</label>
                <input
                  type="number"
                  id="area"
                  name="area"
                  value={property.area}
                  onChange={handleChange}
                  required
                  min="1"
                  placeholder="0"
                />
              </div>
            </div>
          </div>

          <div className="form-section">
            <h3>◆ Konum Bilgileri</h3>
            
            <div className="form-row">
              <div className="form-group">
                <label htmlFor="city_id">Şehir *</label>
                <select
                  id="city_id"
                  name="city_id"
                  value={property.city_id}
                  onChange={handleChange}
                  required
                >
                  <option value="">Şehir seçin</option>
                  {cities.map(city => (
                    <option key={city.id} value={city.id}>
                      {city.name}
                    </option>
                  ))}
                </select>
              </div>

              <div className="form-group">
                <label htmlFor="district_id">İlçe</label>
                <select
                  id="district_id"
                  name="district_id"
                  value={property.district_id}
                  onChange={handleChange}
                  disabled={!property.city_id}
                >
                  <option value="">İlçe seçin</option>
                  {districts.map(district => (
                    <option key={district.id} value={district.id}>
                      {district.name}
                    </option>
                  ))}
                </select>
              </div>
            </div>

            <div className="form-group">
              <label htmlFor="address">Adres</label>
              <textarea
                id="address"
                name="address"
                value={property.address}
                onChange={handleChange}
                rows="2"
                placeholder="Detaylı adres bilgisi"
              />
            </div>
          </div>

          <div className="form-section">
            <h3>◆ Detay Bilgileri</h3>
            
            <div className="form-row">
              <div className="form-group">
                <label htmlFor="rooms">Oda Sayısı</label>
                <input
                  type="number"
                  id="rooms"
                  name="rooms"
                  value={property.rooms}
                  onChange={handleChange}
                  min="0"
                  placeholder="0"
                />
              </div>

              <div className="form-group">
                <label htmlFor="bathrooms">Banyo Sayısı</label>
                <input
                  type="number"
                  id="bathrooms"
                  name="bathrooms"
                  value={property.bathrooms}
                  onChange={handleChange}
                  min="0"
                  placeholder="0"
                />
              </div>
            </div>

            <div className="form-row">
              <div className="form-group">
                <label htmlFor="floor">Bulunduğu Kat</label>
                <input
                  type="number"
                  id="floor"
                  name="floor"
                  value={property.floor}
                  onChange={handleChange}
                  placeholder="0"
                />
              </div>

              <div className="form-group">
                <label htmlFor="total_floors">Toplam Kat Sayısı</label>
                <input
                  type="number"
                  id="total_floors"
                  name="total_floors"
                  value={property.total_floors}
                  onChange={handleChange}
                  min="1"
                  placeholder="0"
                />
              </div>
            </div>

            <div className="form-row">
              <div className="form-group">
                <label htmlFor="building_age">Bina Yaşı</label>
                <input
                  type="number"
                  id="building_age"
                  name="building_age"
                  value={property.building_age}
                  onChange={handleChange}
                  min="0"
                  placeholder="0"
                />
              </div>

              <div className="form-group">
                <label htmlFor="heating_type">Isıtma Tipi</label>
                <select
                  id="heating_type"
                  name="heating_type"
                  value={property.heating_type}
                  onChange={handleChange}
                >
                  <option value="Doğalgaz">Doğalgaz</option>
                  <option value="Elektrik">Elektrik</option>
                  <option value="Kömür">Kömür</option>
                  <option value="Fuel-oil">Fuel-oil</option>
                  <option value="Güneş Enerjisi">Güneş Enerjisi</option>
                  <option value="Jeotermal">Jeotermal</option>
                  <option value="Klima">Klima</option>
                  <option value="Soba">Soba</option>
                  <option value="Şömine">Şömine</option>
                  <option value="Yok">Yok</option>
                </select>
              </div>
            </div>

            <div className="form-group">
              <label htmlFor="furnishing">Eşya Durumu</label>
              <select
                id="furnishing"
                name="furnishing"
                value={property.furnishing}
                onChange={handleChange}
              >
                <option value="Eşyasız">Eşyasız</option>
                <option value="Eşyalı">Eşyalı</option>
                <option value="Yarı Eşyalı">Yarı Eşyalı</option>
              </select>
            </div>
          </div>

          <div className="form-section">
            <h3>◆ Özellikler</h3>
            
            <div className="checkbox-grid">
              <label className="checkbox-item">
                <input
                  type="checkbox"
                  name="balcony"
                  checked={property.balcony === 1}
                  onChange={handleChange}
                />
                <span>◇ Balkon</span>
              </label>

              <label className="checkbox-item">
                <input
                  type="checkbox"
                  name="elevator"
                  checked={property.elevator === 1}
                  onChange={handleChange}
                />
                <span>◇ Asansör</span>
              </label>

              <label className="checkbox-item">
                <input
                  type="checkbox"
                  name="parking"
                  checked={property.parking === 1}
                  onChange={handleChange}
                />
                <span>◇ Otopark</span>
              </label>

              <label className="checkbox-item">
                <input
                  type="checkbox"
                  name="garden"
                  checked={property.garden === 1}
                  onChange={handleChange}
                />
                <span>◇ Bahçe</span>
              </label>

              <label className="checkbox-item">
                <input
                  type="checkbox"
                  name="swimming_pool"
                  checked={property.swimming_pool === 1}
                  onChange={handleChange}
                />
                <span>◇ Havuz</span>
              </label>

              <label className="checkbox-item">
                <input
                  type="checkbox"
                  name="security"
                  checked={property.security === 1}
                  onChange={handleChange}
                />
                <span>◇ Güvenlik</span>
              </label>

              <label className="checkbox-item">
                <input
                  type="checkbox"
                  name="air_conditioning"
                  checked={property.air_conditioning === 1}
                  onChange={handleChange}
                />
                <span>◇ Klima</span>
              </label>

              <label className="checkbox-item">
                <input
                  type="checkbox"
                  name="internet"
                  checked={property.internet === 1}
                  onChange={handleChange}
                />
                <span>◇ İnternet</span>
              </label>

              <label className="checkbox-item">
                <input
                  type="checkbox"
                  name="credit_suitable"
                  checked={property.credit_suitable === 1}
                  onChange={handleChange}
                />
                <span>◇ Krediye Uygun</span>
              </label>

              <label className="checkbox-item">
                <input
                  type="checkbox"
                  name="exchange_suitable"
                  checked={property.exchange_suitable === 1}
                  onChange={handleChange}
                />
                <span>◇ Takasa Uygun</span>
              </label>
            </div>
          </div>

          <div className="form-section">
            <ImageUploader 
              images={images}
              onImagesChange={setImages}
              maxImages={10}
            />
          </div>

          <div className="form-section">
            <h3>◆ İlan Durumu</h3>
            
            <div className="form-group">
              <label className="checkbox-item">
                <input
                  type="checkbox"
                  name="is_active"
                  checked={property.is_active === 1}
                  onChange={handleChange}
                />
                <span>◆ İlan Aktif</span>
              </label>
              <small>İlanın sitede görünür olmasını istiyorsanız işaretleyin</small>
            </div>
          </div>

          <div className="form-actions">
            <button 
              type="button" 
              onClick={() => navigate('/my-properties')}
              className="btn btn-secondary"
              disabled={saving}
            >
              İptal
            </button>
            <button 
              type="submit" 
              className="btn btn-primary"
              disabled={saving}
            >
              {saving ? 'Ekleniyor...' : '◆ İlan Ekle'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};

export default AddPropertyPage; 