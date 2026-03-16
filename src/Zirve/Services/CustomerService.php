<?php

namespace ZirveDonusum\Zirve\Services;

/**
 * Musteri (customer) islemleri servisi.
 * Musteri CRUD, sozlesme, banka, fiyat listesi, notlar ve genel tanimlar.
 */
class CustomerService extends BaseService
{
    // ─── Musteri CRUD ────────────────────────────────────────────────

    /**
     * Yeni musteri olusturur.
     *
     * @param array $data Musteri verileri
     * @return array Olusturulan musteri bilgisi
     */
    public function create(array $data): array
    {
        return $this->http->post('/customer/createCustomer', $data);
    }

    /**
     * VKN veya TCKN ile musteri arar.
     *
     * @param string $vknTckn Vergi kimlik no veya TC kimlik no
     * @return array Musteri bilgisi
     */
    public function findByVknTckn(string $vknTckn): array
    {
        return $this->http->get('/customer/findByVknTckn', ['vknTckn' => $vknTckn]);
    }

    /**
     * Kayit ID ile musteri getirir.
     *
     * @param int $id Kayit ID
     * @return array Musteri bilgisi
     */
    public function findByRecordId(int $id): array
    {
        return $this->http->get("/customer/findByRecordId/{$id}");
    }

    /**
     * Musteri detayini getirir.
     *
     * @param int $id Musteri ID
     * @return array Musteri detay bilgisi
     */
    public function get(int $id): array
    {
        return $this->http->get("/customer/get/{$id}");
    }

    /**
     * Musteri duzenleme bilgilerini getirir.
     *
     * @param int $id Musteri ID
     * @return array Duzenleme formu verileri
     */
    public function edit(int $id): array
    {
        return $this->http->get("/customer/edit/{$id}");
    }

    /**
     * Musteri CP duzenleme bilgilerini getirir.
     *
     * @param int $id Musteri ID
     * @return array CP duzenleme formu verileri
     */
    public function cpEdit(int $id): array
    {
        return $this->http->get("/customer/cpEdit/{$id}");
    }

    /**
     * VKN/TCKN ile musteriyi alt kayitlariyla birlikte getirir.
     *
     * @param string $vknTckn Vergi kimlik no veya TC kimlik no
     * @return array Musteri ve alt kayitlari
     */
    public function findWithChild(string $vknTckn): array
    {
        return $this->http->get('/customer/findCustomerWithChild', [
            'page'    => 0,
            'size'    => 50,
            'sort'    => 'recordId,desc',
            'vknTckn' => $vknTckn,
        ]);
    }

    /**
     * Musterileri native sorgu ile listeler.
     *
     * @param array $query Sorgu parametreleri (filtreleme, sayfalama vb.)
     * @return array Musteri listesi
     */
    public function listNative(array $query = []): array
    {
        return $this->http->get('/customer/listCustomersNative', $query);
    }

    // ─── Genel Tanimlar ─────────────────────────────────────────────

    /**
     * Sehir listesini getirir.
     *
     * @return array Sehir listesi
     */
    public function getCities(): array
    {
        return $this->http->get('/customer/getCities');
    }

    /**
     * Vergi dairesi listesini getirir.
     *
     * @return array Vergi dairesi listesi
     */
    public function getTaxOffices(): array
    {
        return $this->http->get('/customer/getTaxOffices');
    }

    /**
     * Alt bolum listesini getirir.
     *
     * @return array Alt bolum listesi
     */
    public function getSubdivisions(): array
    {
        return $this->http->get('/customer/getSubdivisions');
    }

    /**
     * NACE kodlarini getirir.
     *
     * @return array NACE kod listesi
     */
    public function naceCodes(): array
    {
        return $this->http->get('/customer/naceCodes');
    }

    /**
     * Distributor listesini getirir.
     *
     * @return array Distributor listesi
     */
    public function getDistributors(): array
    {
        return $this->http->get('/customer/getDistributors');
    }

    /**
     * Alt distributor listesini getirir.
     *
     * @return array Alt distributor listesi
     */
    public function getSubDistributors(): array
    {
        return $this->http->get('/customer/getSubDistributors');
    }

    // ─── Hizmet & Servis ────────────────────────────────────────────

    /**
     * Musteri hizmet listesini getirir.
     *
     * @return array Hizmet listesi
     */
    public function getServiceList(): array
    {
        return $this->http->get('/customer/getCustomerServiceList');
    }

    /**
     * Servisleri getirir.
     *
     * @return array Servis listesi
     */
    public function services(): array
    {
        return $this->http->get('/customer/services');
    }

    /**
     * Servis durumunu sorgular.
     *
     * @return array Servis durum bilgisi
     */
    public function fetchServiceState(): array
    {
        return $this->http->get('/customer/fetchServiceState');
    }

