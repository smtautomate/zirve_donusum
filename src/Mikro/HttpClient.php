<?php

namespace ZirveDonusum\Mikro;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\HandlerStack;
use Psr\Http\Message\ResponseInterface;
use ZirveDonusum\Mikro\Auth\SessionManager;
use ZirveDonusum\Exceptions\ApiException;
use ZirveDonusum\Exceptions\AuthenticationException;

class HttpClient
{
    /** eMikro (PHP) oturum çerezi */
    private const SESSION_COOKIE = 'PHPSESSID';

    /** eMikro giriş sayfasını tanıtan işaretler (Guzzle 302 → / yönlendirmesini izlediğinde 200 HTML gelir) */
    private const LOGIN_PAGE_MARKERS = ['loginEmikro', 'e-Portal Girişi'];

    /** Cloudflare engel sayfası işaretleri (küçük harf) */
    private const CLOUDFLARE_MARKERS = ['cloudflare', 'attention required', 'cf-ray', '<!--[if lt ie 7]>'];

    private GuzzleClient $http;
    private SessionManager $sessionManager;
    private CookieJar $cookieJar;
    private string $baseUrl;
    private string $host;
    private string $email;
    private string $password;
    private int $timeout;
    private bool $authenticated = false;

    /**
     * eMikro'nun tüm API çağrıları /cp/{accountId}/... altında.
     * Login sonrası GetAccounts'tan alınır ya da setAccountId ile verilir.
     */
    private ?string $accountId = null;

    /**
     * @param array $config base_url, email, password, timeout, verify_ssl, cache_dir, cache_session,
     *                      handler (isteğe bağlı GuzzleHttp\HandlerStack — test/özel taşıma katmanı;
     *                      çerez desteği için HandlerStack::create($handler) ile kurulmalı)
     */
    public function __construct(array $config)
    {
        $this->baseUrl = rtrim($config['base_url'] ?? 'https://eportal.mikrogrup.com', '/');
        $this->host = strtolower((string) parse_url($this->baseUrl, PHP_URL_HOST));
        $this->email = $config['email'] ?? '';
        $this->password = $config['password'] ?? '';
        $this->timeout = $config['timeout'] ?? 30;

        $cacheDir = $config['cache_dir'] ?? sys_get_temp_dir() . '/emikro';
        $this->sessionManager = new SessionManager(
            ($config['cache_session'] ?? true) ? $cacheDir : null
        );

        $this->cookieJar = new CookieJar();

        // Cache'den session ve accountId yükle
        $cached = $this->sessionManager->load();
        if ($cached) {
            $this->setCookieFromSession($cached['session_id']);
            $this->accountId = $cached['account_id'] ?? null;
            $this->authenticated = true;
        }

        $guzzleConfig = [
            'base_uri' => $this->baseUrl,
            'timeout' => $this->timeout,
            'verify' => $config['verify_ssl'] ?? true,
            'http_errors' => false,
            'cookies' => $this->cookieJar,
            'headers' => [
                'Accept' => 'application/json, text/plain, */*',
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36',
                'Origin' => $this->baseUrl,
                'Referer' => $this->baseUrl . '/',
            ],
        ];

        if (isset($config['handler'])) {
            if (!$config['handler'] instanceof HandlerStack) {
                throw new \InvalidArgumentException("'handler' bir GuzzleHttp\\HandlerStack olmalı");
            }
            $guzzleConfig['handler'] = $config['handler'];
        }

        $this->http = new GuzzleClient($guzzleConfig);
    }

    // ─── Authentication ──────────────────────────────────────────────

