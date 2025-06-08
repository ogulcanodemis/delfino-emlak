# Emlak-Delfino API Endpoints

Bu doküman, Emlak-Delfino uygulamasının tüm API endpoint'lerini, parametrelerini ve dönüş değerlerini detaylı olarak açıklamaktadır.

## 1. Kimlik Doğrulama (Authentication) API

### 1.1. Kullanıcı Kaydı
- **Endpoint**: `POST /api/auth/register`
- **Açıklama**: Yeni bir kullanıcı kaydı oluşturur
- **İstek (Request) Parametreleri**:
  ```json
  {
    "name": "string",
    "email": "string",
    "password": "string",
    "password_confirmation": "string",
    "phone": "string"
  }
  ```
- **Yanıt (Response)**:
  ```json
  {
    "status": "success",
    "message": "Kullanıcı başarıyla kaydedildi",
    "data": {
      "user": {
        "id": "integer",
        "name": "string",
        "email": "string",
        "role_id": "integer",
        "created_at": "timestamp"
      },
      "token": "string"
    }
  }
  ```

### 1.2. Kullanıcı Girişi
- **Endpoint**: `POST /api/auth/login`
- **Açıklama**: Kullanıcı girişi yapar ve token döndürür
- **İstek Parametreleri**:
  ```json
  {
    "email": "string",
    "password": "string",
    "remember_me": "boolean (optional)"
  }
  ```
- **Yanıt**:
  ```json
  {
    "status": "success",
    "message": "Giriş başarılı",
    "data": {
      "user": {
        "id": "integer",
        "name": "string",
        "email": "string",
        "role_id": "integer",
        "created_at": "timestamp"
      },
      "token": "string",
      "token_type": "Bearer",
      "expires_at": "timestamp"
    }
  }
  ```

### 1.3. Kullanıcı Bilgileri
- **Endpoint**: `GET /api/auth/me`
- **Açıklama**: Giriş yapmış kullanıcının bilgilerini döndürür
- **Header**: `Authorization: Bearer {token}`
- **Yanıt**:
  ```json
  {
    "status": "success",
    "data": {
      "user": {
        "id": "integer",
        "name": "string",
        "email": "string",
        "phone": "string",
        "address": "string",
        "role_id": "integer",
        "role": {
          "id": "integer",
          "name": "string"
        },
        "created_at": "timestamp",
        "updated_at": "timestamp"
      }
    }
  }
  ```

### 1.4. Kullanıcı Çıkışı
- **Endpoint**: `POST /api/auth/logout`
- **Açıklama**: Kullanıcı oturumunu sonlandırır
- **Header**: `Authorization: Bearer {token}`
- **Yanıt**:
  ```json
  {
    "status": "success",
    "message": "Çıkış başarılı"
  }
  ```

### 1.5. Şifre Sıfırlama İsteği
- **Endpoint**: `POST /api/auth/password/email`
- **Açıklama**: Şifre sıfırlama e-postası gönderir
- **İstek Parametreleri**:
  ```json
  {
    "email": "string"
  }
  ```
- **Yanıt**:
  ```json
  {
    "status": "success",
    "message": "Şifre sıfırlama bağlantısı e-posta adresinize gönderildi"
  }
  ```

### 1.6. Şifre Sıfırlama
- **Endpoint**: `POST /api/auth/password/reset`
- **Açıklama**: Yeni şifre ile kullanıcı şifresini günceller
- **İstek Parametreleri**:
  ```json
  {
    "email": "string",
    "token": "string",
    "password": "string",
    "password_confirmation": "string"
  }
  ```
- **Yanıt**:
  ```json
  {
    "status": "success",
    "message": "Şifre başarıyla güncellendi"
  }
  ```

### 1.7. Profil Güncelleme
- **Endpoint**: `PUT /api/auth/profile`
- **Açıklama**: Kullanıcı profil bilgilerini günceller
- **Header**: `Authorization: Bearer {token}`
- **İstek Parametreleri**:
  ```json
  {
    "name": "string",
    "phone": "string",
    "address": "string",
    "current_password": "string (if updating password)",
    "password": "string (optional)",
    "password_confirmation": "string (optional)"
  }
  ```