    /**
     * Sozlesmenin aktif olup olmadigini kontrol eder.
     *
     * @return array Aktiflik durumu
     */
    public function isContractActive(): array
    {
        return $this->http->get('/customer/isContractActive');
    }

    // ─── KDV & Varsayilan Degerler ──────────────────────────────────

    /**
     * Varsayilan KDV oranini getirir.
     *
     * @return array KDV bilgisi
     */
    public function getDefaultKdv(): array
    {
        return $this->http->get('/customer/getDefaultKdv');
    }

    /**
     * Varsayilan KDV oranini degistirir.
     *
     * @param array $data KDV verileri
     * @return array Guncelleme sonucu
     */
    public function changeDefaultKdv(array $data): array
    {
        return $this->http->post('/customer/changeDefaultKdv', $data);
    }

    /**
     * Varsayilan degerleri getirir.
     *
     * @return array Varsayilan degerler
     */
    public function getDefaultValues(): array
    {
        return $this->http->get('/customer/getDefaultValues');
    }

    // ─── VKN/TCKN Degisikligi ───────────────────────────────────────

    /**
     * VKN/TCKN degisikligi yapar (admin).
     *
     * @param array $data Degisiklik verileri
     * @return array Islem sonucu
     */
    public function changeVknTckn(array $data): array
    {
        return $this->http->post('/customer/changeVkntckn', $data);
    }

    /**
     * Musteri tarafli VKN/TCKN degisikligi yapar.
     *
     * @param array $data Degisiklik verileri
     * @return array Islem sonucu
     */
    public function customerChangeVknTckn(array $data): array
    {
        return $this->http->post('/customer/customerChangeVknTckn', $data);
    }

    // ─── Fiyat Listesi ──────────────────────────────────────────────

    /**
     * Fiyat listesini getirir.
     *
     * @return array Fiyat listesi
     */
    public function fetchPriceList(): array
    {
        return $this->http->get('/customer/fetchPriceList');
    }

    /**
     * Belirli bir fiyat listesini ID ile getirir.
     *
     * @param int $id Fiyat listesi ID
     * @return array Fiyat listesi detayi
     */
    public function findPriceList(int $id): array
    {
        return $this->http->get("/customer/findPriceList/{$id}");
    }

    /**
     * Fiyat listesi kaydeder.
     *
     * @param array $data Fiyat listesi verileri
     * @return array Kayit sonucu
     */
    public function savePriceList(array $data): array
    {
        return $this->http->post('/customer/savePriceList', $data);
    }

    /**
     * Fiyat tutar eslemelerini getirir.
     *
     * @return array Fiyat tutar eslemeleri
     */
    public function fetchPriceAmountMaps(): array
    {
        return $this->http->get('/customer/fetchPriceAmountMaps');
    }

    // ─── Tanimli Fatura Turleri ─────────────────────────────────────

    /**
     * Onceden tanimli fatura turlerini getirir.
     *
     * @return array Fatura tur listesi
     */
    public function fetchPredefinedInvoiceTypes(): array
    {
        return $this->http->get('/customer/fetchPredefinedInvoiceTypes');
    }

    /**
     * Onceden tanimli fatura turlerini kaydeder.
     *
     * @param array $data Fatura tur verileri
     * @return array Kayit sonucu
     */
    public function savePredefinedInvoiceTypes(array $data): array
    {
        return $this->http->post('/customer/savePredefinedInvoiceTypes', $data);
    }

    // ─── Sozlesme ───────────────────────────────────────────────────

    /**
     * Musterinin sozlesmelerini getirir.
     *
     * @param int $id Musteri ID
     * @return array Sozlesme listesi
     */
    public function getContracts(int $id): array
    {
        return $this->http->get("/customer/customerContracts/{$id}");
    }

    /**
     * Sozlesme detayini getirir.
     *
     * @param array $query Sorgu parametreleri
     * @return array Sozlesme bilgisi
     */
    public function getContract(array $query): array
    {
        return $this->http->get('/customer/getContract', $query);
    }

    /**
     * Sozlesmeyi aktif eder.
     *
     * @param array $data Aktivasyon verileri
     * @return array Islem sonucu
     */
    public function activateContract(array $data): array
    {
        return $this->http->post('/customer/activateContract', $data);
    }

    /**
     * Sozlesmeyi iptal eder.
     *
     * @param array $data Iptal verileri
     * @return array Islem sonucu
     */
    public function cancelContract(array $data): array
    {
        return $this->http->post('/customer/cancelContract', $data);
    }

    /**
     * Sozlesmeyi yeniler.
     *
     * @param array $data Yenileme verileri
     * @return array Islem sonucu
     */
    public function renewContract(array $data): array
    {
        return $this->http->post('/customer/renewContract', $data);
    }