    /**
     * eMikro Portal'a login olur.
     * POST /home/loginEmikro — JSON {email, password}
     * Auth: PHPSESSID cookie ile session bazlı
     *
     * Login sonrası accountId çözülür (bkz. resolveAccountId).
     * Tüm API çağrıları /cp/{accountId}/... altında yapılır.
     */
    public function login(): bool
    {
        // Eski/silinmiş oturum sunucuya gönderilmesin; yeni oturum tek çerezle başlasın.
        $this->forgetSessionCookies();
        $this->authenticated = false;

        try {
            $response = $this->send('POST', '/home/loginEmikro', [
                'json' => [
                    'email' => $this->email,
                    'password' => $this->password,
                ],
            ]);

            $statusCode = $response->getStatusCode();
            $rawBody = (string) $response->getBody();
            $body = json_decode($rawBody, true);

            if ($statusCode !== 200) {
                $hint = match ($statusCode) {
                    403 => $this->isCloudflareBlock($rawBody)
                        ? ' (Cloudflare erişim engeli — çok sık istek; bir süre bekleyin)'
                        : ' (Hesap askıya alınmış veya abonelik süresi dolmuş olabilir)',
                    401 => ' (Kullanıcı adı veya şifre hatalı)',
                    429 => ' (İstek limiti aşıldı, lütfen bekleyin)',
                    default => '',
                };
                throw new AuthenticationException(
                    "eMikro giriş başarısız (HTTP {$statusCode}){$hint}",
                    $statusCode,
                    is_array($body) ? $body : ['raw' => substr($rawBody, 0, 500)]
                );
            }

            // Response'da hata kontrolü
            if (is_array($body) && isset($body['error']) && $body['error']) {
                throw new AuthenticationException(
                    $body['message'] ?? $body['error'] ?? 'Login failed',
                    $statusCode,
                    $body
                );
            }

            // PHPSESSID cookie'yi yakala
            $sessionId = $this->extractSessionCookie();
            $this->authenticated = true;

            // accountId'yi al: login response'dan veya GetAccounts'tan
            $this->resolveAccountId(is_array($body) ? $body : null);

            // Session'ı cache'le
            if ($sessionId) {
                $this->sessionManager->save($sessionId, $this->accountId);
            }

            return true;
        } catch (GuzzleException $e) {
            throw new AuthenticationException(
                'Login request failed: ' . $e->getMessage(),
                $e->getCode(),
                [],
                $e
            );
        }
    }

    /**
     * Login sonrası accountId'yi çöz. Bilinen accountId hiçbir adımda null'a çekilmez.
     * 1. Login response'da varsa oradan al
     * 2. GET /home/GetAccounts (XHR, JSON) — mevcut accountId listede ise korunur, değilse ilki
     * 3. Son çare: /accounts HTML'den parse et
     * Hepsi başarısızsa mevcut değer kalır.
     */
    private function resolveAccountId(?array $loginResponse): void
    {
        // 1. Login response'dan
        if ($loginResponse) {
            $fromLogin = $loginResponse['accountId']
                ?? $loginResponse['account_id']
                ?? $loginResponse['AccountId']
                ?? $loginResponse['id']
                ?? null;

            if (is_scalar($fromLogin) && (string) $fromLogin !== '') {
                $this->accountId = (string) $fromLogin;
                return;
            }
        }

        // 2. /home/GetAccounts (gerçek endpoint; XHR başlığı olmadan HTML dönebiliyor)
        $ids = $this->fetchAccountIds();
        if ($ids) {
            $this->accountId = $this->pickAccountId($ids);
            return;
        }

        // 3. Son çare: /accounts HTML'den parse et
        try {
            $html = (string) $this->send('GET', '/accounts')->getBody();
            if (preg_match_all('#/cp/([a-f0-9\-]{36})/#i', $html, $matches)) {
                $this->accountId = $this->pickAccountId(array_values(array_unique($matches[1])));
            }
        } catch (\Throwable) {
            // Son çare başarısız — mevcut accountId korunur
        }
    }