- **Yanıt**:
  ```json
  {
    "status": "success",
    "message": "Profil başarıyla güncellendi",
    "data": {
      "user": {
        "id": "integer",
        "name": "string",
        "email": "string",
        "phone": "string",
        "address": "string",
        "updated_at": "timestamp"
      }
    }
  }
  ```

### 1.8. Email Doğrulama
- **Endpoint**: `GET /api/auth/verify-email/{token}`
- **Açıklama**: Email doğrulama işlemini gerçekleştirir
- **Yanıt**:
  ```json
  {
    "status": "success",
    "message": "E-posta adresiniz başarıyla doğrulandı"
  }
  ```

## 2. Emlak İlanları API

### 2.1. İlanları Listeleme
- **Endpoint**: `GET /api/properties`
- **Açıklama**: Tüm ilanları listeler (filtreleme seçenekleriyle)
- **Sorgu Parametreleri**:
  - `page`: Sayfa numarası (default: 1)
  - `limit`: Sayfa başına kayıt sayısı (default: 10)
  - `sort`: Sıralama alanı (default: created_at)
  - `order`: Sıralama yönü (asc/desc, default: desc)
  - `property_type_id`: Emlak tipi filtresi
  - `status_id`: İlan durumu filtresi (satılık/kiralık)
  - `city`: Şehir filtresi
  - `district`: İlçe filtresi
  - `min_price`: Minimum fiyat
  - `max_price`: Maksimum fiyat
  - `min_area`: Minimum metrekare
  - `max_area`: Maksimum metrekare
  - `rooms`: Oda sayısı
- **Yanıt**:
  ```json
  {
    "status": "success",
    "data": {
      "properties": [
        {
          "id": "integer",
          "title": "string",
          "price": "decimal",
          "property_type_id": "integer",
          "property_type": {
            "id": "integer",
            "name": "string"
          },
          "status_id": "integer",
          "status": {
            "id": "integer",
            "name": "string"
          },
          "address": "string",
          "city": "string",
          "district": "string",
          "area": "integer",
          "rooms": "integer",
          "bathrooms": "integer",
          "thumbnail": "string (URL)",
          "created_at": "timestamp"
        }
      ],
      "pagination": {
        "total": "integer",
        "count": "integer",
        "per_page": "integer",
        "current_page": "integer",
        "total_pages": "integer"
      }
    }
  }
  ```

### 2.2. İlan Detayları
- **Endpoint**: `GET /api/properties/{id}`
- **Açıklama**: Belirli bir ilanın detaylarını getirir
- **Yanıt**:
  ```json
  {
    "status": "success",
    "data": {
      "property": {
        "id": "integer",
        "user_id": "integer",
        "title": "string",
        "description": "string",
        "price": "decimal",
        "property_type_id": "integer",
        "property_type": {
          "id": "integer",
          "name": "string"
        },
        "status_id": "integer",
        "status": {
          "id": "integer",
          "name": "string"
        },
        "address": "string",
        "city": "string",
        "district": "string",
        "neighborhood": "string",
        "latitude": "decimal",
        "longitude": "decimal",
        "area": "integer",
        "rooms": "integer",
        "bathrooms": "integer",
        "floor": "integer",
        "building_age": "integer",
        "heating_type": "string",
        "is_active": "boolean",
        "view_count": "integer",
        "created_at": "timestamp",
        "updated_at": "timestamp",
        "images": [
          {
            "id": "integer",
            "image_path": "string (URL)",
            "is_main": "boolean"
          }
        ],
        "user": {
          "id": "integer",
          "name": "string",
          "email": "string (masked for non-authenticated users)",
          "phone": "string (masked for non-authenticated users)"
        }
      }
    }
  }
  ```

