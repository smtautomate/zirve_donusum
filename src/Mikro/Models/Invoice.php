<?php

namespace ZirveDonusum\Mikro\Models;

/**
 * E-Fatura / E-Arşiv Fatura Modeli
 *
 * eMikro'nun /newInvoice/get response yapısına birebir karşılık gelir.
 * Builder pattern ile fatura oluşturmayı kolaylaştırır.
 *
 * Kullanım:
 *   $fatura = Invoice::create()
 *       ->type('EInvoice')
 *       ->profile('TEMELFATURA')
 *       ->typeCode('SATIS')
 *       ->currency('TRY')
 *       ->date('2026-03-16')
 *       ->customer('1234567890', 'Firma Adı', 'Vergi Dairesi')
 *       ->addLine('Ürün 1', 1, 100.00, 20)
 *       ->addLine('Hizmet', 1, 500.00, 20)
 *       ->description('Mart ayı hizmet bedeli')
 *       ->toArray();
 */
class Invoice
{
    private array $data;

    public function __construct(array $baseData = [])
    {
        $this->data = array_merge($this->defaults(), $baseData);
    }

    /**
     * Yeni fatura oluştur
     */
    public static function create(): self
    {
        return new self();
    }

    /**
     * Sunucudan gelen fatura verisinden oluştur
     * (newInvoice/get response'undan)
     */
    public static function fromResponse(array $response): self
    {
        // Mikro API farklı yapılarda dönebilir
        $invoiceData = $response['invoice']
            ?? $response['Invoice']
            ?? $response['data']
            ?? $response;

        return new self($invoiceData);
    }

    // ─── Temel Bilgiler ──────────────────────────────────────────────

    /**
     * Fatura tipi: EInvoice, EArchive
     */
    public function type(string $type): self
    {
        $this->data['InvoiceType'] = $type;
        return $this;
    }

    /**
     * Fatura profili: TEMELFATURA, TICARIFATURA, ISTISNA, IHRACAT, YOLCUBERABERFATURA, EARSIVFATURA
     */
    public function profile(string $profile): self
    {
        $this->data['Profile'] = $profile;
        return $this;
    }

    /**
     * Fatura tip kodu: SATIS, IADE, ISTISNA, OZELMATRAH, IHRACKAYITLI, TEVKIFAT, SGK
     */
    public function typeCode(string $code): self
    {
        $this->data['TypeCode'] = $code;
        return $this;
    }

    /**
     * Fatura seri numarası (prefix)
     */
    public function serial(string $serial): self
    {
        $this->data['Number']['Serial'] = $serial;
        return $this;
    }

    /**
     * Para birimi (TRY, USD, EUR vb.)
     */
    public function currency(string $code, ?string $name = null, ?string $id = null): self
    {
        $currencies = [
            'TRY' => 'Türk Lirası',
            'USD' => 'ABD Doları',
            'EUR' => 'Euro',
            'GBP' => 'İngiliz Sterlini',
        ];

        $this->data['Currency'] = [
            'Code' => $code,
            'Name' => $name ?? ($currencies[$code] ?? $code),
            'Id' => $id ?? $this->data['Currency']['Id'] ?? null,
        ];

        return $this;
    }

    /**
     * Döviz kuru (TRY dışı para birimleri için)
     */
    public function exchangeRate(float $rate): self
    {
        $this->data['ExchangeRate'] = $rate;
        return $this;
    }

    /**
     * Fatura tarihi (Y-m-d veya DateTime)
     */
    public function date(string $date): self
    {
        $this->data['Date'] = $date . 'T00:00:00';
        return $this;
    }

    /**
     * Vade tarihi
     */
    public function paymentDueDate(string $date): self
    {
        $this->data['PaymentDueDate'] = $date . 'T00:00:00';
        $this->data['AddPaymentTimeAndType'] = true;
        return $this;
    }

    /**
     * Açıklama / not
     */
    public function description(string $description): self
    {
        $this->data['Description'] = $description;
        return $this;
    }

    /**
     * Bedelsiz fatura mı
     */
    public function isFree(bool $free = true): self
    {
        $this->data['IsFree'] = $free;
        return $this;
    }

    // ─── Müşteri Bilgileri ───────────────────────────────────────────

    /**
     * Müşteri bilgilerini set et
     *
     * @param string     $taxNumber VKN veya TCKN
     * @param string     $title     Firma ünvanı
     * @param string|null $taxOffice Vergi dairesi
     * @param array|null  $aliasObj  checkEInvoiceRegistered()'dan gelen tam alias nesnesi
     *                               ['Alias'=>'urn:mail:...','Id'=>'...','Title'=>'...', ...]
     */
    public function customer(string $taxNumber, string $title, ?string $taxOffice = null, array|string|null $aliasObj = null): self
    {
        $this->data['Customer']['TaxNumber'] = $taxNumber;
        $this->data['Customer']['Title']     = $title;
        $this->data['Customer']['TaxOffice'] = $taxOffice;
        $this->data['Customer']['Name']      = '';
        $this->data['Customer']['Surname']   = '';
        $this->data['Customer']['DealerNo']  = '';

        if (is_array($aliasObj)) {
            $this->data['Customer']['Alias']         = $aliasObj;
            $this->data['Customer']['EInvoiceUsers'] = [$aliasObj];
        } elseif (is_string($aliasObj)) {
            // Geriye uyumluluk: sadece alias string verilmişse
            $this->data['Customer']['Alias']         = $aliasObj;
            $this->data['Customer']['EInvoiceUsers'] = [];
        }

        return $this;
    }

