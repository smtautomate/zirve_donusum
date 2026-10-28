<?php

namespace ZirveDonusum\Tests\Mikro;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use ZirveDonusum\Exceptions\AuthenticationException;
use ZirveDonusum\Mikro\HttpClient;
use ZirveDonusum\Mikro\MikroClient;

/**
 * eMikro oturum çerezi (PHPSESSID) ve yeniden giriş davranışı.
 *
 * Her testte Middleware::history en içte (cookie middleware'den sonra) durur;
 * böylece sunucuya GERÇEKTEN giden Cookie başlığı incelenir.
 * Hata kaydı: Guzzle 7.15'te cache'ten yüklenen PHPSESSID ile girişte gelen yeni
 * PHPSESSID jar'da yan yana kalıyor, PHP ilkini (silinmiş oturum) okuyordu.
 */
final class HttpClientSessionTest extends TestCase
{
    private const BASE_URL = 'https://eportal.mikrogrup.com';
    private const ACCOUNT_X = 'aaaaaaaa-1111-4111-8111-111111111111';
    private const ACCOUNT_Y = 'bbbbbbbb-2222-4222-8222-222222222222';
    private const ACCOUNT_Z = 'cccccccc-3333-4333-8333-333333333333';

    private string $cacheDir;
    private FakeEmikroServer $server;
    private MockHandler $mock;

    /** @var array<int, array{request: RequestInterface}> */
    private array $history = [];

    protected function setUp(): void
    {
        $this->cacheDir = sys_get_temp_dir() . '/zirve-emikro-test-' . bin2hex(random_bytes(6));
        mkdir($this->cacheDir, 0700, true);
        $this->server = new FakeEmikroServer();
        $this->history = [];
    }

    protected function tearDown(): void
    {
        foreach (glob($this->cacheDir . '/*') ?: [] as $file) {
            unlink($file);
        }
        @rmdir($this->cacheDir);
    }

    // ─── 1) Çift PHPSESSID ───────────────────────────────────────────

    public function test_giris_eski_oturumu_gondermez_ve_sonraki_isteklerde_tek_yeni_phpsessid_gider(): void
    {
        $this->cacheSession('OLDSESSION');
        $this->server->validSession = 'OLDSESSION';
        $listPath = '/cp/' . self::ACCOUNT_X . '/inbox/GetIncomingInvoiceList';

        // EFaturaService akışı: cache'li istemci + DB'deki accountId + her seferinde login
        $client = $this->mikroClient([
            $this->server->login('NEWSESSION'),
            $this->server->json('/home/GetAccounts', $this->accounts(self::ACCOUNT_X)),
            $this->server->json($listPath, $this->incomingPage()),
        ]);
        $client->setAccountId(self::ACCOUNT_X);
        $client->login();

        $this->assertSame(['NEWSESSION'], $this->sessionIdsSent(1), 'GetAccounts tek ve yeni PHPSESSID ile gitmeli');
        $this->assertSame([], $this->sessionIdsSent(0), 'login POST eski PHPSESSID göndermemeli');

        $result = $client->invoices()->listIncoming(['page' => 1, 'recordPerPage' => 5]);

        $this->assertSame(['NEWSESSION'], $this->sessionIdsSent(2), 'liste isteği tek ve yeni PHPSESSID ile gitmeli');
        $this->assertSame(self::ACCOUNT_X, $client->getAccountId());
        $this->assertCount(1, $result['incomingInvoices']);
        $this->assertSame(9, $result['pagination']['totalRecord']);
        $this->assertSame(['POST /home/loginEmikro', 'GET /home/GetAccounts', 'GET ' . $listPath], $this->requestLines());
        $this->assertSame(0, $this->mock->count());
        $this->assertSame('NEWSESSION', $this->cachedSession()['session_id'], 'cache yeni oturumu tutmalı');
    }

