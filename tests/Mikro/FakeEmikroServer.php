<?php

namespace ZirveDonusum\Tests\Mikro;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;

/**
 * MockHandler kuyruğuna konan, eMikro'yu taklit eden yanıt üreticileri.
 *
 * Canlıda gözlenen davranış:
 * - Giriş oturumu yeniler (eski id silinir) ve Domain'siz PHPSESSID'i iki kez Set-Cookie ile döner,
 *   ayrıca Domain=mikrogrup.com olan Cloudflare çerezleri gelir.
 * - PHP, Cookie başlığındaki İLK PHPSESSID'i okur; geçersizse giriş sayfasına düşer
 *   (302 → /, Guzzle yönlendirmeyi izler → 200 text/html giriş sayfası).
 */
final class FakeEmikroServer
{
    /** Sunucunun geçerli saydığı oturum */
    public ?string $validSession;

    private int $rotation = 0;

    public function __construct(?string $validSession = null)
    {
        $this->validSession = $validSession;
    }

    /** POST /home/loginEmikro → oturumu yeniler, {"Success":"true"} */
    public function login(?string $newSession = null): callable
    {
        return function (RequestInterface $request) use ($newSession): Response {
            if ($request->getMethod() !== 'POST' || $request->getUri()->getPath() !== '/home/loginEmikro') {
                return self::unexpected($request);
            }

            $this->validSession = $newSession ?? 'NEW' . (++$this->rotation);
            $sessionCookie = 'PHPSESSID=' . $this->validSession . '; expires=' . self::expires() . '; path=/; secure; HttpOnly';

            return new Response(200, [
                'Content-Type' => 'application/json; charset=utf-8',
                'Set-Cookie' => [
                    $sessionCookie,
                    $sessionCookie,
                    '__cf_bm=cfbm' . $this->rotation . '; path=/; expires=' . self::expires() . '; domain=mikrogrup.com; HttpOnly; Secure; SameSite=None',
                    '_cfuvid=cfuvid' . $this->rotation . '; path=/; domain=mikrogrup.com; HttpOnly; Secure; SameSite=None',
                ],
            ], '{"Success":"true"}');
        };
    }

    /**
     * Oturum gerektiren JSON uç noktası. Oturum geçersizse giriş sayfası döner.
     * $renewTo verilirse sunucu bu yanıtta oturumu yeniler (Set-Cookie);
     * $renewDomain verilirse çerez Domain özniteliğiyle gelir.
     */
    public function json(string $path, array $payload, ?string $renewTo = null, ?string $renewDomain = null): callable
    {
        return function (RequestInterface $request) use ($path, $payload, $renewTo, $renewDomain): Response {
            if ($request->getUri()->getPath() !== $path) {
                return self::unexpected($request);
            }
            if (self::sessionPhpReads($request) !== $this->validSession) {
                return self::loginPage();
            }

            $headers = ['Content-Type' => 'application/json; charset=utf-8'];
            if ($renewTo !== null) {
                $this->validSession = $renewTo;
                $headers['Set-Cookie'] = 'PHPSESSID=' . $renewTo . '; expires=' . self::expires() . '; path=/; '
                    . ($renewDomain !== null ? 'domain=' . $renewDomain . '; ' : '') . 'secure; HttpOnly';
            }

            return new Response(200, $headers, json_encode($payload, JSON_UNESCAPED_UNICODE));
        };
    }

    /** Oturum gerektiren dosya uç noktası (PDF/UBL indirme) */
    public function file(string $path, string $bytes, string $contentType = 'application/pdf'): callable
    {
        return function (RequestInterface $request) use ($path, $bytes, $contentType): Response {
            if ($request->getUri()->getPath() !== $path) {
                return self::unexpected($request);
            }
            if (self::sessionPhpReads($request) !== $this->validSession) {
                return self::loginPage();
            }

            return new Response(200, ['Content-Type' => $contentType], $bytes);
        };
    }

    /** Sunucu tarafında oturum düşer (zaman aşımı) → giriş sayfası */
    public function expireSession(): callable
    {
        return function (): Response {
            $this->validSession = null;

            return self::loginPage();
        };
    }

    /** Yönlendirme izlendikten sonra gelen eMikro giriş sayfası (24KB'lık sayfanın özü) */
    public static function loginPage(): Response
    {
        return new Response(200, ['Content-Type' => 'text/html; charset=utf-8'], <<<'HTML'
            <!DOCTYPE html>
            <html lang="tr">
            <head><meta charset="utf-8"><title>e-Portal Girişi</title></head>
            <body>
            <form id="loginForm" onsubmit="return loginEmikro();">
              <input type="email" name="email"><input type="password" name="password">
            </form>
            <script>function loginEmikro(){ fetch('/home/loginEmikro', {method: 'POST'}); return false; }</script>
            </body>
            </html>
            HTML);
    }

    /** Oturumsuz istek → 302 / (giriş sayfası) */
    public static function redirectToLogin(): Response
    {
        return new Response(302, ['Location' => '/', 'Content-Type' => 'text/html; charset=utf-8'], '');
    }

    /** Cloudflare "Attention Required" engel sayfası */
    public static function cloudflareBlock(): Response
    {
        return new Response(403, ['Content-Type' => 'text/html; charset=UTF-8', 'Server' => 'cloudflare', 'CF-RAY' => '8d1f2c3b4a5e6f70-IST'], <<<'HTML'
            <!DOCTYPE html>
            <!--[if lt IE 7]> <html class="no-js ie6 oldie" lang="en-US"> <![endif]-->
            <head><title>Attention Required! | Cloudflare</title></head>
            <body><h1>Sorry, you have been blocked</h1>
            <span>Cloudflare Ray ID: <strong>8d1f2c3b4a5e6f70</strong></span></body>
            </html>
            HTML);
    }

    /** PHP'nin okuduğu oturum: Cookie başlığındaki ilk PHPSESSID */
    public static function sessionPhpReads(RequestInterface $request): ?string
    {
        return self::sessionIdsIn($request)[0] ?? null;
    }

    /**
     * İstekte gerçekten gönderilen PHPSESSID değerleri (sırasıyla).
     *
     * @return string[]
     */
    public static function sessionIdsIn(RequestInterface $request): array
    {
        $ids = [];
        foreach (explode(';', $request->getHeaderLine('Cookie')) as $pair) {
            $parts = explode('=', trim($pair), 2);
            if (count($parts) === 2 && $parts[0] === 'PHPSESSID') {
                $ids[] = $parts[1];
            }
        }

        return $ids;
    }

    private static function unexpected(RequestInterface $request): Response
    {
        return new Response(404, ['Content-Type' => 'application/json'], json_encode([
            'message' => 'Beklenmeyen istek: ' . $request->getMethod() . ' ' . $request->getUri()->getPath(),
        ], JSON_UNESCAPED_UNICODE));
    }

    private static function expires(): string
    {
        return gmdate('D, d-M-Y H:i:s', time() + 86400) . ' GMT';
    }
}