    /**
     * Müşteri adres bilgisi
     *
     * Country: API {"Code":"TR","Name":"TÜRKİYE"} nesne formatı bekler, string değil.
     */
    public function customerAddress(
        ?string $city = null,
        ?string $district = null,
        ?string $street = null,
        ?string $buildingNumber = null,
        ?string $postalZone = null,
        ?string $countryCode = 'TR',
        ?string $countryName = 'TÜRKİYE'
    ): self {
        $address = &$this->data['Customer']['Address'];
        if ($city) $address['City'] = $city;
        if ($district) $address['CitySubdivisionName'] = $district;
        if ($street) $address['Street'] = $street;
        if ($buildingNumber) $address['BuildingNumber'] = $buildingNumber;
        if ($postalZone) $address['PostalZone'] = $postalZone;
        if ($countryCode) {
            $address['Country'] = ['Code' => $countryCode, 'Name' => $countryName];
        }

        return $this;
    }

    /**
     * Müşteri iletişim bilgileri
     */
    public function customerContact(?string $email = null, ?string $phone = null): self
    {
        if ($email) {
            $this->data['Customer']['Email'] = $email;
            $this->data['Customer']['IsEmailSend'] = true;
        }
        if ($phone) {
            $this->data['Customer']['Phone'] = $phone;
        }

        return $this;
    }

    // ─── Fatura Kalemleri ────────────────────────────────────────────

    /**
     * Fatura kalemi ekle
     *
     * Gerçek API payload'ına göre (Chrome DevTools ile doğrulandı):
     *   - Alan adları: StockName, VATRate (Name/TaxRate değil)
     *   - Taxes: [] boş gönderilir, sunucu hesaplar
     *   - Discounts: her zaman bir eleman içerir (indirim olmasa da)
     *
     * @param string $name        Ürün/hizmet adı
     * @param float  $quantity    Miktar
     * @param float  $unitPrice   Birim fiyat (KDV hariç)
     * @param int    $vatRate     KDV oranı (0, 1, 10, 20)
     * @param string $unit        Birim kodu (C62=Adet, KGM=Kg, MTR=Metre, LTR=Litre vb.)
     * @param float  $discountAmount İndirim tutarı
     * @param string|null $stockCode Stok kodu
     * @param array  $withholding Tevkifat: ['rate'=>50,'code'=>'601','name'=>'...']
     */
    public function addLine(
        string $name,
        float $quantity,
        float $unitPrice,
        int $vatRate = 20,
        string $unit = 'C62',
        float $discountAmount = 0.0,
        ?string $stockCode = null,
        array $withholding = []
    ): self {
        $rowNumber   = count($this->data['Details']) + 1;
        $amount      = round($quantity * $unitPrice, 2);
        $kdvAmount   = round($amount * $vatRate / 100, 2);
        $totalAmount = round($amount + $kdvAmount, 2);

        $this->data['Details'][] = [
            'RowNumber'              => $rowNumber,
            'StockName'              => $name,
            'StockCode'              => $stockCode,
            'Unit'                   => $unit,
            'Quantity'               => $quantity,
            'UnitPrice'              => $unitPrice,
            'Amount'                 => $amount,
            'UnFixedAmount'          => $amount,
            'KdvAmount'              => $kdvAmount,
            'TotalAmount'            => $totalAmount,
            'VATRate'                => $vatRate,
            'Currency'               => $this->data['Currency']['Code'] ?? 'TRY',
            'Taxes'                  => [],           // sunucu hesaplar
            'Discounts'              => [[
                'Amount'         => $amount,
                'DiscountRate'   => 0,
                'DiscountAmount' => $discountAmount,
                'Description'    => null,
            ]],
            'IdisTagNumbers'         => [],
            'IsProductSelected'      => true,
            'isProductExist'         => $stockCode !== null,
            'ExemptionReason'        => null,
            'TaxAmountForTaxAssesment' => null,
            'FreightCharge'          => 0,
            'InnsuranceCharge'       => 0,
            'ContainerQuantity'      => 0,
            'ContainerNumber'        => null,
            'PackagingTypeCode'      => null,
            'DeliveryTerm'           => 'Belirtilmedi',
            'TransportMode'          => 'Belirtilmedi',
            'GTIP'                   => null,
        ];

        return $this;
    }

    /**
     * Ham fatura kalemi ekle (tüm alanları manual gir)
     */
    public function addRawLine(array $line): self
    {
        $this->data['Details'][] = $line;
        return $this;
    }

