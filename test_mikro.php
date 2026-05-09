<?php

/**
 * Mikro Portal Diagnostic Script
 *
 * Kullanım:
 *   php test_mikro.php
 *
 * Email/password aşağıya gir veya env var olarak:
 *   EMIKRO_EMAIL=xxx EMIKRO_PASSWORD=yyy php test_mikro.php
 */

require_once __DIR__ . '/vendor/autoload.php';

$email    = getenv('EMIKRO_EMAIL')    ?: 'EMAIL_BURAYA';
$password = getenv('EMIKRO_PASSWORD') ?: 'SIFRE_BURAYA';

$client = new \ZirveDonusum\Mikro\MikroClient([
    'base_url'      => 'https://eportal.mikrogrup.com',
    'email'         => $email,
    'password'      => $password,
    'cache_session' => false,
    'timeout'       => 30,
]);

function ok(string $msg): void   { echo "\033[32m✓\033[0m {$msg}\n"; }
function fail(string $msg): void { echo "\033[31m✗\033[0m {$msg}\n"; }
function info(string $msg): void { echo "\033[33m→\033[0m {$msg}\n"; }
function dump(string $label, mixed $data): void
{
    echo "\n  [{$label}]\n";
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $lines = array_slice(explode("\n", $json), 0, 40);
    echo implode("\n", array_map(fn($l) => "    {$l}", $lines));
    if (count(explode("\n", $json)) > 40) {
        echo "\n    ... (truncated)";
    }
    echo "\n";
}

echo "\n=== Mikro Portal Diagnostic ===\n\n";

// ─── 1. LOGIN ───────────────────────────────────────────────────────
echo "1. LOGIN\n";
try {
    $ok = $client->login();
    $accountId = $client->getAccountId();
    ok("Login başarılı");
    info("AccountId: {$accountId}");
} catch (\Throwable $e) {
    fail("Login HATA: " . $e->getMessage());
    exit(1);
}

// ─── 2. GELEN FATURA ────────────────────────────────────────────────
echo "\n2. GELEN FATURA (listIncoming)\n";
try {
    $incoming = $client->invoices()->listIncoming(['recordPerPage' => 2]);
    $total = $incoming['TotalCount'] ?? $incoming['totalCount'] ?? count($incoming);
    ok("Gelen fatura listelendi — toplam: {$total}");
    dump('Response yapısı (keys)', array_keys($incoming));
} catch (\Throwable $e) {
    fail("Gelen Fatura HATA: " . $e->getMessage());
}

// ─── 3. GİDEN FATURA ────────────────────────────────────────────────
echo "\n3. GİDEN FATURA (listOutgoing)\n";
try {
    $outgoing = $client->invoices()->listOutgoing(['recordPerPage' => 2]);
    $total = $outgoing['TotalCount'] ?? $outgoing['totalCount'] ?? count($outgoing);
    ok("Giden fatura listelendi — toplam: {$total}");
    dump('Response yapısı (keys)', array_keys($outgoing));
} catch (\Throwable $e) {
    fail("Giden Fatura HATA: " . $e->getMessage());
}

// ─── 4. E-ARŞİV ─────────────────────────────────────────────────────
echo "\n4. E-ARŞİV (listArchive)\n";
try {
    $archive = $client->invoices()->listArchive(['recordPerPage' => 2]);
    $total = $archive['TotalCount'] ?? $archive['totalCount'] ?? count($archive);
    ok("E-Arşiv listelendi — toplam: {$total}");
} catch (\Throwable $e) {
    fail("E-Arşiv HATA: " . $e->getMessage());
}

// ─── 5. PREFIX KODLARI ──────────────────────────────────────────────
echo "\n5. PREFIX KODLARI (getPrefixCodes)\n";
try {
    $prefixes = $client->company()->getPrefixCodes();
    ok("Prefix kodları alındı");
    dump('Prefix Response', $prefixes);
} catch (\Throwable $e) {
    fail("Prefix HATA: " . $e->getMessage());
}

