<?php

namespace ZirveDonusum\Services;

/**
 * E-Fatura / E-Arşiv Fatura İşlemleri
 *
 * Gerçek endpoint'ler (Network tab'dan):
 *   GET /cp/{accountId}/newInvoice/getOptions
 *   GET /cp/{accountId}/newInvoice/get/?invoiceType=EInvoice
 *   GET /cp/{accountId}/newInvoice/getSubtotals
 *   GET /cp/{accountId}/DocumentNo/GenerateOrValidateSerial?uuid=...&prefix=EFAB
 *   GET /cp/{accountId}/packagecode/getall
 *   GET /cp/{accountId}/paymenttype/getall
 *   GET /cp/{accountId}/paymentcode/getall
 *   GET /cp/{accountId}/Nace/GetAccountVatRates
 */
class InvoiceService extends BaseService
{
    // ─── Yeni Fatura Oluşturma ───────────────────────────────────────

    /**
     * Fatura oluşturma seçeneklerini getir (KDV oranları, para birimleri, birimler vb.)
     */
    public function getOptions(): array
    {
        return $this->http->get($this->cp('newInvoice/getOptions'));
    }

    /**
     * Yeni fatura formu / taslak getir
     *
     * @param string $invoiceType EInvoice, EArchive, vb.
     */
    public function getNewInvoice(string $invoiceType = 'EInvoice'): array
    {
        return $this->http->get($this->cp('newInvoice/get/'), ['invoiceType' => $invoiceType]);
    }

    /**
     * Fatura alt toplamlarını hesapla
     */
    public function getSubtotals(): array
    {
        return $this->http->get($this->cp('newInvoice/getSubtotals'));
    }

    /**
     * Belge numarası üret veya doğrula
     *
     * @param string $uuid Fatura UUID
     * @param string $prefix Seri prefix (EFAB, EARB vb.)
     */
    public function generateDocumentNo(string $uuid, string $prefix = 'EFAB'): array
    {
        return $this->http->get($this->cp('DocumentNo/GenerateOrValidateSerial'), [
            'uuid' => $uuid,
            'prefix' => $prefix,
        ]);
    }

    // ─── Referans Verileri ───────────────────────────────────────────

    /**
     * Paket kodlarını getir
     */
    public function getPackageCodes(): array
    {
        return $this->http->get($this->cp('packagecode/getall'));
    }

    /**
     * Ödeme tiplerini getir
     */
    public function getPaymentTypes(): array
    {
        return $this->http->get($this->cp('paymenttype/getall'));
    }

    /**
     * Ödeme kodlarını getir
     */
    public function getPaymentCodes(): array
    {
        return $this->http->get($this->cp('paymentcode/getall'));
    }

    /**
     * Hesabın KDV oranlarını getir (NACE koduna göre)
     */
    public function getVatRates(): array
    {
        return $this->http->get($this->cp('Nace/GetAccountVatRates'));
    }

    // ─── Taslak Fatura Listeleme ────────────────────────────────────

    /**
     * Taslak e-faturaları listele (sayfalı, filtreli)
     *
     * Gerçek endpoint:
     *   GET /cp/{accountId}/NewInvoice/GetDraftList?firstDate=...&lastDate=...&invoiceType=EInvoice&page=1&recordPerPage=20
     *
     * @param array $filters Filtre parametreleri:
     *   - firstDate: Başlangıç tarihi (ISO 8601, örn: 2026-02-15T21:00:00.000Z)
     *   - lastDate: Bitiş tarihi (ISO 8601, örn: 2026-03-16T20:59:59.999Z)
     *   - invoiceType: EInvoice, EArchive
     *   - invoiceTypeCode: TUMU, SATIS, IADE vb.
     *   - profile: TUMU, TEMELFATURA, TICARIFATURA
     *   - taxNumber: VKN/TCKN ile filtrele
     *   - title: Firma adı ile filtrele
     *   - name: Ad ile filtrele
     *   - surname: Soyad ile filtrele
     *   - page: Sayfa numarası (varsayılan: 1)
     *   - recordPerPage: Sayfa başına kayıt (varsayılan: 20)
     */
    public function listDrafts(array $filters = []): array
    {
        $defaults = [
            'firstDate' => date('Y-m-d\T00:00:00.000\Z', strtotime('-30 days')),
            'lastDate' => date('Y-m-d\T23:59:59.999\Z'),
            'invoiceType' => 'EInvoice',
            'invoiceTypeCode' => 'TUMU',
            'profile' => 'TUMU',
            'taxNumber' => '',
            'title' => '',
            'name' => '',
            'surname' => '',
            'page' => 1,
            'recordPerPage' => 20,
        ];

        return $this->http->get(
            $this->cp('NewInvoice/GetDraftList'),
            array_merge($defaults, $filters)
        );
    }

