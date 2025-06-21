import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { register } from '../services/authService';

const RegisterPage = ({ onLogin }) => {
  const navigate = useNavigate();
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    phone: '',
    role_id: 1 // Varsayılan: Kayıtlı Kullanıcı
  });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [showConfirmPassword, setShowConfirmPassword] = useState(false);

  const handleChange = (e) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value
    });
    // Hata mesajını temizle
    if (error) setError('');
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    
    // Basit validasyon
    if (!formData.name || !formData.email || !formData.password) {
      setError('Lütfen zorunlu alanları doldurun');
      return;
    }

    if (!formData.email.includes('@')) {
      setError('Geçerli bir e-posta adresi girin');
      return;
    }

    if (formData.password.length < 6) {
      setError('Şifre en az 6 karakter olmalıdır');
      return;
    }

    if (formData.password !== formData.password_confirmation) {
      setError('Şifreler eşleşmiyor');
      return;
    }

    try {
      setLoading(true);
      setError('');
      
      // Gerçek API'ye kayıt ol
      const user = await register(formData);
      
      // Başarılı kayıt
      onLogin(user);
      navigate('/');
      
    } catch (error) {
      console.error('Kayıt hatası:', error);
      setError(error.message || 'Kayıt olurken bir hata oluştu');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div style={{
      minHeight: '100vh',
      background: 'linear-gradient(135deg, #d4af37 0%, #b8941f 100%)',
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      padding: '2rem 1rem'
    }}>
      <div style={{
        width: '100%',
        maxWidth: '600px',
        background: 'rgba(255, 255, 255, 0.95)',
        backdropFilter: 'blur(10px)',
        borderRadius: '20px',
        boxShadow: '0 20px 40px rgba(0, 0, 0, 0.1)',
        padding: '3rem',
        border: '1px solid rgba(255, 255, 255, 0.2)'
      }}>
        {/* Header */}
        <div style={{ textAlign: 'center', marginBottom: '2.5rem' }}>
          <div style={{
            fontSize: '4rem',
            marginBottom: '1rem',
            background: 'linear-gradient(135deg, #d4af37 0%, #b8941f 100%)',
            WebkitBackgroundClip: 'text',
            WebkitTextFillColor: 'transparent',
            backgroundClip: 'text'
          }}>
            ◆
          </div>
          <h1 style={{
            fontSize: '2.5rem',
            fontWeight: '700',
            background: 'linear-gradient(135deg, #d4af37 0%, #b8941f 100%)',
            WebkitBackgroundClip: 'text',
            WebkitTextFillColor: 'transparent',
            backgroundClip: 'text',
            marginBottom: '0.5rem'
          }}>
            Kayıt Ol
          </h1>
          <p style={{
            color: '#6b7280',
            fontSize: '1.1rem',
            margin: '0'
          }}>
            BK Yatırım ailesine katılın
          </p>
        </div>

        {/* Hata mesajı */}
        {error && (
          <div style={{
            background: 'linear-gradient(135deg, #fee2e2 0%, #fecaca 100%)',
            color: '#dc2626',
            padding: '1rem 1.5rem',
            borderRadius: '12px',
            marginBottom: '2rem',
            textAlign: 'center',
            border: '1px solid #fca5a5',
            fontWeight: '500'
          }}>
            ⚠️ {error}
          </div>
        )}

        {/* Kayıt Formu */}
        <form onSubmit={handleSubmit} style={{ display: 'flex', flexDirection: 'column', gap: '1.5rem' }}>
          {/* Ad Soyad ve E-posta */}
          <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '1rem' }}>
            <div>
              <label style={{
                display: 'block',
                marginBottom: '0.5rem',
                fontWeight: '600',
                color: '#374151',
                fontSize: '0.95rem'
              }}>
                ◆ Ad Soyad *
              </label>
              <input
                type="text"
                name="name"
                value={formData.name}
                onChange={handleChange}
                placeholder="Adınız ve soyadınız"
                disabled={loading}
                required
                style={{
                  width: '100%',
                  padding: '0.875rem 1rem',
                  border: '2px solid #e5e7eb',
                  borderRadius: '12px',
                  fontSize: '1rem',
                  transition: 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)',
                  background: 'white',
                  outline: 'none',
                  boxSizing: 'border-box'
                }}
                onFocus={(e) => e.target.style.borderColor = '#d4af37'}
                onBlur={(e) => e.target.style.borderColor = '#e5e7eb'}
              />
            </div>

            <div>
              <label style={{
                display: 'block',
                marginBottom: '0.5rem',
                fontWeight: '600',
                color: '#374151',
                fontSize: '0.95rem'
              }}>
                ◇ E-posta *
              </label>
              <input
                type="email"
                name="email"
                value={formData.email}
                onChange={handleChange}
                placeholder="ornek@email.com"
                disabled={loading}
                required
                style={{
                  width: '100%',
                  padding: '0.875rem 1rem',
                  border: '2px solid #e5e7eb',
                  borderRadius: '12px',
                  fontSize: '1rem',
                  transition: 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)',
                  background: 'white',
                  outline: 'none',
                  boxSizing: 'border-box'
                }}
                onFocus={(e) => e.target.style.borderColor = '#d4af37'}
                onBlur={(e) => e.target.style.borderColor = '#e5e7eb'}
              />
            </div>
          </div>

          {/* Telefon */}
          <div>
            <label style={{
              display: 'block',
              marginBottom: '0.5rem',
              fontWeight: '600',
              color: '#374151',
              fontSize: '0.95rem'
            }}>
              ◇ Telefon
            </label>
            <input
              type="tel"
              name="phone"
              value={formData.phone}
              onChange={handleChange}
              placeholder="0555 123 45 67"
              disabled={loading}
              style={{
                width: '100%',
                padding: '0.875rem 1rem',
                border: '2px solid #e5e7eb',
                borderRadius: '12px',
                fontSize: '1rem',
                transition: 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)',
                background: 'white',
                outline: 'none',
                boxSizing: 'border-box'
              }}
              onFocus={(e) => e.target.style.borderColor = '#d4af37'}
              onBlur={(e) => e.target.style.borderColor = '#e5e7eb'}
            />
          </div>

          {/* Şifre ve Şifre Tekrar */}
          <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '1rem' }}>
            <div>
              <label style={{
                display: 'block',
                marginBottom: '0.5rem',
                fontWeight: '600',
                color: '#374151',
                fontSize: '0.95rem'
              }}>
                ◉ Şifre *
              </label>
              <div style={{ position: 'relative' }}>
                <input
                  type={showPassword ? 'text' : 'password'}
                  name="password"
                  value={formData.password}
                  onChange={handleChange}
                  placeholder="En az 6 karakter"
                  disabled={loading}
                  required
                  style={{
                    width: '100%',
                    padding: '0.875rem 3rem 0.875rem 1rem',
                    border: '2px solid #e5e7eb',
                    borderRadius: '12px',
                    fontSize: '1rem',
                    transition: 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)',
                    background: 'white',
                    outline: 'none',
                    boxSizing: 'border-box'
                  }}
                  onFocus={(e) => e.target.style.borderColor = '#d4af37'}
                  onBlur={(e) => e.target.style.borderColor = '#e5e7eb'}
                />
                <button
                  type="button"
                  onClick={() => setShowPassword(!showPassword)}
                  style={{
                    position: 'absolute',
                    right: '1rem',
                    top: '50%',
                    transform: 'translateY(-50%)',
                    background: 'none',
                    border: 'none',
                    cursor: 'pointer',
                    fontSize: '1.2rem'
                  }}
                >
                  {showPassword ? '◌' : '◌'}
                </button>
              </div>
            </div>

            <div>
              <label style={{
                display: 'block',
                marginBottom: '0.5rem',
                fontWeight: '600',
                color: '#374151',
                fontSize: '0.95rem'
              }}>
                ◉ Şifre Tekrar *
              </label>
              <div style={{ position: 'relative' }}>
                <input
                  type={showConfirmPassword ? 'text' : 'password'}
                  name="password_confirmation"
                  value={formData.password_confirmation}
                  onChange={handleChange}
                  placeholder="Şifrenizi tekrar girin"
                  disabled={loading}
                  required
                  style={{
                    width: '100%',
                    padding: '0.875rem 3rem 0.875rem 1rem',
                    border: '2px solid #e5e7eb',
                    borderRadius: '12px',
                    fontSize: '1rem',
                    transition: 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)',
                    background: 'white',
                    outline: 'none',
                    boxSizing: 'border-box'
                  }}
                  onFocus={(e) => e.target.style.borderColor = '#d4af37'}
                  onBlur={(e) => e.target.style.borderColor = '#e5e7eb'}
                />
                <button
                  type="button"
                  onClick={() => setShowConfirmPassword(!showConfirmPassword)}
                  style={{
                    position: 'absolute',
                    right: '1rem',
                    top: '50%',
                    transform: 'translateY(-50%)',
                    background: 'none',
                    border: 'none',
                    cursor: 'pointer',
                    fontSize: '1.2rem'
                  }}
                >
                  {showConfirmPassword ? '◌' : '◌'}
                </button>
              </div>
            </div>
          </div>

          {/* Hesap Türü */}
          <div>
            <label style={{
              display: 'block',
              marginBottom: '0.5rem',
              fontWeight: '600',
              color: '#374151',
              fontSize: '0.95rem'
            }}>
              ◆ Hesap Türü
            </label>
            <select
              name="role_id"
              value={formData.role_id}
              onChange={handleChange}
              disabled={loading}
              style={{
                width: '100%',
                padding: '0.875rem 1rem',
                border: '2px solid #e5e7eb',
                borderRadius: '12px',
                fontSize: '1rem',
                transition: 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)',
                background: 'white',
                outline: 'none',
                cursor: 'pointer',
                boxSizing: 'border-box'
              }}
              onFocus={(e) => e.target.style.borderColor = '#d4af37'}
              onBlur={(e) => e.target.style.borderColor = '#e5e7eb'}
            >
              <option value={1}>◆ Kayıtlı Kullanıcı</option>
              <option value={2}>◆ Emlakçı</option>
            </select>
            <p style={{
              fontSize: '0.85rem',
              color: '#6b7280',
              marginTop: '0.5rem',
              marginBottom: '0',
              fontStyle: 'italic'
            }}>
              * Emlakçı hesabı için admin onayı gereklidir
            </p>
          </div>

          {/* Kayıt Ol Butonu */}
          <button 
            type="submit" 
            disabled={loading}
            style={{
              width: '100%',
              padding: '1rem',
              background: loading ? '#9ca3af' : 'linear-gradient(135deg, #d4af37 0%, #b8941f 100%)',
              color: 'white',
              border: 'none',
              borderRadius: '12px',
              fontSize: '1.1rem',
              fontWeight: '600',
              cursor: loading ? 'not-allowed' : 'pointer',
              transition: 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)',
              transform: loading ? 'none' : 'translateY(0)',
              boxShadow: loading ? 'none' : '0 4px 15px rgba(212, 175, 55, 0.4)',
              marginTop: '1rem'
            }}
            onMouseEnter={(e) => {
              if (!loading) {
                e.target.style.transform = 'translateY(-2px)';
                e.target.style.boxShadow = '0 6px 20px rgba(212, 175, 55, 0.6)';
              }
            }}
            onMouseLeave={(e) => {
              if (!loading) {
                e.target.style.transform = 'translateY(0)';
                e.target.style.boxShadow = '0 4px 15px rgba(212, 175, 55, 0.4)';
              }
            }}
          >
            {loading ? (
              <span style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', gap: '0.5rem' }}>
                <span style={{ 
                  width: '20px', 
                  height: '20px', 
                  border: '2px solid #ffffff40',
                  borderTop: '2px solid white',
                  borderRadius: '50%',
                  animation: 'spin 1s linear infinite'
                }}></span>
                Kayıt oluşturuluyor...
              </span>
            ) : (
              '◆ Kayıt Ol'
            )}
          </button>
        </form>

        {/* Alt Linkler */}
        <div style={{ 
          textAlign: 'center', 
          marginTop: '2rem',
          paddingTop: '2rem',
          borderTop: '1px solid #e5e7eb'
        }}>
          <p style={{ color: '#6b7280', marginBottom: '1rem' }}>
            Zaten hesabınız var mı?{' '}
            <button
              type="button"
              onClick={() => navigate('/login')}
              style={{
                background: 'none',
                border: 'none',
                color: '#d4af37',
                textDecoration: 'none',
                cursor: 'pointer',
                fontWeight: '600',
                fontSize: '1rem'
              }}
              onMouseEnter={(e) => e.target.style.textDecoration = 'underline'}
              onMouseLeave={(e) => e.target.style.textDecoration = 'none'}
            >
              Giriş Yap
            </button>
          </p>

          <button
            type="button"
            onClick={() => navigate('/')}
            style={{
              background: 'rgba(212, 175, 55, 0.1)',
              border: '2px solid rgba(212, 175, 55, 0.2)',
              color: '#d4af37',
              padding: '0.75rem 1.5rem',
              borderRadius: '12px',
              fontSize: '0.95rem',
              fontWeight: '600',
              cursor: 'pointer',
              transition: 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)'
            }}
            onMouseEnter={(e) => {
              e.target.style.background = 'rgba(212, 175, 55, 0.2)';
              e.target.style.borderColor = '#d4af37';
            }}
            onMouseLeave={(e) => {
              e.target.style.background = 'rgba(212, 175, 55, 0.1)';
              e.target.style.borderColor = 'rgba(212, 175, 55, 0.2)';
            }}
          >
            ◆ Ana Sayfaya Dön
          </button>
        </div>

        {/* Bilgilendirme */}
        <div style={{
          marginTop: '2rem',
          padding: '1.5rem',
          background: 'linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%)',
          borderRadius: '16px',
          border: '1px solid #bae6fd'
        }}>
          <h4 style={{
            marginBottom: '1rem',
            color: '#0369a1',
            fontSize: '1.1rem',
            fontWeight: '600',
            display: 'flex',
            alignItems: 'center',
            gap: '0.5rem'
          }}>
            ℹ️ Bilgilendirme
          </h4>
          <ul style={{
            marginLeft: '1.5rem',
            marginBottom: '0',
            color: '#0369a1',
            lineHeight: '1.6'
          }}>
            <li>Kayıt olduktan sonra hemen giriş yapabilirsiniz</li>
            <li>Emlakçı hesabı için admin onayı beklemeniz gerekebilir</li>
            <li>Kişisel bilgileriniz güvenle saklanır</li>
          </ul>
        </div>
      </div>

      {/* CSS Animasyonları */}
      <style jsx>{`
        @keyframes spin {
          0% { transform: rotate(0deg); }
          100% { transform: rotate(360deg); }
        }
        
        @media (max-width: 768px) {
          .grid-2 {
            grid-template-columns: 1fr !important;
          }
        }
      `}</style>
    </div>
  );
};

export default RegisterPage; 