<?php

namespace ZirveDonusum\Zirve\Services;

/**
 * Harici musteri (cari) islemleri servisi.
 * Dis musteri CRUD, adres, banka, PK listesi ve toplu islemler.
 */
class ExternalCustomerService extends BaseService
{
    // ─── Listeleme & Sorgulama ──────────────────────────────────────

    /**
     * Harici musterileri sayfalanmis olarak listeler.
     *
     * @param int $page Sayfa numarasi (0 tabanli)
     * @param int $size Sayfa basina kayit sayisi
     * @return array Musteri listesi
     */
    public function list(int $page = 0, int $size = 10): array
    {
        return $this->http->get('/externalCustomer/fetch', [
            'page' => $page,
            'size' => $size,
        ]);
    }

    /**
     * Tum harici musterileri getirir.
     *
     * @return array Tam musteri listesi
     */
    public function listAll(): array
    {
        return $this->http->get('/externalCustomer/fetchAllExternalCustomer');
    }

    /**
     * Harici musteri duzenleme bilgilerini getirir.
     *
     * @param int $id Musteri ID
     * @return array Duzenleme formu verileri
     */
    public function edit(int $id): array
    {
        return $this->http->get("/externalCustomer/edit/{$id}");
    }

    /**
     * Harici musteri listesini getirir.
     *
     * @return array Musteri listesi
     */
    public function externalCustomers(): array
    {
        return $this->http->get('/externalCustomer/externalCustomers');
    }

    /**
     * VKN veya TCKN ile harici musteri arar.
     *
     * @param string $vknTckn Vergi kimlik no veya TC kimlik no
     * @return array Musteri bilgisi
     */
    public function findByVknTckn(string $vknTckn): array
    {
        return $this->http->get('/externalCustomer/findByVknTckn', ['vknTckn' => $vknTckn]);
    }

    /**
     * VKN/TCKN ile harici musterinin e-posta adresini getirir.
     *
     * @param string $vknTckn Vergi kimlik no veya TC kimlik no
     * @return array E-posta bilgisi
     */
    public function getEmail(string $vknTckn): array
    {
        return $this->http->get('/externalCustomer/get-email', ['vknTckn' => $vknTckn]);
    }

    /**
     * Harici musteri kodlarini getirir.
     *
     * @return array Kod listesi
     */
    public function getCodes(): array
    {
        return $this->http->get('/externalCustomer/getCodes');
    }

    /**
     * GIB fatura kullanicisini identifier ile sorgular.
     *
     * @param string $identifier Kullanici tanimlayicisi (VKN/TCKN)
     * @return array GIB kullanici bilgisi
     */
    public function gibInvoiceUser(string $identifier): array
    {
        return $this->http->get("/externalCustomer/gib-invoice-user/{$identifier}");
    }

    // ─── Olusturma & Kaydetme ───────────────────────────────────────

    /**
     * Yeni harici musteri olusturur.
     *
     * @param array $data Musteri verileri
     * @return array Olusturulan musteri bilgisi
     */
    public function create(array $data): array
    {
        return $this->http->post('/externalCustomer/create', $data);
    }

    /**
     * Birden fazla harici musteriyi toplu kaydeder.
     *
     * @param array $data Musteri verileri dizisi
     * @return array Kayit sonucu
     */
    public function saveMultiple(array $data): array
    {
        return $this->http->post('/externalCustomer/saveMultiple', $data);
    }

    /**
     * Excel dosyasindan harici musteri aktarir.
     *
     * @param array $data Excel verileri
     * @return array Aktarim sonucu
     */
    public function saveFromExcel(array $data): array
    {
        return $this->http->post('/externalCustomer/saveFromExcel', $data);
    }

    // ─── Adres ──────────────────────────────────────────────────────

    /**
     * Harici musteri adresini kaydeder.
     *
     * @param array $data Adres verileri
     * @return array Kayit sonucu
     */
    public function saveAddress(array $data): array
    {
        return $this->http->post('/externalCustomer/saveExternalCustomerAddress', $data);
    }

    /**
     * Harici musteri adresini siler.
     *
     * @param int $id Adres ID
     * @return array Silme sonucu
     */
    public function deleteAddress(int $id): array
    {
        return $this->http->post("/externalCustomer/deleteAddress/{$id}", []);
    }

    // ─── Banka ──────────────────────────────────────────────────────

    /**
     * Harici musteriye yeni banka ekler.
     *
     * @param array $data Banka verileri
     * @return array Kayit sonucu
     */
    public function addBank(array $data): array
    {
        return $this->http->post('/externalCustomer/addNewBank', $data);
    }

    /**
     * Harici musteri bankasini ID ile siler.
     *
     * @param int $id Banka ID
     * @return array Silme sonucu
     */
    public function deleteBank(int $id): array
    {
        return $this->http->post("/externalCustomer/deleteBank/{$id}", []);
    }

    /**
     * Harici musteri bankasini kayit ID ile siler.
     *
     * @param array $data Kayit ID verileri
     * @return array Silme sonucu
     */
    public function deleteBankByRecordId(array $data): array
    {
        return $this->http->post('/externalCustomer/deleteBankByRecordId', $data);
    }

    // ─── Silme ──────────────────────────────────────────────────────

    /**
     * Harici musteriyi siler.
     *
     * @param int $id Musteri ID
     * @return array Silme sonucu
     */
    public function delete(int $id): array
    {
        return $this->http->post("/externalCustomer/delete/{$id}", []);
    }

    /**
     * Birden fazla harici musteriyi toplu siler.
     *
     * @param array $data Silinecek musteri ID'leri
     * @return array Silme sonucu
     */
    public function deleteMultiple(array $data): array
    {
        return $this->http->post('/externalCustomer/deleteMultiple', $data);
    }

    // ─── PK Listesi ─────────────────────────────────────────────────

    /**
     * Tum PK listesini getirir.
     *
     * @return array PK listesi
     */
    public function fetchAllPkList(): array
    {
        return $this->http->get('/externalCustomer/fetchAllPkList');
    }

    /**
     * PK listesini getirir.
     *
     * @return array PK listesi
     */
    public function fetchPkList(): array
    {
        return $this->http->get('/externalCustomer/fetchPkList');
    }

    // ─── Diger ──────────────────────────────────────────────────────

    /**
     * Secili harici musterileri getirir.
     *
     * @param array $query Sorgu parametreleri
     * @return array Secili musteri listesi
     */
    public function fetchSelected(array $query): array
    {
        return $this->http->get('/externalCustomer/fetchSelected', $query);
    }

    /**
     * TURMOB kullanici bilgisini getirir.
     *
     * @return array TURMOB kullanici bilgisi
     */
    public function getTurmobUser(): array
    {
        return $this->http->get('/externalCustomer/getTurmobUser');
    }
}
