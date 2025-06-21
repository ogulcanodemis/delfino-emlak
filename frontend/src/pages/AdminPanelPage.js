import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { 
  getPendingProperties, 
  approveProperty, 
  rejectProperty, 
  getApprovalStats,
  toggleApprovalSetting,
  canAccessAdminPanel 
} from '../services/apiService';
import AdminPropertyCard from '../components/AdminPropertyCard';
import '../components/AdminPropertyCard.css';
import './AdminPanelPage.css';

const AdminPanelPage = ({ user }) => {
  const navigate = useNavigate();
  const [activeTab, setActiveTab] = useState('pending');
  const [pendingProperties, setPendingProperties] = useState([]);
  const [stats, setStats] = useState({});
  const [loading, setLoading] = useState(false);
  const [currentPage, setCurrentPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  const [selectedProperty, setSelectedProperty] = useState(null);
  const [rejectionReason, setRejectionReason] = useState('');
  const [showRejectModal, setShowRejectModal] = useState(false);

  // Yetki kontrolü - hook'lardan önce yapılmamalı
  const hasAdminAccess = user && canAccessAdminPanel(user);

  // Bekleyen ilanları yükle
  const loadPendingProperties = async (page = 1) => {
    try {
      setLoading(true);
      const data = await getPendingProperties(page, 10);
      setPendingProperties(data.properties || []);
      setTotalPages(data.pagination?.total_pages || 1);
      setCurrentPage(page);
    } catch (error) {
      console.error('Bekleyen ilanlar yüklenirken hata:', error);
      alert('Bekleyen ilanlar yüklenirken hata oluştu');
    } finally {
      setLoading(false);
    }
  };

  // İstatistikleri yükle
  const loadStats = async () => {
    try {
      const statsData = await getApprovalStats();
      setStats(statsData);
    } catch (error) {
      console.error('İstatistikler yüklenirken hata:', error);
      // Varsayılan değerler ayarla
      setStats({
        pending_count: 0,
        approved_today: 0,
        rejected_today: 0,
        total_approved: 0
      });
    }
  };

  // İlanı onayla
  const handleApprove = async (propertyId) => {
    if (!window.confirm('Bu ilanı onaylamak istediğinizden emin misiniz?')) {
      return;
    }

    try {
      await approveProperty(propertyId);
      alert('İlan başarıyla onaylandı!');
      
      // Listeyi güncelle
      loadPendingProperties(currentPage);
      loadStats();
    } catch (error) {
      console.error('İlan onaylanırken hata:', error);
      alert('İlan onaylanırken hata oluştu: ' + error.message);
    }
  };

  // İlanı reddet
  const handleReject = async () => {
    if (!selectedProperty || !rejectionReason.trim()) {
      alert('Lütfen red sebebini belirtin');
      return;
    }

    try {
      await rejectProperty(selectedProperty.id, rejectionReason);
      alert('İlan başarıyla reddedildi!');
      
      // Modal'ı kapat ve listeyi güncelle
      setShowRejectModal(false);
      setSelectedProperty(null);
      setRejectionReason('');
      loadPendingProperties(currentPage);
      loadStats();
    } catch (error) {
      console.error('İlan reddedilirken hata:', error);
      alert('İlan reddedilirken hata oluştu: ' + error.message);
    }
  };

  // Onay ayarını değiştir
  const handleToggleApprovalSetting = async () => {
    try {
      const result = await toggleApprovalSetting();
      alert(`İlan onay sistemi ${result.approval_required ? 'aktifleştirildi' : 'devre dışı bırakıldı'}`);
      loadStats();
    } catch (error) {
      console.error('Onay ayarı değiştirilirken hata:', error);
      alert('Onay ayarı değiştirilirken hata oluştu: ' + error.message);
    }
  };

  // İlanı admin olarak görüntüle
  const handleViewProperty = (propertyId) => {
    navigate(`/admin/property/${propertyId}`);
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

  // Component mount olduğunda verileri yükle
  useEffect(() => {
    if (user && hasAdminAccess) {
      loadStats();
      if (activeTab === 'pending') {
        loadPendingProperties();
      }
    }
  }, [activeTab, user, hasAdminAccess]);

  // Yetki kontrolü - erken return
  if (!hasAdminAccess) {
    return (
      <div className="admin-panel-page">
        <div className="access-denied">
          <h2>◆ Erişim Reddedildi</h2>
          <p>Bu sayfaya erişim için admin yetkisine sahip olmanız gerekiyor.</p>
        </div>
      </div>
    );
  }

  return (
    <div className="admin-panel-page">
      <div className="admin-header">
        <div className="admin-header-content">
          <button 
            onClick={() => navigate('/profile')}
            className="back-btn"
          >
            ← Profile'e Dön
          </button>
          <div className="admin-title">
            <h1>◆ Admin Paneli</h1>
            <p>İlan onay sistemi yönetimi</p>
          </div>
        </div>
      </div>

      {/* İstatistik Kartları */}
      <div className="stats-grid">
        <div className="stat-card pending">
          <div className="stat-icon">◇</div>
          <div className="stat-content">
            <h3>{stats.pending_count || 0}</h3>
            <p>Bekleyen İlan</p>
          </div>
        </div>
        
        <div className="stat-card approved">
          <div className="stat-icon">◆</div>
          <div className="stat-content">
            <h3>{stats.approved_today || 0}</h3>
            <p>Bugün Onaylanan</p>
          </div>
        </div>
        
        <div className="stat-card rejected">
          <div className="stat-icon">◇</div>
          <div className="stat-content">
            <h3>{stats.rejected_today || 0}</h3>
            <p>Bugün Reddedilen</p>
          </div>
        </div>
        
        <div className="stat-card total">
          <div className="stat-icon">◆</div>
          <div className="stat-content">
            <h3>{stats.total_approved || 0}</h3>
            <p>Toplam Onaylı</p>
          </div>
        </div>
      </div>

      {/* Tab Navigation */}
      <div className="tab-navigation">
        <button 
          className={`tab-btn ${activeTab === 'pending' ? 'active' : ''}`}
          onClick={() => setActiveTab('pending')}
        >
          ◇ Bekleyen İlanlar ({stats.pending_count || 0})
        </button>
        <button 
          className={`tab-btn ${activeTab === 'settings' ? 'active' : ''}`}
          onClick={() => setActiveTab('settings')}
        >
          ◆ Ayarlar
        </button>
      </div>

      {/* Tab Content */}
      <div className="tab-content">
        {activeTab === 'pending' && (
          <div className="pending-properties">
            <div className="section-header">
              <h2>Bekleyen İlanlar</h2>
              <button 
                className="refresh-btn"
                onClick={() => loadPendingProperties(currentPage)}
                disabled={loading}
              >
                ◆ Yenile
              </button>
            </div>

            {loading ? (
              <div className="loading-state">
                <div className="spinner"></div>
                <p>İlanlar yükleniyor...</p>
              </div>
            ) : pendingProperties.length === 0 ? (
              <div className="empty-state">
                <span>◇</span>
                <h3>Bekleyen İlan Yok</h3>
                <p>Şu anda onay bekleyen ilan bulunmuyor.</p>
              </div>
            ) : (
              <>
                <div className="properties-list">
                  {pendingProperties.map(property => (
                    <AdminPropertyCard
                      key={property.id}
                      property={property}
                      onApprove={() => handleApprove(property.id)}
                      onReject={() => {
                        setSelectedProperty(property);
                        setShowRejectModal(true);
                      }}
                      onView={() => handleViewProperty(property.id)}
                    />
                  ))}
                </div>

                {/* Pagination */}
                {totalPages > 1 && (
                  <div className="pagination">
                    <button 
                      onClick={() => loadPendingProperties(currentPage - 1)}
                      disabled={currentPage === 1}
                    >
                      ← Önceki
                    </button>
                    <span>Sayfa {currentPage} / {totalPages}</span>
                    <button 
                      onClick={() => loadPendingProperties(currentPage + 1)}
                      disabled={currentPage === totalPages}
                    >
                      Sonraki →
                    </button>
                  </div>
                )}
              </>
            )}
          </div>
        )}

        {activeTab === 'settings' && (
          <div className="settings-panel">
            <div className="section-header">
              <h2>İlan Onay Ayarları</h2>
            </div>

            <div className="settings-card">
              <div className="setting-item">
                <div className="setting-info">
                  <h3>İlan Onay Sistemi</h3>
                  <p>Yeni ilanların admin onayı gerektirip gerektirmediğini belirler.</p>
                </div>
                <button 
                  className="toggle-btn"
                  onClick={handleToggleApprovalSetting}
                >
                  Durumu Değiştir
                </button>
              </div>
            </div>

            <div className="info-card">
              <h3>◆ Bilgi</h3>
              <ul>
                <li>Onay sistemi aktifken, yeni ilanlar "beklemede" durumunda oluşturulur</li>
                <li>Admin onayından sonra ilanlar yayınlanır</li>
                <li>Reddedilen ilanlar için kullanıcıya bildirim gönderilir</li>
                <li>Onaylanan ilanlar için de kullanıcıya bildirim gönderilir</li>
              </ul>
            </div>
          </div>
        )}
      </div>

      {/* Reddetme Modal */}
      {showRejectModal && (
        <div className="modal-overlay">
          <div className="modal">
            <div className="modal-header">
              <h3>İlanı Reddet</h3>
              <button 
                className="close-btn"
                onClick={() => {
                  setShowRejectModal(false);
                  setSelectedProperty(null);
                  setRejectionReason('');
                }}
              >
                ✕
              </button>
            </div>
            
            <div className="modal-content">
              <p><strong>İlan:</strong> {selectedProperty?.title}</p>
              <div className="form-group">
                <label>Red Sebebi:</label>
                <textarea
                  value={rejectionReason}
                  onChange={(e) => setRejectionReason(e.target.value)}
                  placeholder="İlanın neden reddedildiğini açıklayın..."
                  rows={4}
                  required
                />
              </div>
            </div>
            
            <div className="modal-actions">
              <button 
                className="cancel-btn"
                onClick={() => {
                  setShowRejectModal(false);
                  setSelectedProperty(null);
                  setRejectionReason('');
                }}
              >
                İptal
              </button>
              <button 
                className="confirm-btn"
                onClick={handleReject}
                disabled={!rejectionReason.trim()}
              >
                Reddet
              </button>
            </div>
          </div>
        </div>
      )}


    </div>
  );
};

export default AdminPanelPage; 