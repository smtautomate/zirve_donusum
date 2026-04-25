# Zirve Dönüşüm Multi-Portal E-Dönüşüm API Client

Türkiye'deki başlıca e-Dönüşüm portalları ve muhasebe entegratörleri için tek paket altında PHP/Laravel istemcileri.

| Portal / Entegratör | Facade | Auth | Açıklama |
|--------------------|--------|------|----------|
| **Mikro** | `MikroPortal` | Session (cookie) | eportal.mikrogrup.com |
| **Zirve Portal** | `ZirvePortal` | JWT Bearer | yeniportal.zirvedonusum.com |
| **GİB e-Arşiv** | `GibPortal` | Session token | earsivportal.efatura.gov.tr |
| **Paraşüt V4** | `Parasut` | OAuth2 password grant | api.parasut.com — 23 resource |
| **Logo Tiger** | `LogoTiger` | OAuth2 + Bearer | Logo Connect REST API |
| **Uyumsoft** | `Uyumsoft` | Basic + UserInfo | E-Fatura / E-Arşiv / E-İrsaliye |

**Mikro Portal:** https://eportal.mikrogrup.com

## Kurulum

```bash
composer require zirvedonusum/api-client
```

## Laravel Yapılandırma

### 1. Config dosyasını yayınla

```bash
php artisan vendor:publish --tag=zirve-donusum-config
```

### 2. .env dosyasına giriş bilgilerini ekle

Her kullanıcının kendi bilgileri `.env` dosyasına girilir:

```env
EMIKRO_BASE_URL=https://eportal.mikrogrup.com
EMIKRO_EMAIL=kullanici@email.com
EMIKRO_PASSWORD=sifreniz
EMIKRO_TIMEOUT=30
EMIKRO_VERIFY_SSL=true
EMIKRO_CACHE_SESSION=true
EMIKRO_SESSION_TTL=82800
```

> **Not:** Giriş bilgileri `.env` dosyasında tutulur, kaynak koduna yazılmaz. Her ortamın (development, staging, production) kendi `.env` dosyası olacağı için farklı hesaplar kullanılabilir.

## Kullanım

### Laravel (Facade)

```php
use ZirveDonusum\Facades\ZirveDonusum;

// Bağlantı testi
ZirveDonusum::testConnection(); // true/false

// Gelen e-faturalar
$faturalar = ZirveDonusum::invoices()->listIncoming();

// Giden e-faturalar
$faturalar = ZirveDonusum::invoices()->listOutgoing();

// E-Arşiv faturalar
$faturalar = ZirveDonusum::invoices()->listArchive();

// Fatura detayı
$fatura = ZirveDonusum::invoices()->get('fatura-id');

// PDF indir
$pdf = ZirveDonusum::invoices()->downloadPdf('fatura-id');

// Fatura kabul / red
ZirveDonusum::invoices()->accept('fatura-id');
ZirveDonusum::invoices()->reject('fatura-id', 'Hatalı tutar');

// Firma bilgisi
$firma = ZirveDonusum::company()->info();

// Mükellef sorgula (VKN ile)
$mukellef = ZirveDonusum::company()->lookupTaxpayer('1234567890');

// E-İrsaliye
$irsaliyeler = ZirveDonusum::despatch()->listIncoming();

// Custom endpoint
$sonuc = ZirveDonusum::http()->get('/custom/endpoint');
```

### Dependency Injection (Laravel)

```php
use ZirveDonusum\ZirveDonusumClient;

class FaturaController extends Controller
{
    public function index(ZirveDonusumClient $zirve)
    {
        return $zirve->invoices()->listIncoming([
            'startDate' => '2024-01-01',
            'endDate' => '2024-12-31',
        ]);
    }
}
```

### Standalone (Laravel dışı)

```php
$client = new \ZirveDonusum\ZirveDonusumClient([
    'base_url' => 'https://eportal.mikrogrup.com',
    'email'    => 'kullanici@email.com',
    'password' => 'sifreniz',
]);

$client->testConnection(); // true/false
$faturalar = $client->invoices()->listIncoming();
```