    /**
     * GET /home/GetAccounts → hesap id listesi. Yanıt JSON değilse (ör. giriş sayfası) boş liste.
     *
     * @return string[]
     */
    private function fetchAccountIds(): array
    {
        try {
            $response = $this->send('GET', '/home/GetAccounts', [
                'headers' => [
                    'X-Requested-With' => 'XMLHttpRequest',
                    'Accept' => 'application/json',
                ],
            ]);
            if ($response->getStatusCode() !== 200) {
                return [];
            }

            $data = json_decode((string) $response->getBody(), true);
            if (!is_array($data) || !is_array($data['accounts'] ?? null)) {
                return [];
            }

            $ids = [];
            foreach ($data['accounts'] as $account) {
                $id = is_array($account) ? ($account['id'] ?? null) : null;
                if (is_scalar($id) && (string) $id !== '') {
                    $ids[] = (string) $id;
                }
            }

            return $ids;
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Mevcut accountId listede varsa onu, yoksa ilk hesabı seçer.
     *
     * @param string[] $ids boş olmamalı
     */
    private function pickAccountId(array $ids): string
    {
        if ($this->accountId !== null) {
            foreach ($ids as $id) {
                if (strcasecmp($id, $this->accountId) === 0) {
                    return $this->accountId;
                }
            }
        }

        return $ids[0];
    }

    public function ensureAuthenticated(): void
    {
        if (!$this->authenticated) {
            $this->login();
        }
    }

    public function isAuthenticated(): bool
    {
        return $this->authenticated;
    }

    public function getAccountId(): ?string
    {
        return $this->accountId;
    }

    /**
     * accountId'yi manuel set et (biliyorsan direkt verebilirsin)
     */
    public function setAccountId(string $accountId): self
    {
        $this->accountId = $accountId;
        return $this;
    }

    public function logout(): void
    {
        $this->authenticated = false;
        $this->accountId = null;
        $this->sessionManager->clear();
        // Guzzle istemcisi aynı jar nesnesini tutuyor → yeni nesne değil, yerinde temizle
        $this->cookieJar->clear();
    }

    // ─── URL Builder ─────────────────────────────────────────────────

    /**
     * /cp/{accountId}/... path'ini oluşturur.
     * eMikro'nun tüm authenticated endpoint'leri bu pattern'i kullanır.
     */
    public function cpPath(string $path): string
    {
        if (!$this->accountId) {
            throw new AuthenticationException(
                'accountId not available. Login first or set it manually.',
                0,
                []
            );
        }

        return '/cp/' . $this->accountId . '/' . ltrim($path, '/');
    }

    // ─── HTTP Methods ────────────────────────────────────────────────

    public function get(string $endpoint, array $query = []): array
    {
        return $this->request('GET', $endpoint, ['query' => $query]);
    }

    public function post(string $endpoint, array $data = []): array
    {
        return $this->request('POST', $endpoint, ['json' => $data]);
    }

    /**
     * POST with multipart/form-data (eMikro birçok endpoint'te bunu kullanıyor)
     */
    public function postForm(string $endpoint, array $fields = []): array
    {
        $multipart = [];
        foreach ($fields as $name => $value) {
            $multipart[] = ['name' => $name, 'contents' => (string) $value];
        }

        return $this->request('POST', $endpoint, ['multipart' => $multipart]);
    }

    public function put(string $endpoint, array $data = []): array
    {
        return $this->request('PUT', $endpoint, ['json' => $data]);
    }

    public function patch(string $endpoint, array $data = []): array
    {
        return $this->request('PATCH', $endpoint, ['json' => $data]);
    }

    public function delete(string $endpoint, array $data = []): array
    {
        return $this->request('DELETE', $endpoint, ['json' => $data]);
    }

    /**
     * Dosya upload (fatura XML vb.)
     */
    public function upload(string $endpoint, string $filePath, string $fieldName = 'file', array $extraFields = []): array
    {
        $multipart = [
            [
                'name' => $fieldName,
                'contents' => fopen($filePath, 'r'),
                'filename' => basename($filePath),
            ],
        ];

        foreach ($extraFields as $key => $value) {
            $multipart[] = ['name' => $key, 'contents' => (string) $value];
        }

        return $this->request('POST', $endpoint, ['multipart' => $multipart]);
    }

    /**
     * Ham response döndürür (PDF/XML download vb.)
     */
    public function download(string $endpoint, array $query = []): string
    {
        $this->ensureAuthenticated();

        try {
            // İndirmede her 3xx oturum düşmesi sayılır (dosya uç noktaları yönlendirme yapmaz)
            [$response, $body] = $this->sendWithRelogin('GET', $endpoint, ['query' => $query], true);

            if ($response->getStatusCode() !== 200) {
                throw new ApiException(
                    'Download failed',
                    $response->getStatusCode(),
                    $endpoint
                );
            }

            return $body;
        } catch (GuzzleException $e) {
            throw new ApiException(
                "Download failed: {$e->getMessage()}",
                $e->getCode(),
                $endpoint,
                [],
                $e
            );
        }
    }

    /**
     * Ham response objesi döndürür
     */
    public function raw(string $method, string $endpoint, array $options = []): ResponseInterface
    {
        $this->ensureAuthenticated();
        return $this->send($method, $endpoint, $options);
    }

    // ─── Internal ────────────────────────────────────────────────────

    private function request(string $method, string $endpoint, array $options = []): array
    {
        $this->ensureAuthenticated();

        try {
            [$response, $rawBody] = $this->sendWithRelogin($method, $endpoint, $options);
            $statusCode = $response->getStatusCode();

            $body = json_decode($rawBody, true);

            if ($statusCode >= 400) {
                $errorBody = is_array($body) ? $body : [];
                if ($statusCode >= 500 && empty($errorBody)) {
                    $errorBody = ['raw' => substr($rawBody, 0, 1000)];
                }
                throw new ApiException(
                    $errorBody['message'] ?? $errorBody['error'] ?? "API error: HTTP {$statusCode}",
                    $statusCode,
                    $endpoint,
                    $errorBody
                );
            }

            // JSON olmayan (giriş sayfası dışındaki) yanıtlar eskisi gibi ['raw' => ...] döner
            return is_array($body) ? $body : ['raw' => $rawBody];
        } catch (GuzzleException $e) {
            throw new ApiException(
                "Request failed: {$e->getMessage()}",
                $e->getCode(),
                $endpoint,
                [],
                $e
            );
        }
    }

    /**
     * İsteği gönderir; oturum düşmüşse tek bir yeniden giriş + tek tekrar dener.
     * Tekrar da giriş sayfasına düşerse sessizce HTML döndürmek yerine hata atar.
     *
     * @return array{0: ResponseInterface, 1: string}
     */
    private function sendWithRelogin(string $method, string $endpoint, array $options, bool $anyRedirectIsLogout = false): array
    {
        $response = $this->send($method, $endpoint, $options);
        $rawBody = (string) $response->getBody();

        if (!$this->isSessionLost($response, $rawBody, $anyRedirectIsLogout)) {
            return [$response, $rawBody];
        }

        $this->authenticated = false;
        $this->login();

        $response = $this->send($method, $endpoint, $options);
        $rawBody = (string) $response->getBody();

        if ($this->isSessionLost($response, $rawBody, $anyRedirectIsLogout)) {
            // Geçersiz oturum bir sonraki çalıştırmada cache'ten geri yüklenmesin
            $this->authenticated = false;
            $this->forgetSessionCookies();
            $this->sessionManager->clear();

            // Mesajda "oturum açılamadı" geçmeli (CRM EmikroGuard bunu 'auth' sınıflar)
            throw new AuthenticationException(
                "eMikro oturumu açılamadı: yeniden girişten sonra da giriş sayfası döndü, oturum açılamadığı için {$method} {$endpoint} tamamlanamadı",
                401,
                ['endpoint' => $endpoint, 'status' => $response->getStatusCode()]
            );
        }

        return [$response, $rawBody];
    }

    /**
     * Oturum düştü mü? 401, giriş sayfasına 302 (yönlendirme izlenmiyorsa)
     * ya da yönlendirme izlendiğinde gelen giriş sayfası HTML'i.
     */
    private function isSessionLost(ResponseInterface $response, string $body, bool $anyRedirectIsLogout): bool
    {
        $status = $response->getStatusCode();

        if ($status === 401) {
            return true;
        }

        if ($status >= 300 && $status < 400) {
            if ($anyRedirectIsLogout) {
                return true;
            }
            $location = strtolower($response->getHeaderLine('Location'));
            if ($location === '') {
                return false;
            }
            // eMikro oturumsuz isteği "/" (giriş sayfası) adresine yönlendiriyor
            return str_contains($location, 'login')
                || in_array(parse_url($location, PHP_URL_PATH), [null, '', '/'], true);
        }

        return $status < 300 && $this->isLoginPage($response, $body);
    }

    private function isLoginPage(ResponseInterface $response, string $body): bool
    {
        $isHtml = str_contains(strtolower($response->getHeaderLine('Content-Type')), 'text/html')
            || preg_match('/^\s*<(!doctype\s+html|html)/i', $body) === 1;

        if (!$isHtml) {
            return false;
        }

        foreach (self::LOGIN_PAGE_MARKERS as $marker) {
            if (str_contains($body, $marker)) {
                return true;
            }
        }

        return false;
    }

    private function isCloudflareBlock(string $body): bool
    {
        $lower = strtolower($body);
        foreach (self::CLOUDFLARE_MARKERS as $marker) {
            if (str_contains($lower, $marker)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Tüm eMikro istekleri buradan geçer: istek öncesi ve yanıt sonrası
     * jar'daki PHPSESSID çerezleri teke indirilir.
     */
    private function send(string $method, string $uri, array $options = []): ResponseInterface
    {
        $this->dedupeSessionCookies();

        try {
            return $this->http->request($method, $uri, $options);
        } finally {
            $this->dedupeSessionCookies();
        }
    }

    // ─── Oturum çerezi ───────────────────────────────────────────────

    /**
     * Bu host için birden fazla PHPSESSID varsa yalnız en son ayarlananı bırakır.
     * Guzzle 7.15+ çakışma kontrolüne HostOnly'yi de kattığından Domain'li ve
     * Domain'siz iki PHPSESSID aynı anda kalabiliyor; PHP ilkini (eski oturumu) okuyor.
     */
    private function dedupeSessionCookies(): void
    {
        $sessions = $this->sessionCookies();
        if (count($sessions) < 2) {
            return;
        }

        $newest = end($sessions);
        $this->forgetSessionCookies($sessions);
        $this->cookieJar->setCookie($newest);
    }

    /**
     * Bu host'a gidecek PHPSESSID çerezleri, jar sırasıyla.
     * CookieJar yeni/değişen çerezi sona eklediği için son eleman en yenisidir.
     *
     * @return SetCookie[]
     */
    private function sessionCookies(): array
    {
        $found = [];
        foreach ($this->cookieJar as $cookie) {
            if ($cookie->getName() === self::SESSION_COOKIE
                && $cookie->getDomain() !== null
                && $cookie->matchesDomain($this->host)
            ) {
                $found[] = $cookie;
            }
        }

        return $found;
    }

    /**
     * PHPSESSID çerezlerini jar'dan yerinde siler.
     * CookieJar::clear(domain, path, name) HostOnly'ye bakmaz; ad + path + domain eşleşeni siler.
     *
     * @param SetCookie[]|null $sessions
     */
    private function forgetSessionCookies(?array $sessions = null): void
    {
        foreach ($sessions ?? $this->sessionCookies() as $cookie) {
            $this->cookieJar->clear($cookie->getDomain(), $cookie->getPath(), self::SESSION_COOKIE);
        }
    }

    private function extractSessionCookie(): ?string
    {
        $sessions = $this->sessionCookies();

        return $sessions ? end($sessions)->getValue() : null;
    }

    /**
     * Cache'teki oturumu sunucunun Set-Cookie'siyle aynı kimlikte yükler
     * (Domain=host, Path=/, HostOnly). Böylece sunucu oturumu yenilediğinde
     * Guzzle bu çerezi değiştirir; ikinci bir PHPSESSID oluşmaz.
     */
    private function setCookieFromSession(string $sessionId): void
    {
        $cookie = new SetCookie([
            'Name' => self::SESSION_COOKIE,
            'Value' => $sessionId,
            'Domain' => $this->host,
            'Path' => '/',
            'Discard' => true,
        ]);

        // HostOnly Guzzle 7.15+'da var; eski sürümlerde çakışma anahtarı zaten ad+domain+path
        if (method_exists($cookie, 'setHostOnly')) {
            $cookie->setHostOnly(true);
        }

        $this->forgetSessionCookies();
        $this->cookieJar->setCookie($cookie);
    }

    // ─── Getters ─────────────────────────────────────────────────────

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function getHttpClient(): GuzzleClient
    {
        return $this->http;
    }

    public function getCookieJar(): CookieJar
    {
        return $this->cookieJar;
    }
}
