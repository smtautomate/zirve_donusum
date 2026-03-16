<?php

namespace ZirveDonusum\Mikro\Services;

/**
 * Dashboard / Ana Sayfa Verileri
 *
 * Gerçek endpoint'ler:
 *   GET /cp/{accountId}/dashboard/GetUserServices
 *   GET /cp/{accountId}/dashboard/GetDocumentDraftCount?date=...
 *   GET /cp/{accountId}/dashboard/GetUserPacketCountInfo?date=...
 *   GET /cp/{accountId}/dashboard/ShouldShowLeadInformationCard
 *   GET /cp/{accountId}/dashboard/CloseLeadInformationCard
 */
class DashboardService extends BaseService
{
    /**
     * Kullanıcının aktif servisleri (e-Fatura, e-Arşiv, e-İrsaliye vb.)
     */
    public function getUserServices(): array
    {
        return $this->http->get($this->cp('dashboard/GetUserServices'));
    }

    /**
     * Taslak belge sayısı
     */
    public function getDocumentDraftCount(?string $date = null, bool $isOnlyMonth = false): array
    {
        return $this->http->get($this->cp('dashboard/GetDocumentDraftCount'), [
            'date' => $date ?? date('D M d Y'),
            'isOnlyMonth' => $isOnlyMonth ? 'true' : 'false',
        ]);
    }

    /**
     * Kullanıcı paket kullanım bilgisi
     */
    public function getUserPacketCountInfo(?string $date = null, bool $enableCaching = true, bool $initialize = true): array
    {
        return $this->http->get($this->cp('dashboard/GetUserPacketCountInfo'), [
            'date' => $date ?? date('D M d Y'),
            'enableCaching' => $enableCaching ? 'true' : 'false',
            'initialize' => $initialize ? 'true' : 'false',
        ]);
    }

    /**
     * Lead bilgi kartı gösterilmeli mi
     */
    public function shouldShowLeadInformationCard(): array
    {
        return $this->http->get($this->cp('dashboard/ShouldShowLeadInformationCard'));
    }

    /**
     * Lead bilgi kartını kapat
     */
    public function closeLeadInformationCard(): array
    {
        return $this->http->get($this->cp('dashboard/CloseLeadInformationCard'));
    }
}