### 2.3. İlan Ekleme
- **Endpoint**: `POST /api/properties`
- **Açıklama**: Yeni emlak ilanı ekler (sadece emlakçı ve admin rolü)
- **Header**: `Authorization: Bearer {token}`
- **İstek Parametreleri**:
  ```json
  {
    "title": "string",
    "description": "string",
    "price": "decimal",
    "property_type_id": "integer",
    "status_id": "integer",
    "address": "string",
    "city": "string",
    "district": "string",
    "neighborhood": "string",
    "latitude": "decimal (optional)",
    "longitude": "decimal (optional)",
    "area": "integer",
    "rooms": "integer (optional)",
    "bathrooms": "integer (optional)",
    "floor": "integer (optional)",
    "building_age": "integer (optional)",
    "heating_type": "string (optional)",
    "is_active": "boolean (default: true)"
  }
  ```
- **Yanıt**:
  ```json
  {
    "status": "success",
    "message": "İlan başarıyla oluşturuldu",
    "data": {
      "property": {
        "id": "integer",
        "title": "string",
        "price": "decimal",
        "created_at": "timestamp"
      }
    }
  }
  ```

### 2.4. İlan Güncelleme
- **Endpoint**: `PUT /api/properties/{id}`
- **Açıklama**: Mevcut ilanı günceller (sadece ilan sahibi ve admin)
- **Header**: `Authorization: Bearer {token}`
- **İstek Parametreleri**: İlan Ekleme ile aynı (tüm alanlar opsiyonel)
- **Yanıt**:
  ```json
  {
    "status": "success",
    "message": "İlan başarıyla güncellendi",
    "data": {
      "property": {
        "id": "integer",
        "title": "string",
        "updated_at": "timestamp"
      }
    }
  }
  ```

### 2.5. İlan Silme
- **Endpoint**: `DELETE /api/properties/{id}`
- **Açıklama**: Bir ilanı siler (sadece ilan sahibi ve admin)
- **Header**: `Authorization: Bearer {token}`
- **Yanıt**:
  ```json
  {
    "status": "success",
    "message": "İlan başarıyla silindi"
  }
  ```

### 2.6. İlan Görsel Ekleme
- **Endpoint**: `POST /api/properties/{id}/images`
- **Açıklama**: İlana görsel ekler
- **Header**: `Authorization: Bearer {token}`
- **İstek Parametreleri**:
  ```json
  {
    "image": "file (multipart/form-data)",
    "is_main": "boolean (optional, default: false)"
  }
  ```
- **Yanıt**:
  ```json
  {
    "status": "success",
    "message": "Görsel başarıyla yüklendi",
    "data": {
      "image": {
        "id": "integer",
        "property_id": "integer",
        "image_path": "string (URL)",
        "is_main": "boolean",
        "created_at": "timestamp"
      }
    }
  }
  ```

### 2.7. İlan Görsel Silme
- **Endpoint**: `DELETE /api/properties/{id}/images/{image_id}`
- **Açıklama**: İlandan görsel siler
- **Header**: `Authorization: Bearer {token}`
- **Yanıt**:
  ```json
  {
    "status": "success",
    "message": "Görsel başarıyla silindi"
  }
  ```

### 2.8. İlanları Arama
- **Endpoint**: `GET /api/properties/search`
- **Açıklama**: Belirtilen arama terimine göre ilanları arar
- **Sorgu Parametreleri**:
  - `q`: Arama terimi
  - `page`: Sayfa numarası
  - `limit`: Sayfa başına kayıt sayısı
  - Diğer tüm filtreleme parametreleri `/api/properties` endpoint'i ile aynıdır
- **Yanıt**: İlanları Listeleme endpoint'i ile aynı yanıtı döndürür

### 2.9. Benzer İlanlar
- **Endpoint**: `GET /api/properties/{id}/similar`
- **Açıklama**: Belirli bir ilana benzer ilanları getirir
- **Sorgu Parametreleri**:
  - `limit`: Kaç adet benzer ilan getirileceği (default: 4)
- **Yanıt**:
  ```json
  {
    "status": "success",
    "data": {
      "similar_properties": [
        {
          "id": "integer",
          "title": "string",
          "price": "decimal",
          "thumbnail": "string (URL)",
          "address": "string",
          "city": "string",
          "district": "string",
          "area": "integer",
          "rooms": "integer",
          "created_at": "timestamp"
        }
      ]
    }
  }
  ```

