import React, { useEffect, useRef, useState } from 'react';
import './MapSelector.css';

const MapSelector = ({ 
  latitude, 
  longitude, 
  onLocationChange, 
  cityName = '', 
  districtName = '',
  address = '',
  readonly = false
}) => {
  const mapRef = useRef(null);
  const mapInstanceRef = useRef(null);
  const markerRef = useRef(null);
  const [isMapLoaded, setIsMapLoaded] = useState(false);
  const [mapError, setMapError] = useState('');

  // Leaflet'i dinamik olarak yükle
  useEffect(() => {
    const loadLeaflet = async () => {
      try {
        // Leaflet CSS'ini yükle
        if (!document.querySelector('link[href*="leaflet"]')) {
          const leafletCSS = document.createElement('link');
          leafletCSS.rel = 'stylesheet';
          leafletCSS.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
          leafletCSS.integrity = 'sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=';
          leafletCSS.crossOrigin = '';
          document.head.appendChild(leafletCSS);
        }

        // Leaflet JS'ini yükle
        if (!window.L) {
          const leafletJS = document.createElement('script');
          leafletJS.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
          leafletJS.integrity = 'sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=';
          leafletJS.crossOrigin = '';
          
          leafletJS.onload = () => {
            setIsMapLoaded(true);
          };
          
          leafletJS.onerror = () => {
            setMapError('Harita yüklenemedi. Lütfen internet bağlantınızı kontrol edin.');
          };
          
          document.head.appendChild(leafletJS);
        } else {
          setIsMapLoaded(true);
        }
      } catch (error) {
        setMapError('Harita yüklenirken hata oluştu.');
        console.error('Leaflet yükleme hatası:', error);
      }
    };

    loadLeaflet();
  }, []);

  // Harita başlatma
  useEffect(() => {
    if (!isMapLoaded || !mapRef.current || mapInstanceRef.current) return;

    try {
      // Türkiye'nin merkez koordinatları (varsayılan)
      const defaultLat = latitude || 39.9334;
      const defaultLng = longitude || 32.8597;
      const defaultZoom = (latitude && longitude) ? 15 : 6;

      // Harita oluştur
      const map = window.L.map(mapRef.current, {
        center: [defaultLat, defaultLng],
        zoom: defaultZoom,
        zoomControl: true,
        scrollWheelZoom: true,
        doubleClickZoom: true,
        dragging: true
      });

      // OpenStreetMap tile layer ekle
      window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19,
        minZoom: 3
      }).addTo(map);

      mapInstanceRef.current = map;

      // Eğer koordinatlar varsa marker ekle
      if (latitude && longitude) {
        addMarker(latitude, longitude);
      }

      // Harita tıklama olayı (sadece readonly değilse)
      if (!readonly) {
        map.on('click', (e) => {
          const { lat, lng } = e.latlng;
          addMarker(lat, lng);
          
          if (onLocationChange) {
            onLocationChange({
              latitude: lat,
              longitude: lng
            });
          }
        });
      }

    } catch (error) {
      setMapError('Harita oluşturulurken hata oluştu.');
      console.error('Harita oluşturma hatası:', error);
    }
  }, [isMapLoaded]);

  // Koordinat değişikliklerini dinle
  useEffect(() => {
    if (!mapInstanceRef.current) return;

    if (latitude && longitude) {
      // Haritayı yeni konuma odakla
      mapInstanceRef.current.setView([latitude, longitude], 15);
      addMarker(latitude, longitude);
    }
  }, [latitude, longitude]);

  // Marker ekleme fonksiyonu
  const addMarker = (lat, lng) => {
    if (!mapInstanceRef.current) return;

    // Mevcut marker'ı kaldır
    if (markerRef.current) {
      mapInstanceRef.current.removeLayer(markerRef.current);
    }

    // Yeni marker ekle
    const marker = window.L.marker([lat, lng], {
      draggable: !readonly,
      title: readonly ? 'İlan konumu' : 'İlan konumu (sürükleyebilirsiniz)'
    }).addTo(mapInstanceRef.current);

    // Marker sürükleme olayı (sadece readonly değilse)
    if (!readonly) {
      marker.on('dragend', (e) => {
        const position = e.target.getLatLng();
        if (onLocationChange) {
          onLocationChange({
            latitude: position.lat,
            longitude: position.lng
          });
        }
      });
    }

    // Popup ekle
    const popupContent = `
      <div style="text-align: center;">
        <strong>İlan Konumu</strong><br/>
        ${cityName ? `<small>${cityName}${districtName ? ` / ${districtName}` : ''}</small><br/>` : ''}
        <small>Lat: ${lat.toFixed(6)}<br/>Lng: ${lng.toFixed(6)}</small>
      </div>
    `;
    
    marker.bindPopup(popupContent).openPopup();
    markerRef.current = marker;
  };

  // Şehir/ilçe değiştiğinde haritayı o bölgeye odakla
  const focusOnLocation = async (cityName, districtName) => {
    if (!mapInstanceRef.current || !cityName) return;

    try {
      // Basit geocoding - Türkiye şehirleri için yaklaşık koordinatlar
      const turkishCities = {
        'İstanbul': { lat: 41.0082, lng: 28.9784, zoom: 10 },
        'Ankara': { lat: 39.9334, lng: 32.8597, zoom: 11 },
        'İzmir': { lat: 38.4192, lng: 27.1287, zoom: 11 },
        'Bursa': { lat: 40.1826, lng: 29.0665, zoom: 11 },
        'Antalya': { lat: 36.8969, lng: 30.7133, zoom: 11 },
        'Adana': { lat: 37.0000, lng: 35.3213, zoom: 11 },
        'Konya': { lat: 37.8667, lng: 32.4833, zoom: 11 },
        'Gaziantep': { lat: 37.0662, lng: 37.3833, zoom: 11 },
        'Kayseri': { lat: 38.7312, lng: 35.4787, zoom: 11 },
        'Eskişehir': { lat: 39.7767, lng: 30.5206, zoom: 11 }
      };

      const cityData = turkishCities[cityName];
      if (cityData) {
        mapInstanceRef.current.setView([cityData.lat, cityData.lng], cityData.zoom);
      }
    } catch (error) {
      console.error('Şehir odaklama hatası:', error);
    }
  };

  // Şehir veya ilçe değiştiğinde haritayı odakla
  useEffect(() => {
    if (cityName) {
      focusOnLocation(cityName, districtName);
    }
  }, [cityName, districtName]);

  // Cleanup
  useEffect(() => {
    return () => {
      if (mapInstanceRef.current) {
        mapInstanceRef.current.remove();
        mapInstanceRef.current = null;
      }
    };
  }, []);

  if (mapError) {
    return (
      <div className="map-selector">
        <div className="map-error">
          <div className="error-icon">🗺️</div>
          <p>{mapError}</p>
          <button 
            onClick={() => window.location.reload()} 
            className="btn btn-outline btn-sm"
          >
            Tekrar Dene
          </button>
        </div>
      </div>
    );
  }

  if (!isMapLoaded) {
    return (
      <div className="map-selector">
        <div className="map-loading">
          <div className="spinner"></div>
          <p>Harita yükleniyor...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="map-selector">
      <div className="map-header">
        <h4>🗺️ İlan Konumu</h4>
        <p>Harita üzerine tıklayarak ilanın konumunu belirleyin</p>
      </div>
      
      <div className="map-container">
        <div ref={mapRef} className="map-element"></div>
      </div>
      
      <div className="map-info">
        {latitude && longitude ? (
          <div className="location-info">
            <div className="location-item">
              <strong>Enlem:</strong> {latitude.toFixed(6)}
            </div>
            <div className="location-item">
              <strong>Boylam:</strong> {longitude.toFixed(6)}
            </div>
            {(cityName || districtName) && (
              <div className="location-item">
                <strong>Bölge:</strong> {cityName}{districtName ? ` / ${districtName}` : ''}
              </div>
            )}
          </div>
        ) : (
          <div className="no-location">
            <small>📍 Harita üzerine tıklayarak konum seçin</small>
          </div>
        )}
      </div>
      
      {!readonly && (
        <div className="map-tips">
          <small>
            💡 <strong>İpuçları:</strong><br/>
            • Harita üzerine tıklayarak konum belirleyin<br/>
            • Marker'ı sürükleyerek konumu değiştirebilirsiniz<br/>
            • Şehir seçimi yaptığınızda harita otomatik odaklanır
          </small>
        </div>
      )}
    </div>
  );
};

export default MapSelector;