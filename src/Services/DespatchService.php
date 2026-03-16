<?php

namespace ZirveDonusum\Services;

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
        return $this->http->get('/edespatch/getIncomingDespatches', $filters);
    }

    /**
     * Giden irsaliyeleri listele
     */
    public function listOutgoing(array $filters = []): array
    {
        return $this->http->get('/edespatch/getOutgoingDespatches', $filters);
    }

    /**
     * İrsaliye detayı
     */
    public function get(string $despatchId): array
    {
        return $this->http->get('/edespatch/getDespatchDetail', ['id' => $despatchId]);
    }

    /**
     * İrsaliye gönder
     */
    public function send(array $despatchData): array
    {
        return $this->http->post('/edespatch/sendDespatch', $despatchData);
    }

    /**
     * İrsaliye yanıtla (kabul/red)
     */
    public function respond(string $despatchId, string $action, string $reason = ''): array
    {
        return $this->http->postForm('/edespatch/respondDespatch', [
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
        return $this->http->download('/edespatch/downloadPdf', ['id' => $despatchId]);
    }
}