    public function test_cacheten_gelen_oturumu_sunucu_yenilerse_sonraki_istekte_tek_ve_yeni_cerez_gider(): void
    {
        $this->cacheSession('OLDSESSION');
        $this->server->validSession = 'OLDSESSION';

        // Login yok: istemci cache'teki oturumla başlar, sunucu ilk istekte oturumu yeniler
        $client = $this->httpClient([
            $this->server->json('/cp/' . self::ACCOUNT_X . '/a', ['ok' => 'a'], renewTo: 'RENEWED'),
            $this->server->json('/cp/' . self::ACCOUNT_X . '/b', ['ok' => 'b']),
        ]);

        $this->assertSame(['ok' => 'a'], $client->get($client->cpPath('a')));
        $second = $this->attempt(fn () => $client->get($client->cpPath('b')));

        $this->assertSame(['OLDSESSION'], $this->sessionIdsSent(0));
        $this->assertSame(['RENEWED'], $this->sessionIdsSent(1), 'yenilenen oturumdan sonra tek ve yeni çerez gitmeli');
        $this->assertSame(['ok' => 'b'], $second);
        $this->assertSame(0, $this->mock->count());
    }

    public function test_sunucu_domainli_phpsessid_ayarlarsa_yalniz_en_yeni_cerez_gider(): void
    {
        // Domain'li (HostOnly=false) ve Domain'siz çerez her Guzzle sürümünde yan yana kalır → emniyet ağı
        $client = $this->httpClient([
            $this->server->login('NEW1'),
            $this->server->json('/home/GetAccounts', $this->accounts(self::ACCOUNT_X)),
            $this->server->json('/cp/' . self::ACCOUNT_X . '/a', ['ok' => 'a'], renewTo: 'DOMAINED', renewDomain: 'mikrogrup.com'),
            $this->server->json('/cp/' . self::ACCOUNT_X . '/b', ['ok' => 'b']),
        ]);

        $client->login();
        $client->get($client->cpPath('a'));
        $second = $this->attempt(fn () => $client->get($client->cpPath('b')));

        $this->assertSame(['DOMAINED'], $this->sessionIdsSent(3));
        $this->assertSame(['ok' => 'b'], $second);
        $this->assertSame(0, $this->mock->count());
    }

    public function test_logout_jari_yerinde_temizler(): void
    {
        $client = $this->httpClient([
            $this->server->login('NEW1'),
            $this->server->json('/home/GetAccounts', $this->accounts(self::ACCOUNT_X)),
            new Response(200, ['Content-Type' => 'application/json'], '{}'),
        ]);

        $client->login();
        $client->logout();

        $this->assertCount(0, $client->getCookieJar());
        // Guzzle istemcisi aynı jar'ı kullanıyor olmalı: doğrudan istekte çerez gitmez
        $client->getHttpClient()->get('/ping');
        $this->assertSame('', $this->history[2]['request']->getHeaderLine('Cookie'));
        $this->assertNull($client->getAccountId());
        $this->assertNull($this->cachedSession());
    }

    // ─── 2) accountId çözümü ─────────────────────────────────────────

    public function test_getaccounts_giris_sayfasi_donerse_bilinen_accountId_korunur(): void
    {
        $client = $this->httpClient([
            $this->server->login('NEW1'),
            FakeEmikroServer::redirectToLogin(),  // GET /home/GetAccounts → 302 /
            FakeEmikroServer::loginPage(),        // GET / (yönlendirme izlendi)
            FakeEmikroServer::loginPage(),        // GET /accounts
        ]);
        $client->setAccountId(self::ACCOUNT_X);

        $client->login();

        $this->assertSame(self::ACCOUNT_X, $client->getAccountId());
        $this->assertSame(['POST /home/loginEmikro', 'GET /home/GetAccounts', 'GET /', 'GET /accounts'], $this->requestLines());

        $getAccounts = $this->history[1]['request'];
        $this->assertSame('XMLHttpRequest', $getAccounts->getHeaderLine('X-Requested-With'));
        $this->assertSame('application/json', $getAccounts->getHeaderLine('Accept'));
        $this->assertSame(self::ACCOUNT_X, $this->cachedSession()['account_id']);
    }

    /**
     * @return array<string, array{0: ?string, 1: string}>
     */
    public static function accountChoices(): array
    {
        return [
            'mevcut hesap listede → korunur' => [self::ACCOUNT_X, self::ACCOUNT_X],
            'mevcut hesap listede değil → ilki' => [self::ACCOUNT_Z, self::ACCOUNT_Y],
            'hesap bilinmiyor → ilki' => [null, self::ACCOUNT_Y],
        ];
    }

