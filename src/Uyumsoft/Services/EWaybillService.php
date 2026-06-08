<?php

namespace ZirveDonusum\Uyumsoft\Services;

/**
 * Uyumsoft E-Irsaliye (Despatch) servisi.
 * API: POST /api/DespatchApi
 * Referans: iuyum_api/Bilgi Sistemleri/E-Irsaliye/RestApı/Request/
 */
class EWaybillService extends BaseService
{
    /**
     * E-Irsaliye gonder (DespatchAdvice UBL formati).
     *
     * @param  array $despatches  Her eleman {"DespatchAdvice": {...UBL...}} yapısında
     */
    public function send(array $despatches): array
    {
        return $this->http->action('SendDespatch', [
            'despatches' => $despatches,
        ]);
    }

    /**
     * Sikistirilmis (zip) e-Irsaliye gonder.
     */
    public function sendCompressed(array $parameters = []): array
    {
        return $this->http->action('CompressedSendDespatch', $parameters);
    }

    /**
     * VKN/TCKN ile e-Irsaliye kullanicisi sorgula.
     */
    public function getUsers(string $vknTckn): array
    {
        return $this->http->action('GetEDespatchUsers', [
            'vknTckn' => $vknTckn,
        ]);
    }

    /**
     * Gelen tek e-Irsaliye detayi (UUID ile).
     */
    public function getIncoming(string $uuid): array
    {
        return $this->http->action('GetInboxDespatch', [
            'uuid' => $uuid,
        ]);
    }

    /**
     * Gelen e-Irsaliye listesi.
     *
     * @param  array $filters  startDate, endDate, limit vb.
     */
    public function listIncoming(array $filters = []): array
    {
        return $this->http->action('GetInboxDespatchList', $filters);
    }

    /**
     * Gelen e-Irsaliye HTML/XML goruntulu detayi.
     */
    public function getIncomingView(string $uuid): array
    {
        return $this->http->action('GetInboxDespatchView', [
            'uuid' => $uuid,
        ]);
    }

    /**
     * Giden tek e-Irsaliye detayi (UUID ile).
     */
    public function getOutgoing(string $uuid): array
    {
        return $this->http->action('GetOutboxDespatch', [
            'uuid' => $uuid,
        ]);
    }

    /**
     * Giden e-Irsaliye listesi.
     *
     * @param  array $filters  startDate, endDate, limit vb.
     */
    public function listOutgoing(array $filters = []): array
    {
        return $this->http->action('GetOutboxDespatchList', $filters);
    }

    /**
     * Giden e-Irsaliye HTML/XML goruntulu detayi.
     */
    public function getOutgoingView(string $uuid): array
    {
        return $this->http->action('GetOutboxDespatchView', [
            'uuid' => $uuid,
        ]);
    }

    /**
     * E-Irsaliyeyi taslak olarak kaydet.
     */
    public function saveDraft(array $despatches): array
    {
        return $this->http->action('SaveAsDraftİrsaliye', [
            'despatches' => $despatches,
        ]);
    }
}
