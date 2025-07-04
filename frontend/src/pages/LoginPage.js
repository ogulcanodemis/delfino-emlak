import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { login } from '../services/authService';

const LoginPage = ({ onLogin }) => {
  const navigate = useNavigate();
  const [formData, setFormData] = useState({
    email: '',
    password: ''
  });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [showPassword, setShowPassword] = useState(false);

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
    if (!formData.email || !formData.password) {
      setError('Lütfen tüm alanları doldurun');
      return;
    }

    if (!formData.email.includes('@')) {
      setError('Geçerli bir e-posta adresi girin');
      return;
    }

    try {
      setLoading(true);
      setError('');
      
      // Gerçek API'ye giriş yap
      const user = await login(formData.email, formData.password);
      
      // Başarılı giriş
      onLogin(user);
      navigate('/');
      
    } catch (error) {
      console.error('Giriş hatası:', error);
      setError(error.message || 'Giriş yapılırken bir hata oluştu');
    } finally {
      setLoading(false);
    }
  };


  return (
    <div style={{
      background: 'linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 50%, #1a1a1a 100%)',
      minHeight: '100vh',
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      padding: '2rem',
      animation: 'fadeIn 0.8s ease-out'
    }}>
      <div style={{
        background: 'rgba(255, 255, 255, 0.95)',
        backdropFilter: 'blur(20px)',
        borderRadius: '20px',
        padding: '3rem',
        boxShadow: '0 20px 40px rgba(0, 0, 0, 0.1)',
        border: '1px solid rgba(255, 255, 255, 0.2)',
        maxWidth: '450px',
        width: '100%',
        animation: 'fadeInUp 0.8s ease-out'
      }}>
        {/* Logo ve Başlık */}
        <div style={{
          textAlign: 'center',
          marginBottom: '2.5rem'
        }}>
          <div style={{
            fontSize: '4rem',
            marginBottom: '1rem',
            textShadow: '0 4px 8px rgba(0, 0, 0, 0.1)'
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
            Giriş Yap
          </h1>
          <p style={{
            color: '#6b7280',
            fontSize: '1.1rem',
            fontWeight: '500'
          }}>
            BK Yatırım hesabınıza giriş yapın
          </p>
        </div>
        
        {/* Hata mesajı */}
        {error && (
          <div style={{
            background: 'linear-gradient(135deg, #fee2e2 0%, #fecaca 100%)',
            color: '#dc2626',
            padding: '1rem',
            borderRadius: '12px',
            marginBottom: '1.5rem',
            textAlign: 'center',
            border: '1px solid rgba(220, 38, 38, 0.2)',
            fontWeight: '500',
            animation: 'shake 0.5s ease-out'
          }}>
            ⚠️ {error}
          </div>
        )}

        {/* Giriş Formu */}
        <form onSubmit={handleSubmit} style={{ marginBottom: '2rem' }}>
          <div style={{ marginBottom: '1.5rem' }}>
            <label style={{
              display: 'block',
              marginBottom: '0.5rem',
              color: '#374151',
              fontWeight: '600',
              fontSize: '1rem'
            }}>
              ◇ E-posta
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
                padding: '1rem',
                border: '2px solid #e5e7eb',
                borderRadius: '12px',
                fontSize: '1rem',
                transition: 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)',
                background: 'white',
                boxSizing: 'border-box'
              }}
              onFocus={(e) => {
                e.target.style.borderColor = '#d4af37';
                e.target.style.boxShadow = '0 0 0 3px rgba(212, 175, 55, 0.1)';
                e.target.style.transform = 'translateY(-2px)';
              }}
              onBlur={(e) => {
                e.target.style.borderColor = '#e5e7eb';
                e.target.style.boxShadow = 'none';
                e.target.style.transform = 'translateY(0)';
              }}
            />
          </div>

          <div style={{ marginBottom: '2rem' }}>
            <label style={{
              display: 'block',
              marginBottom: '0.5rem',
              color: '#374151',
              fontWeight: '600',
              fontSize: '1rem'
            }}>
              ◌ Şifre
            </label>
            <div style={{ position: 'relative' }}>
              <input
                type={showPassword ? 'text' : 'password'}
                name="password"
                value={formData.password}
                onChange={handleChange}
                placeholder="Şifrenizi girin"
                disabled={loading}
                required
                style={{
                  width: '100%',
                  padding: '1rem',
                  paddingRight: '3rem',
                  border: '2px solid #e5e7eb',
                  borderRadius: '12px',
                  fontSize: '1rem',
                  transition: 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)',
                  background: 'white',
                  boxSizing: 'border-box'
                }}
                onFocus={(e) => {
                  e.target.style.borderColor = '#d4af37';
                  e.target.style.boxShadow = '0 0 0 3px rgba(212, 175, 55, 0.1)';
                  e.target.style.transform = 'translateY(-2px)';
                }}
                onBlur={(e) => {
                  e.target.style.borderColor = '#e5e7eb';
                  e.target.style.boxShadow = 'none';
                  e.target.style.transform = 'translateY(0)';
                }}
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
                  fontSize: '1.2rem',
                  cursor: 'pointer',
                  color: '#6b7280',
                  transition: 'color 0.3s ease'
                }}
                onMouseEnter={(e) => e.target.style.color = '#d4af37'}
                onMouseLeave={(e) => e.target.style.color = '#6b7280'}
              >
                {showPassword ? '◌' : '◌'}
              </button>
            </div>
          </div>

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
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              gap: '0.5rem'
            }}
            onMouseEnter={(e) => {
              if (!loading) {
                e.target.style.transform = 'translateY(-2px)';
                e.target.style.boxShadow = '0 10px 25px rgba(212, 175, 55, 0.4)';
              }
            }}
            onMouseLeave={(e) => {
              if (!loading) {
                e.target.style.transform = 'translateY(0)';
                e.target.style.boxShadow = 'none';
              }
            }}
          >
            {loading && (
              <div style={{
                width: '20px',
                height: '20px',
                border: '2px solid rgba(255, 255, 255, 0.3)',
                borderTop: '2px solid white',
                borderRadius: '50%',
                animation: 'spin 1s linear infinite'
              }}></div>
            )}
            {loading ? 'Giriş yapılıyor...' : '→ Giriş Yap'}
          </button>
        </form>


        {/* Kayıt Ol ve Ana Sayfa Linkleri */}
        <div style={{
          display: 'flex',
          flexDirection: 'column',
          gap: '1rem',
          alignItems: 'center'
        }}>
          <p style={{
            color: '#6b7280',
            fontSize: '1rem',
            textAlign: 'center',
            margin: 0
          }}>
            Hesabınız yok mu?{' '}
            <button
              type="button"
              onClick={() => navigate('/register')}
              style={{
                background: 'none',
                border: 'none',
                color: '#d4af37',
                textDecoration: 'none',
                cursor: 'pointer',
                fontWeight: '600',
                fontSize: '1rem',
                transition: 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)'
              }}
              onMouseEnter={(e) => {
                e.target.style.textDecoration = 'underline';
                e.target.style.color = '#b8941f';
              }}
              onMouseLeave={(e) => {
                e.target.style.textDecoration = 'none';
                e.target.style.color = '#d4af37';
              }}
            >
              Kayıt Ol
            </button>
          </p>

          <button
            type="button"
            onClick={() => navigate('/')}
            style={{
              background: 'rgba(255, 255, 255, 0.8)',
              color: '#d4af37',
              border: '2px solid #d4af37',
              padding: '0.75rem 1.5rem',
              borderRadius: '12px',
              fontSize: '1rem',
              fontWeight: '600',
              cursor: 'pointer',
              transition: 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)',
              backdropFilter: 'blur(10px)'
            }}
            onMouseEnter={(e) => {
              e.target.style.background = '#d4af37';
              e.target.style.color = '#1a1a1a';
              e.target.style.transform = 'translateY(-2px)';
              e.target.style.boxShadow = '0 4px 12px rgba(212, 175, 55, 0.3)';
            }}
            onMouseLeave={(e) => {
              e.target.style.background = 'rgba(255, 255, 255, 0.8)';
              e.target.style.color = '#d4af37';
              e.target.style.transform = 'translateY(0)';
              e.target.style.boxShadow = 'none';
            }}
          >
            ◆ Ana Sayfaya Dön
          </button>
        </div>

      </div>

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
        
        @keyframes shake {
          0%, 100% { transform: translateX(0); }
          25% { transform: translateX(-5px); }
          75% { transform: translateX(5px); }
        }
        
        @media (max-width: 768px) {
          .login-container {
            padding: 1rem !important;
          }
          
          .login-form {
            padding: 2rem !important;
            margin: 1rem !important;
          }
          
          .login-title {
            font-size: 2rem !important;
          }
          
          .login-icon {
            font-size: 3rem !important;
          }
        }
        
        @media (max-width: 480px) {
          .login-container {
            padding: 0.5rem !important;
          }
          
          .login-form {
            padding: 1.5rem !important;
            margin: 0.5rem !important;
          }
          
          .login-title {
            font-size: 1.8rem !important;
          }
          
          .test-buttons {
            flex-direction: column !important;
            gap: 0.5rem !important;
          }
        }
        
        @media (prefers-reduced-motion: reduce) {
          * {
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.01ms !important;
          }
        }
        
        @media (prefers-contrast: high) {
          .login-form {
            border: 2px solid #000 !important;
          }
          
          .form-input {
            border: 2px solid #000 !important;
          }
        }
        
        @media (prefers-color-scheme: dark) {
          .login-form {
            background: rgba(31, 41, 55, 0.95) !important;
            color: white !important;
          }
          
          .form-input {
            background: rgba(55, 65, 81, 0.8) !important;
            color: white !important;
            border-color: rgba(75, 85, 99, 0.8) !important;
          }
        }
      `}</style>
    </div>
  );
};

export default LoginPage; 