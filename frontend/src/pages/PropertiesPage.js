import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { canViewPrice } from '../services/authService';
import { ROOM_FILTER_OPTIONS } from '../utils/constants';
import { 
  getProperties, 
  getPropertyTypes, 
  getCities, 
  getDistricts,
  formatPrice,
  addToFavorites,
  removeFromFavorites,
  getFavoriteIds
} from '../services/apiService';
import PropertyCard from '../components/PropertyCard';

const PropertiesPage = ({ user }) => {
  const navigate = useNavigate();
  const [properties, setProperties] = useState([]);
  const [propertyTypes, setPropertyTypes] = useState([]);
  const [cities, setCities] = useState([]);
  const [districts, setDistricts] = useState([]);
  const [pagination, setPagination] = useState({});
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [favoriteIds, setFavoriteIds] = useState([]);
  const [showFilters, setShowFilters] = useState(false);
  
  // Filtre durumları
  const [filters, setFilters] = useState({
    search: '',
    city_id: '',
    district_id: '',
    property_type_id: '',
    status_id: '',
    min_price: '',
    max_price: '',
    min_area: '',
    max_area: '',
    rooms: '',
    page: 1
  });

  // Sayfa yüklendiğinde temel verileri al
  useEffect(() => {
    const loadInitialData = async () => {
      try {
        setLoading(true);
        
        // Paralel olarak temel verileri çek
        const [typesData, citiesData] = await Promise.all([
          getPropertyTypes(),
          getCities()
        ]);
        
        setPropertyTypes(typesData);
        setCities(citiesData);
        
      } catch (error) {
        console.error('Temel veri yükleme hatası:', error);
        setError('Temel veriler yüklenirken hata oluştu');
      }
    };

    loadInitialData();
  }, []);

  // Kullanıcı favori ID'lerini yükle
  useEffect(() => {
    if (user) {
      loadFavoriteIds();
    } else {
      setFavoriteIds([]);
    }
  }, [user]);

  const loadFavoriteIds = async () => {
    try {
      const favoriteIds = await getFavoriteIds();
      setFavoriteIds(favoriteIds);
    } catch (error) {
      console.error('Favori ID\'leri yüklenirken hata:', error);
      setFavoriteIds([]);
    }
  };

  // İlanları yükle
  useEffect(() => {
    const loadProperties = async () => {
      try {
        setLoading(true);
        setError('');
        
        const data = await getProperties(filters);
        setProperties(data.properties);
        setPagination(data.pagination);
        
      } catch (error) {
        console.error('İlan yükleme hatası:', error);
        setError('İlanlar yüklenirken hata oluştu');
        setProperties([]);
      }
      setLoading(false);
    };

    loadProperties();
  }, [filters]);

  // Şehir değiştiğinde ilçeleri yükle
  useEffect(() => {
    const loadDistricts = async () => {
      if (filters.city_id) {
        try {
          const districtsData = await getDistricts(filters.city_id);
          setDistricts(districtsData);
        } catch (error) {
          console.error('İlçe yükleme hatası:', error);
          setDistricts([]);
        }
      } else {
        setDistricts([]);
        setFilters(prev => ({ ...prev, district_id: '' }));
      }
    };

    loadDistricts();
  }, [filters.city_id]);

  // Filtre değişikliği
  const handleFilterChange = (key, value) => {
    setFilters(prev => ({
      ...prev,
      [key]: value,
      page: 1, // Filtre değiştiğinde ilk sayfaya dön
      ...(key === 'city_id' && { district_id: '' }) // Şehir değişirse ilçeyi sıfırla
    }));
  };

  // Sayfa değişikliği
  const handlePageChange = (page) => {
    setFilters(prev => ({ ...prev, page }));
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  // Filtreleri temizle
  const clearFilters = () => {
    setFilters({
      search: '',
      city_id: '',
      district_id: '',
      property_type_id: '',
      status_id: '',
      min_price: '',
      max_price: '',
      min_area: '',
      max_area: '',
      rooms: '',
      page: 1
    });
  };

  const handleFavoriteToggle = async (propertyId, isFavorite) => {
    try {
      if (isFavorite) {
        await removeFromFavorites(propertyId);
        setFavoriteIds(prev => prev.filter(id => id !== propertyId));
      } else {
        await addToFavorites(propertyId);
        setFavoriteIds(prev => [...prev, propertyId]);
      }
    } catch (error) {
      console.error('Favori işlemi sırasında hata:', error);
      alert('Favori işlemi sırasında bir hata oluştu: ' + error.message);
    }
  };

  // Aktif filtre sayısını hesapla
  const getActiveFilterCount = () => {
    return Object.entries(filters).filter(([key, value]) => 
      key !== 'page' && value !== ''
    ).length;
  };

  return (
    <div style={{
      background: 'linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%)',
      minHeight: '100vh',
      paddingTop: '2rem'
    }}>
      <div style={{
        maxWidth: '1400px',
        margin: '0 auto',
        padding: '0 2rem'
      }}>
        {/* Header */}
        <div style={{
          textAlign: 'center',
          marginBottom: '3rem',
          animation: 'fadeInUp 0.8s ease-out'
        }}>
          <div style={{
            fontSize: '4rem',
            marginBottom: '1rem'
          }}>
            ◆
          </div>
          <h1 style={{
            fontSize: '3rem',
            fontWeight: '700',
            marginBottom: '1rem',
            background: 'linear-gradient(135deg, #d4af37 0%, #b8941f 100%)',
            WebkitBackgroundClip: 'text',
            WebkitTextFillColor: 'transparent',
            backgroundClip: 'text'
          }}>
            Emlak İlanları
          </h1>
          <p style={{
            fontSize: '1.2rem',
            color: '#6b7280',
            maxWidth: '600px',
            margin: '0 auto'
          }}>
            Hayalinizdeki evi bulun, binlerce ilan arasından size en uygun olanı keşfedin
          </p>
        </div>

        {/* Hata mesajı */}
        {error && (
          <div style={{
            background: 'linear-gradient(135deg, #fee2e2 0%, #fecaca 100%)',
            color: '#dc2626',
            padding: '1.5rem',
            borderRadius: '16px',
            marginBottom: '2rem',
            textAlign: 'center',
            border: '1px solid #fca5a5',
            animation: 'fadeIn 0.5s ease-out',
            boxShadow: '0 4px 15px rgba(220, 38, 38, 0.1)'
          }}>
            <div style={{
              fontSize: '2rem',
              marginBottom: '0.5rem'
            }}>
              ⚠️
            </div>
            <p style={{
              fontSize: '1.1rem',
              fontWeight: '500'
            }}>
              {error}
            </p>
          </div>
        )}

        {/* Filtreler */}
        <div style={{
          background: 'white',
          borderRadius: '20px',
          padding: '2rem',
          marginBottom: '2rem',
          boxShadow: '0 10px 30px rgba(0, 0, 0, 0.1)',
          border: '1px solid rgba(212, 175, 55, 0.1)',
          animation: 'fadeInUp 0.8s ease-out 0.2s both'
        }}>
          {/* Filtre Header */}
          <div style={{
            display: 'flex',
            justifyContent: 'space-between',
            alignItems: 'center',
            marginBottom: '1.5rem',
            paddingBottom: '1rem',
            borderBottom: '2px solid #f1f5f9'
          }}>
            <div style={{
              display: 'flex',
              alignItems: 'center',
              gap: '1rem'
            }}>
              <h3 style={{
                fontSize: '1.5rem',
                fontWeight: '600',
                color: '#374151',
                margin: 0,
                display: 'flex',
                alignItems: 'center',
                gap: '0.5rem'
              }}>
                ◇ Filtreler
                {getActiveFilterCount() > 0 && (
                  <span style={{
                    background: 'linear-gradient(135deg, #d4af37 0%, #b8941f 100%)',
                    color: 'white',
                    fontSize: '0.8rem',
                    padding: '0.25rem 0.75rem',
                    borderRadius: '50px',
                    fontWeight: '600'
                  }}>
                    {getActiveFilterCount()}
                  </span>
                )}
              </h3>
            </div>
            
            <button
              onClick={() => setShowFilters(!showFilters)}
              style={{
                background: showFilters ? 'linear-gradient(135deg, #d4af37 0%, #b8941f 100%)' : 'transparent',
                color: showFilters ? 'white' : '#d4af37',
                border: `2px solid ${showFilters ? 'transparent' : '#d4af37'}`,
                padding: '0.75rem 1.5rem',
                borderRadius: '12px',
                fontSize: '1rem',
                fontWeight: '600',
                cursor: 'pointer',
                transition: 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)',
                display: 'flex',
                alignItems: 'center',
                gap: '0.5rem'
              }}
              onMouseEnter={(e) => {
                if (!showFilters) {
                  e.target.style.background = 'rgba(212, 175, 55, 0.1)';
                }
              }}
              onMouseLeave={(e) => {
                if (!showFilters) {
                  e.target.style.background = 'transparent';
                }
              }}
            >
              {showFilters ? '▲' : '▼'} {showFilters ? 'Gizle' : 'Göster'}
            </button>
          </div>

          {/* Filtre İçeriği */}
          <div style={{
            display: showFilters ? 'block' : 'none',
            animation: showFilters ? 'fadeIn 0.3s ease-out' : 'none'
          }}>
            {/* Arama */}
            <div style={{
              marginBottom: '2rem'
            }}>
              <div style={{
                position: 'relative'
              }}>
                <input
                  type="text"
                  placeholder="◇ İlan başlığı, açıklama veya konum ara..."
                  value={filters.search}
                  onChange={(e) => handleFilterChange('search', e.target.value)}
                  style={{
                    width: '100%',
                    padding: '1rem 1.5rem',
                    borderRadius: '12px',
                    border: '2px solid #e5e7eb',
                    fontSize: '1.1rem',
                    transition: 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)',
                    background: '#f9fafb'
                  }}
                  onFocus={(e) => {
                    e.target.style.borderColor = '#d4af37';
                    e.target.style.background = 'white';
                    e.target.style.boxShadow = '0 0 0 3px rgba(212, 175, 55, 0.1)';
                  }}
                  onBlur={(e) => {
                    e.target.style.borderColor = '#e5e7eb';
                    e.target.style.background = '#f9fafb';
                    e.target.style.boxShadow = 'none';
                  }}
                />
              </div>
            </div>

            {/* Konum Filtreleri */}
            <div style={{
              display: 'grid',
              gridTemplateColumns: 'repeat(auto-fit, minmax(250px, 1fr))',
              gap: '1.5rem',
              marginBottom: '2rem'
            }}>
              <div>
                <label style={{
                  display: 'block',
                  marginBottom: '0.5rem',
                  fontWeight: '600',
                  color: '#374151',
                  fontSize: '1rem'
                }}>
                  🏙️ Şehir
                </label>
                <select
                  value={filters.city_id}
                  onChange={(e) => handleFilterChange('city_id', e.target.value)}
                  style={{
                    width: '100%',
                    padding: '0.75rem 1rem',
                    borderRadius: '12px',
                    border: '2px solid #e5e7eb',
                    fontSize: '1rem',
                    background: 'white',
                    transition: 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)'
                  }}
                  onFocus={(e) => {
                    e.target.style.borderColor = '#d4af37';
                    e.target.style.boxShadow = '0 0 0 3px rgba(212, 175, 55, 0.1)';
                  }}
                  onBlur={(e) => {
                    e.target.style.borderColor = '#e5e7eb';
                    e.target.style.boxShadow = 'none';
                  }}
                >
                  <option value="">Tüm Şehirler</option>
                  {cities.map(city => (
                    <option key={city.id} value={city.id}>
                      {city.name}
                    </option>
                  ))}
                </select>
              </div>

              <div>
                <label style={{
                  display: 'block',
                  marginBottom: '0.5rem',
                  fontWeight: '600',
                  color: '#374151',
                  fontSize: '1rem'
                }}>
                  🏘️ İlçe
                </label>
                <select
                  value={filters.district_id}
                  onChange={(e) => handleFilterChange('district_id', e.target.value)}
                  disabled={!filters.city_id}
                  style={{
                    width: '100%',
                    padding: '0.75rem 1rem',
                    borderRadius: '12px',
                    border: '2px solid #e5e7eb',
                    fontSize: '1rem',
                    background: filters.city_id ? 'white' : '#f3f4f6',
                    transition: 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)',
                    opacity: filters.city_id ? 1 : 0.6
                  }}
                  onFocus={(e) => {
                    if (filters.city_id) {
                      e.target.style.borderColor = '#d4af37';
                      e.target.style.boxShadow = '0 0 0 3px rgba(212, 175, 55, 0.1)';
                    }
                  }}
                  onBlur={(e) => {
                    e.target.style.borderColor = '#e5e7eb';
                    e.target.style.boxShadow = 'none';
                  }}
                >
                  <option value="">Tüm İlçeler</option>
                  {districts.map(district => (
                    <option key={district.id} value={district.id}>
                      {district.name}
                    </option>
                  ))}
                </select>
              </div>
            </div>

            {/* Emlak Özellikleri */}
            <div style={{
              display: 'grid',
              gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))',
              gap: '1.5rem',
              marginBottom: '2rem'
            }}>
              <div>
                <label style={{
                  display: 'block',
                  marginBottom: '0.5rem',
                  fontWeight: '600',
                  color: '#374151',
                  fontSize: '1rem'
                }}>
                  🏢 Emlak Tipi
                </label>
                <select
                  value={filters.property_type_id}
                  onChange={(e) => handleFilterChange('property_type_id', e.target.value)}
                  style={{
                    width: '100%',
                    padding: '0.75rem 1rem',
                    borderRadius: '12px',
                    border: '2px solid #e5e7eb',
                    fontSize: '1rem',
                    background: 'white',
                    transition: 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)'
                  }}
                  onFocus={(e) => {
                    e.target.style.borderColor = '#d4af37';
                    e.target.style.boxShadow = '0 0 0 3px rgba(212, 175, 55, 0.1)';
                  }}
                  onBlur={(e) => {
                    e.target.style.borderColor = '#e5e7eb';
                    e.target.style.boxShadow = 'none';
                  }}
                >
                  <option value="">Tüm Tipler</option>
                  {propertyTypes.map(type => (
                    <option key={type.id} value={type.id}>
                      {type.name}
                    </option>
                  ))}
                </select>
              </div>

              <div>
                <label style={{
                  display: 'block',
                  marginBottom: '0.5rem',
                  fontWeight: '600',
                  color: '#374151',
                  fontSize: '1rem'
                }}>
                  ◇ Durum
                </label>
                <select
                  value={filters.status_id}
                  onChange={(e) => handleFilterChange('status_id', e.target.value)}
                  style={{
                    width: '100%',
                    padding: '0.75rem 1rem',
                    borderRadius: '12px',
                    border: '2px solid #e5e7eb',
                    fontSize: '1rem',
                    background: 'white',
                    transition: 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)'
                  }}
                  onFocus={(e) => {
                    e.target.style.borderColor = '#d4af37';
                    e.target.style.boxShadow = '0 0 0 3px rgba(212, 175, 55, 0.1)';
                  }}
                  onBlur={(e) => {
                    e.target.style.borderColor = '#e5e7eb';
                    e.target.style.boxShadow = 'none';
                  }}
                >
                  <option value="">Tümü</option>
                  <option value="1">◆ Satılık</option>
                  <option value="2">◆ Kiralık</option>
                </select>
              </div>

              <div>
                <label style={{
                  display: 'block',
                  marginBottom: '0.5rem',
                  fontWeight: '600',
                  color: '#374151',
                  fontSize: '1rem'
                }}>
                  🚪 Oda Sayısı
                </label>
                <select
                  value={filters.rooms}
                  onChange={(e) => handleFilterChange('rooms', e.target.value)}
                  style={{
                    width: '100%',
                    padding: '0.75rem 1rem',
                    borderRadius: '12px',
                    border: '2px solid #e5e7eb',
                    fontSize: '1rem',
                    background: 'white',
                    transition: 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)'
                  }}
                  onFocus={(e) => {
                    e.target.style.borderColor = '#d4af37';
                    e.target.style.boxShadow = '0 0 0 3px rgba(212, 175, 55, 0.1)';
                  }}
                  onBlur={(e) => {
                    e.target.style.borderColor = '#e5e7eb';
                    e.target.style.boxShadow = 'none';
                  }}
                >
                  {ROOM_FILTER_OPTIONS.map(option => (
                    <option key={option.value} value={option.value}>
                      {option.label}
                    </option>
                  ))}
                </select>
              </div>
            </div>

            {/* Fiyat ve Alan Filtreleri */}
            {canViewPrice(user) && (
              <div style={{
                display: 'grid',
                gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))',
                gap: '1.5rem',
                marginBottom: '2rem'
              }}>
                <div>
                  <label style={{
                    display: 'block',
                    marginBottom: '0.5rem',
                    fontWeight: '600',
                    color: '#374151',
                    fontSize: '1rem'
                  }}>
                    ◆ Min Fiyat
                  </label>
                  <input
                    type="number"
                    placeholder="Minimum fiyat"
                    value={filters.min_price}
                    onChange={(e) => handleFilterChange('min_price', e.target.value)}
                    style={{
                      width: '100%',
                      padding: '0.75rem 1rem',
                      borderRadius: '12px',
                      border: '2px solid #e5e7eb',
                      fontSize: '1rem',
                      background: 'white',
                      transition: 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)'
                    }}
                    onFocus={(e) => {
                      e.target.style.borderColor = '#d4af37';
                      e.target.style.boxShadow = '0 0 0 3px rgba(212, 175, 55, 0.1)';
                    }}
                    onBlur={(e) => {
                      e.target.style.borderColor = '#e5e7eb';
                      e.target.style.boxShadow = 'none';
                    }}
                  />
                </div>
                <div>
                  <label style={{
                    display: 'block',
                    marginBottom: '0.5rem',
                    fontWeight: '600',
                    color: '#374151',
                    fontSize: '1rem'
                  }}>
                    ◆ Max Fiyat
                  </label>
                  <input
                    type="number"
                    placeholder="Maksimum fiyat"
                    value={filters.max_price}
                    onChange={(e) => handleFilterChange('max_price', e.target.value)}
                    style={{
                      width: '100%',
                      padding: '0.75rem 1rem',
                      borderRadius: '12px',
                      border: '2px solid #e5e7eb',
                      fontSize: '1rem',
                      background: 'white',
                      transition: 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)'
                    }}
                    onFocus={(e) => {
                      e.target.style.borderColor = '#d4af37';
                      e.target.style.boxShadow = '0 0 0 3px rgba(212, 175, 55, 0.1)';
                    }}
                    onBlur={(e) => {
                      e.target.style.borderColor = '#e5e7eb';
                      e.target.style.boxShadow = 'none';
                    }}
                  />
                </div>
              </div>
            )}

            <div style={{
              display: 'grid',
              gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))',
              gap: '1.5rem',
              marginBottom: '2rem'
            }}>
              <div>
                <label style={{
                  display: 'block',
                  marginBottom: '0.5rem',
                  fontWeight: '600',
                  color: '#374151',
                  fontSize: '1rem'
                }}>
                  📐 Min Alan (m²)
                </label>
                <input
                  type="number"
                  placeholder="Minimum alan"
                  value={filters.min_area}
                  onChange={(e) => handleFilterChange('min_area', e.target.value)}
                  style={{
                    width: '100%',
                    padding: '0.75rem 1rem',
                    borderRadius: '12px',
                    border: '2px solid #e5e7eb',
                    fontSize: '1rem',
                    background: 'white',
                    transition: 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)'
                  }}
                  onFocus={(e) => {
                    e.target.style.borderColor = '#d4af37';
                    e.target.style.boxShadow = '0 0 0 3px rgba(212, 175, 55, 0.1)';
                  }}
                  onBlur={(e) => {
                    e.target.style.borderColor = '#e5e7eb';
                    e.target.style.boxShadow = 'none';
                  }}
                />
              </div>
              <div>
                <label style={{
                  display: 'block',
                  marginBottom: '0.5rem',
                  fontWeight: '600',
                  color: '#374151',
                  fontSize: '1rem'
                }}>
                  📐 Max Alan (m²)
                </label>
                <input
                  type="number"
                  placeholder="Maksimum alan"
                  value={filters.max_area}
                  onChange={(e) => handleFilterChange('max_area', e.target.value)}
                  style={{
                    width: '100%',
                    padding: '0.75rem 1rem',
                    borderRadius: '12px',
                    border: '2px solid #e5e7eb',
                    fontSize: '1rem',
                    background: 'white',
                    transition: 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)'
                  }}
                  onFocus={(e) => {
                    e.target.style.borderColor = '#d4af37';
                    e.target.style.boxShadow = '0 0 0 3px rgba(212, 175, 55, 0.1)';
                  }}
                  onBlur={(e) => {
                    e.target.style.borderColor = '#e5e7eb';
                    e.target.style.boxShadow = 'none';
                  }}
                />
              </div>
            </div>

            {/* Filtre Butonları */}
            <div style={{
              display: 'flex',
              gap: '1rem',
              justifyContent: 'center',
              flexWrap: 'wrap'
            }}>
              <button 
                onClick={clearFilters}
                style={{
                  background: 'transparent',
                  color: '#6b7280',
                  border: '2px solid #e5e7eb',
                  padding: '0.75rem 1.5rem',
                  borderRadius: '12px',
                  fontSize: '1rem',
                  fontWeight: '600',
                  cursor: 'pointer',
                  transition: 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)',
                  display: 'flex',
                  alignItems: 'center',
                  gap: '0.5rem'
                }}
                onMouseEnter={(e) => {
                  e.target.style.background = '#f3f4f6';
                  e.target.style.borderColor = '#d1d5db';
                }}
                onMouseLeave={(e) => {
                  e.target.style.background = 'transparent';
                  e.target.style.borderColor = '#e5e7eb';
                }}
              >
                🗑️ Filtreleri Temizle
              </button>
            </div>
          </div>
        </div>

        {/* Sonuçlar */}
        {loading ? (
          <div style={{
            display: 'flex',
            flexDirection: 'column',
            alignItems: 'center',
            justifyContent: 'center',
            padding: '4rem',
            animation: 'fadeIn 0.5s ease-out'
          }}>
            <div style={{
              width: '80px',
              height: '80px',
              border: '6px solid #f3f4f6',
              borderTop: '6px solid #d4af37',
              borderRadius: '50%',
              animation: 'spin 1s linear infinite',
              marginBottom: '2rem'
            }}></div>
            <h3 style={{
              fontSize: '1.5rem',
              fontWeight: '600',
              color: '#374151',
              marginBottom: '0.5rem'
            }}>
              Yükleniyor...
            </h3>
            <p style={{
              color: '#6b7280',
              fontSize: '1.1rem'
            }}>
              İlanlar getiriliyor, lütfen bekleyin...
            </p>
          </div>
        ) : (
          <>
            {/* Sonuç Bilgisi */}
            <div style={{
              background: 'white',
              borderRadius: '16px',
              padding: '1.5rem',
              marginBottom: '2rem',
              boxShadow: '0 4px 15px rgba(0, 0, 0, 0.05)',
              border: '1px solid rgba(212, 175, 55, 0.1)',
              animation: 'fadeInUp 0.8s ease-out 0.3s both'
            }}>
              <div style={{
                display: 'flex',
                justifyContent: 'space-between',
                alignItems: 'center',
                flexWrap: 'wrap',
                gap: '1rem'
              }}>
                <div>
                  <p style={{
                    fontSize: '1.2rem',
                    fontWeight: '600',
                    color: '#374151',
                    margin: 0,
                    display: 'flex',
                    alignItems: 'center',
                    gap: '0.5rem'
                  }}>
                    ◆ <strong style={{
                      background: 'linear-gradient(135deg, #d4af37 0%, #b8941f 100%)',
                      WebkitBackgroundClip: 'text',
                      WebkitTextFillColor: 'transparent',
                      backgroundClip: 'text'
                    }}>
                      {pagination.total || 0}
                    </strong> ilan bulundu
                    {pagination.current_page && pagination.total_pages && (
                      <span style={{
                        color: '#6b7280',
                        fontSize: '1rem',
                        fontWeight: '500'
                      }}>
                        (Sayfa {pagination.current_page} / {pagination.total_pages})
                      </span>
                    )}
                  </p>
                </div>
                
                {properties.length > 0 && (
                  <div style={{
                    display: 'flex',
                    alignItems: 'center',
                    gap: '0.5rem',
                    color: '#6b7280',
                    fontSize: '0.9rem'
                  }}>
                    ⏱️ Son güncelleme: {new Date().toLocaleTimeString('tr-TR')}
                  </div>
                )}
              </div>
            </div>

            {/* İlan Listesi */}
            {properties.length > 0 ? (
              <>
                <div style={{
                  display: 'grid',
                  gridTemplateColumns: 'repeat(auto-fit, minmax(350px, 1fr))',
                  gap: '2rem',
                  marginBottom: '3rem',
                  animation: 'fadeInUp 0.8s ease-out 0.4s both'
                }}>
                  {properties.map((property, index) => (
                    <div 
                      key={property.id} 
                      onClick={() => navigate(`/property/${property.id}`)}
                      style={{
                        cursor: 'pointer',
                        transition: 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)',
                        animation: `fadeInUp 0.8s ease-out ${0.1 * index}s both`
                      }}
                      onMouseEnter={(e) => {
                        e.currentTarget.style.transform = 'translateY(-5px)';
                      }}
                      onMouseLeave={(e) => {
                        e.currentTarget.style.transform = 'translateY(0)';
                      }}
                    >
                      <PropertyCard 
                        property={property}
                        user={user}
                        onFavoriteToggle={handleFavoriteToggle}
                        isFavorite={favoriteIds.includes(property.id)}
                      />
                    </div>
                  ))}
                </div>

                {/* Sayfalama */}
                {pagination.total_pages > 1 && (
                  <div style={{
                    display: 'flex',
                    justifyContent: 'center',
                    marginTop: '3rem',
                    animation: 'fadeInUp 0.8s ease-out 0.5s both'
                  }}>
                    <div style={{
                      display: 'flex',
                      gap: '0.5rem',
                      background: 'white',
                      padding: '1rem',
                      borderRadius: '16px',
                      boxShadow: '0 4px 15px rgba(0, 0, 0, 0.1)',
                      border: '1px solid rgba(212, 175, 55, 0.1)'
                    }}>
                      {pagination.current_page > 1 && (
                        <button 
                          onClick={() => handlePageChange(pagination.current_page - 1)}
                          style={{
                            background: 'transparent',
                            color: '#d4af37',
                            border: '2px solid #d4af37',
                            padding: '0.75rem 1.5rem',
                            borderRadius: '12px',
                            fontSize: '1rem',
                            fontWeight: '600',
                            cursor: 'pointer',
                            transition: 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)'
                          }}
                          onMouseEnter={(e) => {
                            e.target.style.background = '#d4af37';
                            e.target.style.color = 'white';
                          }}
                          onMouseLeave={(e) => {
                            e.target.style.background = 'transparent';
                            e.target.style.color = '#d4af37';
                          }}
                        >
                          ← Önceki
                        </button>
                      )}
                      
                      {Array.from({ length: Math.min(5, pagination.total_pages) }, (_, i) => {
                        const page = i + 1;
                        const isActive = page === pagination.current_page;
                        return (
                          <button
                            key={page}
                            onClick={() => handlePageChange(page)}
                            style={{
                              background: isActive ? 'linear-gradient(135deg, #d4af37 0%, #b8941f 100%)' : 'transparent',
                              color: isActive ? 'white' : '#d4af37',
                              border: `2px solid ${isActive ? 'transparent' : '#d4af37'}`,
                              padding: '0.75rem 1rem',
                              borderRadius: '12px',
                              fontSize: '1rem',
                              fontWeight: '600',
                              cursor: 'pointer',
                              transition: 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)',
                              minWidth: '50px'
                            }}
                            onMouseEnter={(e) => {
                              if (!isActive) {
                                e.target.style.background = 'rgba(212, 175, 55, 0.1)';
                              }
                            }}
                            onMouseLeave={(e) => {
                              if (!isActive) {
                                e.target.style.background = 'transparent';
                              }
                            }}
                          >
                            {page}
                          </button>
                        );
                      })}
                      
                      {pagination.current_page < pagination.total_pages && (
                        <button 
                          onClick={() => handlePageChange(pagination.current_page + 1)}
                          style={{
                            background: 'transparent',
                            color: '#d4af37',
                            border: '2px solid #d4af37',
                            padding: '0.75rem 1.5rem',
                            borderRadius: '12px',
                            fontSize: '1rem',
                            fontWeight: '600',
                            cursor: 'pointer',
                            transition: 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)'
                          }}
                          onMouseEnter={(e) => {
                            e.target.style.background = '#d4af37';
                            e.target.style.color = 'white';
                          }}
                          onMouseLeave={(e) => {
                            e.target.style.background = 'transparent';
                            e.target.style.color = '#d4af37';
                          }}
                        >
                          Sonraki →
                        </button>
                      )}
                    </div>
                  </div>
                )}
              </>
            ) : (
              <div style={{
                background: 'white',
                borderRadius: '20px',
                padding: '4rem 2rem',
                textAlign: 'center',
                boxShadow: '0 10px 30px rgba(0, 0, 0, 0.1)',
                border: '1px solid rgba(212, 175, 55, 0.1)',
                animation: 'fadeIn 0.5s ease-out'
              }}>
                <div style={{
                  fontSize: '4rem',
                  marginBottom: '1.5rem',
                  opacity: 0.6
                }}>
                  ◇
                </div>
                <h3 style={{
                  fontSize: '2rem',
                  fontWeight: '600',
                  marginBottom: '1rem',
                  color: '#374151'
                }}>
                  İlan bulunamadı
                </h3>
                <p style={{
                  color: '#6b7280',
                  marginBottom: '2rem',
                  fontSize: '1.1rem',
                  lineHeight: '1.6'
                }}>
                  Arama kriterlerinizi değiştirip tekrar deneyin.<br/>
                  Daha geniş bir arama yapmak için filtreleri temizleyebilirsiniz.
                </p>
                <button 
                  onClick={clearFilters}
                  style={{
                    background: 'linear-gradient(135deg, #d4af37 0%, #b8941f 100%)',
                    color: 'white',
                    border: 'none',
                    padding: '1rem 2rem',
                    borderRadius: '12px',
                    fontSize: '1.1rem',
                    fontWeight: '600',
                    cursor: 'pointer',
                    transition: 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)',
                    boxShadow: '0 4px 15px rgba(212, 175, 55, 0.4)'
                  }}
                  onMouseEnter={(e) => {
                    e.target.style.transform = 'translateY(-2px)';
                    e.target.style.boxShadow = '0 6px 20px rgba(212, 175, 55, 0.6)';
                  }}
                  onMouseLeave={(e) => {
                    e.target.style.transform = 'translateY(0)';
                    e.target.style.boxShadow = '0 4px 15px rgba(212, 175, 55, 0.4)';
                  }}
                >
                  🗑️ Filtreleri Temizle
                </button>
              </div>
            )}
          </>
        )}

        {/* CSS Animations */}
        <style jsx>{`
          @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
          }
          
          @keyframes fadeInUp {
            from { 
              opacity: 0; 
              transform: translateY(30px); 
            }
            to { 
              opacity: 1; 
              transform: translateY(0); 
            }
          }
          
          @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
          }
          
          @media (max-width: 768px) {
            .properties-grid {
              grid-template-columns: 1fr !important;
            }
            
            .filter-grid {
              grid-template-columns: 1fr !important;
            }
            
            .pagination {
              flex-wrap: wrap !important;
              gap: 0.25rem !important;
            }
            
            .pagination button {
              padding: 0.5rem 0.75rem !important;
              font-size: 0.9rem !important;
            }
          }
          
          @media (max-width: 480px) {
            .container {
              padding: 1rem !important;
            }
            
            .filter-card {
              padding: 1.5rem !important;
            }
            
            .page-title {
              font-size: 2rem !important;
            }
            
            .search-input {
              font-size: 1rem !important;
            }
          }
        `}</style>
      </div>
    </div>
  );
};

export default PropertiesPage; 