    #[DataProvider('accountChoices')]
    public function test_getaccounts_coklu_hesap_secimi(?string $known, string $expected): void
    {
        $client = $this->httpClient([
            $this->server->login('NEW1'),
            $this->server->json('/home/GetAccounts', $this->accounts(self::ACCOUNT_Y, self::ACCOUNT_X)),
        ]);
        if ($known !== null) {
            $client->setAccountId($known);
        }

        $client->login();

        $this->assertSame($expected, $client->getAccountId());
        $this->assertSame(['POST /home/loginEmikro', 'GET /home/GetAccounts'], $this->requestLines());
        $this->assertSame(['NEW1'], $this->sessionIdsSent(1));
    }

    public function test_getaccounts_bos_ise_accounts_htmlden_mevcut_hesap_tercih_edilir(): void
    {
        $html = '<html><body><a href="/cp/' . self::ACCOUNT_Y . '/dashboard">Y</a>'
            . '<a href="/cp/' . self::ACCOUNT_X . '/dashboard">X</a></body></html>';
        $client = $this->httpClient([
            $this->server->login('NEW1'),
            $this->server->json('/home/GetAccounts', ['accounts' => []]),
            new Response(200, ['Content-Type' => 'text/html'], $html),
        ]);
        $client->setAccountId(self::ACCOUNT_X);

        $client->login();

        $this->assertSame(self::ACCOUNT_X, $client->getAccountId());
    }

    // ─── 3) Oturum düşmesi ve yeniden giriş ──────────────────────────

    public function test_liste_giris_sayfasi_donerse_tek_yeniden_giris_ve_tekrar_json_doner(): void
    {
        $listPath = '/cp/' . self::ACCOUNT_X . '/inbox/GetIncomingInvoiceList';
        $client = $this->mikroClient([
            $this->server->login('NEW1'),
            $this->server->json('/home/GetAccounts', $this->accounts(self::ACCOUNT_X)),
            $this->server->expireSession(),
            $this->server->login('NEW2'),
            $this->server->json('/home/GetAccounts', $this->accounts(self::ACCOUNT_X)),
            $this->server->json($listPath, $this->incomingPage()),
        ]);
        $client->setAccountId(self::ACCOUNT_X);
        $client->login();

        $result = $client->invoices()->listIncoming();

        $this->assertArrayHasKey('incomingInvoices', $result);
        $this->assertArrayNotHasKey('raw', $result);
        $this->assertSame(2, $this->countLogins());
        $this->assertSame(['NEW2'], $this->sessionIdsSent(5));
        $this->assertSame(0, $this->mock->count());
    }

    public function test_tekrar_da_giris_sayfasi_donerse_sessiz_basari_yerine_oturum_hatasi_atar(): void
    {
        $listPath = '/cp/' . self::ACCOUNT_X . '/inbox/GetIncomingInvoiceList';
        $client = $this->mikroClient([
            $this->server->login('NEW1'),
            $this->server->json('/home/GetAccounts', $this->accounts(self::ACCOUNT_X)),
            FakeEmikroServer::loginPage(),
            $this->server->login('NEW2'),
            $this->server->json('/home/GetAccounts', $this->accounts(self::ACCOUNT_X)),
            FakeEmikroServer::loginPage(),
        ]);
        $client->setAccountId(self::ACCOUNT_X);
        $client->login();

        $outcome = $this->attempt(fn () => $client->invoices()->listIncoming());

        $this->assertInstanceOf(AuthenticationException::class, $outcome, 'giriş sayfası HTML\'i liste diye dönmemeli');
        $this->assertStringContainsString('oturum açılamadı', $outcome->getMessage());
        $this->assertStringContainsString($listPath, $outcome->getMessage());
        $this->assertSame(2, $this->countLogins(), 'tek yeniden giriş yapılmalı');
        $this->assertSame(0, $this->mock->count());
        $this->assertNull($this->cachedSession(), 'geçersiz oturum cache\'te kalmamalı');
    }

