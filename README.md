# Zirve Dönüşüm / eMikro API Client

eMikro (Mikrogrup) E-Dönüşüm Portal API istemcisi. Laravel ve standalone PHP projelerinde kullanılabilir.

**Portal:** https://eportal.mikrogrup.com

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

## Teknik Detaylar

- **Auth:** Session-based (PHPSESSID cookie) — her istekte otomatik login
- **Content-Type:** Login `multipart/form-data`, diğer istekler `application/json`
- **Session cache:** Token dosyada saklanır, her istekte yeniden login olmaz
- **Auto-retry:** Session expire olursa otomatik yeniden login olur
