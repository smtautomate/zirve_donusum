<?php

namespace ZirveDonusum\Zirve\Services;

/**
 * ERP Connector islemleri servisi.
 * Baglanti yonetimi, musteri/stok/fatura ERP entegrasyonu, kural ve defter islemleri.
 */
class ConnectorService extends BaseService
{
    // ─── Baglanti Bilgisi ───────────────────────────────────────────

    /**
     * Baglanti bilgisini getirir.
     *
     * @return array Baglanti bilgisi
     */
    public function getConnectionInfo(): array
    {
        return $this->http->post('/connector/getConnectionInfo');
    }

    /**
     * Connector turlerini getirir.
     *
     * @return array Tur listesi
     */
    public function getTypes(): array
    {
        return $this->http->get('/connector/getTypes');
    }

    /**
     * Connector son kullanma tarihini getirir.
     *
     * @return array Son kullanma tarihi
     */
    public function getExpireDate(): array
    {
        return $this->http->get('/connector/getExpireDate');
    }

    /**
     * Musteri connector son kullanma tarihini getirir.
     *
     * @return array Son kullanma tarihi
     */
    public function getCustomerExpireDate(): array
    {
        return $this->http->get('/connector/getCustomerExpireDate');
    }

    // ─── Musteri Connector ──────────────────────────────────────────

    /**
     * Musteri connector bilgisini getirir.
     *
     * @param int $id Musteri ID
     * @return array Connector bilgisi
     */
    public function getCustomerConnector(int $id): array
    {
        return $this->http->get("/connector/getCustomerConnector/{$id}");
    }

    /**
     * Musteri ve tum connector bilgilerini getirir.
     *
     * @param int $id Musteri ID
     * @return array Connector ve musteri bilgileri
     */
    public function getAllConnectorAndCustomer(int $id): array
    {
        return $this->http->get("/connector/getAllConnectorAndCustomer/{$id}");
    }

    /**
     * Musteri icin ERP turlerini getirir.
     *
     * @param int $id Musteri ID
     * @return array ERP tur listesi
     */
    public function fetchErpTypes(int $id): array
    {
        return $this->http->get("/connector/fetchErpTypes/{$id}");
    }

    /**
     * Musteri icin servis turlerini getirir.
     *
     * @param int $id Musteri ID
     * @return array Servis tur listesi
     */
    public function fetchServiceTypes(int $id): array
    {
        return $this->http->get("/connector/fetchServiceTypes/{$id}");
    }

    // ─── Kaydetme ───────────────────────────────────────────────────

    /**
     * Connector kaydeder.
     *
     * @param array $data Connector verileri
     * @return array Kayit sonucu
     */
    public function save(array $data): array
    {
        return $this->http->post('/connector/save', $data);
    }

    /**
     * Musteri connector bilgisini kaydeder.
     *
     * @param int   $id   Musteri ID
     * @param array $data Connector verileri
     * @return array Kayit sonucu
     */
    public function saveCustomerConnector(int $id, array $data): array
    {
        return $this->http->post("/connector/saveCustomerConnector/{$id}", $data);
    }

    // ─── Musteri ERP Okuma/Yazma ────────────────────────────────────

    /**
     * ERP'den musteri verisi okur.
     *
     * @param array $query Sorgu parametreleri
     * @return array Musteri verisi
     */
    public function readCustomer(array $query): array
    {
        return $this->http->get('/connector/readCustomer', $query);
    }

    /**
     * ERP'ye musteri verisi yazar.
     *
     * @param array $data Musteri verileri
     * @return array Yazma sonucu
     */
    public function writeCustomer(array $data): array
    {
        return $this->http->post('/connector/writeCustomer', $data);
    }

    // ─── Stok ERP Okuma/Yazma ───────────────────────────────────────

    /**
     * ERP'den stok verisi okur.
     *
     * @param array $query Sorgu parametreleri
     * @return array Stok verisi
     */
    public function readStock(array $query): array
    {
        return $this->http->get('/connector/readStock', $query);
    }

