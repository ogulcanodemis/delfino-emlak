<?php
/**
 * Location Controller
 * Emlak-Delfino Projesi
 * Şehir, İlçe ve Mahalle verilerini yönetir
 */

require_once '../models/City.php';
require_once '../models/District.php';
require_once '../models/Neighborhood.php';

class LocationController {
    private $db;
    private $city;
    private $district;
    private $neighborhood;

    public function __construct($database) {
        $this->db = $database;
        $this->city = new City($this->db);
        $this->district = new District($this->db);
        $this->neighborhood = new Neighborhood($this->db);
    }

    /**
     * Tüm şehirleri listele
     * GET /api/cities
     */
    public function getCities() {
        try {
            $search = $_GET['search'] ?? null;
            $with_property_count = $_GET['with_property_count'] ?? false;
            
            if ($search) {
                $cities = $this->city->search($search);
            } else {
                $cities = $this->city->getAll();
            }

            // İlan sayısını da getir
            if ($with_property_count) {
                foreach ($cities as &$city) {
                    $city['property_count'] = $this->city->getPropertyCount($city['id']);
                }
            }

            Response::success([
                'cities' => $cities,
                'total' => count($cities)
            ], 'Şehirler başarıyla getirildi');

        } catch (Exception $e) {
            Response::error('Şehirler getirilemedi: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Şehir detaylarını getir
     * GET /api/cities/{id}
     */
    public function getCity($id) {
        try {
            $city = $this->city->getById($id);
            
            if (!$city) {
                Response::error('Şehir bulunamadı', 404);
            }

            // İlan sayısını ekle
            $city['property_count'] = $this->city->getPropertyCount($id);
            $city['district_count'] = $this->district->getCityDistrictCount($id);

            Response::success([
                'city' => $city
            ], 'Şehir detayları getirildi');

        } catch (Exception $e) {
            Response::error('Şehir detayları getirilemedi: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Şehre göre ilçeleri listele
     * GET /api/districts/{city_id}
     */
    public function getDistrictsByCity($city_id) {
        try {
            // Şehrin var olup olmadığını kontrol et
            if (!$this->city->exists($city_id)) {
                Response::error('Şehir bulunamadı', 404);
            }

            $search = $_GET['search'] ?? null;
            $with_property_count = $_GET['with_property_count'] ?? false;
            
            if ($search) {
                $districts = $this->district->search($search, $city_id);
            } else {
                $districts = $this->district->getByCity($city_id);
            }

            // İlan sayısını da getir
            if ($with_property_count) {
                foreach ($districts as &$district) {
                    $district['property_count'] = $this->district->getPropertyCount($district['id']);
                }
            }

            Response::success([
                'districts' => $districts,
                'total' => count($districts),
                'city_id' => (int)$city_id
            ], 'İlçeler başarıyla getirildi');

        } catch (Exception $e) {
            Response::error('İlçeler getirilemedi: ' . $e->getMessage(), 500);
        }
    }

    /**
     * İlçe detaylarını getir
     * GET /api/districts/detail/{id}
     */
    public function getDistrict($id) {
        try {
            $district = $this->district->getById($id);
            
            if (!$district) {
                Response::error('İlçe bulunamadı', 404);
            }

            // İlan sayısını ekle
            $district['property_count'] = $this->district->getPropertyCount($id);
            $district['neighborhood_count'] = $this->neighborhood->getDistrictNeighborhoodCount($id);

            Response::success([
                'district' => $district
            ], 'İlçe detayları getirildi');

        } catch (Exception $e) {
            Response::error('İlçe detayları getirilemedi: ' . $e->getMessage(), 500);
        }
    }

    /**
     * İlçeye göre mahalleleri listele
     * GET /api/neighborhoods/{district_id}
     */
    public function getNeighborhoodsByDistrict($district_id) {
        try {
            // İlçenin var olup olmadığını kontrol et
            if (!$this->district->exists($district_id)) {
                Response::error('İlçe bulunamadı', 404);
            }

            $search = $_GET['search'] ?? null;
            $with_property_count = $_GET['with_property_count'] ?? false;
            
            if ($search) {
                $neighborhoods = $this->neighborhood->search($search, $district_id);
            } else {
                $neighborhoods = $this->neighborhood->getByDistrict($district_id);
            }

            // İlan sayısını da getir
            if ($with_property_count) {
                foreach ($neighborhoods as &$neighborhood) {
                    $neighborhood['property_count'] = $this->neighborhood->getPropertyCount($neighborhood['id']);
                }
            }

            Response::success([
                'neighborhoods' => $neighborhoods,
                'total' => count($neighborhoods),
                'district_id' => (int)$district_id
            ], 'Mahalleler başarıyla getirildi');

        } catch (Exception $e) {
            Response::error('Mahalleler getirilemedi: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Mahalle detaylarını getir
     * GET /api/neighborhoods/detail/{id}
     */
    public function getNeighborhood($id) {
        try {
            $neighborhood = $this->neighborhood->getById($id);
            
            if (!$neighborhood) {
                Response::error('Mahalle bulunamadı', 404);
            }

            // İlan sayısını ekle
            $neighborhood['property_count'] = $this->neighborhood->getPropertyCount($id);

            Response::success([
                'neighborhood' => $neighborhood
            ], 'Mahalle detayları getirildi');

        } catch (Exception $e) {
            Response::error('Mahalle detayları getirilemedi: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Şehre göre mahalleleri listele
     * GET /api/neighborhoods/by-city/{city_id}
     */
    public function getNeighborhoodsByCity($city_id) {
        try {
            // Şehrin var olup olmadığını kontrol et
            if (!$this->city->exists($city_id)) {
                Response::error('Şehir bulunamadı', 404);
            }

            $search = $_GET['search'] ?? null;
            
            if ($search) {
                $neighborhoods = $this->neighborhood->search($search, null, $city_id);
            } else {
                $neighborhoods = $this->neighborhood->getByCity($city_id);
            }

            Response::success([
                'neighborhoods' => $neighborhoods,
                'total' => count($neighborhoods),
                'city_id' => (int)$city_id
            ], 'Mahalleler başarıyla getirildi');

        } catch (Exception $e) {
            Response::error('Mahalleler getirilemedi: ' . $e->getMessage(), 500);
        }
    }

    /**
     * En çok ilan olan şehirleri getir
     * GET /api/cities/top
     */
    public function getTopCities() {
        try {
            $limit = $_GET['limit'] ?? 10;
            $cities = $this->city->getTopCitiesByPropertyCount($limit);

            Response::success([
                'top_cities' => $cities,
                'total' => count($cities)
            ], 'En çok ilan olan şehirler getirildi');

        } catch (Exception $e) {
            Response::error('En çok ilan olan şehirler getirilemedi: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Şehirdeki en çok ilan olan ilçeleri getir
     * GET /api/districts/top/{city_id}
     */
    public function getTopDistrictsByCity($city_id) {
        try {
            // Şehrin var olup olmadığını kontrol et
            if (!$this->city->exists($city_id)) {
                Response::error('Şehir bulunamadı', 404);
            }

            $limit = $_GET['limit'] ?? 10;
            $districts = $this->district->getTopDistrictsByPropertyCount($city_id, $limit);

            Response::success([
                'top_districts' => $districts,
                'total' => count($districts),
                'city_id' => (int)$city_id
            ], 'En çok ilan olan ilçeler getirildi');

        } catch (Exception $e) {
            Response::error('En çok ilan olan ilçeler getirilemedi: ' . $e->getMessage(), 500);
        }
    }

    /**
     * İlçedeki en çok ilan olan mahalleleri getir
     * GET /api/neighborhoods/top/{district_id}
     */
    public function getTopNeighborhoodsByDistrict($district_id) {
        try {
            // İlçenin var olup olmadığını kontrol et
            if (!$this->district->exists($district_id)) {
                Response::error('İlçe bulunamadı', 404);
            }

            $limit = $_GET['limit'] ?? 10;
            $neighborhoods = $this->neighborhood->getTopNeighborhoodsByPropertyCount($district_id, $limit);

            Response::success([
                'top_neighborhoods' => $neighborhoods,
                'total' => count($neighborhoods),
                'district_id' => (int)$district_id
            ], 'En çok ilan olan mahalleler getirildi');

        } catch (Exception $e) {
            Response::error('En çok ilan olan mahalleler getirilemedi: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Konum hiyerarşisini getir (şehir > ilçe > mahalle)
     * GET /api/locations/hierarchy
     */
    public function getLocationHierarchy() {
        try {
            $city_id = $_GET['city_id'] ?? null;
            $district_id = $_GET['district_id'] ?? null;
            
            $result = [];

            if ($city_id) {
                $result['city'] = $this->city->getById($city_id);
                $result['districts'] = $this->district->getByCity($city_id);
                
                if ($district_id) {
                    $result['district'] = $this->district->getById($district_id);
                    $result['neighborhoods'] = $this->neighborhood->getByDistrict($district_id);
                }
            } else {
                $result['cities'] = $this->city->getAll();
            }

            Response::success($result, 'Konum hiyerarşisi getirildi');

        } catch (Exception $e) {
            Response::error('Konum hiyerarşisi getirilemedi: ' . $e->getMessage(), 500);
        }
    }
}
?> 