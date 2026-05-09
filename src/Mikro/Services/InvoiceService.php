<?php

namespace ZirveDonusum\Mikro\Services;

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
     * Yeni fatura formu / taslak getir (sunucu UUID + serial dahil döner)
     *
     * Mikro API: POST /cp/{accountId}/newInvoice/get
     * @param string $invoiceType EInvoice, EArchive, vb.
     */
    public function getNewInvoice(string $invoiceType = 'EInvoice'): array
    {
        return $this->http->post($this->cp('newInvoice/get'), ['invoiceType' => $invoiceType]);
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
    public function newArchiveDraft(): \ZirveDonusum\Mikro\Models\Invoice
    {
        $response = $this->getNewArchiveInvoice();
        return \ZirveDonusum\Mikro\Models\Invoice::fromResponse($response);
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
     * API: GET /cp/{accountId}/inbox/GetDocumentAsHtml → {"success":true,"html":"<html>..."}
     */
    public function getIncomingHtml(string $invoiceId): string
    {
        $response = $this->http->get($this->cp('inbox/GetDocumentAsHtml'), ['id' => $invoiceId]);
        return $response['html'] ?? '';
    }

    /**
     * Giden fatura HTML görüntüsü
     * API: GET /cp/{accountId}/outbox/GetDocumentAsHtml → {"success":true,"html":"<html>..."}
     */
    public function getOutgoingHtml(string $invoiceId): string
    {
        $response = $this->http->get($this->cp('outbox/GetDocumentAsHtml'), ['id' => $invoiceId]);
        return $response['html'] ?? '';
    }

    /**
     * Fatura HTML görüntüsü (geriye uyumluluk — gelen fatura varsayılan)
     */
    public function getHtml(string $invoiceId, string $direction = 'incoming'): string
    {
        $prefix = $direction === 'outgoing' ? 'outbox' : 'inbox';
        $response = $this->http->get($this->cp("{$prefix}/GetDocumentAsHtml"), ['id' => $invoiceId]);
        return $response['html'] ?? '';
    }

    /**
     * Fatura XML (UBL) indir — ZIP formatında döner (içinde XML vardır)
     * API: GET /cp/{accountId}/inbox/downloadUBL?enveloped=false&id=...
     *
     * @param string $invoiceId Fatura UUID
     * @param string $direction incoming veya outgoing
     * @param bool $enveloped Zarf XML mi (true = zarflı, false = sadece fatura)
     * @return string ZIP binary içeriği (içinde XML vardır)
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
     * @param array|\ZirveDonusum\Mikro\Models\Invoice $invoiceData Fatura verisi
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
    public function send(array|\ZirveDonusum\Mikro\Models\Invoice $invoiceData): array
    {
        $data = $invoiceData instanceof \ZirveDonusum\Mikro\Models\Invoice
            ? $invoiceData->toArray()
            : $invoiceData;

        return $this->http->post($this->cp('newInvoice/send'), $data);
    }

    /**
     * E-Arşiv fatura oluştur
     */
    public function createArchive(array|\ZirveDonusum\Mikro\Models\Invoice $invoiceData): array
    {
        if ($invoiceData instanceof \ZirveDonusum\Mikro\Models\Invoice) {
            $invoiceData->type('EArchive');
            $data = $invoiceData->toArray();
        } else {
            $data = array_merge($invoiceData, ['InvoiceType' => 'EArchive']);
        }

        return $this->http->post($this->cp('newInvoice/send'), $data);
    }

    /**
     * Sunucudan boş fatura şablonu al, Invoice modeli olarak döndür
     *
     * @param string $invoiceType EInvoice, EArchive
     * @return \ZirveDonusum\Mikro\Models\Invoice
     */
    public function newDraft(string $invoiceType = 'EInvoice'): \ZirveDonusum\Mikro\Models\Invoice
    {
        $response = $this->getNewInvoice($invoiceType);
        return \ZirveDonusum\Mikro\Models\Invoice::fromResponse($response);
    }

    // ─── Tam Fatura Oluşturma İş Akışı ──────────────────────────────

    /**
     * Sunucudan sıradaki fatura numarasını üret
     *
     * Akış: getNewInvoice() → UUID + serial al → generateDocumentNo() → numara döndür
     *
     * @param string|null $prefix Seri prefix. null ise sunucudan (getNewInvoice) otomatik alınır.
     * @param string $invoiceType EInvoice veya EArchive
     */
    public function nextDocumentNo(?string $prefix = null, string $invoiceType = 'EInvoice'): array
    {
        $template = $this->getNewInvoice($invoiceType);
        $uuid = $this->extractUuid($template);

        if (!$uuid) {
            throw new \RuntimeException(
                'Sunucudan UUID alınamadı. getNewInvoice() response: ' . json_encode($template)
            );
        }

        // Prefix'i template'den al (her firma/yıl farklı seri kullanabilir)
        if (!$prefix) {
            $invoice = $template['invoice'] ?? $template['Invoice'] ?? $template;
            $prefix = $invoice['Number']['Serial'] ?? ($invoiceType === 'EArchive' ? 'EARB' : 'EFAB');
        }

        return $this->generateDocumentNo($uuid, $prefix);
    }

    /**
     * Tam fatura oluşturma iş akışı (Chrome DevTools ile doğrulandı)
     *
     * Sıra: getNewInvoice() → UUID → generateDocumentNo() → aliasLookup() → send()
     *
     * @param string $taxNumber Müşteri VKN/TCKN
     * @param string $title     Müşteri ünvanı
     * @param array  $lines     Fatura kalemleri:
     *   [['name', 'quantity', 'unitPrice', 'vatRate'?=20, 'unit'?='C62',
     *     'discount'?=0, 'stockCode'?=null, 'withholding'?=[]], ...]
     * @param array  $options   Ek seçenekler:
     *   - invoiceType:  EInvoice (varsayılan) | EArchive
     *   - profile:      TEMELFATURA (varsayılan) | TICARIFATURA | IHRACAT | KAMU
     *   - typeCode:     SATIS (varsayılan) | IADE | TEVKIFAT | SGK
     *   - prefix:       Seri prefix (boş = template'den otomatik)
     *   - date:         Y-m-d formatında tarih (boş = bugün)
     *   - taxOffice:    Vergi dairesi
     *   - aliasObj:     checkEInvoiceRegistered() users[0] tam nesnesi (otomatik sorgulanır)
     *   - description:  Fatura notu
     *   - currency:     TRY (varsayılan) | USD | EUR
     *   - paymentType:  KREDIKARTI_BANKAKARTI (varsayılan) | EFT_HAVALE | NAKIT
     *   - iban:         IBAN numarası
     */
    public function create(
        string $taxNumber,
        string $title,
        array $lines,
        array $options = []
    ): array {
        $invoiceType = $options['invoiceType'] ?? 'EInvoice';

        // 1. Boş şablon + UUID + SubAccountId al
        $template    = $this->getNewInvoice($invoiceType);
        $invoiceNode = $template['invoice'] ?? $template;
        $uuid        = $invoiceNode['UUID'] ?? null;

        if (!$uuid) {
            throw new \RuntimeException('UUID alınamadı: ' . json_encode(array_keys($invoiceNode)));
        }

        // 2. Seri prefix ve sıradaki numara (her firma/yıl farklı seri kullanabilir)
        $prefix        = $options['prefix'] ?? ($invoiceNode['Number']['Serial'] ?? 'EFAB');
        $docNoResponse = $this->generateDocumentNo($uuid, $prefix);
        $serial        = $docNoResponse['Data']['Prefix'] ?? $prefix;
        $docNumber     = $docNoResponse['Data']['Serial'] ?? 0;

        // 3. e-Fatura alias'ı otomatik sorgula (EInvoice için zorunlu)
        $aliasObj = $options['aliasObj'] ?? null;
        if ($aliasObj === null && $invoiceType === 'EInvoice') {
            $aliasResp = $this->http->post(
                $this->cp("newInvoice/getCustomerEInvoiceUsers/{$taxNumber}"),
                []
            );
            $aliasObj = $aliasResp['Data']['users'][0] ?? null;
        }

        $now = date('Y-m-d\TH:i:s.000\Z');

        // 4. Payload (Chrome DevTools'tan doğrulandı)
        $payload = array_merge($invoiceNode, [
            'Id'          => '',
            'InvoiceType' => $invoiceType,
            'Profile'     => $options['profile'] ?? 'TEMELFATURA',
            'TypeCode'    => $options['typeCode'] ?? 'SATIS',
            'UUID'        => $uuid,
            'Number'      => ['Serial' => $serial, 'Number' => $docNumber],
            'Date'        => isset($options['date']) ? $options['date'] . 'T00:00:00.000Z' : $now,
            'Time'        => date('H:i'),
            'ExchangeRate'=> 1,
            'ExchangeType'=> 'Buying',
            'PayableAmountForManuelSet' => 0,
            'IsSpecialBudgetPublicInstitution' => false,
            'PublicPayingCustomerCountry' => ['Code' => 'TR', 'Name' => 'TÜRKİYE'],
            'BuyerCustomerNo' => '',
            'FromDespatch'    => false,
            'FromDespatchDate'=> $now,
            'IsDespatch'      => false,
            'Passenger'       => (object) [],
            'TaxRepresentative' => (object) [],
            'SelectedTechnologies' => ['IMEInumbers' => [''], 'MACnumbers' => ['']],
            'AdditionalFields'    => [],
            'AselsanAdditionalFields' => [],
            'AdditionalDocuments' => [],
            'CancelInfo'      => [],
            'Dispatchs'       => [(object) []],
            'InvestmentIncentiveDocumentDate' => $now,
            'IBANNo'     => $options['iban'] ?? null,
            'Description'=> $options['description'] ?? null,
            'Customer'   => [
                'TaxNumber'  => $taxNumber,
                'Title'      => $title,
                'Name'       => '',
                'Surname'    => '',
                'TaxOffice'  => $options['taxOffice'] ?? '',
                'DealerNo'   => '',
                'VehicleNumberPlate'          => '',
                'VehicleIdentificationNumber' => '',
                'Alias'         => $aliasObj,
                'EInvoiceUsers' => $aliasObj ? [$aliasObj] : [],
                'IsEmailSend'   => false,
                'Address' => [
                    'Country'             => ['Code' => 'TR', 'Name' => 'TÜRKİYE'],
                    'City'                => $options['city'] ?? null,
                    'CitySubdivisionName' => $options['district'] ?? null,
                ],
            ],
            'Payment' => [
                'Type'        => $options['paymentType'] ?? 'KREDIKARTI_BANKAKARTI',
                'IsOnlineSale'=> false,
            ],
            'Details' => [],
        ]);

        if (isset($options['currency'])) {
            $payload['Currency'] = ['Code' => $options['currency'], 'Name' => $options['currency']];
        }

        // 5. Kalemler
        $rowNum = 1;
        foreach ($lines as $line) {
            $amount     = round((float)$line['quantity'] * (float)$line['unitPrice'], 2);
            $vatRate    = (int)($line['vatRate'] ?? 20);
            $kdvAmount  = round($amount * $vatRate / 100, 2);
            $discount   = (float)($line['discount'] ?? 0.0);

            $payload['Details'][] = [
                'RowNumber'      => $rowNum++,
                'StockName'      => $line['name'],
                'StockCode'      => $line['stockCode'] ?? null,
                'Unit'           => $line['unit'] ?? 'C62',
                'Quantity'       => (float)$line['quantity'],
                'UnitPrice'      => (float)$line['unitPrice'],
                'Amount'         => $amount,
                'UnFixedAmount'  => $amount,
                'KdvAmount'      => $kdvAmount,
                'TotalAmount'    => round($amount + $kdvAmount, 2),
                'VATRate'        => $vatRate,
                'Currency'       => $payload['Currency']['Code'] ?? 'TRY',
                'Taxes'          => [],
                'Discounts'      => [['Amount' => $amount, 'DiscountRate' => 0, 'DiscountAmount' => $discount, 'Description' => null]],
                'IdisTagNumbers' => [],
                'IsProductSelected'      => isset($line['stockCode']),
                'isProductExist'         => isset($line['stockCode']),
                'ExemptionReason'        => null,
                'TaxAmountForTaxAssesment'=> null,
                'FreightCharge'  => 0,
                'InnsuranceCharge'=> 0,
                'ContainerQuantity'=> 0,
                'ContainerNumber' => null,
                'PackagingTypeCode'=> null,
                'DeliveryTerm'   => 'Belirtilmedi',
                'TransportMode'  => 'Belirtilmedi',
                'GTIP'           => null,
            ];
        }

        // 6. Gönder
        return $this->send($payload);
    }

    /**
     * UUID'yi response'dan çıkar (birden fazla key olasılığını dener)
     */
    private function extractUuid(array $response): ?string
    {
        $invoice = $response['invoice'] ?? $response['Invoice'] ?? $response;

        return $invoice['UUID']
            ?? $invoice['Uuid']
            ?? $invoice['uuid']
            ?? $invoice['Id']
            ?? $invoice['id']
            ?? $response['UUID']
            ?? $response['uuid']
            ?? null;
    }

    // ─── Fatura Kabul / Red (TICARIFATURA) ──────────────────────────

    /**
     * Gelen TİCARİ FATURA'yı kabul et
     *
     * Endpoint: POST /cp/{accountId}/inbox/AcceptReject
     * Payload:  {"id":"...","operation":"accept","description":""}
     *
     * NOT: Sadece TICARIFATURA profilinde uygulanır.
     *      TEMELFATURA otomatik işlenir, kabul/red gerekmez.
     *      Bir faturaya yalnızca bir kez yanıt verilebilir.
     */
    public function accept(string $invoiceId): array
    {
        return $this->http->post($this->cp('inbox/AcceptReject'), [
            'id'          => $invoiceId,
            'operation'   => 'accept',
            'description' => '',
        ]);
    }

    /**
     * Gelen TİCARİ FATURA'yı reddet
     *
     * Endpoint: POST /cp/{accountId}/inbox/AcceptReject
     * Payload:  {"id":"...","operation":"reject","description":"Red gerekçesi"}
     *
     * @param string $reason Red gerekçesi (zorunlu olabilir)
     */
    public function reject(string $invoiceId, string $reason = ''): array
    {
        return $this->http->post($this->cp('inbox/AcceptReject'), [
            'id'          => $invoiceId,
            'operation'   => 'reject',
            'description' => $reason,
        ]);
    }

    /**
     * Gelen fatura durum sorgulama
     *
     * State kodları:
     *   1000 = Fatura oluşturuldu
     *   1002 = Fatura zarflandı (imzalandı)
     *   1200 = GİB tarafından işlendi
     *   1300 = Başarıyla tamamlandı
     *
     * IsPassedExpiryDate = true → TİCARİFATURA'da 8 günlük süre doldu
     * Responses[n].IsApplicationResponse = true → Alıcının kabul/red yanıtı
     */
    public function incomingStatus(string $invoiceId): array
    {
        $page = 1;
        while (true) {
            $result = $this->listIncoming(['page' => $page, 'recordPerPage' => 50,
                'firstDate' => '2020-01-01T00:00:00.000Z',
                'lastDate'  => date('Y-m-d\T23:59:59.999\Z'),
            ]);
            foreach ($result['incomingInvoices'] ?? [] as $inv) {
                if (strtolower($inv['Id']) === strtolower($invoiceId)) {
                    return $this->formatStatus($inv);
                }
            }
            $total = $result['pagination']['totalRecord'] ?? 0;
            if ($page * 50 >= $total) break;
            $page++;
        }
        throw new \RuntimeException("Fatura bulunamadı: {$invoiceId}");
    }

    /**
     * Giden fatura durum sorgulama
     *
     * Responses dizisinde IsApplicationResponse=true olan kayıt varsa
     * alıcının kabul/red yanıtı gelmiş demektir.
     */
    public function outgoingStatus(string $invoiceId): array
    {
        $page = 1;
        while (true) {
            $result = $this->listOutgoing(['page' => $page, 'recordPerPage' => 50,
                'firstDate' => '2020-01-01T00:00:00.000Z',
                'lastDate'  => date('Y-m-d\T23:59:59.999\Z'),
            ]);
            foreach ($result['submittedInvoices'] ?? [] as $inv) {
                if (strtolower($inv['Id']) === strtolower($invoiceId)) {
                    return $this->formatStatus($inv);
                }
            }
            $total = $result['pagination']['totalRecord'] ?? 0;
            if ($page * 50 >= $total) break;
            $page++;
        }
        throw new \RuntimeException("Fatura bulunamadı: {$invoiceId}");
    }

    private function formatStatus(array $inv): array
    {
        $appResponses = array_filter(
            $inv['Responses'] ?? [],
            fn($r) => $r['IsApplicationResponse'] ?? false
        );

        return [
            'id'                 => $inv['Id'],
            'number'             => ($inv['TransectionSerial'] ?? '') . ($inv['TransectionNumber'] ?? ''),
            'profile'            => $inv['Profile'] ?? null,
            'state'              => $inv['State'] ?? null,
            'envelopeState'      => $inv['EnvelopeState'] ?? null,
            'objectionState'     => $inv['ObjectionState'] ?? null,
            'isPassedExpiryDate' => $inv['IsPassedExpiryDate'] ?? false,
            'gibResponses'       => array_values(array_filter($inv['Responses'] ?? [], fn($r) => !($r['IsApplicationResponse'] ?? false))),
            'applicationResponse'=> array_values($appResponses), // alıcı kabul/red yanıtı
            'isAccepted'         => !empty($appResponses),
            'customerTitle'      => $inv['CustomerTitle'] ?? null,
        ];
    }
}
