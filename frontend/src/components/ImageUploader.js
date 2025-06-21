import React, { useState, useRef } from 'react';
import './ImageUploader.css';

const ImageUploader = ({ images, onImagesChange, maxImages = 10, existingImages = [], onDeleteExisting, onSetPrimaryExisting }) => {
  const [dragActive, setDragActive] = useState(false);
  const fileInputRef = useRef(null);

  // Dosya seçme
  const handleFileSelect = (files) => {
    const fileArray = Array.from(files);
    const validFiles = fileArray.filter(file => {
      // Sadece resim dosyalarını kabul et
      return file.type.startsWith('image/');
    });

    if (validFiles.length === 0) {
      alert('Lütfen sadece resim dosyaları seçin.');
      return;
    }

    const totalImages = images.length + existingImages.length + validFiles.length;
    if (totalImages > maxImages) {
      alert(`En fazla ${maxImages} fotoğraf yükleyebilirsiniz.`);
      return;
    }

    // Dosyaları preview URL'leri ile birlikte ekle
    const newImages = validFiles.map(file => ({
      file,
      preview: URL.createObjectURL(file),
      id: Date.now() + Math.random(),
      isPrimary: images.length === 0 && existingImages.length === 0 // İlk fotoğraf ana fotoğraf olsun
    }));

    onImagesChange([...images, ...newImages]);
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
      handleFileSelect(e.dataTransfer.files);
    }
  };

  // Dosya input'u tetikle
  const handleButtonClick = () => {
    fileInputRef.current?.click();
  };

  // Fotoğraf sil
  const removeImage = (imageId) => {
    const updatedImages = images.filter(img => img.id !== imageId);
    
    // Eğer silinen fotoğraf ana fotoğrafsa, ilk fotoğrafı ana yap
    if (updatedImages.length > 0) {
      const removedImage = images.find(img => img.id === imageId);
      if (removedImage?.isPrimary) {
        updatedImages[0].isPrimary = true;
      }
    }
    
    onImagesChange(updatedImages);
  };

  // Ana fotoğraf belirle
  const setPrimaryImage = (imageId) => {
    const updatedImages = images.map(img => ({
      ...img,
      isPrimary: img.id === imageId
    }));
    onImagesChange(updatedImages);
  };

  return (
    <div className="image-uploader">
      <div className="upload-header">
        <h4>📸 Fotoğraflar</h4>
        <span className="image-count">{images.length + existingImages.length}/{maxImages}</span>
      </div>

      {/* Upload Area */}
      <div
        className={`upload-area ${dragActive ? 'drag-active' : ''}`}
        onDragEnter={handleDrag}
        onDragLeave={handleDrag}
        onDragOver={handleDrag}
        onDrop={handleDrop}
        onClick={handleButtonClick}
      >
        <div className="upload-content">
          <div className="upload-icon">📁</div>
          <p>Fotoğrafları buraya sürükleyin veya tıklayın</p>
          <small>PNG, JPG, JPEG formatları desteklenir (Maks. {maxImages} fotoğraf)</small>
        </div>
        
        <input
          ref={fileInputRef}
          type="file"
          multiple
          accept="image/*"
          onChange={(e) => handleFileSelect(e.target.files)}
          style={{ display: 'none' }}
        />
      </div>

      {/* Image Preview Grid */}
      {(images.length > 0 || existingImages.length > 0) && (
        <div className="image-grid">
          {/* Existing Images */}
          {existingImages.map((image) => (
            <div key={`existing-${image.id}`} className={`image-item ${image.is_primary ? 'primary' : ''}`}>
              <div className="image-preview">
                <img src={`https://bkyatirim.com/${image.image_path}`} alt={image.alt_text || 'Property image'} />
                
                {/* Primary Badge */}
                {image.is_primary && (
                  <div className="primary-badge">⭐ Ana Fotoğraf</div>
                )}
                
                {/* Image Actions */}
                <div className="image-actions">
                  {!image.is_primary && onSetPrimaryExisting && (
                    <button
                      type="button"
                      className="btn-set-primary"
                      onClick={() => onSetPrimaryExisting(image.id)}
                      title="Ana fotoğraf yap"
                    >
                      ⭐
                    </button>
                  )}
                  
                  {onDeleteExisting && (
                    <button
                      type="button"
                      className="btn-remove"
                      onClick={() => onDeleteExisting(image.id)}
                      title="Fotoğrafı sil"
                    >
                      🗑️
                    </button>
                  )}
                </div>
              </div>
              
              <div className="image-info">
                <small>{image.image_name}</small>
                <small>{(image.image_size / 1024 / 1024).toFixed(2)} MB</small>
              </div>
            </div>
          ))}
          
          {/* New Images */}
          {images.map((image) => (
            <div key={image.id} className={`image-item ${image.isPrimary ? 'primary' : ''}`}>
              <div className="image-preview">
                <img src={image.preview} alt="Preview" />
                
                {/* Primary Badge */}
                {image.isPrimary && (
                  <div className="primary-badge">⭐ Ana Fotoğraf</div>
                )}
                
                {/* Image Actions */}
                <div className="image-actions">
                  {!image.isPrimary && (
                    <button
                      type="button"
                      className="btn-set-primary"
                      onClick={() => setPrimaryImage(image.id)}
                      title="Ana fotoğraf yap"
                    >
                      ⭐
                    </button>
                  )}
                  
                  <button
                    type="button"
                    className="btn-remove"
                    onClick={() => removeImage(image.id)}
                    title="Fotoğrafı sil"
                  >
                    🗑️
                  </button>
                </div>
              </div>
              
              <div className="image-info">
                <small>{image.file.name}</small>
                <small>{(image.file.size / 1024 / 1024).toFixed(2)} MB</small>
              </div>
            </div>
          ))}
        </div>
      )}

      {/* Upload Tips */}
      <div className="upload-tips">
        <h5>💡 Fotoğraf Yükleme İpuçları:</h5>
        <ul>
          <li>İlk yüklediğiniz fotoğraf otomatik olarak ana fotoğraf olur</li>
          <li>Ana fotoğrafı değiştirmek için ⭐ butonuna tıklayın</li>
          <li>Kaliteli ve aydınlık fotoğraflar daha çok ilgi çeker</li>
          <li>Farklı açılardan çekilmiş fotoğraflar ekleyin</li>
        </ul>
      </div>
    </div>
  );
};

export default ImageUploader; 