### 2.10. İlan Görüntülenme Sayacı
- **Endpoint**: `POST /api/properties/{id}/view`
- **Açıklama**: İlan görüntülenme sayısını artırır
- **Yanıt**:
  ```json
  {
    "status": "success",
    "data": {
      "view_count": "integer"
    }
  }
  ```

## 3. Favoriler API

### 3.1. Favori İlanları Listeleme
- **Endpoint**: `GET /api/favorites`
- **Açıklama**: Kullanıcının favori ilanlarını listeler
- **Header**: `Authorization: Bearer {token}`
- **Yanıt**:
  ```json
  {
    "status": "success",
    "data": {
      "favorites": [
        {
          "id": "integer",
          "property_id": "integer",
          "created_at": "timestamp",
          "property": {
            "id": "integer",
            "title": "string",
            "price": "decimal",
            "thumbnail": "string (URL)",
            "address": "string",
            "city": "string"
          }
        }
      ]
    }
  }
  ```

### 3.2. Favoriye Ekleme
- **Endpoint**: `POST /api/favorites`
- **Açıklama**: İlanı favorilere ekler
- **Header**: `Authorization: Bearer {token}`
- **İstek Parametreleri**:
  ```json
  {
    "property_id": "integer"
  }
  ```
- **Yanıt**:
  ```json
  {
    "status": "success",
    "message": "İlan favorilere eklendi",
    "data": {
      "favorite": {
        "id": "integer",
        "property_id": "integer",
        "created_at": "timestamp"
      }
    }
  }
  ```

### 3.3. Favoriden Çıkarma
- **Endpoint**: `DELETE /api/favorites/{property_id}`
- **Açıklama**: İlanı favorilerden çıkarır
- **Header**: `Authorization: Bearer {token}`
- **Yanıt**:
  ```json
  {
    "status": "success",
    "message": "İlan favorilerden çıkarıldı"
  }
  ```

## 4. Emlakçı Olma Talebi API

### 4.1. Talep Oluşturma
- **Endpoint**: `POST /api/role-requests`
- **Açıklama**: Emlakçı olma talebi oluşturur
- **Header**: `Authorization: Bearer {token}`
- **İstek Parametreleri**:
  ```json
  {
    "company_name": "string",
    "company_type": "string",
    "address": "string",
    "tax_office": "string",
    "tax_number": "string",
    "document": "file (multipart/form-data, optional)",
    "note": "string (optional)"
  }
  ```
- **Yanıt**:
  ```json
  {
    "status": "success",
    "message": "Talep başarıyla oluşturuldu",
    "data": {
      "request": {
        "id": "integer",
        "status": "integer (0: Beklemede)",
        "created_at": "timestamp"
      }
    }
  }
  ```

### 4.2. Talep Durumu Görüntüleme
- **Endpoint**: `GET /api/role-requests`
- **Açıklama**: Kullanıcının taleplerini listeler
- **Header**: `Authorization: Bearer {token}`
- **Yanıt**:
  ```json
  {
    "status": "success",
    "data": {
      "requests": [
        {
          "id": "integer",
          "requested_role_id": "integer",
          "status": "integer",
          "note": "string",
          "created_at": "timestamp",
          "updated_at": "timestamp"
        }
      ]
    }
  }
  ```

## 5. Admin API

### 5.1. Kullanıcıları Listeleme
- **Endpoint**: `GET /api/admin/users`
- **Açıklama**: Tüm kullanıcıları listeler (sadece süper admin)
- **Header**: `Authorization: Bearer {token}`
- **Sorgu Parametreleri**:
  - `page`: Sayfa numarası
  - `limit`: Sayfa başına kayıt sayısı
  - `search`: Arama terimi
  - `role_id`: Rol filtresi
- **Yanıt**:
  ```json
  {
    "status": "success",
    "data": {
      "users": [
        {
          "id": "integer",
          "name": "string",
          "email": "string",
          "role_id": "integer",
          "role": {
            "id": "integer",
            "name": "string"
          },
          "status": "integer",
          "created_at": "timestamp"
        }
      ],
      "pagination": {
        "total": "integer",
        "count": "integer",
        "per_page": "integer",
        "current_page": "integer",
        "total_pages": "integer"
      }
    }
  }
  ```

