<?php

namespace ZirveDonusum\Uyumsoft\Services;

/**
 * Uyumsoft E-Adisyon servisi (Restoran/Kafe adisyon belgesi).
 * API: POST /api/AdisyonApi
 * Referans: iuyum_api/Bilgi Sistemleri/E-Adisyon/
 *
 * GuestCheck: UBL-TR CreditNote formatinda adisyon belgesi (ProfileID=EARSIVBELGE, TypeCode=ADISYON)
 */
class EAdisyonService extends BaseService
{
    /**
     * E-Adisyon gonder.
     *
     * @param  array $guestChecks  Her eleman {"GuestCheck": {...UBL-TR CreditNote...}} yapısında
     */
    public function send(array $guestChecks): array
    {
        return $this->http->action('SendGuestCheck', [
            'guestChecks' => $guestChecks,
        ]);
    }

    /**
     * Adisyon sorgula (number veya UUID ile).
     */
    public function get(string $id): array
    {
        return $this->http->action('GetGuestCheck', [
            'id' => $id,
        ]);
    }

    /**
     * Adisyon ham veri sorgula.
     */
    public function getData(string $id): array
    {
        return $this->http->action('GetGuestCheckData', [
            'id' => $id,
        ]);
    }

    /**
     * Adisyon iptal et.
     */
    public function cancel(string $id): array
    {
        return $this->http->action('CancelGuestCheck', [
            'id' => $id,
        ]);
    }

    /**
     * Adisyon listesi sorgula.
     *
     * @param  array $context  orn. ["GuestCheckNumber" => "GIB2021123456759"]
     */
    public function queryList(array $context = []): array
    {
        return $this->http->action('QueryGuestCheckList', [
            'context' => $context,
        ]);
    }

    /**
     * Adisyon durumu sorgula.
     */
    public function queryStatus(string $id): array
    {
        return $this->http->action('QueryGuestCheckStatus', [
            'id' => $id,
        ]);
    }

    /**
     * Adisyon durumu + islem loglariyla sorgula.
     */
    public function queryStatusWithLogs(string $id): array
    {
        return $this->http->action('QueryGuestCheckStatusWithLogs', [
            'id' => $id,
        ]);
    }

    /**
     * Adisyon HTML gorunumu getir.
     */
    public function getHtml(string $id): array
    {
        return $this->http->action('GetHtmlView', [
            'id' => $id,
        ]);
    }

    /**
     * Adisyon PDF gorunumu getir.
     */
    public function getPdf(string $id): array
    {
        return $this->http->action('GetPdfView', [
            'id' => $id,
        ]);
    }

    /**
     * Adisyon taslak gonder (draft'i yayimla).
     */
    public function sendDraft(string $id): array
    {
        return $this->http->action('SendDraft', [
            'id' => $id,
        ]);
    }

    /**
     * Sistem tarihini getir.
     */
    public function getSystemDate(): array
    {
        return $this->http->action('GetSystemDate');
    }
}
