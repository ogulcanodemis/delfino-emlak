import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { changePassword, deleteAccount } from '../services/apiService';

const AccountSettingsPage = ({ user, onLogout }) => {
  const navigate = useNavigate();
  const [passwordData, setPasswordData] = useState({
    current_password: '',
    new_password: '',
    confirm_password: ''
  });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  const [showDeleteConfirm, setShowDeleteConfirm] = useState(false);

  const handlePasswordChange = (e) => {
    setPasswordData({
      ...passwordData,
      [e.target.name]: e.target.value
    });
  };

  const handlePasswordSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError('');
    setSuccess('');

    // Şifre doğrulama
    if (passwordData.new_password !== passwordData.confirm_password) {
      setError('Yeni şifreler eşleşmiyor');
      setLoading(false);
      return;
    }

    if (passwordData.new_password.length < 6) {
      setError('Yeni şifre en az 6 karakter olmalıdır');
      setLoading(false);
      return;
    }

    try {
      await changePassword({
        current_password: passwordData.current_password,
        new_password: passwordData.new_password
      });
      
      setSuccess('Şifreniz başarıyla değiştirildi!');
      setPasswordData({
        current_password: '',
        new_password: '',
        confirm_password: ''
      });
      
      setTimeout(() => setSuccess(''), 3000);
    } catch (error) {
      setError('Şifre değiştirilirken bir hata oluştu: ' + error.message);
    } finally {
      setLoading(false);
    }
  };

  const handleDeleteAccount = async () => {
    if (!window.confirm('Hesabınızı silmek istediğinizden emin misiniz? Bu işlem geri alınamaz!')) {
      return;
    }

    try {
      await deleteAccount();
      alert('Hesabınız başarıyla silindi.');
      onLogout();
    } catch (error) {
      alert('Hesap silinirken bir hata oluştu: ' + error.message);
    }
  };

  if (!user) {
    return (
      <div className="page-container">
        <div className="auth-required">
          <div className="auth-required-content">
            <h2>🔒 Giriş Gerekli</h2>
            <p>Hesap ayarlarınızı görüntülemek için giriş yapmalısınız.</p>
            <div className="auth-actions">
              <button 
                onClick={() => navigate('/login')} 
                className="btn btn-primary"
              >
                Giriş Yap
              </button>
              <button 
                onClick={() => navigate('/register')} 
                className="btn btn-secondary"
              >
                Kayıt Ol
              </button>
            </div>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="page-container">
      <div className="account-settings-page">
        <div className="page-header">
          <h1>⚙️ Hesap Ayarları</h1>
          <p>Güvenlik ve hesap ayarlarınızı yönetin</p>
        </div>

        <div className="settings-content">
          {/* Şifre Değiştirme */}
          <div className="settings-section">
            <div className="section-header">
              <h3>🔐 Şifre Değiştir</h3>
              <p>Hesabınızın güvenliği için düzenli olarak şifrenizi değiştirin</p>
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

            <form onSubmit={handlePasswordSubmit} className="password-form">
              <div className="form-group">
                <label htmlFor="current_password">Mevcut Şifre *</label>
                <input
                  type="password"
                  id="current_password"
                  name="current_password"
                  value={passwordData.current_password}
                  onChange={handlePasswordChange}
                  required
                  placeholder="Mevcut şifrenizi girin"
                />
              </div>

              <div className="form-group">
                <label htmlFor="new_password">Yeni Şifre *</label>
                <input
                  type="password"
                  id="new_password"
                  name="new_password"
                  value={passwordData.new_password}
                  onChange={handlePasswordChange}
                  required
                  placeholder="Yeni şifrenizi girin (en az 6 karakter)"
                  minLength="6"
                />
              </div>

              <div className="form-group">
                <label htmlFor="confirm_password">Yeni Şifre Tekrar *</label>
                <input
                  type="password"
                  id="confirm_password"
                  name="confirm_password"
                  value={passwordData.confirm_password}
                  onChange={handlePasswordChange}
                  required
                  placeholder="Yeni şifrenizi tekrar girin"
                  minLength="6"
                />
              </div>

              <div className="form-actions">
                <button 
                  type="submit" 
                  disabled={loading}
                  className="btn btn-primary"
                >
                  {loading ? 'Değiştiriliyor...' : '🔐 Şifreyi Değiştir'}
                </button>
              </div>
            </form>
          </div>

          {/* Hesap Bilgileri */}
          <div className="settings-section">
            <div className="section-header">
              <h3>👤 Hesap Bilgileri</h3>
              <p>Hesabınızla ilgili temel bilgiler</p>
            </div>

            <div className="account-info">
              <div className="info-item">
                <span className="info-label">E-posta:</span>
                <span className="info-value">{user.email}</span>
              </div>
              <div className="info-item">
                <span className="info-label">Kullanıcı Rolü:</span>
                <span className="info-value">
                  {user.role_id === 0 && 'Ziyaretçi'}
                  {user.role_id === 1 && 'Kayıtlı Kullanıcı'}
                  {user.role_id === 2 && 'Emlakçı'}
                  {user.role_id === 3 && 'Admin'}
                  {user.role_id === 4 && 'Süper Admin'}
                </span>
              </div>
              <div className="info-item">
                <span className="info-label">Üyelik Tarihi:</span>
                <span className="info-value">
                  {new Date(user.created_at).toLocaleDateString('tr-TR')}
                </span>
              </div>
              <div className="info-item">
                <span className="info-label">Son Güncelleme:</span>
                <span className="info-value">
                  {new Date(user.updated_at).toLocaleDateString('tr-TR')}
                </span>
              </div>
            </div>

            <div className="account-actions">
              <button 
                onClick={() => navigate('/profile')}
                className="btn btn-outline"
              >
                ✏️ Profili Düzenle
              </button>
            </div>
          </div>

          {/* Tehlikeli İşlemler */}
          <div className="settings-section danger-section">
            <div className="section-header">
              <h3>⚠️ Tehlikeli İşlemler</h3>
              <p>Bu işlemler geri alınamaz. Dikkatli olun!</p>
            </div>

            <div className="danger-actions">
              <div className="danger-item">
                <div className="danger-info">
                  <h4>Hesabı Sil</h4>
                  <p>Hesabınızı ve tüm verilerinizi kalıcı olarak siler. Bu işlem geri alınamaz.</p>
                </div>
                <button 
                  onClick={() => setShowDeleteConfirm(true)}
                  className="btn btn-danger"
                >
                  🗑️ Hesabı Sil
                </button>
              </div>
            </div>
          </div>
        </div>

        {/* Hesap Silme Onay Modal */}
        {showDeleteConfirm && (
          <div className="modal-overlay" onClick={() => setShowDeleteConfirm(false)}>
            <div className="modal-content" onClick={(e) => e.stopPropagation()}>
              <div className="modal-header">
                <h3>⚠️ Hesap Silme Onayı</h3>
                <button 
                  className="close-btn" 
                  onClick={() => setShowDeleteConfirm(false)}
                >
                  ×
                </button>
              </div>
              
              <div className="modal-body">
                <p><strong>Bu işlem geri alınamaz!</strong></p>
                <p>Hesabınızı sildiğinizde:</p>
                <ul>
                  <li>Tüm kişisel bilgileriniz silinecek</li>
                  <li>Yayınladığınız ilanlar kaldırılacak</li>
                  <li>Favori listeniz silinecek</li>
                  <li>Mesaj geçmişiniz kaybolacak</li>
                </ul>
                <p>Devam etmek istediğinizden emin misiniz?</p>
              </div>
              
              <div className="modal-actions">
                <button 
                  onClick={() => setShowDeleteConfirm(false)}
                  className="btn btn-secondary"
                >
                  İptal
                </button>
                <button 
                  onClick={handleDeleteAccount}
                  className="btn btn-danger"
                >
                  Evet, Hesabımı Sil
                </button>
              </div>
            </div>
          </div>
        )}
      </div>
    </div>
  );
};

export default AccountSettingsPage; 