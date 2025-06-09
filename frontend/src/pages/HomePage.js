import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { getFeaturedProperties, addToFavorites, removeFromFavorites } from '../services/apiService';
import PropertyCard from '../components/PropertyCard';

const HomePage = ({ user }) => {
  const navigate = useNavigate();
  const [featuredProperties, setFeaturedProperties] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [favoriteIds, setFavoriteIds] = useState([]);

  useEffect(() => {
    loadFeaturedProperties();
  }, []);

  const loadFeaturedProperties = async () => {
    try {
      setLoading(true);
      const properties = await getFeaturedProperties(6);
      setFeaturedProperties(properties);
    } catch (error) {
      setError('Öne çıkan ilanlar yüklenirken bir hata oluştu: ' + error.message);
    } finally {
      setLoading(false);
    }
  };

  const handleFavoriteToggle = async (propertyId) => {
    if (!user) {
      alert('Favorilere eklemek için giriş yapmalısınız');
      return;
    }

    try {
      const isFavorite = favoriteIds.includes(propertyId);
      if (isFavorite) {
        await removeFromFavorites(propertyId);
        setFavoriteIds(prev => prev.filter(id => id !== propertyId));
      } else {
        await addToFavorites(propertyId);
        setFavoriteIds(prev => [...prev, propertyId]);
      }
    } catch (error) {
      alert('Favori işlemi sırasında bir hata oluştu: ' + error.message);
    }
  };

  return (
    <div style={{ 
      background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
      minHeight: '100vh'
    }}>
      {/* Hero Section */}
      <section style={{
        background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
        color: 'white',
        padding: '6rem 2rem',
        textAlign: 'center',
        position: 'relative',
        overflow: 'hidden'
      }}>
        {/* Background Pattern */}
        <div style={{
          position: 'absolute',
          top: 0,
          left: 0,
          right: 0,
          bottom: 0,
          background: `url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.1'%3E%3Ccircle cx='30' cy='30' r='2'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E")`,
          opacity: 0.3
        }}></div>

        <div style={{
          maxWidth: '1200px',
          margin: '0 auto',
          position: 'relative',
          zIndex: 1
        }}>
          <div style={{
            animation: 'fadeInUp 0.8s ease-out',
            marginBottom: '3rem'
          }}>
            <div style={{
              fontSize: '5rem',
              marginBottom: '1rem',
              textShadow: '0 4px 8px rgba(0,0,0,0.3)'
            }}>
              🏠
            </div>
            <h1 style={{
              fontSize: '3.5rem',
              fontWeight: '800',
              marginBottom: '1.5rem',
              textShadow: '0 4px 8px rgba(0,0,0,0.3)',
              lineHeight: '1.2'
            }}>
              Hayalinizdeki Evi Bulun
            </h1>
            <p style={{
              fontSize: '1.3rem',
              marginBottom: '3rem',
              opacity: 0.9,
              maxWidth: '600px',
              margin: '0 auto 3rem auto',
              lineHeight: '1.6'
            }}>
              Emlak Delfino ile binlerce ilan arasından size en uygun olanı keşfedin
            </p>
          </div>
          
          <div style={{
            display: 'grid',
            gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))',
            gap: '2rem',
            marginBottom: '3rem',
            animation: 'fadeInUp 0.8s ease-out 0.2s both'
          }}>
            <div style={{
              background: 'rgba(255, 255, 255, 0.15)',
              backdropFilter: 'blur(10px)',
              padding: '2rem',
              borderRadius: '20px',
              border: '1px solid rgba(255, 255, 255, 0.2)',
              transition: 'all 0.3s ease'
            }}
            onMouseEnter={(e) => {
              e.currentTarget.style.transform = 'translateY(-5px)';
              e.currentTarget.style.background = 'rgba(255, 255, 255, 0.25)';
            }}
            onMouseLeave={(e) => {
              e.currentTarget.style.transform = 'translateY(0)';
              e.currentTarget.style.background = 'rgba(255, 255, 255, 0.15)';
            }}>
              <div style={{
                fontSize: '2.5rem',
                fontWeight: '800',
                marginBottom: '0.5rem',
                textShadow: '0 2px 4px rgba(0,0,0,0.3)'
              }}>
                1000+
              </div>
              <div style={{
                fontSize: '1rem',
                opacity: 0.9,
                fontWeight: '500'
              }}>
                Aktif İlan
              </div>
            </div>
            
            <div style={{
              background: 'rgba(255, 255, 255, 0.15)',
              backdropFilter: 'blur(10px)',
              padding: '2rem',
              borderRadius: '20px',
              border: '1px solid rgba(255, 255, 255, 0.2)',
              transition: 'all 0.3s ease'
            }}
            onMouseEnter={(e) => {
              e.currentTarget.style.transform = 'translateY(-5px)';
              e.currentTarget.style.background = 'rgba(255, 255, 255, 0.25)';
            }}
            onMouseLeave={(e) => {
              e.currentTarget.style.transform = 'translateY(0)';
              e.currentTarget.style.background = 'rgba(255, 255, 255, 0.15)';
            }}>
              <div style={{
                fontSize: '2.5rem',
                fontWeight: '800',
                marginBottom: '0.5rem',
                textShadow: '0 2px 4px rgba(0,0,0,0.3)'
              }}>
                500+
              </div>
              <div style={{
                fontSize: '1rem',
                opacity: 0.9,
                fontWeight: '500'
              }}>
                Mutlu Müşteri
              </div>
            </div>
            
            <div style={{
              background: 'rgba(255, 255, 255, 0.15)',
              backdropFilter: 'blur(10px)',
              padding: '2rem',
              borderRadius: '20px',
              border: '1px solid rgba(255, 255, 255, 0.2)',
              transition: 'all 0.3s ease'
            }}
            onMouseEnter={(e) => {
              e.currentTarget.style.transform = 'translateY(-5px)';
              e.currentTarget.style.background = 'rgba(255, 255, 255, 0.25)';
            }}
            onMouseLeave={(e) => {
              e.currentTarget.style.transform = 'translateY(0)';
              e.currentTarget.style.background = 'rgba(255, 255, 255, 0.15)';
            }}>
              <div style={{
                fontSize: '2.5rem',
                fontWeight: '800',
                marginBottom: '0.5rem',
                textShadow: '0 2px 4px rgba(0,0,0,0.3)'
              }}>
                50+
              </div>
              <div style={{
                fontSize: '1rem',
                opacity: 0.9,
                fontWeight: '500'
              }}>
                Şehir
              </div>
            </div>
          </div>
          
          <div style={{
            display: 'flex',
            gap: '1.5rem',
            justifyContent: 'center',
            flexWrap: 'wrap',
            animation: 'fadeInUp 0.8s ease-out 0.4s both'
          }}>
            <button 
              onClick={() => navigate('/properties')} 
              style={{
                background: 'rgba(255, 255, 255, 0.9)',
                color: '#667eea',
                border: 'none',
                padding: '1rem 2.5rem',
                borderRadius: '50px',
                fontSize: '1.1rem',
                fontWeight: '600',
                cursor: 'pointer',
                transition: 'all 0.3s ease',
                boxShadow: '0 8px 25px rgba(0, 0, 0, 0.15)',
                backdropFilter: 'blur(10px)'
              }}
              onMouseEnter={(e) => {
                e.target.style.transform = 'translateY(-3px)';
                e.target.style.boxShadow = '0 12px 35px rgba(0, 0, 0, 0.25)';
                e.target.style.background = 'white';
              }}
              onMouseLeave={(e) => {
                e.target.style.transform = 'translateY(0)';
                e.target.style.boxShadow = '0 8px 25px rgba(0, 0, 0, 0.15)';
                e.target.style.background = 'rgba(255, 255, 255, 0.9)';
              }}
            >
              🔍 İlanları Keşfet
            </button>
            {!user && (
              <button 
                onClick={() => navigate('/register')} 
                style={{
                  background: 'transparent',
                  color: 'white',
                  border: '2px solid rgba(255, 255, 255, 0.8)',
                  padding: '1rem 2.5rem',
                  borderRadius: '50px',
                  fontSize: '1.1rem',
                  fontWeight: '600',
                  cursor: 'pointer',
                  transition: 'all 0.3s ease',
                  backdropFilter: 'blur(10px)'
                }}
                onMouseEnter={(e) => {
                  e.target.style.transform = 'translateY(-3px)';
                  e.target.style.background = 'rgba(255, 255, 255, 0.2)';
                  e.target.style.borderColor = 'white';
                }}
                onMouseLeave={(e) => {
                  e.target.style.transform = 'translateY(0)';
                  e.target.style.background = 'transparent';
                  e.target.style.borderColor = 'rgba(255, 255, 255, 0.8)';
                }}
              >
                🚀 Ücretsiz Üye Ol
              </button>
            )}
          </div>
        </div>
      </section>

      {/* Featured Properties */}
      <section style={{
        background: 'white',
        padding: '6rem 2rem',
        position: 'relative'
      }}>
        <div style={{
          maxWidth: '1200px',
          margin: '0 auto'
        }}>
          <div style={{
            textAlign: 'center',
            marginBottom: '4rem',
            animation: 'fadeInUp 0.8s ease-out'
          }}>
            <div style={{
              fontSize: '3rem',
              marginBottom: '1rem'
            }}>
              ⭐
            </div>
            <h2 style={{
              fontSize: '2.5rem',
              fontWeight: '700',
              marginBottom: '1rem',
              background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
              WebkitBackgroundClip: 'text',
              WebkitTextFillColor: 'transparent',
              backgroundClip: 'text'
            }}>
              Öne Çıkan İlanlar
            </h2>
            <p style={{
              fontSize: '1.2rem',
              color: '#6b7280',
              maxWidth: '600px',
              margin: '0 auto'
            }}>
              En popüler ve özel seçilmiş emlak ilanları
            </p>
          </div>

          {loading && (
            <div style={{
              display: 'flex',
              flexDirection: 'column',
              alignItems: 'center',
              justifyContent: 'center',
              padding: '4rem',
              animation: 'fadeIn 0.5s ease-out'
            }}>
              <div style={{
                width: '60px',
                height: '60px',
                border: '4px solid #f3f4f6',
                borderTop: '4px solid #667eea',
                borderRadius: '50%',
                animation: 'spin 1s linear infinite',
                marginBottom: '1rem'
              }}></div>
              <p style={{
                color: '#6b7280',
                fontSize: '1.1rem'
              }}>
                Öne çıkan ilanlar yükleniyor...
              </p>
            </div>
          )}

          {error && (
            <div style={{
              background: 'linear-gradient(135deg, #fee2e2 0%, #fecaca 100%)',
              color: '#dc2626',
              padding: '2rem',
              borderRadius: '16px',
              textAlign: 'center',
              border: '1px solid #fca5a5',
              animation: 'fadeIn 0.5s ease-out'
            }}>
              <div style={{
                fontSize: '2rem',
                marginBottom: '1rem'
              }}>
                ⚠️
              </div>
              <p style={{
                marginBottom: '1.5rem',
                fontSize: '1.1rem'
              }}>
                {error}
              </p>
              <button 
                onClick={loadFeaturedProperties}
                style={{
                  background: '#dc2626',
                  color: 'white',
                  border: 'none',
                  padding: '0.75rem 1.5rem',
                  borderRadius: '12px',
                  fontSize: '1rem',
                  fontWeight: '600',
                  cursor: 'pointer',
                  transition: 'all 0.3s ease'
                }}
                onMouseEnter={(e) => {
                  e.target.style.transform = 'translateY(-2px)';
                  e.target.style.background = '#b91c1c';
                }}
                onMouseLeave={(e) => {
                  e.target.style.transform = 'translateY(0)';
                  e.target.style.background = '#dc2626';
                }}
              >
                🔄 Tekrar Dene
              </button>
            </div>
          )}

          {!loading && !error && (
            <>
              {featuredProperties.length === 0 ? (
                <div style={{
                  textAlign: 'center',
                  padding: '4rem 2rem',
                  animation: 'fadeIn 0.5s ease-out'
                }}>
                  <div style={{
                    fontSize: '4rem',
                    marginBottom: '1.5rem',
                    opacity: 0.6
                  }}>
                    🏠
                  </div>
                  <h3 style={{
                    fontSize: '1.8rem',
                    fontWeight: '600',
                    marginBottom: '1rem',
                    color: '#374151'
                  }}>
                    Henüz öne çıkan ilan yok
                  </h3>
                  <p style={{
                    color: '#6b7280',
                    marginBottom: '2rem',
                    fontSize: '1.1rem'
                  }}>
                    Yakında harika ilanlar burada görünecek.
                  </p>
                  <button 
                    onClick={() => navigate('/properties')}
                    style={{
                      background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                      color: 'white',
                      border: 'none',
                      padding: '1rem 2rem',
                      borderRadius: '12px',
                      fontSize: '1.1rem',
                      fontWeight: '600',
                      cursor: 'pointer',
                      transition: 'all 0.3s ease',
                      boxShadow: '0 4px 15px rgba(102, 126, 234, 0.4)'
                    }}
                    onMouseEnter={(e) => {
                      e.target.style.transform = 'translateY(-2px)';
                      e.target.style.boxShadow = '0 6px 20px rgba(102, 126, 234, 0.6)';
                    }}
                    onMouseLeave={(e) => {
                      e.target.style.transform = 'translateY(0)';
                      e.target.style.boxShadow = '0 4px 15px rgba(102, 126, 234, 0.4)';
                    }}
                  >
                    📋 Tüm İlanları Görüntüle
                  </button>
                </div>
              ) : (
                <>
                  <div style={{
                    display: 'grid',
                    gridTemplateColumns: 'repeat(auto-fit, minmax(350px, 1fr))',
                    gap: '2rem',
                    marginBottom: '3rem',
                    animation: 'fadeInUp 0.8s ease-out 0.2s both'
                  }}>
                    {featuredProperties.map((property, index) => (
                      <div 
                        key={property.id} 
                        onClick={() => navigate(`/property/${property.id}`)}
                        style={{
                          cursor: 'pointer',
                          transition: 'all 0.3s ease',
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
                  
                  <div style={{
                    textAlign: 'center',
                    animation: 'fadeInUp 0.8s ease-out 0.4s both'
                  }}>
                    <button 
                      onClick={() => navigate('/properties')}
                      style={{
                        background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                        color: 'white',
                        border: 'none',
                        padding: '1rem 2.5rem',
                        borderRadius: '50px',
                        fontSize: '1.1rem',
                        fontWeight: '600',
                        cursor: 'pointer',
                        transition: 'all 0.3s ease',
                        boxShadow: '0 4px 15px rgba(102, 126, 234, 0.4)'
                      }}
                      onMouseEnter={(e) => {
                        e.target.style.transform = 'translateY(-2px)';
                        e.target.style.boxShadow = '0 6px 20px rgba(102, 126, 234, 0.6)';
                      }}
                      onMouseLeave={(e) => {
                        e.target.style.transform = 'translateY(0)';
                        e.target.style.boxShadow = '0 4px 15px rgba(102, 126, 234, 0.4)';
                      }}
                    >
                      📋 Tüm İlanları Görüntüle →
                    </button>
                  </div>
                </>
              )}
            </>
          )}
        </div>
      </section>

      {/* Features Section */}
      <section style={{
        background: 'linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%)',
        padding: '6rem 2rem',
        position: 'relative'
      }}>
        <div style={{
          maxWidth: '1200px',
          margin: '0 auto'
        }}>
          <div style={{
            textAlign: 'center',
            marginBottom: '4rem',
            animation: 'fadeInUp 0.8s ease-out'
          }}>
            <div style={{
              fontSize: '3rem',
              marginBottom: '1rem'
            }}>
              🌟
            </div>
            <h2 style={{
              fontSize: '2.5rem',
              fontWeight: '700',
              marginBottom: '1rem',
              background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
              WebkitBackgroundClip: 'text',
              WebkitTextFillColor: 'transparent',
              backgroundClip: 'text'
            }}>
              Neden Emlak Delfino?
            </h2>
            <p style={{
              fontSize: '1.2rem',
              color: '#6b7280',
              maxWidth: '600px',
              margin: '0 auto'
            }}>
              Size en iyi emlak deneyimini sunmak için buradayız
            </p>
          </div>

          <div style={{
            display: 'grid',
            gridTemplateColumns: 'repeat(auto-fit, minmax(300px, 1fr))',
            gap: '2rem',
            animation: 'fadeInUp 0.8s ease-out 0.2s both'
          }}>
            {[
              {
                icon: '🔍',
                title: 'Kolay Arama',
                description: 'Gelişmiş filtreleme seçenekleri ile istediğiniz evi kolayca bulun'
              },
              {
                icon: '💰',
                title: 'Şeffaf Fiyatlar',
                description: 'Gizli maliyet yok, tüm fiyatlar net ve şeffaf şekilde gösteriliyor'
              },
              {
                icon: '📱',
                title: 'Mobil Uyumlu',
                description: 'Her cihazdan kolayca erişim, istediğiniz zaman istediğiniz yerden'
              },
              {
                icon: '🤝',
                title: 'Güvenilir Emlakçılar',
                description: 'Doğrulanmış ve deneyimli emlakçılarla güvenle iletişime geçin'
              },
              {
                icon: '❤️',
                title: 'Favoriler',
                description: 'Beğendiğiniz ilanları favorilerinize ekleyin, kaybetmeyin'
              },
              {
                icon: '📊',
                title: 'Detaylı Bilgiler',
                description: 'Her ilan için kapsamlı bilgiler ve yüksek kaliteli fotoğraflar'
              }
            ].map((feature, index) => (
              <div 
                key={index}
                style={{
                  background: 'white',
                  padding: '2.5rem',
                  borderRadius: '20px',
                  textAlign: 'center',
                  boxShadow: '0 10px 30px rgba(0, 0, 0, 0.1)',
                  border: '1px solid rgba(102, 126, 234, 0.1)',
                  transition: 'all 0.3s ease',
                  animation: `fadeInUp 0.8s ease-out ${0.1 * index}s both`
                }}
                onMouseEnter={(e) => {
                  e.currentTarget.style.transform = 'translateY(-10px)';
                  e.currentTarget.style.boxShadow = '0 20px 40px rgba(102, 126, 234, 0.15)';
                  e.currentTarget.style.borderColor = 'rgba(102, 126, 234, 0.3)';
                }}
                onMouseLeave={(e) => {
                  e.currentTarget.style.transform = 'translateY(0)';
                  e.currentTarget.style.boxShadow = '0 10px 30px rgba(0, 0, 0, 0.1)';
                  e.currentTarget.style.borderColor = 'rgba(102, 126, 234, 0.1)';
                }}
              >
                <div style={{
                  fontSize: '3rem',
                  marginBottom: '1.5rem',
                  background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                  borderRadius: '50%',
                  width: '80px',
                  height: '80px',
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center',
                  margin: '0 auto 1.5rem auto',
                  boxShadow: '0 8px 25px rgba(102, 126, 234, 0.3)'
                }}>
                  {feature.icon}
                </div>
                <h3 style={{
                  fontSize: '1.5rem',
                  fontWeight: '600',
                  marginBottom: '1rem',
                  color: '#374151'
                }}>
                  {feature.title}
                </h3>
                <p style={{
                  color: '#6b7280',
                  lineHeight: '1.6',
                  fontSize: '1rem'
                }}>
                  {feature.description}
                </p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* CTA Section */}
      {!user && (
        <section style={{
          background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
          color: 'white',
          padding: '6rem 2rem',
          textAlign: 'center',
          position: 'relative',
          overflow: 'hidden'
        }}>
          {/* Background Pattern */}
          <div style={{
            position: 'absolute',
            top: 0,
            left: 0,
            right: 0,
            bottom: 0,
            background: `url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.1'%3E%3Ccircle cx='30' cy='30' r='2'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E")`,
            opacity: 0.3
          }}></div>

          <div style={{
            maxWidth: '800px',
            margin: '0 auto',
            position: 'relative',
            zIndex: 1,
            animation: 'fadeInUp 0.8s ease-out'
          }}>
            <div style={{
              fontSize: '4rem',
              marginBottom: '1.5rem'
            }}>
              🚀
            </div>
            <h2 style={{
              fontSize: '3rem',
              fontWeight: '800',
              marginBottom: '1.5rem',
              textShadow: '0 4px 8px rgba(0,0,0,0.3)'
            }}>
              Hemen Başlayın!
            </h2>
            <p style={{
              fontSize: '1.3rem',
              marginBottom: '3rem',
              opacity: 0.9,
              lineHeight: '1.6'
            }}>
              Ücretsiz hesap oluşturun ve tüm özelliklere erişim sağlayın
            </p>
            
            <div style={{
              display: 'grid',
              gridTemplateColumns: 'repeat(auto-fit, minmax(250px, 1fr))',
              gap: '1rem',
              marginBottom: '3rem',
              textAlign: 'left'
            }}>
              {[
                '✅ Fiyatları görüntüleyin',
                '✅ Emlakçılarla iletişime geçin',
                '✅ Favorilerinizi kaydedin',
                '✅ Özel tekliflerden haberdar olun'
              ].map((benefit, index) => (
                <div 
                  key={index}
                  style={{
                    background: 'rgba(255, 255, 255, 0.15)',
                    backdropFilter: 'blur(10px)',
                    padding: '1rem 1.5rem',
                    borderRadius: '12px',
                    border: '1px solid rgba(255, 255, 255, 0.2)',
                    fontSize: '1.1rem',
                    fontWeight: '500',
                    animation: `fadeInUp 0.8s ease-out ${0.1 * index}s both`
                  }}
                >
                  {benefit}
                </div>
              ))}
            </div>
            
            <button 
              onClick={() => navigate('/register')}
              style={{
                background: 'rgba(255, 255, 255, 0.9)',
                color: '#667eea',
                border: 'none',
                padding: '1.25rem 3rem',
                borderRadius: '50px',
                fontSize: '1.2rem',
                fontWeight: '700',
                cursor: 'pointer',
                transition: 'all 0.3s ease',
                boxShadow: '0 8px 25px rgba(0, 0, 0, 0.15)',
                backdropFilter: 'blur(10px)',
                animation: 'fadeInUp 0.8s ease-out 0.4s both'
              }}
              onMouseEnter={(e) => {
                e.target.style.transform = 'translateY(-3px) scale(1.05)';
                e.target.style.boxShadow = '0 12px 35px rgba(0, 0, 0, 0.25)';
                e.target.style.background = 'white';
              }}
              onMouseLeave={(e) => {
                e.target.style.transform = 'translateY(0) scale(1)';
                e.target.style.boxShadow = '0 8px 25px rgba(0, 0, 0, 0.15)';
                e.target.style.background = 'rgba(255, 255, 255, 0.9)';
              }}
            >
              🎯 Ücretsiz Kayıt Ol
            </button>
          </div>
        </section>
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
          .hero h1 {
            font-size: 2.5rem !important;
          }
          
          .hero p {
            font-size: 1.1rem !important;
          }
          
          .hero-actions {
            flex-direction: column !important;
            align-items: center !important;
          }
          
          .hero-actions button {
            width: 100% !important;
            max-width: 300px !important;
          }
          
          .features-grid {
            grid-template-columns: 1fr !important;
          }
          
          .properties-grid {
            grid-template-columns: 1fr !important;
          }
        }
        
        @media (max-width: 480px) {
          .hero {
            padding: 4rem 1rem !important;
          }
          
          .hero h1 {
            font-size: 2rem !important;
          }
          
          .section {
            padding: 4rem 1rem !important;
          }
          
          .feature-card {
            padding: 2rem !important;
          }
        }
      `}</style>
    </div>
  );
};

export default HomePage; 