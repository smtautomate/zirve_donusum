<?php

namespace ZirveDonusum\LogoTiger\Services;

/**
 * Logo Tiger E-Irsaliye entegrasyonu.
 * Endpoint: /eDespatches
 */
class EWaybillService extends BaseService
{
    public function listOutgoing(array $query = []): array
    {
        return $this->http->get('eDespatches/outgoing', $query);
    }

    public function listIncoming(array $query = []): array
    {
        return $this->http->get('eDespatches/incoming', $query);
    }

    public function show(int|string $id): array
    {
        return $this->http->get("eDespatches/{$id}");
    }

    public function send(array $data): array
    {
        return $this->http->post('eDespatches/send', $data);
    }

    public function respond(int|string $id, string $action, array $data = []): array
    {
        return $this->http->post("eDespatches/{$id}/{$action}", $data);
    }
}