## Servisler

| Servis | Metod | Açıklama |
|--------|-------|----------|
| `invoices()` | `listIncoming()` | Gelen e-faturalar |
| `invoices()` | `listOutgoing()` | Giden e-faturalar |
| `invoices()` | `listArchive()` | E-Arşiv faturalar |
| `invoices()` | `get($id)` | Fatura detay |
| `invoices()` | `send($data)` | E-fatura gönder |
| `invoices()` | `createArchive($data)` | E-Arşiv fatura oluştur |
| `invoices()` | `downloadPdf($id)` | PDF indir |
| `invoices()` | `downloadXml($id)` | XML indir |
| `invoices()` | `accept($id)` | Faturayı kabul et |
| `invoices()` | `reject($id, $reason)` | Faturayı reddet |
| `invoices()` | `status($id)` | Fatura durumu |
| `company()` | `info()` | Firma bilgileri |
| `company()` | `lookupTaxpayer($vkn)` | Mükellef sorgula |
| `company()` | `checkEInvoiceRegistered($vkn)` | E-Fatura mükellefi mi? |
| `company()` | `profile()` | Kullanıcı profili |
| `company()` | `dashboard()` | Dashboard verileri |
| `despatch()` | `listIncoming()` | Gelen irsaliyeler |
| `despatch()` | `listOutgoing()` | Giden irsaliyeler |
| `despatch()` | `get($id)` | İrsaliye detay |
| `despatch()` | `send($data)` | İrsaliye gönder |
| `despatch()` | `respond($id, $action)` | İrsaliye yanıtla |
| `reports()` | `invoiceSummary()` | Fatura özet raporu |
| `reports()` | `monthly($yil, $ay)` | Aylık rapor |

## Teknik Detaylar (Mikro)

- **Auth:** Session-based (PHPSESSID cookie) — her istekte otomatik login
- **Content-Type:** Login `multipart/form-data`, diğer istekler `application/json`
- **Session cache:** Token dosyada saklanır, her istekte yeniden login olmaz
- **Auto-retry:** Session expire olursa otomatik yeniden login olur

---

## Paraşüt V4 (Parasut)

**Portal:** https://api.parasut.com — 23 resource (Bill, Customer, Supplier, Product, EBill, EArchive, ESmm, Inbox, Receipt, Bank, Salary, Tax, Employee, Account, Transaction, Warehouse, Waybill, StockMovement, Category, Tag, ApiHome, TrackableJob, Webhook).

### .env

```env
PARASUT_USERNAME=kullanici@email.com
PARASUT_PASSWORD=sifre
PARASUT_COMPANY_ID=12345
PARASUT_CLIENT_ID=
PARASUT_CLIENT_SECRET=
PARASUT_REDIRECT_URI=urn:ietf:wg:oauth:2.0:oob
```

> OAuth uygulaması: https://api.parasut.com/oauth/applications

### Kullanım

```php
use ZirveDonusum\Facades\Parasut;

// API Home / kullanıcı bilgisi
Parasut::apiHome()->me();

// Müşteri / Tedarikçi
Parasut::customers()->index(['filter' => ['name' => 'ACME']]);
Parasut::customers()->show(123);
Parasut::suppliers()->create($payload);

// Ürün
Parasut::products()->index();
Parasut::products()->create($payload);

// Satış faturası → e-Fatura / e-Arşiv'e dönüştürme
$bill = Parasut::bills()->create($billData);
Parasut::bills()->toEInvoice($bill['data']['id'], $eInvoicePayload);
Parasut::bills()->toEArchive($bill['data']['id'], $eArchivePayload);

// E-Fatura durum
Parasut::eBills()->show($id);
Parasut::trackableJobs()->show($jobId);

// Tahsilat / banka / vergi / maaş / hesap
Parasut::receipts()->create($data);
Parasut::banks()->index();
Parasut::taxes()->index();
Parasut::salaries()->create($data);

// Stok / depo / irsaliye
Parasut::warehouses()->index();
Parasut::waybills()->create($data);
Parasut::stockMovements()->index();

// Webhook
Parasut::webhooks()->create(['event' => 'sales_invoice/created', 'url' => 'https://...']);
```