    // ─── Ödeme Bilgileri ─────────────────────────────────────────────

    /**
     * Ödeme tipi
     * KREDIKARTI_BANKAKARTI, EFTHAVALE, NAKIT, CEK_SENET, DIGER
     */
    public function paymentType(string $type): self
    {
        $this->data['Payment']['Type'] = $type;
        return $this;
    }

    /**
     * IBAN numarası
     */
    public function iban(string $iban): self
    {
        $this->data['IBANNo'] = $iban;
        return $this;
    }

    /**
     * Ödeme notu
     */
    public function paymentNote(string $note): self
    {
        $this->data['PaymentNote'] = $note;
        return $this;
    }

    // ─── Maliyet Kalemleri ───────────────────────────────────────────

    /**
     * Komisyon maliyeti
     */
    public function commissionCost(float $amount, float $percent = 0.0): self
    {
        $this->data['CommisionCost'] = $amount;
        $this->data['CommisionCostPercent'] = $percent;
        return $this;
    }

    /**
     * Nakliye maliyeti
     */
    public function freightCost(float $amount, float $percent = 0.0): self
    {
        $this->data['FreightCost'] = $amount;
        $this->data['FreightCostPercent'] = $percent;
        return $this;
    }

    // ─── İrsaliye Bağlantısı ─────────────────────────────────────────

    /**
     * İrsaliye bilgisi ekle
     */
    public function withDespatch(array $despatchData): self
    {
        $this->data['HasDispatch'] = true;
        $this->data['Dispatchs'][] = $despatchData;
        return $this;
    }

    // ─── Çıktı ───────────────────────────────────────────────────────

    /**
     * Fatura verisini array olarak döndür (API'ye gönderilecek format)
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * JSON olarak döndür
     */
    public function toJson(): string
    {
        return json_encode($this->data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    /**
     * Belirli bir alanı oku
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * Belirli bir alanı yaz
     */
    public function set(string $key, mixed $value): self
    {
        $this->data[$key] = $value;
        return $this;
    }

    /**
     * UUID'yi al
     */
    public function getUuid(): ?string
    {
        return $this->data['UUID'] ?? null;
    }

    // ─── Defaults ────────────────────────────────────────────────────

    private function defaults(): array
    {
        $now = date('Y-m-d\TH:i:s.000\Z');
        return [
            'Id'          => '',
            'UUID'        => null,
            'InvoiceType' => 'EInvoice',
            'Profile'     => 'TEMELFATURA',
            'TypeCode'    => 'SATIS',
            'Number'      => ['Serial' => '', 'Number' => 0],
            'Date'        => $now,
            'Time'        => date('H:i'),
            'Currency'    => ['Code' => 'TRY', 'Name' => 'Türk Lirası'],
            'ExchangeRate'=> 1,
            'ExchangeType'=> 'Buying',
            'IbanAccountCurrency'  => ['Code' => 'TRY', 'Name' => 'Türk Lirası'],
            'IbanAccountCurrency2' => ['Code' => 'TRY', 'Name' => 'Türk Lirası'],
            'Customer' => [
                'TaxNumber'   => null,
                'Title'       => null,
                'Name'        => '',
                'Surname'     => '',
                'TaxOffice'   => null,
                'DealerNo'    => '',
                'VehicleNumberPlate'           => '',
                'VehicleIdentificationNumber'  => '',
                'Alias'       => null,
                'Email'       => null,
                'IsEmailSend' => false,
                'Phone'       => null,
                'EInvoiceUsers' => [],
                'Address' => [
                    'Country'              => null, // ['Code'=>'TR','Name'=>'TÜRKİYE']
                    'City'                 => null,
                    'CitySubdivisionName'  => null,
                    'Street'               => null,
                    'BuildingNumber'       => null,
                    'PostalZone'           => null,
                ],
            ],
            'Passenger'        => (object) [],
            'TaxRepresentative'=> (object) [],
            'Payment'    => ['Type' => 'KREDIKARTI_BANKAKARTI', 'IsOnlineSale' => false],
            'Details'    => [],
            'CancelInfo' => [],
            'Dispatchs'  => [(object) []],
            'AdditionalDocuments' => [],
            'AdditionalFields'    => [],
            'AselsanAdditionalFields' => [],
            'SelectedTechnologies'=> ['IMEInumbers' => [''], 'MACnumbers' => ['']],
            'Description'         => null,
            'IsFree'              => false,
            'FromDespatch'        => false,
            'FromDespatchDate'    => $now,
            'IsDespatch'          => false,
            'HasDispatch'         => false,
            'BuyerCustomerNo'     => '',
            'PayableAmountForManuelSet' => 0,
            'IsSpecialBudgetPublicInstitution' => false,
            'PublicPayingCustomerCountry' => ['Code' => 'TR', 'Name' => 'TÜRKİYE'],
            'ExemptionReason'     => null,
            'IBANNo'              => null,
            'InvestmentIncentiveDocumentDate' => $now,
            'SubAccountId'        => null,
        ];
    }
}