    // ─── Giden Fatura Listeleme ─────────────────────────────────────

    /**
     * Giden e-faturaları listele (sayfalı, filtreli)
     *
     * Gerçek endpoint:
     *   GET /cp/{accountId}/outbox/GetSubmittedInvoiceList
     *
     * @param array $filters Filtre parametreleri:
     *   - firstDate: Başlangıç tarihi (ISO 8601)
     *   - lastDate: Bitiş tarihi (ISO 8601)
     *   - filterDateType: DocumentDate (varsayılan)
     *   - invoiceProfilesFilter: TUMU, TEMELFATURA, TICARIFATURA
     *   - invoiceTypeCodesFilter: TUMU, SATIS, IADE
     *   - state: Hepsi, Onaylandi, Reddedildi
     *   - outingInvoiceState: Hepsi
     *   - cancelledStatus: All
     *   - FlagStatus: All
     *   - readingState: All
     *   - invoiceCurrency: All, TRY, USD, EUR
     *   - taxNumber: VKN/TCKN ile filtrele
     *   - gibNumber: GİB numarası ile filtrele
     *   - minAmount: Minimum tutar
     *   - maxAmount: Maksimum tutar
     *   - page: Sayfa numarası (varsayılan: 1)
     *   - recordPerPage: Sayfa başına kayıt (varsayılan: 20)
     *   - sortColumn: Sıralama kolonu
     *   - sortOrder: Sıralama yönü
     */
    public function listOutgoing(array $filters = []): array
    {
        $defaults = [
            'FlagStatus' => 'All',
            'cancelledStatus' => 'All',
            'filterDateType' => 'DocumentDate',
            'firstDate' => date('Y-m-d\TH:i:s.v\Z', strtotime('-30 days')),
            'lastDate' => date('Y-m-d\TH:i:s.v\Z'),
            'folder' => '',
            'gibNumber' => '',
            'invoiceCurrency' => 'All',
            'invoiceProfilesFilter' => 'TUMU',
            'invoiceTypeCodesFilter' => 'TUMU',
            'maxAmount' => '',
            'minAmount' => '',
            'outingInvoiceState' => 'Hepsi',
            'page' => 1,
            'readingState' => 'All',
            'recordPerPage' => 20,
            'sortColumn' => '',
            'sortOrder' => '',
            'state' => 'Hepsi',
            'taxNumber' => '',
        ];

        return $this->http->get(
            $this->cp('outbox/GetSubmittedInvoiceList'),
            array_merge($defaults, $filters)
        );
    }

    // ─── Gelen Fatura Listeleme ──────────────────────────────────────

    /**
     * Gelen e-faturaları listele (sayfalı, filtreli)
     *
     * Gerçek endpoint:
     *   GET /cp/{accountId}/inbox/GetIncomingInvoiceList
     *
     * @param array $filters Aynı filtre parametreleri (giden ile aynı yapı)
     */
    public function listIncoming(array $filters = []): array
    {
        $defaults = [
            'FlagStatus' => 'All',
            'cancelledStatus' => 'All',
            'filterDateType' => 'DocumentDate',
            'firstDate' => date('Y-m-d\TH:i:s.v\Z', strtotime('-30 days')),
            'lastDate' => date('Y-m-d\TH:i:s.v\Z'),
            'folder' => '',
            'gibNumber' => '',
            'invoiceCurrency' => 'All',
            'invoiceProfilesFilter' => 'TUMU',
            'invoiceTypeCodesFilter' => 'TUMU',
            'maxAmount' => '',
            'minAmount' => '',
            'page' => 1,
            'readingState' => 'All',
            'recordPerPage' => 20,
            'sortColumn' => '',
            'sortOrder' => '',
            'state' => 'Hepsi',
            'taxNumber' => '',
        ];

        return $this->http->get(
            $this->cp('inbox/GetIncomingInvoiceList'),
            array_merge($defaults, $filters)
        );
    }

    // ─── E-Arşiv ──────────────────────────────────────────────────────

