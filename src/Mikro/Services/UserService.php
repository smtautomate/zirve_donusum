<?php

namespace ZirveDonusum\Mikro\Services;

/**
 * Kullanıcı / Hesap / Yetki İşlemleri
 *
 * Gerçek endpoint'ler:
 *   GET /cp/{accountId}/user/GetUserAuthorizations
 *   GET /cp/{accountId}/VersionHistory/GetVersionCount
 *   GET /cp/{accountId}/Nace/ShouldChange
 *   GET /Account/GetIysInformation?accountId={accountId}
 */
class UserService extends BaseService
{
    /**
     * Kullanıcı yetkileri
     */
    public function getAuthorizations(): array
    {
        return $this->http->get($this->cp('user/GetUserAuthorizations'));
    }

    /**
     * Versiyon geçmişi sayısı
     */
    public function getVersionCount(): array
    {
        return $this->http->get($this->cp('VersionHistory/GetVersionCount'));
    }

    /**
     * NACE kodu değişikliği gerekiyor mu
     */
    public function shouldChangeNace(): array
    {
        return $this->http->get($this->cp('Nace/ShouldChange'));
    }

    /**
     * İYS (İleti Yönetim Sistemi) bilgisi
     */
    public function getIysInformation(): array
    {
        return $this->http->get('/Account/GetIysInformation', [
            'accountId' => $this->http->getAccountId(),
        ]);
    }
}