---

## Logo Tiger REST API (LogoTiger)

**Portal:** Logo Connect REST API (lokal kurulum, varsayılan port `32001`).

### .env

```env
LOGO_TIGER_BASE_URL=http://localhost:32001/api/v1/
LOGO_TIGER_USERNAME=admin
LOGO_TIGER_PASSWORD=
LOGO_TIGER_FIRMA_NO=1
LOGO_TIGER_DONEM_NO=1
```

### Kullanım

```php
use ZirveDonusum\Facades\LogoTiger;

// Stok kartları (LG_ITEMS)
LogoTiger::items()->index();
LogoTiger::items()->show(1234);

// Cari kartları (LG_CLCARD)
LogoTiger::clCards()->findByTaxNumber('1234567890');
LogoTiger::clCards()->create($payload);

// Sipariş / fatura (LG_ORFICHE / LG_STFICHE)
LogoTiger::orders()->listSalesOrders();
LogoTiger::invoices()->listSalesInvoices();
LogoTiger::invoices()->createSalesInvoice($payload);

// Ödeme hareketleri (LG_PAYLINES)
LogoTiger::payments()->index();

// E-Fatura / E-Arşiv / E-İrsaliye
LogoTiger::eInvoice()->send($invoiceData);
LogoTiger::eInvoice()->status($id);
LogoTiger::eArchive()->send($invoiceData);
LogoTiger::eWaybill()->send($waybillData);
```

> `firmaNo` ve `donemNo` her query'ye otomatik eklenir; override etmek için `['firmaNo' => 2]` gönderin.

---

## Uyumsoft (Uyumsoft)

**Portal:** Test `efatura-test.uyumsoft.com.tr` / Prod `efatura.uyumsoft.com.tr`. Stateless: her istekte UserInfo + Basic Auth — token yok.

### .env

```env
UYUMSOFT_USERNAME=
UYUMSOFT_PASSWORD=
UYUMSOFT_TEST_MODE=true
```

### Kullanım

```php
use ZirveDonusum\Facades\Uyumsoft;

// Mükellef sorgu
Uyumsoft::eInvoice()->checkUser('1234567890');
Uyumsoft::company()->lookupTaxpayer('1234567890');

// E-Fatura
Uyumsoft::eInvoice()->send($invoiceData);
Uyumsoft::eInvoice()->status($uuid);
Uyumsoft::eInvoice()->accept($uuid);
Uyumsoft::eInvoice()->reject($uuid, 'Hatalı tutar');
Uyumsoft::eInvoice()->cancel($uuid, 'İade');
$pdf = Uyumsoft::eInvoice()->download($uuid, 'pdf');

// E-Arşiv
Uyumsoft::eArchive()->send($invoiceData);
Uyumsoft::eArchive()->cancel($uuid);

// E-İrsaliye
Uyumsoft::eWaybill()->send($waybillData);
Uyumsoft::eWaybill()->respond($uuid, 'accept');

// Rapor
Uyumsoft::reports()->monthly(2026, 4);
```

---

## Referanslar (esinlenilen kütüphaneler)

- **Paraşüt**: [theposeidonas/laravel-parasut-api](https://github.com/theposeidonas/laravel-parasut-api), [dervisgelmez/parasut](https://github.com/dervisgelmez/parasut), [yedincisenol/parasut](https://github.com/yedincisenol/parasut)
- **Logo Tiger**: [canberkdoger/logo-tiger-araclar](https://github.com/canberkdoger/logo-tiger-araclar) (SQL şema gezgini), [logedosoft/erpnext-logo-tiger-integration](https://github.com/logedosoft/erpnext-logo-tiger-integration)
- **Uyumsoft**: [alperuluses/Uyumsoft-E-fatura-Entegrasyon](https://github.com/alperuluses/Uyumsoft-E-fatura-Entegrasyon), [fatihaslamaci/GIBFramework](https://github.com/fatihaslamaci/GIBFramework)
