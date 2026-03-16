<?php

namespace ZirveDonusum\Mikro\Services;

/**
 * E-İrsaliye İşlemleri
 */
class DespatchService extends BaseService
{
    /**
     * Gelen irsaliyeleri listele
     */
    public function listIncoming(array $filters = []): array
    {
        return $this->http->get($this->cp('edespatch/GetIncomingDespatches'), $filters);
    }

    /**
     * Giden irsaliyeleri listele
     */
    public function listOutgoing(array $filters = []): array
    {
        return $this->http->get($this->cp('edespatch/GetOutgoingDespatches'), $filters);
    }

    /**
     * İrsaliye detayı
     */
    public function get(string $despatchId): array
    {
        return $this->http->get($this->cp('edespatch/GetDespatchDetail'), ['id' => $despatchId]);
    }

    /**
     * İrsaliye gönder
     */
    public function send(array $despatchData): array
    {
        return $this->http->post($this->cp('edespatch/SendDespatch'), $despatchData);
    }

    /**
     * İrsaliye yanıtla (kabul/red)
     */
    public function respond(string $despatchId, string $action, string $reason = ''): array
    {
        return $this->http->postForm($this->cp('edespatch/RespondDespatch'), [
            'id' => $despatchId,
            'action' => $action,
            'reason' => $reason,
        ]);
    }

    /**
     * İrsaliye PDF indir
     */
    public function downloadPdf(string $despatchId): string
    {
        return $this->http->download($this->cp('edespatch/DownloadPdf'), ['id' => $despatchId]);
    }
}
