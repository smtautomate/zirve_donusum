<?php

/**
 * Yerel doğrulama: testleri canlıdaki Guzzle 7.15.5 ile koşturur (composer'a dokunmadan).
 *
 *   php vendor/bin/phpunit --bootstrap tests/bootstrap-guzzle715.php
 *
 * GuzzleHttp\ sınıfları crm-system/vendor'dan (7.15.5) yüklenir; geri kalan her şey bu
 * reponun vendor'ından. Yol GUZZLE715_VENDOR ortam değişkeniyle değiştirilebilir.
 */

$vendor = rtrim(getenv('GUZZLE715_VENDOR') ?: '/Users/sercanozbilek/Documents/GitHub/crm-system/vendor', '/');

// Composer kendi yükleyicisini başa (prepend) ekler; bu yüzden önce o, sonra bizimki.
require_once __DIR__ . '/../vendor/autoload.php';

foreach (get_declared_classes() as $class) {
    if (str_starts_with($class, 'GuzzleHttp\\')) {
        fwrite(STDERR, "Guzzle sınıfı erken yüklendi ({$class}); 7.15.5 garanti edilemez.\n");
        exit(1);
    }
}

$prefixes = [
    // En uzun önek önce
    'GuzzleHttp\\Psr7\\' => $vendor . '/guzzlehttp/psr7/src/',
    'GuzzleHttp\\Promise\\' => $vendor . '/guzzlehttp/promises/src/',
    'GuzzleHttp\\UriTemplate\\' => $vendor . '/guzzlehttp/uri-template/src/',
    'GuzzleHttp\\' => $vendor . '/guzzlehttp/guzzle/src/',
];

spl_autoload_register(static function (string $class) use ($prefixes): void {
    foreach ($prefixes as $prefix => $dir) {
        if (str_starts_with($class, $prefix)) {
            $file = $dir . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
            if (is_file($file)) {
                require $file;
            }
            return;
        }
    }
}, true, true);

$clientFile = (new ReflectionClass(GuzzleHttp\Client::class))->getFileName();
$composerJson = json_decode((string) file_get_contents($vendor . '/composer/installed.json'), true);
$version = 'bilinmiyor';
foreach ($composerJson['packages'] ?? [] as $package) {
    if ($package['name'] === 'guzzlehttp/guzzle') {
        $version = $package['version'];
    }
}

if (!str_starts_with($clientFile, $vendor . '/') || !method_exists(GuzzleHttp\Cookie\SetCookie::class, 'setHostOnly')) {
    fwrite(STDERR, "Guzzle 7.15.x yüklenemedi: {$clientFile}\n");
    exit(1);
}

fwrite(STDERR, "Guzzle {$version} ({$clientFile})\n");