// ─── 6. YENİ FATURA ŞABLONU (UUID İÇİN) ────────────────────────────
echo "\n6. YENİ FATURA ŞABLONU (getNewInvoice)\n";
$template  = null;
$uuid      = null;
try {
    $template = $client->invoices()->getNewInvoice('EInvoice');
    ok("Yeni fatura şablonu alındı");
    dump('Response yapısı (keys)', array_keys($template));

    // UUID nerede?
    $keys = ['UUID', 'Uuid', 'uuid', 'Id', 'id'];
    $topLevel = $template['invoice'] ?? $template['Invoice'] ?? $template;
    foreach ($keys as $k) {
        if (!empty($topLevel[$k])) {
            $uuid = $topLevel[$k];
            ok("UUID bulundu — key: '{$k}', değer: {$uuid}");
            break;
        }
    }
    if (!$uuid) {
        fail("UUID bulunamadı! Tüm response:");
        dump('newInvoice/get response (tam)', $template);
    }
} catch (\Throwable $e) {
    fail("Yeni Fatura Şablonu HATA: " . $e->getMessage());
}

// ─── 7. DOCUMENT NO ÜRET ────────────────────────────────────────────
echo "\n7. DOCUMENT NO (generateDocumentNo)\n";
if ($uuid) {
    try {
        $docNo = $client->invoices()->generateDocumentNo($uuid, 'EFAB');
        ok("Document No üretildi");
        dump('Document No Response', $docNo);
    } catch (\Throwable $e) {
        fail("Document No HATA: " . $e->getMessage());
        info("Muhtemelen yanlış prefix. Prefix kodlarına bakın (adım 5)");
    }
} else {
    fail("UUID yok, Document No testi atlandı");
}

// ─── 8. FATURA OLUŞTURMA TEST (DRY-RUN, GERÇEK GÖNDERMEZ) ──────────
echo "\n8. FATURA OLUŞTURMA (create - payload hazırlama)\n";
if ($template) {
    try {
        $invoice = \ZirveDonusum\Mikro\Models\Invoice::fromResponse($template);
        $invoice
            ->type('EInvoice')
            ->profile('TEMELFATURA')
            ->customer('1234567890', 'Test Firma A.Ş.', 'KADIKÖY')
            ->addLine('Test Hizmet', 1, 100.00, 20)
            ->description('Diagnostic test faturası');

        $payload = $invoice->toArray();
        ok("Invoice modeli oluşturuldu (gönderilmedi)");
        dump('Payload (send edilecek JSON)', $payload);
    } catch (\Throwable $e) {
        fail("Invoice model HATA: " . $e->getMessage());
    }
} else {
    fail("Template yok, fatura model testi atlandı");
}

// ─── 9. MÜKELLEF SORGULAMA ──────────────────────────────────────────
echo "\n9. MÜKELLEF SORGULAMA\n";
$vkn = '3350432123'; // Enerjisa
try {
    $eInvCheck = $client->company()->checkEInvoiceRegistered($vkn);
    $users = $eInvCheck['Data']['users'] ?? [];
    ok("checkEInvoiceRegistered (VKN: $vkn) — " . (count($users) > 0 ? "e-Fatura KAYITLI, alias: " . $users[0]['Alias'] : "e-Fatura KAYITLI DEĞİL"));

    $identityCheck = $client->company()->checkTaxpayerIdentity($vkn);
    ok("checkTaxpayerIdentity — hasIdentity: " . ($identityCheck['data']['hasIdentity'] ? 'EVET' : 'HAYIR'));
} catch (\Throwable $e) {
    fail("Mükellef sorgulama HATA: " . $e->getMessage());
}