    public function test_302_ile_giris_sayfasina_yonlendirme_oturum_dusmesi_sayilir(): void
    {
        $path = '/cp/' . self::ACCOUNT_X . '/a';
        $client = $this->httpClient([
            $this->server->login('NEW1'),
            $this->server->json('/home/GetAccounts', $this->accounts(self::ACCOUNT_X)),
            FakeEmikroServer::redirectToLogin(),  // 302 → /
            FakeEmikroServer::loginPage(),        // Guzzle yönlendirmeyi izler
            $this->server->login('NEW2'),
            $this->server->json('/home/GetAccounts', $this->accounts(self::ACCOUNT_X)),
            $this->server->json($path, ['ok' => true]),
        ]);
        $client->login();

        $this->assertSame(['ok' => true], $client->get($path));
        $this->assertSame(
            ['POST /home/loginEmikro', 'GET /home/GetAccounts', 'GET ' . $path, 'GET /', 'POST /home/loginEmikro', 'GET /home/GetAccounts', 'GET ' . $path],
            $this->requestLines()
        );
    }

    public function test_indirmede_giris_sayfasi_once_yeniden_giris_sonra_dosya(): void
    {
        $pdfPath = '/cp/' . self::ACCOUNT_X . '/outbox/DownloadPdf';
        $client = $this->httpClient([
            $this->server->login('NEW1'),
            $this->server->json('/home/GetAccounts', $this->accounts(self::ACCOUNT_X)),
            $this->server->expireSession(),
            $this->server->login('NEW2'),
            $this->server->json('/home/GetAccounts', $this->accounts(self::ACCOUNT_X)),
            $this->server->file($pdfPath, '%PDF-1.7 sahte'),
        ]);
        $client->login();

        $this->assertSame('%PDF-1.7 sahte', $client->download($pdfPath, ['id' => 'abc']));
        $this->assertSame(2, $this->countLogins());
    }

    public function test_indirmede_tekrar_da_giris_sayfasi_donerse_oturum_hatasi_atar(): void
    {
        $client = $this->httpClient([
            $this->server->login('NEW1'),
            $this->server->json('/home/GetAccounts', $this->accounts(self::ACCOUNT_X)),
            FakeEmikroServer::loginPage(),
            $this->server->login('NEW2'),
            $this->server->json('/home/GetAccounts', $this->accounts(self::ACCOUNT_X)),
            FakeEmikroServer::loginPage(),
        ]);
        $client->login();

        $outcome = $this->attempt(fn () => $client->download('/cp/' . self::ACCOUNT_X . '/outbox/DownloadPdf'));

        $this->assertInstanceOf(AuthenticationException::class, $outcome);
        $this->assertStringContainsString('oturum açılamadı', $outcome->getMessage());
        $this->assertSame(2, $this->countLogins());
    }

    public function test_giris_sayfasi_olmayan_html_yanit_raw_olarak_kalir(): void
    {
        $html = '<html><body><h1>Rapor</h1></body></html>';
        $client = $this->httpClient([
            $this->server->login('NEW1'),
            $this->server->json('/home/GetAccounts', $this->accounts(self::ACCOUNT_X)),
            new Response(200, ['Content-Type' => 'text/html; charset=utf-8'], $html),
        ]);
        $client->login();

        $this->assertSame(['raw' => $html], $client->get($client->cpPath('rapor')));
        $this->assertSame(1, $this->countLogins());
    }

    // ─── 4) Giriş hata mesajları ─────────────────────────────────────

    /**
     * @return array<string, array{0: Response, 1: string[], 2: string[]}>
     */
    public static function loginFailures(): array
    {
        return [
            'Cloudflare engel sayfası' => [
                FakeEmikroServer::cloudflareBlock(),
                ['HTTP 403', 'Cloudflare erişim engeli'],
                ['askıya alınmış'],
            ],
            'eMikro 403 (Cloudflare değil)' => [
                new Response(403, ['Content-Type' => 'application/json'], '{"message":"Forbidden"}'),
                ['HTTP 403', 'Hesap askıya alınmış'],
                ['Cloudflare'],
            ],
            'istek limiti' => [
                new Response(429, ['Content-Type' => 'application/json'], '{"message":"İstek limiti aşıldı"}'),
                ['HTTP 429', 'İstek limiti aşıldı'],
                [],
            ],
        ];
    }

