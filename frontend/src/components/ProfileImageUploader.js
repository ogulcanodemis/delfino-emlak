import React, { useState, useRef } from 'react';
import { uploadProfileImage, deleteProfileImage } from '../services/apiService';
import './ProfileImageUploader.css';

const ProfileImageUploader = ({ user, onImageUpdate, onUserUpdate }) => {
  const [uploading, setUploading] = useState(false);
  const [dragActive, setDragActive] = useState(false);
  const fileInputRef = useRef(null);

  // Profil resmi URL'ini oluştur
  const getProfileImageUrl = () => {
    if (user?.profile_image) {
      // Eğer zaten tam URL ise direkt kullan
      if (user.profile_image.startsWith('http')) {
        return user.profile_image;
      }
      // Relative path ise base URL ekle
      return `https://bkyatirim.com/${user.profile_image}`;
    }
    return null;
  };

  // Dosya seçme işlemi
  const handleFileSelect = async (file) => {
    if (!file) return;

    // Dosya tipini kontrol et
    if (!file.type.startsWith('image/')) {
      alert('Lütfen sadece resim dosyası seçin.');
      return;
    }

    // Dosya boyutunu kontrol et (2MB limit)
    if (file.size > 2 * 1024 * 1024) {
      alert('Dosya boyutu 2MB\'dan küçük olmalıdır.');
      return;
    }

    setUploading(true);
    
    try {
      const result = await uploadProfileImage(file);
      
      if (result.status === 'success') {
        // Başarılı yükleme sonrası kullanıcı bilgilerini güncelle
        if (onUserUpdate && result.data.user) {
          onUserUpdate(result.data.user);
        }
        
        if (onImageUpdate) {
          onImageUpdate(result.data.profile_image_url);
        }
        
        alert('Profil resmi başarıyla yüklendi!');
      }
    } catch (error) {
      console.error('Profil resmi yükleme hatası:', error);
      alert('Profil resmi yüklenirken hata oluştu: ' + error.message);
    } finally {
      setUploading(false);
    }
  };

  // Profil resmini silme
  const handleDeleteImage = async () => {
    if (!user?.profile_image) {
      return;
    }

    if (!window.confirm('Profil resminizi silmek istediğinizden emin misiniz?')) {
      return;
    }

    setUploading(true);
    
    try {
      const result = await deleteProfileImage();
      
      // Başarılı silme sonrası kullanıcı bilgilerini güncelle
      if (onUserUpdate && result.user) {
        onUserUpdate(result.user);
      }
      
      if (onImageUpdate) {
        onImageUpdate(null);
      }
      
      alert('Profil resmi başarıyla silindi!');
    } catch (error) {
      console.error('Profil resmi silme hatası:', error);
      alert('Profil resmi silinirken hata oluştu: ' + error.message);
    } finally {
      setUploading(false);
    }
  };

  // Drag & Drop handlers
  const handleDrag = (e) => {
    e.preventDefault();
    e.stopPropagation();
    if (e.type === 'dragenter' || e.type === 'dragover') {
      setDragActive(true);
    } else if (e.type === 'dragleave') {
      setDragActive(false);
    }
  };

  const handleDrop = (e) => {
    e.preventDefault();
    e.stopPropagation();
    setDragActive(false);
    
    if (e.dataTransfer.files && e.dataTransfer.files[0]) {
      handleFileSelect(e.dataTransfer.files[0]);
    }
  };

  // Dosya input'u tetikle
  const handleButtonClick = () => {
    fileInputRef.current?.click();
  };

  const profileImageUrl = getProfileImageUrl();

  return (
    <div className="profile-image-uploader">
      <div className="profile-image-container">
        <div className="profile-image-wrapper">
          {profileImageUrl ? (
            <img 
              src={profileImageUrl} 
              alt="Profil Resmi" 
              className="profile-image"
              onError={(e) => {
                // Resim yüklenemezse placeholder göster
                e.target.style.display = 'none';
                e.target.nextSibling.style.display = 'flex';
              }}
            />
          ) : null}
          
          {/* Placeholder/Avatar */}
          <div 
            className={`profile-avatar-placeholder ${profileImageUrl ? 'hidden' : ''}`}
            style={{ display: profileImageUrl ? 'none' : 'flex' }}
          >
            {user?.name ? user.name.charAt(0).toUpperCase() : '👤'}
          </div>

          {/* Upload Overlay */}
          <div 
            className={`upload-overlay ${dragActive ? 'drag-active' : ''} ${uploading ? 'uploading' : ''}`}
            onDragEnter={handleDrag}
            onDragLeave={handleDrag}
            onDragOver={handleDrag}
            onDrop={handleDrop}
            onClick={handleButtonClick}
          >
            {uploading ? (
              <div className="upload-progress">
                <div className="spinner"></div>
                <span>Yükleniyor...</span>
              </div>
            ) : (
              <div className="upload-content">
                <div className="upload-icon">📷</div>
                <span>Resim Yükle</span>
              </div>
            )}
          </div>

          {/* Hidden file input */}
          <input
            ref={fileInputRef}
            type="file"
            accept="image/*"
            onChange={(e) => handleFileSelect(e.target.files[0])}
            style={{ display: 'none' }}
          />
        </div>

        {/* Action Buttons */}
        <div className="profile-image-actions">
          <button 
            type="button"
            onClick={handleButtonClick}
            disabled={uploading}
            className="btn btn-outline btn-sm"
          >
            📷 Resim Seç
          </button>
          
          {profileImageUrl && (
            <button 
              type="button"
              onClick={handleDeleteImage}
              disabled={uploading}
              className="btn btn-danger btn-sm"
            >
              🗑️ Sil
            </button>
          )}
        </div>
      </div>

      {/* Upload Tips */}
      <div className="upload-tips">
        <small>
          • Maksimum dosya boyutu: 2MB<br/>
          • Desteklenen formatlar: JPG, PNG, GIF<br/>
          • Kare (1:1) oranında resimler daha iyi görünür
        </small>
      </div>
    </div>
  );
};

export default ProfileImageUploader;