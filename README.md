# Emlak-Delfino

Emlak-Delfino, emlak ilanlarının listelenmesi ve yönetilmesi için geliştirilmiş bir web platformudur. Ziyaretçilerin ilanları görüntüleyebildiği, kayıtlı kullanıcıların detaylı bilgilere erişebildiği ve süper admin tarafından yetkilendirilen kullanıcıların ilan ekleyebildiği bir yapıya sahiptir.

## Proje Yapısı

- `/frontend` - React tabanlı frontend uygulaması
- `/backend` - PHP tabanlı API ve backend uygulaması
- `/is_analizi.md` - Detaylı iş analizi dokümanı

## Kullanıcı Rolleri

1. **Ziyaretçi**: İlanları görüntüleyebilir (fiyat ve emlakçı iletişim bilgileri hariç)
2. **Kayıtlı Kullanıcı**: İlanları detaylı görüntüleyebilir, favori ekleyebilir
3. **Emlakçı**: İlan ekleyebilir, düzenleyebilir ve silebilir
4. **Süper Admin**: Sistem yönetimini sağlar, kullanıcı rollerini yönetir

## Kurulum

### Gereksinimler

- Node.js v14+
- PHP 8.x
- MySQL 8.x
- Composer

### Frontend Kurulumu

```bash
cd frontend
npm install
npm start
```

### Backend Kurulumu

```bash
cd backend
# Veritabanı bağlantı ayarlarını yapılandırın
# config/database.php dosyasını düzenleyin
```

## Veritabanı Kurulumu

1. MySQL veritabanı oluşturun: `emlak_delfino`
2. Veritabanı şema dosyalarını içe aktarın (ileride eklenecek)

## Geliştirme

- Frontend geliştirmesi için: `cd frontend && npm start`
- Backend API'leri için PHP ve MySQL kullanılmaktadır

## İletişim

Daha fazla bilgi için lütfen iletişime geçin. 