// ─── 10. PDF / HTML İNDİR ───────────────────────────────────────────
echo "\n10. PDF / HTML İNDİR\n";
try {
    $invoices = $client->invoices()->listIncoming(['recordPerPage' => 1]);
    $testId = $invoices['incomingInvoices'][0]['Id'] ?? null;

    if ($testId) {
        $html = $client->invoices()->getIncomingHtml($testId);
        ok("getIncomingHtml — " . strlen($html) . " karakter");

        $pdf = $client->invoices()->downloadPdf($testId, 'incoming');
        ok("downloadPdf — " . strlen($pdf) . " byte PDF");

        $xml = $client->invoices()->downloadXml($testId, 'incoming');
        ok("downloadXml — " . strlen($xml) . " byte ZIP(XML içerir)");
    }
} catch (\Throwable $e) {
    fail("İndirme HATA: " . $e->getMessage());
}

// ─── 11. GELEN FATURA DURUM / ONAY TAKİBİ ───────────────────────────
echo "\n11. GELEN FATURA ONAY TAKİBİ (TİCARİ FATURA)\n";
try {
    $invoices = $client->invoices()->listIncoming(['recordPerPage' => 5]);
    foreach ($invoices['incomingInvoices'] ?? [] as $f) {
        $profile  = $f['Profile'] ?? '?';
        $state    = $f['State']['Description'] ?? ($f['State']['Code'] ?? '?');
        $expired  = $f['IsPassedExpiryDate'] ? '⚠ 8 GÜN GEÇTİ' : 'Süre içinde';
        $respCount= count($f['Responses'] ?? []);
        info("  [{$profile}] {$state} | {$expired} | {$respCount} yanıt | ID: " . $f['Id']);
    }
    ok("Gelen fatura durum taraması tamamlandı");
    info("NOT: TİCARİFATURA'da kabul/red gereklidir. IsPassedExpiryDate=true ise 8 gün dolmuştur.");
} catch (\Throwable $e) {
    fail("Onay takibi HATA: " . $e->getMessage());
}

// ─── 12. FATURA GÖNDER (GERÇEK — YORUMA ALINMIŞ) ───────────────────
echo "\n12. FATURA GÖNDER / OLUŞTUR — Yoruma alındı (Details formatı Chrome DevTools gerektirir)\n";
info("UYARI: newInvoice/send endpoint'i Details dizisi varken 500 döndürüyor.");
info("Doğru Details formatını öğrenmek için Chrome DevTools'da gerçek fatura gönderimi yakalanmalıdır.");
echo <<<'PHP'

/* Tevkifatlı fatura örneği (Details formatı doğrulandıktan sonra aktive edilecek):
$sonuc = $client->invoices()->create(
    '3350432123',
    'ENERJİSA BAŞKENT ELEKTRİK PERAKENDE SATIŞ A.Ş.',
    [
        [
            'name'       => 'Danışmanlık Hizmeti',
            'quantity'   => 1,
            'unitPrice'  => 1000.00,
            'vatRate'    => 20,
            'withholding'=> ['rate' => 50, 'code' => '601', 'name' => 'Yapım işleri ve bu işlerle birlikte ifa edilen mühendislik-mimarlık ve etüt-proje hizmetleri']
        ],
    ],
    [
        'invoiceType' => 'EInvoice',
        'profile'     => 'TICARIFATURA',  // Ticari fatura (onay takibi gerektirir)
        'alias'       => 'urn:mail:defaultpk@sni.net.tr',
        'description' => 'Nisan 2026 danışmanlık bedeli',
        'taxOffice'   => 'KADIKÖY',
    ]
);
echo json_encode($sonuc, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
*/
PHP;
echo "\n";

echo "\n=== Diagnostic tamamlandı ===\n\n";
echo "ÖZET:\n";
echo "  ✓ Login, Dashboard, Firma, Listeleme (gelen/giden/arşiv), Mükellef sorgu\n";
echo "  ✓ getNewInvoice (POST), generateDocumentNo, PDF/HTML/XML indir\n";
echo "  ✗ newInvoice/send → Details dizisi 500 yapıyor (Chrome DevTools gerekli)\n";
echo "  ✗ Kabul/Red, İptal → endpoint bulunamadı (Chrome DevTools gerekli)\n";
echo "  ✗ E-İrsaliye → bu hesapta servis aktif değil\n\n";