### 5.2. Kullanıcı Detayları
- **Endpoint**: `GET /api/admin/users/{id}`
- **Açıklama**: Belirli bir kullanıcının detaylarını getirir (sadece süper admin)
- **Header**: `Authorization: Bearer {token}`
- **Yanıt**:
  ```json
  {
    "status": "success",
    "data": {
      "user": {
        "id": "integer",
        "name": "string",
        "email": "string",
        "phone": "string",
        "address": "string",
        "role_id": "integer",
        "role": {
          "id": "integer",
          "name": "string"
        },
        "status": "integer",
        "email_verified_at": "timestamp",
        "created_at": "timestamp",
        "updated_at": "timestamp",
        "property_count": "integer"
      }
    }
  }
  ```

### 5.3. Kullanıcı Güncelleme
- **Endpoint**: `PUT /api/admin/users/{id}`
- **Açıklama**: Kullanıcı bilgilerini günceller (sadece süper admin)
- **Header**: `Authorization: Bearer {token}`
- **İstek Parametreleri**:
  ```json
  {
    "name": "string (optional)",
    "email": "string (optional)",
    "phone": "string (optional)",
    "address": "string (optional)",
    "role_id": "integer (optional)",
    "status": "integer (optional)"
  }
  ```
- **Yanıt**:
  ```json
  {
    "status": "success",
    "message": "Kullanıcı başarıyla güncellendi",
    "data": {
      "user": {
        "id": "integer",
        "name": "string",
        "email": "string",
        "role_id": "integer",
        "updated_at": "timestamp"
      }
    }
  }
  ```

### 5.4. Kullanıcı Silme
- **Endpoint**: `DELETE /api/admin/users/{id}`
- **Açıklama**: Kullanıcıyı siler (sadece süper admin)
- **Header**: `Authorization: Bearer {token}`
- **Yanıt**:
  ```json
  {
    "status": "success",
    "message": "Kullanıcı başarıyla silindi"
  }
  ```

### 5.5. Rol Taleplerini Listeleme
- **Endpoint**: `GET /api/admin/role-requests`
- **Açıklama**: Tüm rol taleplerini listeler (sadece süper admin)
- **Header**: `Authorization: Bearer {token}`
- **Sorgu Parametreleri**:
  - `status`: Durum filtresi (0: Beklemede, 1: Onaylandı, 2: Reddedildi)
- **Yanıt**:
  ```json
  {
    "status": "success",
    "data": {
      "requests": [
        {
          "id": "integer",
          "user_id": "integer",
          "requested_role_id": "integer",
          "status": "integer",
          "note": "string",
          "created_at": "timestamp",
          "user": {
            "id": "integer",
            "name": "string",
            "email": "string"
          }
        }
      ]
    }
  }
  ```

### 5.6. Rol Talebi Güncelleme
- **Endpoint**: `PUT /api/admin/role-requests/{id}`
- **Açıklama**: Rol talebini günceller (onay/red) (sadece süper admin)
- **Header**: `Authorization: Bearer {token}`
- **İstek Parametreleri**:
  ```json
  {
    "status": "integer (1: Onay, 2: Red)",
    "note": "string (optional)"
  }
  ```
- **Yanıt**:
  ```json
  {
    "status": "success",
    "message": "Rol talebi başarıyla güncellendi",
    "data": {
      "request": {
        "id": "integer",
        "status": "integer",
        "updated_at": "timestamp"
      }
    }
  }
  ```

## 6. Genel API

### 6.1. Emlak Tipleri
- **Endpoint**: `GET /api/property-types`
- **Açıklama**: Tüm emlak tiplerini listeler
- **Yanıt**:
  ```json
  {
    "status": "success",
    "data": {
      "property_types": [
        {
          "id": "integer",
          "name": "string"
        }
      ]
    }
  }
  ```