    /**
     * E-Arşiv faturaları listele (sayfalı, filtreli)
     *
     * Gerçek endpoint:
     *   GET /cp/{accountId}/EArchive/GetEArchiveList
     *
     * @param array $filters Filtre parametreleri:
     *   - firstDate, lastDate: Tarih aralığı (ISO 8601)
     *   - filterDateType: DocumentDate
     *   - eArchiveState: Hepsi
     *   - invoiceTypeCodesFilter: TUMU, SATIS, IADE
     *   - cancelledStatus: All
     *   - FlagStatus: All
     *   - status: All
     *   - readingState: All
     *   - invoiceCurrency: All, TRY, USD, EUR
     *   - taxNumber, gibNumber, minAmount, maxAmount
     *   - page, recordPerPage, sortColumn, sortOrder
     */
    public function listArchive(array $filters = []): array
    {
        $defaults = [
            'FlagStatus' => 'All',
            'cancelledStatus' => 'All',
            'eArchiveState' => 'Hepsi',
            'filterDateType' => 'DocumentDate',
            'firstDate' => date('Y-m-d\TH:i:s.v\Z', strtotime('-30 days')),
            'lastDate' => date('Y-m-d\TH:i:s.v\Z'),
            'folder' => '',
            'gibNumber' => '',
            'invoiceCurrency' => 'All',
            'invoiceTypeCodesFilter' => 'TUMU',
            'maxAmount' => '',
            'minAmount' => '',
            'page' => 1,
            'readingState' => 'All',
            'recordPerPage' => 20,
            'sortColumn' => '',
            'sortOrder' => '',
            'status' => 'All',
            'taxNumber' => '',
        ];

        return $this->http->get(
            $this->cp('EArchive/GetEArchiveList'),
            array_merge($defaults, $filters)
        );
    }

    /**
     * E-Arşiv taslak faturaları listele
     * Aynı GetDraftList endpoint'i, invoiceType=EArchive filtresi ile
     */
    public function listArchiveDrafts(array $filters = []): array
    {
        return $this->listDrafts(array_merge(['invoiceType' => 'EArchive'], $filters));
    }

    /**
     * Yeni E-Arşiv fatura şablonu al
     * Aynı newInvoice/get endpoint'i, invoiceType=EArchive ile
     * Prefix: EARB
     */
    public function getNewArchiveInvoice(): array
    {
        return $this->getNewInvoice('EArchive');
    }

    /**
     * E-Arşiv fatura şablonunu Invoice modeli olarak al
     */
    public function newArchiveDraft(): \ZirveDonusum\Models\Invoice
    {
        $response = $this->getNewArchiveInvoice();
        return \ZirveDonusum\Models\Invoice::fromResponse($response);
    }

    /**
     * E-Arşiv belge numarası üret (EARB prefix)
     */
    public function generateArchiveDocumentNo(string $uuid): array
    {
        return $this->generateDocumentNo($uuid, 'EARB');
    }

    // ─── Fatura Detay ────────────────────────────────────────────────

    /**
     * Tek fatura detayı getir
     */
    public function get(string $invoiceId): array
    {
        return $this->http->get($this->cp('einvoice/GetInvoiceDetail'), ['id' => $invoiceId]);
    }

    /**
     * Gelen fatura HTML görüntüsü
     * Gerçek endpoint: /cp/{accountId}/inbox/GetDocumentAsHtml?id=...
     */
    public function getIncomingHtml(string $invoiceId): string
    {
        return $this->http->download($this->cp('inbox/GetDocumentAsHtml'), ['id' => $invoiceId]);
    }

    /**
     * Giden fatura HTML görüntüsü
     * Gerçek endpoint: /cp/{accountId}/outbox/GetDocumentAsHtml?id=...
     */
    public function getOutgoingHtml(string $invoiceId): string
    {
        return $this->http->download($this->cp('outbox/GetDocumentAsHtml'), ['id' => $invoiceId]);
    }

    /**
     * Fatura HTML görüntüsü (geriye uyumluluk — gelen fatura varsayılan)
     */
    public function getHtml(string $invoiceId, string $direction = 'incoming'): string
    {
        $prefix = $direction === 'outgoing' ? 'outbox' : 'inbox';
        return $this->http->download($this->cp("{$prefix}/GetDocumentAsHtml"), ['id' => $invoiceId]);
    }

    /**
     * Fatura XML (UBL) indir
     * Gerçek endpoint: /cp/{accountId}/inbox/downloadUBL?enveloped=false&id=...
     *
     * @param string $invoiceId Fatura UUID
     * @param string $direction incoming veya outgoing
     * @param bool $enveloped Zarf XML mi (true = zarflı, false = sadece fatura)
     */
    public function downloadXml(string $invoiceId, string $direction = 'incoming', bool $enveloped = false): string
    {
        $prefix = $direction === 'outgoing' ? 'outbox' : 'inbox';
        return $this->http->download($this->cp("{$prefix}/downloadUBL"), [
            'id' => $invoiceId,
            'enveloped' => $enveloped ? 'true' : 'false',
        ]);
    }