    /**
     * Sozlesmeyi siler.
     *
     * @param int $id Sozlesme ID
     * @return array Silme sonucu
     */
    public function deleteContract(int $id): array
    {
        return $this->http->post("/customer/deleteContract/{$id}", []);
    }

    /**
     * Sozlesme silme uygunlugunu kontrol eder.
     *
     * @param int $id Sozlesme ID
     * @return array Kontrol sonucu
     */
    public function checkDeleteContract(int $id): array
    {
        return $this->http->post("/customer/checkDeleteContract/{$id}", []);
    }

    // ─── Banka ──────────────────────────────────────────────────────

    /**
     * Musteri banka bilgilerini getirir.
     *
     * @return array Banka listesi
     */
    public function getBanks(): array
    {
        return $this->http->get('/customer/getCustomerBankByCustomerId');
    }

    /**
     * Musteri banka bilgisi kaydeder.
     *
     * @param array $data Banka verileri
     * @return array Kayit sonucu
     */
    public function saveBank(array $data): array
    {
        return $this->http->post('/customer/saveCustomerBank', $data);
    }

    /**
     * Musteri banka bilgisini siler.
     *
     * @param array $data Silinecek banka verileri
     * @return array Silme sonucu
     */
    public function deleteBank(array $data): array
    {
        return $this->http->post('/customer/deleteCustomerBank', $data);
    }

    /**
     * Banka ozelliklerini getirir.
     *
     * @return array Banka ozellikleri
     */
    public function getBankProperties(): array
    {
        return $this->http->get('/customer/getCustomerBankProperties');
    }

    // ─── Kullanici Tanimlari & Bildirim ─────────────────────────────

    /**
     * Belge turune gore kullanici tanimini getirir.
     *
     * @param string $docType Belge turu
     * @return array Kullanici tanim bilgisi
     */
    public function getUserDefinition(string $docType): array
    {
        return $this->http->get('/customer/getUserDefinition', ['docType' => $docType]);
    }

    /**
     * Kullanici tanimini kaydeder.
     *
     * @param array $data Tanim verileri
     * @return array Kayit sonucu
     */
    public function saveUserDefinition(array $data): array
    {
        return $this->http->post('/customer/saveUserDefinition', $data);
    }

    /**
     * Bilgilendirme maili kaydeder.
     *
     * @param array $data Mail verileri
     * @return array Kayit sonucu
     */
    public function saveInformationMail(array $data): array
    {
        return $this->http->post('/customer/saveInformationMail', $data);
    }

    // ─── Telefon & Iletisim ─────────────────────────────────────────

    /**
     * Cep telefonu bilgisini kontrol eder.
     *
     * @return array Kontrol sonucu
     */
    public function checkCepTel(): array
    {
        return $this->http->get('/customer/checkCepTel');
    }

    /**
     * Sorumlu cep telefonu degisikligini kaydeder.
     *
     * @param array $data Telefon degisiklik verileri
     * @return array Kayit sonucu
     */
    public function saveChangedSorumluCepTel(array $data): array
    {
        return $this->http->post('/customer/saveChangedSorumluCepTel', $data);
    }

    // ─── Aktivite & Kredi ───────────────────────────────────────────

    /**
     * Aktivite kredi bilgisini getirir.
     *
     * @return array Aktivite kredi bilgisi
     */
    public function getActivityCredit(): array
    {
        return $this->http->get('/customer/getActivityCredit');
    }

    /**
     * Yeni fatura olup olmadigini kontrol eder.
     *
     * @return array Kontrol sonucu
     */
    public function checkNewInvoices(): array
    {
        return $this->http->get('/customer/checkNewInvoices');
    }

    // ─── Musteri Notlari ────────────────────────────────────────────

    /**
     * Musteriye not ekler.
     *
     * @param array $data Not verileri
     * @return array Kayit sonucu
     */
    public function addNote(array $data): array
    {
        return $this->http->post('/customer/addCustomerNote', $data);
    }

    /**
     * Musteri notlarini sayfalanmis olarak getirir.
     *
     * @param int $page Sayfa numarasi (0 tabanli)
     * @param int $size Sayfa basina kayit sayisi
     * @return array Not listesi
     */
    public function fetchNotes(int $page = 0, int $size = 10): array
    {
        return $this->http->get('/customer/fetchCustomerNotes', [
            'page' => $page,
            'size' => $size,
        ]);
    }

    /**
     * Musteri notunu siler.
     *
     * @param array $data Silinecek not verileri
     * @return array Silme sonucu
     */
    public function deleteNote(array $data): array
    {
        return $this->http->post('/customer/deleteCustomerNote', $data);
    }
}
