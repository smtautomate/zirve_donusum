<?php

namespace ZirveDonusum\Uyumsoft\Services;

/**
 * Uyumsoft E-Irsaliye servisi.
 * Endpoint: /Integration/EIrsaliye
 */
class EWaybillService extends BaseService
{
    public function send(array $waybill): array
    {
        return $this->http->post('Integration/EIrsaliye/SendDespatch', [
            'Despatch' => $waybill,
        ]);
    }

    public function listOutgoing(array $filters = []): array
    {
        return $this->http->post('Integration/EIrsaliye/GetOutboxDespatches', $filters);
    }

    public function listIncoming(array $filters = []): array
    {
        return $this->http->post('Integration/EIrsaliye/GetInboxDespatches', $filters);
    }

    public function status(string $uuid): array
    {
        return $this->http->post('Integration/EIrsaliye/GetDespatchStatus', [
            'Uuid' => $uuid,
        ]);
    }

    public function respond(string $uuid, string $action, string $reason = ''): array
    {
        return $this->http->post('Integration/EIrsaliye/RespondDespatch', [
            'Uuid' => $uuid,
            'Action' => $action,
            'Reason' => $reason,
        ]);
    }
}