    /**
     * Zarf XML indir (enveloped=true)
     */
    public function downloadEnvelopeXml(string $invoiceId, string $direction = 'incoming'): string
    {
        return $this->downloadXml($invoiceId, $direction, true);
    }

    /**
     * Fatura PDF indir (ZIP olarak döner)
     * Gerçek endpoint: POST /cp/{accountId}/inbox/DownloadPdfFilesZip
     *
     * @param string $invoiceId Fatura UUID
     * @param string $direction incoming veya outgoing
     * @return string ZIP dosya içeriği
     */
    public function downloadPdf(string $invoiceId, string $direction = 'incoming'): string
    {
        $prefix = $direction === 'outgoing' ? 'outbox' : 'inbox';
        $positionType = $direction === 'outgoing' ? 'Outgoing' : 'Incoming';

        $response = $this->http->raw('POST', $this->cp("{$prefix}/DownloadPdfFilesZip"), [
            'json' => [
                'IdList' => [$invoiceId],
                'IsSinglePdf' => true,
                'DocumentType' => 'DCEInvoice',
                'IsZipped' => false,
                'ItemPositionType' => $positionType,
            ],
        ]);

        return $response->getBody()->getContents();
    }

    /**
     * Fatura PDF indir (ZIP olarak, birden fazla fatura)
     */
    public function downloadPdfZip(array $invoiceIds, string $direction = 'incoming'): string
    {
        $prefix = $direction === 'outgoing' ? 'outbox' : 'inbox';
        $positionType = $direction === 'outgoing' ? 'Outgoing' : 'Incoming';

        $response = $this->http->raw('POST', $this->cp("{$prefix}/DownloadPdfFilesZip"), [
            'json' => [
                'IdList' => $invoiceIds,
                'IsSinglePdf' => false,
                'DocumentType' => 'DCEInvoice',
                'IsZipped' => true,
                'ItemPositionType' => $positionType,
            ],
        ]);

        return $response->getBody()->getContents();
    }

    // ─── Fatura Gönderme ─────────────────────────────────────────────

    /**
     * Yeni e-fatura gönder / kaydet
     *
     * @param array|\ZirveDonusum\Models\Invoice $invoiceData Fatura verisi
     *
     * Kullanım:
     *   // Array ile
     *   $service->send(['InvoiceType' => 'EInvoice', ...]);
     *
     *   // Invoice model ile
     *   $fatura = Invoice::create()
     *       ->customer('1234567890', 'Firma Adı', 'Vergi Dairesi')
     *       ->addLine('Ürün', 1, 100.00, 20);
     *   $service->send($fatura);
     */
    public function send(array|\ZirveDonusum\Models\Invoice $invoiceData): array
    {
        $data = $invoiceData instanceof \ZirveDonusum\Models\Invoice
            ? $invoiceData->toArray()
            : $invoiceData;

        return $this->http->post($this->cp('newInvoice/save'), $data);
    }

    /**
     * E-Arşiv fatura oluştur
     */
    public function createArchive(array|\ZirveDonusum\Models\Invoice $invoiceData): array
    {
        if ($invoiceData instanceof \ZirveDonusum\Models\Invoice) {
            $invoiceData->type('EArchive');
            $data = $invoiceData->toArray();
        } else {
            $data = array_merge($invoiceData, ['InvoiceType' => 'EArchive']);
        }

        return $this->http->post($this->cp('newInvoice/save'), $data);
    }

    /**
     * Sunucudan boş fatura şablonu al, Invoice modeli olarak döndür
     *
     * @param string $invoiceType EInvoice, EArchive
     * @return \ZirveDonusum\Models\Invoice
     */
    public function newDraft(string $invoiceType = 'EInvoice'): \ZirveDonusum\Models\Invoice
    {
        $response = $this->getNewInvoice($invoiceType);
        return \ZirveDonusum\Models\Invoice::fromResponse($response);
    }

    // ─── Fatura İşlemleri ────────────────────────────────────────────

    /**
     * Gelen faturayı kabul et
     */
    public function accept(string $invoiceId): array
    {
        return $this->http->postForm($this->cp('einvoice/AcceptInvoice'), ['id' => $invoiceId]);
    }

    /**
     * Gelen faturayı reddet
     */
    public function reject(string $invoiceId, string $reason = ''): array
    {
        return $this->http->postForm($this->cp('einvoice/RejectInvoice'), [
            'id' => $invoiceId,
            'reason' => $reason,
        ]);
    }

    /**
     * Fatura durumunu sorgula
     */
    public function status(string $invoiceId): array
    {
        return $this->http->get($this->cp('einvoice/GetInvoiceStatus'), ['id' => $invoiceId]);
    }
}