    /**
     * ERP'ye stok verisi yazar.
     *
     * @param array $data Stok verileri
     * @return array Yazma sonucu
     */
    public function writeStock(array $data): array
    {
        return $this->http->post('/connector/writeStock', $data);
    }

    /**
     * ERP'ye stok verisi SQL ile yazar.
     *
     * @param array $data Stok SQL verileri
     * @return array Yazma sonucu
     */
    public function writeStockSql(array $data): array
    {
        return $this->http->post('/connector/writeStockSql', $data);
    }

    // ─── Belge/Fatura ERP ───────────────────────────────────────────

    /**
     * ERP'den belge okur.
     *
     * @param int $id Belge ID
     * @return array Belge verisi
     */
    public function readDocument(int $id): array
    {
        return $this->http->get("/connector/readDocument/{$id}");
    }

    /**
     * ERP'ye fatura yazar.
     *
     * @param int   $id   Fatura ID
     * @param array $data Fatura verileri
     * @return array Yazma sonucu
     */
    public function writeInvoice(int $id, array $data): array
    {
        return $this->http->post("/connector/writeInvoice/{$id}", $data);
    }

    /**
     * Connector stok listesini getirir.
     *
     * @return array Stok listesi
     */
    public function getStockList(): array
    {
        return $this->http->get('/connector/getStockList');
    }

    // ─── ERP Muhasebe & Odeme ───────────────────────────────────────

    /**
     * ERP muhasebe kullanicisini getirir.
     *
     * @return array Muhasebe kullanici bilgisi
     */
    public function getErpAccountingUser(): array
    {
        return $this->http->get('/connector/getErpAccountingUser');
    }

    /**
     * ERP odeme bilgisini kaydeder.
     *
     * @param array $data Odeme verileri
     * @return array Kayit sonucu
     */
    public function setErpPaymentInfo(array $data): array
    {
        return $this->http->post('/connector/setErpPaymentInfo', $data);
    }

    /**
     * ERP odeme bilgilerini getirir.
     *
     * @return array Odeme bilgileri
     */
    public function fetchErpPaymentInformation(): array
    {
        return $this->http->get('/connector/fetchErpPaymentInformation');
    }

    // ─── Belge Bilgisi ──────────────────────────────────────────────

    /**
     * Belge bilgisini getirir.
     *
     * @param int $id Belge ID
     * @return array Belge bilgisi
     */
    public function documentInformation(int $id): array
    {
        return $this->http->get("/connector/documentInformation/{$id}");
    }

    /**
     * Belge bilgisini web servis uzerinden getirir.
     *
     * @param int $id Belge ID
     * @return array Belge bilgisi
     */
    public function documentInformationWs(int $id): array
    {
        return $this->http->get("/connector/documentInformationWs/{$id}");
    }

    // ─── Kurallar ───────────────────────────────────────────────────

    /**
     * Connector kurallari olusturur.
     *
     * @param array $data Kural verileri
     * @return array Kayit sonucu
     */
    public function createRules(array $data): array
    {
        return $this->http->post('/connector/createRules', $data);
    }

    /**
     * Connector kurallarini getirir.
     *
     * @return array Kural listesi
     */
    public function getRules(): array
    {
        return $this->http->get('/connector/getRules');
    }

    // ─── Defter ─────────────────────────────────────────────────────

    /**
     * Defter bilgisini getirir.
     *
     * @param int $id Defter ID
     * @return array Defter bilgisi
     */
    public function getDefter(int $id): array
    {
        return $this->http->get("/connector/getDefter/{$id}");
    }

    /**
     * ERP'den defter okur.
     *
     * @param int $id Defter ID
     * @return array Defter verisi
     */
    public function readDefter(int $id): array
    {
        return $this->http->post("/connector/readDefter/{$id}", []);
    }
}