    #[DataProvider('loginFailures')]
    public function test_giris_hatasi_mesajlari(Response $response, array $mustContain, array $mustNotContain): void
    {
        $client = $this->httpClient([$response]);

        $outcome = $this->attempt(fn () => $client->login());

        $this->assertInstanceOf(AuthenticationException::class, $outcome);
        foreach ($mustContain as $text) {
            $this->assertStringContainsString($text, $outcome->getMessage());
        }
        foreach ($mustNotContain as $text) {
            $this->assertStringNotContainsString($text, $outcome->getMessage());
        }
        $this->assertFalse($client->isAuthenticated());
    }

    public function test_handler_handlerstack_olmali(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new HttpClient(['base_url' => self::BASE_URL, 'cache_session' => false, 'handler' => fn () => null]);
    }

    // ─── Yardımcılar ─────────────────────────────────────────────────

    private function stack(array $queue): HandlerStack
    {
        $this->mock = new MockHandler($queue);
        $stack = HandlerStack::create($this->mock);
        // push en içe ekler (HandlerStack::resolve ters sırada sarar) → Cookie başlığı eklendikten sonra kaydeder
        $stack->push(Middleware::history($this->history), 'history');

        return $stack;
    }

    private function config(array $queue): array
    {
        return [
            'base_url' => self::BASE_URL,
            'email' => 'muhasebe@example.com',
            'password' => 'gizli',
            'cache_dir' => $this->cacheDir,
            'handler' => $this->stack($queue),
        ];
    }

    private function httpClient(array $queue): HttpClient
    {
        return new HttpClient($this->config($queue));
    }

    private function mikroClient(array $queue): MikroClient
    {
        return new MikroClient($this->config($queue));
    }

    private function cacheSession(string $sessionId, ?string $accountId = self::ACCOUNT_X): void
    {
        file_put_contents($this->cacheDir . '/emikro_session.json', json_encode([
            'session_id' => $sessionId,
            'account_id' => $accountId,
            'expires_at' => time() + 3600,
        ]));
    }

    private function cachedSession(): ?array
    {
        $file = $this->cacheDir . '/emikro_session.json';

        return is_file($file) ? json_decode((string) file_get_contents($file), true) : null;
    }

    /** @return string[] */
    private function sessionIdsSent(int $index): array
    {
        $this->assertArrayHasKey($index, $this->history, "#{$index} numaralı istek gönderilmemiş");

        return FakeEmikroServer::sessionIdsIn($this->history[$index]['request']);
    }

    /** @return string[] */
    private function requestLines(): array
    {
        return array_map(
            static fn (array $entry): string => $entry['request']->getMethod() . ' ' . $entry['request']->getUri()->getPath(),
            $this->history
        );
    }

    private function countLogins(): int
    {
        return count(array_filter($this->requestLines(), static fn (string $line): bool => $line === 'POST /home/loginEmikro'));
    }

    /** Sonucu ya da atılan hatayı döndürür; hata olsa bile gönderilen çerezler doğrulanabilsin. */
    private function attempt(callable $fn): mixed
    {
        try {
            return $fn();
        } catch (\Throwable $e) {
            return $e;
        }
    }

    private function accounts(string ...$ids): array
    {
        return ['accounts' => array_map(static fn (string $id): array => [
            'id' => $id,
            'taxNumber' => '1234567890',
            'title' => 'Örnek Ticaret A.Ş.',
            'state' => 'Active',
        ], $ids)];
    }

    private function incomingPage(): array
    {
        return [
            'incomingInvoices' => [[
                'Id' => 'f1e2d3c4-0000-4000-8000-000000000001',
                'UserTitle' => 'Tedarikçi Ltd. Şti.',
                'UserTaxIdentification' => '9876543210',
                'LineExtensionAmount' => 1000,
                'TransectionSerial' => 'ABC',
                'TransectionNumber' => '2026000000123',
            ]],
            'pagination' => ['page' => 1, 'totalPage' => 2, 'totalRecord' => 9, 'recordPerPage' => 5],
            'invoiceChangeableAccounts' => [],
        ];
    }
}
