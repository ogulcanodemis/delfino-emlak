import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { updateProfile, getUserProfile, canAccessAdminPanel } from '../services/apiService';
import ProfileImageUploader from '../components/ProfileImageUploader';
import './ProfilePage.css';

const ProfilePage = ({ user, onUserUpdate }) => {
  const navigate = useNavigate();
  const [profile, setProfile] = useState({
    name: '',
    email: '',
    phone: '',
    bio: '',
    company: '',
    website: '',
    address: ''
  });
  const [isEditing, setIsEditing] = useState(false);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');

  useEffect(() => {
    if (user) {
      loadProfile();
    }
  }, [user]);

  const loadProfile = async () => {
    try {
      const data = await getUserProfile();
      setProfile({
        name: data.name || '',
        email: data.email || '',
        phone: data.phone || '',
        bio: data.bio || '',
        company: data.company || '',
        website: data.website || '',
        address: data.address || ''
      });
    } catch (error) {
      console.error('Profile load error:', error);
      // Kullanıcı verilerini fallback olarak kullan
      setProfile({
        name: user.name || '',
        email: user.email || '',
        phone: user.phone || '',
        bio: '',
        company: '',
        website: '',
        address: ''
      });
    }
  };

  const handleChange = (e) => {
    setProfile({
      ...profile,
      [e.target.name]: e.target.value
    });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError('');
    setSuccess('');

    try {
      const updatedUser = await updateProfile(profile);
      setSuccess('Profil başarıyla güncellendi!');
      setIsEditing(false);
      
      // Ana uygulamadaki kullanıcı bilgilerini güncelle
      if (onUserUpdate) {
        onUserUpdate(updatedUser);
      }
      
      setTimeout(() => setSuccess(''), 3000);
    } catch (error) {
      setError('Profil güncellenirken bir hata oluştu: ' + error.message);
    } finally {
      setLoading(false);
    }
  };

  const handleCancel = () => {
    setIsEditing(false);
    setError('');
    setSuccess('');
    loadProfile(); // Değişiklikleri geri al
  };

  if (!user) {
    return (
      <div className="page-container">
        <div className="auth-required">
          <div className="auth-required-content">
            <h2>◆ Giriş Gerekli</h2>
            <p>Profilinizi görüntülemek için giriş yapmalısınız.</p>
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

  const getRoleName = (roleId) => {
    const roles = {
      0: 'Ziyaretçi',
      1: 'Kayıtlı Kullanıcı',
      2: 'Emlakçı',
      3: 'Admin',
      4: 'Süper Admin'
    };
    return roles[roleId] || 'Bilinmeyen';
  };

  return (
    <div className="page-container">
      <div className="profile-page">
        <div className="page-header">
          <h1>◆ Profilim</h1>
          <p>Hesap bilgilerinizi görüntüleyin ve düzenleyin</p>
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

        <div className="profile-content">
          <div className="profile-sidebar">
            <div className="profile-avatar">
              <ProfileImageUploader 
                user={user} 
                onUserUpdate={onUserUpdate}
                onImageUpdate={(imageUrl) => {
                  // Profil resmi güncellendiğinde sidebar'ı yenile
                  if (onUserUpdate && user) {
                    const updatedUser = { ...user };
                    if (imageUrl) {
                      updatedUser.profile_image = imageUrl;
                    } else {
                      updatedUser.profile_image = null;
                    }
                    onUserUpdate(updatedUser);
                  }
                }}
              />
              <h3>{profile.name || 'İsimsiz Kullanıcı'}</h3>
              <p className="user-role">{getRoleName(user.role_id)}</p>
              <p className="member-since">
                Üye olma: {new Date(user.created_at).toLocaleDateString('tr-TR')}
              </p>
            </div>

            <div className="profile-stats">
              <h4>◆ İstatistikler</h4>
              <div className="stat-item">
                <span>İlanlar:</span>
                <span>-</span>
              </div>
              <div className="stat-item">
                <span>Favoriler:</span>
                <span>-</span>
              </div>
              <div className="stat-item">
                <span>Mesajlar:</span>
                <span>-</span>
              </div>
            </div>

            <div className="profile-actions">
              <button 
                onClick={() => navigate('/my-properties')}
                className="btn btn-outline"
              >
                ◇ İlanlarım
              </button>
              
              {/* Admin Panel Butonu - Sadece süper adminler için */}
              {user && canAccessAdminPanel(user) && (
                <button 
                  onClick={() => navigate('/admin')}
                  className="btn btn-admin"
                >
                  ◆ Admin Paneli
                </button>
              )}
              
              <button 
                onClick={() => navigate('/account-settings')}
                className="btn btn-outline"
              >
                ◇ Hesap Ayarları
              </button>
            </div>
          </div>

          <div className="profile-main">
            <div className="profile-form-section">
              <div className="section-header">
                <h3>◆ Profil Bilgileri</h3>
                {!isEditing ? (
                  <button 
                    onClick={() => setIsEditing(true)}
                    className="btn btn-primary"
                  >
                    ◆ Düzenle
                  </button>
                ) : (
                  <div className="edit-actions">
                    <button 
                      onClick={handleCancel}
                      className="btn btn-secondary"
                    >
                      İptal
                    </button>
                  </div>
                )}
              </div>

              <form onSubmit={handleSubmit} className="profile-form">
                <div className="form-row">
                  <div className="form-group">
                    <label htmlFor="name">Ad Soyad *</label>
                    <input
                      type="text"
                      id="name"
                      name="name"
                      value={profile.name}
                      onChange={handleChange}
                      disabled={!isEditing}
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
                      value={profile.email}
                      onChange={handleChange}
                      disabled={!isEditing}
                      required
                      placeholder="ornek@email.com"
                    />
                  </div>
                </div>

                <div className="form-row">
                  <div className="form-group">
                    <label htmlFor="phone">Telefon</label>
                    <input
                      type="tel"
                      id="phone"
                      name="phone"
                      value={profile.phone}
                      onChange={handleChange}
                      disabled={!isEditing}
                      placeholder="0555 123 45 67"
                    />
                  </div>

                  <div className="form-group">
                    <label htmlFor="company">Şirket</label>
                    <input
                      type="text"
                      id="company"
                      name="company"
                      value={profile.company}
                      onChange={handleChange}
                      disabled={!isEditing}
                      placeholder="Şirket adınız"
                    />
                  </div>
                </div>

                <div className="form-group">
                  <label htmlFor="website">Website</label>
                  <input
                    type="url"
                    id="website"
                    name="website"
                    value={profile.website}
                    onChange={handleChange}
                    disabled={!isEditing}
                    placeholder="https://www.example.com"
                  />
                </div>

                <div className="form-group">
                  <label htmlFor="address">Adres</label>
                  <textarea
                    id="address"
                    name="address"
                    value={profile.address}
                    onChange={handleChange}
                    disabled={!isEditing}
                    rows="2"
                    placeholder="Adresiniz"
                  />
                </div>

                <div className="form-group">
                  <label htmlFor="bio">Hakkımda</label>
                  <textarea
                    id="bio"
                    name="bio"
                    value={profile.bio}
                    onChange={handleChange}
                    disabled={!isEditing}
                    rows="4"
                    placeholder="Kendiniz hakkında kısa bilgi..."
                  />
                </div>

                {isEditing && (
                  <div className="form-actions">
                    <button 
                      type="submit" 
                      disabled={loading}
                      className="btn btn-primary"
                    >
                      {loading ? 'Kaydediliyor...' : '◆ Kaydet'}
                    </button>
                  </div>
                )}
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default ProfilePage; 