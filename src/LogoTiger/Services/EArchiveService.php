<?php

namespace ZirveDonusum\LogoTiger\Services;

/**
 * Logo Tiger E-Arsiv entegrasyonu.
 * Endpoint: /eArchives
 */
class EArchiveService extends BaseService
{
    public function index(array $query = []): array
    {
        return $this->http->get('eArchives', $query);
    }

    public function show(int|string $id): array
    {
        return $this->http->get("eArchives/{$id}");
    }

    public function send(array $data): array
    {
        return $this->http->post('eArchives/send', $data);
    }

    public function cancel(int|string $id, array $data = []): array
    {
        return $this->http->post("eArchives/{$id}/cancel", $data);
    }

    public function downloadPdf(int|string $id): string
    {
        return $this->http->download("eArchives/{$id}/pdf");
    }
}