### 6.2. İlan Durumları
- **Endpoint**: `GET /api/property-status`
- **Açıklama**: Tüm ilan durumlarını listeler (satılık, kiralık)
- **Yanıt**:
  ```json
  {
    "status": "success",
    "data": {
      "property_status": [
        {
          "id": "integer",
          "name": "string"
        }
      ]
    }
  }
  ```

### 6.3. Şehirler
- **Endpoint**: `GET /api/cities`
- **Açıklama**: Tüm şehirleri listeler
- **Yanıt**:
  ```json
  {
    "status": "success",
    "data": {
      "cities": [
        {
          "id": "integer",
          "name": "string"
        }
      ]
    }
  }
  ```

### 6.4. İlçeler
- **Endpoint**: `GET /api/districts/{city_id}`
- **Açıklama**: Belirli bir şehre ait ilçeleri listeler
- **Yanıt**:
  ```json
  {
    "status": "success",
    "data": {
      "districts": [
        {
          "id": "integer",
          "city_id": "integer",
          "name": "string"
        }
      ]
    }
  }
  ```

### 6.5. Mahalleler
- **Endpoint**: `GET /api/neighborhoods/{district_id}`
- **Açıklama**: Belirli bir ilçeye ait mahalleleri listeler
- **Yanıt**:
  ```json
  {
    "status": "success",
    "data": {
      "neighborhoods": [
        {
          "id": "integer",
          "district_id": "integer",
          "name": "string"
        }
      ]
    }
  }
  ```

### 6.6. İstatistikler
- **Endpoint**: `GET /api/stats`
- **Açıklama**: Sistem genel istatistiklerini getirir
- **Yanıt**:
  ```json
  {
    "status": "success",
    "data": {
      "total_properties": "integer",
      "total_users": "integer",
      "active_properties": "integer",
      "most_viewed_properties": [
        {
          "id": "integer",
          "title": "string",
          "view_count": "integer"
        }
      ]
    }
  }
  ```

### 6.7. İletişim Formu
- **Endpoint**: `POST /api/contact`
- **Açıklama**: İletişim formu gönderimi
- **İstek Parametreleri**:
  ```json
  {
    "name": "string",
    "email": "string",
    "subject": "string",
    "message": "string"
  }
  ```
- **Yanıt**:
  ```json
  {
    "status": "success",
    "message": "Mesajınız başarıyla gönderildi"
  }
  ```

## 7. Bildirimler API

### 7.1. Bildirimleri Listeleme
- **Endpoint**: `GET /api/notifications`
- **Açıklama**: Kullanıcının bildirimlerini listeler
- **Header**: `Authorization: Bearer {token}`
- **Sorgu Parametreleri**:
  - `page`: Sayfa numarası
  - `limit`: Sayfa başına kayıt sayısı
  - `read`: Okunmuş/okunmamış filtresi (true/false)
- **Yanıt**:
  ```json
  {
    "status": "success",
    "data": {
      "notifications": [
        {
          "id": "string",
          "type": "string",
          "data": "object",
          "read_at": "timestamp or null",
          "created_at": "timestamp"
        }
      ],
      "pagination": {
        "total": "integer",
        "count": "integer",
        "per_page": "integer",
        "current_page": "integer",
        "total_pages": "integer"
      }
    }
  }
  ```

### 7.2. Bildirimi Okundu Olarak İşaretleme
- **Endpoint**: `PATCH /api/notifications/{id}`
- **Açıklama**: Belirli bir bildirimi okundu olarak işaretler
- **Header**: `Authorization: Bearer {token}`
- **Yanıt**:
  ```json
  {
    "status": "success",
    "message": "Bildirim okundu olarak işaretlendi"
  }
  ```

### 7.3. Tüm Bildirimleri Okundu Olarak İşaretleme
- **Endpoint**: `PATCH /api/notifications`
- **Açıklama**: Tüm bildirimleri okundu olarak işaretler
- **Header**: `Authorization: Bearer {token}`
- **Yanıt**:
  ```json
  {
    "status": "success",
    "message": "Tüm bildirimler okundu olarak işaretlendi"
  }
  ``` 