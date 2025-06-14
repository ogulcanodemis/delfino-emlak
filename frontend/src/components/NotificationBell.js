import React, { useState, useEffect } from 'react';
import { getNotifications, getUnreadNotificationCount, markNotificationAsRead, markAllNotificationsAsRead } from '../services/apiService';
import './NotificationBell.css';

const NotificationBell = ({ user }) => {
  const [notifications, setNotifications] = useState([]);
  const [unreadCount, setUnreadCount] = useState(0);
  const [isOpen, setIsOpen] = useState(false);
  const [loading, setLoading] = useState(false);

  // Bildirimleri yükle
  const loadNotifications = async () => {
    try {
      setLoading(true);
      const [notificationsData, countData] = await Promise.all([
        getNotifications(),
        getUnreadNotificationCount()
      ]);
      
      setNotifications(notificationsData);
      setUnreadCount(countData);
    } catch (error) {
      console.error('Bildirimler yüklenirken hata:', error);
    } finally {
      setLoading(false);
    }
  };

  // Bildirimi okundu işaretle
  const handleMarkAsRead = async (notificationId) => {
    try {
      await markNotificationAsRead(notificationId);
      
      // Local state'i güncelle
      setNotifications(prev => 
        prev.map(notif => 
          notif.id === notificationId 
            ? { ...notif, is_read: 1 }
            : notif
        )
      );
      
      // Okunmamış sayısını güncelle
      setUnreadCount(prev => Math.max(0, prev - 1));
    } catch (error) {
      console.error('Bildirim okundu işaretlenirken hata:', error);
    }
  };

  // Tüm bildirimleri okundu işaretle
  const handleMarkAllAsRead = async () => {
    try {
      await markAllNotificationsAsRead();
      
      // Local state'i güncelle
      setNotifications(prev => 
        prev.map(notif => ({ ...notif, is_read: 1 }))
      );
      setUnreadCount(0);
    } catch (error) {
      console.error('Tüm bildirimler okundu işaretlenirken hata:', error);
    }
  };

  // Bildirim tipine göre ikon
  const getNotificationIcon = (type) => {
    switch (type) {
      case 'property_approved':
        return '✅';
      case 'property_rejected':
        return '❌';
      case 'property_approval_required':
        return '⏳';
      case 'role_request':
        return '👤';
      case 'system':
        return '🔔';
      default:
        return '📢';
    }
  };

  // Bildirim tipine göre renk
  const getNotificationColor = (type) => {
    switch (type) {
      case 'property_approved':
        return '#10b981';
      case 'property_rejected':
        return '#ef4444';
      case 'property_approval_required':
        return '#f59e0b';
      case 'role_request':
        return '#3b82f6';
      case 'system':
        return '#6366f1';
      default:
        return '#6b7280';
    }
  };

  // Tarih formatı
  const formatDate = (dateString) => {
    const date = new Date(dateString);
    const now = new Date();
    const diffInHours = (now - date) / (1000 * 60 * 60);
    
    if (diffInHours < 1) {
      return 'Az önce';
    } else if (diffInHours < 24) {
      return `${Math.floor(diffInHours)} saat önce`;
    } else {
      return date.toLocaleDateString('tr-TR');
    }
  };

  // Component mount olduğunda bildirimleri yükle
  useEffect(() => {
    if (user) {
      loadNotifications();
      
      // Her 30 saniyede bir kontrol et
      const interval = setInterval(loadNotifications, 30000);
      return () => clearInterval(interval);
    }
  }, [user]);

  // Kullanıcı giriş yapmamışsa gösterme
  if (!user) return null;

  return (
    <div className="notification-bell">
      <button 
        className="notification-button"
        onClick={() => setIsOpen(!isOpen)}
        aria-label="Bildirimler"
      >
        🔔
        {unreadCount > 0 && (
          <span className="notification-badge">
            {unreadCount > 99 ? '99+' : unreadCount}
          </span>
        )}
      </button>

      {isOpen && (
        <div className="notification-dropdown">
          <div className="notification-header">
            <h3>Bildirimler</h3>
            {unreadCount > 0 && (
              <button 
                className="mark-all-read-btn"
                onClick={handleMarkAllAsRead}
              >
                Tümünü Okundu İşaretle
              </button>
            )}
          </div>

          <div className="notification-list">
            {loading ? (
              <div className="notification-loading">
                <div className="spinner"></div>
                <span>Bildirimler yükleniyor...</span>
              </div>
            ) : notifications.length === 0 ? (
              <div className="no-notifications">
                <span>📭</span>
                <p>Henüz bildiriminiz yok</p>
              </div>
            ) : (
              notifications.slice(0, 10).map(notification => (
                <div 
                  key={notification.id}
                  className={`notification-item ${notification.is_read ? 'read' : 'unread'}`}
                  onClick={() => !notification.is_read && handleMarkAsRead(notification.id)}
                >
                  <div className="notification-content">
                    <div className="notification-icon">
                      <span 
                        style={{ color: getNotificationColor(notification.type) }}
                      >
                        {getNotificationIcon(notification.type)}
                      </span>
                    </div>
                    <div className="notification-text">
                      <h4>{notification.title}</h4>
                      <p>{notification.message}</p>
                      <span className="notification-time">
                        {formatDate(notification.created_at)}
                      </span>
                    </div>
                  </div>
                  {!notification.is_read && (
                    <div className="unread-indicator"></div>
                  )}
                </div>
              ))
            )}
          </div>

          {notifications.length > 10 && (
            <div className="notification-footer">
              <button className="view-all-btn">
                Tüm Bildirimleri Görüntüle
              </button>
            </div>
          )}
        </div>
      )}

      {/* Dropdown dışına tıklandığında kapat */}
      {isOpen && (
        <div 
          className="notification-overlay"
          onClick={() => setIsOpen(false)}
        ></div>
      )}
    </div>
  );
};

export default NotificationBell; 