<?php

namespace ZirveDonusum\Services;

/**
 * Firma / Şirket / Mükellef İşlemleri
 */
class CompanyService extends BaseService
{
    /**
     * Firma bilgilerini getir
     */
    public function info(): array
    {
        return $this->http->get('/home/getCompanyInfo');
    }

    /**
     * Mükellef sorgula (VKN/TCKN ile)
     */
    public function lookupTaxpayer(string $taxNumber): array
    {
        return $this->http->postForm('/home/checkTaxpayer', [
            'taxNumber' => $taxNumber,
        ]);
    }

    /**
     * E-Fatura mükellefi mi kontrol et
     */
    public function checkEInvoiceRegistered(string $taxNumber): array
    {
        return $this->http->postForm('/home/checkEInvoiceUser', [
            'taxNumber' => $taxNumber,
        ]);
    }

    /**
     * Kullanıcı profil bilgisi
     */
    public function profile(): array
    {
        return $this->http->get('/home/getUserProfile');
    }

    /**
     * Dashboard / Ana sayfa verileri
     */
    public function dashboard(): array
    {
        return $this->http->get('/home/getDashboard');
    }
}
