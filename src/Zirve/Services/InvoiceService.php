<?php

namespace ZirveDonusum\Zirve\Services;

/**
 * Fatura (taslak) ve belge sablonu islemleri servisi.
 * Gecici fatura olusturma, gonderme, ek yukleme ve sablon yonetimi.
 */
class InvoiceService extends BaseService
{
    // ─── Gecici Fatura (invoiceTmp) ─────────────────────────────────

    /**
     * Fatura gonderir.
     *
     * @param array $data Fatura verileri
     * @return array Gonderim sonucu
     */
    public function send(array $data): array
    {
        return $this->http->post('/invoiceTmp/send', $data);
    }

    /**
     * Fatura onizleme belgesi getirir.
     *
     * @param array $query Sorgu parametreleri (uuid, vb.)
     * @return array Belge onizleme verisi
     */
    public function showDocument(array $query): array
    {
        return $this->http->get('/invoiceTmp/showDocument', $query);
    }

    /**
     * Faturaya ek dosya yukler.
     *
     * @param array $data Ek dosya verileri
     * @return array Yukleme sonucu
     */
    public function uploadAttachment(array $data): array
    {
        return $this->http->post('/invoiceTmp/uploadAttachment', $data);
    }

    /**
     * Fatura ek dosyasini indirir.
     *
     * @param array $query Sorgu parametreleri (uuid, attachmentId vb.)
     * @return string Ham dosya icerigi
     */
    public function downloadAttachment(array $query): string
    {
        return $this->http->download('/invoiceTmp/downloadAttachment', $query);
    }

    /**
     * Fatura onek (prefix) verisini getirir.
     *
     * @param int $id Prefix ID
     * @return array Prefix verileri
     */
    public function getPrefixData(int $id): array
    {
        return $this->http->get("/invoiceTmp/getInvoicePrefixData/{$id}");
    }

    /**
     * Fatura onek kodunu getirir.
     *
     * @param int $id Prefix ID
     * @return array Prefix kodu
     */
    public function getPrefixCode(int $id): array
    {
        return $this->http->get("/invoiceTmp/getPrefixCode/{$id}");
    }

    /**
     * Ana sayfa prefix kodunu yila gore getirir.
     *
     * @param int $year Yil (ornegin 2024)
     * @return array Prefix kodu bilgisi
     */
    public function getHomePrefixCode(int $year): array
    {
        return $this->http->get('/invoiceTmp/home-prefix-code', ['year' => $year]);
    }

    /**
     * Siradaki fatura numarasini yila gore getirir.
     *
     * @param int $year Yil (ornegin 2024)
     * @return array Siradaki fatura bilgisi
     */
    public function getNextInvoice(int $year): array
    {
        return $this->http->get('/invoiceTmp/next-invoice', ['year' => $year]);
    }

    /**
     * Fatura referans ID'sinin mevcut olup olmadigini kontrol eder.
     *
     * @param array $data Referans ID verileri
     * @return array Kontrol sonucu
     */
    public function checkReferenceIdExist(array $data): array
    {
        return $this->http->post('/invoiceTmp/checkInvoiceReferenceIdExist', $data);
    }

    /**
     * Cezaevi PDF dosyasi yukler.
     *
     * @param array $data PDF yukleme verileri
     * @return array Yukleme sonucu
     */
    public function uploadCezaeviPdf(array $data): array
    {
        return $this->http->post('/invoiceTmp/uploadCezaeviPdf', $data);
    }

    /**
     * Medula PDF dosyasi yukler.
     *
     * @param array $data PDF yukleme verileri
     * @return array Yukleme sonucu
     */
    public function uploadMedulaPdf(array $data): array
    {
        return $this->http->post('/invoiceTmp/uploadMedulaPdf', $data);
    }

    /**
     * ITS/UTS listesini getirir.
     *
     * @return array ITS/UTS kayitlari
     */
    public function getItsUtsList(): array
    {
        return $this->http->get('/invoiceTmp/getItsUtsList');
    }

    /**
     * ITS/UTS dosyasini getirir.
     *
     * @param array $query Sorgu parametreleri
     * @return array Dosya verisi
     */
    public function getItsUtsFile(array $query): array
    {
        return $this->http->get('/invoiceTmp/getItsUtsFile', $query);
    }

    /**
     * ITS/UTS dosyasini siler.
     *
     * @param array $data Silinecek dosya verileri
     * @return array Silme sonucu
     */
    public function deleteItsUtsFile(array $data): array
    {
        return $this->http->post('/invoiceTmp/deleteItsUtsFile', $data);
    }

    // ─── Belge Sablonu (belgeTmp) ───────────────────────────────────

    /**
     * Belge sablonunu duzenleme icin getirir.
     * Yeni sablon olusturmak icin id=-1 gonderilir.
     *
     * @param int $id Sablon ID (-1 = yeni sablon)
     * @return array Sablon verileri
     */
    public function editTemplate(int $id): array
    {
        return $this->http->get("/belgeTmp/edit/{$id}");
    }

    /**
     * Yeni belge sablonu kaydeder.
     *
     * @param array $data Sablon verileri
     * @return array Kayit sonucu
     */
    public function saveTemplate(array $data): array
    {
        return $this->http->post('/belgeTmp/save', $data);
    }

    /**
     * Mevcut belge sablonunu gunceller.
     *
     * @param array $data Guncellenecek sablon verileri
     * @return array Guncelleme sonucu
     */
    public function updateTemplate(array $data): array
    {
        return $this->http->post('/belgeTmp/update', $data);
    }

    /**
     * Belge sablonu onizlemesi getirir.
     *
     * @param array $query Sorgu parametreleri
     * @return array Belge onizleme verisi
     */
    public function showTemplateDocument(array $query): array
    {
        return $this->http->get('/belgeTmp/showDocument', $query);
    }
}
